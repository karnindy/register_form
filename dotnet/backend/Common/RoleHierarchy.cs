using System.IdentityModel.Tokens.Jwt;
using System.Security.Claims;
using Microsoft.AspNetCore.Http;

namespace backend.Common
{
    public static class RoleHierarchy
    {
        /// <summary>
        /// ดึงบทบาท (Role) ของผู้เรียก API จาก HttpContext.User หรือแกะจาก Authorization Bearer Token Header
        /// </summary>
        public static string? GetCallerRole(HttpContext httpContext)
        {
            if (httpContext == null) return null;

            // 1. Try standard claims
            var roleClaim = httpContext.User?.FindFirst(ClaimTypes.Role)?.Value 
                         ?? httpContext.User?.FindFirst("role")?.Value;
            if (!string.IsNullOrWhiteSpace(roleClaim)) return roleClaim;

            // 2. Fallback: Parse Authorization Bearer header
            var authHeader = httpContext.Request.Headers["Authorization"].FirstOrDefault();
            if (!string.IsNullOrWhiteSpace(authHeader) && authHeader.StartsWith("Bearer ", StringComparison.OrdinalIgnoreCase))
            {
                var tokenStr = authHeader.Substring("Bearer ".Length).Trim();
                try
                {
                    var handler = new JwtSecurityTokenHandler();
                    if (handler.CanReadToken(tokenStr))
                    {
                        var jwt = handler.ReadJwtToken(tokenStr);
                        return jwt.Claims.FirstOrDefault(c => c.Type == ClaimTypes.Role || c.Type == "role")?.Value;
                    }
                }
                catch { }
            }

            return null;
        }

        public static int GetRoleLevel(string? roleCode)
        {
            return (roleCode?.ToLower()) switch
            {
                "superadmin" => 100,
                "admin" => 50,
                "viewer" => 20,
                "applicant" => 10,
                _ => 30 // Custom created roles default to level 30 (below Admin)
            };
        }

        /// <summary>
        /// ผู้ดำเนินการ (Caller) สามารถจัดการเป้าหมาย (Target) ได้ก็ต่อเมื่อมีระดับสิทธิ์ "สูงกว่าอย่างเคร่งครัด" เท่านั้น
        /// Role ตัวเองไม่สามารถกำหนดสิทธิ์ หรือจัดการบัญชีของ Role ตัวเอง หรือ Role ที่สูงกว่าได้
        /// </summary>
        public static bool CanManageRole(string? callerRole, string? targetRole)
        {
            if (string.IsNullOrWhiteSpace(callerRole)) return false;

            int callerLevel = GetRoleLevel(callerRole);
            int targetLevel = GetRoleLevel(targetRole);

            return callerLevel > targetLevel;
        }
    }
}
