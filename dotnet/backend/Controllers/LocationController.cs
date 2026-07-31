using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;

namespace backend.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class LocationController : ControllerBase
    {
        private readonly AppDbContext _context;

        public LocationController(AppDbContext context)
        {
            _context = context;
        }

        // --- Provinces ---
        [HttpGet("provinces")]
        public async Task<IActionResult> GetProvinces()
        {
            return Ok(await _context.Provinces.OrderBy(p => p.ProvinceThai).ToListAsync());
        }

        [HttpPost("provinces")]
        public async Task<IActionResult> CreateProvince([FromBody] MstProvince dto)
        {
            _context.Provinces.Add(dto);
            await _context.SaveChangesAsync();
            return Ok(new { message = "Created successfully" });
        }

        [HttpPut("provinces/{id}")]
        public async Task<IActionResult> UpdateProvince(int id, [FromBody] MstProvince dto)
        {
            var existing = await _context.Provinces.FindAsync(id);
            if (existing == null) return NotFound();
            
            existing.ProvinceId = dto.ProvinceId;
            existing.ProvinceThai = dto.ProvinceThai;
            existing.ProvinceEng = dto.ProvinceEng;
            
            await _context.SaveChangesAsync();
            return Ok(new { message = "Updated successfully" });
        }

        [HttpDelete("provinces/{id}")]
        public async Task<IActionResult> DeleteProvince(int id)
        {
            var existing = await _context.Provinces.FindAsync(id);
            if (existing != null) {
                _context.Provinces.Remove(existing);
                await _context.SaveChangesAsync();
            }
            return Ok(new { message = "Deleted successfully" });
        }

        // --- Districts ---
        [HttpGet("districts")]
        public async Task<IActionResult> GetDistricts()
        {
            // Returns all for the master data page
            return Ok(await _context.Districts.OrderBy(d => d.DistrictThai).ToListAsync());
        }

        [HttpPost("districts")]
        public async Task<IActionResult> CreateDistrict([FromBody] MstDistrict dto)
        {
            _context.Districts.Add(dto);
            await _context.SaveChangesAsync();
            return Ok(new { message = "Created successfully" });
        }

        [HttpPut("districts/{id}")]
        public async Task<IActionResult> UpdateDistrict(int id, [FromBody] MstDistrict dto)
        {
            var existing = await _context.Districts.FindAsync(id);
            if (existing == null) return NotFound();
            
            existing.DistrictId = dto.DistrictId;
            existing.DistrictThai = dto.DistrictThai;
            existing.DistrictEng = dto.DistrictEng;
            existing.ProvinceId = dto.ProvinceId;
            
            await _context.SaveChangesAsync();
            return Ok(new { message = "Updated successfully" });
        }

        [HttpDelete("districts/{id}")]
        public async Task<IActionResult> DeleteDistrict(int id)
        {
            var existing = await _context.Districts.FindAsync(id);
            if (existing != null) {
                _context.Districts.Remove(existing);
                await _context.SaveChangesAsync();
            }
            return Ok(new { message = "Deleted successfully" });
        }

        // --- SubDistricts ---
        [HttpGet("subdistricts")]
        public async Task<IActionResult> GetSubDistricts([FromQuery] int limit = 100)
        {
            // To prevent large payloads if not needed, optionally limit
            // But for simple CRUD, we can just return all (there are ~7k)
            return Ok(await _context.SubDistricts.OrderBy(s => s.SubDistrictThai).Take(limit).ToListAsync());
        }

        [HttpGet("subdistricts/all")]
        public async Task<IActionResult> GetAllSubDistricts()
        {
            return Ok(await _context.SubDistricts.OrderBy(s => s.SubDistrictThai).ToListAsync());
        }

        [HttpPost("subdistricts")]
        public async Task<IActionResult> CreateSubDistrict([FromBody] MstSubDistrict dto)
        {
            _context.SubDistricts.Add(dto);
            await _context.SaveChangesAsync();
            return Ok(new { message = "Created successfully" });
        }

        [HttpPut("subdistricts/{id}")]
        public async Task<IActionResult> UpdateSubDistrict(int id, [FromBody] MstSubDistrict dto)
        {
            var existing = await _context.SubDistricts.FindAsync(id);
            if (existing == null) return NotFound();
            
            existing.SubDistrictId = dto.SubDistrictId;
            existing.SubDistrictThai = dto.SubDistrictThai;
            existing.SubDistrictEng = dto.SubDistrictEng;
            existing.DistrictId = dto.DistrictId;
            existing.Zipcode = dto.Zipcode;
            
            await _context.SaveChangesAsync();
            return Ok(new { message = "Updated successfully" });
        }

        [HttpDelete("subdistricts/{id}")]
        public async Task<IActionResult> DeleteSubDistrict(int id)
        {
            var existing = await _context.SubDistricts.FindAsync(id);
            if (existing != null) {
                _context.SubDistricts.Remove(existing);
                await _context.SaveChangesAsync();
            }
            return Ok(new { message = "Deleted successfully" });
        }
    }
}
