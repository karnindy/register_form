using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace backend.Models
{
    [Table("sys_config")]
    public class SysConfig
    {
        [Key]
        [Column("key_name")]
        [MaxLength(100)]
        public string Key { get; set; } = null!;
        
        [Column("value")]
        public string Value { get; set; } = string.Empty;
        
        [Column("description")]
        public string? Description { get; set; }
        
        [Column("group_name")]
        [MaxLength(50)]
        public string Group { get; set; } = "general";
        
        [Column("default_value")]
        public string? DefaultValue { get; set; }
    }
}
