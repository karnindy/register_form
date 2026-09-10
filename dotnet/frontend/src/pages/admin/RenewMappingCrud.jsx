import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';

const API_BASE_URL = 'http://localhost:8085/api';

export default function RenewMappingCrud() {
  const [pillars, setPillars] = useState([]);
  const [dates, setDates] = useState([]);
  const [subjects, setSubjects] = useState([]);
  const [mappings, setMappings] = useState([]);
  const [loading, setLoading] = useState(true);
  
  // Slots for the drag & drop builder
  const [selectedPillar, setSelectedPillar] = useState(null);
  const [selectedDate, setSelectedDate] = useState(null);
  const [selectedSubject, setSelectedSubject] = useState(null);
  const [displayOrder, setDisplayOrder] = useState(1);
  const [isSaving, setIsSaving] = useState(false);

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const [pRes, dRes, sRes, mRes] = await Promise.all([
        fetch(`${API_BASE_URL}/MasterData/pillars?all=true`),
        fetch(`${API_BASE_URL}/RenewData/dates?all=true`),
        fetch(`${API_BASE_URL}/MasterData/renewcourse?all=true`),
        fetch(`${API_BASE_URL}/RenewData/mappings?all=true`)
      ]);
      
      const pJson = await pRes.json();
      const dJson = await dRes.json();
      const sJson = await sRes.json();
      const mJson = await mRes.json();

      setPillars(pJson.filter(x => x.status === 'active'));
      setDates(dJson.filter(x => x.status === 'active'));
      setSubjects(sJson.filter(x => x.status === 'active'));
      setMappings(mJson);
      setDisplayOrder(mJson.length > 0 ? Math.max(...mJson.map(m => m.displayOrder)) + 1 : 1);
    } catch (err) {
      console.error(err);
      alert('ไม่สามารถโหลดข้อมูลได้');
    } finally {
      setLoading(false);
    }
  };

  const handleDragStart = (e, item, type) => {
    e.dataTransfer.setData('application/json', JSON.stringify({ item, type }));
    e.dataTransfer.effectAllowed = 'copy';
  };

  const handleDragOver = (e) => {
    e.preventDefault(); // allow drop
    e.dataTransfer.dropEffect = 'copy';
  };

  const handleDrop = (e, targetType) => {
    e.preventDefault();
    try {
      const data = JSON.parse(e.dataTransfer.getData('application/json'));
      if (data.type === targetType) {
        if (targetType === 'pillar') setSelectedPillar(data.item);
        if (targetType === 'date') setSelectedDate(data.item);
        if (targetType === 'subject') {
          setSelectedSubject(data.item);
          // If subject has default pillar defined, auto-fill the pillar slot
          if (data.item.defaultPillarId) {
            const foundPillar = pillars.find(p => p.id === data.item.defaultPillarId);
            if (foundPillar) {
              setSelectedPillar(foundPillar);
            }
          }
        }
      } else {
        alert('กรุณาวางในช่องที่ตรงกับประเภทคำศัพท์');
      }
    } catch (err) {
      console.error('Drop error', err);
    }
  };

  const handleSave = async () => {
    if (!selectedPillar || !selectedDate || !selectedSubject) {
      alert('กรุณาลากคำศัพท์มาวางให้ครบทั้ง 3 ช่อง');
      return;
    }

    try {
      setIsSaving(true);
      const payload = {
        pillarId: selectedPillar.id,
        dateId: selectedDate.id,
        subjectId: selectedSubject.id,
        status: 'active',
        displayOrder: parseInt(displayOrder) || 1
      };

      const res = await fetch(`${API_BASE_URL}/RenewData/mappings`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      if (!res.ok) throw new Error('Failed to save mapping');
      
      // Reset slots
      setSelectedPillar(null);
      setSelectedDate(null);
      setSelectedSubject(null);
      
      await fetchData();
    } catch (err) {
      alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
    } finally {
      setIsSaving(false);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('คุณต้องการลบข้อมูลนี้ใช่หรือไม่?')) return;
    try {
      const res = await fetch(`${API_BASE_URL}/RenewData/mappings/${id}`, { method: 'DELETE' });
      if (!res.ok) throw new Error('Delete failed');
      fetchData();
    } catch (err) {
      alert('เกิดข้อผิดพลาดในการลบข้อมูล');
    }
  };

  // Draggable pill component
  const DraggablePill = ({ item, type, colorClass }) => {
    const details = item.curriculums && item.curriculums.length > 0
      ? item.curriculums.map(c => ({
          id: c.id,
          agentType: c.agentType,
          courseCode: c.trainingCourseCode,
          curriculumCode: c.curriculumCode,
          oicCourseCode: c.subDetails?.[0]?.oicCourseCode,
          subCourseName: c.subDetails?.[0]?.subCourseName
        }))
      : (item.details || []);
    return (
      <div 
        draggable
        onDragStart={(e) => handleDragStart(e, item, type)}
        className={`px-3 py-2 mb-2 rounded cursor-grab shadow-sm border text-sm hover:opacity-80 active:cursor-grabbing bg-white ${colorClass}`}
      >
        <div className="flex items-center justify-between">
          <div className="flex items-center">
            <i className="fas fa-grip-vertical text-gray-400 mr-2"></i>
            <span className="font-medium">{item.name || item.courseDateDisplay}</span>
          </div>
        </div>
        {(details.length > 0 || (type === 'subject' && (item.defaultPillarName || item.defaultPillarId))) && (
          <div className="flex flex-wrap gap-1 mt-1 pl-5">
            {type === 'subject' && (item.defaultPillarName || item.defaultPillarId) && (
              <span className="text-[11px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-medium" title="เสาหลักเริ่มต้น">
                <i className="fas fa-landmark mr-1 text-[10px]"></i>{item.defaultPillarName || `Pillar #${item.defaultPillarId}`}
              </span>
            )}
            {details.map(d => (
              <span key={d.id} className="text-[10px] bg-purple-50 text-purple-800 border border-purple-200 px-1.5 py-0.5 rounded font-mono" title={`ประเภท: ${d.agentType === 'broker' ? 'นายหน้า' : d.agentType === 'both' ? 'ทั้งสองประเภท' : 'ตัวแทน'} | ชื่อวิชาย่อย: ${d.subCourseName || '-'} | รหัส OIC: ${d.oicCourseCode || '-'} | วิชา: ${d.courseCode || '-'}`}>
                {d.agentType === 'broker' ? <span className="text-amber-700 font-bold mr-0.5">[B]</span> : d.agentType === 'both' ? <span className="text-indigo-700 font-bold mr-0.5">[A+B]</span> : <span className="text-blue-700 font-bold mr-0.5">[A]</span>}
                {d.subCourseName && <span className="font-sans font-semibold text-gray-800 mr-1">{d.subCourseName}</span>}
                {d.oicCourseCode ? <><i className="fas fa-certificate text-[9px] mr-0.5 text-rose-600"></i>{d.oicCourseCode}</> : (d.courseCode || d.curriculumCode)}
              </span>
            ))}
          </div>
        )}
      </div>
    );
  };

  // Drop zone slot
  const DropSlot = ({ type, selectedItem, placeholder, colorBorder }) => (
    <div 
      onDragOver={handleDragOver}
      onDrop={(e) => handleDrop(e, type)}
      className={`relative border-2 border-dashed ${selectedItem ? 'border-gray-300 bg-gray-50' : colorBorder + ' bg-white'} rounded-lg h-24 flex flex-col items-center justify-center transition-colors`}
    >
      {selectedItem ? (
        <div className="text-center px-2">
          <div className="text-xs text-gray-500 mb-1">{placeholder}</div>
          <div className="font-bold text-gray-800">{selectedItem.name || selectedItem.courseDateDisplay}</div>
          <button 
            onClick={() => {
              if (type === 'pillar') setSelectedPillar(null);
              if (type === 'date') setSelectedDate(null);
              if (type === 'subject') setSelectedSubject(null);
            }} 
            className="absolute top-1 right-2 text-gray-400 hover:text-danger"
          >
            <i className="fas fa-times"></i>
          </button>
        </div>
      ) : (
        <div className="text-gray-400 text-sm text-center">
          <i className="fas fa-plus mb-2 text-xl block"></i>
          วาง {placeholder} ที่นี่
        </div>
      )}
    </div>
  );

  return (
    <div className="max-w-7xl mx-auto">
      <div className="mb-6 border-b-2 border-warning pb-4 flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold text-warning mb-2">จับคู่วิชาต่ออายุ (Renew Mappings)</h2>
          <p className="text-gray-500 text-sm">ลากคำศัพท์จากคลังด้านซ้าย มาวางเพื่อสร้างวิชาต่ออายุ</p>
        </div>
        <Link to="/admin/master-data" className="px-4 py-2 border border-gray-400 text-gray-600 rounded hover:bg-gray-100 transition-colors">
          <i className="fas fa-arrow-left mr-2"></i> กลับหน้ารวม Master Data
        </Link>
      </div>

      {loading ? (
        <div className="text-center py-20"><i className="fas fa-spinner fa-spin text-3xl text-warning"></i></div>
      ) : (
        <div className="flex flex-col lg:flex-row gap-6">
          
          {/* Left Column: Draggable Vocabulary */}
          <div className="lg:w-1/3 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <h3 className="text-lg font-bold mb-4 text-gray-700 border-b pb-2"><i className="fas fa-book mr-2"></i>คลังคำศัพท์</h3>
            
            <div className="space-y-6 max-h-[800px] overflow-y-auto pr-2">
              <div>
                <h4 className="text-sm font-semibold text-blue-700 mb-2">1. Pillars</h4>
                {pillars.map(p => <DraggablePill key={p.id} item={p} type="pillar" colorClass="border-blue-300 text-blue-800" />)}
              </div>
              <div>
                <h4 className="text-sm font-semibold text-green-700 mb-2">2. วันที่เปิดอบรม (Dates)</h4>
                {dates.map(d => <DraggablePill key={d.id} item={d} type="date" colorClass="border-green-300 text-green-800" />)}
              </div>
              <div>
                <h4 className="text-sm font-semibold text-purple-700 mb-2">3. ชื่อวิชา (Subjects)</h4>
                {subjects.map(s => <DraggablePill key={s.id} item={s} type="subject" colorClass="border-purple-300 text-purple-800" />)}
              </div>
            </div>
          </div>

          {/* Right Column: Drop Zone & Table */}
          <div className="lg:w-2/3 flex flex-col gap-6">
            
            {/* Drop Zone */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-bold mb-4 text-warning"><i className="fas fa-object-group mr-2"></i>ลากมาวางเพื่อสร้างวิชาใหม่</h3>
              
              <div className="grid grid-cols-3 gap-4 mb-6">
                <DropSlot type="pillar" selectedItem={selectedPillar} placeholder="Pillar" colorBorder="border-blue-300" />
                <DropSlot type="date" selectedItem={selectedDate} placeholder="วันที่" colorBorder="border-green-300" />
                <DropSlot type="subject" selectedItem={selectedSubject} placeholder="ชื่อวิชา" colorBorder="border-purple-300" />
              </div>

              {/* Preview & Save */}
              <div className="bg-gray-50 p-4 rounded-lg border flex flex-col sm:flex-row items-center justify-between gap-4">
                <div className="flex-1">
                  <span className="text-sm text-gray-500 block mb-1">ตัวอย่างข้อความที่จะแสดงผล:</span>
                  <div className="text-lg font-medium text-gray-800 min-h-[1.75rem]">
                    {selectedPillar || selectedDate || selectedSubject ? (
                      `[${selectedPillar?.name || '...'}] [${selectedDate?.courseDateDisplay || '...'}] : ${selectedSubject?.name || '...'}`
                    ) : (
                      <span className="text-gray-400 italic">กรุณาลากคำมาวาง</span>
                    )}
                  </div>
                </div>
                
                <div className="flex items-center gap-3">
                  <div>
                    <label className="text-xs text-gray-500 block">ลำดับแสดงผล</label>
                    <input type="number" value={displayOrder} onChange={e => setDisplayOrder(e.target.value)} className="w-20 border rounded p-2 text-center" min="1" />
                  </div>
                  <button 
                    onClick={handleSave} 
                    disabled={!selectedPillar || !selectedDate || !selectedSubject || isSaving}
                    className="px-6 py-2 bg-yellow-600 text-white font-bold rounded hover:bg-yellow-700 disabled:opacity-50 disabled:cursor-not-allowed mt-4"
                  >
                    {isSaving ? 'กำลังบันทึก...' : 'บันทึกวิชานี้'}
                  </button>
                </div>
              </div>
            </div>

            {/* Mappings Table */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex-1">
              <h3 className="text-lg font-bold mb-4 text-gray-700">รายการวิชาที่สร้างแล้ว</h3>
              <div className="overflow-x-auto">
                <table className="w-full text-left border-collapse">
                  <thead>
                    <tr className="bg-gray-100 text-gray-600">
                      <th className="p-3 border-b text-sm">ข้อความที่แสดงผล</th>
                      <th className="p-3 border-b text-sm w-16 text-center">ลำดับ</th>
                      <th className="p-3 border-b text-sm w-20 text-center">สถานะ</th>
                      <th className="p-3 border-b text-sm w-16 text-center">จัดการ</th>
                    </tr>
                  </thead>
                  <tbody>
                    {mappings.map(m => (
                      <tr key={m.id} className="border-b hover:bg-gray-50">
                        <td className="p-3">
                          <div className="font-medium text-gray-800">
                            [{m.pillarName}] [{m.dateDisplay}] : {m.subjectName}
                          </div>
                        </td>
                        <td className="p-3 text-center">{m.displayOrder}</td>
                        <td className="p-3 text-center">
                          <span className={`px-2 py-1 rounded text-xs ${m.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                            {m.status.toUpperCase()}
                          </span>
                        </td>
                        <td className="p-3 text-center">
                          <button onClick={() => handleDelete(m.id)} className="text-danger hover:text-red-700" title="ลบ">
                            <i className="fas fa-trash"></i>
                          </button>
                        </td>
                      </tr>
                    ))}
                    {mappings.length === 0 && (
                      <tr>
                        <td colSpan="4" className="text-center p-8 text-gray-500">ไม่มีข้อมูล</td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      )}
    </div>
  );
}
