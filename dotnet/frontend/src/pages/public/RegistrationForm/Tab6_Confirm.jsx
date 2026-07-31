import { useState } from 'react';
import { useRegistration } from '../../../context/RegistrationContext';
import Button from '../../../components/Button';
import { useNavigate } from 'react-router-dom';

export default function Tab6Confirm() {
  const { prevStep, formData, updateData, masterData } = useRegistration();
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

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      const firstErrorId = Object.keys(newErrors)[0];
      document.getElementById(firstErrorId)?.scrollIntoView({ behavior: 'smooth' });
      return;
    }
    
    try {
      console.log('Submitting data to backend:', formData);
      
      const payload = { ...formData };
      
      // Convert arrays to comma-separated strings of IDs
      // Convert all array fields to comma-separated strings to match backend string fields and avoid 400 Bad Request
      const arrayFields = [
        'salesTerritories',
        'insuranceSpecialty',
        'otherInsuranceCompanies',
        'previousCourses',
        'selectedSubjects',
        'deductionPrivilege'
      ];
      
      arrayFields.forEach(field => {
        if (Array.isArray(payload[field])) {
          if (field === 'salesTerritories') {
            payload.salesArea = payload[field].join(',');
          } else {
            payload[field] = payload[field].join(',');
          }
        }
      });
      
      // Convert empty strings to null for backend validation
      // and booleans to string representations
      Object.keys(payload).forEach(key => {
        if (payload[key] === '') {
          payload[key] = null;
        } else if (typeof payload[key] === 'boolean') {
          payload[key] = payload[key] ? 'true' : 'false';
        }
      });
      
      const res = await fetch('http://localhost:8085/api/registration', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });
      
      if (!res.ok) {
        const errData = await res.json();
        console.error('API Error Details:', errData);
        alert('Validation Error: ' + JSON.stringify(errData.errors || errData));
        throw new Error('API Error');
      }
      
      navigate('/success');
    } catch (error) {
      if (error.message !== 'API Error') {
        alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' + error.message);
      }
    }
  };

  const territories = masterData?.territories || [];
  const specialties = masterData?.expertises || [];
  const companies = masterData?.companies || [];

  return (
    <div className="animate-[fadeIn_0.5s]">
      <h3 className="text-xl font-semibold mb-6 text-primary border-b pb-2">
        <i className="fa-solid fa-paperclip"></i> 6. แนบไฟล์เอกสาร
      </h3>
      
      <form onSubmit={submitForm} noValidate>
        <div className="mb-6">
          <label htmlFor="mainBusiness" className="block text-sm font-medium text-textMain mb-1">ธุรกิจอื่นที่ท่านทำ</label>
          <p className="text-[13px] text-gray-500 mb-2 mt-[-4px]">กรุณาระบุประเภทธุรกิจ</p>
          <input
            id="mainBusiness"
            type="text"
            className="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-4 transition-colors border-border focus:border-primary focus:ring-primary/10"
            placeholder="กรุณาระบุ"
            value={formData.mainBusiness || ''}
            onChange={handleChange}
          />
        </div>
        
        <div className="mb-6">
          <label htmlFor="insuranceExperienceYears" className="block text-sm font-medium text-textMain mb-1">ประสบการณ์ในธุรกิจประกันภัย</label>
          <p className="text-[13px] text-gray-500 mb-2 mt-[-4px]">จำนวนปี เช่น 3</p>
          <input
            id="insuranceExperienceYears"
            type="number"
            min="0"
            className="w-full px-4 py-3 border rounded-md focus:outline-none focus:ring-4 transition-colors border-border focus:border-primary focus:ring-primary/10"
            placeholder="ต้องระบุเป็นตัวเลข"
            value={formData.insuranceExperienceYears || ''}
            onChange={handleChange}
          />
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
                  value={territory.id}
                  checked={(formData.salesTerritories || []).includes(territory.id.toString())}
                  onChange={(e) => handleCheckboxChange('salesTerritories', territory.id.toString(), e.target.checked)}
                />
                <span>{territory.name}</span>
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
                  value={specialty.id}
                  checked={(formData.insuranceSpecialty || []).includes(specialty.id.toString())}
                  onChange={(e) => handleCheckboxChange('insuranceSpecialty', specialty.id.toString(), e.target.checked)}
                />
                <span>{specialty.name}</span>
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
                  value={company.id}
                  checked={(formData.otherInsuranceCompanies || []).includes(company.id.toString())}
                  onChange={(e) => handleCheckboxChange('otherInsuranceCompanies', company.id.toString(), e.target.checked)}
                />
                <span>{company.name}</span>
              </label>
            ))}
          </div>
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
