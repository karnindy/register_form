using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;
using System.Security.Claims;
using Microsoft.IdentityModel.Tokens;
using System.IdentityModel.Tokens.Jwt;
using System.Text;
using System.Text.RegularExpressions;
using Microsoft.AspNetCore.Authorization;

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
            public string Username { get; set; } = null!; // Can be Username or Email
            public string Password { get; set; } = null!;
        }

        public class RegisterRequest
        {
            public string Email { get; set; } = null!;
            public string Password { get; set; } = null!;
            public string NationId { get; set; } = null!;
            public string FullName { get; set; } = null!;
            public string? Phone { get; set; }
        }

        public class ForgotPasswordRequest
        {
            public string Email { get; set; } = null!;
        }

        public class ResetPasswordRequest
        {
            public string Email { get; set; } = null!;
            public string Token { get; set; } = null!;
            public string NewPassword { get; set; } = null!;
        }

        [HttpPost("login")]
        public async Task<IActionResult> Login([FromBody] LoginRequest request)
        {
            if (string.IsNullOrWhiteSpace(request.Username) || string.IsNullOrWhiteSpace(request.Password))
            {
                return BadRequest(new { message = "กรุณากรอกชื่อผู้ใช้/อีเมล และรหัสผ่าน" });
            }

            var trimmedInput = request.Username.Trim();
            
            // Search by Username OR Email (case-insensitive)
            var user = await _context.Users.SingleOrDefaultAsync(u => 
                u.Username.ToLower() == trimmedInput.ToLower() || 
                (u.Email != null && u.Email.ToLower() == trimmedInput.ToLower()));
            
            if (user == null || user.PasswordHash != request.Password)
            {
                // Fallback for legacy test accounts if not in DB
                if ((trimmedInput == "admin" && request.Password == "editor") ||
                    (trimmedInput == "viewer" && request.Password == "viewer") ||
                    (trimmedInput == "superadmin" && request.Password == "password123"))
                {
                    var fallbackRole = trimmedInput == "superadmin" ? "Superadmin" : (trimmedInput == "admin" ? "Admin" : "Viewer");
                    user = new Models.User 
                    { 
                        Id = 999,
                        Username = trimmedInput, 
                        FullName = trimmedInput,
                        Role = fallbackRole 
                    };
                }
                else
                {
                    return Unauthorized(new { message = "อีเมล/ชื่อผู้ใช้งาน หรือรหัสผ่านไม่ถูกต้อง" });
                }
            }

            if (!user.IsActive) 
            {
                return StatusCode(403, new { message = "บัญชีนี้ถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ" });
            }

            // Update LastLoginAt if existing user
            if (user.Id != 999)
            {
                user.LastLoginAt = DateTime.UtcNow;
                await _context.SaveChangesAsync();
            }

            // Generate JWT Token
            var tokenHandler = new JwtSecurityTokenHandler();
            var key = Encoding.ASCII.GetBytes(_configuration["Jwt:SecretKey"] ?? "default_secret_key_needs_to_be_long_enough_for_hs256");
            
            var claims = new List<Claim>
            {
                new Claim(ClaimTypes.NameIdentifier, user.Id.ToString()),
                new Claim(ClaimTypes.Name, user.Username),
                new Claim(ClaimTypes.Role, user.Role)
            };

            if (!string.IsNullOrEmpty(user.Email)) claims.Add(new Claim(ClaimTypes.Email, user.Email));
            if (!string.IsNullOrEmpty(user.NationId)) claims.Add(new Claim("NationId", user.NationId));
            if (!string.IsNullOrEmpty(user.FullName)) claims.Add(new Claim("FullName", user.FullName));

            var tokenDescriptor = new SecurityTokenDescriptor
            {
                Subject = new ClaimsIdentity(claims),
                Expires = DateTime.UtcNow.AddDays(7),
                SigningCredentials = new SigningCredentials(new SymmetricSecurityKey(key), SecurityAlgorithms.HmacSha256Signature)
            };
            var token = tokenHandler.CreateToken(tokenDescriptor);

            // Fetch dynamic permissions for this role
            var roleEntity = await _context.Roles
                .Include(r => r.Permissions)
                .SingleOrDefaultAsync(r => r.RoleCode.ToLower() == user.Role.ToLower());

            var permissions = (roleEntity?.Permissions ?? Enumerable.Empty<RolePermission>())
                .Select(p => new
                {
                    menuKey = p.MenuKey,
                    canView = p.CanView,
                    canEdit = p.CanEdit
                }).ToList();
            
            return Ok(new 
            { 
                token = tokenHandler.WriteToken(token),
                user = new
                {
                    id = user.Id,
                    username = user.Username,
                    email = user.Email,
                    role = user.Role,
                    nationId = user.NationId,
                    fullName = user.FullName,
                    phone = user.Phone,
                    permissions = permissions
                }
            });
        }

        [HttpPost("register")]
        public async Task<IActionResult> Register([FromBody] RegisterRequest request)
        {
            if (string.IsNullOrWhiteSpace(request.Email) || string.IsNullOrWhiteSpace(request.Password) || string.IsNullOrWhiteSpace(request.NationId))
            {
                return BadRequest(new { message = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน (อีเมล, รหัสผ่าน, เลขบัตรประชาชน)" });
            }

            // Validate Thai National ID Checksum (Modulo 11)
            var cleanNationId = Regex.Replace(request.NationId, @"\D", "");
            if (cleanNationId.Length != 13 || !ValidateThaiNationalId(cleanNationId))
            {
                return BadRequest(new { message = "เลขประจำตัวประชาชน 13 หลักไม่ถูกต้องตามหลักการคำนวณ" });
            }

            var cleanEmail = request.Email.Trim().ToLower();

            // Check if Email already exists
            var existingUser = await _context.Users.AnyAsync(u => u.Username.ToLower() == cleanEmail || (u.Email != null && u.Email.ToLower() == cleanEmail));
            if (existingUser)
            {
                return BadRequest(new { message = "อีเมลนี้ถูกใช้งานในระบบแล้ว กรุณาเข้าสู่ระบบหรือใช้อีเมลอื่น" });
            }

            // Check if NationId already registered as an applicant
            var existingNationId = await _context.Users.AnyAsync(u => u.NationId == cleanNationId && u.Role == "Applicant");
            if (existingNationId)
            {
                return BadRequest(new { message = "เลขประจำตัวประชาชนนี้มีบัญชีผู้ใช้งานในระบบแล้ว กรุณาเข้าสู่ระบบ" });
            }

            var newUser = new User
            {
                Username = cleanEmail,
                Email = cleanEmail,
                PasswordHash = request.Password, // Can use BCrypt in production
                Role = "Applicant",
                NationId = cleanNationId,
                FullName = request.FullName?.Trim() ?? cleanEmail,
                Phone = request.Phone?.Trim(),
                IsActive = true,
                IsEmailVerified = true,
                CreatedAt = DateTime.UtcNow
            };

            _context.Users.Add(newUser);
            await _context.SaveChangesAsync();

            // Auto-login: Generate JWT Token
            var tokenHandler = new JwtSecurityTokenHandler();
            var key = Encoding.ASCII.GetBytes(_configuration["Jwt:SecretKey"] ?? "default_secret_key_needs_to_be_long_enough_for_hs256");
            var claims = new List<Claim>
            {
                new Claim(ClaimTypes.NameIdentifier, newUser.Id.ToString()),
                new Claim(ClaimTypes.Name, newUser.Username),
                new Claim(ClaimTypes.Role, newUser.Role),
                new Claim(ClaimTypes.Email, newUser.Email ?? ""),
                new Claim("NationId", newUser.NationId ?? ""),
                new Claim("FullName", newUser.FullName ?? "")
            };

            var tokenDescriptor = new SecurityTokenDescriptor
            {
                Subject = new ClaimsIdentity(claims),
                Expires = DateTime.UtcNow.AddDays(7),
                SigningCredentials = new SigningCredentials(new SymmetricSecurityKey(key), SecurityAlgorithms.HmacSha256Signature)
            };
            var token = tokenHandler.CreateToken(tokenDescriptor);

            return Created("", new
            {
                message = "สมัครสมาชิกสำเร็จ",
                token = tokenHandler.WriteToken(token),
                user = new
                {
                    id = newUser.Id,
                    username = newUser.Username,
                    email = newUser.Email,
                    role = newUser.Role,
                    nationId = newUser.NationId,
                    fullName = newUser.FullName,
                    phone = newUser.Phone
                }
            });
        }

        [HttpGet("me")]
        public async Task<IActionResult> GetCurrentUser()
        {
            var authHeader = Request.Headers["Authorization"].FirstOrDefault();
            if (string.IsNullOrEmpty(authHeader) || !authHeader.StartsWith("Bearer "))
            {
                return Unauthorized(new { message = "ไม่มี Token ยืนยันตัวตน" });
            }

            var tokenStr = authHeader.Substring("Bearer ".Length).Trim();
            try
            {
                var tokenHandler = new JwtSecurityTokenHandler();
                var key = Encoding.ASCII.GetBytes(_configuration["Jwt:SecretKey"] ?? "default_secret_key_needs_to_be_long_enough_for_hs256");
                tokenHandler.ValidateToken(tokenStr, new TokenValidationParameters
                {
                    ValidateIssuerSigningKey = true,
                    IssuerSigningKey = new SymmetricSecurityKey(key),
                    ValidateIssuer = false,
                    ValidateAudience = false,
                    ClockSkew = TimeSpan.Zero
                }, out SecurityToken validatedToken);

                var jwtToken = (JwtSecurityToken)validatedToken;
                var userIdClaim = jwtToken.Claims.FirstOrDefault(x => x.Type == ClaimTypes.NameIdentifier)?.Value;
                var usernameClaim = jwtToken.Claims.FirstOrDefault(x => x.Type == ClaimTypes.Name)?.Value;

                if (string.IsNullOrEmpty(userIdClaim) && string.IsNullOrEmpty(usernameClaim))
                {
                    return Unauthorized(new { message = "Token ไม่ถูกต้อง" });
                }

                User? user = null;
                if (int.TryParse(userIdClaim, out int userId))
                {
                    user = await _context.Users.FindAsync(userId);
                }
                
                if (user == null && !string.IsNullOrEmpty(usernameClaim))
                {
                    user = await _context.Users.SingleOrDefaultAsync(u => u.Username == usernameClaim);
                }

                if (user == null)
                {
                    return NotFound(new { message = "ไม่พบข้อมูลผู้ใช้งาน" });
                }

                // Fetch dynamic permissions for this role
                var roleEntity = await _context.Roles
                    .Include(r => r.Permissions)
                    .SingleOrDefaultAsync(r => r.RoleCode.ToLower() == user.Role.ToLower());

                var permissions = (roleEntity?.Permissions ?? Enumerable.Empty<RolePermission>())
                    .Select(p => new
                    {
                        menuKey = p.MenuKey,
                        canView = p.CanView,
                        canEdit = p.CanEdit
                    }).ToList();

                return Ok(new
                {
                    id = user.Id,
                    username = user.Username,
                    email = user.Email,
                    role = user.Role,
                    nationId = user.NationId,
                    fullName = user.FullName,
                    phone = user.Phone,
                    permissions = permissions
                });
            }
            catch (Exception ex)
            {
                return Unauthorized(new { message = "Token หมดอายุหรือไม่ถูกต้อง: " + ex.Message });
            }
        }

        [HttpPost("forgot-password")]
        public async Task<IActionResult> ForgotPassword([FromBody] ForgotPasswordRequest request)
        {
            if (string.IsNullOrWhiteSpace(request.Email))
            {
                return BadRequest(new { message = "กรุณากรอกอีเมล" });
            }

            var user = await _context.Users.SingleOrDefaultAsync(u => u.Email != null && u.Email.ToLower() == request.Email.Trim().ToLower());
            if (user == null)
            {
                // Return success to avoid email enumeration
                return Ok(new { message = "หากอีเมลนี้มีอยู่ในระบบ ระบบจะส่งคำแนะนำการตั้งรหัสผ่านใหม่ไปให้ท่าน" });
            }

            user.PasswordResetToken = Guid.NewGuid().ToString("N").Substring(0, 8).ToUpper();
            user.ResetTokenExpires = DateTime.UtcNow.AddHours(2);
            await _context.SaveChangesAsync();

            return Ok(new 
            { 
                message = "หากอีเมลนี้มีอยู่ในระบบ ระบบจะส่งคำแนะนำการตั้งรหัสผ่านใหม่ไปให้ท่าน",
                mockResetToken = user.PasswordResetToken // Provided for easy dev testing
            });
        }

        [HttpPost("reset-password")]
        public async Task<IActionResult> ResetPassword([FromBody] ResetPasswordRequest request)
        {
            if (string.IsNullOrWhiteSpace(request.Email) || string.IsNullOrWhiteSpace(request.Token) || string.IsNullOrWhiteSpace(request.NewPassword))
            {
                return BadRequest(new { message = "กรุณากรอกข้อมูลให้ครบถ้วน" });
            }

            var user = await _context.Users.SingleOrDefaultAsync(u => 
                u.Email != null && u.Email.ToLower() == request.Email.Trim().ToLower() &&
                u.PasswordResetToken == request.Token.Trim() &&
                u.ResetTokenExpires > DateTime.UtcNow);

            if (user == null)
            {
                return BadRequest(new { message = "รหัสยืนยันไม่ถูกต้องหรือหมดอายุการใช้งานแล้ว" });
            }

            user.PasswordHash = request.NewPassword;
            user.PasswordResetToken = null;
            user.ResetTokenExpires = null;
            user.UpdatedAt = DateTime.UtcNow;
            await _context.SaveChangesAsync();

            return Ok(new { message = "ตั้งรหัสผ่านใหม่สำเร็จแล้ว ท่านสามารถเข้าสู่ระบบได้ทันที" });
        }

        private static bool ValidateThaiNationalId(string id)
        {
            if (id.Length != 13 || !long.TryParse(id, out _)) return false;
            int sum = 0;
            for (int i = 0; i < 12; i++)
            {
                sum += (id[i] - '0') * (13 - i);
            }
            int checkDigit = (11 - (sum % 11)) % 10;
            return checkDigit == (id[12] - '0');
        }
    }
}

