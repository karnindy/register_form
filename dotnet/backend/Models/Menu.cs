using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace backend.Models
{
    [Table("admin_menus")]
    public class Menu
    {
        [Key]
        [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
        public int Id { get; set; }

        [Required]
        [MaxLength(50)]
        public string MenuKey { get; set; } = null!;

        [Required]
        [MaxLength(100)]
        public string MenuName { get; set; } = null!;

        [Required]
        [MaxLength(50)]
        public string Category { get; set; } = "Admin";

        [MaxLength(50)]
        public string? Icon { get; set; }

        [MaxLength(100)]
        public string? Path { get; set; }

        public int SortOrder { get; set; } = 0;
    }
}
