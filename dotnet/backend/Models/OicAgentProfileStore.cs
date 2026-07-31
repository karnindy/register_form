using System;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace backend.Models
{
    [Table("oic_agent_profile_store")]
    public class OicAgentProfileStore
    {
        [Key]
        [Column("profile_id")]
        public long ProfileId { get; set; }

        [Column("id_card_number")]
        public string IdCardNumber { get; set; } = null!;

        [Column("source_system")]
        public string? SourceSystem { get; set; }

        [Column("verification_status")]
        public string? VerificationStatus { get; set; }

        [Column("http_status_code")]
        public int? HttpStatusCode { get; set; }

        [Column("profile_payload")]
        public string ProfilePayload { get; set; } = null!;

        [Column("last_verified_at")]
        public DateTime LastVerifiedAt { get; set; }

        [Column("created_at")]
        public DateTime CreatedAt { get; set; }

        [Column("updated_at")]
        public DateTime UpdatedAt { get; set; }

        [Column("last_successful_log_id")]
        public long? LastSuccessfulLogId { get; set; }

        [Column("is_latest")]
        public bool IsLatest { get; set; } = false;
    }
}
