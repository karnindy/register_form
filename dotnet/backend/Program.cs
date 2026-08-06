using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Infrastructure;
using backend.Data;
using backend.Repositories;
using Serilog;
using Microsoft.Extensions.FileProviders;
using System.IO;

Log.Logger = new LoggerConfiguration()
    .WriteTo.Console()
    .WriteTo.File("logs/log-.txt", rollingInterval: RollingInterval.Day)
    .CreateBootstrapLogger();

try
{
    Log.Information("Starting web application");
    var builder = WebApplication.CreateBuilder(args);

    builder.Services.AddSerilog((services, lc) => lc
        .ReadFrom.Configuration(builder.Configuration)
        .ReadFrom.Services(services)
        .Enrich.FromLogContext()
        .WriteTo.Console()
        .WriteTo.File("logs/log-.txt", rollingInterval: RollingInterval.Day));

    // Add services to the container.

// Configure Database Connection using EF Core
// NOTE: This uses MySQL. When migrating to SQL Server with Data API builder, 
// you will replace this ORM setup with a typed HttpClient to the REST endpoint.
var connectionString = builder.Configuration.GetConnectionString("DefaultConnection");
builder.Services.AddDbContext<AppDbContext>(options =>
    options.UseSqlServer(connectionString));

var mySqlConnectionString = builder.Configuration.GetConnectionString("MySqlConnection");
builder.Services.AddDbContext<MySqlDbContext>(options =>
    options.UseMySql(mySqlConnectionString, ServerVersion.Parse("8.0.30-mysql")));

// Dependency Injection for Repositories
builder.Services.AddScoped<IRegisterRepository, RegisterRepository>();
builder.Services.AddScoped<IRegisterHistoryRepository, RegisterHistoryRepository>();
builder.Services.AddHttpClient<backend.Services.OicApiService>();
builder.Services.AddHostedService<backend.Services.OicFileWatcherService>();

builder.Services.AddControllers()
    .AddJsonOptions(options =>
    {
        options.JsonSerializerOptions.ReferenceHandler = System.Text.Json.Serialization.ReferenceHandler.IgnoreCycles;
    })
    .ConfigureApiBehaviorOptions(options =>
    {
        options.InvalidModelStateResponseFactory = context =>
        {
            var errors = context.ModelState.Values.SelectMany(v => v.Errors).Select(e => e.ErrorMessage);
            Serilog.Log.Error("Model State Validation Failed: {Errors}", string.Join(", ", errors));
            return new Microsoft.AspNetCore.Mvc.BadRequestObjectResult(context.ModelState);
        };
    });
// Learn more about configuring Swagger/OpenAPI at https://aka.ms/aspnetcore/swashbuckle
builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen(options =>
{
    var xmlFilename = $"{System.Reflection.Assembly.GetExecutingAssembly().GetName().Name}.xml";
    options.IncludeXmlComments(Path.Combine(AppContext.BaseDirectory, xmlFilename));
});

// Configure CORS for the React frontend
builder.Services.AddCors(options =>
{
    options.AddPolicy("AllowFrontend",
        policy =>
        {
            policy.WithOrigins("http://localhost:5173", "http://localhost") // Adjust for production
                  .AllowAnyHeader()
                  .AllowAnyMethod();
        });
});

var app = builder.Build();

// Auto-migrate and seed database
using (var scope = app.Services.CreateScope())
{
    var context = scope.ServiceProvider.GetRequiredService<AppDbContext>();
    
    // In a legacy DB scenario, EnsureCreated ignores everything if the DB exists.
    // Instead, we get the create script and run it command by command, ignoring "Table already exists" errors.
    // Ensure DB exists (without running schema scripts which contain GO)
    context.Database.EnsureCreated();

    // Ensure new columns exist
    try
    {
        context.Database.ExecuteSqlRaw("IF COL_LENGTH('personregistration', 'DeductionPrivilege') IS NULL BEGIN ALTER TABLE personregistration ADD DeductionPrivilege NVARCHAR(MAX) NULL; END");
        context.Database.ExecuteSqlRaw("IF COL_LENGTH('personregistration', 'MasterDegreeStatus') IS NULL BEGIN ALTER TABLE personregistration ADD MasterDegreeStatus NVARCHAR(MAX) NULL; END");
        context.Database.ExecuteSqlRaw("IF COL_LENGTH('registrations', 'master_degree_status') IS NULL BEGIN ALTER TABLE registrations ADD master_degree_status NVARCHAR(MAX) NULL; END");
    }
    catch (Exception ex)
    {
        Console.WriteLine("Warning: Could not add columns: " + ex.Message);
    }

    try 
    {
        context.Database.ExecuteSqlRaw("IF COL_LENGTH('oic_raw_api_store', 'is_latest') IS NULL BEGIN ALTER TABLE oic_raw_api_store ADD is_latest BIT DEFAULT 0 NOT NULL; END");
        context.Database.ExecuteSqlRaw(@"
            WITH LatestRecords AS (
                SELECT raw_id, ROW_NUMBER() OVER(PARTITION BY id_card_number ORDER BY raw_id DESC) as rn
                FROM oic_raw_api_store
            )
            UPDATE oic_raw_api_store
            SET is_latest = CASE WHEN l.rn = 1 THEN 1 ELSE 0 END
            FROM oic_raw_api_store o
            INNER JOIN LatestRecords l ON o.raw_id = l.raw_id;
        ");

        context.Database.ExecuteSqlRaw("IF COL_LENGTH('oic_agent_profile_store', 'is_latest') IS NULL BEGIN ALTER TABLE oic_agent_profile_store ADD is_latest BIT DEFAULT 0 NOT NULL; END");
        
        // Drop unique constraint on id_card_number in oic_agent_profile_store if it exists, so we can store history
        context.Database.ExecuteSqlRaw(@"
            DECLARE @constraint_name NVARCHAR(256);
            SELECT @constraint_name = name 
            FROM sys.key_constraints 
            WHERE type = 'UQ' AND parent_object_id = OBJECT_ID('oic_agent_profile_store');
            IF @constraint_name IS NOT NULL
            BEGIN
                EXEC('ALTER TABLE oic_agent_profile_store DROP CONSTRAINT ' + @constraint_name);
            END
            
            -- Also check for unique index
            SELECT @constraint_name = name 
            FROM sys.indexes 
            WHERE is_unique = 1 AND object_id = OBJECT_ID('oic_agent_profile_store') AND is_primary_key = 0;
            IF @constraint_name IS NOT NULL
            BEGIN
                EXEC('DROP INDEX ' + @constraint_name + ' ON oic_agent_profile_store');
            END
        ");

        context.Database.ExecuteSqlRaw(@"
            WITH LatestProfiles AS (
                SELECT profile_id, ROW_NUMBER() OVER(PARTITION BY id_card_number ORDER BY profile_id DESC) as rn
                FROM oic_agent_profile_store
            )
            UPDATE oic_agent_profile_store
            SET is_latest = CASE WHEN l.rn = 1 THEN 1 ELSE 0 END
            FROM oic_agent_profile_store p
            INNER JOIN LatestProfiles l ON p.profile_id = l.profile_id;
        ");
    }
    catch (Exception ex)
    {
        Serilog.Log.Error(ex, "Failed to update is_latest column in oic_raw_api_store or oic_agent_profile_store");
    }

    // Seed initial admin user if none exists
    if (!context.Users.Any())
    {
        context.Users.Add(new backend.Models.User 
        { 
            Username = "admin", 
            PasswordHash = "editor", // In real app, this should be hashed
            Role = "Admin",
            IsActive = true
        });
        context.SaveChanges();
    }

    // Seed agent regions and branches
    if (!context.AgentRegions.Any())
    {
        var agentRegionMap = new Dictionary<string, string[]>
        {
            { "ภาค 1 (ภาคเหนือ)", new[] { "เชียงราย", "เชียงใหม่", "นครสวรรค์", "พิษณุโลก" } },
            { "ภาค 2 (ภาคตะวันออกเฉียงเหนือ)", new[] { "ขอนแก่น", "นครราชสีมา", "อุดรธานี", "อุบลราชธานี" } },
            { "ภาค 3 (ภาคตะวันออก)", new[] { "จันทบุรี", "ฉะเชิงเทรา", "พัทยา", "ระยอง" } },
            { "ภาค 4 (ภาคกลางและภาคตะวันตก)", new[] { "นครปฐม", "พระนครศรีอยุธยา", "สมุทรสาคร", "สระบุรี" } },
            { "ภาค 5 (ภาคใต้)", new[] { "กระบี่", "นครศรีธรรมราช", "ภูเก็ต", "สุราษฎร์ธานี", "หาดใหญ่" } },
            { "ภาค 6 (ภาคกรุงเทพฯ)", new[] { "กรุงเกษม", "ดอนเมือง", "บางนา", "บางพลัด", "ปู่เจ้าสมิงพราย", "พระราม 2", "ปากเกร็ด-345", "รัชดาภิเษก", "ลุมพินี", "วงศ์สว่าง", "วิภาวดี", "สุขสวัสดิ์", "สุขาภิบาล 3", "กิจกรรพิเศษ1", "กิจกรรพิเศษ2" } }
        };

        foreach (var kvp in agentRegionMap)
        {
            var region = new backend.Models.MstAgentRegion { Name = kvp.Key };
            context.AgentRegions.Add(region);
            context.SaveChanges(); // Need ID generated

            foreach (var branch in kvp.Value)
            {
                context.AgentBranches.Add(new backend.Models.MstAgentBranch 
                { 
                    Name = branch, 
                    RegionId = region.Id 
                });
            }
        }
        context.SaveChanges();
    }
}

app.UseSerilogRequestLogging();

// Configure the HTTP request pipeline.
if (app.Environment.IsDevelopment())
{
    app.UseSwagger();
    app.UseSwaggerUI();
}

// app.UseHttpsRedirection();

app.UseCors("AllowFrontend");

var localDataPath = Path.Combine(app.Environment.ContentRootPath, "LocalData");
if (!Directory.Exists(localDataPath)) Directory.CreateDirectory(localDataPath);
app.UseStaticFiles(new StaticFileOptions
{
    FileProvider = new PhysicalFileProvider(localDataPath),
    RequestPath = "/LocalData"
});

app.UseAuthorization();

app.MapControllers();

app.MapPost("/api/migrate-db", async (MySqlDbContext mysqlDb, AppDbContext sqlDb) =>
{
    // Ensure SQL Server database is created
    await sqlDb.Database.MigrateAsync();
    
    // Ensure new columns exist
    try
    {
        await sqlDb.Database.ExecuteSqlRawAsync("IF COL_LENGTH('personregistration', 'DeductionPrivilege') IS NULL BEGIN ALTER TABLE personregistration ADD DeductionPrivilege NVARCHAR(MAX) NULL; END");
        await sqlDb.Database.ExecuteSqlRawAsync("IF COL_LENGTH('personregistration', 'MasterDegreeStatus') IS NULL BEGIN ALTER TABLE personregistration ADD MasterDegreeStatus NVARCHAR(MAX) NULL; END");
        await sqlDb.Database.ExecuteSqlRawAsync("IF COL_LENGTH('registrations', 'master_degree_status') IS NULL BEGIN ALTER TABLE registrations ADD master_degree_status NVARCHAR(MAX) NULL; END");
    }
    catch (Exception ex)
    {
        Console.WriteLine("Warning: Could not add columns: " + ex.Message);
    }

    // Note: To insert explicit IDs, we need to enable IDENTITY_INSERT.
    // For simplicity, we just use raw SQL to insert them to bypass IDENTITY constraints in EF Core.
    
    // Quick helper to execute raw SQL for inserts
    async Task MigrateTable<T>(DbSet<T> sourceSet, string tableName) where T : class
    {
        var records = await sourceSet.AsNoTracking().ToListAsync();
        if (!records.Any()) return;
        
        var first = records.First();
        var props = first.GetType().GetProperties().Where(p => p.Name != "Id" && (p.PropertyType == typeof(string) || p.PropertyType.IsPrimitive || p.PropertyType.IsValueType)).ToList();
        
        foreach (var record in records)
        {
            var idProp = record.GetType().GetProperty("Id");
            var idVal = idProp?.GetValue(record);
            
            // Check if exists
            var exists = await sqlDb.Database.ExecuteSqlRawAsync($"SELECT 1 FROM {tableName} WHERE Id = {idVal}");
            // Actually ExecuteSqlRaw returns number of rows affected, we can't use it for SELECT easily.
            // Better to just try catch insert.
        }
    }

    // Since it's getting complicated with IDENTITY_INSERT, let's just insert them via EF but we have to manually run SET IDENTITY_INSERT ON
    using var transaction = await sqlDb.Database.BeginTransactionAsync();
    try
    {
        // For Master Data, we can just allow EF Core to generate NEW IDs, but wait, the IDs are used as Foreign Keys!
        // We MUST preserve the IDs!
        // Let's use raw SQL for Identity Insert.
        var tables = new[] { "Provinces", "Districts", "SubDistricts", "Genders", "Titles", "BloodTypes", "Religions", "Territories", "Expertises", "Companies" };
        foreach (var t in tables) 
        {
            await sqlDb.Database.ExecuteSqlRawAsync($"IF OBJECT_ID('{t}', 'U') IS NOT NULL SET IDENTITY_INSERT {t} ON;");
        }
        
        var provinces = await mysqlDb.Provinces.AsNoTracking().ToListAsync();
        foreach (var p in provinces) {
            if (!await sqlDb.Provinces.AnyAsync(x => x.Id == p.Id)) { sqlDb.Provinces.Add(p); }
        }
        await sqlDb.SaveChangesAsync();

        var districts = await mysqlDb.Districts.AsNoTracking().ToListAsync();
        foreach (var d in districts) {
            if (!await sqlDb.Districts.AnyAsync(x => x.Id == d.Id)) { sqlDb.Districts.Add(d); }
        }
        await sqlDb.SaveChangesAsync();

        var subDistricts = await mysqlDb.SubDistricts.AsNoTracking().ToListAsync();
        foreach (var s in subDistricts) {
            if (!await sqlDb.SubDistricts.AnyAsync(x => x.Id == s.Id)) { sqlDb.SubDistricts.Add(s); }
        }
        await sqlDb.SaveChangesAsync();

        var genders = await mysqlDb.Genders.AsNoTracking().ToListAsync();
        foreach (var g in genders) {
            if (!await sqlDb.Genders.AnyAsync(x => x.Id == g.Id)) { sqlDb.Genders.Add(g); }
        }
        await sqlDb.SaveChangesAsync();

        var titles = await mysqlDb.Titles.AsNoTracking().ToListAsync();
        foreach (var t in titles) {
            if (!await sqlDb.Titles.AnyAsync(x => x.Id == t.Id)) { sqlDb.Titles.Add(t); }
        }
        await sqlDb.SaveChangesAsync();

        var bloods = await mysqlDb.BloodTypes.AsNoTracking().ToListAsync();
        foreach (var b in bloods) {
            if (!await sqlDb.BloodTypes.AnyAsync(x => x.Id == b.Id)) { sqlDb.BloodTypes.Add(b); }
        }
        await sqlDb.SaveChangesAsync();

        var religions = await mysqlDb.Religions.AsNoTracking().ToListAsync();
        foreach (var r in religions) {
            if (!await sqlDb.Religions.AnyAsync(x => x.Id == r.Id)) { sqlDb.Religions.Add(r); }
        }
        await sqlDb.SaveChangesAsync();

        var territories = await mysqlDb.Territories.AsNoTracking().ToListAsync();
        foreach (var t in territories) {
            if (!await sqlDb.Territories.AnyAsync(x => x.Id == t.Id)) { sqlDb.Territories.Add(t); }
        }
        await sqlDb.SaveChangesAsync();

        var expertises = await mysqlDb.Expertises.AsNoTracking().ToListAsync();
        foreach (var e in expertises) {
            if (!await sqlDb.Expertises.AnyAsync(x => x.Id == e.Id)) { sqlDb.Expertises.Add(e); }
        }
        await sqlDb.SaveChangesAsync();

        var companies = await mysqlDb.Companies.AsNoTracking().ToListAsync();
        foreach (var c in companies) {
            if (!await sqlDb.Companies.AnyAsync(x => x.Id == c.Id)) { sqlDb.Companies.Add(c); }
        }
        await sqlDb.SaveChangesAsync();

        var users = await mysqlDb.Users.AsNoTracking().ToListAsync();
        foreach (var u in users) {
            if (!await sqlDb.Users.AnyAsync(x => x.Id == u.Id)) { sqlDb.Users.Add(u); }
        }
        await sqlDb.SaveChangesAsync();

        foreach (var t in tables) 
        {
            await sqlDb.Database.ExecuteSqlRawAsync($"IF OBJECT_ID('{t}', 'U') IS NOT NULL SET IDENTITY_INSERT {t} OFF;");
        }
        
        await transaction.CommitAsync();
    }
    catch
    {
        await transaction.RollbackAsync();
        throw;
    }

    return Results.Ok(new { message = "Data migration completed successfully" });
});

app.Run();
}
catch (Exception ex)
{
    Log.Fatal(ex, "Application terminated unexpectedly");
}
finally
{
    Log.CloseAndFlush();
}
