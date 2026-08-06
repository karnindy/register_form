using Microsoft.AspNetCore.Mvc;
using backend.Data;
using backend.Models;
using System.IO;

namespace backend.Controllers
{
    [Route("api/[controller]")]
    [ApiController]
    public class UploadController : ControllerBase
    {
        private readonly AppDbContext _context;
        private readonly IWebHostEnvironment _env;

        public UploadController(AppDbContext context, IWebHostEnvironment env)
        {
            _context = context;
            _env = env;
        }

        [HttpPost]
        public async Task<IActionResult> UploadDocuments([FromForm] string nationalId, [FromForm] string phone)
        {
            if (string.IsNullOrEmpty(nationalId)) return BadRequest("National ID is required");
            
            var cleanNationId = nationalId.Replace("-", "");

            // Check if user exists
            var person = _context.Persons.FirstOrDefault(p => p.NationId == cleanNationId);
            if (person == null) return NotFound("Person not found");

            var uploadPath = Path.Combine(_env.ContentRootPath, "LocalData", "uploads", cleanNationId);
            if (!Directory.Exists(uploadPath))
            {
                Directory.CreateDirectory(uploadPath);
            }

            var files = Request.Form.Files;
            foreach (var file in files)
            {
                if (file.Length > 0)
                {
                    // Use form field name as DocumentType (e.g. "Profile", "IDCard", "IDCardFace")
                    var docType = file.Name;
                    var fileName = Guid.NewGuid().ToString() + Path.GetExtension(file.FileName);
                    var filePath = Path.Combine(uploadPath, fileName);

                    using (var stream = new FileStream(filePath, FileMode.Create))
                    {
                        await file.CopyToAsync(stream);
                    }

                    var existingDoc = _context.PersonDocuments.FirstOrDefault(d => d.NationId == cleanNationId && d.DocumentType == docType);
                    if (existingDoc != null)
                    {
                        // Delete old file if exists
                        var oldFilePath = Path.Combine(_env.ContentRootPath, existingDoc.FilePath.Replace("/", Path.DirectorySeparatorChar.ToString()));
                        if (System.IO.File.Exists(oldFilePath))
                        {
                            System.IO.File.Delete(oldFilePath);
                        }

                        // Update existing record
                        existingDoc.FilePath = Path.Combine("LocalData", "uploads", cleanNationId, fileName).Replace("\\", "/");
                        existingDoc.UploadedAt = DateTime.Now;
                        _context.PersonDocuments.Update(existingDoc);
                    }
                    else
                    {
                        // Add new record
                        var doc = new PersonDocument
                        {
                            NationId = cleanNationId,
                            DocumentType = docType,
                            FilePath = Path.Combine("LocalData", "uploads", cleanNationId, fileName).Replace("\\", "/"),
                            UploadedAt = DateTime.Now
                        };
                        _context.PersonDocuments.Add(doc);
                    }
                }
            }

            // Once documents are uploaded, we consider the registration fully confirmed
            var registration = _context.PersonRegistrations.OrderByDescending(r => r.Id).FirstOrDefault(r => r.NationId == cleanNationId);
            if (registration != null)
            {
                registration.confirmed = true;
                registration.completion_time = DateTime.Now;
            }

            await _context.SaveChangesAsync();

            return Ok(new { message = "Files uploaded successfully" });
        }
    }
}
