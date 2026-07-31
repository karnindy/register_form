using System;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace backend.Models
{
    [Table("oic_api_request_log")]
    public class OicApiRequestLog
    {
        [Key]
        [Column("log_id")]
        public long LogId { get; set; }

        [Column("id_card_number")]
        public string IdCardNumber { get; set; } = null!;

        [Column("endpoint_url")]
        public string? EndpointUrl { get; set; }

        [Column("http_method")]
        public string HttpMethod { get; set; } = "GET";

        [Column("http_status_code")]
        public int? HttpStatusCode { get; set; }

        [Column("response_body")]
        public string? ResponseBody { get; set; }

        [Column("error_message")]
        public string? ErrorMessage { get; set; }

        [Column("execution_time_ms")]
        public int? ExecutionTimeMs { get; set; }

        [Column("created_at")]
        public DateTime CreatedAt { get; set; } = DateTime.UtcNow;
    }
}
