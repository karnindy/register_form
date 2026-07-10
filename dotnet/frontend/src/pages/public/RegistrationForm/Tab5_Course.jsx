import { useState, useEffect } from 'react';
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

    const selectedCourseObj = courseOptions.find(c => c.courseName === formData.courseType);
    const isComplex = selectedCourseObj && selectedCourseObj.dateId === null;

    if (formData.courseType && !isComplex && !formData.trainingDate) {
      newErrors.trainingDate = 'กรุณาเลือกรอบวันอบรม';
    }

    if (isComplex) {
      if (!formData.selectedSubjects || formData.selectedSubjects.length === 0) {
        newErrors.selectedSubjects = 'กรุณาเลือกวิชาที่ประสงค์จะเข้าอบรมอย่างน้อย 1 วิชา';
      }
    }

    if ((formData.deductionPrivilege || []).includes('MasterDegree') && !formData.masterDegreeStatus) {
      newErrors.masterDegreeStatus = 'กรุณาระบุสถานะการยื่นเอกสาร';
    }

    if (formData.courseType?.includes('ต่อใบอนุญาต')) {
      if (!formData.licenseNo?.trim() || !formData.licenseExpire) {
        newErrors.courseType = 'กรุณาย้อนกลับไปกรอก "เลขที่ใบอนุญาต" และ "วันที่บัตรหมดอายุ" ในส่วนที่ 4';
      }
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

  const [courseOptions, setCourseOptions] = useState([]);
  const [isLoadingCourses, setIsLoadingCourses] = useState(true);
  const [renewCourseCheckboxes, setRenewCourseCheckboxes] = useState([]);
  const [renewOtherOptions, setRenewOtherOptions] = useState([]);

  useEffect(() => {
    const fetchCourses = async () => {
      try {
        setIsLoadingCourses(true);
        const agentType = formData.agentType || 'agent'; // Default to agent if not set
        const res = await fetch(`http://localhost:8085/api/masterdata/renew-courses?agentType=${agentType}`);
        if (res.ok) {
          const data = await res.json();
          setCourseOptions(data);
        }
      } catch (err) {
        console.error("Failed to load courses", err);
      } finally {
        setIsLoadingCourses(false);
      }
    };
    
    fetchCourses();
  }, [formData.agentType]);

  useEffect(() => {
    const fetchRenewCheckboxes = async () => {
      try {
        const res = await fetch(`http://localhost:8085/api/masterdata/renewcourse`);
        if (res.ok) {
          const data = await res.json();
          setRenewCourseCheckboxes(data.map(item => item.name));
        }
      } catch (err) {
        console.error("Failed to load renew courses", err);
      }
    };
    fetchRenewCheckboxes();
  }, []);

  useEffect(() => {
    const fetchRenewOtherOptions = async () => {
      try {
        const res = await fetch(`http://localhost:8085/api/masterdata/renew-other-courses`);
        if (res.ok) {
          const data = await res.json();
          setRenewOtherOptions(data.map(item => item.displayName));
        }
      } catch (err) {
        console.error("Failed to load renew other courses", err);
      }
    };
    fetchRenewOtherOptions();
  }, []);

  const selectedCourseObj = courseOptions.find(c => c.courseName === formData.courseType);
  const isComplexCourse = selectedCourseObj && selectedCourseObj.dateId === null;
  const showDeductionPrivilege = formData.courseType === 'ขอต่อใบอนุญาตเป็นตัวแทน/นายหน้าประกันวินาศภัย 4 เป็นต้นไป';

  return (
    <div className="bg-white p-6 md:p-8 rounded-lg shadow-sm border border-border">
      <h3 className="text-xl font-semibold mb-6 text-primary border-b pb-2">
        <i className="fa-solid fa-chalkboard-user"></i> 5. หลักสูตรที่ประสงค์เข้าอบรม
      </h3>
      
      <form onSubmit={handleNext} noValidate>
        <div className="mb-6">
          <label className="block mb-2 font-medium text-textMain after:content-['_*'] after:text-error">ระดับคอร์ส</label>
          <div className="flex flex-col gap-3">
            {courseOptions.map((course, idx) => (
              <label key={idx} className={`flex items-center gap-3 cursor-pointer p-3 border rounded-md transition-colors bg-white ${errors.courseType ? 'border-error' : 'hover:bg-gray-50'}`}>
                <input
                  type="radio"
                  name="courseType"
                  className="w-4 h-4 accent-primary"
                  value={course.courseName}
                  checked={formData.courseType === course.courseName}
                  onChange={() => {
                    updateData({ courseType: course.courseName, trainingDate: '', previousCourses: [], selectedSubjects: [] });
                    if (errors.courseType) setErrors(prev => ({ ...prev, courseType: '' }));
                  }}
                />
                {course.courseName}
              </label>
            ))}
          </div>
          {errors.courseType && <p className="text-error text-sm mt-2">{errors.courseType}</p>}
        </div>

        {isComplexCourse && (
          <div className="mb-6 animate-[fadeIn_0.3s]">
            
            <label className="block mb-2 font-medium text-textMain mt-2">
              หากท่านถือใบอนุญาตเป็นตัวแทนหรือนายหน้าประกันวินาศภัยที่ต่ออายุครั้งที่ 4 เป็นต้นไป โปรดระบุวิชาที่ท่านเคยเข้าอบรมในรอบการสะสมชั่วโมงอบรมปัจจุบัน (5 ปี)<br/>
              <span className="text-error text-sm font-normal">* สำคัญ * : เพื่อท่านจะต้องไม่อบรมวิชาที่เคยเข้าอบรมซ้ำอีก ตามข้อกำหนดของ สำนักงาน คปภ.</span>
            </label>
            <div className="p-4 border border-border rounded-md bg-gray-50 flex flex-col gap-3 mb-6">
              {renewCourseCheckboxes.map((course, idx) => (
                <label key={idx} className="flex items-start gap-3 cursor-pointer">
                  <input
                    type="checkbox"
                    name="previousCourses"
                    className="mt-1 w-4 h-4 accent-primary cursor-pointer"
                    value={course}
                    checked={(formData.previousCourses || []).includes(course)}
                    onChange={(e) => {
                      const isChecked = e.target.checked;
                      handleCheckboxChange('previousCourses', course, isChecked);
                      if (isChecked) {
                        const newSelectedSubjects = (formData.selectedSubjects || []).filter(s => !s.includes(course));
                        updateData({ selectedSubjects: newSelectedSubjects });
                      }
                    }}
                  />
                  <span className="text-sm">{course}</span>
                </label>
              ))}
            </div>

            <label className="block mb-3 font-medium text-primary text-lg">{selectedCourseObj.courseName} <span className="text-error">*</span></label>
            <div className="mb-4">
              <span className="text-error text-sm block mb-1">* เลือกได้มากกว่า 1 วิชา *</span>
              <span className="text-error text-sm block mb-2">* หากเลือกวิชาที่เคยอบรม จะไม่นับรวมรอบปัจจุบัน (5 ปี) *</span>
              <div className="flex flex-col gap-3">
                {renewOtherOptions.map((course, idx) => {
                  const isDisabled = (formData.previousCourses || []).some(prev => course.includes(prev));
                  return (
                    <label key={idx} className={`flex items-start gap-3 p-3 border rounded-md ${isDisabled ? 'bg-gray-100 opacity-60 cursor-not-allowed' : 'bg-white hover:bg-gray-50 cursor-pointer'}`}>
                      <input
                        type="checkbox"
                        name="selectedSubjects"
                        className={`mt-1 w-4 h-4 ${isDisabled ? '' : 'accent-primary cursor-pointer'}`}
                        value={course}
                        checked={(formData.selectedSubjects || []).includes(course)}
                        disabled={isDisabled}
                        onChange={(e) => handleCheckboxChange('selectedSubjects', course, e.target.checked)}
                      />
                      <span className="text-sm">{course}</span>
                    </label>
                  );
                })}
              </div>
              {errors.selectedSubjects && <p className="text-error text-sm mt-2">{errors.selectedSubjects}</p>}
            </div>

          </div>
        )}

        {formData.courseType && !isComplexCourse && selectedCourseObj?.dateDisplay && (
          <div className="mb-6 animate-[fadeIn_0.3s]" id="trainingDate">
            <label className="block mb-3 font-medium text-primary after:content-['_*'] after:text-error text-lg">{selectedCourseObj.courseName}</label>
            <div className="flex flex-col gap-3">
              <label className={`flex items-center gap-3 cursor-pointer p-3 border rounded-md bg-white ${errors.trainingDate ? 'border-error' : 'hover:bg-gray-50'}`}>
                <input type="radio" name="trainingDate" className="w-4 h-4 accent-primary" value={selectedCourseObj.dateDisplay} checked={formData.trainingDate === selectedCourseObj.dateDisplay} onChange={(e) => { updateData({ trainingDate: e.target.value }); if (errors.trainingDate) setErrors(prev => ({ ...prev, trainingDate: '' })); }} />
                {selectedCourseObj.dateDisplay}
              </label>
            </div>
            {errors.trainingDate && <p className="text-error text-sm mt-2">{errors.trainingDate}</p>}
          </div>
        )}

        {showDeductionPrivilege && (
          <>
            <hr className="border-t border-border my-8" />
            <div className="mb-6 animate-[fadeIn_0.3s]">
              <label className="block mb-3 font-medium text-textMain">สิทธิ์ลดหย่อนชั่วโมงอบรม</label>
              <div className="p-4 border border-primary rounded-md bg-white">
                <label className="flex items-start gap-3 cursor-pointer">
                  <input
                    type="checkbox"
                    name="deductionPrivilege"
                    className="mt-1 w-4 h-4 accent-primary"
                    value="MasterDegree"
                    checked={(formData.deductionPrivilege || []).includes('MasterDegree')}
                    onChange={(e) => handleCheckboxChange('deductionPrivilege', 'MasterDegree', e.target.checked)}
                  />
                  <span>สำเร็จการศึกษาตั้งแต่ระดับปริญญาโทขึ้นไป จากสถาบันอุดมศึกษาหรือสถาบันการศึกษาในต่างประเทศที่สำนักงานคณะกรรมการข้าราชการพลเรือนรับรอง</span>
                </label>
              </div>

              {(formData.deductionPrivilege || []).includes('MasterDegree') && (
                <div className="mt-6 animate-[fadeIn_0.3s]" id="masterDegreeStatus">
                  <label className="block mb-1 font-medium text-textMain after:content-['_*'] after:text-error">กรุณาระบุสถานะการยื่นเอกสาร</label>
                  <span className="text-error text-sm block mb-4">* หากท่านเคยยื่นเอกสารและบันทึกในระบบของสำนักงาน คปภ. แล้วไม่ต้องยื่นซ้ำ</span>
                  <div className="flex flex-col gap-3">
                    <label className="flex items-center gap-3 cursor-pointer p-3 border rounded-md bg-white hover:bg-gray-50">
                      <input type="radio" name="masterDegreeStatus" className="w-4 h-4 accent-primary" value="เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว" checked={formData.masterDegreeStatus === "เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว"} onChange={(e) => { updateData({ masterDegreeStatus: e.target.value }); if (errors.masterDegreeStatus) setErrors(prev => ({ ...prev, masterDegreeStatus: '' })); }} />
                      เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว
                    </label>
                    <label className="flex items-center gap-3 cursor-pointer p-3 border rounded-md bg-white hover:bg-gray-50">
                      <input type="radio" name="masterDegreeStatus" className="w-4 h-4 accent-primary" value="ไม่เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว" checked={formData.masterDegreeStatus === "ไม่เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว"} onChange={(e) => { updateData({ masterDegreeStatus: e.target.value }); if (errors.masterDegreeStatus) setErrors(prev => ({ ...prev, masterDegreeStatus: '' })); }} />
                      ไม่เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว
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
