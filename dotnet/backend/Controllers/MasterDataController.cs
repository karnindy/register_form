using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using backend.Data;

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

        // Generic Master Data DTO
        public class MasterDataDto
        {
            public int Id { get; set; }
            public string Name { get; set; } = string.Empty;
            public string Status { get; set; } = "active";
            public int DisplayOrder { get; set; }
        }

        [HttpGet("{type}")]
        public async Task<IActionResult> GetGenericMasterData(string type, [FromQuery] bool all = false)
        {
            var queryStatus = all ? null : "active";
            switch (type.ToLower())
            {
                case "blood":
                    return Ok(await _context.BloodTypes
                        .Where(b => queryStatus == null || b.Status == queryStatus)
                        .OrderBy(b => b.DisplayOrder)
                        .Select(b => new MasterDataDto { Id = b.Id, Name = b.Name, Status = b.Status, DisplayOrder = b.DisplayOrder })
                        .ToListAsync());
                case "gender":
                    return Ok(await _context.Genders
                        .Where(g => queryStatus == null || g.Status == queryStatus)
                        .OrderBy(g => g.DisplayOrder)
                        .Select(g => new MasterDataDto { Id = g.Id, Name = g.Name, Status = g.Status, DisplayOrder = g.DisplayOrder })
                        .ToListAsync());
                case "religion":
                    return Ok(await _context.Religions
                        .Where(r => queryStatus == null || r.Status == queryStatus)
                        .OrderBy(r => r.DisplayOrder)
                        .Select(r => new MasterDataDto { Id = r.Id, Name = r.Name, Status = r.Status, DisplayOrder = r.DisplayOrder })
                        .ToListAsync());
                case "titles":
                    return Ok(await _context.Titles
                        .Where(t => queryStatus == null || t.Status == queryStatus)
                        .OrderBy(t => t.DisplayOrder)
                        .Select(t => new MasterDataDto { Id = t.Id, Name = t.Name, Status = t.Status, DisplayOrder = t.DisplayOrder })
                        .ToListAsync());
                case "renewcourse":
                    return Ok(await _context.RenewCourses
                        .Where(c => queryStatus == null || c.Status == queryStatus)
                        .OrderBy(c => c.DisplayOrder)
                        .Select(c => new MasterDataDto { Id = c.Id, Name = c.Name, Status = c.Status, DisplayOrder = c.DisplayOrder })
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
                    var b = new Models.MstBlood { Name = dto.Name, Status = dto.Status, DisplayOrder = dto.DisplayOrder };
                    _context.BloodTypes.Add(b); break;
                case "gender":
                    var g = new Models.MstGender { Name = dto.Name, Status = dto.Status, DisplayOrder = dto.DisplayOrder };
                    _context.Genders.Add(g); break;
                case "religion":
                    var r = new Models.MstReligion { Name = dto.Name, Status = dto.Status, DisplayOrder = dto.DisplayOrder };
                    _context.Religions.Add(r); break;
                case "titles":
                    var t = new Models.MstTitle { Name = dto.Name, Status = dto.Status, DisplayOrder = dto.DisplayOrder };
                    _context.Titles.Add(t); break;
                case "renewcourse":
                    var c = new Models.MstRenewCourse { Name = dto.Name, Status = dto.Status, DisplayOrder = dto.DisplayOrder };
                    _context.RenewCourses.Add(c); break;
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
                    var b = await _context.BloodTypes.FindAsync(id);
                    if (b == null) return NotFound();
                    b.Name = dto.Name; b.Status = dto.Status; b.DisplayOrder = dto.DisplayOrder;
                    break;
                case "gender":
                    var g = await _context.Genders.FindAsync(id);
                    if (g == null) return NotFound();
                    g.Name = dto.Name; g.Status = dto.Status; g.DisplayOrder = dto.DisplayOrder;
                    break;
                case "religion":
                    var r = await _context.Religions.FindAsync(id);
                    if (r == null) return NotFound();
                    r.Name = dto.Name; r.Status = dto.Status; r.DisplayOrder = dto.DisplayOrder;
                    break;
                case "titles":
                    var t = await _context.Titles.FindAsync(id);
                    if (t == null) return NotFound();
                    t.Name = dto.Name; t.Status = dto.Status; t.DisplayOrder = dto.DisplayOrder;
                    break;
                case "renewcourse":
                    var c = await _context.RenewCourses.FindAsync(id);
                    if (c == null) return NotFound();
                    c.Name = dto.Name; c.Status = dto.Status; c.DisplayOrder = dto.DisplayOrder;
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
                    var b = await _context.BloodTypes.FindAsync(id);
                    if (b != null) { b.Status = "inactive"; } break;
                case "gender":
                    var g = await _context.Genders.FindAsync(id);
                    if (g != null) { g.Status = "inactive"; } break;
                case "religion":
                    var r = await _context.Religions.FindAsync(id);
                    if (r != null) { r.Status = "inactive"; } break;
                case "titles":
                    var t = await _context.Titles.FindAsync(id);
                    if (t != null) { t.Status = "inactive"; } break;
                case "renewcourse":
                    var c = await _context.RenewCourses.FindAsync(id);
                    if (c != null) { c.Status = "inactive"; } break;
                default:
                    return BadRequest("Invalid type");
            }
            await _context.SaveChangesAsync();
            return Ok(new { message = "Deleted (soft) successfully" });
        }
    }
}
