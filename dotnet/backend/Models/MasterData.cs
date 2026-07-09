using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace backend.Models
{
    // Generic abstraction for master data tables that just have an ID and a Name/Display text.
    // This supports the user's request to "design for 3-4 future hardcoded tables to be moved to DB".
    public abstract class BaseMasterData
    {
        [Key]
        [Column("id")]
        [DatabaseGenerated(DatabaseGeneratedOption.Identity)]
        public int Id { get; set; }
        
        [Column("name")]
        public string Name { get; set; } = null!;

        [Column("status")]
        [MaxLength(20)]
        public string Status { get; set; } = "active"; // active, inactive
        
        [Column("display_order")]
        public int DisplayOrder { get; set; } = 0;
    }

    [Table("mst_provinces")]
    public class MstProvince
    {
        [Key]
        [Column("id")]
        public int Id { get; set; }
        
        [Column("province_id")]
        public int ProvinceId { get; set; }
        
        [Column("province_thai")]
        public string ProvinceThai { get; set; } = null!;
        
        [Column("province_english")]
        public string? ProvinceEng { get; set; }
    }

    [Table("mst_districts")]
    public class MstDistrict
    {
        [Key]
        [Column("id")]
        public int Id { get; set; }
        
        [Column("district_id")]
        public int DistrictId { get; set; }
        
        [Column("district_thai")]
        public string DistrictThai { get; set; } = null!;
        
        [Column("district_english")]
        public string? DistrictEng { get; set; }
        
        [Column("province_id")]
        public int ProvinceId { get; set; }
    }

    [Table("mst_sub_districts")]
    public class MstSubDistrict
    {
        [Key]
        [Column("id")]
        public int Id { get; set; }
        
        [Column("sub_district_id")]
        public int SubDistrictId { get; set; }
        
        [Column("sub_district_thai")]
        public string SubDistrictThai { get; set; } = null!;
        
        [Column("sub_district_english")]
        public string? SubDistrictEng { get; set; }
        
        [Column("district_id")]
        public int DistrictId { get; set; }
        
        [Column("postal_code")]
        public string? Zipcode { get; set; }
    }

    [Table("mst_blood")]
    public class MstBlood : BaseMasterData
    {
    }

    [Table("mst_gender")]
    public class MstGender : BaseMasterData
    {
    }

    [Table("mst_religion")]
    public class MstReligion : BaseMasterData
    {
    }

    [Table("mst_titles")]
    public class MstTitle : BaseMasterData
    {
    }

    [Table("mst_renew_course")]
    public class MstRenewCourse : BaseMasterData
    {
    }

    [Table("mst_renew_dates")]
    public class MstRenewDate : BaseMasterData
    {
        [Column("course_date_display")]
        public string CourseDateDisplay { get; set; } = null!;
        
        [Column("course_date")]
        public DateTime CourseDate { get; set; }
    }

    [Table("mst_agent_regions")]
    public class MstAgentRegion
    {
        [Key]
        [Column("id")]
        public int Id { get; set; }
        
        [Column("name")]
        public string Name { get; set; } = null!;
    }

    [Table("mst_agent_branches")]
    public class MstAgentBranch
    {
        [Key]
        [Column("id")]
        public int Id { get; set; }
        
        [Column("name")]
        public string Name { get; set; } = null!;
        
        [Column("region_id")]
        public int RegionId { get; set; }
    }

    [Table("mst_renew_basic")]
    public class MstRenewBasic
    {
        [Key]
        [Column("id")]
        public int Id { get; set; }

        [Column("course_name")]
        public string CourseName { get; set; } = null!;

        [Column("status")]
        [MaxLength(20)]
        public string Status { get; set; } = "active";

        [Column("date_id")]
        public int? DateId { get; set; }

        [Column("agent_type")]
        public string? AgentType { get; set; }
    }

    [Table("mst_renew_pillars")]
    public class MstRenewPillar : BaseMasterData
    {
    }

    [Table("mst_renew_other")]
    public class MstRenewOther
    {
        [Key]
        [Column("id")]
        public int Id { get; set; }

        [Column("pillar_id")]
        public int PillarId { get; set; }

        [Column("date_id")]
        public int DateId { get; set; }

        [Column("subject_id")]
        public int SubjectId { get; set; }

        [Column("status")]
        [MaxLength(20)]
        public string Status { get; set; } = "active";

        [Column("display_order")]
        public int DisplayOrder { get; set; } = 0;
    }
}
