import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';

const API_BASE_URL = 'http://localhost:8085/api';

export default function LocationMasterCrud({ type: propType }) {
  const { type: paramType } = useParams(); // fallback
  const type = propType || paramType;
  const [data, setData] = useState([]);
  const [provinces, setProvinces] = useState([]);
  const [districts, setDistricts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  
  const [showModal, setShowModal] = useState(false);
  const [editItem, setEditItem] = useState(null);
  const [formData, setFormData] = useState({});

  const getTypeTitle = () => {
    switch (type) {
      case 'provinces': return 'จัดการจังหวัด';
      case 'districts': return 'จัดการอำเภอ';
      case 'subdistricts': return 'จัดการตำบล';
      default: return 'จัดการข้อมูลสถานที่';
    }
  };

  useEffect(() => {
    fetchData();
  }, [type]);

  const fetchData = async () => {
    try {
      setLoading(true);
      const res = await fetch(`${API_BASE_URL}/Location/${type}`);
      if (!res.ok) throw new Error('Failed to fetch data');
      const json = await res.json();
      setData(json);

      if (type === 'districts' || type === 'subdistricts') {
        const pRes = await fetch(`${API_BASE_URL}/Location/provinces`);
        if (pRes.ok) setProvinces(await pRes.json());
      }
      if (type === 'subdistricts') {
        const dRes = await fetch(`${API_BASE_URL}/Location/districts`);
        if (dRes.ok) setDistricts(await dRes.json());
      }

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
        ? `${API_BASE_URL}/Location/${type}/${editItem.id}` 
        : `${API_BASE_URL}/Location/${type}`;

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
      const res = await fetch(`${API_BASE_URL}/Location/${type}/${id}`, {
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
    let initial = {};
    if (type === 'provinces') initial = { provinceId: 0, provinceThai: '', provinceEng: '' };
    if (type === 'districts') initial = { districtId: 0, districtThai: '', districtEng: '', provinceId: 0 };
    if (type === 'subdistricts') initial = { subDistrictId: 0, subDistrictThai: '', subDistrictEng: '', districtId: 0, zipcode: '' };
    setFormData(initial);
    setShowModal(true);
  };

  const openEditModal = (item) => {
    setEditItem(item);
    setFormData({ ...item });
    setShowModal(true);
  };

  return (
    <div className="max-w-6xl mx-auto">
      <div className="mb-6 border-b-2 border-info pb-4 flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold text-info mb-2">{getTypeTitle()}</h2>
          <p className="text-gray-500 text-sm">เพิ่ม ลบ หรือแก้ไขข้อมูลสถานที่</p>
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
          <div className="text-center py-10"><i className="fas fa-spinner fa-spin text-3xl text-info"></i></div>
        ) : error ? (
          <div className="text-danger text-center py-5">{error}</div>
        ) : (
          <div className="overflow-x-auto h-96">
            <table className="w-full text-left border-collapse relative">
              <thead className="sticky top-0 bg-white shadow-sm">
                <tr className="bg-gray-100 text-gray-600">
                  <th className="p-3 border-b">ID</th>
                  <th className="p-3 border-b">รหัสอ้างอิง</th>
                  <th className="p-3 border-b">ชื่อ (ไทย)</th>
                  <th className="p-3 border-b">ชื่อ (Eng)</th>
                  {type === 'districts' && <th className="p-3 border-b">จังหวัด</th>}
                  {type === 'subdistricts' && <th className="p-3 border-b">อำเภอ</th>}
                  {type === 'subdistricts' && <th className="p-3 border-b">รหัสไปรษณีย์</th>}
                  <th className="p-3 border-b text-center">จัดการ</th>
                </tr>
              </thead>
              <tbody>
                {data.map(item => (
                  <tr key={item.id} className="border-b hover:bg-gray-50">
                    <td className="p-3 text-gray-500">#{item.id}</td>
                    <td className="p-3">
                      {type === 'provinces' && item.provinceId}
                      {type === 'districts' && item.districtId}
                      {type === 'subdistricts' && item.subDistrictId}
                    </td>
                    <td className="p-3 font-medium">
                      {type === 'provinces' && item.provinceThai}
                      {type === 'districts' && item.districtThai}
                      {type === 'subdistricts' && item.subDistrictThai}
                    </td>
                    <td className="p-3">
                      {type === 'provinces' && item.provinceEng}
                      {type === 'districts' && item.districtEng}
                      {type === 'subdistricts' && item.subDistrictEng}
                    </td>
                    {type === 'districts' && <td className="p-3">{provinces.find(p => p.provinceId === item.provinceId)?.provinceThai || item.provinceId}</td>}
                    {type === 'subdistricts' && <td className="p-3">{districts.find(d => d.districtId === item.districtId)?.districtThai || item.districtId}</td>}
                    {type === 'subdistricts' && <td className="p-3">{item.zipcode}</td>}
                    
                    <td className="p-3 text-center">
                      <button onClick={() => openEditModal(item)} className="text-info hover:text-blue-700 mx-2" title="แก้ไข">
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
                    <td colSpan="8" className="text-center p-8 text-gray-500">ไม่มีข้อมูล</td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg shadow-lg w-full max-w-md p-6 max-h-screen overflow-y-auto">
            <h3 className="text-xl font-bold mb-4 text-info">{editItem ? 'แก้ไขข้อมูล' : 'เพิ่มข้อมูล'}</h3>
            <form onSubmit={handleSave}>
              
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-1">รหัสอ้างอิง</label>
                <input type="number" required value={
                    type === 'provinces' ? formData.provinceId :
                    type === 'districts' ? formData.districtId :
                    formData.subDistrictId
                } onChange={e => {
                  const val = parseInt(e.target.value);
                  if (type === 'provinces') setFormData({...formData, provinceId: val});
                  if (type === 'districts') setFormData({...formData, districtId: val});
                  if (type === 'subdistricts') setFormData({...formData, subDistrictId: val});
                }} className="w-full border rounded p-2 focus:ring-2 focus:ring-info outline-none" />
              </div>

              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-1">ชื่อ (ภาษาไทย)</label>
                <input type="text" required value={
                    type === 'provinces' ? formData.provinceThai || '' :
                    type === 'districts' ? formData.districtThai || '' :
                    formData.subDistrictThai || ''
                } onChange={e => {
                  const val = e.target.value;
                  if (type === 'provinces') setFormData({...formData, provinceThai: val});
                  if (type === 'districts') setFormData({...formData, districtThai: val});
                  if (type === 'subdistricts') setFormData({...formData, subDistrictThai: val});
                }} className="w-full border rounded p-2 focus:ring-2 focus:ring-info outline-none" />
              </div>

              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-1">ชื่อ (ภาษาอังกฤษ)</label>
                <input type="text" value={
                    type === 'provinces' ? formData.provinceEng || '' :
                    type === 'districts' ? formData.districtEng || '' :
                    formData.subDistrictEng || ''
                } onChange={e => {
                  const val = e.target.value;
                  if (type === 'provinces') setFormData({...formData, provinceEng: val});
                  if (type === 'districts') setFormData({...formData, districtEng: val});
                  if (type === 'subdistricts') setFormData({...formData, subDistrictEng: val});
                }} className="w-full border rounded p-2 focus:ring-2 focus:ring-info outline-none" />
              </div>

              {type === 'districts' && (
                <div className="mb-4">
                  <label className="block text-sm font-medium text-gray-700 mb-1">จังหวัด</label>
                  <select required value={formData.provinceId || ''} onChange={e => setFormData({...formData, provinceId: parseInt(e.target.value)})} className="w-full border rounded p-2 focus:ring-2 focus:ring-info outline-none">
                    <option value="" disabled>เลือกจังหวัด...</option>
                    {provinces.map(p => (
                      <option key={p.provinceId} value={p.provinceId}>{p.provinceThai}</option>
                    ))}
                  </select>
                </div>
              )}

              {type === 'subdistricts' && (
                <>
                  <div className="mb-4">
                    <label className="block text-sm font-medium text-gray-700 mb-1">อำเภอ</label>
                    <select required value={formData.districtId || ''} onChange={e => setFormData({...formData, districtId: parseInt(e.target.value)})} className="w-full border rounded p-2 focus:ring-2 focus:ring-info outline-none">
                      <option value="" disabled>เลือกอำเภอ...</option>
                      {districts.map(d => {
                        const provName = provinces.find(p => p.provinceId === d.provinceId)?.provinceThai || '';
                        return (
                          <option key={d.districtId} value={d.districtId}>
                            {d.districtThai} {provName ? `(จ.${provName})` : ''}
                          </option>
                        );
                      })}
                    </select>
                  </div>
                  <div className="mb-6">
                    <label className="block text-sm font-medium text-gray-700 mb-1">รหัสไปรษณีย์</label>
                    <input type="text" value={formData.zipcode || ''} onChange={e => setFormData({...formData, zipcode: e.target.value})} className="w-full border rounded p-2 focus:ring-2 focus:ring-info outline-none" />
                  </div>
                </>
              )}

              <div className="flex justify-end gap-2 mt-4">
                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 border rounded hover:bg-gray-100">ยกเลิก</button>
                <button type="submit" className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">บันทึก</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
