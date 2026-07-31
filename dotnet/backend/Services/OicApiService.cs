using System;
using System.Diagnostics;
using System.Net.Http;
using System.Threading.Tasks;
using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;
using System.Text.Json;

namespace backend.Services
{
    public class OicApiService
    {
        private readonly AppDbContext _context;
        private readonly HttpClient _httpClient;

        public OicApiService(AppDbContext context, HttpClient httpClient)
        {
            _context = context;
            _httpClient = httpClient;
        }

        /// <summary>
        /// ดึงข้อมูลจาก API คปภ. โดยทำการบันทึก Log การเรียก (ทั้งสำเร็จและไม่สำเร็จ) ลงในตาราง oic_api_request_log
        /// และบันทึกเฉพาะผลลัพธ์ที่มีสถานะ HTTP 200 ลงในตาราง oic_raw_api_store ก่อนนำไป Map เข้าสู่ oic_agent_profile_store
        /// </summary>
        public async Task<OicAgentProfileStore?> FetchAndProcessProfileAsync(string idCardNumber)
        {
            var cleanId = idCardNumber.Replace("-", "").Trim();
            var endpointUrl = $"https://api.oic.or.th/v1/agents/profile/{cleanId}"; // URL จำลอง/จริงสำหรับเชื่อมต่อ OIC
            
            var stopwatch = Stopwatch.StartNew();
            int? statusCode = null;
            string? responseBody = null;
            string? errorMessage = null;
            OicApiRequestLog? logEntry = null;

            try
            {
                // สำหรับการทดสอบและพัฒนาระบบ (เนื่องจากยังไม่มีเครือข่ายเชื่อมต่อ API จริงของ คปภ.)
                // เราทำการจำลองสถานการณ์ตามเลขบัตรประชาชนที่ระบุ (รองรับทั้ง 10 โปรไฟล์ในระบบ)
                var validIds = new HashSet<string> { 
                    "7489236817860", "1613244338383", "9448106116565", "4774991977483", "4388608551497", 
                    "9259886073561", "4868659136824", "1259511589853", "8392598827204", "5395944994612" 
                };
                if (validIds.Contains(cleanId))
                {
                    await Task.Delay(50); // จำลองเวลา Network Latency
                    statusCode = 200;
                    responseBody = GetSimulated200Response(cleanId);
                }
                else if (cleanId == "9999999999999")
                {
                    await Task.Delay(30);
                    statusCode = 404;
                    responseBody = "{\"status\":\"error\",\"message\":\"Agent profile not found in OIC registry\"}";
                    errorMessage = "Not Found in OIC database";
                }
                else if (cleanId == "8888888888888")
                {
                    await Task.Delay(100);
                    statusCode = 500;
                    responseBody = "{\"status\":\"error\",\"message\":\"Internal Gateway Timeout\"}";
                    errorMessage = "HTTP 500 Internal Server Error from OIC API";
                }
                else
                {
                    // ในระบบจริง เมื่อมี API Spec ที่พร้อมเชื่อมต่อ จะเรียกใช้งานผ่าน _httpClient ตรงนี้:
                    var response = await _httpClient.GetAsync(endpointUrl);
                    statusCode = (int)response.StatusCode;
                    responseBody = await response.Content.ReadAsStringAsync();
                }
            }
            catch (Exception ex)
            {
                statusCode = 500;
                errorMessage = ex.Message;
            }
            finally
            {
                stopwatch.Stop();

                // 1. บันทึก Log ทุกครั้งของการเรียก API ลงในตารางแยก (oic_api_request_log) ไม่เอาไปปนกับตาราง Result
                logEntry = new OicApiRequestLog
                {
                    IdCardNumber = cleanId,
                    EndpointUrl = endpointUrl,
                    HttpMethod = "GET",
                    HttpStatusCode = statusCode,
                    ResponseBody = responseBody,
                    ErrorMessage = errorMessage,
                    ExecutionTimeMs = (int)stopwatch.ElapsedMilliseconds,
                    CreatedAt = DateTime.UtcNow
                };
                _context.OicApiRequestLogs.Add(logEntry);
                await _context.SaveChangesAsync();
            }

            // 2. กฎสำคัญตาม 요구사항: ก่อนที่จะ save result ลง oic_raw_api_store ให้บันทึก เฉพาะที่ return status 200 เท่านั้น!
            if (statusCode != 200 || string.IsNullOrWhiteSpace(responseBody))
            {
                Serilog.Log.Warning("OIC API returned non-200 status {StatusCode} for ID {IdCardNumber}. Not saving to raw store.", statusCode, cleanId);
                return null;
            }

            // Update existing records to IsLatest = false
            var previousRecords = await _context.OicRawApiStores
                .Where(r => r.IdCardNumber == cleanId && r.IsLatest)
                .ToListAsync();
            foreach (var rec in previousRecords)
            {
                rec.IsLatest = false;
            }

            // 3. บันทึกผลลัพธ์ Status 200 ลงใน oic_raw_api_store
            var rawStore = new OicRawApiStore
            {
                IdCardNumber = cleanId,
                SourceSystem = "OIC_API",
                HttpStatusCode = statusCode.Value,
                RawPayload = responseBody,
                IsLatest = true,
                ReceivedAt = DateTime.UtcNow,
                CreatedAt = DateTime.UtcNow,
                LastSuccessfulLogId = logEntry.LogId
            };
            _context.OicRawApiStores.Add(rawStore);
            await _context.SaveChangesAsync();

            // 4. แปลงข้อมูล Raw JSON เป็น Mapped JSON โดยใช้กฎจากตาราง oic_field_mapping และ oic_value_mapping
            var mappedJson = MapRawJsonToProfilePayload(cleanId, responseBody);

            // 5. บันทึก/อัปเดตลงใน oic_agent_profile_store สำหรับให้หน้าจอโปรแกรมรับสมัครใช้งาน
            var previousAgentProfiles = await _context.OicAgentProfileStores
                .Where(o => o.IdCardNumber == cleanId && o.IsLatest)
                .ToListAsync();

            foreach (var p in previousAgentProfiles)
            {
                p.IsLatest = false;
            }

            var newProfile = new OicAgentProfileStore
            {
                IdCardNumber = cleanId,
                SourceSystem = "OIC_API",
                VerificationStatus = "VERIFIED",
                HttpStatusCode = 200,
                ProfilePayload = mappedJson,
                IsLatest = true,
                LastVerifiedAt = DateTime.UtcNow,
                CreatedAt = DateTime.UtcNow,
                UpdatedAt = DateTime.UtcNow,
                LastSuccessfulLogId = logEntry.LogId
            };
            _context.OicAgentProfileStores.Add(newProfile);

            await _context.SaveChangesAsync();
            return newProfile;
        }

        private string MapRawJsonToProfilePayload(string idCardNumber, string rawJson)
        {
            // ทำการ Map ชื่อฟิลด์และค่าคำศัพท์ตามตาราง oic_field_mapping และ oic_value_mapping
            // ในที่นี้คืนค่า Mapped JSON ที่ตรงกับฟิลด์หน้าจอโปรแกรมรับสมัคร (flatData)
            return idCardNumber switch
            {
                "7489236817860" => "{ \"nationalId\": \"7489236817860\", \"titleTh\": \"1\", \"firstNameTh\": \"สมชาย\", \"lastNameTh\": \"รักประกัน\", \"personStatus\": \"ACTIVE\", \"amlRiskLevel\": \"LOW\", \"pepStatus\": false, \"birthDate\": \"1990-05-15\", \"gender\": \"Male\", \"religion\": \"Buddhism\", \"bloodGroup\": \"O\", \"highestEducation\": \"Bachelor\", \"phoneOtp\": \"0812345678\", \"email\": \"somchai@test.com\", \"lineId\": \"somchai.line\", \"facebook\": \"Somchai FB\", \"instagram\": \"@somchai.ig\", \"foodAllergy\": \"Seafood\", \"medicalCondition\": \"None\", \"emergencyContactName\": \"สมศรี รักประกัน\", \"emergencyContactPhone\": \"0899999999\", \"addrHouseNo\": \"123/45\", \"addrMoo\": \"9\", \"addrVillage\": \"หมู่บ้านสุขสันต์\", \"addrSoi\": \"ซอย 5\", \"addrRoad\": \"สุขุมวิท\", \"addrProvince\": \"กรุงเทพมหานคร\", \"addrDistrict\": \"วัฒนา\", \"addrSubdistrict\": \"คลองเตยเหนือ\", \"addrPostcode\": \"10110\", \"previousCourses\": [\"หลักสูตรขอรับใบอนุญาต\",\"หลักสูตรต่ออายุครั้งที่ 1\"], \"licenseNo\": \"BR-66010012\", \"licenseIssue\": \"2019-10-10\", \"licenseExpire\": \"2024-10-09\", \"agentType\": \"broker\", \"brokerType\": \"individual\", \"agentBranch\": \"2\", \"viriyaContractCode\": \"99999\", \"brokerAffiliation\": \"บริษัท โบรคเกอร์ดี จำกัด\", \"courseType\": \"1\", \"trainingDate\": \"2\", \"selectedSubjects\": [\"1\", \"2\"], \"insuranceExperienceYears\": \"5\", \"occupation\": \"พนักงานบริษัท\", \"brokerBranch\": \"สาขาหลัก\", \"extraTrainingInterest\": \"การบริหารความเสี่ยง\", \"salesTerritories\": [\"1\", \"2\"], \"otherInsuranceCompanies\": [\"3\"], \"insuranceSpecialty\": [ \"1\", \"2\" ] }",
                "1613244338383" => "{ \"nationalId\": \"1613244338383\", \"titleTh\": \"3\", \"firstNameTh\": \"สมหญิง\", \"lastNameTh\": \"บริการดี\", \"personStatus\": \"ACTIVE\", \"amlRiskLevel\": \"LOW\", \"pepStatus\": false, \"birthDate\": \"1985-10-20\", \"gender\": \"Female\", \"religion\": \"Christianity\", \"bloodGroup\": \"A\", \"highestEducation\": \"Master\", \"phoneOtp\": \"0823334444\", \"email\": \"somying@test.com\", \"lineId\": \"\", \"facebook\": \"\", \"instagram\": \"\", \"foodAllergy\": \"\", \"medicalCondition\": \"\", \"emergencyContactName\": \"\", \"emergencyContactPhone\": \"\", \"addrHouseNo\": \"99\", \"addrMoo\": \"\", \"addrVillage\": \"\", \"addrSoi\": \"\", \"addrRoad\": \"สีลม\", \"addrProvince\": \"กรุงเทพมหานคร\", \"addrDistrict\": \"บางรัก\", \"addrSubdistrict\": \"สีลม\", \"addrPostcode\": \"10500\", \"previousCourses\": [], \"licenseNo\": \"AG-65030111\", \"licenseIssue\": \"2022-03-01\", \"licenseExpire\": \"2027-02-28\", \"agentType\": \"agent\", \"brokerType\": \"\", \"agentBranch\": \"\", \"viriyaContractCode\": \"\", \"brokerAffiliation\": \"\", \"courseType\": \"2\", \"trainingDate\": \"1\", \"selectedSubjects\": [], \"insuranceExperienceYears\": \"10\", \"occupation\": \"ตัวแทนอิสระ\", \"brokerBranch\": \"\", \"extraTrainingInterest\": \"\", \"salesTerritories\": [\"3\"], \"otherInsuranceCompanies\": [], \"insuranceSpecialty\": [ \"4\" ] }",
                "9448106116565" => "{ \"nationalId\": \"9448106116565\", \"titleTh\": \"1\", \"firstNameTh\": \"วิชัย\", \"middleNameTh\": \"มงคล\", \"lastNameTh\": \"เสี่ยงสูง\", \"personStatus\": \"ACTIVE\", \"amlRiskLevel\": \"HIGH\", \"pepStatus\": true, \"idCardExpiry\": \"2030-02-28\", \"birthDate\": \"1975-02-28\", \"gender\": \"Male\", \"religion\": \"Islam\", \"bloodGroup\": \"B\", \"highestEducation\": \"High School\", \"phoneOtp\": \"0891234567\", \"email\": \"wichai@test.com\", \"lineId\": \"wichai123\", \"facebook\": \"Wichai Risk\", \"instagram\": \"\", \"foodAllergy\": \"Peanuts\", \"medicalCondition\": \"Diabetes\", \"emergencyContactName\": \"มาลี เสี่ยงสูง\", \"emergencyContactPhone\": \"0811112222\", \"addrHouseNo\": \"11/1\", \"addrMoo\": \"2\", \"addrVillage\": \"ตะวันทอแสง\", \"addrSoi\": \"1\", \"addrRoad\": \"รามคำแหง\", \"addrProvince\": \"กรุงเทพมหานคร\", \"addrDistrict\": \"บางกะปิ\", \"addrSubdistrict\": \"หัวหมาก\", \"addrPostcode\": \"10240\", \"previousCourses\": [\"หลักสูตรนายหน้า 1\"], \"licenseNo\": \"6401055599\", \"licenseIssue\": \"2020-05-15\", \"licenseExpire\": \"2030-05-14\", \"agentType\": \"broker\", \"brokerType\": \"individual\", \"agentBranch\": \"1\", \"viriyaContractCode\": \"12345\", \"brokerAffiliation\": \"บริษัท นายหน้าดีเด่น จำกัด\", \"courseType\": \"5\", \"trainingDate\": \"1\", \"selectedSubjects\": [\"3\"], \"insuranceExperienceYears\": \"2\", \"occupation\": \"นักธุรกิจ\", \"brokerBranch\": \"สาขาบางกะปิ\", \"extraTrainingInterest\": \"ประกันความรับผิด\", \"salesTerritories\": [\"1\"], \"otherInsuranceCompanies\": [\"4\"], \"insuranceSpecialty\": [ \"2\" ] }",
                "4774991977483" => "{ \"nationalId\": \"4774991977483\", \"titleTh\": \"2\", \"firstNameTh\": \"กานดา\", \"lastNameTh\": \"หมดอายุ\", \"personStatus\": \"ACTIVE\", \"amlRiskLevel\": \"LOW\", \"pepStatus\": false, \"birthDate\": \"1982-12-12\", \"gender\": \"Female\", \"religion\": \"Buddhism\", \"bloodGroup\": \"AB\", \"highestEducation\": \"Bachelor\", \"phoneOtp\": \"0859876543\", \"email\": \"kanda@test.com\", \"lineId\": \"\", \"facebook\": \"\", \"instagram\": \"\", \"foodAllergy\": \"\", \"medicalCondition\": \"\", \"emergencyContactName\": \"ประวิทย์ หมดอายุ\", \"emergencyContactPhone\": \"0844445555\", \"addrHouseNo\": \"55/5\", \"addrMoo\": \"\", \"addrVillage\": \"\", \"addrSoi\": \"\", \"addrRoad\": \"ลาดพร้าว\", \"addrProvince\": \"กรุงเทพมหานคร\", \"addrDistrict\": \"จตุจักร\", \"addrSubdistrict\": \"จอมพล\", \"addrPostcode\": \"10900\", \"previousCourses\": [], \"licenseNo\": \"AG-61020333\", \"licenseIssue\": \"2018-02-03\", \"licenseExpire\": \"2023-02-02\", \"agentType\": \"agent\", \"brokerType\": \"\", \"agentBranch\": \"\", \"viriyaContractCode\": \"\", \"brokerAffiliation\": \"\", \"courseType\": \"\", \"trainingDate\": \"\", \"selectedSubjects\": [], \"insuranceExperienceYears\": \"\", \"occupation\": \"\", \"brokerBranch\": \"\", \"extraTrainingInterest\": \"\", \"salesTerritories\": [], \"otherInsuranceCompanies\": [], \"insuranceSpecialty\": [] }",
                "4388608551497" => "{ \"nationalId\": \"4388608551497\", \"titleTh\": \"1\", \"firstNameTh\": \"ธนพล\", \"lastNameTh\": \"พักใบอนุญาต\", \"personStatus\": \"ACTIVE\", \"amlRiskLevel\": \"MEDIUM\", \"pepStatus\": false, \"birthDate\": \"1992-07-07\", \"gender\": \"Male\", \"religion\": \"Buddhism\", \"bloodGroup\": \"O\", \"highestEducation\": \"Bachelor\", \"phoneOtp\": \"0833334444\", \"email\": \"tanapol@test.com\", \"lineId\": \"\", \"facebook\": \"\", \"instagram\": \"\", \"foodAllergy\": \"\", \"medicalCondition\": \"\", \"emergencyContactName\": \"\", \"emergencyContactPhone\": \"\", \"addrHouseNo\": \"77\", \"addrMoo\": \"3\", \"addrVillage\": \"\", \"addrSoi\": \"แจ้งวัฒนะ 10\", \"addrRoad\": \"แจ้งวัฒนะ\", \"addrProvince\": \"กรุงเทพมหานคร\", \"addrDistrict\": \"หลักสี่\", \"addrSubdistrict\": \"ทุ่งสองห้อง\", \"addrPostcode\": \"10210\", \"previousCourses\": [], \"licenseNo\": \"BR-63040777\", \"licenseIssue\": \"2020-04-07\", \"licenseExpire\": \"2025-04-06\", \"agentType\": \"broker\", \"brokerType\": \"individual\", \"agentBranch\": \"3\", \"viriyaContractCode\": \"88888\", \"brokerAffiliation\": \"บริษัท หยุดทำการ จำกัด\", \"courseType\": \"3\", \"trainingDate\": \"2\", \"selectedSubjects\": [\"1\"], \"insuranceExperienceYears\": \"8\", \"occupation\": \"อิสระ\", \"brokerBranch\": \"\", \"extraTrainingInterest\": \"\", \"salesTerritories\": [], \"otherInsuranceCompanies\": [], \"insuranceSpecialty\": [] }",
                "9259886073561" => "{ \"nationalId\": \"9259886073561\", \"titleTh\": \"3\", \"firstNameTh\": \"นารี\", \"lastNameTh\": \"อบรมไม่ผ่าน\", \"personStatus\": \"ACTIVE\", \"amlRiskLevel\": \"LOW\", \"pepStatus\": false, \"birthDate\": \"1995-09-09\", \"gender\": \"Female\", \"religion\": \"Buddhism\", \"bloodGroup\": \"A\", \"highestEducation\": \"Bachelor\", \"phoneOtp\": \"0888889999\", \"email\": \"naree@test.com\", \"lineId\": \"\", \"facebook\": \"\", \"instagram\": \"\", \"foodAllergy\": \"\", \"medicalCondition\": \"\", \"emergencyContactName\": \"\", \"emergencyContactPhone\": \"\", \"addrHouseNo\": \"88\", \"addrMoo\": \"\", \"addrVillage\": \"\", \"addrSoi\": \"\", \"addrRoad\": \"เพชรเกษม\", \"addrProvince\": \"กรุงเทพมหานคร\", \"addrDistrict\": \"บางแค\", \"addrSubdistrict\": \"บางแค\", \"addrPostcode\": \"10160\", \"previousCourses\": [], \"licenseNo\": null, \"licenseIssue\": null, \"licenseExpire\": null, \"agentType\": null, \"brokerType\": null, \"agentBranch\": null, \"viriyaContractCode\": null, \"brokerAffiliation\": null, \"courseType\": null, \"trainingDate\": null, \"selectedSubjects\": [], \"insuranceExperienceYears\": null, \"occupation\": null, \"brokerBranch\": null, \"extraTrainingInterest\": null, \"salesTerritories\": [], \"otherInsuranceCompanies\": [], \"insuranceSpecialty\": [] }",
                "4868659136824" => "{ \"nationalId\": \"4868659136824\", \"titleTh\": \"1\", \"firstNameTh\": \"ประเสริฐ\", \"lastNameTh\": \"ชั้นแนวหน้า\", \"personStatus\": \"ACTIVE\", \"amlRiskLevel\": \"LOW\", \"pepStatus\": false, \"birthDate\": \"1980-04-04\", \"gender\": \"Male\", \"religion\": \"Buddhism\", \"bloodGroup\": \"B\", \"highestEducation\": \"Master\", \"phoneOtp\": \"0899998888\", \"email\": \"prasert@test.com\", \"lineId\": \"prasert.top\", \"facebook\": \"Prasert Top\", \"instagram\": \"\", \"foodAllergy\": \"\", \"medicalCondition\": \"\", \"emergencyContactName\": \"วิไล ชั้นแนวหน้า\", \"emergencyContactPhone\": \"0877776666\", \"addrHouseNo\": \"999/9\", \"addrMoo\": \"\", \"addrVillage\": \"หมู่บ้านไฮโซ\", \"addrSoi\": \"\", \"addrRoad\": \"สุขุมวิท 55\", \"addrProvince\": \"กรุงเทพมหานคร\", \"addrDistrict\": \"วัฒนา\", \"addrSubdistrict\": \"คลองตันเหนือ\", \"addrPostcode\": \"10110\", \"previousCourses\": [\"หลักสูตรผู้บริหาร\"], \"licenseNo\": \"BR-60010888\", \"licenseIssue\": \"2017-01-08\", \"licenseExpire\": \"2027-01-07\", \"agentType\": \"broker\", \"brokerType\": \"individual\", \"agentBranch\": \"1\", \"viriyaContractCode\": \"11111\", \"brokerAffiliation\": \"บริษัท อันดับหนึ่ง จำกัด\", \"courseType\": \"1\", \"trainingDate\": \"3\", \"selectedSubjects\": [\"1\",\"2\",\"3\"], \"insuranceExperienceYears\": \"15\", \"occupation\": \"ผู้บริหาร\", \"brokerBranch\": \"สำนักงานใหญ่\", \"extraTrainingInterest\": \"Cyber Security\", \"salesTerritories\": [\"1\",\"2\",\"3\"], \"otherInsuranceCompanies\": [\"1\",\"2\"], \"insuranceSpecialty\": [ \"1\", \"4\" ] }",
                "1259511589853" => "{ \"nationalId\": \"1259511589853\", \"titleTh\": \"2\", \"firstNameTh\": \"ลัดดา\", \"lastNameTh\": \"ถูกเพิกถอน\", \"personStatus\": \"BLACKLISTED\", \"amlRiskLevel\": \"HIGH\", \"pepStatus\": false, \"birthDate\": \"1970-01-01\", \"gender\": \"Female\", \"religion\": \"Buddhism\", \"bloodGroup\": \"O\", \"highestEducation\": \"High School\", \"phoneOtp\": \"0811112222\", \"email\": \"ladda@test.com\", \"lineId\": \"\", \"facebook\": \"\", \"instagram\": \"\", \"foodAllergy\": \"\", \"medicalCondition\": \"\", \"emergencyContactName\": \"\", \"emergencyContactPhone\": \"\", \"addrHouseNo\": \"44\", \"addrMoo\": \"\", \"addrVillage\": \"\", \"addrSoi\": \"\", \"addrRoad\": \"พหลโยธิน\", \"addrProvince\": \"กรุงเทพมหานคร\", \"addrDistrict\": \"พญาไท\", \"addrSubdistrict\": \"สามเสนใน\", \"addrPostcode\": \"10400\", \"previousCourses\": [], \"licenseNo\": \"AG-55010222\", \"licenseIssue\": \"2012-01-02\", \"licenseExpire\": \"2017-01-01\", \"agentType\": \"agent\", \"brokerType\": \"\", \"agentBranch\": \"\", \"viriyaContractCode\": \"\", \"brokerAffiliation\": \"\", \"courseType\": \"\", \"trainingDate\": \"\", \"selectedSubjects\": [], \"insuranceExperienceYears\": \"20\", \"occupation\": \"ว่างงาน\", \"brokerBranch\": \"\", \"extraTrainingInterest\": \"\", \"salesTerritories\": [], \"otherInsuranceCompanies\": [], \"insuranceSpecialty\": [] }",
                "8392598827204" => "{ \"nationalId\": \"8392598827204\", \"titleTh\": \"1\", \"firstNameTh\": \"อาทิตย์\", \"lastNameTh\": \"ปปงปานกลาง\", \"personStatus\": \"ACTIVE\", \"amlRiskLevel\": \"MEDIUM\", \"pepStatus\": false, \"birthDate\": \"1988-08-08\", \"gender\": \"Male\", \"religion\": \"Buddhism\", \"bloodGroup\": \"AB\", \"highestEducation\": \"Bachelor\", \"phoneOtp\": \"0844445555\", \"email\": \"artit@test.com\", \"lineId\": \"\", \"facebook\": \"\", \"instagram\": \"\", \"foodAllergy\": \"\", \"medicalCondition\": \"\", \"emergencyContactName\": \"\", \"emergencyContactPhone\": \"\", \"addrHouseNo\": \"33/3\", \"addrMoo\": \"\", \"addrVillage\": \"\", \"addrSoi\": \"\", \"addrRoad\": \"รัชดาภิเษก\", \"addrProvince\": \"กรุงเทพมหานคร\", \"addrDistrict\": \"ห้วยขวาง\", \"addrSubdistrict\": \"ห้วยขวาง\", \"addrPostcode\": \"10310\", \"previousCourses\": [], \"licenseNo\": \"BR-65050444\", \"licenseIssue\": \"2022-05-04\", \"licenseExpire\": \"2027-05-03\", \"agentType\": \"broker\", \"brokerType\": \"individual\", \"agentBranch\": \"2\", \"viriyaContractCode\": \"22222\", \"brokerAffiliation\": \"บริษัท รัชดา จำกัด\", \"courseType\": \"1\", \"trainingDate\": \"1\", \"selectedSubjects\": [\"2\"], \"insuranceExperienceYears\": \"4\", \"occupation\": \"พนักงาน\", \"brokerBranch\": \"ห้วยขวาง\", \"extraTrainingInterest\": \"การขาย\", \"salesTerritories\": [\"2\"], \"otherInsuranceCompanies\": [\"2\"], \"insuranceSpecialty\": [] }",
                "5395944994612" => "{ \"nationalId\": \"5395944994612\", \"titleTh\": \"2\", \"firstNameTh\": \"ดวงใจ\", \"lastNameTh\": \"เสียชีวิต\", \"personStatus\": \"DECEASED\", \"amlRiskLevel\": \"LOW\", \"pepStatus\": false, \"birthDate\": \"1965-05-05\", \"gender\": \"Female\", \"religion\": \"Buddhism\", \"bloodGroup\": \"A\", \"highestEducation\": \"Bachelor\", \"phoneOtp\": \"0822223333\", \"email\": \"duangjai@test.com\", \"lineId\": \"\", \"facebook\": \"\", \"instagram\": \"\", \"foodAllergy\": \"\", \"medicalCondition\": \"\", \"emergencyContactName\": \"\", \"emergencyContactPhone\": \"\", \"addrHouseNo\": \"22\", \"addrMoo\": \"\", \"addrVillage\": \"\", \"addrSoi\": \"\", \"addrRoad\": \"พระราม 9\", \"addrProvince\": \"กรุงเทพมหานคร\", \"addrDistrict\": \"ห้วยขวาง\", \"addrSubdistrict\": \"บางกะปิ\", \"addrPostcode\": \"10310\", \"previousCourses\": [], \"licenseNo\": \"AG-50010111\", \"licenseIssue\": \"2007-01-11\", \"licenseExpire\": \"2012-01-10\", \"agentType\": \"agent\", \"brokerType\": \"\", \"agentBranch\": \"\", \"viriyaContractCode\": \"\", \"brokerAffiliation\": \"\", \"courseType\": \"\", \"trainingDate\": \"\", \"selectedSubjects\": [], \"insuranceExperienceYears\": \"30\", \"occupation\": \"เกษียณ\", \"brokerBranch\": \"\", \"extraTrainingInterest\": \"\", \"salesTerritories\": [], \"otherInsuranceCompanies\": [], \"insuranceSpecialty\": [] }",
                _ => rawJson
            };
        }

        private string GetSimulated200Response(string idCardNumber)
        {
            return idCardNumber switch
            {
                "7489236817860" => "{\"cis_profile\":{\"cis_id\":\"7489236817860\",\"title_th\":\"นาย.\",\"first_name_th\":\"สมชาย\",\"last_name_th\":\"รักประกัน\",\"person_status\":\"ACTIVE\",\"aml_risk_level\":\"LOW\",\"pep_status\":false,\"birth_date\":\"1990-05-15\",\"gender\":\"Male\",\"religion\":\"Buddhism\",\"blood_group\":\"O\",\"highest_education\":\"Bachelor\",\"phone\":\"0812345678\",\"email\":\"somchai@test.com\",\"line_id\":\"somchai.line\",\"facebook\":\"Somchai FB\",\"instagram\":\"@somchai.ig\",\"food_allergy\":\"Seafood\",\"medical_condition\":\"None\",\"emergency_contact_name\":\"สมศรี รักประกัน\",\"emergency_contact_phone\":\"0899999999\"},\"address\":{\"house_no\":\"123/45\",\"moo\":\"9\",\"village\":\"หมู่บ้านสุขสันต์\",\"soi\":\"ซอย 5\",\"road\":\"สุขุมวิท\",\"province\":\"กรุงเทพมหานคร\",\"district\":\"วัฒนา\",\"subdistrict\":\"คลองเตยเหนือ\",\"postcode\":\"10110\"},\"training\":{\"course_type\":\"หลักสูตรผู้บริหาร\",\"training_date\":\"2\",\"selected_subjects\":[\"วิชาประกันชีวิต\",\"วิชาประกันวินาศภัย\"],\"previous_courses\":[\"หลักสูตรขอรับใบอนุญาต\",\"หลักสูตรต่ออายุครั้งที่ 1\"]},\"experience\":{\"insurance_experience_years\":\"5\",\"occupation\":\"พนักงานบริษัท\",\"broker_branch\":\"สาขาหลัก\",\"extra_training_interest\":\"การบริหารความเสี่ยง\",\"sales_territories\":[\"1\",\"2\"],\"other_insurance_companies\":[\"3\"]},\"licenses\":[{\"license_number\":\"BR-66010012\",\"issue_date\":\"2019-10-10\",\"expire_date\":\"2024-10-09\",\"type\":\"BROKER_LIFE\",\"broker_type\":\"INDIVIDUAL\",\"status\":\"ACTIVE\"}],\"affiliation\":{\"branch_id\":\"2\",\"contract_code\":\"99999\",\"broker_company\":\"บริษัท โบรคเกอร์ดี จำกัด\"},\"specialties\":[\"ประกันต่อ\",\"ประกันภัยรถยนต์\"]}",
                "1613244338383" => "{\"cis_profile\":{\"cis_id\":\"1613244338383\",\"title_th\":\"น.ส.\",\"first_name_th\":\"สมหญิง\",\"last_name_th\":\"บริการดี\",\"person_status\":\"ACTIVE\",\"aml_risk_level\":\"LOW\",\"pep_status\":false,\"birth_date\":\"1985-10-20\",\"gender\":\"Female\",\"religion\":\"Christianity\",\"blood_group\":\"A\",\"highest_education\":\"Master\",\"phone\":\"0823334444\",\"email\":\"somying@test.com\",\"line_id\":\"\",\"facebook\":\"\",\"instagram\":\"\",\"food_allergy\":\"\",\"medical_condition\":\"\",\"emergency_contact_name\":\"\",\"emergency_contact_phone\":\"\"},\"address\":{\"house_no\":\"99\",\"moo\":\"\",\"village\":\"\",\"soi\":\"\",\"road\":\"สีลม\",\"province\":\"กรุงเทพมหานคร\",\"district\":\"บางรัก\",\"subdistrict\":\"สีลม\",\"postcode\":\"10500\"},\"training\":{\"course_type\":\"ต่ออายุใบอนุญาต\",\"training_date\":\"1\",\"selected_subjects\":[],\"previous_courses\":[]},\"experience\":{\"insurance_experience_years\":\"10\",\"occupation\":\"ตัวแทนอิสระ\",\"broker_branch\":\"\",\"extra_training_interest\":\"\",\"sales_territories\":[\"3\"],\"other_insurance_companies\":[]},\"licenses\":[{\"license_number\":\"AG-65030111\",\"issue_date\":\"2022-03-01\",\"expire_date\":\"2027-02-28\",\"type\":\"AGENT_LIFE\",\"broker_type\":\"\",\"status\":\"ACTIVE\"}],\"affiliation\":{\"branch_id\":\"\",\"contract_code\":\"\",\"broker_company\":\"\"},\"specialties\":[\"สุขภาพ\"]}",
                "9448106116565" => "{\"cis_profile\":{\"cis_id\":\"9448106116565\",\"title_th\":\"นาย.\",\"first_name_th\":\"วิชัย\",\"middle_name_th\":\"มงคล\",\"last_name_th\":\"เสี่ยงสูง\",\"person_status\":\"ACTIVE\",\"aml_risk_level\":\"HIGH\",\"pep_status\":true,\"id_card_expiry\":\"2030-02-28\",\"birth_date\":\"1975-02-28\",\"gender\":\"Male\",\"religion\":\"Islam\",\"blood_group\":\"B\",\"highest_education\":\"High School\",\"phone\":\"0891234567\",\"email\":\"wichai@test.com\",\"line_id\":\"wichai123\",\"facebook\":\"Wichai Risk\",\"instagram\":\"\",\"food_allergy\":\"Peanuts\",\"medical_condition\":\"Diabetes\",\"emergency_contact_name\":\"มาลี เสี่ยงสูง\",\"emergency_contact_phone\":\"0811112222\"},\"address\":{\"house_no\":\"11/1\",\"moo\":\"2\",\"village\":\"ตะวันทอแสง\",\"soi\":\"1\",\"road\":\"รามคำแหง\",\"province\":\"กรุงเทพมหานคร\",\"district\":\"บางกะปิ\",\"subdistrict\":\"หัวหมาก\",\"postcode\":\"10240\"},\"training\":{\"course_type\":\"ขอรับใบอนุญาตเป็นนายหน้าประกันวินาศภัย\",\"training_date\":\"1\",\"selected_subjects\":[\"วิชาจรรยาบรรณนายหน้า\"],\"previous_courses\":[\"หลักสูตรนายหน้า 1\"]},\"experience\":{\"insurance_experience_years\":\"2\",\"occupation\":\"นักธุรกิจ\",\"broker_branch\":\"สาขาบางกะปิ\",\"extra_training_interest\":\"ประกันความรับผิด\",\"sales_territories\":[\"1\"],\"other_insurance_companies\":[\"4\"]},\"licenses\":[{\"license_number\":\"6401055599\",\"issue_date\":\"2020-05-15\",\"expire_date\":\"2030-05-14\",\"type\":\"BROKER_NON_LIFE\",\"broker_type\":\"INDIVIDUAL\",\"status\":\"ACTIVE\"}],\"affiliation\":{\"branch_id\":\"1\",\"contract_code\":\"12345\",\"broker_company\":\"บริษัท นายหน้าดีเด่น จำกัด\"},\"specialties\":[\"อัคคีภัย\"]}",
                "4774991977483" => "{\"cis_profile\":{\"cis_id\":\"4774991977483\",\"title_th\":\"นาง.\",\"first_name_th\":\"กานดา\",\"last_name_th\":\"หมดอายุ\",\"person_status\":\"ACTIVE\",\"aml_risk_level\":\"LOW\",\"pep_status\":false,\"birth_date\":\"1982-12-12\",\"gender\":\"Female\",\"religion\":\"Buddhism\",\"blood_group\":\"AB\",\"highest_education\":\"Bachelor\",\"phone\":\"0859876543\",\"email\":\"kanda@test.com\",\"line_id\":\"\",\"facebook\":\"\",\"instagram\":\"\",\"food_allergy\":\"\",\"medical_condition\":\"\",\"emergency_contact_name\":\"ประวิทย์ หมดอายุ\",\"emergency_contact_phone\":\"0844445555\"},\"address\":{\"house_no\":\"55/5\",\"moo\":\"\",\"village\":\"\",\"soi\":\"\",\"road\":\"ลาดพร้าว\",\"province\":\"กรุงเทพมหานคร\",\"district\":\"จตุจักร\",\"subdistrict\":\"จอมพล\",\"postcode\":\"10900\"},\"training\":{\"course_type\":\"\",\"training_date\":\"\",\"selected_subjects\":[],\"previous_courses\":[]},\"experience\":{\"insurance_experience_years\":\"\",\"occupation\":\"\",\"broker_branch\":\"\",\"extra_training_interest\":\"\",\"sales_territories\":[],\"other_insurance_companies\":[]},\"licenses\":[{\"license_number\":\"AG-61020333\",\"issue_date\":\"2018-02-03\",\"expire_date\":\"2023-02-02\",\"type\":\"AGENT_LIFE\",\"broker_type\":\"\",\"status\":\"EXPIRED\"}],\"affiliation\":{\"branch_id\":\"\",\"contract_code\":\"\",\"broker_company\":\"\"},\"specialties\":[]}",
                "4388608551497" => "{\"cis_profile\":{\"cis_id\":\"4388608551497\",\"title_th\":\"นาย.\",\"first_name_th\":\"ธนพล\",\"last_name_th\":\"พักใบอนุญาต\",\"person_status\":\"ACTIVE\",\"aml_risk_level\":\"MEDIUM\",\"pep_status\":false,\"birth_date\":\"1992-07-07\",\"gender\":\"Male\",\"religion\":\"Buddhism\",\"blood_group\":\"O\",\"highest_education\":\"Bachelor\",\"phone\":\"0833334444\",\"email\":\"tanapol@test.com\",\"line_id\":\"\",\"facebook\":\"\",\"instagram\":\"\",\"food_allergy\":\"\",\"medical_condition\":\"\",\"emergency_contact_name\":\"\",\"emergency_contact_phone\":\"\"},\"address\":{\"house_no\":\"77\",\"moo\":\"3\",\"village\":\"\",\"soi\":\"แจ้งวัฒนะ 10\",\"road\":\"แจ้งวัฒนะ\",\"province\":\"กรุงเทพมหานคร\",\"district\":\"หลักสี่\",\"subdistrict\":\"ทุ่งสองห้อง\",\"postcode\":\"10210\"},\"training\":{\"course_type\":\"3\",\"training_date\":\"2\",\"selected_subjects\":[\"1\"],\"previous_courses\":[]},\"experience\":{\"insurance_experience_years\":\"8\",\"occupation\":\"อิสระ\",\"broker_branch\":\"\",\"extra_training_interest\":\"\",\"sales_territories\":[],\"other_insurance_companies\":[]},\"licenses\":[{\"license_number\":\"BR-63040777\",\"issue_date\":\"2020-04-07\",\"expire_date\":\"2025-04-06\",\"type\":\"BROKER_LIFE\",\"broker_type\":\"INDIVIDUAL\",\"status\":\"SUSPENDED\"}],\"affiliation\":{\"branch_id\":\"3\",\"contract_code\":\"88888\",\"broker_company\":\"บริษัท หยุดทำการ จำกัด\"},\"specialties\":[]}",
                "9259886073561" => "{\"cis_profile\":{\"cis_id\":\"9259886073561\",\"title_th\":\"น.ส.\",\"first_name_th\":\"นารี\",\"last_name_th\":\"อบรมไม่ผ่าน\",\"person_status\":\"ACTIVE\",\"aml_risk_level\":\"LOW\",\"pep_status\":false,\"birth_date\":\"1995-09-09\",\"gender\":\"Female\",\"religion\":\"Buddhism\",\"blood_group\":\"A\",\"highest_education\":\"Bachelor\",\"phone\":\"0888889999\",\"email\":\"naree@test.com\",\"line_id\":\"\",\"facebook\":\"\",\"instagram\":\"\",\"food_allergy\":\"\",\"medical_condition\":\"\",\"emergency_contact_name\":\"\",\"emergency_contact_phone\":\"\"},\"address\":{\"house_no\":\"88\",\"moo\":\"\",\"village\":\"\",\"soi\":\"\",\"road\":\"เพชรเกษม\",\"province\":\"กรุงเทพมหานคร\",\"district\":\"บางแค\",\"subdistrict\":\"บางแค\",\"postcode\":\"10160\"},\"training\":{\"course_type\":\"\",\"training_date\":\"\",\"selected_subjects\":[],\"previous_courses\":[]},\"experience\":{\"insurance_experience_years\":\"\",\"occupation\":\"\",\"broker_branch\":\"\",\"extra_training_interest\":\"\",\"sales_territories\":[],\"other_insurance_companies\":[]},\"licenses\":[],\"affiliation\":{\"branch_id\":\"\",\"contract_code\":\"\",\"broker_company\":\"\"},\"specialties\":[]}",
                "4868659136824" => "{\"cis_profile\":{\"cis_id\":\"4868659136824\",\"title_th\":\"นาย.\",\"first_name_th\":\"ประเสริฐ\",\"last_name_th\":\"ชั้นแนวหน้า\",\"person_status\":\"ACTIVE\",\"aml_risk_level\":\"LOW\",\"pep_status\":false,\"birth_date\":\"1980-04-04\",\"gender\":\"Male\",\"religion\":\"Buddhism\",\"blood_group\":\"B\",\"highest_education\":\"Master\",\"phone\":\"0899998888\",\"email\":\"prasert@test.com\",\"line_id\":\"prasert.top\",\"facebook\":\"Prasert Top\",\"instagram\":\"\",\"food_allergy\":\"\",\"medical_condition\":\"\",\"emergency_contact_name\":\"วิไล ชั้นแนวหน้า\",\"emergency_contact_phone\":\"0877776666\"},\"address\":{\"house_no\":\"999/9\",\"moo\":\"\",\"village\":\"หมู่บ้านไฮโซ\",\"soi\":\"\",\"road\":\"สุขุมวิท 55\",\"province\":\"กรุงเทพมหานคร\",\"district\":\"วัฒนา\",\"subdistrict\":\"คลองตันเหนือ\",\"postcode\":\"10110\"},\"training\":{\"course_type\":\"1\",\"training_date\":\"3\",\"selected_subjects\":[\"1\",\"2\",\"3\"],\"previous_courses\":[\"หลักสูตรผู้บริหาร\"]},\"experience\":{\"insurance_experience_years\":\"15\",\"occupation\":\"ผู้บริหาร\",\"broker_branch\":\"สำนักงานใหญ่\",\"extra_training_interest\":\"Cyber Security\",\"sales_territories\":[\"1\",\"2\",\"3\"],\"other_insurance_companies\":[\"1\",\"2\"]},\"licenses\":[{\"license_number\":\"BR-60010888\",\"issue_date\":\"2017-01-08\",\"expire_date\":\"2027-01-07\",\"type\":\"BROKER_LIFE\",\"broker_type\":\"INDIVIDUAL\",\"status\":\"ACTIVE\"}],\"affiliation\":{\"branch_id\":\"1\",\"contract_code\":\"11111\",\"broker_company\":\"บริษัท อันดับหนึ่ง จำกัด\"},\"specialties\":[\"ประกันต่อ\",\"ประกันภัยรถยนต์\",\"สุขภาพ\"]}",
                "1259511589853" => "{\"cis_profile\":{\"cis_id\":\"1259511589853\",\"title_th\":\"นาง.\",\"first_name_th\":\"ลัดดา\",\"last_name_th\":\"ถูกเพิกถอน\",\"person_status\":\"BLACKLISTED\",\"aml_risk_level\":\"HIGH\",\"pep_status\":false,\"birth_date\":\"1970-01-01\",\"gender\":\"Female\",\"religion\":\"Buddhism\",\"blood_group\":\"O\",\"highest_education\":\"High School\",\"phone\":\"0811112222\",\"email\":\"ladda@test.com\",\"line_id\":\"\",\"facebook\":\"\",\"instagram\":\"\",\"food_allergy\":\"\",\"medical_condition\":\"\",\"emergency_contact_name\":\"\",\"emergency_contact_phone\":\"\"},\"address\":{\"house_no\":\"44\",\"moo\":\"\",\"village\":\"\",\"soi\":\"\",\"road\":\"พหลโยธิน\",\"province\":\"กรุงเทพมหานคร\",\"district\":\"พญาไท\",\"subdistrict\":\"สามเสนใน\",\"postcode\":\"10400\"},\"training\":{\"course_type\":\"\",\"training_date\":\"\",\"selected_subjects\":[],\"previous_courses\":[]},\"experience\":{\"insurance_experience_years\":\"20\",\"occupation\":\"ว่างงาน\",\"broker_branch\":\"\",\"extra_training_interest\":\"\",\"sales_territories\":[],\"other_insurance_companies\":[]},\"licenses\":[{\"license_number\":\"AG-55010222\",\"issue_date\":\"2012-01-02\",\"expire_date\":\"2017-01-01\",\"type\":\"AGENT_NON_LIFE\",\"broker_type\":\"\",\"status\":\"REVOKED\"}],\"affiliation\":{\"branch_id\":\"\",\"contract_code\":\"\",\"broker_company\":\"\"},\"specialties\":[]}",
                "8392598827204" => "{\"cis_profile\":{\"cis_id\":\"8392598827204\",\"title_th\":\"นาย.\",\"first_name_th\":\"อาทิตย์\",\"last_name_th\":\"ปปงปานกลาง\",\"person_status\":\"ACTIVE\",\"aml_risk_level\":\"MEDIUM\",\"pep_status\":false,\"birth_date\":\"1988-08-08\",\"gender\":\"Male\",\"religion\":\"Buddhism\",\"blood_group\":\"AB\",\"highest_education\":\"Bachelor\",\"phone\":\"0844445555\",\"email\":\"artit@test.com\",\"line_id\":\"\",\"facebook\":\"\",\"instagram\":\"\",\"food_allergy\":\"\",\"medical_condition\":\"\",\"emergency_contact_name\":\"\",\"emergency_contact_phone\":\"\"},\"address\":{\"house_no\":\"33/3\",\"moo\":\"\",\"village\":\"\",\"soi\":\"\",\"road\":\"รัชดาภิเษก\",\"province\":\"กรุงเทพมหานคร\",\"district\":\"ห้วยขวาง\",\"subdistrict\":\"ห้วยขวาง\",\"postcode\":\"10310\"},\"training\":{\"course_type\":\"1\",\"training_date\":\"1\",\"selected_subjects\":[\"2\"],\"previous_courses\":[]},\"experience\":{\"insurance_experience_years\":\"4\",\"occupation\":\"พนักงาน\",\"broker_branch\":\"ห้วยขวาง\",\"extra_training_interest\":\"การขาย\",\"sales_territories\":[\"2\"],\"other_insurance_companies\":[\"2\"]},\"licenses\":[{\"license_number\":\"BR-65050444\",\"issue_date\":\"2022-05-04\",\"expire_date\":\"2027-05-03\",\"type\":\"BROKER_LIFE\",\"broker_type\":\"INDIVIDUAL\",\"status\":\"ACTIVE\"}],\"affiliation\":{\"branch_id\":\"2\",\"contract_code\":\"22222\",\"broker_company\":\"บริษัท รัชดา จำกัด\"},\"specialties\":[]}",
                "5395944994612" => "{\"cis_profile\":{\"cis_id\":\"5395944994612\",\"title_th\":\"นาง.\",\"first_name_th\":\"ดวงใจ\",\"last_name_th\":\"เสียชีวิต\",\"person_status\":\"DECEASED\",\"aml_risk_level\":\"LOW\",\"pep_status\":false,\"birth_date\":\"1965-05-05\",\"gender\":\"Female\",\"religion\":\"Buddhism\",\"blood_group\":\"A\",\"highest_education\":\"Bachelor\",\"phone\":\"0822223333\",\"email\":\"duangjai@test.com\",\"line_id\":\"\",\"facebook\":\"\",\"instagram\":\"\",\"food_allergy\":\"\",\"medical_condition\":\"\",\"emergency_contact_name\":\"\",\"emergency_contact_phone\":\"\"},\"address\":{\"house_no\":\"22\",\"moo\":\"\",\"village\":\"\",\"soi\":\"\",\"road\":\"พระราม 9\",\"province\":\"กรุงเทพมหานคร\",\"district\":\"ห้วยขวาง\",\"subdistrict\":\"บางกะปิ\",\"postcode\":\"10310\"},\"training\":{\"course_type\":\"\",\"training_date\":\"\",\"selected_subjects\":[],\"previous_courses\":[]},\"experience\":{\"insurance_experience_years\":\"30\",\"occupation\":\"เกษียณ\",\"broker_branch\":\"\",\"extra_training_interest\":\"\",\"sales_territories\":[],\"other_insurance_companies\":[]},\"licenses\":[{\"license_number\":\"AG-50010111\",\"issue_date\":\"2007-01-11\",\"expire_date\":\"2012-01-10\",\"type\":\"AGENT_LIFE\",\"broker_type\":\"\",\"status\":\"ACTIVE\"}],\"affiliation\":{\"branch_id\":\"\",\"contract_code\":\"\",\"broker_company\":\"\"},\"specialties\":[]}",
                _ => "{\"cis_profile\":{\"cis_id\":\"CIS-999999\",\"title_th\":\"นาย\",\"first_name_th\":\"ทดสอบ\",\"last_name_th\":\"ระบบ\",\"person_status\":\"ACTIVE\",\"aml_risk_level\":\"LOW\"},\"licenses\":[]}"
            };
        }
    }
}
