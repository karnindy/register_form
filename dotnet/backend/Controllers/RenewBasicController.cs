using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;

namespace backend.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class RenewBasicController : ControllerBase
    {
        private readonly AppDbContext _context;

        public RenewBasicController(AppDbContext context)
        {
            _context = context;
        }

        [HttpGet]
        public async Task<IActionResult> GetAll()
        {
            var data = await _context.RenewBasics
                .OrderBy(x => x.CourseName)
                .ToListAsync();

            var curriculums = await _context.CourseCurriculums
                .Include(c => c.SubDetails)
                .Where(c => c.CourseType == "basic")
                .OrderBy(c => c.DisplayOrder)
                .ToListAsync();

            var curriculumsByCourseId = curriculums.GroupBy(c => c.CourseId).ToDictionary(g => g.Key, g => g.ToList());

            var result = data.Select(b => new {
                b.Id,
                b.CourseName,
                b.Status,
                b.DateId,
                b.AgentType,
                Curriculums = curriculumsByCourseId.GetValueOrDefault(b.Id, new List<MstCourseCurriculum>())
            });

            return Ok(result);
        }

        [HttpPost]
        public async Task<IActionResult> Create([FromBody] MstRenewBasic model)
        {
            _context.RenewBasics.Add(model);
            await _context.SaveChangesAsync();
            return Ok(model);
        }

        [HttpPut("{id}")]
        public async Task<IActionResult> Update(int id, [FromBody] MstRenewBasic model)
        {
            var existing = await _context.RenewBasics.FindAsync(id);
            if (existing == null) return NotFound();
            existing.CourseName = model.CourseName;
            existing.AgentType = model.AgentType;
            existing.DateId = model.DateId;
            existing.Status = model.Status;
            
            await _context.SaveChangesAsync();
            return Ok(existing);
        }

        [HttpDelete("{id}")]
        public async Task<IActionResult> Delete(int id)
        {
            var existing = await _context.RenewBasics.FindAsync(id);
            if (existing == null) return NotFound();

            // Soft delete by status, or hard delete? The schema has status active/inactive.
            existing.Status = "inactive";
            await _context.SaveChangesAsync();
            return Ok(new { message = "Deleted successfully" });
        }
    }
}
