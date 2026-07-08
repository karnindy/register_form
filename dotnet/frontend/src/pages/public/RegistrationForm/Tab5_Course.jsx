import { useRegistration } from '../../../context/RegistrationContext';
import Button from '../../../components/Button';
import Select from '../../../components/Select';

export default function Tab5Course() {
  const { nextStep, prevStep, formData, updateData } = useRegistration();

  const handleChange = (e) => {
    updateData({ [e.target.id]: e.target.value });
  };

  const formatOptions = [
    { value: 'online', label: 'อบรมออนไลน์ (E-Learning)' },
    { value: 'onsite', label: 'อบรมในห้องเรียน (Onsite)' },
  ];

  return (
    <div className="animate-[fadeIn_0.5s]">
      <h3 className="text-xl font-semibold mb-6 text-primary border-b pb-2">ส่วนที่ 5: หลักสูตรที่ต้องการอบรม (Course Selection)</h3>
      
      <div className="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-md mb-6">
        <p><i className="fas fa-info-circle mr-2"></i> กรุณาเลือกหลักสูตรและวันที่ต้องการอบรมให้ถูกต้องตรงกับประเภทใบอนุญาตของคุณ</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <Select label="รูปแบบการอบรม" id="trainingFormat" required options={formatOptions} value={formData.trainingFormat || ''} onChange={handleChange} />
        <Select label="หลักสูตร" id="courseType" required options={[]} value={formData.courseType || ''} onChange={handleChange} />
      </div>

      <div className="grid grid-cols-1 mt-2">
        <Select label="วันที่ต้องการอบรม" id="trainingDate" required options={[]} value={formData.trainingDate || ''} onChange={handleChange} />
      </div>

      <div className="flex justify-between mt-10 pt-5 border-t border-border">
        <Button variant="secondary" onClick={prevStep}><i className="fas fa-arrow-left"></i> ย้อนกลับ</Button>
        <Button onClick={nextStep}>ถัดไป <i className="fas fa-arrow-right"></i></Button>
      </div>
    </div>
  );
}
