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
        
        // Master Data
        public DbSet<MstProvince> Provinces { get; set; }
        public DbSet<MstDistrict> Districts { get; set; }
        public DbSet<MstSubDistrict> SubDistricts { get; set; }
        public DbSet<MstBlood> BloodTypes { get; set; }
        public DbSet<MstGender> Genders { get; set; }
        public DbSet<MstReligion> Religions { get; set; }
        public DbSet<MstTitle> Titles { get; set; }
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
            
            // Map table names for Master Data base class inheritance if needed
        }
    }
}
