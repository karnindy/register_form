using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using backend.Data;

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
    }
}
