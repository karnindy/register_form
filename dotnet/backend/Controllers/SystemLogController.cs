using Microsoft.AspNetCore.Mvc;
using System.IO;

namespace backend.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class SystemLogController : ControllerBase
    {
        private readonly string _logDirectory = Path.Combine(Directory.GetCurrentDirectory(), "logs");

        [HttpGet("files")]
        public IActionResult GetLogFiles()
        {
            if (!Directory.Exists(_logDirectory))
            {
                return Ok(new List<string>());
            }

            var files = Directory.GetFiles(_logDirectory, "*.txt")
                                 .Select(Path.GetFileName)
                                 .OrderByDescending(f => f)
                                 .ToList();
            
            return Ok(files);
        }

        [HttpGet("files/{filename}")]
        public IActionResult GetLogContent(string filename)
        {
            // Simple security check to prevent directory traversal
            if (filename.Contains("..") || filename.Contains("/") || filename.Contains("\\"))
            {
                return BadRequest("Invalid filename.");
            }

            var filePath = Path.Combine(_logDirectory, filename);
            if (!System.IO.File.Exists(filePath))
            {
                return NotFound("Log file not found.");
            }

            // Using FileStream to read file that might be currently locked by Serilog
            using var fileStream = new FileStream(filePath, FileMode.Open, FileAccess.Read, FileShare.ReadWrite);
            using var streamReader = new StreamReader(fileStream);
            var content = streamReader.ReadToEnd();
            
            return Content(content, "text/plain");
        }
    }
}
