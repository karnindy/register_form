using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using backend.Repositories;
using backend.Models;

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
        public async Task<IActionResult> GetTrainees([FromQuery] int page = 1, [FromQuery] int pageSize = 50)
        {
            // Fetch all from repository (we could add pagination at the repository level later)
            var allTrainees = await _repository.GetAllAsync();
            
            // Map to the simple format the React table expects
            var result = allTrainees.Select(r => new
            {
                Id = r.Id,
                Name = $"{r.FirstNameTh} {r.LastNameTh}",
                IdCard = r.NationalId,
                Course = r.CourseType ?? "ไม่ระบุ",
                Date = r.CreatedAt?.ToString("yyyy-MM-dd") ?? "",
                Status = r.Confirmed == "ยืนยันการสมัคร" ? "ยืนยันแล้ว" : "รอยืนยัน"
            });

            return Ok(result);
        }

        // [Authorize(Roles = "Admin")] // Only full admins can delete
        [HttpDelete("trainees/{id}")]
        public async Task<IActionResult> DeleteTrainee(int id)
        {
            await _repository.DeleteAsync(id);
            return NoContent();
        }
    }
}
