import { useState } from 'react';
import { useRegistration } from '../../../context/RegistrationContext';
import Button from '../../../components/Button';
import Input from '../../../components/Input';
import Select from '../../../components/Select';
import ThaiDatePicker from '../../../components/ThaiDatePicker';

export default function Tab2PersonalInfo() {
  const { nextStep, prevStep, formData, updateData, masterData } = useRegistration();

  const titleOptions = masterData.titles?.length > 0 ? masterData.titles.map(t => ({ value: t.name, label: t.name })) : [
    { value: 'นาย', label: 'นาย' },
    { value: 'นาง', label: 'นาง' },
    { value: 'นางสาว', label: 'นางสาว' },
    { value: 'อื่นๆ', label: 'อื่นๆ' },
  ];

  const religionOptions = masterData.religion?.length > 0 ? masterData.religion.map(r => ({ value: r.name, label: r.name })) : [
    { value: 'พุทธ', label: 'พุทธ' },
    { value: 'คริสต์', label: 'คริสต์' },
    { value: 'อิสลาม', label: 'อิสลาม' },
    { value: 'อื่นๆ', label: 'อื่นๆ' },
  ];

  const genderOptions = masterData.gender?.length > 0 ? masterData.gender.map(g => ({ value: g.name, label: g.name })) : [
    { value: 'ชาย', label: 'ชาย' },
    { value: 'หญิง', label: 'หญิง' },
  ];

  const bloodOptions = masterData.blood?.length > 0 ? masterData.blood.map(b => ({ value: b.name, label: b.name })) : [
    { value: 'A', label: 'A' },
    { value: 'B', label: 'B' },
    { value: 'O', label: 'O' },
    { value: 'AB', label: 'AB' },
  ];

  const [errors, setErrors] = useState({});

  const handleChange = (e) => {
    updateData({ [e.target.id || e.target.name]: e.target.value });
    // Clear error when user types
    if (errors[e.target.id || e.target.name]) {
      setErrors(prev => ({ ...prev, [e.target.id || e.target.name]: '' }));
    }
  };

  const handleNationalIdChange = (e) => {
    let val = e.target.value.replace(/\D/g, ''); // Remove non-digits
    if (val.length > 13) val = val.slice(0, 13);
    
    // Format to x-xxxx-xxxxx-xx-x
    let formatted = val;
    if (val.length > 1) formatted = formatted.slice(0, 1) + '-' + formatted.slice(1);
    if (val.length > 5) formatted = formatted.slice(0, 6) + '-' + formatted.slice(6);
    if (val.length > 10) formatted = formatted.slice(0, 12) + '-' + formatted.slice(12);
    if (val.length > 12) formatted = formatted.slice(0, 15) + '-' + formatted.slice(15);
    
    updateData({ nationalId: formatted });
    if (errors.nationalId) {
      setErrors(prev => ({ ...prev, nationalId: '' }));
    }
  };

  const handleNext = (e) => {
    e.preventDefault();
    
    // Custom Validation
    const newErrors = {};
    
    // Check required fields
    const requiredFields = [
      { id: 'nationalId', name: 'เลขประจำตัวประชาชน' },
      { id: 'idCardExpiry', name: 'วันหมดอายุบัตรประชาชน' },
      { id: 'titleTh', name: 'คำนำหน้าชื่อ' },
      { id: 'firstNameTh', name: 'ชื่อ' },
      { id: 'lastNameTh', name: 'นามสกุล' },
      { id: 'birthDate', name: 'วัน/เดือน/ปี เกิด' },
      { id: 'religion', name: 'ศาสนา' },
      { id: 'gender', name: 'เพศ' },
      { id: 'bloodGroup', name: 'กรุ๊ปเลือด' },
      { id: 'phone', name: 'หมายเลขโทรศัพท์มือถือ' },
      { id: 'email', name: 'อีเมล' },
      { id: 'emergencyContactName', name: 'ชื่อ-สกุล ผู้ติดต่อฉุกเฉิน' },
      { id: 'emergencyContactPhone', name: 'เบอร์โทรศัพท์ผู้ติดต่อฉุกเฉิน' }
    ];

    requiredFields.forEach(field => {
      if (!formData[field.id]) {
        newErrors[field.id] = `กรุณากรอก${field.name}`;
      }
    });

    if (formData.titleTh === 'อื่นๆ' && !formData.titleCustom) {
      newErrors.titleCustom = 'กรุณาระบุคำนำหน้าชื่อ';
    }

    if (formData.hasChangedName === 'yes') {
      if (!formData.titlePrev) newErrors.titlePrev = 'กรุณาระบุคำนำหน้าชื่อเดิม';
      if (!formData.firstNameOldTh) newErrors.firstNameOldTh = 'กรุณากรอกชื่อเดิม';
      if (!formData.lastNameOldTh) newErrors.lastNameOldTh = 'กรุณากรอกนามสกุลเดิม';
    }

    const rawNationalId = formData.nationalId ? formData.nationalId.replace(/-/g, '') : '';
    if (rawNationalId.length !== 13) {
      newErrors.nationalId = 'กรุณากรอกเลขบัตรประชาชนให้ครบ 13 หลัก';
    } else {
      // Validate Check Digit
      const idStr = rawNationalId;
      let sum = 0;
      for (let i = 0; i < 12; i++) {
        sum += parseFloat(idStr.charAt(i)) * (13 - i);
      }
      const checkDigit = (11 - (sum % 11)) % 10;
      if (parseFloat(idStr.charAt(12)) !== checkDigit) {
        newErrors.nationalId = 'เลขบัตรประชาชนไม่ถูกต้อง';
      }
    }
    if (formData.phone) {
      if (!/^0\d{9}$/.test(formData.phone)) {
        newErrors.phone = 'รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง (ต้องเป็นตัวเลข 10 หลัก และขึ้นต้นด้วย 0)';
      }
    }
    if (formData.emergencyContactPhone) {
      if (!/^0\d{8,9}$/.test(formData.emergencyContactPhone)) {
        newErrors.emergencyContactPhone = 'รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง (ต้องเป็นตัวเลข 9-10 หลัก และขึ้นต้นด้วย 0)';
      }
    }
    if (formData.email) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(formData.email)) {
        newErrors.email = 'รูปแบบอีเมลไม่ถูกต้อง';
      }
    }
    
    if (formData.idCardExpiry) {
      const expiryDate = new Date(formData.idCardExpiry);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      if (expiryDate < today) {
        newErrors.idCardExpiry = 'บัตรประชาชนหมดอายุแล้ว';
      }
    }

    if (formData.birthDate) {
      const birthDate = new Date(formData.birthDate);
      const today = new Date();
      let age = today.getFullYear() - birthDate.getFullYear();
      const m = today.getMonth() - birthDate.getMonth();
      if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
      }
      if (age < 20) {
        newErrors.birthDate = 'ผู้สมัครต้องมีอายุ 20 ปีบริบูรณ์ขึ้นไป';
      }
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
      <h2 className="text-2xl font-semibold mb-6 text-primary flex items-center gap-3 border-b-2 border-border pb-3">
        <i className="fa-solid fa-user"></i> 2. ข้อมูลส่วนบุคคล
      </h2>
      
      <form onSubmit={handleNext} noValidate>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
          <Input 
            label="เลขประจำตัวประชาชน (13 หลัก)" 
            id="nationalId" 
            required 
            maxLength={17} // 13 digits + 4 hyphens
            placeholder="x-xxxx-xxxxx-xx-x"
            value={formData.nationalId || ''} 
            onChange={handleNationalIdChange}
            error={errors.nationalId}
          />
          <div>
            <ThaiDatePicker 
              label="วันหมดอายุบัตรประชาชน" 
              id="idCardExpiry" 
              required
              value={formData.idCardExpiry || ''} 
              onChange={handleChange}
              error={errors.idCardExpiry}
            />
            <small className="text-textMuted block mt-1">* บัตรประชาชนตลอดชีพ ให้กรอกเป็นวันที่ 31 ธันวาคม 2599</small>
          </div>
        </div>

        <hr className="border-border my-8" />
        
        <h3 className="text-primary mb-5 text-[18px] font-semibold flex items-center gap-2">
          <i className="fa-solid fa-address-card"></i> ข้อมูลชื่อ-นามสกุล (ปัจจุบัน) เป็นภาษาไทยเท่านั้น
        </h3>

        <div className="mb-5 md:w-1/2 pr-2.5">
          <Select 
            label="คำนำหน้าชื่อ" 
            id="titleTh" 
            required 
            options={titleOptions}
            value={formData.titleTh || ''}
            onChange={handleChange}
            error={errors.titleTh}
          />
          {formData.titleTh === 'อื่นๆ' && (
            <div className="mt-3">
              <Input label="คำนำหน้าตามบัตรประชาชน (โปรดระบุ)" id="titleCustom" required value={formData.titleCustom || ''} onChange={handleChange} placeholder="ใส่คำตอบ" error={errors.titleCustom} />
            </div>
          )}
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
          <Input label="ชื่อ" id="firstNameTh" required value={formData.firstNameTh || ''} onChange={handleChange} placeholder="สมชาย" error={errors.firstNameTh} />
          <Input label="ชื่อกลาง (ตามบัตรประชาชน)" id="middleNameTh" value={formData.middleNameTh || ''} onChange={handleChange} placeholder="ถ้ามี" />
          <Input label="นามสกุล" id="lastNameTh" required value={formData.lastNameTh || ''} onChange={handleChange} placeholder="ใจดี" error={errors.lastNameTh} />
        </div>

        <hr className="border-border my-8" />

        <div className="mb-6">
          <label className="block mb-2 font-medium text-textMain after:content-['_*'] after:text-error">ท่านเคยเปลี่ยนชื่อหรือนามสกุลหรือไม่?</label>
          <div className="flex flex-col gap-2 mt-2">
            <label className={`flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all ${formData.hasChangedName === 'no' || !formData.hasChangedName ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-border bg-white hover:bg-gray-50'}`}>
              <input type="radio" name="hasChangedName" value="no" className="w-5 h-5 accent-primary cursor-pointer" checked={formData.hasChangedName === 'no' || !formData.hasChangedName} onChange={handleChange} />
              <span className="text-textMain">ไม่เคยเปลี่ยน</span>
            </label>
            <label className={`flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all ${formData.hasChangedName === 'yes' ? 'border-primary bg-primary/5 ring-1 ring-primary' : 'border-border bg-white hover:bg-gray-50'}`}>
              <input type="radio" name="hasChangedName" value="yes" className="w-5 h-5 accent-primary cursor-pointer" checked={formData.hasChangedName === 'yes'} onChange={handleChange} />
              <span className="text-textMain">เคยเปลี่ยน</span>
            </label>
          </div>
        </div>

        {formData.hasChangedName === 'yes' && (
          <div className="bg-[#F8F9FA] p-5 rounded-md border border-border mb-8">
            <h3 className="text-primary mb-5 text-[18px] font-semibold flex items-center gap-2">
              <i className="fa-solid fa-clock-rotate-left"></i> ข้อมูลชื่อ-นามสกุล (เดิม)
            </h3>
            
            <div className="mb-5 md:w-1/2 pr-2.5">
              <Select label="คำนำหน้าชื่อ" id="titlePrev" required options={titleOptions} value={formData.titlePrev || ''} onChange={handleChange} error={errors.titlePrev} />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
              <Input label="ชื่อ" id="firstNameOldTh" required value={formData.firstNameOldTh || ''} onChange={handleChange} placeholder="ชื่อเดิม" error={errors.firstNameOldTh} />
              <Input label="ชื่อกลาง (ตามบัตรประชาชน)" id="middleNameOldTh" value={formData.middleNameOldTh || ''} onChange={handleChange} placeholder="ถ้ามี" />
              <Input label="นามสกุล" id="lastNameOldTh" required value={formData.lastNameOldTh || ''} onChange={handleChange} placeholder="นามสกุลเดิม" error={errors.lastNameOldTh} />
            </div>
          </div>
        )}

        <div className="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
          <ThaiDatePicker label="วัน/เดือน/ปี เกิด" id="birthDate" required value={formData.birthDate || ''} onChange={handleChange} error={errors.birthDate} />
          <Select label="ศาสนา" id="religion" required options={religionOptions} value={formData.religion || ''} onChange={handleChange} error={errors.religion} />
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
          <Select label="เพศ" id="gender" required options={genderOptions} value={formData.gender || ''} onChange={handleChange} error={errors.gender} />
          <div>
            <Select label="กรุ๊ปเลือด" id="bloodGroup" required options={bloodOptions} value={formData.bloodGroup || ''} onChange={handleChange} error={errors.bloodGroup} />
            <small className="text-textMuted block mt-1">* ให้ระบุให้ครบถ้วนเพื่อความรวดเร็วในการอบรม</small>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
          <Input label="หมายเลขโทรศัพท์มือถือที่รับ OTP" id="phone" required maxLength={10} value={formData.phone || ''} onChange={handleChange} placeholder="0xx-xxx-xxxx" error={errors.phone} />
          <Input label="อีเมล (E-mail)" id="email" type="email" required value={formData.email || ''} onChange={handleChange} placeholder="example@email.com" error={errors.email} />
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
          <div>
            <Input 
              label={<span className="flex items-center gap-1"><i className="fa-brands fa-line text-[#00B900] text-lg"></i> Line ID</span>} 
              id="lineId" value={formData.lineId || ''} onChange={handleChange} placeholder="Line ID" 
            />
          </div>
          <div>
            <Input 
              label={<span className="flex items-center gap-1"><i className="fa-brands fa-facebook text-[#1877F2] text-lg"></i> Facebook</span>} 
              id="facebook" value={formData.facebook || ''} onChange={handleChange} placeholder="ชื่อบัญชี Facebook" 
            />
          </div>
          <div>
            <Input 
              label={<span className="flex items-center gap-1"><i className="fa-brands fa-instagram text-[#E1306C] text-lg"></i> Instagram</span>} 
              id="instagram" value={formData.instagram || ''} onChange={handleChange} placeholder="ชื่อบัญชี Instagram" 
            />
          </div>
        </div>

        <h4 className="text-[18px] font-semibold mt-8 mb-5 text-primary flex items-center gap-2">
          <i className="fa-solid fa-phone-volume"></i> กรณีฉุกเฉิน
        </h4>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          <Input label="ชื่อ-สกุล ผู้ติดต่อฉุกเฉิน" id="emergencyContactName" required value={formData.emergencyContactName || ''} onChange={handleChange} error={errors.emergencyContactName} />
          <Input label="เบอร์โทรศัพท์ผู้ติดต่อฉุกเฉิน" id="emergencyContactPhone" required maxLength={10} value={formData.emergencyContactPhone || ''} onChange={handleChange} error={errors.emergencyContactPhone} />
        </div>

        <div className="flex justify-between mt-10 pt-5 border-t border-border">
          <Button type="button" variant="secondary" onClick={prevStep}>
            <i className="fas fa-arrow-left mr-2"></i> ย้อนกลับ
          </Button>
          <Button type="submit">
            ถัดไป <i className="fas fa-arrow-right ml-2"></i>
          </Button>
        </div>
      </form>
    </div>
  );
}
