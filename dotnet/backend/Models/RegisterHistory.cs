using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace backend.Models
{
    [Table("register_history")]
    public class RegisterHistory
    {
        [Key]
        [Column("id")]
        public int Id { get; set; }

        [Column("register_id")]
        public int RegisterId { get; set; }

        [Column("edited_by_type")]
        public string EditedByType { get; set; } = "applicant";

        [Column("created_by")]
        public string CreatedBy { get; set; } = "user";

        [Column("old_data")]
        public string? OldData { get; set; }

        [Column("new_data")]
        public string? NewData { get; set; }

        [Column("created_at")]
        public DateTime? CreatedAt { get; set; }
    }
}
