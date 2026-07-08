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
        public int ProvinceId { get; set; }
        public string ProvinceThai { get; set; } = null!;
        public string? ProvinceEng { get; set; }
    }

    [Table("mst_districts")]
    public class MstDistrict
    {
        [Key]
        public int DistrictId { get; set; }
        public string DistrictThai { get; set; } = null!;
        public string? DistrictEng { get; set; }
        
        public int ProvinceId { get; set; }
    }

    [Table("mst_sub_districts")]
    public class MstSubDistrict
    {
        [Key]
        public int SubDistrictId { get; set; }
        public string SubDistrictThai { get; set; } = null!;
        public string? SubDistrictEng { get; set; }
        
        public int DistrictId { get; set; }
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
}
