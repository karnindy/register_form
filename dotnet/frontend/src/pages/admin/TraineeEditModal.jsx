import { useState, useEffect } from 'react';
import Select from 'react-select';

// --- AddressBlock Sub-component ---
const AddressBlock = ({ address, index, masterData, handleArrayChange, handleReactSelectChange }) => {
  const [districts, setDistricts] = useState([]);
  const [subDistricts, setSubDistricts] = useState([]);
  const [loadingD, setLoadingD] = useState(false);
  const [loadingS, setLoadingS] = useState(false);

  useEffect(() => {
    if (address.provinceId) {
      setLoadingD(true);
      fetch(`http://localhost:8085/api/masterdata/districts/${address.provinceId}`)
        .then(res => res.json())
        .then(data => setDistricts(data))
        .catch(err => console.error(err))
        .finally(() => setLoadingD(false));
    } else {
      setDistricts([]);
    }
  }, [address.provinceId]);

  useEffect(() => {
    if (address.districtId) {
      setLoadingS(true);
      fetch(`http://localhost:8085/api/masterdata/subdistricts/${address.districtId}`)
        .then(res => res.json())
        .then(data => setSubDistricts(data))
        .catch(err => console.error(err))
        .finally(() => setLoadingS(false));
    } else {
      setSubDistricts([]);
    }
  }, [address.districtId]);

  const title = address.addressType === 'A' ? 'ที่อยู่ตามทะเบียนบ้าน' : 'ที่อยู่จัดส่งเอกสาร';

  return (
    <div className="bg-white p-4 rounded shadow-sm mb-4">
      <h4 className="font-semibold text-gray-700 border-b pb-2 mb-4">{title}</h4>
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div>
          <label className="block text-sm text-gray-600">บ้านเลขที่</label>
          <input type="text" name="houseNo" value={address.houseNo || ''} onChange={(e) => handleArrayChange(e, 'addresses', index)} className="w-full border rounded p-2 mt-1" />
        </div>
        <div>
          <label className="block text-sm text-gray-600">หมู่</label>
          <input type="text" name="moo" value={address.moo || ''} onChange={(e) => handleArrayChange(e, 'addresses', index)} className="w-full border rounded p-2 mt-1" />
        </div>
        <div>
          <label className="block text-sm text-gray-600">หมู่บ้าน/อาคาร</label>
          <input type="text" name="village" value={address.village || ''} onChange={(e) => handleArrayChange(e, 'addresses', index)} className="w-full border rounded p-2 mt-1" />
        </div>
        <div>
          <label className="block text-sm text-gray-600">ซอย</label>
          <input type="text" name="soi" value={address.soi || ''} onChange={(e) => handleArrayChange(e, 'addresses', index)} className="w-full border rounded p-2 mt-1" />
        </div>
      </div>
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label className="block text-sm text-gray-600">ถนน</label>
          <input type="text" name="road" value={address.road || ''} onChange={(e) => handleArrayChange(e, 'addresses', index)} className="w-full border rounded p-2 mt-1" />
        </div>
        <div>
          <label className="block text-sm text-gray-600 mb-1">จังหวัด</label>
          <Select 
            options={masterData.provinces.map(p => ({ value: p.id, label: p.provinceThai }))}
            value={masterData.provinces.filter(p => p.id === address.provinceId).map(p => ({ value: p.id, label: p.provinceThai }))[0] || null}
            onChange={(sel, action) => handleReactSelectChange(sel, action, 'addresses', index, 'provinceId')}
            placeholder="ค้นหาจังหวัด..."
            isClearable
          />
        </div>
        <div>
          <label className="block text-sm text-gray-600 mb-1">อำเภอ/เขต</label>
          <Select 
            options={districts.map(d => ({ value: d.id, label: d.districtThai }))}
            value={districts.filter(d => d.id === address.districtId).map(d => ({ value: d.id, label: d.districtThai }))[0] || null}
            onChange={(sel, action) => handleReactSelectChange(sel, action, 'addresses', index, 'districtId')}
            placeholder="ค้นหาอำเภอ..."
            isLoading={loadingD}
            isClearable
            isDisabled={!address.provinceId}
          />
        </div>
        <div>
          <label className="block text-sm text-gray-600 mb-1">ตำบล/แขวง</label>
          <Select 
            options={subDistricts.map(s => ({ value: s.id, label: s.subDistrictThai }))}
            value={subDistricts.filter(s => s.id === address.subDistrictId).map(s => ({ value: s.id, label: s.subDistrictThai }))[0] || null}
            onChange={(sel, action) => {
              handleReactSelectChange(sel, action, 'addresses', index, 'subDistrictId');
              if(sel) {
                const sd = subDistricts.find(x => x.id === sel.value);
                if(sd && sd.postCode) {
                  const e = { target: { name: 'postcode', value: sd.postCode } };
                  handleArrayChange(e, 'addresses', index);
                }
              }
            }}
            placeholder="ค้นหาตำบล..."
            isLoading={loadingS}
            isClearable
            isDisabled={!address.districtId}
          />
        </div>
      </div>
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
        <div>
          <label className="block text-sm text-gray-600">รหัสไปรษณีย์</label>
          <input type="text" name="postcode" value={address.postcode || ''} onChange={(e) => handleArrayChange(e, 'addresses', index)} className="w-full border rounded p-2 mt-1" />
        </div>
      </div>
    </div>
  );
};
// ----------------------------------------

export default function TraineeEditModal({ nationId, onClose, onSuccess }) {
  const [formData, setFormData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [activeTab, setActiveTab] = useState('personal');

  // Master Data State
  const [masterData, setMasterData] = useState({
    titles: [],
    religions: [],
    genders: [],
    bloods: [],
    provinces: [],
    courses: [],
    agentBranches: [],
    territories: [],
    expertises: [],
    companies: [],
    renewCourseCheckboxes: [],
    renewOtherOptions: []
  });

  useEffect(() => {
    const fetchData = async () => {
      try {
        const token = localStorage.getItem('admin_token');
        
        // Fetch Master Data
        const [titles, religions, genders, bloods, provinces, courses, agentBranches, territories, expertises, companies, renewCourseCheckboxes, renewOtherOptions, fullData] = await Promise.all([
          fetch('http://localhost:8085/api/masterdata/titles').then(r => r.json()),
          fetch('http://localhost:8085/api/masterdata/religion').then(r => r.json()),
          fetch('http://localhost:8085/api/masterdata/gender').then(r => r.json()),
          fetch('http://localhost:8085/api/masterdata/blood').then(r => r.json()),
          fetch('http://localhost:8085/api/masterdata/provinces').then(r => r.json()),
          fetch('http://localhost:8085/api/masterdata/renew-courses').then(r => r.json()),
          fetch('http://localhost:8085/api/masterdata/agent-branches').then(r => r.json()),
          fetch('http://localhost:8085/api/masterdata/territory').then(r => r.json()),
          fetch('http://localhost:8085/api/masterdata/expertise').then(r => r.json()),
          fetch('http://localhost:8085/api/masterdata/company').then(r => r.json()),
          fetch('http://localhost:8085/api/masterdata/renewcourse').then(r => r.json()),
          fetch('http://localhost:8085/api/masterdata/renew-other-courses').then(r => r.json()),
          fetch(`http://localhost:8085/api/admin/trainees/${nationId}/full`, { headers: { 'Authorization': `Bearer ${token}` } }).then(r => r.json())
        ]);

        // Initialize empty arrays if null
        fullData.addresses = fullData.addresses || [];
        
        // Ensure only 'A' and 'C' addresses are used
        fullData.addresses = fullData.addresses.filter(a => a.addressType === 'A' || a.addressType === 'C');
        const requiredTypes = ['A', 'C'];
        requiredTypes.forEach(type => {
          if (!fullData.addresses.find(a => a.addressType === type)) {
            fullData.addresses.push({ addressType: type, houseNo: '', moo: '', soi: '', road: '', provinceId: null, districtId: null, subDistrictId: null, postcode: '' });
          }
        });
        // Sort addresses so A is first, C is second
        fullData.addresses.sort((a, b) => requiredTypes.indexOf(a.addressType) - requiredTypes.indexOf(b.addressType));

        fullData.licenses = fullData.licenses || [];
        fullData.affiliations = fullData.affiliations || [];
        fullData.courses = fullData.courses || [];
        fullData.trainings = fullData.trainings || [];
        
        setMasterData({ 
          titles, religions, genders, bloods, provinces, courses, agentBranches, 
          territories, expertises, companies, renewCourseCheckboxes, renewOtherOptions 
        });
        setFormData(fullData);
      } catch (err) {
        console.error(err);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [nationId]);

  const handleTrainingCheckboxChange = (courseId, isChecked) => {
    const newTrainings = [...(formData.trainings || [])];
    if (isChecked) {
      if (!newTrainings.some(t => t.courseId?.toString() === courseId.toString())) {
        newTrainings.push({ courseId: parseInt(courseId) });
      }
    } else {
      const idx = newTrainings.findIndex(t => t.courseId?.toString() === courseId.toString());
      if (idx > -1) newTrainings.splice(idx, 1);
    }
    setFormData(prev => ({ ...prev, trainings: newTrainings }));
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleArrayChange = (e, arrayName, index) => {
    const { name, value } = e.target;
    setFormData(prev => {
      const newArray = [...prev[arrayName]];
      newArray[index] = { ...newArray[index], [name]: value };
      let updatedData = { ...prev, [arrayName]: newArray };
      
      if (arrayName === 'licenses' && name === 'courseType' && value !== 'broker') {
        if (updatedData.affiliations && updatedData.affiliations.length > 0) {
          const newAffiliations = [...updatedData.affiliations];
          newAffiliations[0] = { ...newAffiliations[0], brokerBranch: null };
          updatedData.affiliations = newAffiliations;
        }
      }
      return updatedData;
    });
  };

  const handleRegistrationChange = (e, field) => {
    const value = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
    const newRegs = [...(formData.registrations || [])];
    if (newRegs.length === 0) newRegs.push({});
    
    if (e.target.type === 'checkbox') {
      let currentVal = newRegs[0][field] ? newRegs[0][field].split(',') : [];
      if (value) {
        if (!currentVal.includes(e.target.value)) currentVal.push(e.target.value);
      } else {
        currentVal = currentVal.filter(v => v !== e.target.value);
      }
      newRegs[0][field] = currentVal.length > 0 ? currentVal.join(',') : null;
      
      if (!value && e.target.value === 'MasterDegree') {
        newRegs[0].MasterDegreeStatus = null;
      }
    } else {
      newRegs[0][field] = value;
    }
    
    setFormData({ ...formData, registrations: newRegs });
  };

  const handleReactSelectChange = (selectedOption, actionMeta, arrayName, index, fieldName) => {
    const value = selectedOption ? selectedOption.value : null;
    setFormData(prev => {
      const newArray = [...prev[arrayName]];
      newArray[index] = { ...newArray[index], [fieldName]: value };
      
      // Reset district and subdistrict if province changes
      if(fieldName === 'provinceId') {
        newArray[index].districtId = null;
        newArray[index].subDistrictId = null;
        newArray[index].postcode = '';
      }
      // Reset subdistrict if district changes
      if(fieldName === 'districtId') {
        newArray[index].subDistrictId = null;
        newArray[index].postcode = '';
      }

      return { ...prev, [arrayName]: newArray };
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    try {
      const token = localStorage.getItem('admin_token');
      // Convert ID fields to integers where necessary before sending
      const payload = {
        ...formData,
        religionId: formData.religionId ? parseInt(formData.religionId) : null,
        genderId: formData.genderId ? parseInt(formData.genderId) : null,
        bloodGroupId: formData.bloodGroupId ? parseInt(formData.bloodGroupId) : null
      };

      const res = await fetch(`http://localhost:8085/api/admin/trainees/${nationId}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });
      if (res.ok) {
        alert('บันทึกข้อมูลเรียบร้อย');
        onSuccess();
      } else {
        alert('ไม่สามารถบันทึกข้อมูลได้');
      }
    } catch (err) {
      console.error(err);
      alert('เกิดข้อผิดพลาดในการบันทึก');
    } finally {
      setSaving(false);
    }
  };

  if (loading) return (
    <div className="fixed inset-0 flex items-center justify-center bg-black/60 z-[60]">
      <div className="bg-white p-6 rounded shadow-xl">กำลังโหลดข้อมูล...</div>
    </div>
  );

  // Transform options for react-select
  const courseOptions = masterData.courses.map(c => ({ value: c.id, label: c.courseName || `Course ${c.id}` }));

  return (
    <div className="fixed inset-0 flex items-center justify-center bg-black/60 z-[60] p-4">
      <div className="bg-white rounded-lg shadow-xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden relative">
        <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 flex-shrink-0">
          <h3 className="text-lg font-bold text-gray-800">
            แก้ไขข้อมูล: {formData?.firstNameTh} {formData?.lastNameTh}
          </h3>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600 focus:outline-none">
            <i className="fas fa-times text-xl"></i>
          </button>
        </div>

        {/* Tabs Navigation */}
        <div className="flex border-b border-gray-200 bg-white overflow-x-auto flex-shrink-0">
          <button type="button" onClick={() => setActiveTab('personal')} className={`px-4 py-3 text-sm font-medium whitespace-nowrap flex-shrink-0 ${activeTab === 'personal' ? 'border-b-2 border-primary text-primary' : 'text-gray-500 hover:text-gray-700'}`}>
            1. ข้อมูลส่วนตัว
          </button>
          <button type="button" onClick={() => setActiveTab('address')} className={`px-4 py-3 text-sm font-medium whitespace-nowrap flex-shrink-0 ${activeTab === 'address' ? 'border-b-2 border-primary text-primary' : 'text-gray-500 hover:text-gray-700'}`}>
            2. ข้อมูลที่อยู่
          </button>
          <button type="button" onClick={() => setActiveTab('license')} className={`px-4 py-3 text-sm font-medium whitespace-nowrap flex-shrink-0 ${activeTab === 'license' ? 'border-b-2 border-primary text-primary' : 'text-gray-500 hover:text-gray-700'}`}>
            3. ใบอนุญาตและการทำงาน
          </button>
          <button type="button" onClick={() => setActiveTab('course')} className={`px-4 py-3 text-sm font-medium whitespace-nowrap flex-shrink-0 ${activeTab === 'course' ? 'border-b-2 border-primary text-primary' : 'text-gray-500 hover:text-gray-700'}`}>
            4. หลักสูตรที่อบรม
          </button>
          <button type="button" onClick={() => setActiveTab('other')} className={`px-4 py-3 text-sm font-medium whitespace-nowrap flex-shrink-0 ${activeTab === 'other' ? 'border-b-2 border-primary text-primary' : 'text-gray-500 hover:text-gray-700'}`}>
            5. ข้อมูลอื่นๆ
          </button>
        </div>

        <div className="p-6 overflow-y-auto flex-1 bg-gray-50">
          <form id="editPersonForm" onSubmit={handleSubmit}>
            
            {/* Tab 1: Personal Info */}
            <div className={activeTab === 'personal' ? 'block' : 'hidden'}>
              <div className="bg-white p-4 rounded shadow-sm mb-4">
                <h4 className="font-semibold text-gray-700 border-b pb-2 mb-4">ข้อมูลส่วนตัวเบื้องต้น</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                  <div>
                    <label className="block text-sm text-gray-600">คำนำหน้า (ไทย)</label>
                    <select name="titleTh" value={formData?.titleTh || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1 bg-white">
                      <option value="">-- เลือกคำนำหน้า --</option>
                      {masterData.titles.map(t => (
                        <option key={t.id} value={t.id.toString()}>{t.name}</option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm text-gray-600">ชื่อ (ไทย)</label>
                    <input type="text" name="firstNameTh" value={formData?.firstNameTh || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1" />
                  </div>
                  <div>
                    <label className="block text-sm text-gray-600">นามสกุล (ไทย)</label>
                    <input type="text" name="lastNameTh" value={formData?.lastNameTh || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1" />
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                  <div>
                    <label className="block text-sm text-gray-600">คำนำหน้าเดิม</label>
                    <select name="titleOldTh" value={formData?.titleOldTh || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1 bg-white">
                      <option value="">-- เลือกคำนำหน้า --</option>
                      {masterData.titles.map(t => (
                        <option key={t.id} value={t.id.toString()}>{t.name}</option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm text-gray-600">ชื่อเดิม</label>
                    <input type="text" name="firstNameOldTh" value={formData?.firstNameOldTh || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1" />
                  </div>
                  <div>
                    <label className="block text-sm text-gray-600">นามสกุลเดิม</label>
                    <input type="text" name="lastNameOldTh" value={formData?.lastNameOldTh || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1" />
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                  <div>
                    <label className="block text-sm text-gray-600">วันเกิด</label>
                    <input type="date" name="birthDate" value={formData?.birthDate ? formData.birthDate.split('T')[0] : ''} onChange={handleChange} className="w-full border rounded p-2 mt-1" />
                  </div>
                  <div>
                    <label className="block text-sm text-gray-600">วันบัตรหมดอายุ</label>
                    <input type="date" name="idCardExpiry" value={formData?.idCardExpiry ? formData.idCardExpiry.split('T')[0] : ''} onChange={handleChange} className="w-full border rounded p-2 mt-1" />
                  </div>
                  <div>
                    <label className="block text-sm text-gray-600">ศาสนา</label>
                    <select name="religionId" value={formData?.religionId || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1 bg-white">
                      <option value="">-- เลือกศาสนา --</option>
                      {masterData.religions.map(r => (
                        <option key={r.id} value={r.id}>{r.name}</option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm text-gray-600">เพศ</label>
                    <select name="genderId" value={formData?.genderId || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1 bg-white">
                      <option value="">-- เลือกเพศ --</option>
                      {masterData.genders.map(g => (
                        <option key={g.id} value={g.id}>{g.name}</option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm text-gray-600">กรุ๊ปเลือด</label>
                    <select name="bloodGroupId" value={formData?.bloodGroupId || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1 bg-white">
                      <option value="">-- เลือกกรุ๊ปเลือด --</option>
                      {masterData.bloods.map(b => (
                        <option key={b.id} value={b.id}>{b.name}</option>
                      ))}
                    </select>
                  </div>
                </div>
              </div>

              <div className="bg-white p-4 rounded shadow-sm mb-4">
                <h4 className="font-semibold text-gray-700 border-b pb-2 mb-4">ข้อมูลการติดต่อ</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                  <div>
                    <label className="block text-sm text-gray-600">เบอร์โทรศัพท์ (OTP)</label>
                    <input type="text" name="phoneOtp" value={formData?.phoneOtp || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1" />
                  </div>
                  <div>
                    <label className="block text-sm text-gray-600">อีเมล (สำรอง)</label>
                    <input type="email" name="emailAlt" value={formData?.emailAlt || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1" />
                  </div>
                  <div>
                    <label className="block text-sm text-gray-600">Line ID</label>
                    <input type="text" name="lineId" value={formData?.lineId || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1" />
                  </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm text-gray-600">Facebook</label>
                    <input type="text" name="facebook" value={formData?.facebook || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1" />
                  </div>
                  <div>
                    <label className="block text-sm text-gray-600">Instagram</label>
                    <input type="text" name="instagram" value={formData?.instagram || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1" />
                  </div>
                </div>
              </div>

              <div className="bg-white p-4 rounded shadow-sm mb-4">
                <h4 className="font-semibold text-gray-700 border-b pb-2 mb-4">ข้อมูลสุขภาพ / ผู้ติดต่อฉุกเฉิน</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                  <div>
                    <label className="block text-sm text-gray-600">แพ้อาหาร</label>
                    <input type="text" name="foodAllergy" value={formData?.foodAllergy || ''} onChange={handleChange} placeholder="เช่น แพ้อาหารทะเล" className="w-full border rounded p-2 mt-1" />
                  </div>
                  <div>
                    <label className="block text-sm text-gray-600">โรคประจำตัว</label>
                    <input type="text" name="medicalCondition" value={formData?.medicalCondition || ''} onChange={handleChange} placeholder="เช่น เบาหวาน" className="w-full border rounded p-2 mt-1" />
                  </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm text-gray-600">ผู้ติดต่อฉุกเฉิน</label>
                    <input type="text" name="emergencyContactName" value={formData?.emergencyContactName || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1" />
                  </div>
                  <div>
                    <label className="block text-sm text-gray-600">เบอร์ผู้ติดต่อฉุกเฉิน</label>
                    <input type="text" name="emergencyContactPhone" value={formData?.emergencyContactPhone || ''} onChange={handleChange} className="w-full border rounded p-2 mt-1" />
                  </div>
                </div>
              </div>
            </div>

            {/* Tab 2: Addresses */}
            <div className={activeTab === 'address' ? 'block' : 'hidden'}>
              {formData?.addresses.map((address, index) => (
                <AddressBlock 
                  key={`${address.addressType}-${index}`} 
                  address={address} 
                  index={index} 
                  masterData={masterData} 
                  handleArrayChange={handleArrayChange} 
                  handleReactSelectChange={handleReactSelectChange} 
                />
              ))}
            </div>

            {/* Tab 3: Licenses */}
            <div className={activeTab === 'license' ? 'block' : 'hidden'}>
              <div className="bg-white p-4 rounded shadow-sm mb-4">
                <h4 className="font-semibold text-gray-700 border-b pb-2 mb-4">ข้อมูลใบอนุญาต</h4>
                {formData?.licenses.map((license, index) => (
                  <div key={index} className="mb-4 pb-4 border-b">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                      <div>
                        <label className="block text-sm text-gray-600">ประเภทใบอนุญาต</label>
                        <select name="courseType" value={license.courseType || ''} onChange={(e) => handleArrayChange(e, 'licenses', index)} className="w-full border rounded p-2 mt-1">
                          <option value="">-- เลือกประเภทใบอนุญาต --</option>
                          <option value="agent">ตัวแทนประกันวินาศภัย</option>
                          <option value="broker">นายหน้าประกันวินาศภัย</option>
                        </select>
                      </div>
                      <div>
                        <label className="block text-sm text-gray-600">เลขที่ใบอนุญาต</label>
                        <input type="text" name="licenseNo" value={license.licenseNo || ''} onChange={(e) => handleArrayChange(e, 'licenses', index)} className="w-full border rounded p-2 mt-1" />
                      </div>
                      <div>
                        <label className="block text-sm text-gray-600">วันที่ออกใบอนุญาต</label>
                        <input type="date" name="licenseIssueDate" value={license.licenseIssueDate ? license.licenseIssueDate.split('T')[0] : ''} onChange={(e) => handleArrayChange(e, 'licenses', index)} className="w-full border rounded p-2 mt-1" />
                      </div>
                      <div>
                        <label className="block text-sm text-gray-600">วันหมดอายุใบอนุญาต</label>
                        <input type="date" name="licenseExpiryDate" value={license.licenseExpiryDate ? license.licenseExpiryDate.split('T')[0] : ''} onChange={(e) => handleArrayChange(e, 'licenses', index)} className="w-full border rounded p-2 mt-1" />
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              <div className="bg-white p-4 rounded shadow-sm mb-4">
                <h4 className="font-semibold text-gray-700 border-b pb-2 mb-4">ข้อมูลสังกัด/สาขา</h4>
                {formData?.affiliations.map((affil, index) => {
                  const isBroker = formData?.licenses?.[0]?.courseType === 'broker';
                  return (
                  <div key={index} className="mb-4 pb-4 border-b">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                      <div>
                        <label className="block text-sm text-gray-600 mb-1">สาขา</label>
                        <Select
                          options={masterData.agentBranches.map(b => ({ value: b.branchId, label: b.branchName, regionId: b.regionId, regionName: b.regionName }))}
                          value={masterData.agentBranches.filter(b => b.branchId === affil.branchId).map(b => ({ value: b.branchId, label: b.branchName }))[0] || null}
                          onChange={(sel) => {
                            if (sel) {
                              handleReactSelectChange(sel, null, 'affiliations', index, 'branchId');
                              const eRegion = { target: { name: 'regionId', value: sel.regionId } };
                              handleArrayChange(eRegion, 'affiliations', index);
                            } else {
                              handleReactSelectChange(null, null, 'affiliations', index, 'branchId');
                              const eRegion = { target: { name: 'regionId', value: null } };
                              handleArrayChange(eRegion, 'affiliations', index);
                            }
                          }}
                          placeholder="ค้นหาสาขา..."
                          isClearable
                        />
                      </div>
                      <div>
                        <label className="block text-sm text-gray-600">ภาค</label>
                        <input type="text" value={masterData.agentBranches.find(b => b.branchId === affil.branchId)?.regionName || ''} className="w-full border rounded p-2 mt-1 bg-gray-100 text-gray-500" readOnly />
                      </div>
                      <div>
                        <label className="block text-sm text-gray-600">ประเภทนายหน้า</label>
                        <select name="brokerType" value={affil.brokerType || ''} onChange={(e) => {
                          handleArrayChange(e, 'affiliations', index);
                          // Clear corporate fields if not corporate
                          if (e.target.value !== 'corporate') {
                            const eComp = { target: { name: 'brokerCompany', value: '' } };
                            handleArrayChange(eComp, 'affiliations', index);
                            const eBranch = { target: { name: 'brokerBranch', value: '' } };
                            handleArrayChange(eBranch, 'affiliations', index);
                          }
                        }} disabled={!isBroker} className="w-full border rounded p-2 mt-1 disabled:bg-gray-100 disabled:text-gray-400">
                          <option value="">-- เลือกประเภทนายหน้า --</option>
                          <option value="individual">นายหน้าบุคคลธรรมดา</option>
                          <option value="corporate">นายหน้านิติบุคคล</option>
                        </select>
                      </div>
                      <div>
                        <label className="block text-sm text-gray-600">รหัสวิริยะ</label>
                        <input type="text" name="viriyahAgentCode" value={affil.viriyahAgentCode || ''} onChange={(e) => handleArrayChange(e, 'affiliations', index)} className="w-full border rounded p-2 mt-1" />
                      </div>
                    </div>
                    {affil.brokerType === 'corporate' && (
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 mt-4 p-4 bg-gray-50 border rounded animate-[fadeIn_0.3s]">
                        <div>
                          <label className="block text-sm text-gray-600">ข้อมูลสังกัดบริษัทโบรกเกอร์</label>
                          <input type="text" name="brokerCompany" value={affil.brokerCompany || ''} onChange={(e) => handleArrayChange(e, 'affiliations', index)} placeholder="ระบุชื่อบริษัท" className="w-full border rounded p-2 mt-1" />
                        </div>
                        <div>
                          <label className="block text-sm text-gray-600">สาขาของบริษัทนายหน้าที่สังกัด</label>
                          <input type="text" name="brokerBranch" value={affil.brokerBranch || ''} onChange={(e) => handleArrayChange(e, 'affiliations', index)} placeholder="ระบุสาขา (ถ้ามี)" className="w-full border rounded p-2 mt-1" />
                        </div>
                      </div>
                    )}
                  </div>
                )})}
              </div>
            </div>

            {/* Tab 4: Courses */}
            <div className={activeTab === 'course' ? 'block' : 'hidden'}>
              <div className="bg-white p-4 rounded shadow-sm mb-4">
                <h4 className="font-semibold text-gray-700 border-b pb-2 mb-4">หลักสูตรที่ประสงค์เข้าอบรม</h4>
                
                <div className="mb-6">
                  <label className="block mb-2 font-medium text-gray-700">ระดับคอร์ส</label>
                  <div className="flex flex-col gap-2">
                    {masterData?.courses?.filter(c => !c.agentType || c.agentType === formData.licenses?.[0]?.courseType).map((course, idx) => {
                      const currentCourseType = formData.courses?.[0]?.courseId;
                      return (
                        <label key={idx} className="flex items-center gap-2 cursor-pointer p-2 border rounded hover:bg-gray-50">
                          <input
                            type="radio"
                            name="courseType"
                            className="w-4 h-4 accent-primary"
                            value={course.id}
                            checked={currentCourseType === course.id}
                            onChange={() => handleCourseTypeChange(course.id)}
                          />
                          {course.courseName}
                        </label>
                      );
                    })}
                  </div>
                </div>

                {(() => {
                  const currentCourseType = formData.courses?.[0]?.courseId;
                  const selectedCourseObj = masterData?.courses?.find(c => c.id === currentCourseType);
                  const isComplexCourse = selectedCourseObj && selectedCourseObj.dateId === null;

                  if (isComplexCourse) {
                    return (
                      <div className="mb-6 animate-[fadeIn_0.3s]">
                        <label className="block mb-2 font-medium text-gray-700 mt-2">
                          หากท่านถือใบอนุญาตเป็นตัวแทนหรือนายหน้าประกันวินาศภัยที่ต่ออายุครั้งที่ 4 เป็นต้นไป โปรดระบุวิชาที่ท่านเคยเข้าอบรมในรอบการสะสมชั่วโมงอบรมปัจจุบัน (5 ปี)<br/>
                          <span className="text-red-500 text-sm font-normal">* สำคัญ * : เพื่อท่านจะต้องไม่อบรมวิชาที่เคยเข้าอบรมซ้ำอีก ตามข้อกำหนดของ สำนักงาน คปภ.</span>
                        </label>
                        <div className="flex flex-col gap-2 p-4 border rounded bg-gray-50 mb-6">
                          {masterData?.renewCourseCheckboxes?.map((course, idx) => {
                            const isChecked = formData.trainings?.some(t => t.courseId?.toString() === course.id.toString());
                            return (
                              <label key={idx} className="flex items-start gap-3 cursor-pointer">
                                <input
                                  type="checkbox"
                                  className="mt-1 w-4 h-4 accent-primary"
                                  checked={isChecked}
                                  onChange={(e) => handleTrainingChange(course.id, e.target.checked)}
                                />
                                <span className="text-sm">{course.name}</span>
                              </label>
                            );
                          })}
                        </div>

                        <div className="bg-gray-50 p-4 border rounded">
                          <label className="block mb-2 font-medium text-primary text-lg">{selectedCourseObj.courseName}</label>
                          <span className="text-red-500 text-sm block mb-2">* เลือกได้มากกว่า 1 วิชา *<br/>* หากเลือกวิชาที่เคยอบรม จะไม่นับรวมรอบปัจจุบัน (5 ปี) *</span>
                          <div className="flex flex-col gap-2">
                            {masterData?.renewOtherOptions?.map((otherCourse, idx) => {
                              const isChecked = formData.courses?.some(c => c.renewOtherId === otherCourse.id);
                              return (
                                <label key={idx} className="flex items-start gap-2 p-2 border rounded bg-white hover:bg-gray-50 cursor-pointer">
                                  <input
                                    type="checkbox"
                                    className="mt-1 w-4 h-4 accent-primary"
                                    checked={isChecked}
                                    onChange={(e) => handleCourseSubjectChange(otherCourse.id, e.target.checked)}
                                  />
                                  <span className="text-sm">{otherCourse.displayName}</span>
                                </label>
                              );
                            })}
                          </div>
                        </div>

                        {/* Deduction Privilege Section (Only for Course 4+) */}
                        {[9, 10, '9', '10'].includes(currentCourseType) && (
                          <>
                            <hr className="border-t border-gray-200 my-8" />
                            <div className="mb-6 animate-[fadeIn_0.3s]">
                              <label className="block mb-3 font-medium text-gray-700">สิทธิ์ลดหย่อนชั่วโมงอบรม</label>
                              <div className="p-4 border border-primary rounded-md bg-white">
                                <label className="flex items-start gap-3 cursor-pointer">
                                  <input
                                    type="checkbox"
                                    className="mt-1 w-4 h-4 accent-primary"
                                    value="MasterDegree"
                                    checked={(formData.registrations?.[0]?.DeductionPrivilege || '').includes('MasterDegree')}
                                    onChange={(e) => handleRegistrationChange(e, 'DeductionPrivilege')}
                                  />
                                  <span className="text-sm">สำเร็จการศึกษาตั้งแต่ระดับปริญญาโทขึ้นไป จากสถาบันอุดมศึกษาหรือสถาบันการศึกษาในต่างประเทศที่สำนักงานคณะกรรมการข้าราชการพลเรือนรับรอง</span>
                                </label>
                              </div>

                              {(formData.registrations?.[0]?.DeductionPrivilege || '').includes('MasterDegree') && (
                                <div className="mt-6 animate-[fadeIn_0.3s]">
                                  <label className="block mb-1 font-medium text-gray-700 after:content-['_*'] after:text-red-500">กรุณาระบุสถานะการยื่นเอกสาร</label>
                                  <span className="text-red-500 text-sm block mb-4">* หากท่านเคยยื่นเอกสารและบันทึกในระบบของสำนักงาน คปภ. แล้วไม่ต้องยื่นซ้ำ</span>
                                  <div className="flex flex-col gap-3">
                                    <label className="flex items-center gap-3 cursor-pointer p-3 border rounded-md bg-white hover:bg-gray-50">
                                      <input 
                                        type="radio" 
                                        name="masterDegreeStatus"
                                        className="w-4 h-4 accent-primary" 
                                        value="เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว" 
                                        checked={formData.registrations?.[0]?.MasterDegreeStatus === "เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว"} 
                                        onChange={(e) => handleRegistrationChange(e, 'MasterDegreeStatus')} 
                                      />
                                      <span className="text-sm">เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว</span>
                                    </label>
                                    <label className="flex items-center gap-3 cursor-pointer p-3 border rounded-md bg-white hover:bg-gray-50">
                                      <input 
                                        type="radio" 
                                        name="masterDegreeStatus"
                                        className="w-4 h-4 accent-primary" 
                                        value="ไม่เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว" 
                                        checked={formData.registrations?.[0]?.MasterDegreeStatus === "ไม่เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว"} 
                                        onChange={(e) => handleRegistrationChange(e, 'MasterDegreeStatus')} 
                                      />
                                      <span className="text-sm">ไม่เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว</span>
                                    </label>
                                  </div>
                                </div>
                              )}
                            </div>
                          </>
                        )}

                      </div>
                    );
                  }

                  if (currentCourseType && !isComplexCourse && selectedCourseObj?.dateDisplay) {
                    return (
                      <div className="mb-6 bg-gray-50 p-4 border rounded">
                        <label className="block mb-2 font-medium text-primary">{selectedCourseObj.courseName}</label>
                        <label className="flex items-center gap-2 cursor-pointer p-2 border rounded bg-white">
                          <input 
                            type="radio" 
                            className="w-4 h-4 accent-primary" 
                            checked={formData.courses?.[0]?.courseDateId === selectedCourseObj.dateId} 
                            onChange={() => handleCourseDateChange(selectedCourseObj.dateId)} 
                          />
                          {selectedCourseObj.dateDisplay}
                        </label>
                      </div>
                    );
                  }
                  return null;
                })()}

              </div>
            </div>
            
            {/* Tab 5: Other / Documents */}
            <div className={activeTab === 'other' ? 'block' : 'hidden'}>
              <div className="bg-white p-4 rounded shadow-sm mb-4">
                <h4 className="font-semibold text-gray-700 border-b pb-2 mb-4">ข้อมูลอื่นๆ</h4>
                
                <div className="mb-4">
                  <label className="block text-sm text-gray-600 mb-1">ธุรกิจอื่นที่ท่านทำ (ระบุประเภทธุรกิจ)</label>
                  <input 
                    type="text" 
                    value={formData?.others?.[0]?.otherBusiness || ''} 
                    onChange={(e) => handleOtherChange(e, 'otherBusiness')} 
                    className="w-full border rounded p-2" 
                    placeholder="กรุณาระบุ" 
                  />
                </div>
                
                <div className="mb-4">
                  <label className="block text-sm text-gray-600 mb-1">ประสบการณ์ในธุรกิจประกันภัย (จำนวนปี)</label>
                  <input 
                    type="number" 
                    value={formData?.others?.[0]?.insuranceExperienceYears || ''} 
                    onChange={(e) => handleOtherChange(e, 'insuranceExperienceYears')} 
                    className="w-full border rounded p-2" 
                    placeholder="ต้องระบุเป็นตัวเลข" 
                  />
                </div>

                <div className="mb-4">
                  <label className="block text-sm text-gray-600 mb-2">เขตพื้นที่ขาย</label>
                  <div className="flex flex-wrap gap-4">
                    {masterData?.territories?.map((territory, idx) => {
                      const checked = formData?.others?.[0]?.salesAreas?.some(sa => sa.territoriesId?.toString() === territory.id.toString());
                      return (
                        <label key={idx} className="flex items-center gap-2 cursor-pointer">
                          <input 
                            type="checkbox" 
                            className="w-4 h-4 accent-primary" 
                            checked={checked || false} 
                            onChange={(e) => handleOtherCheckboxChange('salesAreas', 'territoriesId', territory.id, e.target.checked)} 
                          />
                          <span>{territory.name}</span>
                        </label>
                      );
                    })}
                  </div>
                </div>

                <div className="mb-4">
                  <label className="block text-sm text-gray-600 mb-2">ความเชี่ยวชาญประกันภัย</label>
                  <div className="flex flex-col gap-2">
                    {masterData?.expertises?.map((specialty, idx) => {
                      const checked = formData?.others?.[0]?.specialties?.some(sp => sp.expertiseId?.toString() === specialty.id.toString());
                      return (
                        <label key={idx} className="flex items-center gap-2 cursor-pointer">
                          <input 
                            type="checkbox" 
                            className="w-4 h-4 accent-primary" 
                            checked={checked || false} 
                            onChange={(e) => handleOtherCheckboxChange('specialties', 'expertiseId', specialty.id, e.target.checked)} 
                          />
                          <span>{specialty.name}</span>
                        </label>
                      );
                    })}
                  </div>
                </div>

                <div className="mb-4">
                  <label className="block text-sm text-gray-600 mb-2">บริษัทประกันภัยอื่นที่ท่านส่งงานในปัจจุบัน</label>
                  <div className="max-h-60 overflow-y-auto p-3 border rounded bg-gray-50 flex flex-col gap-2">
                    {masterData?.companies?.map((company, idx) => {
                      const checked = formData?.others?.[0]?.otherCompanies?.some(oc => oc.companyId?.toString() === company.id.toString());
                      return (
                        <label key={idx} className="flex items-center gap-2 cursor-pointer">
                          <input 
                            type="checkbox" 
                            className="w-4 h-4 accent-primary" 
                            checked={checked || false} 
                            onChange={(e) => handleOtherCheckboxChange('otherCompanies', 'companyId', company.id, e.target.checked)} 
                          />
                          <span>{company.name}</span>
                        </label>
                      );
                    })}
                  </div>
                </div>

              </div>
            </div>

          </form>
        </div>
        
        <div className="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
          <button type="button" onClick={onClose} className="px-4 py-2 border rounded text-gray-600 mr-2 hover:bg-gray-100">
            ยกเลิก
          </button>
          <button type="submit" form="editPersonForm" disabled={saving} className="px-4 py-2 bg-primary text-white rounded shadow hover:bg-primary-light disabled:opacity-50">
            {saving ? 'กำลังบันทึก...' : 'บันทึกข้อมูล (อัปเดต Current)'}
          </button>
        </div>
      </div>
    </div>
  );
}
