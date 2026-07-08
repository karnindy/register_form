using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using backend.Data;
using System.Security.Claims;
using Microsoft.IdentityModel.Tokens;
using System.IdentityModel.Tokens.Jwt;
using System.Text;

namespace backend.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class AuthController : ControllerBase
    {
        private readonly AppDbContext _context;
        private readonly IConfiguration _configuration;

        public AuthController(AppDbContext context, IConfiguration configuration)
        {
            _context = context;
            _configuration = configuration;
        }

        public class LoginRequest
        {
            public string Username { get; set; } = null!;
            public string Password { get; set; } = null!;
        }

        [HttpPost("login")]
        public async Task<IActionResult> Login([FromBody] LoginRequest request)
        {
            // Note: In a real system, you would hash the incoming password and compare.
            // For now, this mimics the basic logic, preparing for AD integration.
            var user = await _context.Users.SingleOrDefaultAsync(u => u.Username == request.Username);
            
            if (user == null || user.PasswordHash != request.Password) // Simplified check
            {
                // Fallback to legacy hardcoded logic during migration if needed
                if ((request.Username == "admin" && request.Password == "editor") ||
                    (request.Username == "viewer" && request.Password == "viewer"))
                {
                    user = new Models.User 
                    { 
                        Username = request.Username, 
                        Role = request.Username == "admin" ? "Admin" : "Viewer" 
                    };
                }
                else
                {
                    return Unauthorized(new { message = "รหัสผ่านไม่ถูกต้อง" });
                }
            }

            if (!user.IsActive) return Forbid();

            // Generate JWT Token
            var tokenHandler = new JwtSecurityTokenHandler();
            var key = Encoding.ASCII.GetBytes(_configuration["Jwt:SecretKey"] ?? "default_secret_key_needs_to_be_long_enough_for_hs256");
            var tokenDescriptor = new SecurityTokenDescriptor
            {
                Subject = new ClaimsIdentity(new[]
                {
                    new Claim(ClaimTypes.Name, user.Username),
                    new Claim(ClaimTypes.Role, user.Role)
                }),
                Expires = DateTime.UtcNow.AddDays(1),
                SigningCredentials = new SigningCredentials(new SymmetricSecurityKey(key), SecurityAlgorithms.HmacSha256Signature)
            };
            var token = tokenHandler.CreateToken(tokenDescriptor);
            
            return Ok(new 
            { 
                token = tokenHandler.WriteToken(token),
                role = user.Role
            });
        }
    }
}
