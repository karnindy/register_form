import { useRegistration } from '../../../context/RegistrationContext';
import Button from '../../../components/Button';
import Input from '../../../components/Input';
import Select from '../../../components/Select';

export default function Tab4License() {
  const { nextStep, prevStep, formData, updateData } = useRegistration();

  const handleChange = (e) => {
    updateData({ [e.target.id]: e.target.value });
  };

  const agentTypeOptions = [
    { value: 'agent', label: 'ตัวแทนประกันวินาศภัย' },
    { value: 'broker', label: 'นายหน้าประกันวินาศภัย' },
  ];

  const licenseStatusOptions = [
    { value: 'new', label: 'ขอรับใบอนุญาตใหม่' },
    { value: 'renew1', label: 'ต่ออายุใบอนุญาตครั้งที่ 1' },
    { value: 'renew2', label: 'ต่ออายุใบอนุญาตครั้งที่ 2' },
    { value: 'renew3', label: 'ต่ออายุใบอนุญาตครั้งที่ 3' },
    { value: 'renew4', label: 'ต่ออายุใบอนุญาตครั้งที่ 4 เป็นต้นไป' },
  ];

  return (
    <div className="animate-[fadeIn_0.5s]">
      <h3 className="text-xl font-semibold mb-6 text-primary border-b pb-2">ส่วนที่ 4: ข้อมูลใบอนุญาต (License Information)</h3>
      
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <Select label="ประเภทบุคคล" id="agentType" required options={agentTypeOptions} value={formData.agentType || ''} onChange={handleChange} />
        <Select label="สถานะการขอรับ/ต่ออายุ" id="licenseStatus" required options={licenseStatusOptions} value={formData.licenseStatus || ''} onChange={handleChange} />
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
        <Input label="เลขที่ใบอนุญาต (ถ้ามี)" id="licenseNo" value={formData.licenseNo || ''} onChange={handleChange} />
        <Input label="วันที่ออกใบอนุญาต" id="licenseIssue" type="date" value={formData.licenseIssue || ''} onChange={handleChange} />
        <Input label="วันที่บัตรหมดอายุ" id="licenseExpire" type="date" value={formData.licenseExpire || ''} onChange={handleChange} />
      </div>

      <h4 className="text-lg font-semibold mt-6 mb-4 text-primary border-b pb-2">สังกัดภูมิภาค/สาขา (วิริยะประกันภัย)</h4>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <Select label="ภาคที่สังกัด" id="agentRegion" required options={[]} value={formData.agentRegion || ''} onChange={handleChange} />
        <Select label="สาขาที่สังกัด" id="agentBranch" required options={[]} value={formData.agentBranch || ''} onChange={handleChange} />
      </div>

      <div className="flex justify-between mt-10 pt-5 border-t border-border">
        <Button variant="secondary" onClick={prevStep}><i className="fas fa-arrow-left"></i> ย้อนกลับ</Button>
        <Button onClick={nextStep}>ถัดไป <i className="fas fa-arrow-right"></i></Button>
      </div>
    </div>
  );
}
