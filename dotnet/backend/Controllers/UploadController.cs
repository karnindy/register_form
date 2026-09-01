using Microsoft.AspNetCore.Mvc;
using backend.Data;
using backend.Models;
using System.IO;
using System;
using System.Linq;
using System.Threading.Tasks;
using System.Text.Json;
using Microsoft.EntityFrameworkCore;

namespace backend.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class UploadController : ControllerBase
    {
        private readonly AppDbContext _context;
        private readonly IWebHostEnvironment _env;

        public UploadController(AppDbContext context, IWebHostEnvironment env)
        {
            _context = context;
            _env = env;
        }

        [HttpPost]
        public async Task<IActionResult> UploadDocuments([FromForm] string nationalId, [FromForm] string? phone = null)
        {
            if (string.IsNullOrEmpty(nationalId)) return BadRequest("National ID is required");
            
            var cleanNationId = nationalId.Replace("-", "");

            // Check if user exists
            var person = await _context.Persons
                .Include(p => p.Registrations)
                .FirstOrDefaultAsync(p => p.NationId == cleanNationId);
                
            if (person == null) return NotFound("Person not found");

            var uploadPath = Path.Combine(_env.ContentRootPath, "LocalData", "uploads", cleanNationId);
            if (!Directory.Exists(uploadPath))
            {
                Directory.CreateDirectory(uploadPath);
            }

            // Snapshot old documents before updating
            var oldDocs = await _context.PersonDocuments
                .Where(d => d.NationId == cleanNationId)
                .Select(d => new
                {
                    d.DocumentType,
                    UploadedAt = d.UploadedAt.HasValue ? d.UploadedAt.Value.ToString("yyyy-MM-dd HH:mm:ss") : null,
                    SizeBytes = d.FileData != null ? d.FileData.Length : 0
                })
                .ToListAsync();

            var files = Request.Form.Files;
            var uploadedSummary = new System.Collections.Generic.List<object>();

            foreach (var file in files)
            {
                if (file.Length > 0)
                {
                    // Normalize DocumentType (e.g. Profile, IDCardFace, IDCard)
                    var rawDocType = file.Name;
                    var docType = rawDocType;
                    if (rawDocType.Equals("profile", StringComparison.OrdinalIgnoreCase)) docType = "Profile";
                    else if (rawDocType.Equals("idcardface", StringComparison.OrdinalIgnoreCase) || rawDocType.Equals("idcardwithface", StringComparison.OrdinalIgnoreCase)) docType = "IDCardFace";
                    else if (rawDocType.Equals("idcard", StringComparison.OrdinalIgnoreCase)) docType = "IDCard";

                    using (var ms = new MemoryStream())
                    {
                        await file.CopyToAsync(ms);
                        var fileBytes = ms.ToArray();
                        var contentType = file.ContentType;

                        var existingDoc = await _context.PersonDocuments.FirstOrDefaultAsync(d => d.NationId == cleanNationId && d.DocumentType == docType);
                        if (existingDoc != null)
                        {
                            // Update existing record
                            existingDoc.FileData = fileBytes;
                            existingDoc.ContentType = contentType;
                            existingDoc.UploadedAt = DateTime.Now;
                            existingDoc.FilePath = null;
                            _context.PersonDocuments.Update(existingDoc);
                        }
                        else
                        {
                            // Add new record
                            var doc = new PersonDocument
                            {
                                NationId = cleanNationId,
                                DocumentType = docType,
                                FileData = fileBytes,
                                ContentType = contentType,
                                UploadedAt = DateTime.Now
                            };
                            _context.PersonDocuments.Add(doc);
                        }

                        uploadedSummary.Add(new
                        {
                            DocumentType = docType,
                            FileName = file.FileName,
                            SizeBytes = file.Length,
                            ContentType = contentType,
                            UploadedAt = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")
                        });
                    }
                }
            }

            // Once documents are uploaded, we consider the registration fully confirmed
            var registration = _context.PersonRegistrations.OrderByDescending(r => r.Id).FirstOrDefault(r => r.NationId == cleanNationId);
            if (registration != null)
            {
                registration.confirmed = true;
                registration.completion_time = DateTime.Now;
            }

            // Extract caller user info from JWT token if available
            string callerUsername = person.EmailAlt ?? person.FirstNameTh ?? "applicant";
            string callerRole = "applicant";
            try
            {
                var authHeader = Request.Headers["Authorization"].FirstOrDefault();
                if (!string.IsNullOrEmpty(authHeader) && authHeader.StartsWith("Bearer "))
                {
                    var token = authHeader.Substring("Bearer ".Length).Trim();
                    var tokenHandler = new System.IdentityModel.Tokens.Jwt.JwtSecurityTokenHandler();
                    if (tokenHandler.CanReadToken(token))
                    {
                        var jwt = tokenHandler.ReadJwtToken(token);
                        var uName = jwt.Claims.FirstOrDefault(c => c.Type == System.Security.Claims.ClaimTypes.Name || c.Type == "unique_name" || c.Type == "sub")?.Value;
                        var uRole = jwt.Claims.FirstOrDefault(c => c.Type == System.Security.Claims.ClaimTypes.Role || c.Type == "role")?.Value;
                        if (!string.IsNullOrEmpty(uName)) callerUsername = uName;
                        if (!string.IsNullOrEmpty(uRole)) callerRole = uRole.ToLower();
                    }
                }
            }
            catch { }

            var regId = registration?.Id ?? 0;
            var oldSnapshot = new
            {
                Action = "อัปโหลด / แก้ไขรูปภาพและเอกสารประจำตัว",
                NationId = cleanNationId,
                TitleTh = person.TitleTh,
                FirstNameTh = person.FirstNameTh,
                LastNameTh = person.LastNameTh,
                Documents = oldDocs
            };

            var newSnapshot = new
            {
                Action = "อัปโหลด / แก้ไขรูปภาพและเอกสารประจำตัว",
                NationId = cleanNationId,
                TitleTh = person.TitleTh,
                FirstNameTh = person.FirstNameTh,
                LastNameTh = person.LastNameTh,
                UploadedFiles = uploadedSummary,
                UpdatedBy = callerUsername
            };

            var historyRecord = new RegisterHistory
            {
                RegisterId = regId,
                EditedByType = callerRole,
                CreatedBy = callerUsername,
                OldData = JsonSerializer.Serialize(oldSnapshot),
                NewData = JsonSerializer.Serialize(newSnapshot),
                CreatedAt = DateTime.Now
            };
            _context.RegisterHistories.Add(historyRecord);

            var regHistoryNew = new RegistrationHistoryModel
            {
                NationId = cleanNationId,
                RegisterId = registration != null ? registration.Id : null,
                EditedByType = callerRole,
                CreatedBy = callerUsername,
                OldData = JsonSerializer.Serialize(oldSnapshot),
                NewData = JsonSerializer.Serialize(newSnapshot),
                CreatedAt = DateTime.Now
            };
            _context.RegistrationHistoriesNew.Add(regHistoryNew);

            await _context.SaveChangesAsync();

            return Ok(new { message = "Files uploaded successfully and history recorded", files = uploadedSummary });
        }
    }
}
