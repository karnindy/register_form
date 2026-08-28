using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace backend.Models
{
    [Table("admin_role_permissions")]
    public class RolePermission
    {
        [Key]
        [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
        public int Id { get; set; }

        [Required]
        public int RoleId { get; set; }

        [ForeignKey("RoleId")]
        public virtual Role? Role { get; set; }

        [Required]
        [MaxLength(50)]
        public string MenuKey { get; set; } = null!;

        public bool CanView { get; set; } = false;

        public bool CanEdit { get; set; } = false;
    }
}
