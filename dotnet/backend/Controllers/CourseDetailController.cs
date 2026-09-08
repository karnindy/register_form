using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;

namespace backend.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class CourseDetailController : ControllerBase
    {
        private readonly AppDbContext _context;

        public CourseDetailController(AppDbContext context)
        {
            _context = context;
        }

        [HttpGet]
        public async Task<IActionResult> GetDetails([FromQuery] string? courseType, [FromQuery] int? courseId, [FromQuery] string? agentType, [FromQuery] bool all = false)
        {
            var query = _context.CourseDetails.AsQueryable();

            if (!string.IsNullOrEmpty(courseType))
            {
                query = query.Where(x => x.CourseType == courseType.ToLower());
            }

            if (courseId.HasValue)
            {
                query = query.Where(x => x.CourseId == courseId.Value);
            }

            if (!string.IsNullOrEmpty(agentType))
            {
                var at = agentType.ToLower();
                query = query.Where(x => x.AgentType == at || x.AgentType == "both" || x.AgentType == null);
            }

            if (!all)
            {
                query = query.Where(x => x.Status == "active");
            }

            var data = await query.OrderBy(x => x.DisplayOrder).ThenBy(x => x.Id).ToListAsync();
            return Ok(data);
        }

        [HttpGet("{id}")]
        public async Task<IActionResult> GetById(int id)
        {
            var item = await _context.CourseDetails.FindAsync(id);
            if (item == null) return NotFound();
            return Ok(item);
        }

        [HttpPost]
        public async Task<IActionResult> Create([FromBody] MstCourseDetail model)
        {
            if (string.IsNullOrWhiteSpace(model.CourseType))
            {
                return BadRequest("courseType is required (e.g. 'basic' or 'renew')");
            }

            model.CourseType = model.CourseType.ToLower();
            if (!string.IsNullOrWhiteSpace(model.AgentType))
            {
                model.AgentType = model.AgentType.ToLower();
            }
            _context.CourseDetails.Add(model);
            await _context.SaveChangesAsync();
            return Ok(model);
        }

        [HttpPut("{id}")]
        public async Task<IActionResult> Update(int id, [FromBody] MstCourseDetail model)
        {
            var existing = await _context.CourseDetails.FindAsync(id);
            if (existing == null) return NotFound();

            existing.CourseType = model.CourseType?.ToLower() ?? existing.CourseType;
            existing.CourseId = model.CourseId;
            existing.AgentType = model.AgentType?.ToLower();
            existing.AnnouncementCode = model.AnnouncementCode;
            existing.CourseShortName = model.CourseShortName;
            existing.CurriculumCode = model.CurriculumCode;
            existing.CourseCode = model.CourseCode;
            existing.OicCourseCode = model.OicCourseCode;
            existing.DisplayOrder = model.DisplayOrder;
            existing.Status = model.Status ?? existing.Status;

            await _context.SaveChangesAsync();
            return Ok(existing);
        }

        [HttpDelete("{id}")]
        public async Task<IActionResult> Delete(int id)
        {
            var existing = await _context.CourseDetails.FindAsync(id);
            if (existing == null) return NotFound();

            _context.CourseDetails.Remove(existing);
            await _context.SaveChangesAsync();
            return Ok(new { message = "Deleted successfully" });
        }

        public class BatchSyncDto
        {
            public string CourseType { get; set; } = null!;
            public int CourseId { get; set; }
            public List<MstCourseDetail> Details { get; set; } = new();
        }

        [HttpPost("batch")]
        public async Task<IActionResult> BatchSync([FromBody] BatchSyncDto dto)
        {
            if (string.IsNullOrWhiteSpace(dto.CourseType)) return BadRequest("CourseType is required");
            var type = dto.CourseType.ToLower();

            // Remove existing details for this course
            var existing = await _context.CourseDetails
                .Where(x => x.CourseType == type && x.CourseId == dto.CourseId)
                .ToListAsync();
            _context.CourseDetails.RemoveRange(existing);

            // Add new items
            int order = 1;
            foreach (var item in dto.Details)
            {
                item.Id = 0;
                item.CourseType = type;
                item.CourseId = dto.CourseId;
                item.DisplayOrder = item.DisplayOrder > 0 ? item.DisplayOrder : order++;
                if (string.IsNullOrEmpty(item.Status)) item.Status = "active";
                _context.CourseDetails.Add(item);
            }

            await _context.SaveChangesAsync();
            return Ok(new { message = "Saved batch successfully", count = dto.Details.Count });
        }
    }
}
