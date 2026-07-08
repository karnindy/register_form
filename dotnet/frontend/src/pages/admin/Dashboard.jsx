import { Link } from 'react-router-dom';

export default function Dashboard() {
  return (
    <div className="max-w-6xl mx-auto">
      <div className="mb-8 border-b-2 border-[#243c84] pb-4">
        <h3 className="text-[#243c84] font-semibold text-sm mb-2">Admin Dashboard</h3>
        <h1 className="text-3xl font-bold text-[#243c84] mb-3">ยินดีต้อนรับสู่ระบบจัดการหลังบ้าน</h1>
        <p className="text-gray-500 text-sm">เลือกระบบที่คุณต้องการจัดการจากเมนูด้านซ้ายมือ</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        {/* Card 1 */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-8 flex flex-col items-center text-center transition-shadow hover:shadow-md h-full">
          <i className="fas fa-users text-5xl text-[#ffc107] mb-4"></i>
          <h2 className="text-xl font-bold text-gray-800 mb-2">ข้อมูลผู้สมัคร/ผู้อบรม</h2>
          <p className="text-gray-500 text-xs mb-6 flex-1">ดูรายชื่อผู้สมัคร ดูรายละเอียด แก้ไข ลบ และดาวน์โหลดเป็น Excel (CSV)</p>
          <Link to="/admin/trainees" className="px-6 py-2 border border-[#28a745] text-[#28a745] rounded hover:bg-[#28a745] hover:text-white transition-colors text-sm">
            จัดการข้อมูล
          </Link>
        </div>

        {/* Card 2 */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-8 flex flex-col items-center text-center transition-shadow hover:shadow-md h-full">
          <i className="fas fa-cogs text-5xl text-[#243c84] mb-4"></i>
          <h2 className="text-xl font-bold text-gray-800 mb-2">การตั้งค่าระบบ (System Config)</h2>
          <p className="text-gray-500 text-xs mb-6 flex-1">ตั้งค่าโหมดการทำงาน เปิด-ปิดระบบ และค่าเริ่มต้นของแบบฟอร์ม</p>
          <Link to="/admin/config" className="px-6 py-2 border border-[#007bff] text-[#007bff] rounded hover:bg-[#007bff] hover:text-white transition-colors text-sm">
            ไปที่หน้าการตั้งค่า
          </Link>
        </div>

        {/* Card 3 */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-8 flex flex-col items-center text-center transition-shadow hover:shadow-md h-full">
          <i className="fas fa-database text-5xl text-[#ffc107] mb-4"></i>
          <h2 className="text-xl font-bold text-gray-800 mb-2">จัดการข้อมูล (Master Data)</h2>
          <p className="text-gray-500 text-xs mb-6 flex-1">ระบบจัดการข้อมูลคำนำหน้าชื่อ และข้อมูลพื้นฐานอื่นๆ</p>
          <Link to="/admin/master-data" className="px-6 py-2 border border-[#ffc107] text-[#ffc107] rounded hover:bg-[#ffc107] hover:text-white transition-colors text-sm">
            เข้าสู่ระบบจัดการข้อมูล
          </Link>
        </div>

        {/* Card 4 - Audit Logs */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-8 flex flex-col items-center text-center transition-shadow hover:shadow-md h-full">
          <i className="fas fa-history text-5xl text-purple-600 mb-4"></i>
          <h2 className="text-xl font-bold text-gray-800 mb-2">ประวัติการแก้ไข (Audit Logs)</h2>
          <p className="text-gray-500 text-xs mb-6 flex-1">ตรวจสอบประวัติการเพิ่ม แก้ไข ลบข้อมูลจากผู้ดูแลระบบและผู้สมัคร</p>
          <Link to="/admin/audit-logs" className="px-6 py-2 border border-purple-600 text-purple-600 rounded hover:bg-purple-600 hover:text-white transition-colors text-sm">
            ดูประวัติการแก้ไข
          </Link>
        </div>

        {/* Card 5 - System Logs */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-8 flex flex-col items-center text-center transition-shadow hover:shadow-md h-full">
          <i className="fas fa-terminal text-5xl text-gray-700 mb-4"></i>
          <h2 className="text-xl font-bold text-gray-800 mb-2">บันทึกระบบ (System Logs)</h2>
          <p className="text-gray-500 text-xs mb-6 flex-1">ตรวจสอบ Text Log และ Error ของระบบ (Serilog)</p>
          <Link to="/admin/system-logs" className="px-6 py-2 border border-gray-700 text-gray-700 rounded hover:bg-gray-700 hover:text-white transition-colors text-sm">
            ดู Log ระบบ
          </Link>
        </div>

      </div>
    </div>
  );
}
