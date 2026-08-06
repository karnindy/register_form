import { useState, useEffect, Fragment } from 'react';
import TraineeEditModal from './TraineeEditModal';

export default function TraineesList() {
  const [trainees, setTrainees] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [page, setPage] = useState(1);
  const [pageSize, setPageSize] = useState(10);
  const [total, setTotal] = useState(0);

  const [selectedTrainee, setSelectedTrainee] = useState(null);
  const [documents, setDocuments] = useState([]);
  const [isUploading, setIsUploading] = useState(false);
  const [expandedRows, setExpandedRows] = useState({});
  const [editingTraineeId, setEditingTraineeId] = useState(null);

  const toggleRow = (id) => {
    setExpandedRows(prev => ({ ...prev, [id]: !prev[id] }));
  };

  const fetchTrainees = async () => {
    setLoading(true);
    try {
      const token = localStorage.getItem('admin_token');
      const qs = new URLSearchParams({ search: searchTerm, page, pageSize: pageSize === 'All' ? 0 : pageSize });
      const response = await fetch(`http://localhost:8085/api/admin/trainees?${qs.toString()}`, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      if (response.ok) {
        const res = await response.json();
        if (res.data) {
          setTrainees(res.data);
          setTotal(res.total);
        } else {
          setTrainees(res);
          setTotal(res.length);
        }
      }
    } catch (error) {
      console.error('Failed to fetch trainees', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchTrainees();
  }, [page, pageSize]);

  const handleSearch = (e) => {
    e.preventDefault();
    setPage(1);
    fetchTrainees();
  };

  const handleViewTrainee = async (trainee) => {
    setSelectedTrainee(trainee);
    setDocuments([]);
    try {
      const token = localStorage.getItem('admin_token');
      const res = await fetch(`http://localhost:8085/api/admin/trainees/${trainee.idCard}/documents`, {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      if (res.ok) {
        setDocuments(await res.json());
      }
    } catch (err) {
      console.error(err);
    }
  };

  const handleUploadDocument = async (e, docType) => {
    const file = e.target.files[0];
    if (!file) return;

    setIsUploading(true);
    try {
      const formData = new FormData();
      formData.append('nationalId', selectedTrainee.idCard);
      formData.append('phone', '0000000000');
      formData.append(docType, file);

      const token = localStorage.getItem('admin_token');
      const res = await fetch('http://localhost:8085/api/upload', {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` },
        body: formData
      });
      
      if (res.ok) {
        alert('อัปโหลดไฟล์สำเร็จ');
        handleViewTrainee(selectedTrainee);
      } else {
        alert('อัปโหลดไฟล์ไม่สำเร็จ');
      }
    } catch (err) {
      alert('เกิดข้อผิดพลาดในการอัปโหลด');
    } finally {
      setIsUploading(false);
      e.target.value = null;
    }
  };

  const docTypes = [
    { key: 'profile', label: 'ภาพถ่ายหน้าตรง (Profile)' },
    { key: 'idCardWithFace', label: 'ภาพถ่ายคู่บัตร ปชช. (IDCardFace)' },
    { key: 'idCard', label: 'ภาพถ่ายบัตร ปชช. (IDCard)' }
  ];

  if (loading) return <div className="text-center p-10">กำลังโหลดข้อมูล...</div>;

  return (
    <div>
      <div className="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h2 className="text-2xl font-bold text-primary">รายชื่อผู้ลงทะเบียนอบรม</h2>
        
        <div className="flex flex-wrap items-center gap-4">
          <form onSubmit={handleSearch} className="flex">
            <input 
              type="text" 
              placeholder="ค้นหา ชื่อ, บัตร ปชช, เบอร์..." 
              className="border border-gray-300 rounded-l px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary w-64"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
            <button type="submit" className="bg-primary text-white px-4 py-2 rounded-r hover:bg-primary-light">
              <i className="fas fa-search"></i>
            </button>
          </form>

          <button className="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">
            <i className="fas fa-file-excel mr-2"></i> Export
          </button>
        </div>
      </div>

      <div className="flex justify-between items-center mb-4">
        <div className="text-sm text-gray-600">
          แสดง 
          <select 
            className="mx-2 border rounded p-1" 
            value={pageSize} 
            onChange={(e) => {
              setPageSize(e.target.value === 'All' ? 'All' : Number(e.target.value));
              setPage(1);
            }}
          >
            <option value={10}>10</option>
            <option value={20}>20</option>
            <option value={50}>50</option>
            <option value={100}>100</option>
            <option value={1000}>1000</option>
            <option value="All">ทั้งหมด</option>
          </select>
          รายการต่อหน้า
        </div>
        <div className="text-sm text-gray-600">พบทั้งหมด {total} รายการ</div>
      </div>

      <div className="overflow-x-auto bg-white rounded-lg shadow">
        <table className="min-w-full table-auto">
          <thead className="bg-gray-50 border-b border-gray-200">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ลำดับ</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อ-นามสกุล</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เลขบัตร ปชช.</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">หลักสูตรล่าสุด</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่สมัครล่าสุด</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {trainees.length > 0 ? (
              trainees.map((t, index) => (
                <Fragment key={t.nationId}>
                  <tr className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <button onClick={() => toggleRow(t.nationId)} className="mr-2 text-gray-500 hover:text-gray-700">
                        <i className={`fas fa-chevron-${expandedRows[t.nationId] ? 'down' : 'right'}`}></i>
                      </button>
                      {((page - 1) * (pageSize === 'All' ? total : pageSize)) + index + 1}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{t.name}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{t.idCard}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{t.course}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{t.date}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm">
                      <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${t.status === 'ยืนยันแล้ว' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}`}>
                        {t.status}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <button onClick={() => handleViewTrainee(t)} className="text-primary hover:text-primary-light mr-3" title="ดูรูปภาพ">
                        <i className="fas fa-image"></i>
                      </button>
                      <button onClick={() => setEditingTraineeId(t.nationId)} className="text-yellow-600 hover:text-yellow-900 mr-3" title="แก้ไขข้อมูล/ประวัติ">
                        <i className="fas fa-edit"></i> แก้ไข
                      </button>
                      <button className="text-red-600 hover:text-red-900" title="ลบข้อมูล">
                        <i className="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  
                  {expandedRows[t.nationId] && (
                    <tr className="bg-blue-50/50">
                      <td colSpan="7" className="px-6 py-4">
                        <div className="text-sm text-gray-700 bg-white p-4 rounded border shadow-inner">
                          <h4 className="font-bold mb-3 text-primary">
                            <i className="fas fa-history mr-2"></i>ประวัติการทำรายการ (Transactions)
                          </h4>
                          
                          {t.transactions && t.transactions.length > 0 ? (
                            <div className="overflow-x-auto">
                              <table className="w-full text-sm text-left border-collapse">
                                <thead className="bg-gray-100 text-gray-600">
                                  <tr>
                                    <th className="px-4 py-2 border">ครั้งที่</th>
                                    <th className="px-4 py-2 border">ชื่อ-นามสกุล</th>
                                    <th className="px-4 py-2 border">เลขบัตร ปชช.</th>
                                    <th className="px-4 py-2 border">หลักสูตร</th>
                                    <th className="px-4 py-2 border">วันที่ทำรายการ</th>
                                    <th className="px-4 py-2 border">สถานะ</th>
                                    <th className="px-4 py-2 border text-red-600">สิ่งที่แก้ไขจากครั้งก่อน</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  {t.transactions.map((trans, i) => {
                                    // Parse NewData
                                    let currentData = {};
                                    let prevData = {};
                                    try { currentData = JSON.parse(trans.newData || '{}'); } catch(e){}
                                    if (i + 1 < t.transactions.length) {
                                      try { prevData = JSON.parse(t.transactions[i+1].newData || '{}'); } catch(e){}
                                    }
                                    
                                    // Compute diffs
                                    let diffs = [];
                                    if (i + 1 < t.transactions.length) {
                                      const keysToCheck = [
                                        'TitleTh', 'titleTh', 'FirstNameTh', 'firstNameTh', 'LastNameTh', 'lastNameTh', 
                                        'PhoneOtp', 'phone', 'EmailAlt', 'email', 'LineId', 'lineId', 
                                        'EmergencyContactName', 'EmergencyContactPhone',
                                        'HouseNo', 'Moo', 'Village', 'Soi', 'ProvinceId', 'DistrictId', 'SubDistrictId', 'Postcode', 'zipCode',
                                        'BrokerBranch', 'AgentBranch', 'courseType', 'trainingDate',
                                        'PreviousCourses', 'previousCourses', 'ExtraTrainingInterest', 'extraTrainingInterest',
                                        'AdditionalCourseRequirement', 'additionalCourseRequirement', 'MainBusiness', 'mainBusiness',
                                        'HasExperience', 'hasExperience', 'InsuranceExperienceYears', 'insuranceExperienceYears',
                                        'SalesArea', 'salesArea', 'InsuranceSpecialty', 'insuranceSpecialty', 'OtherInsuranceCompanies', 'otherInsuranceCompanies'
                                      ];
                                      const keyMap = {
                                        TitleTh: 'คำนำหน้า', titleTh: 'คำนำหน้า', 
                                        FirstNameTh: 'ชื่อ', firstNameTh: 'ชื่อ', 
                                        LastNameTh: 'นามสกุล', lastNameTh: 'นามสกุล', 
                                        PhoneOtp: 'เบอร์โทร', phone: 'เบอร์โทร', 
                                        EmailAlt: 'อีเมล', email: 'อีเมล', 
                                        LineId: 'Line ID', lineId: 'Line ID', 
                                        EmergencyContactName: 'ชื่อ-สกุล ผู้ติดต่อฉุกเฉิน',
                                        EmergencyContactPhone: 'เบอร์โทรฉุกเฉิน',
                                        HouseNo: 'บ้านเลขที่', Moo: 'หมู่', Village: 'หมู่บ้าน/อาคาร', Soi: 'ซอย',
                                        ProvinceId: 'จังหวัด', DistrictId: 'อำเภอ/เขต', SubDistrictId: 'ตำบล/แขวง', 
                                        Postcode: 'รหัสไปรษณีย์', zipCode: 'รหัสไปรษณีย์',
                                        BrokerBranch: 'สาขา', AgentBranch: 'สาขา',
                                        courseType: 'หลักสูตร', trainingDate: 'วันที่อบรม',
                                        PreviousCourses: 'วิชาที่เคยอบรม', previousCourses: 'วิชาที่เคยอบรม',
                                        ExtraTrainingInterest: 'วิชาที่ประสงค์จะอบรม', extraTrainingInterest: 'วิชาที่ประสงค์จะอบรม',
                                        AdditionalCourseRequirement: 'วิชาที่ประสงค์จะอบรม', additionalCourseRequirement: 'วิชาที่ประสงค์จะอบรม',
                                        MainBusiness: 'ธุรกิจอื่น', mainBusiness: 'ธุรกิจอื่น',
                                        HasExperience: 'ประสบการณ์', hasExperience: 'ประสบการณ์',
                                        InsuranceExperienceYears: 'ปีประสบการณ์', insuranceExperienceYears: 'ปีประสบการณ์',
                                        SalesArea: 'พื้นที่ขาย', salesArea: 'พื้นที่ขาย',
                                        InsuranceSpecialty: 'ความเชี่ยวชาญ', insuranceSpecialty: 'ความเชี่ยวชาญ',
                                        OtherInsuranceCompanies: 'บริษัทประกันอื่น', otherInsuranceCompanies: 'บริษัทประกันอื่น'
                                      };
                                      
                                      let checkedMappedKeys = new Set();
                                      keysToCheck.forEach(k => {
                                        const mappedKey = keyMap[k] || k;
                                        if (checkedMappedKeys.has(mappedKey)) return; // Don't duplicate if both TitleTh and titleTh changed
                                        
                                        if (currentData[k] !== undefined && prevData[k] !== undefined && currentData[k] !== prevData[k]) {
                                          diffs.push(mappedKey);
                                          checkedMappedKeys.add(mappedKey);
                                        }
                                      });
                                    } else {
                                      diffs.push('ลงทะเบียนครั้งแรก');
                                    }

                                    return (
                                      <tr key={trans.id} className="border-b hover:bg-gray-50">
                                        <td className="px-4 py-2 border text-center">{t.transactions.length - i}</td>
                                        <td className="px-4 py-2 border">
                                          {[1, "1"].includes(currentData.titleTh || currentData.TitleTh) ? 'นาย' : 
                                           [2, "2"].includes(currentData.titleTh || currentData.TitleTh) ? 'นาง' : 
                                           [3, "3"].includes(currentData.titleTh || currentData.TitleTh) ? 'นางสาว' : 
                                           (currentData.titleTh || currentData.TitleTh || '')}{(currentData.firstNameTh || currentData.FirstNameTh || '')} {(currentData.lastNameTh || currentData.LastNameTh || '')}
                                        </td>
                                        <td className="px-4 py-2 border">{currentData.idCard || currentData.NationalId || t.idCard || t.nationId}</td>
                                        <td className="px-4 py-2 border">{currentData.courseType || currentData.CourseType || 'ไม่ระบุ'}</td>
                                        <td className="px-4 py-2 border">{trans.createdAt}</td>
                                        <td className="px-4 py-2 border">บันทึกระบบ</td>
                                        <td className="px-4 py-2 border text-red-500 text-xs">
                                          {diffs.length > 0 ? diffs.join(', ') : '-'}
                                        </td>
                                      </tr>
                                    );
                                  })}
                                </tbody>
                              </table>
                            </div>
                          ) : (
                            <p className="text-gray-500 italic">ไม่มีประวัติการทำรายการ</p>
                          )}
                        </div>
                      </td>
                    </tr>
                  )}
                </Fragment>
              ))
            ) : (
              <tr>
                <td colSpan="7" className="px-6 py-8 text-center text-gray-500">
                  ไม่พบข้อมูลผู้สมัครที่ตรงกับคำค้นหา
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {pageSize !== 'All' && total > 0 && (
        <div className="flex justify-center mt-6">
          <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <button
              onClick={() => setPage(p => Math.max(1, p - 1))}
              disabled={page === 1}
              className="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span className="sr-only">Previous</span>
              <i className="fas fa-chevron-left"></i>
            </button>
            <span className="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
              หน้า {page} จาก {Math.ceil(total / pageSize) || 1}
            </span>
            <button
              onClick={() => setPage(p => Math.min(Math.ceil(total / pageSize), p + 1))}
              disabled={page >= Math.ceil(total / pageSize)}
              className="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span className="sr-only">Next</span>
              <i className="fas fa-chevron-right"></i>
            </button>
          </nav>
        </div>
      )}

      {selectedTrainee && (
        <div className="fixed inset-0 flex items-center justify-center bg-black/60 z-50 p-4">
          <div className="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden relative">
            <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
              <h3 className="text-lg font-bold text-gray-800">
                ข้อมูลรูปภาพ: {selectedTrainee.name} (รหัส ปชช. {selectedTrainee.idCard})
              </h3>
              <button 
                onClick={() => setSelectedTrainee(null)}
                className="text-gray-400 hover:text-gray-600 focus:outline-none"
              >
                <i className="fas fa-times text-xl"></i>
              </button>
            </div>

            <div className="p-6 overflow-y-auto flex-1">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                {docTypes.map(type => {
                  const doc = documents.find(d => d.documentType === type.key);
                  return (
                    <div key={type.key} className="border border-gray-200 rounded-lg p-4 flex flex-col items-center bg-gray-50">
                      <h4 className="font-semibold text-sm mb-3 text-center">{type.label}</h4>
                      <div className="w-full aspect-[3/4] bg-gray-200 rounded flex items-center justify-center mb-4 overflow-hidden border border-gray-300">
                        {doc ? (
                          <img src={`http://localhost:8085${doc.filePath}`} alt={type.label} className="w-full h-full object-cover" />
                        ) : (
                          <span className="text-gray-400 text-sm flex flex-col items-center">
                            <i className="fas fa-image text-3xl mb-2"></i>ไม่มีรูปภาพ
                          </span>
                        )}
                      </div>
                      <div className="w-full mt-auto relative">
                        <label className={`w-full block text-center text-sm font-medium py-2 px-4 rounded cursor-pointer transition-colors ${isUploading ? 'bg-gray-300 text-gray-500' : 'bg-primary text-white hover:bg-primary-light'}`}>
                          {isUploading ? 'กำลังอัปโหลด...' : 'อัปโหลดรูปใหม่'}
                          <input 
                            type="file" 
                            accept="image/jpeg,image/png,image/gif" 
                            className="hidden" 
                            disabled={isUploading}
                            onChange={(e) => handleUploadDocument(e, type.key)} 
                          />
                        </label>
                        {doc && <p className="text-xs text-gray-500 text-center mt-2">อัปเดตล่าสุด: {new Date(doc.uploadedAt).toLocaleString('th-TH')}</p>}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          </div>
        </div>
      )}

      {editingTraineeId && (
        <TraineeEditModal 
          nationId={editingTraineeId} 
          onClose={() => setEditingTraineeId(null)} 
          onSuccess={() => {
            setEditingTraineeId(null);
            fetchTrainees();
          }} 
        />
      )}
    </div>
  );
}
