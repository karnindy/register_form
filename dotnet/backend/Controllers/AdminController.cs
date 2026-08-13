using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using backend.Repositories;
using backend.Models;
using backend.Data;

namespace backend.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    // [Authorize(Roles = "Admin,Viewer")] // Uncomment when JWT is fully set up in Program.cs
    public class AdminController : ControllerBase
    {
        private readonly IRegisterRepository _repository;

        public AdminController(IRegisterRepository repository)
        {
            _repository = repository;
        }

        [HttpGet("trainees")]
        public async Task<IActionResult> GetTrainees(
            [FromServices] AppDbContext context,
            [FromQuery] string? search,
            [FromQuery] string? idRanges,
            [FromQuery] int page = 1,
            [FromQuery] int pageSize = 10)
        {
            var query = context.Persons
                .Include(p => p.Registrations)
                .Include(p => p.Licenses)
                .Include(p => p.Addresses)
                .Include(p => p.Affiliations)
                .Include(p => p.Courses)
                .Include(p => p.RegistrationHistories)
                .AsQueryable();

            if (!string.IsNullOrEmpty(search))
            {
                var s = search.ToLower();
                query = query.Where(p => 
                    (p.FirstNameTh != null && p.FirstNameTh.ToLower().Contains(s)) ||
                    (p.LastNameTh != null && p.LastNameTh.ToLower().Contains(s)) ||
                    (p.NationId != null && p.NationId.Contains(s)) ||
                    (p.PhoneOtp != null && p.PhoneOtp.Contains(s)) ||
                    (p.EmailAlt != null && p.EmailAlt.ToLower().Contains(s)) ||
                    (p.LineId != null && p.LineId.ToLower().Contains(s)) ||
                    
                    (s == "ชาย" && p.GenderId == 1) ||
                    (s == "หญิง" && p.GenderId == 2) ||

                    p.Licenses.Any(l => (l.LicenseNo != null && l.LicenseNo.ToLower().Contains(s)) || (l.CourseType != null && l.CourseType.ToLower().Contains(s))) ||
                    
                    p.Affiliations.Any(a => 
                        (a.BrokerBranch != null && a.BrokerBranch.ToLower().Contains(s)) || 
                        (a.BrokerType != null && a.BrokerType.ToLower().Contains(s))
                    ) ||
                    
                    context.Provinces.Any(pv => p.Addresses.Any(a => a.AddressType == "A" && a.ProvinceId == pv.Id) && pv.ProvinceThai != null && pv.ProvinceThai.ToLower().Contains(s)) ||
                    
                    context.AgentRegions.Any(r => p.Affiliations.Any(a => a.RegionId == r.Id) && r.Name != null && r.Name.ToLower().Contains(s)) ||
                    
                    context.AgentBranches.Any(ab => p.Affiliations.Any(a => a.BranchId == ab.Id) && ab.Name != null && ab.Name.ToLower().Contains(s)) ||

                    context.RenewBasics.Any(rb => p.Courses.Any(c => c.CourseId == rb.Id) && rb.CourseName != null && rb.CourseName.ToLower().Contains(s)) ||
                    
                    context.RenewDates.Any(rd => p.Courses.Any(c => c.CourseDateId == rd.Id) && rd.CourseDateDisplay != null && rd.CourseDateDisplay.ToLower().Contains(s))
                );
            }

            var personsList = await query.ToListAsync();

            if (!string.IsNullOrEmpty(idRanges))
            {
                var ranges = idRanges.Split(',', StringSplitOptions.RemoveEmptyEntries);
                var validRecords = new List<Person>();
                foreach (var person in personsList)
                {
                    bool match = false;
                    foreach (var rangeStr in ranges)
                    {
                        var cleanStr = rangeStr.Replace("{", "").Replace("}", "").Trim();
                        var subParts = cleanStr.Split(new[] { ' ' }, StringSplitOptions.RemoveEmptyEntries);
                        
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

            var total = personsList.Count;
            var pagedData = personsList.OrderByDescending(p => p.NationId)
                .Skip(pageSize > 0 ? (page - 1) * pageSize : 0)
                .Take(pageSize > 0 ? pageSize : Math.Max(1, total))
                .ToList();

            var data = pagedData.Select(p => {
                var latestRegistration = p.Registrations.OrderByDescending(r => r.Id).FirstOrDefault();
                var latestLicense = p.Licenses.OrderByDescending(l => l.Id).FirstOrDefault();
                var latestCourse = p.Courses.OrderByDescending(c => c.PersonCourseId).FirstOrDefault();
                
                var renewCourse = latestCourse?.CourseId != null ? context.RenewBasics.FirstOrDefault(rb => rb.Id == latestCourse.CourseId) : null;
                var renewDate = latestCourse?.CourseDateId != null ? context.RenewDates.FirstOrDefault(rd => rd.Id == latestCourse.CourseDateId) : null;

                return new
                {
                    NationId = p.NationId,
                    Name = $"{p.FirstNameTh} {p.LastNameTh}",
                    IdCard = p.NationId,
                    Course = renewCourse?.CourseName ?? latestLicense?.CourseType ?? "ไม่ระบุ",
                    Date = renewDate?.CourseDateDisplay ?? latestRegistration?.start_time?.ToString("yyyy-MM-dd") ?? "",
                    Status = (latestRegistration?.confirmed ?? false) ? "ยืนยันแล้ว" : "รอยืนยัน",
                    
                    HistoryRegistrations = p.Registrations.OrderByDescending(r => r.Id).Select(r => new { r.Id, r.start_time, r.confirmed }).ToList(),
                    HistoryCourses = p.Courses.OrderByDescending(c => c.PersonCourseId).Select(c => {
                        var rc = c.CourseId != null ? context.RenewBasics.FirstOrDefault(rb => rb.Id == c.CourseId) : null;
                        var rd = c.CourseDateId != null ? context.RenewDates.FirstOrDefault(x => x.Id == c.CourseDateId) : null;
                        return new { c.PersonCourseId, CourseName = rc?.CourseName, DateDisplay = rd?.CourseDateDisplay };
                    }).ToList(),
                    Transactions = p.RegistrationHistories.OrderByDescending(h => h.Id).Select(h => new {
                        Id = h.Id,
                        CreatedAt = h.CreatedAt?.ToString("yyyy-MM-dd HH:mm:ss"),
                        NewData = h.NewData
                    }).ToList()
                };
            });

            return Ok(new { data, total, page, pageSize = pageSize > 0 ? pageSize : total });
        }

        [HttpGet("trainees/{nationId}/full")]
        public async Task<IActionResult> GetTraineeFull(string nationId, [FromServices] AppDbContext context)
        {
            var p = await context.Persons
                .Include(x => x.Registrations)
                .Include(x => x.Licenses)
                .Include(x => x.Addresses)
                .Include(x => x.Affiliations)
                .Include(x => x.Courses)
                .Include(x => x.Trainings)
                .Include(x => x.Others).ThenInclude(o => o.SalesAreas)
                .Include(x => x.Others).ThenInclude(o => o.OtherCompanies)
                .Include(x => x.Others).ThenInclude(o => o.Specialties)
                .FirstOrDefaultAsync(x => x.NationId == nationId);
                
            if (p == null) return NotFound();
            
            return Ok(p);
        }

        [HttpPut("trainees/{nationId}")]
        public async Task<IActionResult> UpdateTraineeFull(string nationId, [FromBody] Person updatedPerson, [FromServices] AppDbContext context)
        {
            var p = await context.Persons
                .Include(x => x.Registrations)
                .Include(x => x.Licenses)
                .Include(x => x.Addresses)
                .Include(x => x.Affiliations)
                .Include(x => x.Courses)
                .Include(x => x.Trainings)
                .Include(x => x.Others).ThenInclude(o => o.SalesAreas)
                .Include(x => x.Others).ThenInclude(o => o.OtherCompanies)
                .Include(x => x.Others).ThenInclude(o => o.Specialties)
                .FirstOrDefaultAsync(x => x.NationId == nationId);
                
            if (p == null) return NotFound();
            
            // Update scalar fields of Person
            context.Entry(p).CurrentValues.SetValues(updatedPerson);
            
            // For a complete full update, we would also update collections. 
            // This requires mapping logic. For simplicity, we can trust EF Core's update if passed correctly,
            // or we manually update the fields we care about. 
            // In EF Core, updating collections manually is safer:
            
            // Registrations
            foreach(var r in updatedPerson.Registrations) {
                var existing = p.Registrations.FirstOrDefault(e => e.Id == r.Id);
                if (existing != null) context.Entry(existing).CurrentValues.SetValues(r);
            }
            
            // Licenses
            foreach(var l in updatedPerson.Licenses) {
                var existing = p.Licenses.FirstOrDefault(e => e.Id == l.Id);
                if (existing != null) context.Entry(existing).CurrentValues.SetValues(l);
            }

            // Addresses
            foreach(var a in updatedPerson.Addresses) {
                var existing = p.Addresses.FirstOrDefault(e => e.Id == a.Id);
                if (existing != null) context.Entry(existing).CurrentValues.SetValues(a);
            }
            
            // Affiliations
            foreach(var a in updatedPerson.Affiliations) {
                var existing = p.Affiliations.FirstOrDefault(e => e.Id == a.Id);
                if (existing != null) context.Entry(existing).CurrentValues.SetValues(a);
            }

            // Registrations
            foreach(var reg in updatedPerson.Registrations) {
                var existing = p.Registrations.FirstOrDefault(e => e.Id == reg.Id);
                if (existing != null) {
                    existing.DeductionPrivilege = reg.DeductionPrivilege;
                    existing.MasterDegreeStatus = reg.MasterDegreeStatus;
                }
            }
            
            // Courses
            context.PersonCourses.RemoveRange(p.Courses);
            if (updatedPerson.Courses != null) {
                foreach(var c in updatedPerson.Courses) {
                    c.PersonCourseId = 0; // Ensure EF treats as new
                    c.NationId = nationId;
                    p.Courses.Add(c);
                }
            }

            // Trainings (5y)
            context.PersonTrainings5y.RemoveRange(p.Trainings);
            if (updatedPerson.Trainings != null) {
                foreach(var t in updatedPerson.Trainings) {
                    t.Id = 0;
                    t.NationId = nationId;
                    p.Trainings.Add(t);
                }
            }

            // Others
            foreach(var other in p.Others) {
                context.PersonOtherSalesAreas.RemoveRange(other.SalesAreas);
                context.PersonOtherCompanies.RemoveRange(other.OtherCompanies);
                context.PersonOtherSpecialties.RemoveRange(other.Specialties);
            }
            context.PersonOthers.RemoveRange(p.Others);

            if (updatedPerson.Others != null) {
                foreach(var other in updatedPerson.Others) {
                    other.id = 0;
                    other.NationId = nationId;
                    
                    if (other.SalesAreas != null) {
                        foreach(var sa in other.SalesAreas) { sa.Id = 0; }
                    }
                    if (other.OtherCompanies != null) {
                        foreach(var oc in other.OtherCompanies) { oc.Id = 0; }
                    }
                    if (other.Specialties != null) {
                        foreach(var sp in other.Specialties) { sp.Id = 0; }
                    }
                    
                    p.Others.Add(other);
                }
            }

            await context.SaveChangesAsync();
            return Ok(p);
        }

        // [Authorize(Roles = "Admin")] // Only full admins can delete
        [HttpDelete("trainees/{id}")]
        public async Task<IActionResult> DeleteTrainee(int id)
        {
            await _repository.DeleteAsync(id);
            return NoContent();
        }

        [HttpGet("trainees/{nationId}/documents")]
        public async Task<IActionResult> GetTraineeDocuments(string nationId, [FromServices] AppDbContext context)
        {
            var cleanNationId = nationId.Replace("-", "");
            var documents = context.PersonDocuments
                .Where(d => d.NationId == cleanNationId)
                .Select(d => new
                {
                    d.Id,
                    d.DocumentType,
                    // Make sure the path is URL friendly
                    FilePath = "/" + d.FilePath.Replace("\\", "/"),
                    d.UploadedAt
                })
                .ToList();

            return Ok(documents);
        }
    }
}
