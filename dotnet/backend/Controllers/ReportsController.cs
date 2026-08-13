using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using backend.Data;
using System.Text.Json;
using backend.Models;

namespace backend.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class ReportsController : ControllerBase
    {
        private readonly AppDbContext _context;

        public ReportsController(AppDbContext context)
        {
            _context = context;
        }

        [HttpGet("Remarks")]
        public async Task<IActionResult> GetRemarksReport()
        {
            // Fetch trainees where Remark is not null or empty
            var trainees = await _context.Registers
                .Where(r => !string.IsNullOrEmpty(r.Remark))
                .OrderByDescending(r => r.CreatedAt)
                .Select(r => new {
                    r.Id,
                    r.FirstNameTh,
                    r.LastNameTh,
                    IdCard = r.NationalId,
                    Mobile = r.PhoneOtp, // assuming PhoneOtp was mapped from mobile
                    RegDate = r.CreatedAt,
                    r.Remark
                })
                .ToListAsync();

            return Ok(trainees);
        }

        public class CourseSelection
        {
            public int? CourseId { get; set; }
            public List<int>? DateIds { get; set; }
        }

        public class ExportRequest
        {
            public string? SearchTerm { get; set; }
            public string? IdRanges { get; set; }
            public List<string>? SelectedNationalIds { get; set; }
            public List<CourseSelection>? SelectedCourses { get; set; }
            public bool AllColumns { get; set; }
            public bool MainInfoOnly { get; set; }
            public bool CustomColumns { get; set; }
        }

                [HttpPost("export")]
        public async Task<IActionResult> ExportData([FromBody] ExportRequest request)
        {
            var query = _context.Persons
                .Include(p => p.Registrations)
                .Include(p => p.Licenses)
                .Include(p => p.Addresses)
                .Include(p => p.Affiliations)
                .Include(p => p.Courses)
                .AsQueryable();

            if (!string.IsNullOrEmpty(request.SearchTerm))
            {
                var s = request.SearchTerm.ToLower();
                query = query.Where(p => 
                    (p.FirstNameTh != null && p.FirstNameTh.ToLower().Contains(s)) ||
                    (p.LastNameTh != null && p.LastNameTh.ToLower().Contains(s)) ||
                    (p.NationId != null && p.NationId.ToLower().Contains(s)) ||
                    (p.PhoneOtp != null && p.PhoneOtp.ToLower().Contains(s)) ||
                    p.Licenses.Any(l => l.LicenseNo != null && l.LicenseNo.ToLower().Contains(s))
                );
            }

            if (request.SelectedNationalIds != null && request.SelectedNationalIds.Count > 0)
            {
                query = query.Where(p => request.SelectedNationalIds.Contains(p.NationId));
            }

            bool hasPersonFilter = !string.IsNullOrEmpty(request.SearchTerm) ||
                                   !string.IsNullOrEmpty(request.IdRanges) ||
                                   (request.SelectedNationalIds != null && request.SelectedNationalIds.Count > 0);

            if (!hasPersonFilter && request.SelectedCourses != null && request.SelectedCourses.Count > 0)
            {
                var coursePredicates = new List<Func<Person, bool>>();
                foreach (var c in request.SelectedCourses)
                {
                    // Course matches if the person has this course, and if dateIds are provided, it must match one of them.
                    bool hasDateFilter = c.DateIds != null && c.DateIds.Count > 0;
                    if (hasDateFilter)
                    {
                        coursePredicates.Add(p => p.Courses.Any(pc => pc.CourseId == c.CourseId && pc.CourseDateId != null && c.DateIds.Contains(pc.CourseDateId.Value)));
                    }
                    else
                    {
                        coursePredicates.Add(p => p.Courses.Any(pc => pc.CourseId == c.CourseId));
                    }
                }
                
                // Fetch to memory first due to complex EF translation for this dynamic OR logic
                // Or we can just evaluate it after fetching. Since we want to limit fetch size if possible, 
                // we'll fetch then filter.
            }

            // Fetch records
            var personsList = await query.ToListAsync();

            // Apply ID ranges filter against Registrations
            if (!string.IsNullOrEmpty(request.IdRanges))
            {
                var ranges = request.IdRanges.Split(',', StringSplitOptions.RemoveEmptyEntries);
                var validRecords = new List<Person>();
                foreach (var person in personsList)
                {
                    bool match = false;
                    foreach (var rangeStr in ranges)
                    {
                        var cleanStr = rangeStr.Replace("{", "").Replace("}", "").Trim();
                        var subParts = cleanStr.Split(new[] { ' ' }, StringSplitOptions.RemoveEmptyEntries); // Handle space-separated like "1 2"
                        
                        foreach (var subPart in subParts)
                        {
                            var parts = subPart.Split('-');
                            if (parts.Length == 2 && int.TryParse(parts[0], out int min) && int.TryParse(parts[1], out int max))
                            {
                                if (person.Registrations.Any(r => r.Id >= min && r.Id <= max)) { match = true; break; }
                            }
                            else if (parts.Length == 1 && int.TryParse(parts[0], out int eq))
                            {
                                if (person.Registrations.Any(r => r.Id == eq)) { match = true; break; }
                            }
                        }
                        if (match) break;
                    }
                    if (match) validRecords.Add(person);
                }
                personsList = validRecords;
            }

            // Apply Course filter
            if (!hasPersonFilter && request.SelectedCourses != null && request.SelectedCourses.Count > 0)
            {
                var validRecords = new List<Person>();
                foreach (var person in personsList)
                {
                    bool match = false;
                    foreach (var c in request.SelectedCourses)
                    {
                        if (person.Courses.Any(pc => pc.CourseId == c.CourseId && (c.DateIds == null || c.DateIds.Count == 0 || c.DateIds.Contains(pc.CourseDateId ?? 0))))
                        {
                            match = true;
                            break;
                        }
                    }
                    if (match) validRecords.Add(person);
                }
                personsList = validRecords;
            }

            // Map results
            var results = new List<object>();

            var provinces = await _context.Provinces.ToDictionaryAsync(p => p.Id, p => p.ProvinceThai);
            var districts = await _context.Districts.ToDictionaryAsync(d => d.Id, d => d.DistrictThai);
            var subdistricts = await _context.SubDistricts.ToDictionaryAsync(s => s.Id, s => s.SubDistrictThai);
            
            var branches = await _context.AgentBranches.ToDictionaryAsync(b => b.Id, b => b.Name);
            var regions = await _context.AgentRegions.ToDictionaryAsync(r => r.Id, r => r.Name);
            
            var religionDict = await _context.Religions.ToDictionaryAsync(r => r.Id, r => r.Name);
            var bloodDict = await _context.BloodTypes.ToDictionaryAsync(b => b.Id, b => b.Name);
            
            var renewOthers = await _context.RenewOthers.ToListAsync();
            var pillars = await _context.RenewPillars.ToDictionaryAsync(p => p.Id, p => p.Name);
            var dates = await _context.RenewDates.ToDictionaryAsync(d => d.Id, d => d.CourseDateDisplay);
            var subjects = await _context.RenewCourses.ToDictionaryAsync(s => s.Id, s => s.Name);

            foreach (var p in personsList)
            {
                var lic = p.Licenses.FirstOrDefault();
                var aff = p.Affiliations.FirstOrDefault();
                var reg = p.Registrations.OrderByDescending(r => r.Id).FirstOrDefault();
                var addrA = p.Addresses.FirstOrDefault(a => a.AddressType == "A") ?? p.Addresses.FirstOrDefault();
                var addrC = p.Addresses.FirstOrDefault(a => a.AddressType == "C") ?? p.Addresses.LastOrDefault();

                string? mergedSubjectsStr = null;
                var tor4Courses = p.Courses.Where(c => c.CourseId == 9 || c.CourseId == 10).ToList();
                if (tor4Courses.Any())
                {
                    var allSelectedSubjects = new List<string>();
                    foreach(var tc in tor4Courses)
                    {
                        if (tc.RenewOtherId.HasValue)
                        {
                            var ro = renewOthers.FirstOrDefault(x => x.Id == tc.RenewOtherId.Value);
                            if (ro != null)
                            {
                                var pName = pillars.ContainsKey(ro.PillarId) ? pillars[ro.PillarId] : "";
                                var dName = dates.ContainsKey(ro.DateId) ? dates[ro.DateId] : "";
                                var sName = subjects.ContainsKey(ro.SubjectId) ? subjects[ro.SubjectId] : "";
                                
                                var formattedStr = $"[Pillar {pName}] [{dName}] : {sName}";
                                if (!allSelectedSubjects.Contains(formattedStr))
                                {
                                    allSelectedSubjects.Add(formattedStr);
                                }
                            }
                        }
                    }
                    if (allSelectedSubjects.Any()) {
                        mergedSubjectsStr = string.Join(" ; ", allSelectedSubjects);
                    }
                }

                results.Add(MapPersonToOutput(p, reg, lic, aff, addrA, addrC, mergedSubjectsStr, provinces, districts, subdistricts, branches, regions, religionDict, bloodDict));
            }

            return Ok(results);
        }

        private object MapPersonToOutput(Person p, PersonRegistration reg, PersonLicense lic, PersonAffiliation aff, PersonAddress addrA, PersonAddress addrC, string? mergedSubjects, Dictionary<int, string> provinces, Dictionary<int, string> districts, Dictionary<int, string> subdistricts, Dictionary<int, string> branches, Dictionary<int, string> regions, Dictionary<int, string> religionDict, Dictionary<int, string> bloodDict)
        {
            var provName = addrA != null && addrA.ProvinceId.HasValue && provinces.ContainsKey(addrA.ProvinceId.Value) ? provinces[addrA.ProvinceId.Value] : "";
            var distName = addrA != null && addrA.DistrictId.HasValue && districts.ContainsKey(addrA.DistrictId.Value) ? districts[addrA.DistrictId.Value] : "";
            var subName = addrA != null && addrA.SubDistrictId.HasValue && subdistricts.ContainsKey(addrA.SubDistrictId.Value) ? subdistricts[addrA.SubDistrictId.Value] : "";
            
            var cProvName = addrC != null && addrC.ProvinceId.HasValue && provinces.ContainsKey(addrC.ProvinceId.Value) ? provinces[addrC.ProvinceId.Value] : "";
            var cDistName = addrC != null && addrC.DistrictId.HasValue && districts.ContainsKey(addrC.DistrictId.Value) ? districts[addrC.DistrictId.Value] : "";
            var cSubName = addrC != null && addrC.SubDistrictId.HasValue && subdistricts.ContainsKey(addrC.SubDistrictId.Value) ? subdistricts[addrC.SubDistrictId.Value] : "";

            var branchName = aff != null && aff.BranchId.HasValue && branches.ContainsKey(aff.BranchId.Value) ? branches[aff.BranchId.Value] : (aff?.BrokerBranch ?? "");
            var regionName = aff != null && aff.RegionId.HasValue && regions.ContainsKey(aff.RegionId.Value) ? regions[aff.RegionId.Value] : "";

            return new
            {
                // Person
                NationalId = p.NationId,
                Title = p.TitleTh == "1" ? "นาย" : p.TitleTh == "2" ? "นาง" : p.TitleTh == "3" ? "นางสาว" : p.TitleTh,
                FirstName = p.FirstNameTh,
                MiddleNameTh = p.MiddleNameTh,
                LastName = p.LastNameTh,
                TitleOldTh = p.TitleOldTh,
                FirstNameOldTh = p.FirstNameOldTh,
                MiddleNameOldTh = p.MiddleNameOldTh,
                LastNameOldTh = p.LastNameOldTh,
                Gender = p.GenderId == 1 ? "ชาย" : (p.GenderId == 2 ? "หญิง" : p.GenderId?.ToString()),
                BirthDate = p.BirthDate?.ToString("yyyy-MM-dd"),
                Religion = p.ReligionId.HasValue && religionDict.ContainsKey(p.ReligionId.Value) ? religionDict[p.ReligionId.Value] : "",
                BloodGroup = p.BloodGroupId.HasValue && bloodDict.ContainsKey(p.BloodGroupId.Value) ? bloodDict[p.BloodGroupId.Value] : "",
                Phone = p.PhoneOtp,
                Email = p.EmailAlt,
                LineId = p.LineId,
                Facebook = p.Facebook,
                Instagram = p.Instagram,
                FoodAllergy = p.FoodAllergy,
                MedicalCondition = p.MedicalCondition,
                EmergencyContactName = p.EmergencyContactName,
                EmergencyContactPhone = p.EmergencyContactPhone,

                // PersonRegistration
                Id = reg?.Id ?? 0,
                CreatedAt = reg?.start_time?.ToString("yyyy-MM-dd HH:mm:ss"),

                // PersonAddress
                Address = addrA != null ? $"{addrA.HouseNo} {addrA.Moo} {addrA.Soi} {addrA.Road} {subName} {distName} {provName} {addrA.Postcode}" : "",
                ContactAddress = addrC != null ? $"{addrC.HouseNo} {addrC.Moo} {addrC.Soi} {addrC.Road} {cSubName} {cDistName} {cProvName} {addrC.Postcode}" : "",

                // PersonLicense
                LicenseNo = lic?.LicenseNo,
                LicenseIssueDate = lic?.LicenseIssueDate?.ToString("yyyy-MM-dd"),
                LicenseExpiryDate = lic?.LicenseExpiryDate?.ToString("yyyy-MM-dd"),
                CourseType = lic?.CourseType,

                // PersonAffiliation
                AgentLevel = (string?)null,
                BrokerLevel = aff?.BrokerType, 
                Region = regionName,
                Branch = branchName,

                // PersonCourse
                MergedSubjects = mergedSubjects
            };
        }
    }
}