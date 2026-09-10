using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;

namespace backend.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class MasterDataController : ControllerBase
    {
        private readonly AppDbContext _context;

        public MasterDataController(AppDbContext context)
        {
            _context = context;
        }

        [HttpGet("provinces")]
        public async Task<IActionResult> GetProvinces()
        {
            var data = await _context.Provinces.OrderBy(p => p.ProvinceThai).ToListAsync();
            return Ok(data);
        }

        [HttpGet("districts/{provinceId}")]
        public async Task<IActionResult> GetDistricts(int provinceId)
        {
            var data = await _context.Districts
                .Where(d => d.ProvinceId == provinceId)
                .OrderBy(d => d.DistrictThai)
                .ToListAsync();
            return Ok(data);
        }

        [HttpGet("subdistricts/{districtId}")]
        public async Task<IActionResult> GetSubDistricts(int districtId)
        {
            var data = await _context.SubDistricts
                .Where(s => s.DistrictId == districtId)
                .OrderBy(s => s.SubDistrictThai)
                .ToListAsync();
            return Ok(data);
        }

        [HttpGet("districts")]
        public async Task<IActionResult> GetAllDistricts()
        {
            var data = await _context.Districts.ToListAsync();
            return Ok(data);
        }

        [HttpGet("subdistricts")]
        public async Task<IActionResult> GetAllSubDistricts()
        {
            var data = await _context.SubDistricts.ToListAsync();
            return Ok(data);
        }

        [HttpGet("agent-branches")]
        public async Task<IActionResult> GetAgentBranches()
        {
            var data = await _context.AgentBranches
                .Join(_context.AgentRegions,
                    b => b.RegionId,
                    r => r.Id,
                    (b, r) => new
                    {
                        branchId = b.Id,
                        branchName = b.Name,
                        regionId = r.Id,
                        regionName = r.Name
                    })
                .OrderBy(x => x.branchName)
                .ToListAsync();
                
            return Ok(data);
        }

        [HttpGet("renew-courses")]
        [HttpGet("courses")]
        public async Task<IActionResult> GetRenewCourses([FromQuery] string? agentType)
        {
            var query = _context.RenewBasics.Where(c => c.Status == "active");

            if (!string.IsNullOrEmpty(agentType))
            {
                query = query.Where(c => c.AgentType == agentType || c.AgentType == null);
            }

            var data = await query
                .GroupJoin(_context.RenewDates.Where(d => d.Status == "active"), b => b.DateId, d => d.Id, (b, dates) => new { b, dates })
                .SelectMany(x => x.dates.DefaultIfEmpty(), (x, d) => new {
                    id = x.b.Id,
                    courseName = x.b.CourseName,
                    agentType = x.b.AgentType,
                    dateId = x.b.DateId,
                    dateDisplay = d != null ? d.CourseDateDisplay : null
                })
                .OrderBy(c => c.id)
                .ToListAsync();

            return Ok(data);
        }

        [HttpGet("renew-other-courses")]
        public async Task<IActionResult> GetRenewOtherCourses()
        {
            var query = from o in _context.RenewOthers
                        join p in _context.RenewPillars on o.PillarId equals p.Id
                        join d in _context.RenewDates on o.DateId equals d.Id
                        join c in _context.RenewCourses on o.SubjectId equals c.Id
                        where o.Status == "active"
                           && p.Status == "active"
                           && d.Status == "active"
                           && c.Status == "active"
                        orderby o.DisplayOrder ascending
                        select new {
                            id = o.Id,
                            subjectId = c.Id,
                            subjectName = c.Name,
                            pillarId = p.Id,
                            pillarName = p.Name,
                            dateId = d.Id,
                            dateDisplay = d.CourseDateDisplay,
                            displayName = $"[{p.Name}][{d.CourseDateDisplay}] : {c.Name}"
                        };

            var data = await query.ToListAsync();
            return Ok(data);
        }

        // Generic Master Data DTO
        public class MasterDataDto
        {
            public int Id { get; set; }
            public int? DefaultPillarId { get; set; }
            public string? DefaultPillarName { get; set; }
            public string Name { get; set; } = string.Empty;
            public string Status { get; set; } = "active";
            public int DisplayOrder { get; set; }
            public List<MstCourseCurriculum> Curriculums { get; set; } = new();
        }

        [HttpGet("{type}")]
        public async Task<IActionResult> GetGenericMasterData(string type, [FromQuery] bool all = false)
        {
            var queryStatus = all ? null : "active";
            switch (type.ToLower())
            {
                case "blood":
                case "bloods":
                    return Ok(await _context.BloodTypes
                        .Where(b => queryStatus == null || b.Status == queryStatus)
                        .OrderBy(b => b.DisplayOrder)
                        .Select(b => new MasterDataDto { Id = b.Id, Name = b.Name, Status = b.Status, DisplayOrder = b.DisplayOrder })
                        .ToListAsync());
                case "gender":
                case "genders":
                    return Ok(await _context.Genders
                        .Where(g => queryStatus == null || g.Status == queryStatus)
                        .OrderBy(g => g.DisplayOrder)
                        .Select(g => new MasterDataDto { Id = g.Id, Name = g.Name, Status = g.Status, DisplayOrder = g.DisplayOrder })
                        .ToListAsync());
                case "religion":
                case "religions":
                    return Ok(await _context.Religions
                        .Where(r => queryStatus == null || r.Status == queryStatus)
                        .OrderBy(r => r.DisplayOrder)
                        .Select(r => new MasterDataDto { Id = r.Id, Name = r.Name, Status = r.Status, DisplayOrder = r.DisplayOrder })
                        .ToListAsync());
                case "title":
                case "titles":
                    return Ok(await _context.Titles
                        .Where(t => queryStatus == null || t.Status == queryStatus)
                        .OrderBy(t => t.DisplayOrder)
                        .Select(t => new MasterDataDto { Id = t.Id, Name = t.Name, Status = t.Status, DisplayOrder = t.DisplayOrder })
                        .ToListAsync());
                case "territory":
                case "territories":
                    return Ok(await _context.Territories
                        .Where(t => queryStatus == null || t.Status == queryStatus)
                        .OrderBy(t => t.DisplayOrder)
                        .Select(t => new MasterDataDto { Id = t.Id, Name = t.Name, Status = t.Status, DisplayOrder = t.DisplayOrder })
                        .ToListAsync());
                case "expertise":
                case "expertises":
                    return Ok(await _context.Expertises
                        .Where(e => queryStatus == null || e.Status == queryStatus)
                        .OrderBy(e => e.DisplayOrder)
                        .Select(e => new MasterDataDto { Id = e.Id, Name = e.Name, Status = e.Status, DisplayOrder = e.DisplayOrder })
                        .ToListAsync());
                case "company":
                case "companies":
                    return Ok(await _context.Companies
                        .Where(c => queryStatus == null || c.Status == queryStatus)
                        .OrderBy(c => c.DisplayOrder)
                        .Select(c => new MasterDataDto { Id = c.Id, Name = c.Name, Status = c.Status, DisplayOrder = c.DisplayOrder })
                        .ToListAsync());
                case "renewcourse":
                case "renewcourses":
                    var renewCourses = await _context.RenewCourses
                        .Where(c => queryStatus == null || c.Status == queryStatus)
                        .OrderBy(c => c.DisplayOrder)
                        .Select(c => new MasterDataDto { 
                            Id = c.Id, 
                            Name = c.Name, 
                            DefaultPillarId = c.DefaultPillarId,
                            Status = c.Status, 
                            DisplayOrder = c.DisplayOrder 
                        })
                        .ToListAsync();

                    var pillarDict = await _context.RenewPillars.ToDictionaryAsync(p => p.Id, p => p.Name);

                    var curriculums = await _context.CourseCurriculums
                        .Include(c => c.SubDetails)
                        .Where(c => c.CourseType == "renew" && (queryStatus == null || c.Status == queryStatus))
                        .OrderBy(c => c.DisplayOrder)
                        .ToListAsync();
                    var curriculumsByCourseId = curriculums.GroupBy(c => c.CourseId).ToDictionary(g => g.Key, g => g.ToList());

                    foreach (var c in renewCourses)
                    {
                        if (c.DefaultPillarId.HasValue && pillarDict.TryGetValue(c.DefaultPillarId.Value, out var pName))
                        {
                            c.DefaultPillarName = pName;
                        }
                        if (curriculumsByCourseId.TryGetValue(c.Id, out var cList))
                        {
                            c.Curriculums = cList;
                        }
                    }
                    return Ok(renewCourses);
                case "pillar":
                case "pillars":
                    return Ok(await _context.RenewPillars
                        .Where(p => queryStatus == null || p.Status == queryStatus)
                        .OrderBy(p => p.DisplayOrder)
                        .Select(p => new MasterDataDto { Id = p.Id, Name = p.Name, Status = p.Status, DisplayOrder = p.DisplayOrder })
                        .ToListAsync());
                default:
                    return NotFound(new { message = $"Master data type '{type}' not found or not supported for generic API." });
            }
        }

        [HttpPost("{type}")]
        public async Task<IActionResult> CreateGenericMasterData(string type, [FromBody] MasterDataDto dto)
        {
            switch (type.ToLower())
            {
                case "blood":
                case "bloods":
                    var b = new Models.MstBlood { Name = dto.Name, Status = dto.Status, DisplayOrder = dto.DisplayOrder };
                    _context.BloodTypes.Add(b); break;
                case "gender":
                case "genders":
                    var g = new Models.MstGender { Name = dto.Name, Status = dto.Status, DisplayOrder = dto.DisplayOrder };
                    _context.Genders.Add(g); break;
                case "religion":
                case "religions":
                    var r = new Models.MstReligion { Name = dto.Name, Status = dto.Status, DisplayOrder = dto.DisplayOrder };
                    _context.Religions.Add(r); break;
                case "title":
                case "titles":
                    var t = new Models.MstTitle { Name = dto.Name, Status = dto.Status, DisplayOrder = dto.DisplayOrder };
                    _context.Titles.Add(t); break;
                case "renewcourse":
                case "renewcourses":
                    var c = new Models.MstRenewCourse { 
                        Name = dto.Name, 
                        DefaultPillarId = dto.DefaultPillarId,
                        Status = dto.Status, 
                        DisplayOrder = dto.DisplayOrder 
                    };
                    _context.RenewCourses.Add(c); break;
                case "territory":
                case "territories":
                    var territory = new Models.MstTerritory { Name = dto.Name, Status = dto.Status, DisplayOrder = dto.DisplayOrder };
                    _context.Territories.Add(territory); break;
                case "expertise":
                case "expertises":
                    var expertise = new Models.MstExpertise { Name = dto.Name, Status = dto.Status, DisplayOrder = dto.DisplayOrder };
                    _context.Expertises.Add(expertise); break;
                case "company":
                case "companies":
                    var company = new Models.MstCompany { Name = dto.Name, Status = dto.Status, DisplayOrder = dto.DisplayOrder };
                    _context.Companies.Add(company); break;
                case "pillar":
                case "pillars":
                    var pillar = new Models.MstRenewPillar { Name = dto.Name, Status = dto.Status, DisplayOrder = dto.DisplayOrder };
                    _context.RenewPillars.Add(pillar); break;
                default:
                    return BadRequest("Invalid type");
            }
            await _context.SaveChangesAsync();
            return Ok(new { message = "Created successfully" });
        }

        [HttpPut("{type}/{id}")]
        public async Task<IActionResult> UpdateGenericMasterData(string type, int id, [FromBody] MasterDataDto dto)
        {
            switch (type.ToLower())
            {
                case "blood":
                case "bloods":
                    var b = await _context.BloodTypes.FindAsync(id);
                    if (b == null) return NotFound();
                    b.Name = dto.Name; b.Status = dto.Status; b.DisplayOrder = dto.DisplayOrder;
                    break;
                case "gender":
                case "genders":
                    var g = await _context.Genders.FindAsync(id);
                    if (g == null) return NotFound();
                    g.Name = dto.Name; g.Status = dto.Status; g.DisplayOrder = dto.DisplayOrder;
                    break;
                case "religion":
                case "religions":
                    var r = await _context.Religions.FindAsync(id);
                    if (r == null) return NotFound();
                    r.Name = dto.Name; r.Status = dto.Status; r.DisplayOrder = dto.DisplayOrder;
                    break;
                case "title":
                case "titles":
                    var t = await _context.Titles.FindAsync(id);
                    if (t == null) return NotFound();
                    t.Name = dto.Name; t.Status = dto.Status; t.DisplayOrder = dto.DisplayOrder;
                    break;
                case "renewcourse":
                case "renewcourses":
                    var c = await _context.RenewCourses.FindAsync(id);
                    if (c == null) return NotFound();
                    c.Name = dto.Name; 
                    c.DefaultPillarId = dto.DefaultPillarId;
                    c.Status = dto.Status; 
                    c.DisplayOrder = dto.DisplayOrder;
                    break;
                case "territory":
                case "territories":
                    var territory = await _context.Territories.FindAsync(id);
                    if (territory == null) return NotFound();
                    territory.Name = dto.Name; territory.Status = dto.Status; territory.DisplayOrder = dto.DisplayOrder;
                    break;
                case "expertise":
                case "expertises":
                    var expertise = await _context.Expertises.FindAsync(id);
                    if (expertise == null) return NotFound();
                    expertise.Name = dto.Name; expertise.Status = dto.Status; expertise.DisplayOrder = dto.DisplayOrder;
                    break;
                case "company":
                case "companies":
                    var company = await _context.Companies.FindAsync(id);
                    if (company == null) return NotFound();
                    company.Name = dto.Name; company.Status = dto.Status; company.DisplayOrder = dto.DisplayOrder;
                    break;
                case "pillar":
                case "pillars":
                    var pillar = await _context.RenewPillars.FindAsync(id);
                    if (pillar == null) return NotFound();
                    pillar.Name = dto.Name; pillar.Status = dto.Status; pillar.DisplayOrder = dto.DisplayOrder;
                    break;
                default:
                    return BadRequest("Invalid type");
            }
            await _context.SaveChangesAsync();
            return Ok(new { message = "Updated successfully" });
        }

        [HttpDelete("{type}/{id}")]
        public async Task<IActionResult> DeleteGenericMasterData(string type, int id)
        {
            // Instead of hard delete, we set status = inactive
            switch (type.ToLower())
            {
                case "blood":
                case "bloods":
                    var b = await _context.BloodTypes.FindAsync(id);
                    if (b != null) { b.Status = "inactive"; } break;
                case "gender":
                case "genders":
                    var g = await _context.Genders.FindAsync(id);
                    if (g != null) { g.Status = "inactive"; } break;
                case "religion":
                case "religions":
                    var r = await _context.Religions.FindAsync(id);
                    if (r != null) { r.Status = "inactive"; } break;
                case "title":
                case "titles":
                    var t = await _context.Titles.FindAsync(id);
                    if (t != null) { t.Status = "inactive"; } break;
                case "renewcourse":
                case "renewcourses":
                    var c = await _context.RenewCourses.FindAsync(id);
                    if (c != null) { c.Status = "inactive"; } break;
                case "territory":
                case "territories":
                    var territory = await _context.Territories.FindAsync(id);
                    if (territory != null) { territory.Status = "inactive"; } break;
                case "expertise":
                case "expertises":
                    var expertise = await _context.Expertises.FindAsync(id);
                    if (expertise != null) { expertise.Status = "inactive"; } break;
                case "company":
                case "companies":
                    var company = await _context.Companies.FindAsync(id);
                    if (company != null) { company.Status = "inactive"; } break;
                case "pillar":
                case "pillars":
                    var pillar = await _context.RenewPillars.FindAsync(id);
                    if (pillar != null) { pillar.Status = "inactive"; } break;
                default:
                    return BadRequest("Invalid type");
            }
            await _context.SaveChangesAsync();
            return Ok(new { message = "Deleted (soft) successfully" });
        }
    }
}
