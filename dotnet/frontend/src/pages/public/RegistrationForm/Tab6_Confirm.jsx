import { useState } from 'react';
import { useRegistration } from '../../../context/RegistrationContext';
import Button from '../../../components/Button';
import { useNavigate } from 'react-router-dom';

export default function Tab6Confirm() {
  const { prevStep, formData, updateData } = useRegistration();
  const [errors, setErrors] = useState({});
  const navigate = useNavigate();

  const handleChange = (e) => {
    updateData({ [e.target.id]: e.target.value });
    if (errors[e.target.id]) {
      setErrors(prev => ({ ...prev, [e.target.id]: '' }));
    }
  };

  const handleCheckboxChange = (name, value, isChecked) => {
    const currentArray = formData[name] || [];
    let newArray;
    if (isChecked) {
      newArray = [...currentArray, value];
    } else {
      newArray = currentArray.filter(item => item !== value);
    }
    updateData({ [name]: newArray });
  };

  const submitForm = async (e) => {
    e.preventDefault();
    const newErrors = {};

    if (!formData.occupation?.trim()) newErrors.occupation = 'กรุณาระบุอาชีพหลัก';
    if (!formData.insuranceExperienceYears?.trim()) newErrors.insuranceExperienceYears = 'กรุณาระบุประสบการณ์ทำงาน (ปี)';
    if (!formData.certifyTrue) newErrors.certifyTrue = 'กรุณายืนยันคำรับรองผู้สมัคร';

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      const firstErrorId = Object.keys(newErrors)[0];
      document.getElementById(firstErrorId)?.scrollIntoView({ behavior: 'smooth' });
      return;
    }
    
    try {
      // Placeholder for actual API call
      // const response = await fetch('/api/registration', { method: 'POST', body: JSON.stringify(formData) });
      console.log('Submitting data to backend:', formData);
      navigate('/success');
    } catch (error) {
      alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
    }
  };

  const territories = ['ภาคกลาง', 'ภาคเหนือ', 'ภาคตะวันออกเฉียงเหนือ', 'ภาคตะวันออก', 'ภาคตะวันตก', 'ภาคใต้'];
  
  const specialties = [
    'ประกันรถยนต์', 'ประกันอัคคีภัย/ทรัพย์สิน', 'ประกันอุบัติเหตุ', 'ประกันสุขภาพ', 'ประกันเดินทาง/ทางทะเล',
    'ประกันการก่อสร้าง/วิศวกรรม', 'ประกันภัยความรับผิดของกรรมการ/ผู้บริหาร', 'ประกันภัยความรับผิด',
    'ประกันภัยด้านการเงิน/ค้ำประกัน', 'ประกันภัยด้านสิทธิบัตร', 'ประกันภัยทางทะเล/ขนส่ง', 'ประกันอื่นๆ'
  ];

  const companies = [
    'บ. กรุงเทพประกันภัย', 'บ. กรุงเทพประกันสุขภาพ', 'บ. กรุงไทยพานิชประกันภัย', 'บ. กลางคุ้มครองผู้ประสบภัยจากรถ',
    'บ. ชับบ์สามัคคีประกันภัย', 'บ. โตเกียวมารีนประกันภัย', 'บ. ทิพยประกันภัย', 'บ. เทเวศประกันภัย',
    'บ. ไทยไพบูลย์ประกันภัย', 'บ. ไทยวิวัฒน์ประกันภัย', 'บ. ไทยศรีประกันภัย', 'บ. ไทยเศรษฐกิจประกันภัย',
    'บ. นวกิจประกันภัย', 'บ. บางกอกสหประกันภัย', 'บ. ประกันภัยไทยวิวัฒน์', 'บ. เมืองไทยประกันภัย',
    'บ. สินมั่นคงประกันภัย', 'บ. อาคเนย์ประกันภัย', 'บ. อินทรประกันภัย', 'บ. เอเชียประกันภัย 1950',
    'บ. แอลเอ็มจี ประกันภัย', 'บ. เอไอจี ประกันภัย (ประเทศไทย)'
  ];

  return (
    <div className="animate-[fadeIn_0.5s]">
      <h3 className="text-xl font-semibold mb-6 text-primary border-b pb-2">ส่วนที่ 6: ข้อมูลรายละเอียด (Additional Details)</h3>
      
      <form onSubmit={submitForm} noValidate>
        <div className="mb-6" id="occupation">
          <label htmlFor="occupation" className="block text-sm font-medium text-textMain mb-1 after:content-['_*'] after:text-error">อาชีพหลักปัจจุบัน</label>
          <p className="text-[13px] text-gray-500 mb-2 mt-[-4px]">กรุณาระบุอาชีพหลัก</p>
          <input
            type="text"
            className={`w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-4 transition-colors ${errors.occupation ? 'border-error focus:border-error focus:ring-error/10' : 'border-border focus:border-primary focus:ring-primary/10'}`}
            placeholder="คุณระบุ"
            value={formData.occupation || ''}
            onChange={handleChange}
            required
          />
          {errors.occupation && <p className="text-error text-sm mt-1">{errors.occupation}</p>}
        </div>
        
        <div className="mb-6" id="insuranceExperienceYears">
          <label htmlFor="insuranceExperienceYears" className="block text-sm font-medium text-textMain mb-1 after:content-['_*'] after:text-error">ประสบการณ์ในธุรกิจประกันภัย</label>
          <p className="text-[13px] text-gray-500 mb-2 mt-[-4px]">จำนวนปี เช่น 3</p>
          <input
            type="number"
            min="0"
            className={`w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-4 transition-colors ${errors.insuranceExperienceYears ? 'border-error focus:border-error focus:ring-error/10' : 'border-border focus:border-primary focus:ring-primary/10'}`}
            placeholder="ต้องระบุเป็นตัวเลข"
            value={formData.insuranceExperienceYears || ''}
            onChange={handleChange}
            required
          />
          {errors.insuranceExperienceYears && <p className="text-error text-sm mt-1">{errors.insuranceExperienceYears}</p>}
        </div>

        <div className="mb-6">
          <label className="block text-sm font-medium text-textMain mb-2">เขตพื้นที่ขาย</label>
          <div className="flex flex-wrap gap-4">
            {territories.map((territory, idx) => (
              <label key={idx} className="flex items-center gap-2 cursor-pointer">
                <input
                  type="checkbox"
                  name="salesTerritories"
                  className="w-4 h-4 accent-primary"
                  value={territory}
                  checked={(formData.salesTerritories || []).includes(territory)}
                  onChange={(e) => handleCheckboxChange('salesTerritories', territory, e.target.checked)}
                />
                <span>{territory}</span>
              </label>
            ))}
          </div>
        </div>

        <div className="mb-6">
          <label className="block text-sm font-medium text-textMain mb-2">ความเชี่ยวชาญประกันภัย</label>
          <div className="flex flex-col gap-3">
            {specialties.map((specialty, idx) => (
              <label key={idx} className="flex items-center gap-3 cursor-pointer">
                <input
                  type="checkbox"
                  name="insuranceSpecialty"
                  className="w-4 h-4 accent-primary"
                  value={specialty}
                  checked={(formData.insuranceSpecialty || []).includes(specialty)}
                  onChange={(e) => handleCheckboxChange('insuranceSpecialty', specialty, e.target.checked)}
                />
                <span>{specialty}</span>
              </label>
            ))}
          </div>
        </div>

        <div className="mb-6">
          <label className="block text-sm font-medium text-textMain mb-2">บริษัทประกันภัยอื่นที่ท่านส่งงานในปัจจุบัน</label>
          <div className="max-h-60 overflow-y-auto p-4 border border-border rounded-md bg-gray-50 flex flex-col gap-3">
            {companies.map((company, idx) => (
              <label key={idx} className="flex items-center gap-3 cursor-pointer">
                <input
                  type="checkbox"
                  name="otherInsuranceCompanies"
                  className="w-4 h-4 accent-primary"
                  value={company}
                  checked={(formData.otherInsuranceCompanies || []).includes(company)}
                  onChange={(e) => handleCheckboxChange('otherInsuranceCompanies', company, e.target.checked)}
                />
                <span>{company}</span>
              </label>
            ))}
          </div>
        </div>

        <div className={`mt-8 mb-6 p-4 border rounded-md ${errors.certifyTrue ? 'border-error bg-red-50' : 'border-border bg-gray-50'}`} id="certifyTrue">
          <h4 className="font-semibold mb-3">คำรับรองผู้สมัคร</h4>
          <div className="flex items-start gap-3">
            <input 
              type="checkbox" 
              className="mt-1 w-5 h-5 accent-primary cursor-pointer"
              checked={formData.certifyTrue || false}
              onChange={(e) => {
                updateData({ certifyTrue: e.target.checked });
                if (errors.certifyTrue) setErrors(prev => ({ ...prev, certifyTrue: '' }));
              }}
            />
            <label className="cursor-pointer">
              ข้าพเจ้าขอรับรองว่าข้อความและข้อมูลข้างต้นเป็นความจริงทุกประการ หากตรวจสอบพบว่าไม่เป็นความจริง ข้าพเจ้ายินยอมให้ยกเลิกการลงทะเบียน
            </label>
          </div>
          {errors.certifyTrue && <p className="text-error text-sm mt-2 font-medium">{errors.certifyTrue}</p>}
        </div>

        <div className="flex justify-between mt-10 pt-5 border-t border-border">
          <Button type="button" variant="secondary" onClick={prevStep}><i className="fas fa-arrow-left"></i> ย้อนกลับ</Button>
          <Button type="submit">
            ส่งข้อมูลสมัคร <i className="fas fa-paper-plane"></i>
          </Button>
        </div>
      </form>
    </div>
  );
}
