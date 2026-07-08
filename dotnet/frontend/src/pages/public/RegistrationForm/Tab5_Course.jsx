import { useState } from 'react';
import { useRegistration } from '../../../context/RegistrationContext';
import Button from '../../../components/Button';
import Select from '../../../components/Select';

export default function Tab5Course() {
  const { nextStep, prevStep, formData, updateData } = useRegistration();
  const [errors, setErrors] = useState({});

  const handleCheckboxChange = (name, value, isChecked) => {
    const currentArray = formData[name] || [];
    let newArray;
    if (isChecked) {
      newArray = [...currentArray, value];
    } else {
      newArray = currentArray.filter(item => item !== value);
    }
    updateData({ [name]: newArray });
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }));
    }
  };

  const handleNext = (e) => {
    e.preventDefault();
    const newErrors = {};

    if (!formData.courseType) {
      newErrors.courseType = 'กรุณาเลือกระดับคอร์สที่ต้องการอบรม';
    }

    if (formData.courseType && !formData.trainingDate) {
      newErrors.trainingDate = 'กรุณาเลือกรอบวันอบรม';
    }

    if ((formData.deductionPrivilege || []).includes('MasterDegree') && !formData.masterDegreeStatus) {
      newErrors.masterDegreeStatus = 'กรุณาระบุสถานะการศึกษา';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      // Optional: scroll to the first error
      const firstErrorId = Object.keys(newErrors)[0];
      document.getElementById(firstErrorId)?.scrollIntoView({ behavior: 'smooth' });
      return;
    }

    nextStep();
  };

  // Fake course options for now (would come from API)
  const courseOptions = [
    'ขอรับใบอนุญาตตัวแทน/นายหน้าประกันวินาศภัย',
    'ต่ออายุใบอนุญาตตัวแทน/นายหน้าประกันวินาศภัย ครั้งที่ 1',
    'ต่ออายุใบอนุญาตตัวแทน/นายหน้าประกันวินาศภัย ครั้งที่ 2',
    'ต่ออายุใบอนุญาตตัวแทน/นายหน้าประกันวินาศภัย ครั้งที่ 3',
    'ต่ออายุใบอนุญาตตัวแทน/นายหน้าประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป'
  ];

  const previousCoursesList = [
    "การกำกับดูแลบริษัทประกันภัยระดับเสียง",
    "การจัดการสินไหมทดแทน Non-Motor",
    "การประกันภัยความเสี่ยงภัยทรัพย์สิน",
    "ทางเปลี่ยนผ่าน",
    "ทางเปลี่ยนผ่านสำหรับตัวแทนและนายหน้าประกันภัย",
    "การประกันภัยรถยนต์",
    "จรรยาบรรณวิชาชีพของตัวแทน/นายหน้าประกันภัย",
    "พ.ร.บ.จราจรทางบก พ.ศ.2522 (ปี2562) และการพิจารณาคดี/อาญาความผิดอุบัติเหตุจราจร",
    "พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล",
    "การถูกฉ้อฉลฉ้อโกงประกันภัย",
    "มาตรการการป้องกันและปราบปรามการฟอกเงินและการต่อต้านการสนับสนุนทางการเงินแก่การก่อการร้าย",
    "ประกันรถยนต์ไฟฟ้าและข้อควรระวังจากการใช้รถ",
    "พ.ร.บ.ทวงหนี้",
    "ความรับผิดในฐานะตัวแทน/นายหน้าประกันภัย",
    "การตลาดบุคคล",
    "หลักประกันสุขภาพ",
    "การพิจารณารับประกันรถยนต์"
  ];

  const showPreviousCourses = formData.courseType?.includes('ครั้งที่ 4 เป็นต้นไป');
  const showDeductionPrivilege = formData.courseType?.includes('ครั้งที่ 4 เป็นต้นไป');

  return (
    <div className="animate-[fadeIn_0.5s]">
      <h3 className="text-xl font-semibold mb-6 text-primary border-b pb-2">ส่วนที่ 5: หลักสูตรที่ต้องการอบรม (Course Selection)</h3>
      
      <form onSubmit={handleNext} noValidate>
        <div className="mb-6" id="courseType">
          <label className="block mb-3 font-medium text-textMain after:content-['_*'] after:text-error text-lg text-primary">ระดับคอร์ส</label>
          <div className="flex flex-col gap-3">
            {courseOptions.map((course, idx) => (
              <label key={idx} className={`flex items-center gap-3 cursor-pointer p-3 border rounded-md transition-colors bg-white ${errors.courseType ? 'border-error' : 'hover:bg-gray-50'}`}>
                <input
                  type="radio"
                  name="courseType"
                  className="w-4 h-4 accent-primary"
                  value={course}
                  checked={formData.courseType === course}
                  onChange={() => {
                    updateData({ courseType: course, trainingDate: '' });
                    if (errors.courseType) setErrors(prev => ({ ...prev, courseType: '' }));
                  }}
                />
                {course}
              </label>
            ))}
          </div>
          {errors.courseType && <p className="text-error text-sm mt-2">{errors.courseType}</p>}
        </div>

        {showPreviousCourses && (
          <div className="mb-6 animate-[fadeIn_0.3s]">
            <label className="block mb-2 font-medium text-textMain">
              หากท่านขอต่ออายุใบอนุญาตเป็นตัวแทนหรือนายหน้าประกันวินาศภัยครั้งที่ 4 เป็นต้นไป โปรดระบุวิชาที่ท่านเคยอบรม (5 วิชา)<br/>
              <span className="text-error text-sm font-normal">* สำคัญ * : ห้ามท่านเลือกลงทะเบียนอบรมวิชาที่เคยอบรมแล้ว หากฝ่าฝืนจะไม่สามารถขอรับใบอนุญาตได้</span>
            </label>
            <div className="p-4 border border-border rounded-md bg-gray-50 max-h-60 overflow-y-auto flex flex-col gap-3">
              {previousCoursesList.map((course, idx) => (
                <label key={idx} className="flex items-start gap-3 cursor-pointer">
                  <input
                    type="checkbox"
                    name="previousCourses"
                    className="mt-1 w-4 h-4 accent-primary"
                    value={course}
                    checked={(formData.previousCourses || []).includes(course)}
                    onChange={(e) => handleCheckboxChange('previousCourses', course, e.target.checked)}
                  />
                  <span className="text-sm">{course}</span>
                </label>
              ))}
            </div>
          </div>
        )}

        {formData.courseType && (
          <div className="mb-6 animate-[fadeIn_0.3s]" id="trainingDate">
            <label className="block mb-3 font-medium text-textMain after:content-['_*'] after:text-error text-lg text-primary">เลือกรอบวันอบรม</label>
            <div className="flex flex-col gap-3">
              {/* Mock training dates */}
              <label className={`flex items-center gap-3 cursor-pointer p-3 border rounded-md bg-white ${errors.trainingDate ? 'border-error' : 'hover:bg-gray-50'}`}>
                <input type="radio" name="trainingDate" className="w-4 h-4 accent-primary" value="รอบวันที่ 10 สิงหาคม 2569" checked={formData.trainingDate === "รอบวันที่ 10 สิงหาคม 2569"} onChange={(e) => { updateData({ trainingDate: e.target.value }); if (errors.trainingDate) setErrors(prev => ({ ...prev, trainingDate: '' })); }} />
                รอบวันที่ 10 สิงหาคม 2569
              </label>
              <label className={`flex items-center gap-3 cursor-pointer p-3 border rounded-md bg-white ${errors.trainingDate ? 'border-error' : 'hover:bg-gray-50'}`}>
                <input type="radio" name="trainingDate" className="w-4 h-4 accent-primary" value="รอบวันที่ 25 สิงหาคม 2569" checked={formData.trainingDate === "รอบวันที่ 25 สิงหาคม 2569"} onChange={(e) => { updateData({ trainingDate: e.target.value }); if (errors.trainingDate) setErrors(prev => ({ ...prev, trainingDate: '' })); }} />
                รอบวันที่ 25 สิงหาคม 2569
              </label>
            </div>
            {errors.trainingDate && <p className="text-error text-sm mt-2">{errors.trainingDate}</p>}
          </div>
        )}

        {showDeductionPrivilege && (
          <>
            <hr className="border-t border-border my-8" />
            <div className="mb-6 animate-[fadeIn_0.3s]">
              <label className="block mb-3 font-medium text-textMain">สิทธิลดหย่อนการอบรม</label>
              <label className="flex items-start gap-3 cursor-pointer">
                <input
                  type="checkbox"
                  name="deductionPrivilege"
                  className="mt-1 w-4 h-4 accent-primary"
                  value="MasterDegree"
                  checked={(formData.deductionPrivilege || []).includes('MasterDegree')}
                  onChange={(e) => handleCheckboxChange('deductionPrivilege', 'MasterDegree', e.target.checked)}
                />
                <span>สำเร็จการศึกษาระดับปริญญาโทขึ้นไป จากสถาบันอุดมศึกษาที่ คปภ. รับรอง</span>
              </label>

              {(formData.deductionPrivilege || []).includes('MasterDegree') && (
                <div className="ml-7 mt-4 animate-[fadeIn_0.3s]" id="masterDegreeStatus">
                  <label className="block mb-1 font-medium text-textMain after:content-['_*'] after:text-error">กรุณาระบุสถานะการศึกษา</label>
                  <span className="text-error text-sm block mb-3">* ต้องนำส่งหลักฐานประกอบการขอลดหย่อน</span>
                  <div className="flex flex-col gap-2">
                    <label className="flex items-center gap-3 cursor-pointer">
                      <input type="radio" name="masterDegreeStatus" className="w-4 h-4 accent-primary" value="อยู่ระหว่างการขออนุมัติ" checked={formData.masterDegreeStatus === "อยู่ระหว่างการขออนุมัติ"} onChange={(e) => { updateData({ masterDegreeStatus: e.target.value }); if (errors.masterDegreeStatus) setErrors(prev => ({ ...prev, masterDegreeStatus: '' })); }} />
                      อยู่ระหว่างการขออนุมัติ
                    </label>
                    <label className="flex items-center gap-3 cursor-pointer">
                      <input type="radio" name="masterDegreeStatus" className="w-4 h-4 accent-primary" value="อนุมัติแล้ว" checked={formData.masterDegreeStatus === "อนุมัติแล้ว"} onChange={(e) => { updateData({ masterDegreeStatus: e.target.value }); if (errors.masterDegreeStatus) setErrors(prev => ({ ...prev, masterDegreeStatus: '' })); }} />
                      อนุมัติแล้ว
                    </label>
                  </div>
                  {errors.masterDegreeStatus && <p className="text-error text-sm mt-2">{errors.masterDegreeStatus}</p>}
                </div>
              )}
            </div>
          </>
        )}

        <div className="flex justify-between mt-10 pt-5 border-t border-border">
          <Button type="button" variant="secondary" onClick={prevStep}><i className="fas fa-arrow-left"></i> ย้อนกลับ</Button>
          <Button type="submit">ถัดไป <i className="fas fa-arrow-right"></i></Button>
        </div>
      </form>
    </div>
  );
}
