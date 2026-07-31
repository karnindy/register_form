using System;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace backend.Models
{
    [Table("oic_value_mapping")]
    public class OicValueMapping
    {
        [Key]
        [Column("val_mapping_id")]
        public int ValMappingId { get; set; }

        [Column("target_field_name")]
        public string TargetFieldName { get; set; } = null!;

        [Column("source_word")]
        public string SourceWord { get; set; } = null!;

        [Column("target_word")]
        public string TargetWord { get; set; } = null!;

        [Column("description")]
        public string? Description { get; set; }

        [Column("is_active")]
        public bool IsActive { get; set; }
    }
}
