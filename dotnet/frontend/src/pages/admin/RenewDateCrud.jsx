import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';

const API_BASE_URL = 'http://localhost:8085/api';

export default function RenewDateCrud() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  
  const [showModal, setShowModal] = useState(false);
  const [editItem, setEditItem] = useState(null);
  const [formData, setFormData] = useState({});

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const res = await fetch(`${API_BASE_URL}/RenewData/dates`);
      if (!res.ok) throw new Error('Failed to fetch data');
      const json = await res.json();
      setData(json);
      setError(null);
    } catch (err) {
      console.error(err);
      setError('ไม่สามารถโหลดข้อมูลได้');
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      const method = editItem ? 'PUT' : 'POST';
      const url = editItem 
        ? `${API_BASE_URL}/RenewData/dates/${editItem.id}` 
        : `${API_BASE_URL}/RenewData/dates`;

      const res = await fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
      });
      if (!res.ok) throw new Error('Save failed');
      
      setShowModal(false);
      fetchData();
    } catch (err) {
      alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('คุณต้องการลบข้อมูลนี้ใช่หรือไม่?')) return;
    try {
      const res = await fetch(`${API_BASE_URL}/RenewData/dates/${id}`, {
        method: 'DELETE'
      });
      if (!res.ok) throw new Error('Delete failed');
      fetchData();
    } catch (err) {
      alert('เกิดข้อผิดพลาดในการลบข้อมูล');
    }
  };

  const openAddModal = () => {
    setEditItem(null);
    setFormData({ status: 'active', displayOrder: data.length + 1, courseDateDisplay: '', courseDate: new Date().toISOString().split('T')[0] });
    setShowModal(true);
  };

  const openEditModal = (item) => {
    setEditItem(item);
    setFormData({ 
        ...item, 
        courseDate: item.courseDate ? item.courseDate.split('T')[0] : '' 
    });
    setShowModal(true);
  };

  return (
    <div className="max-w-6xl mx-auto">
      <div className="mb-6 border-b-2 border-warning pb-4 flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold text-warning mb-2">จัดการวันที่เปิดอบรม (Renew Dates)</h2>
          <p className="text-gray-500 text-sm">เพิ่ม ลบ หรือแก้ไขข้อมูลวันที่เปิดอบรม</p>
        </div>
        <Link to="/admin/master-data" className="px-4 py-2 border border-gray-400 text-gray-600 rounded hover:bg-gray-100 transition-colors">
          <i className="fas fa-arrow-left mr-2"></i> กลับหน้ารวม Master Data
        </Link>
      </div>

      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex justify-end mb-4">
          <button onClick={openAddModal} className="px-4 py-2 bg-success text-white rounded hover:bg-green-600 transition">
            <i className="fas fa-plus mr-2"></i> เพิ่มข้อมูล
          </button>
        </div>

        {loading ? (
          <div className="text-center py-10"><i className="fas fa-spinner fa-spin text-3xl text-warning"></i></div>
        ) : error ? (
          <div className="text-danger text-center py-5">{error}</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="bg-gray-100 text-gray-600">
                  <th className="p-3 border-b">ID</th>
                  <th className="p-3 border-b">ข้อความแสดงวันที่ (Display)</th>
                  <th className="p-3 border-b">วันที่จริง (Date)</th>
                  <th className="p-3 border-b">ลำดับ</th>
                  <th className="p-3 border-b text-center">จัดการ</th>
                </tr>
              </thead>
              <tbody>
                {data.map(item => (
                  <tr key={item.id} className="border-b hover:bg-gray-50">
                    <td className="p-3 text-gray-500">#{item.id}</td>
                    <td className="p-3 text-primary">{item.courseDateDisplay}</td>
                    <td className="p-3">{new Date(item.courseDate).toLocaleDateString('th-TH')}</td>
                    <td className="p-3">{item.displayOrder}</td>
                    <td className="p-3 text-center">
                      <button onClick={() => openEditModal(item)} className="text-warning hover:text-yellow-700 mx-2" title="แก้ไข">
                        <i className="fas fa-edit"></i>
                      </button>
                      <button onClick={() => handleDelete(item.id)} className="text-danger hover:text-red-700 mx-2" title="ลบ">
                        <i className="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                ))}
                {data.length === 0 && (
                  <tr>
                    <td colSpan="6" className="text-center p-8 text-gray-500">ไม่มีข้อมูล</td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <h3 className="text-xl font-bold mb-4 text-warning">{editItem ? 'แก้ไขข้อมูล' : 'เพิ่มข้อมูล'}</h3>
            <form onSubmit={handleSave}>
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-1">ข้อความแสดงวันที่ (เช่น 12 ม.ค. 67)</label>
                <input type="text" required value={formData.courseDateDisplay || ''} onChange={e => setFormData({...formData, courseDateDisplay: e.target.value})} className="w-full border rounded p-2 focus:ring-2 focus:ring-warning outline-none" />
              </div>
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-1">วันที่จริง (เลือกปฏิทิน)</label>
                <input type="date" required value={formData.courseDate || ''} onChange={e => setFormData({...formData, courseDate: e.target.value})} className="w-full border rounded p-2 focus:ring-2 focus:ring-warning outline-none" />
              </div>
              <div className="mb-6 flex gap-4">
                  <div className="flex-1">
                    <label className="block text-sm font-medium text-gray-700 mb-1">ลำดับการแสดงผล</label>
                    <input type="number" required value={formData.displayOrder || 0} onChange={e => setFormData({...formData, displayOrder: parseInt(e.target.value)})} className="w-full border rounded p-2 focus:ring-2 focus:ring-warning outline-none" />
                  </div>
                  <div className="flex-1">
                    <label className="block text-sm font-medium text-gray-700 mb-1">สถานะ</label>
                    <select value={formData.status || 'active'} onChange={e => setFormData({...formData, status: e.target.value})} className="w-full border rounded p-2 focus:ring-2 focus:ring-warning outline-none">
                      <option value="active">Active</option>
                      <option value="inactive">Inactive</option>
                    </select>
                  </div>
              </div>
              <div className="flex justify-end gap-2">
                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 border rounded hover:bg-gray-100">ยกเลิก</button>
                <button type="submit" className="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">บันทึก</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
