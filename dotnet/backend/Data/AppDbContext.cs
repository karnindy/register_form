using Microsoft.EntityFrameworkCore;
using backend.Models;

namespace backend.Data
{
    public class AppDbContext : DbContext
    {
        public AppDbContext(DbContextOptions<AppDbContext> options) : base(options)
        {
        }

        public DbSet<Register> Registers { get; set; }
        public DbSet<RegisterHistory> RegisterHistories { get; set; }
        public DbSet<User> Users { get; set; }
        public DbSet<Role> Roles { get; set; }
        public DbSet<Menu> Menus { get; set; }
        public DbSet<RolePermission> RolePermissions { get; set; }
        public DbSet<SysConfig> SysConfigs { get; set; }
        public DbSet<OicAgentProfileStore> OicAgentProfileStores { get; set; }
        public DbSet<OicRawApiStore> OicRawApiStores { get; set; }
        public DbSet<OicFieldMapping> OicFieldMappings { get; set; }
        public DbSet<OicValueMapping> OicValueMappings { get; set; }
        public DbSet<OicApiRequestLog> OicApiRequestLogs { get; set; }

        // New Person Schema
        public DbSet<Person> Persons { get; set; }
        public DbSet<PersonRegistration> PersonRegistrations { get; set; }
        public DbSet<PersonAddress> PersonAddresses { get; set; }
        public DbSet<PersonLicense> PersonLicenses { get; set; }
        public DbSet<PersonAffiliation> PersonAffiliations { get; set; }
        public DbSet<PersonCourse> PersonCourses { get; set; }
        public DbSet<PersonTraining5y> PersonTrainings5y { get; set; }
        public DbSet<PersonOther> PersonOthers { get; set; }
        public DbSet<PersonOtherSalesArea> PersonOtherSalesAreas { get; set; }
        public DbSet<PersonOtherCompanies> PersonOtherCompanies { get; set; }
        public DbSet<PersonOtherSpecialty> PersonOtherSpecialties { get; set; }
        public DbSet<PersonDocument> PersonDocuments { get; set; }
        public DbSet<RegistrationHistoryModel> RegistrationHistoriesNew { get; set; }
        
        // Master Data
        public DbSet<MstProvince> Provinces { get; set; }
        public DbSet<MstDistrict> Districts { get; set; }
        public DbSet<MstSubDistrict> SubDistricts { get; set; }
        public DbSet<MstBlood> BloodTypes { get; set; }
        public DbSet<MstGender> Genders { get; set; }
        public DbSet<MstReligion> Religions { get; set; }
        public DbSet<MstTitle> Titles { get; set; }
        public DbSet<MstTerritory> Territories { get; set; }
        public DbSet<MstExpertise> Expertises { get; set; }
        public DbSet<MstCompany> Companies { get; set; }
        public DbSet<MstRenewCourse> RenewCourses { get; set; }
        public DbSet<MstRenewDate> RenewDates { get; set; }
        public DbSet<MstAgentRegion> AgentRegions { get; set; }
        public DbSet<MstAgentBranch> AgentBranches { get; set; }
        public DbSet<MstRenewBasic> RenewBasics { get; set; }
        public DbSet<MstRenewPillar> RenewPillars { get; set; }
        public DbSet<MstRenewOther> RenewOthers { get; set; }

        protected override void OnModelCreating(ModelBuilder modelBuilder)
        {
            base.OnModelCreating(modelBuilder);
            
            // Seed Tab 6 Master Data
            int i = 1;
            modelBuilder.Entity<MstTerritory>().HasData(
                new[] { "ภาคกลาง", "ภาคเหนือ", "ภาคตะวันออกเฉียงเหนือ", "ภาคตะวันออก", "ภาคตะวันตก", "ภาคใต้" }
                .Select(name => new MstTerritory { Id = i, Name = name, DisplayOrder = i++ }).ToArray()
            );

            i = 1;
            modelBuilder.Entity<MstExpertise>().HasData(
                new[] { "ประกันรถยนต์", "ประกันอัคคีภัย/ทรัพย์สิน", "ประกันอุบัติเหตุ", "ประกันสุขภาพ", "ประกันเดินทาง/ทางทะเล",
                "ประกันการก่อสร้าง/วิศวกรรม", "ประกันภัยความรับผิดของกรรมการ/ผู้บริหาร", "ประกันภัยความรับผิด",
                "ประกันภัยด้านการเงิน/ค้ำประกัน", "ประกันภัยด้านสิทธิบัตร", "ประกันภัยทางทะเล/ขนส่ง", "ประกันอื่นๆ" }
                .Select(name => new MstExpertise { Id = i, Name = name, DisplayOrder = i++ }).ToArray()
            );

            i = 1;
            modelBuilder.Entity<MstCompany>().HasData(
                new[] { "บ. กรุงเทพประกันภัย", "บ. กรุงเทพประกันสุขภาพ", "บ. กรุงไทยพานิชประกันภัย", "บ. กลางคุ้มครองผู้ประสบภัยจากรถ",
                "บ. ชับบ์สามัคคีประกันภัย", "บ. โตเกียวมารีนประกันภัย", "บ. ทิพยประกันภัย", "บ. เทเวศประกันภัย",
                "บ. ไทยไพบูลย์ประกันภัย", "บ. ไทยวิวัฒน์ประกันภัย", "บ. ไทยศรีประกันภัย", "บ. ไทยเศรษฐกิจประกันภัย",
                "บ. นวกิจประกันภัย", "บ. บางกอกสหประกันภัย", "บ. ประกันภัยไทยวิวัฒน์", "บ. เมืองไทยประกันภัย",
                "บ. สินมั่นคงประกันภัย", "บ. อาคเนย์ประกันภัย", "บ. อินทรประกันภัย", "บ. เอเชียประกันภัย 1950",
                "บ. แอลเอ็มจี ประกันภัย", "บ. เอไอจี ประกันภัย (ประเทศไทย)" }
                .Select(name => new MstCompany { Id = i, Name = name, DisplayOrder = i++ }).ToArray()
            );

            i = 1;
            modelBuilder.Entity<MstTitle>().HasData(
                new[] { "นาย", "นาง", "นางสาว", "ด.ช.", "ด.ญ.", "ยศ/อื่นๆ (โปรดระบุ)" }
                .Select(name => new MstTitle { Id = i, Name = name, DisplayOrder = i++ }).ToArray()
            );

            i = 1;
            modelBuilder.Entity<MstGender>().HasData(
                new[] { "ชาย", "หญิง" }
                .Select(name => new MstGender { Id = i, Name = name, DisplayOrder = i++ }).ToArray()
            );

            i = 1;
            modelBuilder.Entity<MstBlood>().HasData(
                new[] { "A", "B", "AB", "O" }
                .Select(name => new MstBlood { Id = i, Name = name, DisplayOrder = i++ }).ToArray()
            );

            i = 1;
            modelBuilder.Entity<MstReligion>().HasData(
                new[] { "พุทธ", "คริสต์", "อิสลาม", "ซิกข์", "ฮินดู", "อื่นๆ" }
                .Select(name => new MstReligion { Id = i, Name = name, DisplayOrder = i++ }).ToArray()
            );

            // Seed System Configuration
            modelBuilder.Entity<SysConfig>().HasData(
                // System Settings
                new SysConfig { Key = "APP_ENV", Value = "prd", Description = "Environment (dev, uat, prd)", Group = "system" },
                new SysConfig { Key = "SESSION_TIMEOUT", Value = "3600", Description = "Session timeout in seconds", Group = "system" },
                new SysConfig { Key = "SYSTEM_IS_ONLINE", Value = "true", Description = "Is system online", Group = "system" },
                new SysConfig { Key = "SYSTEM_OPEN_PERIODS", Value = "[]", Description = "JSON array of open periods", Group = "system" },

                // Tab 4 Settings
                new SysConfig { Key = "tab4_label_branch", Value = "สาขา *", Description = "Label for Branch", Group = "tab4" },
                new SysConfig { Key = "tab4_hint_branch", Value = "พิมพ์เพื่อค้นหาสาขา", Description = "Hint for Branch", Group = "tab4" },
                new SysConfig { Key = "tab4_label_region", Value = "ภาค *", Description = "Label for Region", Group = "tab4" },
                new SysConfig { Key = "tab4_hint_region", Value = "ระบบจะเติมให้อัตโนมัติ", Description = "Hint for Region", Group = "tab4" },
                new SysConfig { Key = "tab4_label_agentcode", Value = "รหัสที่มีสัญญากับ บมจ.วิริยะประกันภัย *", Description = "Label for Agent Code", Group = "tab4" },
                new SysConfig { Key = "tab4_hint_agentcode", Value = "ถ้าไม่ทราบ สอบถามสาขา หรือตัวแทน/นายหน้าที่ท่านสังกัด, ถ้าเป็นขอรับใบอนุญาต และยังไม่มีรหัส ให้กรอก 00000", Description = "Hint for Agent Code", Group = "tab4" },
                new SysConfig { Key = "tab4_default_branch", Value = "", Description = "Default value for Branch", Group = "tab4" },
                new SysConfig { Key = "tab4_default_agentcode", Value = "", Description = "Default value for Agent Code", Group = "tab4" },
                new SysConfig { Key = "tab4_allowed_agent_types", Value = "both", Description = "Allowed agent types (both, agent, broker)", Group = "tab4" }
            );
        }
    }
}
