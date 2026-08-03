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
            var profile = await _oicApiService.GetProfileFromDbAsync(cleanNationId);
            if (profile == null)
            {
                return BadRequest(new { message = "ยังไม่มีข้อมูลจาก OIC (กรุณานำไฟล์ไปวางให้ Worker ทำงานก่อน)" });
            }
            return Ok(new { message = "ดึงข้อมูลล่าสุดจาก Database สำเร็จ", profile });
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

            // oicProfile will be null if the Background Worker hasn't processed the file yet.
            if (person == null && oicProfile == null)
            {
                return NotFound(new { message = "ไม่พบข้อมูลเดิม และยังไม่มีข้อมูลจาก OIC (Worker ยังไม่ได้ทำงาน)" });
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

            bool useDb = person != null;

            var flatData = new
            {
                nationalId = useDb ? person.NationId : cleanNationId,
                idCardExpiry = useDb ? person.IdCardExpiry?.ToString("yyyy-MM-dd") : GetStr("idCardExpiry"),
                titleTh = useDb ? person.TitleTh : GetStr("titleTh"),
                firstNameTh = useDb ? person.FirstNameTh : GetStr("firstNameTh"),
                middleNameTh = useDb ? person.MiddleNameTh : GetStr("middleNameTh"),
                lastNameTh = useDb ? person.LastNameTh : GetStr("lastNameTh"),
                hasChangedName = !string.IsNullOrEmpty(person?.FirstNameOldTh) ? "yes" : "no",
                titlePrev = person?.TitleOldTh,
                firstNameOldTh = person?.FirstNameOldTh,
                middleNameOldTh = person?.MiddleNameOldTh,
                lastNameOldTh = person?.LastNameOldTh,
                birthDate = useDb ? person.BirthDate?.ToString("yyyy-MM-dd") : GetStr("birthDate"),
                religion = useDb ? person.ReligionId?.ToString() : (GetReligionId(GetStr("religion")))?.ToString(),
                gender = useDb ? person.GenderId?.ToString() : (GetGenderId(GetStr("gender")))?.ToString(),
                bloodGroup = useDb ? person.BloodGroupId?.ToString() : (GetBloodId(GetStr("bloodGroup")))?.ToString(),
                phone = useDb ? person.PhoneOtp : GetStr("phoneOtp"),
                email = useDb ? person.EmailAlt : GetStr("email"),
                lineId = useDb ? person.LineId : GetStr("lineId"),
                facebook = useDb ? person.Facebook : GetStr("facebook"),
                instagram = useDb ? person.Instagram : GetStr("instagram"),
                foodAllergy = useDb ? person.FoodAllergy : GetStr("foodAllergy"),
                medicalCondition = useDb ? person.MedicalCondition : GetStr("medicalCondition"),
                emergencyContactName = useDb ? person.EmergencyContactName : GetStr("emergencyContactName"),
                emergencyContactPhone = useDb ? person.EmergencyContactPhone : GetStr("emergencyContactPhone"),
                
                // Tab 3 Address
                sameAddress = addressCurrent == null ? true : false,
                houseNo = useDb ? addressHouse?.HouseNo : GetStr("addrHouseNo"),
                moo = useDb ? addressHouse?.Moo : GetStr("addrMoo"),
                village = useDb ? addressHouse?.Village : GetStr("addrVillage"),
                soi = useDb ? addressHouse?.Soi : GetStr("addrSoi"),
                road = useDb ? addressHouse?.Road : GetStr("addrRoad"),
                provinceId = useDb ? addressHouse?.ProvinceId?.ToString() : (GetProvinceId(GetStr("addrProvince")))?.ToString(),
                districtId = useDb ? addressHouse?.DistrictId?.ToString() : (GetDistrictId(GetStr("addrDistrict")))?.ToString(),
                subDistrictId = useDb ? addressHouse?.SubDistrictId?.ToString() : (GetSubDistrictId(GetStr("addrSubdistrict")))?.ToString(),
                zipcode = useDb ? addressHouse?.Postcode : GetStr("addrPostcode"),

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
                agentRegion = useDb ? affiliation?.RegionId?.ToString() : GetStr("agentRegion"),
                agentBranch = useDb ? affiliation?.BranchId?.ToString() : GetStr("agentBranch"),
                brokerAffiliation = useDb ? affiliation?.BrokerCompany : GetStr("brokerAffiliation"),
                viriyaContractCode = useDb ? affiliation?.ViriyahAgentCode : GetStr("viriyaContractCode"),
                brokerType = useDb ? affiliation?.BrokerType : GetStr("brokerType"),

                // Licenses
                licenseNo = useDb ? person.Licenses.FirstOrDefault()?.LicenseNo : GetStr("licenseNo"),
                licenseIssue = useDb ? person.Licenses.FirstOrDefault()?.LicenseIssueDate?.ToString("yyyy-MM-dd") : GetStr("licenseIssue"),
                licenseExpire = useDb ? person.Licenses.FirstOrDefault()?.LicenseExpiryDate?.ToString("yyyy-MM-dd") : GetStr("licenseExpire"),
                agentType = useDb ? person.Licenses.FirstOrDefault()?.CourseType : GetStr("agentType"),
                
                // Tab 5 Course
                courseType = useDb ? (person.Courses.FirstOrDefault(c => c.CourseId != null && c.CourseId != 9 && c.CourseId != 10)?.CourseId?.ToString() ?? person.Courses.FirstOrDefault(c => c.CourseId == 9 || c.CourseId == 10)?.CourseId?.ToString()) : GetStr("courseType"),
                trainingDate = useDb ? person.Courses.FirstOrDefault(c => c.CourseDateId != null)?.CourseDateId?.ToString() : GetStr("trainingDate"),
                selectedSubjects = useDb ? (person.Courses.Any(c => c.RenewOtherId != null) ? person.Courses.Where(c => c.RenewOtherId != null).Select(c => c.RenewOtherId?.ToString() ?? "").Where(s => !string.IsNullOrEmpty(s)).ToList() : new List<string>()) : (GetStrList("selectedSubjects") ?? new List<string>()),
                previousCourses = useDb ? (person.Trainings.Any(t => t.CourseId != null) ? person.Trainings.Where(t => t.CourseId != null).Select(t => t.CourseId?.ToString() ?? "").Where(s => !string.IsNullOrEmpty(s)).ToList() : new List<string>()) : (GetStrList("previousCourses") ?? new List<string>()),

                // Other
                insuranceExperienceYears = useDb ? other?.InsuranceExperienceYears?.ToString() : GetStr("insuranceExperienceYears"),
                occupation = useDb ? other?.OtherBusiness : GetStr("occupation"),
                brokerBranch = useDb ? other?.BrokerBranch : GetStr("brokerBranch"),
                extraTrainingInterest = useDb ? other?.ExtraTrainingInterest : GetStr("extraTrainingInterest"),
                
                salesTerritories = useDb ? (other?.SalesAreas.Any(s => s.TerritoriesId != null) == true ? other.SalesAreas.Where(s => s.TerritoriesId != null).Select(s => s.TerritoriesId.ToString() ?? "").Where(s => !string.IsNullOrEmpty(s)).ToList() : new List<string>()) : (GetStrList("salesTerritories") ?? new List<string>()),
                otherInsuranceCompanies = useDb ? (other?.OtherCompanies.Any(c => c.CompanyId != null) == true ? other.OtherCompanies.Where(c => c.CompanyId != null).Select(c => c.CompanyId.ToString() ?? "").Where(s => !string.IsNullOrEmpty(s)).ToList() : new List<string>()) : (GetStrList("otherInsuranceCompanies") ?? new List<string>()),
                insuranceSpecialty = useDb ? (other?.Specialties.Any(s => s.ExpertiseId != null) == true ? other.Specialties.Where(s => s.ExpertiseId != null).Select(s => s.ExpertiseId.ToString() ?? "").Where(s => !string.IsNullOrEmpty(s)).ToList() : new List<string>()) : (GetStrList("insuranceSpecialty") ?? new List<string>())
            };

            return Ok(flatData);
        }
    }
}

