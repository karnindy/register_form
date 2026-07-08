import { useState, useEffect } from 'react';

export default function TraineesList() {
  const [trainees, setTrainees] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchTrainees = async () => {
      try {
        const token = localStorage.getItem('admin_token');
        const response = await fetch('http://localhost:8085/api/admin/trainees', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        if (response.ok) {
          const data = await response.json();
          setTrainees(data);
        }
      } catch (error) {
        console.error('Failed to fetch trainees', error);
      } finally {
        setLoading(false);
      }
    };
    fetchTrainees();
  }, []);

  if (loading) return <div className="text-center p-10">กำลังโหลดข้อมูล...</div>;

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold text-primary">รายชื่อผู้ลงทะเบียนอบรม</h2>
        <button className="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">
          <i className="fas fa-file-excel mr-2"></i> Export Excel
        </button>
      </div>

      <div className="overflow-x-auto bg-white rounded-lg shadow">
        <table className="min-w-full table-auto">
          <thead className="bg-gray-50 border-b border-gray-200">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ลำดับ</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อ-นามสกุล</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เลขบัตร ปชช.</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">หลักสูตร</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่สมัคร</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {trainees.map((t, index) => (
              <tr key={t.id} className="hover:bg-gray-50">
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{index + 1}</td>
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
                  <a href="#" className="text-primary hover:text-primary-light mr-3"><i className="fas fa-eye"></i> ดูข้อมูล</a>
                  <a href="#" className="text-red-600 hover:text-red-900"><i className="fas fa-trash"></i></a>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
