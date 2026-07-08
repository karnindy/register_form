import { Outlet, NavLink, Link } from 'react-router-dom';

export default function AdminLayout() {
  const navLinkClass = ({ isActive }) =>
    `flex items-center gap-3 px-4 py-3 border-l-4 transition-colors ${
      isActive
        ? 'bg-[#ffc107] text-[#003B6F] border-[#003B6F] font-semibold'
        : 'text-white border-transparent hover:bg-white/10 hover:border-white/50'
    }`;

  return (
    <div className="min-h-screen flex bg-[#f8f9fa] font-sans">
      {/* Sidebar */}
      <aside className="w-[260px] bg-[#243c84] text-white flex flex-col shrink-0 shadow-xl z-10 min-h-screen relative">
        <div className="p-4 border-b border-white/20 text-center flex flex-col items-center">
          <div className="bg-white rounded-md p-2 mb-4 w-4/5 mx-auto flex items-center justify-center">
             <img src="/logo.jpg" alt="Logo" className="max-h-[40px] object-contain" />
          </div>
          <div className="flex flex-col items-center">
            <i className="fas fa-user-shield text-3xl text-[#ffc107] mb-2"></i>
            <span className="font-bold text-lg">ผู้ดูแลระบบ</span>
            <span className="text-xs text-gray-300">วิริยะประกันภัย</span>
          </div>
        </div>

        <nav className="flex-1 py-4 flex flex-col gap-1 text-[15px]">
          <NavLink to="/admin" end className={navLinkClass}>
            <i className="fas fa-home w-5 text-center"></i> หน้าแรก (Dashboard)
          </NavLink>
          <NavLink to="/admin/trainees" className={navLinkClass}>
            <i className="fas fa-users w-5 text-center"></i> ข้อมูลผู้สมัคร/ผู้อบรม
          </NavLink>
          <NavLink to="/admin/reports" className={navLinkClass}>
            <i className="fas fa-file-excel w-5 text-center"></i> รายงาน (Reports)
          </NavLink>
          <NavLink to="/admin/config" className={navLinkClass}>
            <i className="fas fa-cogs w-5 text-center"></i> ตั้งค่าระบบ (System Config)
          </NavLink>
          <NavLink to="/admin/master-data" className={navLinkClass}>
            <i className="fas fa-database w-5 text-center"></i> จัดการข้อมูล (Master Data)
          </NavLink>
          <NavLink to="/admin/audit-logs" className={navLinkClass}>
            <i className="fas fa-history w-5 text-center"></i> ประวัติการแก้ไข (Audit Logs)
          </NavLink>
          <NavLink to="/admin/system-logs" className={navLinkClass}>
            <i className="fas fa-terminal w-5 text-center"></i> บันทึกระบบ (System Logs)
          </NavLink>
        </nav>

        <div className="p-4 border-t border-white/20 mt-auto">
          <Link to="/" className="flex items-center justify-center gap-2 px-4 py-2 border border-white/50 rounded hover:bg-white/10 hover:border-white transition">
            <i className="fas fa-sign-out-alt"></i> ออกจากระบบ
          </Link>
        </div>
      </aside>

      {/* Main Content */}
      <main className="flex-1 p-8 w-full overflow-y-auto h-screen">
        <Outlet />
      </main>
    </div>
  );
}
