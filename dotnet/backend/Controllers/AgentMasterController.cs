using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;

namespace backend.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class AgentMasterController : ControllerBase
    {
        private readonly AppDbContext _context;

        public AgentMasterController(AppDbContext context)
        {
            _context = context;
        }

        // --- REGIONS ---
        [HttpGet("regions")]
        public async Task<IActionResult> GetRegions()
        {
            var data = await _context.AgentRegions.OrderBy(r => r.Name).ToListAsync();
            return Ok(data);
        }

        [HttpPost("regions")]
        public async Task<IActionResult> CreateRegion([FromBody] MstAgentRegion region)
        {
            _context.AgentRegions.Add(region);
            await _context.SaveChangesAsync();
            return Ok(region);
        }

        [HttpPut("regions/{id}")]
        public async Task<IActionResult> UpdateRegion(int id, [FromBody] MstAgentRegion region)
        {
            var existing = await _context.AgentRegions.FindAsync(id);
            if (existing == null) return NotFound();
            
            existing.Name = region.Name;
            await _context.SaveChangesAsync();
            return Ok(existing);
        }

        [HttpDelete("regions/{id}")]
        public async Task<IActionResult> DeleteRegion(int id)
        {
            var existing = await _context.AgentRegions.FindAsync(id);
            if (existing == null) return NotFound();

            // Check if used in branches
            if (await _context.AgentBranches.AnyAsync(b => b.RegionId == id))
            {
                return BadRequest("Cannot delete region because it is being used by branches.");
            }

            _context.AgentRegions.Remove(existing);
            await _context.SaveChangesAsync();
            return Ok(new { message = "Deleted successfully" });
        }

        // --- BRANCHES ---
        [HttpGet("branches")]
        public async Task<IActionResult> GetBranches()
        {
            var data = await _context.AgentBranches.OrderBy(b => b.Name).ToListAsync();
            return Ok(data);
        }

        [HttpPost("branches")]
        public async Task<IActionResult> CreateBranch([FromBody] MstAgentBranch branch)
        {
            _context.AgentBranches.Add(branch);
            await _context.SaveChangesAsync();
            return Ok(branch);
        }

        [HttpPut("branches/{id}")]
        public async Task<IActionResult> UpdateBranch(int id, [FromBody] MstAgentBranch branch)
        {
            var existing = await _context.AgentBranches.FindAsync(id);
            if (existing == null) return NotFound();
            
            existing.Name = branch.Name;
            existing.RegionId = branch.RegionId;
            await _context.SaveChangesAsync();
            return Ok(existing);
        }

        [HttpDelete("branches/{id}")]
        public async Task<IActionResult> DeleteBranch(int id)
        {
            var existing = await _context.AgentBranches.FindAsync(id);
            if (existing == null) return NotFound();

            _context.AgentBranches.Remove(existing);
            await _context.SaveChangesAsync();
            return Ok(new { message = "Deleted successfully" });
        }
    }
}
