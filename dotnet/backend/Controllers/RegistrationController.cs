using Microsoft.AspNetCore.Mvc;
using backend.Models;
using backend.Repositories;

namespace backend.Controllers
{
    /// <summary>
    /// API สำหรับจัดการข้อมูลการลงทะเบียนอบรม (Registration Form)
    /// </summary>
    [Route("api/[controller]")]
    [ApiController]
    public class RegistrationController : ControllerBase
    {
        private readonly IRegisterRepository _repository;

        public RegistrationController(IRegisterRepository repository)
        {
            _repository = repository;
        }

        /// <summary>
        /// ดึงข้อมูลการลงทะเบียนตาม ID
        /// </summary>
        /// <param name="id">ID ของผู้ลงทะเบียน</param>
        /// <returns>ข้อมูลผู้ลงทะเบียน</returns>
        [HttpGet("{id}")]
        public async Task<IActionResult> Get(int id)
        {
            var register = await _repository.GetByIdAsync(id);
            if (register == null) return NotFound();
            return Ok(register);
        }

        /// <summary>
        /// บันทึกข้อมูลการลงทะเบียนใหม่ (Submit Form)
        /// </summary>
        /// <param name="register">ข้อมูลจากหน้าฟอร์ม React</param>
        /// <returns>สถานะการบันทึก และ ID ที่เพิ่งสร้าง</returns>
        [HttpPost]
        public async Task<IActionResult> Create([FromBody] Register register)
        {
            var id = await _repository.CreateAsync(register);
            return CreatedAtAction(nameof(Get), new { id = id }, register);
        }

        /// <summary>
        /// อัปเดตข้อมูลการลงทะเบียนเดิม
        /// </summary>
        /// <param name="id">ID ของผู้ลงทะเบียน</param>
        /// <param name="register">ข้อมูลที่ต้องการแก้ไข</param>
        /// <returns>NoContent เมื่อสำเร็จ</returns>
        [HttpPut("{id}")]
        public async Task<IActionResult> Update(int id, [FromBody] Register register)
        {
            if (id != register.Id) return BadRequest();
            await _repository.UpdateAsync(register);
            return NoContent();
        }
    }
}
