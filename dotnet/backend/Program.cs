using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Infrastructure;
using backend.Data;
using backend.Repositories;
using Serilog;

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
    options.UseMySql(connectionString, ServerVersion.AutoDetect(connectionString)));

// Dependency Injection for Repositories
builder.Services.AddScoped<IRegisterRepository, RegisterRepository>();
builder.Services.AddScoped<IRegisterHistoryRepository, RegisterHistoryRepository>();

builder.Services.AddControllers();
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
    var dbCreator = context.GetService<Microsoft.EntityFrameworkCore.Storage.IRelationalDatabaseCreator>();
    var script = dbCreator.GenerateCreateScript();
    
    var commands = script.Split(';', StringSplitOptions.RemoveEmptyEntries);
    foreach (var command in commands)
    {
        if (string.IsNullOrWhiteSpace(command)) continue;
        try 
        {
            context.Database.ExecuteSqlRaw(command);
        } 
        catch (MySqlConnector.MySqlException ex) when (ex.Number == 1050) 
        {
            // Error 1050: Table already exists. Safe to ignore.
        }
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

app.UseAuthorization();

app.MapControllers();

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
