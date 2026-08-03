using System;
using System.Diagnostics;
using System.Net.Http;
using System.Threading.Tasks;
using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;
using System.Text.Json;

namespace backend.Services
{
    public class OicApiService
    {
        private readonly AppDbContext _context;
        private readonly HttpClient _httpClient;

        public OicApiService(AppDbContext context, HttpClient httpClient)
        {
            _context = context;
            _httpClient = httpClient;
        }

        public async Task<OicAgentProfileStore?> GetProfileFromDbAsync(string idCardNumber)
        {
            var cleanId = idCardNumber.Replace("-", "").Trim();
            return await _context.OicAgentProfileStores
                .Where(p => p.IdCardNumber == cleanId && p.IsLatest)
                .FirstOrDefaultAsync();
        }

        public async Task<OicAgentProfileStore?> ProcessFileAsync(string filePath, string cleanId)
        {
            var stopwatch = Stopwatch.StartNew();
            int statusCode = 200;
            string? responseBody = null;
            string? errorMessage = null;
            OicApiRequestLog? logEntry = null;

            try
            {
                if (System.IO.File.Exists(filePath))
                {
                    responseBody = await System.IO.File.ReadAllTextAsync(filePath);
                }
                else
                {
                    return null;
                }
            }
            catch (Exception ex)
            {
                statusCode = 500;
                errorMessage = ex.Message;
            }
            finally
            {
                stopwatch.Stop();
                logEntry = new OicApiRequestLog
                {
                    IdCardNumber = cleanId,
                    EndpointUrl = "FILE_WATCHER",
                    HttpMethod = "FILE",
                    HttpStatusCode = statusCode,
                    ResponseBody = responseBody,
                    ErrorMessage = errorMessage,
                    ExecutionTimeMs = (int)stopwatch.ElapsedMilliseconds,
                    CreatedAt = DateTime.UtcNow
                };
                _context.OicApiRequestLogs.Add(logEntry);
                await _context.SaveChangesAsync();
            }

            if (statusCode != 200 || string.IsNullOrWhiteSpace(responseBody))
            {
                Serilog.Log.Warning("OIC Processing failed or file empty for {IdCardNumber}.", cleanId);
                return null;
            }

            // Update existing records to IsLatest = false
            var previousRecords = await _context.OicRawApiStores
                .Where(r => r.IdCardNumber == cleanId && r.IsLatest)
                .ToListAsync();
            foreach (var rec in previousRecords)
            {
                rec.IsLatest = false;
            }

            var rawStore = new OicRawApiStore
            {
                IdCardNumber = cleanId,
                SourceSystem = "OIC_WORKER",
                HttpStatusCode = statusCode,
                RawPayload = responseBody,
                IsLatest = true,
                ReceivedAt = DateTime.UtcNow,
                CreatedAt = DateTime.UtcNow,
                LastSuccessfulLogId = logEntry.LogId
            };
            _context.OicRawApiStores.Add(rawStore);
            await _context.SaveChangesAsync();

            // Map the JSON
            var mappedJson = await MapRawJsonToProfilePayloadAsync(responseBody);

            // Update profile stores
            var previousAgentProfiles = await _context.OicAgentProfileStores
                .Where(o => o.IdCardNumber == cleanId && o.IsLatest)
                .ToListAsync();

            foreach (var p in previousAgentProfiles)
            {
                p.IsLatest = false;
            }

            var newProfile = new OicAgentProfileStore
            {
                IdCardNumber = cleanId,
                SourceSystem = "OIC_WORKER",
                VerificationStatus = "VERIFIED",
                HttpStatusCode = 200,
                ProfilePayload = mappedJson,
                IsLatest = true,
                LastVerifiedAt = DateTime.UtcNow,
                CreatedAt = DateTime.UtcNow,
                UpdatedAt = DateTime.UtcNow,
                LastSuccessfulLogId = logEntry.LogId
            };
            _context.OicAgentProfileStores.Add(newProfile);
            await _context.SaveChangesAsync();

            // Move file to Archives
            var baseDir = System.IO.Directory.GetCurrentDirectory();
            var archiveDir = System.IO.Path.Combine(baseDir, "LocalData", "Archives");
            try
            {
                if (!System.IO.Directory.Exists(archiveDir))
                {
                    System.IO.Directory.CreateDirectory(archiveDir);
                }
                var archivePath = System.IO.Path.Combine(archiveDir, $"{cleanId}_{DateTime.Now:yyyyMMddHHmmss}.json");
                System.IO.File.Move(filePath, archivePath);
            }
            catch (Exception ex)
            {
                Serilog.Log.Error(ex, "Failed to move JSON file to Archives for {IdCardNumber}", cleanId);
            }

            return newProfile;
        }

        private JsonElement? GetJsonElementByPath(JsonElement root, string path)
        {
            if (string.IsNullOrWhiteSpace(path) || path == "$") return root;

            if (path.StartsWith("$."))
                path = path.Substring(2);

            string[] parts = path.Split('.');
            JsonElement current = root;

            foreach (var part in parts)
            {
                if (current.ValueKind != JsonValueKind.Object && current.ValueKind != JsonValueKind.Array)
                    return null;

                int bracketIndex = part.IndexOf('[');
                if (bracketIndex > 0 && part.EndsWith("]"))
                {
                    string propertyName = part.Substring(0, bracketIndex);
                    string indexStr = part.Substring(bracketIndex + 1, part.Length - bracketIndex - 2);
                    
                    if (current.ValueKind == JsonValueKind.Object && current.TryGetProperty(propertyName, out var arrayProp))
                    {
                        if (arrayProp.ValueKind == JsonValueKind.Array && int.TryParse(indexStr, out int index))
                        {
                            if (index >= 0 && index < arrayProp.GetArrayLength())
                            {
                                current = arrayProp[index];
                                continue;
                            }
                        }
                    }
                    return null;
                }
                else
                {
                    if (current.ValueKind == JsonValueKind.Object && current.TryGetProperty(part, out var nextProp))
                    {
                        current = nextProp;
                    }
                    else
                    {
                        return null;
                    }
                }
            }
            return current;
        }

        private async Task<string> MapRawJsonToProfilePayloadAsync(string rawJson)
        {
            var fieldMappings = await _context.OicFieldMappings.Where(f => f.IsActive).ToListAsync();
            var valueMappings = await _context.OicValueMappings.Where(v => v.IsActive).ToListAsync();
            
            var valueMappingDict = valueMappings
                .GroupBy(v => v.TargetFieldName)
                .ToDictionary(
                    g => g.Key, 
                    g => g.GroupBy(v => v.SourceWord).ToDictionary(v => v.Key, v => v.First().TargetWord)
                );

            using var doc = JsonDocument.Parse(rawJson);
            var root = doc.RootElement;
            
            var resultDict = new System.Collections.Generic.Dictionary<string, object?>();

            foreach (var fieldMap in fieldMappings)
            {
                var element = GetJsonElementByPath(root, fieldMap.SourceJsonPath);
                if (element == null || element.Value.ValueKind == JsonValueKind.Null || element.Value.ValueKind == JsonValueKind.Undefined)
                {
                    continue;
                }

                var val = element.Value;
                
                if (fieldMap.DataType.ToLower() == "array" && val.ValueKind == JsonValueKind.Array)
                {
                    var list = new System.Collections.Generic.List<string>();
                    foreach (var item in val.EnumerateArray())
                    {
                        var itemStr = item.GetString();
                        if (itemStr != null)
                        {
                            if (valueMappingDict.TryGetValue(fieldMap.TargetFieldName, out var mappings) && mappings.TryGetValue(itemStr, out var mappedVal))
                            {
                                list.Add(mappedVal);
                            }
                            else
                            {
                                list.Add(itemStr);
                            }
                        }
                    }
                    resultDict[fieldMap.TargetFieldName] = list;
                }
                else if (fieldMap.DataType.ToLower() == "boolean")
                {
                    if (val.ValueKind == JsonValueKind.True || val.ValueKind == JsonValueKind.False)
                    {
                        resultDict[fieldMap.TargetFieldName] = val.GetBoolean();
                    }
                    else if (val.ValueKind == JsonValueKind.String && bool.TryParse(val.GetString(), out bool b))
                    {
                        resultDict[fieldMap.TargetFieldName] = b;
                    }
                }
                else
                {
                    var strVal = val.ValueKind == JsonValueKind.String ? val.GetString() : val.GetRawText();
                    if (strVal != null)
                    {
                        if (valueMappingDict.TryGetValue(fieldMap.TargetFieldName, out var mappings) && mappings.TryGetValue(strVal, out var mappedVal))
                        {
                            resultDict[fieldMap.TargetFieldName] = mappedVal;
                        }
                        else
                        {
                            resultDict[fieldMap.TargetFieldName] = strVal;
                        }
                    }
                }
            }

            return JsonSerializer.Serialize(resultDict);
        }


    }
}
