using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;

namespace backend.Controllers
{
    [ApiController]
    [Route("api/admin/oic")]
    public class AdminOicController : ControllerBase
    {
        private readonly AppDbContext _context;

        public AdminOicController(AppDbContext context)
        {
            _context = context;
        }

        // --- Field Mapping ---

        [HttpGet("field-mappings")]
        public async Task<IActionResult> GetFieldMappings()
        {
            var data = await _context.OicFieldMappings.OrderBy(x => x.MappingId).ToListAsync();
            return Ok(data);
        }

        [HttpPost("field-mappings")]
        public async Task<IActionResult> CreateFieldMapping([FromBody] OicFieldMapping dto)
        {
            if (dto == null) return BadRequest();
            _context.OicFieldMappings.Add(dto);
            await _context.SaveChangesAsync();
            return Ok(dto);
        }

        [HttpPut("field-mappings/{id}")]
        public async Task<IActionResult> UpdateFieldMapping(int id, [FromBody] OicFieldMapping dto)
        {
            if (dto == null || id != dto.MappingId) return BadRequest();
            
            var existing = await _context.OicFieldMappings.FindAsync(id);
            if (existing == null) return NotFound();

            existing.SourceJsonPath = dto.SourceJsonPath;
            existing.TargetFieldName = dto.TargetFieldName;
            existing.DataType = dto.DataType;
            existing.Description = dto.Description;
            existing.IsActive = dto.IsActive;

            await _context.SaveChangesAsync();
            return Ok(existing);
        }

        [HttpDelete("field-mappings/{id}")]
        public async Task<IActionResult> DeleteFieldMapping(int id)
        {
            var existing = await _context.OicFieldMappings.FindAsync(id);
            if (existing == null) return NotFound();

            _context.OicFieldMappings.Remove(existing);
            await _context.SaveChangesAsync();
            return Ok(new { message = "Deleted successfully" });
        }

        // --- Value Mapping ---

        [HttpGet("value-mappings")]
        public async Task<IActionResult> GetValueMappings()
        {
            var data = await _context.OicValueMappings.OrderBy(x => x.ValMappingId).ToListAsync();
            return Ok(data);
        }

        [HttpPost("value-mappings")]
        public async Task<IActionResult> CreateValueMapping([FromBody] OicValueMapping dto)
        {
            if (dto == null) return BadRequest();
            _context.OicValueMappings.Add(dto);
            await _context.SaveChangesAsync();
            return Ok(dto);
        }

        [HttpPut("value-mappings/{id}")]
        public async Task<IActionResult> UpdateValueMapping(int id, [FromBody] OicValueMapping dto)
        {
            if (dto == null || id != dto.ValMappingId) return BadRequest();
            
            var existing = await _context.OicValueMappings.FindAsync(id);
            if (existing == null) return NotFound();

            existing.TargetFieldName = dto.TargetFieldName;
            existing.SourceWord = dto.SourceWord;
            existing.TargetWord = dto.TargetWord;
            existing.Description = dto.Description;
            existing.IsActive = dto.IsActive;

            await _context.SaveChangesAsync();
            return Ok(existing);
        }

        [HttpDelete("value-mappings/{id}")]
        public async Task<IActionResult> DeleteValueMapping(int id)
        {
            var existing = await _context.OicValueMappings.FindAsync(id);
            if (existing == null) return NotFound();

            _context.OicValueMappings.Remove(existing);
            await _context.SaveChangesAsync();
            return Ok(new { message = "Deleted successfully" });
        }
    }
}
