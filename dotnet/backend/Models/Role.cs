using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace backend.Models
{
    [Table("admin_roles")]
    public class Role
    {
        [Key]
        [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
        public int Id { get; set; }

        [Required]
        [MaxLength(50)]
        public string RoleCode { get; set; } = null!;

        [Required]
        [MaxLength(100)]
        public string RoleName { get; set; } = null!;

        [MaxLength(255)]
        public string? Description { get; set; }

        public bool IsSystem { get; set; } = false;

        public DateTime CreatedAt { get; set; } = DateTime.UtcNow;

        public DateTime? UpdatedAt { get; set; }

        public virtual ICollection<RolePermission> Permissions { get; set; } = new List<RolePermission>();
    }
}
