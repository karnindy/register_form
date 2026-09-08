import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';

const API_BASE = 'http://localhost:8085/api';

export default function RenewBasicCrud() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const [renewDates, setRenewDates] = useState([]);

  const [showModal, setShowModal] = useState(false);
  const [editItem, setEditItem] = useState(null);
  const [formData, setFormData] = useState({});

  // Course Details (OIC Codes) Modal for basic courses
  const [showDetailsModal, setShowDetailsModal] = useState(false);
  const [selectedCourseForDetails, setSelectedCourseForDetails] = useState(null);
  const [courseDetailsList, setCourseDetailsList] = useState([]);
  const [detailsLoading, setDetailsLoading] = useState(false);
  const [editingDetailId, setEditingDetailId] = useState(null);
  const [detailFormData, setDetailFormData] = useState({
    agentType: 'agent',
    announcementCode: '',
    courseShortName: '',
    curriculumCode: '',
    courseCode: '',
    oicCourseCode: '',
    displayOrder: 1,
    status: 'active'
  });

  useEffect(() => {
    fetchData();
    fetchDates();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const res = await fetch(`${API_BASE}/RenewBasic`);
      if (!res.ok) throw new Error('Failed to fetch data');
      const json = await res.json();
      setData(json);
      setError(null);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const fetchDates = async () => {
    try {
      const res = await fetch(`${API_BASE}/RenewData/dates`);
      if (res.ok) {
        setRenewDates(await res.json());
      }
    } catch (err) {
      console.error(err);
    }
  };

  const openAddModal = () => {
    setEditItem(null);
    setFormData({ 
      courseName: '', 
      agentType: 'agent', 
      dateId: '', 
      status: 'active' 
    });
    setShowModal(true);
  };

  const openEditModal = (item) => {
    setEditItem(item);
    setFormData({ 
      courseName: item.courseName || '',
      agentType: item.agentType || 'agent',
      dateId: item.dateId || '',
      status: item.status || 'active'
    });
    setShowModal(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      const url = editItem 
        ? `${API_BASE}/RenewBasic/${editItem.id}`
        : `${API_BASE}/RenewBasic`;
        
      const payload = {
        courseName: formData.courseName,
        agentType: formData.agentType,
        dateId: formData.dateId ? parseInt(formData.dateId) : null,
        status: formData.status
      };
        
      const res = await fetch(url, {
        method: editItem ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      
      if (!res.ok) throw new Error('Failed to save');
      
      setShowModal(false);
      fetchData();
    } catch (err) {
      alert('Error: ' + err.message);
    }
  };

  const handleDelete = async (id) => {
    if (!confirm('คุณต้องการลบข้อมูลนี้ใช่หรือไม่?')) return;
    try {
      const res = await fetch(`${API_BASE}/RenewBasic/${id}`, { method: 'DELETE' });
      if (!res.ok) throw new Error('Failed to delete');
      fetchData();
    } catch (err) {
      alert('Error: ' + err.message);
    }
  };

  // --- Course Details (OIC Codes) Handlers ---
  const openDetailsModal = async (course) => {
    setSelectedCourseForDetails(course);
    setEditingDetailId(null);
    resetDetailForm();
    setShowDetailsModal(true);
    await fetchCourseDetails(course.id);
  };

  const fetchCourseDetails = async (courseId) => {
    try {
      setDetailsLoading(true);
      const res = await fetch(`${API_BASE}/CourseDetail?courseType=basic&courseId=${courseId}&all=true`);
      if (res.ok) {
        const json = await res.json();
        setCourseDetailsList(json);
      }
    } catch (err) {
      console.error('Failed to load course details', err);
    } finally {
      setDetailsLoading(false);
    }
  };

  const resetDetailForm = () => {
    setEditingDetailId(null);
    setDetailFormData({
      agentType: selectedCourseForDetails?.agentType || 'agent',
      announcementCode: '',
      courseShortName: '',
      curriculumCode: '',
      courseCode: '',
      oicCourseCode: '',
      displayOrder: courseDetailsList.length + 1,
      status: 'active'
    });
  };

  const handleEditDetail = (detail) => {
    setEditingDetailId(detail.id);
    setDetailFormData({
      agentType: detail.agentType || 'agent',
      announcementCode: detail.announcementCode || '',
      courseShortName: detail.courseShortName || '',
      curriculumCode: detail.curriculumCode || '',
      courseCode: detail.courseCode || '',
      oicCourseCode: detail.oicCourseCode || '',
      displayOrder: detail.displayOrder || 1,
      status: detail.status || 'active'
    });
  };

  const handleSaveDetail = async (e) => {
    e.preventDefault();
    if (!selectedCourseForDetails) return;

    try {
      const method = editingDetailId ? 'PUT' : 'POST';
      const url = editingDetailId 
        ? `${API_BASE}/CourseDetail/${editingDetailId}` 
        : `${API_BASE}/CourseDetail`;

      const payload = {
        id: editingDetailId || 0,
        courseType: 'basic',
        courseId: selectedCourseForDetails.id,
        ...detailFormData,
        displayOrder: parseInt(detailFormData.displayOrder) || 1
      };

      const res = await fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      if (!res.ok) throw new Error('Save detail failed');

      resetDetailForm();
      await fetchCourseDetails(selectedCourseForDetails.id);
      await fetchData();
    } catch (err) {
      alert('เกิดข้อผิดพลาดในการบันทึกรหัส OIC: ' + err.message);
    }
  };

  const handleDeleteDetail = async (detailId) => {
    if (!window.confirm('คุณต้องการลบรหัส OIC รายการนี้ใช่หรือไม่?')) return;
    try {
      const res = await fetch(`${API_BASE}/CourseDetail/${detailId}`, {
        method: 'DELETE'
      });
      if (!res.ok) throw new Error('Delete detail failed');
      await fetchCourseDetails(selectedCourseForDetails.id);
      await fetchData();
    } catch (err) {
      alert('เกิดข้อผิดพลาดในการลบ: ' + err.message);
    }
  };

  return (
    <div className="max-w-7xl mx-auto">
      <div className="mb-6 border-b-2 border-warning pb-4 flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold text-warning mb-2">จัดการหลักสูตรพื้นฐาน (Renew Basic)</h2>
          <p className="text-gray-500 text-sm">เพิ่ม ลบ หรือแก้ไขข้อมูลหลักสูตร และจัดการรหัส OIC (1 หลักสูตร ผูกได้หลายรหัส OIC)</p>
        </div>
        <Link to="/admin/master-data" className="px-4 py-2 border border-gray-400 text-gray-600 rounded hover:bg-gray-100 transition-colors">
          <i className="fas fa-arrow-left mr-2"></i> กลับหน้ารวม Master Data
        </Link>
      </div>

      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex justify-end mb-4">
          <button onClick={openAddModal} className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
            <i className="fas fa-plus mr-2"></i> เพิ่มข้อมูล
          </button>
        </div>

        {loading ? (
          <div className="text-center py-10"><i className="fas fa-spinner fa-spin text-3xl text-warning"></i></div>
        ) : error ? (
          <div className="text-red-600 text-center py-5">{error}</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse text-sm">
              <thead>
                <tr className="bg-gray-100 text-gray-700">
                  <th className="p-3 border-b w-14">ID</th>
                  <th className="p-3 border-b min-w-[200px]">หลักสูตร</th>
                  <th className="p-3 border-b min-w-[260px]">รหัสประกาศ / รหัสหลักสูตร / รหัสวิชา OIC</th>
                  <th className="p-3 border-b w-24">ประเภท</th>
                  <th className="p-3 border-b">วันที่อบรม</th>
                  <th className="p-3 border-b w-20 text-center">สถานะ</th>
                  <th className="p-3 border-b text-center w-36">จัดการ</th>
                </tr>
              </thead>
              <tbody>
                {data.map(item => (
                  <tr key={item.id} className="border-b hover:bg-gray-50">
                    <td className="p-3 text-gray-500 font-mono">#{item.id}</td>
                    <td className="p-3 font-medium text-gray-800">{item.courseName}</td>
                    <td className="p-3">
                      <div className="flex flex-col gap-1.5">
                        {item.details && item.details.length > 0 ? (
                          <div className="flex flex-wrap gap-1.5 items-center">
                            {item.details.map(d => (
                              <div key={d.id} className="inline-flex items-center gap-1 px-2 py-0.5 bg-yellow-50 text-yellow-900 border border-yellow-200 rounded text-xs font-mono">
                                {d.agentType === 'broker' ? (
                                  <span className="px-1 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800 rounded">นายหน้า</span>
                                ) : d.agentType === 'both' ? (
                                  <span className="px-1 py-0.5 text-[10px] font-bold bg-indigo-100 text-indigo-800 rounded">Agent+Broker</span>
                                ) : (
                                  <span className="px-1 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-800 rounded">ตัวแทน</span>
                                )}
                                {d.oicCourseCode && (
                                  <span className="font-bold text-rose-700" title="รหัสวิชา OIC">
                                    <i className="fas fa-certificate text-[10px] mr-1"></i>{d.oicCourseCode}
                                  </span>
                                )}
                                {d.courseCode && (
                                  <span className="text-blue-700" title="รหัสวิชา">
                                    ({d.courseCode})
                                  </span>
                                )}
                                {d.curriculumCode && (
                                  <span className="text-teal-700 bg-teal-50 px-1 rounded text-[11px]" title="รหัสหลักสูตร">
                                    {d.curriculumCode}
                                  </span>
                                )}
                                {d.announcementCode && (
                                  <span className="text-amber-700 text-[10px]" title="รหัสประกาศ">
                                    [{d.announcementCode}]
                                  </span>
                                )}
                              </div>
                            ))}
                          </div>
                        ) : (
                          <span className="text-gray-400 italic text-xs">ยังไม่มีรหัส OIC ผูกไว้</span>
                        )}
                        <div>
                          <button 
                            onClick={() => openDetailsModal(item)}
                            className="inline-flex items-center text-xs text-yellow-700 hover:text-yellow-900 font-medium hover:underline mt-0.5"
                          >
                            <i className="fas fa-tags mr-1"></i>
                            จัดการรหัส OIC ({item.details?.length || 0} รายการ)
                          </button>
                        </div>
                      </div>
                    </td>
                    <td className="p-3 text-gray-600 uppercase text-xs font-bold">
                      {item.agentType === 'agent' ? <span className="text-blue-600">Agent</span> : <span className="text-purple-600">Broker</span>}
                    </td>
                    <td className="p-3 text-gray-600">
                      {renewDates.find(d => d.id === item.dateId)?.courseDateDisplay || (item.dateId ? `ID: ${item.dateId}` : '-')}
                    </td>
                    <td className="p-3 text-center">
                      <span className={`px-2 py-1 rounded text-xs font-semibold ${item.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                        {item.status ? item.status.toUpperCase() : 'UNKNOWN'}
                      </span>
                    </td>
                    <td className="p-3 text-center">
                      <button 
                        onClick={() => openDetailsModal(item)} 
                        className="px-2 py-1 text-xs bg-yellow-50 text-yellow-800 border border-yellow-300 rounded hover:bg-yellow-100 mx-1"
                        title="จัดการรหัส OIC"
                      >
                        <i className="fas fa-tags"></i>
                      </button>
                      <button onClick={() => openEditModal(item)} className="text-blue-600 hover:text-blue-800 mx-1.5" title="แก้ไข">
                        <i className="fas fa-edit"></i>
                      </button>
                      <button onClick={() => handleDelete(item.id)} className="text-red-600 hover:text-red-800 mx-1.5" title="ลบ">
                        <i className="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                ))}
                {data.length === 0 && (
                  <tr>
                    <td colSpan="7" className="text-center p-8 text-gray-500">ไม่มีข้อมูล</td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Main Add/Edit Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h3 className="text-xl font-bold mb-4 text-warning border-b pb-2">{editItem ? 'แก้ไขข้อมูล' : 'เพิ่มข้อมูล'}</h3>
            <form onSubmit={handleSave}>
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-1">ชื่อหลักสูตร <span className="text-red-500">*</span></label>
                <input type="text" required value={formData.courseName || ''} onChange={e => setFormData({...formData, courseName: e.target.value})} className="w-full border rounded p-2 focus:ring-2 focus:ring-warning outline-none" placeholder="เช่น ขอรับใบอนุญาตเป็นตัวแทนประกันวินาศภัย" />
              </div>

              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-1">ประเภท</label>
                <select required value={formData.agentType || 'agent'} onChange={e => setFormData({...formData, agentType: e.target.value})} className="w-full border rounded p-2 focus:ring-2 focus:ring-warning outline-none text-sm">
                  <option value="agent">Agent (ตัวแทน)</option>
                  <option value="broker">Broker (นายหน้า)</option>
                </select>
              </div>

              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-1">วันที่รอบอบรม</label>
                <select value={formData.dateId || ''} onChange={e => setFormData({...formData, dateId: e.target.value})} className="w-full border rounded p-2 focus:ring-2 focus:ring-warning outline-none text-sm">
                  <option value="">-- ไม่ระบุ --</option>
                  {renewDates.map(d => (
                    <option key={d.id} value={d.id}>{d.courseDateDisplay}</option>
                  ))}
                </select>
              </div>

              <div className="mb-6">
                <label className="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                <select value={formData.status || 'active'} onChange={e => setFormData({...formData, status: e.target.value})} className="w-full border rounded p-2 focus:ring-2 focus:ring-warning outline-none text-sm">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>

              <div className="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 border rounded hover:bg-gray-100 text-gray-600">ยกเลิก</button>
                <button type="submit" className="px-5 py-2 bg-yellow-600 text-white font-medium rounded hover:bg-yellow-700">บันทึก</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Course Details (OIC Codes) Modal */}
      {showDetailsModal && selectedCourseForDetails && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 overflow-y-auto">
          <div className="bg-white rounded-lg shadow-2xl w-full max-w-3xl p-6 animate-in fade-in zoom-in-95 duration-150 my-8">
            <div className="flex justify-between items-start border-b pb-3 mb-4">
              <div>
                <h3 className="text-xl font-bold text-gray-800">
                  <i className="fas fa-tags text-yellow-600 mr-2"></i>
                  จัดการรหัส OIC / รายละเอียดหลักสูตร
                </h3>
                <p className="text-sm text-gray-600 mt-1">
                  หลักสูตร: <span className="font-semibold text-warning">{selectedCourseForDetails.courseName}</span> (ID: #{selectedCourseForDetails.id})
                </p>
                <p className="text-xs text-gray-500">
                  * 1 หลักสูตรสามารถผูกได้หลายรหัสประกาศ, รหัสหลักสูตร, และรหัสวิชา OIC (เก็บในตาราง <code className="bg-gray-100 px-1 py-0.5 rounded font-mono">mst_course_detail</code>)
                </p>
              </div>
              <button 
                onClick={() => { setShowDetailsModal(false); resetDetailForm(); }}
                className="text-gray-400 hover:text-gray-600 text-xl"
              >
                <i className="fas fa-times"></i>
              </button>
            </div>

            {/* Existing Details List */}
            <div className="mb-6">
              <h4 className="text-sm font-bold text-gray-700 mb-2">รายการรหัส OIC ที่ผูกไว้ ({courseDetailsList.length} รายการ):</h4>
              {detailsLoading ? (
                <div className="text-center py-6"><i className="fas fa-spinner fa-spin text-yellow-600 text-xl"></i></div>
              ) : (
                <div className="overflow-x-auto border rounded-lg">
                  <table className="w-full text-left text-xs">
                    <thead>
                      <tr className="bg-gray-100 text-gray-700">
                        <th className="p-2.5 border-b">ประเภท</th>
                        <th className="p-2.5 border-b">รหัสประกาศ</th>
                        <th className="p-2.5 border-b">ชื่อย่อหลักสูตร</th>
                        <th className="p-2.5 border-b">รหัสหลักสูตร</th>
                        <th className="p-2.5 border-b">รหัสวิชา</th>
                        <th className="p-2.5 border-b">รหัสวิชา OIC</th>
                        <th className="p-2.5 border-b text-center w-16">สถานะ</th>
                        <th className="p-2.5 border-b text-center w-20">จัดการ</th>
                      </tr>
                    </thead>
                    <tbody>
                      {courseDetailsList.map(detail => (
                        <tr key={detail.id} className={`border-b hover:bg-gray-50 ${editingDetailId === detail.id ? 'bg-yellow-50/60' : ''}`}>
                          <td className="p-2.5">
                            {detail.agentType === 'broker' ? (
                              <span className="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">นายหน้า (Broker)</span>
                            ) : detail.agentType === 'both' ? (
                              <span className="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">ตัวแทน + นายหน้า</span>
                            ) : (
                              <span className="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">ตัวแทน (Agent)</span>
                            )}
                          </td>
                          <td className="p-2.5 font-mono">{detail.announcementCode || <span className="text-gray-400">-</span>}</td>
                          <td className="p-2.5">{detail.courseShortName || <span className="text-gray-400">-</span>}</td>
                          <td className="p-2.5 font-mono">{detail.curriculumCode || <span className="text-gray-400">-</span>}</td>
                          <td className="p-2.5 font-mono">{detail.courseCode || <span className="text-gray-400">-</span>}</td>
                          <td className="p-2.5 font-mono font-bold text-rose-700">{detail.oicCourseCode || <span className="text-gray-400 font-normal">-</span>}</td>
                          <td className="p-2.5 text-center">
                            <span className={`px-1.5 py-0.5 rounded text-[10px] font-semibold ${detail.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                              {detail.status?.toUpperCase()}
                            </span>
                          </td>
                          <td className="p-2.5 text-center">
                            <button 
                              onClick={() => handleEditDetail(detail)}
                              className="text-blue-600 hover:text-blue-800 mx-1" 
                              title="แก้ไข"
                            >
                              <i className="fas fa-edit"></i>
                            </button>
                            <button 
                              onClick={() => handleDeleteDetail(detail.id)}
                              className="text-red-600 hover:text-red-800 mx-1" 
                              title="ลบ"
                            >
                              <i className="fas fa-trash"></i>
                            </button>
                          </td>
                        </tr>
                      ))}
                      {courseDetailsList.length === 0 && (
                        <tr>
                          <td colSpan="8" className="text-center p-6 text-gray-500">
                            ยังไม่มีรายการรหัส OIC กรุณาเพิ่มข้อมูลด้านล่าง
                          </td>
                        </tr>
                      )}
                    </tbody>
                  </table>
                </div>
              )}
            </div>

            {/* Add / Edit Detail Form */}
            <div className="bg-gray-50 border rounded-lg p-4">
              <h4 className="text-sm font-bold text-gray-800 mb-3 flex items-center justify-between">
                <span>
                  <i className={`fas ${editingDetailId ? 'fa-edit text-blue-600' : 'fa-plus-circle text-green-600'} mr-1.5`}></i>
                  {editingDetailId ? 'แก้ไขรหัส OIC' : 'เพิ่มรหัส OIC / รายละเอียดใหม่'}
                </span>
                {editingDetailId && (
                  <button 
                    type="button" 
                    onClick={resetDetailForm}
                    className="text-xs text-gray-500 hover:text-gray-700 underline font-normal"
                  >
                    ยกเลิกการแก้ไข (เพิ่มใหม่)
                  </button>
                )}
              </h4>
              <form onSubmit={handleSaveDetail}>
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">ประเภท (Agent / Broker)</label>
                    <select 
                      value={detailFormData.agentType || 'agent'} 
                      onChange={e => setDetailFormData({...detailFormData, agentType: e.target.value})}
                      className="w-full border rounded p-2 text-xs font-semibold focus:ring-1 focus:ring-warning outline-none bg-white text-gray-800"
                    >
                      <option value="agent">ตัวแทน (Agent)</option>
                      <option value="broker">นายหน้า (Broker)</option>
                      <option value="both">ทั้งสองประเภท (Both)</option>
                    </select>
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">รหัสประกาศ</label>
                    <input 
                      type="text" 
                      value={detailFormData.announcementCode} 
                      onChange={e => setDetailFormData({...detailFormData, announcementCode: e.target.value})}
                      placeholder="เช่น ANN-2567-01"
                      className="w-full border rounded p-2 text-xs font-mono focus:ring-1 focus:ring-warning outline-none bg-white" 
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">ชื่อย่อหลักสูตร</label>
                    <input 
                      type="text" 
                      value={detailFormData.courseShortName} 
                      onChange={e => setDetailFormData({...detailFormData, courseShortName: e.target.value})}
                      placeholder="เช่น ปรภ.1"
                      className="w-full border rounded p-2 text-xs focus:ring-1 focus:ring-warning outline-none bg-white" 
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">รหัสหลักสูตร</label>
                    <input 
                      type="text" 
                      value={detailFormData.curriculumCode} 
                      onChange={e => setDetailFormData({...detailFormData, curriculumCode: e.target.value})}
                      placeholder="เช่น CURR-101"
                      className="w-full border rounded p-2 text-xs font-mono focus:ring-1 focus:ring-warning outline-none bg-white" 
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">รหัสวิชา (Course Code)</label>
                    <input 
                      type="text" 
                      value={detailFormData.courseCode} 
                      onChange={e => setDetailFormData({...detailFormData, courseCode: e.target.value})}
                      placeholder="เช่น BASIC-01"
                      className="w-full border rounded p-2 text-xs font-mono focus:ring-1 focus:ring-warning outline-none bg-white" 
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">รหัสวิชา OIC</label>
                    <input 
                      type="text" 
                      value={detailFormData.oicCourseCode} 
                      onChange={e => setDetailFormData({...detailFormData, oicCourseCode: e.target.value})}
                      placeholder="เช่น OIC-SUBJ-001"
                      className="w-full border rounded p-2 text-xs font-mono focus:ring-1 focus:ring-warning outline-none bg-white" 
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">ลำดับ</label>
                    <input 
                      type="number" 
                      value={detailFormData.displayOrder} 
                      onChange={e => setDetailFormData({...detailFormData, displayOrder: e.target.value})}
                      className="w-full border rounded p-2 text-xs focus:ring-1 focus:ring-warning outline-none text-center bg-white" 
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-gray-700 mb-1">สถานะ</label>
                    <select 
                      value={detailFormData.status} 
                      onChange={e => setDetailFormData({...detailFormData, status: e.target.value})}
                      className="w-full border rounded p-2 text-xs focus:ring-1 focus:ring-warning outline-none bg-white"
                    >
                      <option value="active">Active</option>
                      <option value="inactive">Inactive</option>
                    </select>
                  </div>
                </div>

                <div className="flex justify-end gap-2 pt-2 border-t border-gray-200">
                  {editingDetailId && (
                    <button 
                      type="button" 
                      onClick={resetDetailForm}
                      className="px-3 py-1.5 border rounded text-xs text-gray-600 hover:bg-gray-100"
                    >
                      ยกเลิก
                    </button>
                  )}
                  <button 
                    type="submit" 
                    className="px-4 py-1.5 bg-yellow-600 text-white font-medium rounded text-xs hover:bg-yellow-700 shadow-sm"
                  >
                    <i className="fas fa-save mr-1"></i>
                    {editingDetailId ? 'บันทึกการแก้ไข' : 'เพิ่มรหัส OIC นี้'}
                  </button>
                </div>
              </form>
            </div>

            <div className="flex justify-end mt-4 pt-3 border-t">
              <button 
                type="button" 
                onClick={() => { setShowDetailsModal(false); resetDetailForm(); }}
                className="px-5 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm font-medium"
              >
                ปิดหน้าต่าง
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
