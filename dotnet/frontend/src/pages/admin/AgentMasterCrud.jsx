import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';

const API_BASE = 'http://localhost:8085/api';

export default function AgentMasterCrud({ type: propType }) {
  const { type: paramType } = useParams();
  const type = propType || paramType;

  const [data, setData] = useState([]);
  const [regions, setRegions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const [showModal, setShowModal] = useState(false);
  const [editItem, setEditItem] = useState(null);
  const [formData, setFormData] = useState({});

  useEffect(() => {
    fetchData();
    if (type === 'agent-branches') {
      fetchRegions();
    }
  }, [type]);

  const fetchData = async () => {
    try {
      setLoading(true);
      const endpoint = type === 'agent-regions' ? 'regions' : 'branches';
      const res = await fetch(`${API_BASE}/AgentMaster/${endpoint}`);
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

  const fetchRegions = async () => {
    try {
      const res = await fetch(`${API_BASE}/AgentMaster/regions`);
      if (res.ok) {
        setRegions(await res.json());
      }
    } catch (err) {
      console.error(err);
    }
  };

  const getTypeTitle = () => {
    if (type === 'agent-regions') return 'จัดการภูมิภาค (ตัวแทน)';
    if (type === 'agent-branches') return 'จัดการสาขา (ตัวแทน)';
    return 'จัดการข้อมูล';
  };

  const openAddModal = () => {
    setEditItem(null);
    setFormData(type === 'agent-branches' ? { name: '', regionId: '' } : { name: '' });
    setShowModal(true);
  };

  const openEditModal = (item) => {
    setEditItem(item);
    setFormData({ ...item });
    setShowModal(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      const endpoint = type === 'agent-regions' ? 'regions' : 'branches';
      const url = editItem 
        ? `${API_BASE}/AgentMaster/${endpoint}/${editItem.id}`
        : `${API_BASE}/AgentMaster/${endpoint}`;
        
      const res = await fetch(url, {
        method: editItem ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
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
      const endpoint = type === 'agent-regions' ? 'regions' : 'branches';
      const res = await fetch(`${API_BASE}/AgentMaster/${endpoint}/${id}`, { method: 'DELETE' });
      if (!res.ok) {
        const text = await res.text();
        throw new Error(text || 'Failed to delete');
      }
      fetchData();
    } catch (err) {
      alert('Error: ' + err.message);
    }
  };

  return (
    <div className="max-w-6xl mx-auto">
      <div className="mb-6 border-b-2 border-primary pb-4 flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold text-primary mb-2">{getTypeTitle()}</h2>
          <p className="text-gray-500 text-sm">เพิ่ม ลบ หรือแก้ไขข้อมูล</p>
        </div>
        <Link to="/admin" className="px-4 py-2 border border-gray-400 text-gray-600 rounded hover:bg-gray-100 transition-colors">
          <i className="fas fa-arrow-left mr-2"></i> กลับหน้าหลัก
        </Link>
      </div>

      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex justify-end mb-4">
          <button onClick={openAddModal} className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
            <i className="fas fa-plus mr-2"></i> เพิ่มข้อมูล
          </button>
        </div>

        {loading ? (
          <div className="text-center py-10"><i className="fas fa-spinner fa-spin text-3xl text-primary"></i></div>
        ) : error ? (
          <div className="text-red-600 text-center py-5">{error}</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="bg-gray-100 text-gray-600">
                  <th className="p-3 border-b w-24 text-center">ID</th>
                  <th className="p-3 border-b">ชื่อ</th>
                  {type === 'agent-branches' && <th className="p-3 border-b">ภูมิภาค</th>}
                  <th className="p-3 border-b text-center w-24">จัดการ</th>
                </tr>
              </thead>
              <tbody>
                {data.map(item => (
                  <tr key={item.id} className="border-b hover:bg-gray-50">
                    <td className="p-3 text-center text-gray-500">#{item.id}</td>
                    <td className="p-3 font-medium text-gray-800">{item.name}</td>
                    {type === 'agent-branches' && (
                      <td className="p-3 text-gray-600">
                        {regions.find(r => r.id === item.regionId)?.name || `Region ID: ${item.regionId}`}
                      </td>
                    )}
                    <td className="p-3 text-center">
                      <button onClick={() => openEditModal(item)} className="text-blue-600 hover:text-blue-800 mx-2" title="แก้ไข">
                        <i className="fas fa-edit"></i>
                      </button>
                      <button onClick={() => handleDelete(item.id)} className="text-red-600 hover:text-red-800 mx-2" title="ลบ">
                        <i className="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                ))}
                {data.length === 0 && (
                  <tr>
                    <td colSpan={type === 'agent-branches' ? 4 : 3} className="text-center p-8 text-gray-500">ไม่มีข้อมูล</td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h3 className="text-xl font-bold mb-4 text-primary">{editItem ? 'แก้ไขข้อมูล' : 'เพิ่มข้อมูล'}</h3>
            <form onSubmit={handleSave}>
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-1">ชื่อ</label>
                <input type="text" required value={formData.name || ''} onChange={e => setFormData({...formData, name: e.target.value})} className="w-full border rounded p-2 focus:ring-2 focus:ring-primary outline-none" />
              </div>

              {type === 'agent-branches' && (
                <div className="mb-6">
                  <label className="block text-sm font-medium text-gray-700 mb-1">ภูมิภาค</label>
                  <select required value={formData.regionId || ''} onChange={e => setFormData({...formData, regionId: parseInt(e.target.value)})} className="w-full border rounded p-2 focus:ring-2 focus:ring-primary outline-none">
                    <option value="" disabled>เลือกภูมิภาค...</option>
                    {regions.map(r => (
                      <option key={r.id} value={r.id}>{r.name}</option>
                    ))}
                  </select>
                </div>
              )}

              <div className="flex justify-end gap-2 mt-6">
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
