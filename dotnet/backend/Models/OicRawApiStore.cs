using System;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace backend.Models
{
    [Table("oic_raw_api_store")]
    public class OicRawApiStore
    {
        [Key]
        [Column("raw_id")]
        public long RawId { get; set; }

        [Column("id_card_number")]
        public string IdCardNumber { get; set; } = null!;

        [Column("source_system")]
        public string SourceSystem { get; set; } = null!;

        [Column("http_status_code")]
        public int? HttpStatusCode { get; set; }

        [Column("raw_payload")]
        public string RawPayload { get; set; } = null!;

        [Column("received_at")]
        public DateTime ReceivedAt { get; set; }

        [Column("created_at")]
        public DateTime CreatedAt { get; set; }

        [Column("last_successful_log_id")]
        public long? LastSuccessfulLogId { get; set; }

        [Column("is_latest")]
        public bool IsLatest { get; set; } = false;
    }
}
