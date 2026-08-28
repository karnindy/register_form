using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace backend.Models
{
    [Table("admin_users")]
    public class User
    {
        [Key]
        [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
        public int Id { get; set; }

        [Required]
        [MaxLength(150)]
        public string Username { get; set; } = null!;

        [MaxLength(150)]
        public string? Email { get; set; }

        [Required]
        [MaxLength(255)]
        public string PasswordHash { get; set; } = null!;

        [Required]
        [MaxLength(20)]
        public string Role { get; set; } = "Applicant"; // e.g., "Superadmin", "Admin", "Viewer", "Applicant"

        [MaxLength(13)]
        public string? NationId { get; set; }

        [MaxLength(200)]
        public string? FullName { get; set; }

        [MaxLength(20)]
        public string? Phone { get; set; }

        public bool IsActive { get; set; } = true;

        public bool IsEmailVerified { get; set; } = false;

        [MaxLength(100)]
        public string? PasswordResetToken { get; set; }

        public DateTime? ResetTokenExpires { get; set; }

        public DateTime? LastLoginAt { get; set; }

        public DateTime CreatedAt { get; set; } = DateTime.UtcNow;

        public DateTime? UpdatedAt { get; set; }
    }
}
