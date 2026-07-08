import { useState } from 'react';
import { useRegistration } from '../../../context/RegistrationContext';
import Button from '../../../components/Button';
import Input from '../../../components/Input';
import CascadingAddress from '../../../components/CascadingAddress';

export default function Tab3Address() {
  const { nextStep, prevStep, formData, updateData } = useRegistration();
  const [errors, setErrors] = useState({});

  const handleChange = (e) => {
    updateData({ [e.target.id]: e.target.value });
    if (errors[e.target.id]) {
      setErrors(prev => ({ ...prev, [e.target.id]: '' }));
    }
  };

  const handleNext = (e) => {
    e.preventDefault();
    const newErrors = {};

    // Validate main address
    if (!formData.houseNo?.trim()) newErrors.houseNo = 'กรุณากรอกบ้านเลขที่';
    if (!formData.provinceId) newErrors.provinceId = 'กรุณาเลือกจังหวัด';
    if (!formData.districtId) newErrors.districtId = 'กรุณาเลือกอำเภอ';
    if (!formData.subDistrictId) newErrors.subDistrictId = 'กรุณาเลือกตำบล';

    // Validate shipping address if not same
    if (!formData.sameAddress) {
      if (!formData.shipHouseNo?.trim()) newErrors.shipHouseNo = 'กรุณากรอกบ้านเลขที่ (จัดส่ง)';
      if (!formData.shipprovinceId) newErrors.shipprovinceId = 'กรุณาเลือกจังหวัด (จัดส่ง)';
      if (!formData.shipdistrictId) newErrors.shipdistrictId = 'กรุณาเลือกอำเภอ (จัดส่ง)';
      if (!formData.shipsubDistrictId) newErrors.shipsubDistrictId = 'กรุณาเลือกตำบล (จัดส่ง)';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      // Optional: scroll to the first error
      const firstErrorId = Object.keys(newErrors)[0];
      document.getElementById(firstErrorId)?.focus();
      return;
    }

    nextStep();
  };

  return (
    <div className="animate-[fadeIn_0.5s]">
      <h3 className="text-xl font-semibold mb-6 text-primary border-b pb-2">ส่วนที่ 3: ที่อยู่ (Address)</h3>
      
      <form onSubmit={handleNext} noValidate>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <Input label="บ้านเลขที่" id="houseNo" required value={formData.houseNo || ''} onChange={handleChange} error={errors.houseNo} />
          <Input label="หมู่" id="moo" value={formData.moo || ''} onChange={handleChange} />
          <Input label="หมู่บ้าน/อาคาร/ชั้น" id="village" value={formData.village || ''} onChange={handleChange} />
          <Input label="ซอย" id="soi" value={formData.soi || ''} onChange={handleChange} />
        </div>

        <div className="grid grid-cols-1 gap-4 mt-2 mb-4">
          <Input label="ถนน" id="road" value={formData.road || ''} onChange={handleChange} />
        </div>
        
        <CascadingAddress prefix="" errors={errors} />

        <div className="mt-8 mb-4 flex items-center gap-3">
          <input 
            type="checkbox" 
            id="sameAddress" 
            className="w-5 h-5 accent-primary cursor-pointer"
            checked={formData.sameAddress || false}
            onChange={(e) => updateData({ sameAddress: e.target.checked })}
          />
          <label htmlFor="sameAddress" className="font-semibold cursor-pointer text-primary">
            ที่อยู่สำหรับจัดส่งเอกสารเหมือนกับที่อยู่ตามบัตรประชาชน
          </label>
        </div>

        {!formData.sameAddress && (
          <div className="p-4 border border-border bg-gray-50 rounded-lg animate-[fadeIn_0.3s]">
            <h4 className="font-semibold mb-4 text-textMain">ที่อยู่สำหรับจัดส่งเอกสาร</h4>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <Input label="บ้านเลขที่" id="shipHouseNo" required value={formData.shipHouseNo || ''} onChange={handleChange} error={errors.shipHouseNo} />
              <Input label="หมู่" id="shipMoo" value={formData.shipMoo || ''} onChange={handleChange} />
              <Input label="หมู่บ้าน/อาคาร" id="shipVillage" value={formData.shipVillage || ''} onChange={handleChange} />
              <Input label="ซอย" id="shipSoi" value={formData.shipSoi || ''} onChange={handleChange} />
            </div>
            <div className="grid grid-cols-1 gap-4 mt-2 mb-4">
              <Input label="ถนน" id="shipRoad" value={formData.shipRoad || ''} onChange={handleChange} />
            </div>

            <CascadingAddress prefix="ship" errors={errors} />
          </div>
        )}

        <div className="flex justify-between mt-10 pt-5 border-t border-border">
          <Button type="button" variant="secondary" onClick={prevStep}><i className="fas fa-arrow-left"></i> ย้อนกลับ</Button>
          <Button type="submit">ถัดไป <i className="fas fa-arrow-right"></i></Button>
        </div>
      </form>
    </div>
  );
}
