using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text.RegularExpressions;
using System.Threading.Tasks;
using ClosedXML.Excel;
using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;

namespace backend.Services
{
    public class TrainingImportRowDto
    {
        public int RowIndex { get; set; }
        public string? NationalId { get; set; }
        public string? OriginalLicenseNo { get; set; }
        public string? OriginalPrefix { get; set; }
        public string? OriginalFirstName { get; set; }
        public string? OriginalLastName { get; set; }
        public string? DeductionPrivilege { get; set; } // Col J (Y/N)
        public string? TrainingStatus { get; set; } // Col L (อนุมัติ, ไม่ผ่าน)
        public string? DeductionDocStatus { get; set; } // Col M
        public string? TrainingDateDisplay { get; set; } // Col Q
        public string? TrainingCourseCode { get; set; } // Col R (e.g. A4P1_04, B0N0O00)
        public string? TrainingCourseName { get; set; } // Col S
        public string? VerifiedTitleTh { get; set; } // Col T
        public string? VerifiedFirstNameTh { get; set; } // Col U
        public string? VerifiedLastNameTh { get; set; } // Col V
        public string? VerifiedLicenseNo { get; set; } // Col W
        public string? VerifiedLicenseIssueDate { get; set; } // Col X
        public string? VerifiedLicenseExpiryDate { get; set; } // Col Y
        public string? VerifiedApplicantType { get; set; } // Col Z
        public string? VerifiedLicenseType { get; set; } // Col AA
        public string? VerifiedInsuranceType { get; set; } // Col AB
        public string? ProgressPercent { get; set; } // Col AC
        public string? ScorePercent { get; set; } // Col AD
        public string? EnrollmentUrl { get; set; } // Col AE

        // Matching & Audit status
        public bool IsPersonMatched { get; set; }
        public bool IsCourseMatched { get; set; }
        public bool IsDataMismatch { get; set; }
        public string? MismatchDetails { get; set; }
        public string? SystemPersonName { get; set; }
        public string? SystemLicenseNo { get; set; }
        public string? CourseTypeName { get; set; } // Basic or Renew
    }

    public class TrainingImportSummaryDto
    {
        public string FileName { get; set; } = string.Empty;
        public int TotalRows { get; set; }
        public int PassedCount { get; set; }
        public int FailedCount { get; set; }
        public int DeductionCount { get; set; }
        public int MatchedPersonsCount { get; set; }
        public int UnmatchedPersonsCount { get; set; }
        public int MismatchedDataCount { get; set; }
        public List<TrainingImportRowDto> Rows { get; set; } = new List<TrainingImportRowDto>();
    }

    public class TrainingImportService
    {
        private readonly AppDbContext _context;

        public TrainingImportService(AppDbContext context)
        {
            _context = context;
        }

        public async Task<TrainingImportSummaryDto> ParseAndPreviewExcelAsync(Stream fileStream, string fileName)
        {
            var rows = ParseExcelRows(fileStream);
            var summary = await EvaluateRowsAsync(rows, fileName);
            return summary;
        }

        public async Task<TrainingImportSummaryDto> ExecuteStampAsync(Stream fileStream, string fileName, bool syncVerifiedDataToPerson = false)
        {
            var rows = ParseExcelRows(fileStream);
            var summary = await EvaluateRowsAsync(rows, fileName);

            // Pre-fetch persons and licenses
            var nationIds = rows.Where(r => !string.IsNullOrEmpty(r.NationalId)).Select(r => r.NationalId!).Distinct().ToList();
            var persons = await _context.Persons
                .Include(p => p.Registrations)
                .Include(p => p.Licenses)
                .Where(p => nationIds.Contains(p.NationId!))
                .ToDictionaryAsync(p => p.NationId!, p => p);

            var resultsToInsert = new List<TrnTrainingResult>();

            foreach (var r in summary.Rows)
            {
                var result = new TrnTrainingResult
                {
                    NationId = r.NationalId,
                    OriginalLicenseNo = r.OriginalLicenseNo,
                    TrainingCourseCode = r.TrainingCourseCode,
                    TrainingCourseName = r.TrainingCourseName,
                    TrainingDateDisplay = r.TrainingDateDisplay,
                    TrainingStatus = r.TrainingStatus,
                    DeductionPrivilege = r.DeductionPrivilege,
                    DeductionDocStatus = r.DeductionDocStatus,
                    ProgressPercent = r.ProgressPercent,
                    ScorePercent = r.ScorePercent,
                    EnrollmentUrl = r.EnrollmentUrl,
                    VerifiedTitleTh = r.VerifiedTitleTh,
                    VerifiedFirstNameTh = r.VerifiedFirstNameTh,
                    VerifiedLastNameTh = r.VerifiedLastNameTh,
                    VerifiedLicenseNo = r.VerifiedLicenseNo,
                    VerifiedLicenseIssueDate = r.VerifiedLicenseIssueDate,
                    VerifiedLicenseExpiryDate = r.VerifiedLicenseExpiryDate,
                    VerifiedApplicantType = r.VerifiedApplicantType,
                    VerifiedLicenseType = r.VerifiedLicenseType,
                    VerifiedInsuranceType = r.VerifiedInsuranceType,
                    StampDate = DateTime.Now,
                    ImportFileName = fileName,
                    IsDataMismatch = r.IsDataMismatch ? "Y" : "N"
                };

                if (!string.IsNullOrEmpty(r.NationalId) && persons.TryGetValue(r.NationalId, out var person))
                {
                    var latestReg = person.Registrations.OrderByDescending(reg => reg.Id).FirstOrDefault();
                    if (latestReg != null)
                    {
                        result.PersonRegistrationId = latestReg.Id;

                        // If passed in training system ("อนุมัติ" or "ผ่าน"), stamp confirmed status
                        if (r.TrainingStatus == "อนุมัติ" || r.TrainingStatus == "ผ่าน")
                        {
                            latestReg.confirmed = true;
                            latestReg.completion_time = DateTime.Now;
                        }

                        if (r.DeductionPrivilege == "Y")
                        {
                            latestReg.DeductionPrivilege = "Y";
                        }
                    }

                    // Optional sync of verified data to person master & license
                    if (syncVerifiedDataToPerson)
                    {
                        if (!string.IsNullOrWhiteSpace(r.VerifiedTitleTh)) person.TitleTh = r.VerifiedTitleTh.Trim();
                        if (!string.IsNullOrWhiteSpace(r.VerifiedFirstNameTh)) person.FirstNameTh = r.VerifiedFirstNameTh.Trim();
                        if (!string.IsNullOrWhiteSpace(r.VerifiedLastNameTh)) person.LastNameTh = r.VerifiedLastNameTh.Trim();

                        if (!string.IsNullOrWhiteSpace(r.VerifiedLicenseNo))
                        {
                            var cleanLic = r.VerifiedLicenseNo.Trim();
                            var license = person.Licenses.FirstOrDefault();
                            if (license == null)
                            {
                                license = new PersonLicense
                                {
                                    NationId = person.NationId,
                                    LicenseNo = cleanLic
                                };
                                person.Licenses.Add(license);
                            }
                            else
                            {
                                license.LicenseNo = cleanLic;
                            }

                            if (DateTime.TryParse(r.VerifiedLicenseIssueDate, out var issueDate))
                            {
                                license.LicenseIssueDate = issueDate;
                            }
                            if (DateTime.TryParse(r.VerifiedLicenseExpiryDate, out var expiryDate))
                            {
                                license.LicenseExpiryDate = expiryDate;
                            }
                        }
                    }
                }

                resultsToInsert.Add(result);
            }

            await _context.TrainingResults.AddRangeAsync(resultsToInsert);
            await _context.SaveChangesAsync();

            return summary;
        }

        private List<TrainingImportRowDto> ParseExcelRows(Stream fileStream)
        {
            var list = new List<TrainingImportRowDto>();

            using (var workbook = new XLWorkbook(fileStream))
            {
                var worksheet = workbook.Worksheets.FirstOrDefault();
                if (worksheet == null) return list;

                // Find the header row (typically row 3 has headers like 'คำสั่ง', 'เลขที่ใบอนุญาต', 'หมายเลขบัตรประชาชน', etc.)
                int startRow = 4;
                int maxRow = worksheet.LastRowUsed()?.RowNumber() ?? 0;

                for (int row = startRow; row <= maxRow; row++)
                {
                    var r = worksheet.Row(row);
                    if (r.IsEmpty()) continue;

                    string cmd = r.Cell(3).GetString().Trim(); // Col C: [1.1.N.0.0]-ADD
                    string licenseNo = r.Cell(5).GetString().Trim(); // Col E: AG_LICENSE
                    string nationalIdRaw = r.Cell(6).GetString().Trim(); // Col F: AG_ID_CARD
                    string nationalId = Regex.Replace(nationalIdRaw, @"\D", ""); // clean 13 digits

                    // Skip empty rows without national ID or license
                    if (string.IsNullOrEmpty(nationalId) && string.IsNullOrEmpty(licenseNo))
                        continue;

                    var item = new TrainingImportRowDto
                    {
                        RowIndex = row,
                        OriginalLicenseNo = licenseNo,
                        NationalId = nationalId,
                        OriginalPrefix = r.Cell(7).GetString().Trim(),
                        OriginalFirstName = r.Cell(8).GetString().Trim(),
                        OriginalLastName = r.Cell(9).GetString().Trim(),
                        DeductionPrivilege = r.Cell(10).GetString().Trim().ToUpper(), // Col J
                        TrainingStatus = r.Cell(12).GetString().Trim(), // Col L
                        DeductionDocStatus = r.Cell(13).GetString().Trim(), // Col M
                        TrainingDateDisplay = r.Cell(17).GetString().Trim(), // Col Q
                        TrainingCourseCode = r.Cell(18).GetString().Trim(), // Col R
                        TrainingCourseName = r.Cell(19).GetString().Trim(), // Col S
                        VerifiedTitleTh = r.Cell(20).GetString().Trim(), // Col T
                        VerifiedFirstNameTh = r.Cell(21).GetString().Trim(), // Col U
                        VerifiedLastNameTh = r.Cell(22).GetString().Trim(), // Col V
                        VerifiedLicenseNo = r.Cell(23).GetString().Trim(), // Col W
                        VerifiedLicenseIssueDate = r.Cell(24).GetString().Trim(), // Col X
                        VerifiedLicenseExpiryDate = r.Cell(25).GetString().Trim(), // Col Y
                        VerifiedApplicantType = r.Cell(26).GetString().Trim(), // Col Z
                        VerifiedLicenseType = r.Cell(27).GetString().Trim(), // Col AA
                        VerifiedInsuranceType = r.Cell(28).GetString().Trim(), // Col AB
                        ProgressPercent = r.Cell(29).GetString().Trim(), // Col AC
                        ScorePercent = r.Cell(30).GetString().Trim(), // Col AD
                        EnrollmentUrl = r.Cell(31).GetString().Trim() // Col AE
                    };

                    list.Add(item);
                }
            }

            return list;
        }

        private async Task<TrainingImportSummaryDto> EvaluateRowsAsync(List<TrainingImportRowDto> rows, string fileName)
        {
            var nationIds = rows.Where(r => !string.IsNullOrEmpty(r.NationalId)).Select(r => r.NationalId!).Distinct().ToList();
            var courseCodes = rows.Where(r => !string.IsNullOrEmpty(r.TrainingCourseCode)).Select(r => r.TrainingCourseCode!).Distinct().ToList();

            var persons = await _context.Persons
                .Include(p => p.Licenses)
                .Where(p => nationIds.Contains(p.NationId!))
                .ToDictionaryAsync(p => p.NationId!, p => p);

            var curriculums = await _context.CourseCurriculums
                .Where(c => courseCodes.Contains(c.TrainingCourseCode!))
                .ToListAsync();

            var summary = new TrainingImportSummaryDto
            {
                FileName = fileName,
                TotalRows = rows.Count
            };

            foreach (var r in rows)
            {
                // Status counts
                if (r.TrainingStatus == "อนุมัติ" || r.TrainingStatus == "ผ่าน")
                    summary.PassedCount++;
                else
                    summary.FailedCount++;

                if (r.DeductionPrivilege == "Y")
                    summary.DeductionCount++;

                // Person matching
                if (!string.IsNullOrEmpty(r.NationalId) && persons.TryGetValue(r.NationalId, out var person))
                {
                    r.IsPersonMatched = true;
                    r.SystemPersonName = $"{person.TitleTh}{person.FirstNameTh} {person.LastNameTh}".Trim();
                    r.SystemLicenseNo = person.Licenses.FirstOrDefault()?.LicenseNo;

                    // Audit check for discrepancies
                    var mismatches = new List<string>();
                    if (!string.IsNullOrEmpty(r.VerifiedFirstNameTh) && !string.Equals(r.VerifiedFirstNameTh.Trim(), person.FirstNameTh?.Trim(), StringComparison.OrdinalIgnoreCase))
                    {
                        mismatches.Add($"ชื่อไม่ตรง: ในระบบ '{person.FirstNameTh}' vs ตรวจสอบแล้ว '{r.VerifiedFirstNameTh}'");
                    }
                    if (!string.IsNullOrEmpty(r.VerifiedLastNameTh) && !string.Equals(r.VerifiedLastNameTh.Trim(), person.LastNameTh?.Trim(), StringComparison.OrdinalIgnoreCase))
                    {
                        mismatches.Add($"นามสกุลไม่ตรง: ในระบบ '{person.LastNameTh}' vs ตรวจสอบแล้ว '{r.VerifiedLastNameTh}'");
                    }
                    if (!string.IsNullOrEmpty(r.VerifiedLicenseNo) && !string.IsNullOrEmpty(r.SystemLicenseNo) && !string.Equals(r.VerifiedLicenseNo.Trim(), r.SystemLicenseNo.Trim(), StringComparison.OrdinalIgnoreCase))
                    {
                        mismatches.Add($"เลขใบอนุญาตไม่ตรง: ในระบบ '{r.SystemLicenseNo}' vs ตรวจสอบแล้ว '{r.VerifiedLicenseNo}'");
                    }

                    if (mismatches.Count > 0)
                    {
                        r.IsDataMismatch = true;
                        r.MismatchDetails = string.Join("; ", mismatches);
                        summary.MismatchedDataCount++;
                    }
                }
                else
                {
                    r.IsPersonMatched = false;
                }

                // Course matching
                var foundCurriculum = curriculums.FirstOrDefault(c => c.TrainingCourseCode == r.TrainingCourseCode);
                if (foundCurriculum != null)
                {
                    r.IsCourseMatched = true;
                    r.CourseTypeName = foundCurriculum.CourseType == "basic" ? "ขอรับ/ต่อ 1-3 (Basic)" : "ต่อ 4 (Renew 4)";
                }

                summary.Rows.Add(r);
            }

            summary.MatchedPersonsCount = summary.Rows.Count(r => r.IsPersonMatched);
            summary.UnmatchedPersonsCount = summary.Rows.Count(r => !r.IsPersonMatched);

            return summary;
        }
    }
}
