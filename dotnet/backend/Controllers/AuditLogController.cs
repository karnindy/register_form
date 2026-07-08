using Microsoft.AspNetCore.Mvc;
using backend.Repositories;
using Microsoft.AspNetCore.Authorization;

namespace backend.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    // [Authorize] // Uncomment when JWT is fully integrated
    public class AuditLogController : ControllerBase
    {
        private readonly IRegisterHistoryRepository _repository;

        public AuditLogController(IRegisterHistoryRepository repository)
        {
            _repository = repository;
        }

        [HttpGet]
        public async Task<IActionResult> GetAll()
        {
            var logs = await _repository.GetAllAsync();
            return Ok(logs);
        }

        [HttpGet("register/{registerId}")]
        public async Task<IActionResult> GetByRegisterId(int registerId)
        {
            var logs = await _repository.GetByRegisterIdAsync(registerId);
            return Ok(logs);
        }
    }
}
