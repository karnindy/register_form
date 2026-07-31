using System;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace backend.Models
{
    [Table("oic_field_mapping")]
    public class OicFieldMapping
    {
        [Key]
        [Column("mapping_id")]
        public int MappingId { get; set; }

        [Column("source_json_path")]
        public string SourceJsonPath { get; set; } = null!;

        [Column("target_field_name")]
        public string TargetFieldName { get; set; } = null!;

        [Column("data_type")]
        public string DataType { get; set; } = null!;

        [Column("description")]
        public string? Description { get; set; }

        [Column("is_active")]
        public bool IsActive { get; set; }
    }
}
