#pragma warning disable CS8601 // Possible null reference assignment
#pragma warning disable CS1998 // Async method lacks 'await' operators

using Microsoft.EntityFrameworkCore;
using backend.Data;
using backend.Models;
using System.Text.Json;

namespace backend.Repositories
{
    public class RegisterRepository : IRegisterRepository
    {
        private readonly AppDbContext _context;

        public RegisterRepository(AppDbContext context)
        {
            _context = context;
        }

        public async Task<Register?> GetByIdAsync(int id)
        {
            // Note: Returning null or dummy as we moved to Person tables. 
            // The Admin page will need to be refactored later.
            return await _context.Registers.FindAsync(id);
        }

        public async Task<IEnumerable<Register>> GetAllAsync()
        {
            // Note: Currently returns from the old table so the admin UI doesn't crash, 
            // but new entries won't show up here unless we map them. 
            // The user approved a hard cutover for Submit: "ไม่ต้องให้ใช้ตารางใหม่ไปเลย เดี๋ยวหน้า Admin ค่อยมาตามแก้"
            return await _context.Registers.OrderByDescending(r => r.Id).ToListAsync();
        }

        public async Task<int> CreateAsync(Register register)
        {
            var cleanNationId = register.NationalId?.Replace("-", "");
            if (string.IsNullOrEmpty(cleanNationId)) throw new Exception("National ID is required");

            using var transaction = await _context.Database.BeginTransactionAsync();
            try
            {
                // 1. Person
                var person = await _context.Persons
                    .Include(p => p.Others)
                    .FirstOrDefaultAsync(p => p.NationId == cleanNationId);

                if (person != null)
                {
                    // Remove old related records before recreating them
                    _context.PersonAddresses.RemoveRange(_context.PersonAddresses.Where(a => a.NationId == cleanNationId));
                    _context.PersonLicenses.RemoveRange(_context.PersonLicenses.Where(l => l.NationId == cleanNationId));
                    _context.PersonAffiliations.RemoveRange(_context.PersonAffiliations.Where(a => a.NationId == cleanNationId));
                    _context.PersonCourses.RemoveRange(_context.PersonCourses.Where(c => c.NationId == cleanNationId));
                    _context.PersonTrainings5y.RemoveRange(_context.PersonTrainings5y.Where(t => t.NationId == cleanNationId));
                    _context.PersonOtherSalesAreas.RemoveRange(_context.PersonOtherSalesAreas.Where(s => s.NationId == cleanNationId));
                    _context.PersonOtherCompanies.RemoveRange(_context.PersonOtherCompanies.Where(c => c.NationId == cleanNationId));
                    _context.PersonOtherSpecialties.RemoveRange(_context.PersonOtherSpecialties.Where(s => s.NationId == cleanNationId));
                }
                else
                {
                    person = new Person { NationId = cleanNationId };
                    _context.Persons.Add(person);
                }

            // Map fields to Person
            if (register.IdCardExpiry.HasValue) person.IdCardExpiry = register.IdCardExpiry;
            
            var otherTitleId = _context.Titles.FirstOrDefault(t => t.Name == "อื่นๆ")?.Id.ToString();
            if (register.TitleTh == otherTitleId)
            {
                person.TitleTh = register.TitleCustom;
            }
            else
            {
                person.TitleTh = register.TitleTh;
            }

            person.FirstNameTh = register.FirstNameTh;
            person.MiddleNameTh = register.MiddleNameTh;
            person.LastNameTh = register.LastNameTh;
            
            person.TitleOldTh = (register.TitlePrev == otherTitleId) ? register.TitleCustomPrev : register.TitlePrev;
            person.FirstNameOldTh = register.FirstNameOldTh;
            person.MiddleNameOldTh = register.MiddleNameOldTh;
            person.LastNameOldTh = register.LastNameOldTh;
            if (register.BirthDate.HasValue) person.BirthDate = register.BirthDate;
            if (int.TryParse(register.Religion, out var religionId)) person.ReligionId = religionId;
            else if (!string.IsNullOrEmpty(register.Religion)) person.ReligionId = _context.Religions.FirstOrDefault(r => r.Name == register.Religion)?.Id;
            if (int.TryParse(register.Gender, out var genderId)) person.GenderId = genderId;
            else if (!string.IsNullOrEmpty(register.Gender)) person.GenderId = _context.Genders.FirstOrDefault(g => g.Name == register.Gender)?.Id;
            if (int.TryParse(register.BloodGroup, out var bloodId)) person.BloodGroupId = bloodId;
            else if (!string.IsNullOrEmpty(register.BloodGroup)) person.BloodGroupId = _context.BloodTypes.FirstOrDefault(b => b.Name == register.BloodGroup)?.Id;
            person.PhoneOtp = register.Phone ?? register.PhoneOtp;
            person.EmailAlt = register.Email ?? register.EmailAlt;
            person.LineId = register.LineId;
            person.Facebook = register.Facebook;
            person.Instagram = register.Instagram;
            person.FoodAllergy = register.FoodAllergy;
            person.MedicalCondition = register.MedicalCondition;
            person.EmergencyContactName = register.EmergencyContactName;
            person.EmergencyContactPhone = register.EmergencyContactPhone;

            // 2. PersonRegistration
            var personReg = new PersonRegistration
            {
                NationId = cleanNationId,
                PdpaConsent = register.PdpaConsent == "true" || register.PdpaConsent == "1" || register.PdpaConsent?.ToLower() == "yes",
                start_time = DateTime.Now,
                completion_time = DateTime.Now,
                confirmed = true
            };
            _context.PersonRegistrations.Add(personReg);

            // 3. PersonAddress (House)
            var houseAddr = new PersonAddress
            {
                NationId = cleanNationId,
                AddressType = "A",
                AddressTypeText = "House",
                HouseNo = register.HouseNo ?? register.AddrHouseNo,
                Moo = register.Moo ?? register.AddrMoo,
                Village = register.Village ?? register.AddrVillage,
                Soi = register.Soi ?? register.AddrSoi,
                Road = register.Road ?? register.AddrRoad,
                ProvinceId = register.ProvinceId ?? (int.TryParse(register.AddrProvince, out var hp) ? hp : null),
                DistrictId = register.DistrictId ?? (int.TryParse(register.AddrDistrict, out var hd) ? hd : null),
                SubDistrictId = register.SubDistrictId ?? (int.TryParse(register.AddrSubdistrict, out var hs) ? hs : null),
                Postcode = register.Zipcode ?? register.AddrPostcode
            };
            _context.PersonAddresses.Add(houseAddr);

            // 3. PersonAddress (Current)
            bool isSame = true; // Default to true if not specified
            if (register.SameAddress.HasValue)
            {
                var el = register.SameAddress.Value;
                if (el.ValueKind == System.Text.Json.JsonValueKind.True) isSame = true;
                else if (el.ValueKind == System.Text.Json.JsonValueKind.False) isSame = false;
                else if (el.ValueKind == System.Text.Json.JsonValueKind.String)
                {
                    var str = el.GetString();
                    if (str == "true" || str == "True") isSame = true;
                    else if (str == "false" || str == "False") isSame = false;
                }
            }
            else if (!string.IsNullOrEmpty(register.ContactAddress))
            {
                isSame = register.ContactAddress == "same";
            }

            if (!isSame)
            {
                var currentAddr = new PersonAddress
                {
                    NationId = cleanNationId,
                    AddressType = "C",
                    AddressTypeText = "different",
                    HouseNo = register.ShipHouseNo ?? register.ContactHouseNo,
                    Moo = register.ShipMoo ?? register.ContactMoo,
                    Village = register.ShipVillage ?? register.ContactVillage,
                    Soi = register.ShipSoi ?? register.ContactSoi,
                    Road = register.ShipRoad ?? register.ContactRoad,
                    ProvinceId = register.ShipProvinceId ?? (int.TryParse(register.ContactProvince, out var cp2) ? cp2 : null),
                    DistrictId = register.ShipDistrictId ?? (int.TryParse(register.ContactDistrict, out var cd2) ? cd2 : null),
                    SubDistrictId = register.ShipSubDistrictId ?? (int.TryParse(register.ContactSubdistrict, out var cs2) ? cs2 : null),
                    Postcode = register.ShipZipcode ?? register.ContactPostcode
                };
                _context.PersonAddresses.Add(currentAddr);
            }

            // 4. PersonLicense
            if (!string.IsNullOrEmpty(register.LicenseNo))
            {
                string? agentTypeFromCourse = null;
                if (int.TryParse(register.CourseType, out var cId))
                {
                    var renewCourse = _context.RenewBasics.FirstOrDefault(c => c.Id == cId);
                    if (renewCourse != null)
                    {
                        agentTypeFromCourse = renewCourse.AgentType;
                    }
                }

                var license = new PersonLicense
                {
                    NationId = cleanNationId,
                    CourseType = agentTypeFromCourse ?? register.AgentType,
                    CourseTypeCode = register.CourseType,
                    LicenseNo = register.LicenseNo,
                    LicenseIssueDate = register.LicenseIssue ?? register.LicenseIssueDate,
                    LicenseExpiryDate = register.LicenseExpire ?? register.LicenseExpiryDate
                };
                _context.PersonLicenses.Add(license);
            }

            // 5. PersonAffiliation
            var affiliation = new PersonAffiliation
            {
                NationId = cleanNationId,
                RegionId = int.TryParse(register.AgentRegion ?? register.RegionAffiliation, out var rId) ? rId : null,
                BranchId = int.TryParse(register.AgentBranch, out var bId) ? bId : null,
                BrokerCompany = register.BrokerAffiliation ?? register.BrokerCompany,
                BrokerBranch = register.BranchRecommender,
                ViriyahAgentCode = register.ViriyaContractCode ?? register.ViriyahAgentCode,
                BrokerType = register.BrokerType
            };
            _context.PersonAffiliations.Add(affiliation);

            // 6. PersonOther
            var other = person.Others.FirstOrDefault();
            if (other == null)
            {
                other = new PersonOther { NationId = cleanNationId };
                _context.PersonOthers.Add(other);
            }
            other.InsuranceExperienceYears = register.InsuranceExperienceYears.HasValue ? (int)register.InsuranceExperienceYears.Value : null;
            other.OtherBusiness = register.MainBusiness;
            other.BrokerBranch = register.BrokerBranch;
            other.ExtraTrainingInterest = register.ExtraTrainingInterest;
            
            // Save initial phase to get OtherId if new
            await _context.SaveChangesAsync();

            // 7. PersonOtherSalesArea
            if (!string.IsNullOrEmpty(register.SalesArea))
            {
                var territories = register.SalesArea.Split(',');
                foreach (var tStr in territories)
                {
                    if (int.TryParse(tStr, out var tId))
                    {
                        _context.PersonOtherSalesAreas.Add(new PersonOtherSalesArea { OtherId = other.id, NationId = cleanNationId, TerritoriesId = tId });
                    }
                }
            }

            // 8. PersonOtherCompanies
            if (!string.IsNullOrEmpty(register.OtherInsuranceCompanies))
            {
                var companies = register.OtherInsuranceCompanies.Split(',');
                foreach (var cStr in companies)
                {
                    if (int.TryParse(cStr, out var compId))
                    {
                        _context.PersonOtherCompanies.Add(new PersonOtherCompanies { OtherId = other.id, NationId = cleanNationId, CompanyId = compId });
                    }
                }
            }

            // 9. PersonOtherSpecialty
            if (!string.IsNullOrEmpty(register.InsuranceSpecialty))
            {
                var spec = register.InsuranceSpecialty.Split(',');
                foreach (var sStr in spec)
                {
                    if (int.TryParse(sStr, out var sId))
                    {
                        _context.PersonOtherSpecialties.Add(new PersonOtherSpecialty { OtherId = other.id, NationId = cleanNationId, ExpertiseId = sId });
                    }
                }
            }

            // 9.1 PersonCourse
            if (int.TryParse(register.CourseType, out var courseId))
            {
                if (courseId == 9 || courseId == 10) // "ขอต่อใบอนุญาต 4" (Agent and Broker)
                {
                    if (!string.IsNullOrEmpty(register.SelectedSubjects))
                    {
                        var subjects = register.SelectedSubjects.Split(',');
                        foreach (var sStr in subjects)
                        {
                            if (int.TryParse(sStr, out var subId))
                            {
                                _context.PersonCourses.Add(new PersonCourse
                                {
                                    NationId = cleanNationId,
                                    CourseId = courseId,
                                    RenewOtherId = subId
                                });
                            }
                        }
                    }
                }
                else
                {
                    // Normal courses
                    _context.PersonCourses.Add(new PersonCourse
                    {
                        NationId = cleanNationId,
                        CourseId = courseId,
                        CourseDateId = int.TryParse(register.TrainingDate, out var dId) ? dId : null
                    });
                }
            }

            // 9.2 PersonTraining5y
            if (!string.IsNullOrEmpty(register.PreviousCourses))
            {
                var prevCourses = register.PreviousCourses.Split(',');
                foreach (var pStr in prevCourses)
                {
                    if (int.TryParse(pStr, out var pId))
                    {
                        _context.PersonTrainings5y.Add(new PersonTraining5y
                        {
                            NationId = cleanNationId,
                            CourseId = pId
                        });
                    }
                }
            }

            // 10. RegistrationHistory
            var history = new RegistrationHistoryModel
            {
                RegisterId = personReg.Id,
                NationId = cleanNationId,
                EditedByType = "applicant",
                CreatedBy = "user",
                NewData = JsonSerializer.Serialize(register),
                CreatedAt = DateTime.Now
            };
            _context.RegistrationHistoriesNew.Add(history);

            await _context.SaveChangesAsync();
            await transaction.CommitAsync();

            return personReg.Id;
            }
            catch
            {
                await transaction.RollbackAsync();
                throw;
            }
        }

        public async Task UpdateAsync(Register register)
        {
            await Task.CompletedTask;
            // For Hard Cutover, we would also update the Person tables.
            // But since the current flow primarily uses CreateAsync for new registrations,
            // we will just map Update to create a new registration record for now or update Person.
            // To keep it simple, we reuse the same logic or throw NotImplemented if Admin update is needed later.
            throw new NotImplementedException("Admin update to new schema is pending.");
        }

        public async Task DeleteAsync(int id)
        {
            await Task.CompletedTask;
            throw new NotImplementedException("Admin delete from new schema is pending.");
        }
    }
}
