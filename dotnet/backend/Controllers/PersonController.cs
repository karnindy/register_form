using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;
using System.Linq;
using System.Threading.Tasks;
using System.Text.Json;
using System.Collections.Generic;
using System;

namespace backend.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class PersonController : ControllerBase
    {
        private readonly AppDbContext _context;
        private readonly backend.Services.OicApiService _oicApiService;

        public PersonController(AppDbContext context, backend.Services.OicApiService oicApiService)
        {
            _context = context;
            _oicApiService = oicApiService;
        }

        [HttpPost("refresh-oic/{nationId}")]
        public async Task<IActionResult> RefreshOicProfile(string nationId)
        {
            var cleanNationId = nationId.Replace("-", "");
            var profile = await _oicApiService.FetchAndProcessProfileAsync(cleanNationId);
            if (profile == null)
            {
                return BadRequest(new { message = "ไม่สามารถดึงข้อมูลจาก OIC ได้ หรือสถานะไม่เป็น 200 (ตรวจสอบใน Log oic_api_request_log)" });
            }
            return Ok(new { message = "อัปเดตข้อมูลจาก OIC สำเร็จ (บันทึกลง oic_raw_api_store และ oic_agent_profile_store เรียบร้อย)", profile });
        }

        [HttpGet("{nationId}")]
        public async Task<IActionResult> GetPerson(string nationId)
        {
            // Remove hyphens if any
            var cleanNationId = nationId.Replace("-", "");

            var person = await _context.Persons
                .Include(p => p.Addresses)
                .Include(p => p.Licenses)
                .Include(p => p.Affiliations)
                .Include(p => p.Courses)
                .Include(p => p.Trainings)
                .Include(p => p.Others)
                    .ThenInclude(o => o.SalesAreas)
                .Include(p => p.Others)
                    .ThenInclude(o => o.OtherCompanies)
                .Include(p => p.Others)
                    .ThenInclude(o => o.Specialties)
                .FirstOrDefaultAsync(p => p.NationId == cleanNationId);

            var oicProfile = await _context.OicAgentProfileStores
                .OrderByDescending(o => o.ProfileId)
                .FirstOrDefaultAsync(o => o.IdCardNumber == cleanNationId && o.IsLatest);

            if (oicProfile == null)
            {
                oicProfile = await _oicApiService.FetchAndProcessProfileAsync(cleanNationId);
            }

            if (person == null && oicProfile == null)
            {
                return NotFound(new { message = "ไม่พบข้อมูลเดิม" });
            }

            var oicData = new Dictionary<string, JsonElement>();
            if (oicProfile != null && !string.IsNullOrWhiteSpace(oicProfile.ProfilePayload))
            {
                try
                {
                    oicData = JsonSerializer.Deserialize<Dictionary<string, JsonElement>>(oicProfile.ProfilePayload) ?? new Dictionary<string, JsonElement>();
                }
                catch (Exception ex)
                {
                    Serilog.Log.Error(ex, "Failed to parse OIC profile payload for NationId {NationId}", cleanNationId);
                }
            }

            string? GetStr(string key) => oicData.TryGetValue(key, out var val) && val.ValueKind == JsonValueKind.String ? val.GetString() : null;
            List<string>? GetStrList(string key) => oicData.TryGetValue(key, out var val) && val.ValueKind == JsonValueKind.Array ? val.EnumerateArray().Where(x => x.ValueKind == JsonValueKind.String).Select(x => x.GetString()!).ToList() : null;

            var provinces = await _context.Provinces.ToListAsync();
            var districts = await _context.Districts.ToListAsync();
            var subDistricts = await _context.SubDistricts.ToListAsync();

            int? GetProvinceId(string? name) => string.IsNullOrEmpty(name) ? null : provinces.FirstOrDefault(x => x.ProvinceThai == name)?.ProvinceId;
            int? GetDistrictId(string? name) => string.IsNullOrEmpty(name) ? null : districts.FirstOrDefault(x => x.DistrictThai == name)?.DistrictId;
            int? GetSubDistrictId(string? name) => string.IsNullOrEmpty(name) ? null : subDistricts.FirstOrDefault(x => x.SubDistrictThai == name)?.SubDistrictId;

            int? GetGenderId(string? name) => name?.ToLower() switch {
                "male" or "ชาย" => 1,
                "female" or "หญิง" => 2,
                _ => null
            };

            int? GetReligionId(string? name) => name?.ToLower() switch {
                "buddhism" or "พุทธ" => 1,
                "christianity" or "คริสต์" => 2,
                "islam" or "อิสลาม" => 3,
                "hinduism" or "ฮินดู" => 4,
                "sikhism" or "ซิกข์" => 5,
                _ => 6
            };

            int? GetBloodId(string? name) => name?.ToUpper() switch {
                "A" => 1,
                "B" => 2,
                "O" => 3,
                "AB" => 4,
                _ => 5
            };

            // Map to flat structure for the frontend RegistrationForm
            var addressHouse = person?.Addresses.FirstOrDefault(a => a.AddressType == "A"); // ทะเบียนบ้าน
            var addressCurrent = person?.Addresses.FirstOrDefault(a => a.AddressType == "C"); // ปัจจุบัน
            var affiliation = person?.Affiliations.FirstOrDefault();
            var other = person?.Others.FirstOrDefault();

            var flatData = new
            {
                nationalId = person?.NationId ?? cleanNationId,
                idCardExpiry = GetStr("idCardExpiry") ?? person?.IdCardExpiry?.ToString("yyyy-MM-dd"),
                titleTh = GetStr("titleTh") ?? person?.TitleTh,
                firstNameTh = GetStr("firstNameTh") ?? person?.FirstNameTh,
                middleNameTh = GetStr("middleNameTh") ?? person?.MiddleNameTh,
                lastNameTh = GetStr("lastNameTh") ?? person?.LastNameTh,
                hasChangedName = !string.IsNullOrEmpty(person?.FirstNameOldTh) ? "yes" : "no",
                titlePrev = person?.TitleOldTh,
                firstNameOldTh = person?.FirstNameOldTh,
                middleNameOldTh = person?.MiddleNameOldTh,
                lastNameOldTh = person?.LastNameOldTh,
                birthDate = GetStr("birthDate") ?? person?.BirthDate?.ToString("yyyy-MM-dd"),
                religion = (GetReligionId(GetStr("religion")) ?? person?.ReligionId)?.ToString(),
                gender = (GetGenderId(GetStr("gender")) ?? person?.GenderId)?.ToString(),
                bloodGroup = (GetBloodId(GetStr("bloodGroup")) ?? person?.BloodGroupId)?.ToString(),
                phone = GetStr("phoneOtp") ?? person?.PhoneOtp,
                email = GetStr("email") ?? person?.EmailAlt,
                lineId = GetStr("lineId") ?? person?.LineId,
                facebook = GetStr("facebook") ?? person?.Facebook,
                instagram = GetStr("instagram") ?? person?.Instagram,
                foodAllergy = GetStr("foodAllergy") ?? person?.FoodAllergy,
                medicalCondition = GetStr("medicalCondition") ?? person?.MedicalCondition,
                emergencyContactName = GetStr("emergencyContactName") ?? person?.EmergencyContactName,
                emergencyContactPhone = GetStr("emergencyContactPhone") ?? person?.EmergencyContactPhone,
                
                // Tab 3 Address
                sameAddress = addressCurrent == null ? true : false,
                houseNo = GetStr("addrHouseNo") ?? addressHouse?.HouseNo,
                moo = GetStr("addrMoo") ?? addressHouse?.Moo,
                village = GetStr("addrVillage") ?? addressHouse?.Village,
                soi = GetStr("addrSoi") ?? addressHouse?.Soi,
                road = GetStr("addrRoad") ?? addressHouse?.Road,
                provinceId = (GetProvinceId(GetStr("addrProvince")) ?? addressHouse?.ProvinceId)?.ToString(),
                districtId = (GetDistrictId(GetStr("addrDistrict")) ?? addressHouse?.DistrictId)?.ToString(),
                subDistrictId = (GetSubDistrictId(GetStr("addrSubdistrict")) ?? addressHouse?.SubDistrictId)?.ToString(),
                zipcode = GetStr("addrPostcode") ?? addressHouse?.Postcode,

                shipHouseNo = addressCurrent?.HouseNo,
                shipMoo = addressCurrent?.Moo,
                shipVillage = addressCurrent?.Village,
                shipSoi = addressCurrent?.Soi,
                shipRoad = addressCurrent?.Road,
                shipprovinceId = addressCurrent?.ProvinceId?.ToString(),
                shipdistrictId = addressCurrent?.DistrictId?.ToString(),
                shipsubDistrictId = addressCurrent?.SubDistrictId?.ToString(),
                shipzipcode = addressCurrent?.Postcode,
                
                // Tab 4 License & Affiliation
                agentRegion = GetStr("agentRegion") ?? affiliation?.RegionId?.ToString(),
                agentBranch = GetStr("agentBranch") ?? affiliation?.BranchId?.ToString(),
                brokerAffiliation = GetStr("brokerAffiliation") ?? affiliation?.BrokerCompany,
                viriyaContractCode = GetStr("viriyaContractCode") ?? affiliation?.ViriyahAgentCode,
                brokerType = GetStr("brokerType") ?? affiliation?.BrokerType,

                // Licenses
                licenseNo = GetStr("licenseNo") ?? person?.Licenses.FirstOrDefault()?.LicenseNo,
                licenseIssue = GetStr("licenseIssue") ?? person?.Licenses.FirstOrDefault()?.LicenseIssueDate?.ToString("yyyy-MM-dd"),
                licenseExpire = GetStr("licenseExpire") ?? person?.Licenses.FirstOrDefault()?.LicenseExpiryDate?.ToString("yyyy-MM-dd"),
                agentType = GetStr("agentType") ?? person?.Licenses.FirstOrDefault()?.CourseType,
                
                // Tab 5 Course
                courseType = GetStr("courseType") ?? person?.Courses.FirstOrDefault(c => c.CourseId != null && c.CourseId != 9 && c.CourseId != 10)?.CourseId?.ToString() ?? person?.Courses.FirstOrDefault(c => c.CourseId == 9 || c.CourseId == 10)?.CourseId?.ToString(),
                trainingDate = GetStr("trainingDate") ?? person?.Courses.FirstOrDefault(c => c.CourseDateId != null)?.CourseDateId?.ToString(),
                selectedSubjects = GetStrList("selectedSubjects") ?? (person?.Courses.Where(c => c.RenewOtherId != null).Select(c => c.RenewOtherId?.ToString() ?? "").Where(s => !string.IsNullOrEmpty(s)).ToList() ?? new List<string>()),
                previousCourses = GetStrList("previousCourses") ?? (person?.Trainings.Where(t => t.CourseId != null).Select(t => t.CourseId?.ToString() ?? "").Where(s => !string.IsNullOrEmpty(s)).ToList() ?? new List<string>()),

                // Other
                insuranceExperienceYears = GetStr("insuranceExperienceYears") ?? other?.InsuranceExperienceYears?.ToString(),
                occupation = GetStr("occupation") ?? other?.OtherBusiness,
                brokerBranch = GetStr("brokerBranch") ?? other?.BrokerBranch,
                extraTrainingInterest = GetStr("extraTrainingInterest") ?? other?.ExtraTrainingInterest,
                
                salesTerritories = GetStrList("salesTerritories") ?? (other?.SalesAreas.Where(s => s.TerritoriesId != null).Select(s => s.TerritoriesId.ToString() ?? "").Where(s => !string.IsNullOrEmpty(s)).ToList() ?? new List<string>()),
                otherInsuranceCompanies = GetStrList("otherInsuranceCompanies") ?? (other?.OtherCompanies.Where(c => c.CompanyId != null).Select(c => c.CompanyId.ToString() ?? "").Where(s => !string.IsNullOrEmpty(s)).ToList() ?? new List<string>()),
                insuranceSpecialty = GetStrList("insuranceSpecialty") ?? (other?.Specialties.Where(s => s.ExpertiseId != null).Select(s => s.ExpertiseId.ToString() ?? "").Where(s => !string.IsNullOrEmpty(s)).ToList() ?? new List<string>())
            };

            return Ok(flatData);
        }
    }
}

