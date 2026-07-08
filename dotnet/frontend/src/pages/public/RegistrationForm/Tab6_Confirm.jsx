import { useRegistration } from '../../../context/RegistrationContext';
import Button from '../../../components/Button';
import Input from '../../../components/Input';
import Select from '../../../components/Select';
import { useNavigate } from 'react-router-dom';

export default function Tab6Confirm() {
  const { prevStep, formData, updateData } = useRegistration();
  const navigate = useNavigate();

  const handleChange = (e) => {
    updateData({ [e.target.id]: e.target.value });
  };

  const submitForm = async () => {
    if (!formData.certifyTrue) {
      alert("กรุณายืนยันว่าข้อมูลเป็นความจริง");
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

  const eduOptions = [
    { value: 'highschool', label: 'มัธยมศึกษา' },
    { value: 'bachelor', label: 'ปริญญาตรี' },
    { value: 'master', label: 'ปริญญาโท' },
    { value: 'other', label: 'อื่นๆ' },
  ];

  return (
    <div className="animate-[fadeIn_0.5s]">
      <h3 className="text-xl font-semibold mb-6 text-primary border-b pb-2">ส่วนที่ 6: ข้อมูลเพิ่มเติมและยืนยัน (Additional Info)</h3>
      
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <Select label="ระดับการศึกษาสูงสุด" id="education" options={eduOptions} value={formData.education || ''} onChange={handleChange} />
        <Input label="อาชีพหลัก/ธุรกิจหลัก" id="occupation" value={formData.occupation || ''} onChange={handleChange} />
      </div>
      
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
        <Select label="มีประสบการณ์ด้านประกันภัยหรือไม่" id="hasExperience" options={[{value:'yes',label:'มี'},{value:'no',label:'ไม่มี'}]} value={formData.hasExperience || ''} onChange={handleChange} />
        <Input label="สิ่งที่คาดหวังจากการอบรมครั้งนี้" id="expectation" value={formData.expectation || ''} onChange={handleChange} />
      </div>

      <div className="mt-8 mb-6 p-4 bg-gray-50 border border-border rounded-md">
        <h4 className="font-semibold mb-3">คำรับรองผู้สมัคร</h4>
        <div className="flex items-start gap-3">
          <input 
            type="checkbox" 
            id="certifyTrue" 
            className="mt-1 w-5 h-5 accent-primary cursor-pointer"
            checked={formData.certifyTrue || false}
            onChange={(e) => updateData({ certifyTrue: e.target.checked })}
          />
          <label htmlFor="certifyTrue" className="cursor-pointer">
            ข้าพเจ้าขอรับรองว่าข้อความและข้อมูลข้างต้นเป็นความจริงทุกประการ หากตรวจสอบพบว่าไม่เป็นความจริง ข้าพเจ้ายินยอมให้ยกเลิกการลงทะเบียน
          </label>
        </div>
      </div>

      <div className="flex justify-between mt-10 pt-5 border-t border-border">
        <Button variant="secondary" onClick={prevStep}><i className="fas fa-arrow-left"></i> ย้อนกลับ</Button>
        <Button onClick={submitForm} disabled={!formData.certifyTrue} className={!formData.certifyTrue ? 'opacity-50 cursor-not-allowed' : ''}>
          บันทึกข้อมูลและยืนยัน <i className="fas fa-save"></i>
        </Button>
      </div>
    </div>
  );
}
