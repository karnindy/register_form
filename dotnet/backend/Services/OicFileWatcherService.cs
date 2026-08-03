using System;
using System.IO;
using System.Threading;
using System.Threading.Tasks;
using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Logging;

namespace backend.Services
{
    public class OicFileWatcherService : BackgroundService
    {
        private readonly ILogger<OicFileWatcherService> _logger;
        private readonly IServiceScopeFactory _scopeFactory;
        private readonly string _oicDirectory;

        public OicFileWatcherService(ILogger<OicFileWatcherService> logger, IServiceScopeFactory scopeFactory)
        {
            _logger = logger;
            _scopeFactory = scopeFactory;
            _oicDirectory = Path.Combine(Directory.GetCurrentDirectory(), "LocalData", "Oic");
        }

        protected override async Task ExecuteAsync(CancellationToken stoppingToken)
        {
            _logger.LogInformation("OicFileWatcherService is starting.");

            if (!Directory.Exists(_oicDirectory))
            {
                Directory.CreateDirectory(_oicDirectory);
                _logger.LogInformation("Created directory: {Directory}", _oicDirectory);
            }

            while (!stoppingToken.IsCancellationRequested)
            {
                try
                {
                    var files = Directory.GetFiles(_oicDirectory, "*.json");
                    foreach (var file in files)
                    {
                        if (stoppingToken.IsCancellationRequested) break;

                        var fileName = Path.GetFileName(file);
                        var cleanId = Path.GetFileNameWithoutExtension(fileName).Replace("-", "").Trim();
                        
                        _logger.LogInformation("Found new OIC profile file for ID {IdCardNumber}: {FileName}", cleanId, fileName);

                        using (var scope = _scopeFactory.CreateScope())
                        {
                            var oicApiService = scope.ServiceProvider.GetRequiredService<OicApiService>();
                            var result = await oicApiService.ProcessFileAsync(file, cleanId);

                            if (result != null)
                            {
                                _logger.LogInformation("Successfully processed and archived profile for ID {IdCardNumber}", cleanId);
                            }
                            else
                            {
                                _logger.LogWarning("Failed to process profile for ID {IdCardNumber} or file was empty. Renaming to .error", cleanId);
                                try 
                                {
                                    System.IO.File.Move(file, file + ".error");
                                }
                                catch (Exception moveEx) 
                                {
                                    _logger.LogError(moveEx, "Failed to rename bad file {File}", file);
                                }
                            }
                        }
                    }
                }
                catch (Exception ex)
                {
                    _logger.LogError(ex, "Error occurred executing OicFileWatcherService.");
                }

                await Task.Delay(5000, stoppingToken); // Poll every 5 seconds
            }

            _logger.LogInformation("OicFileWatcherService is stopping.");
        }
    }
}
