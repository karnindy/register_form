using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;

namespace backend.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class ConfigController : ControllerBase
    {
        private readonly AppDbContext _context;

        public ConfigController(AppDbContext context)
        {
            _context = context;
        }

        [HttpGet]
        public async Task<IActionResult> GetAllConfigs()
        {
            var configs = await _context.SysConfigs.ToListAsync();
            return Ok(configs);
        }

        [HttpPut]
        public async Task<IActionResult> UpdateConfigs([FromBody] Dictionary<string, string> updates)
        {
            if (updates == null || updates.Count == 0)
                return BadRequest("No updates provided");

            var keysToUpdate = updates.Keys.ToList();
            var configsToUpdate = await _context.SysConfigs
                                    .Where(c => keysToUpdate.Contains(c.Key))
                                    .ToListAsync();

            foreach (var config in configsToUpdate)
            {
                if (updates.TryGetValue(config.Key, out var newValue))
                {
                    config.Value = newValue;
                }
            }

            // Optional: If a key doesn't exist, you might want to create it, but for now we only update existing.
            
            await _context.SaveChangesAsync();
            return Ok(new { message = "Configuration updated successfully." });
        }
    }
}
