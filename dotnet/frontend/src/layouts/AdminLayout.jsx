import { Outlet, NavLink, Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import ErrorBoundary from '../components/ErrorBoundary';

export default function AdminLayout() {
  const { user, canViewMenu, logout } = useAuth();
  const navigate = useNavigate();

  const navLinkClass = ({ isActive }) =>
    `flex items-center gap-3 px-4 py-3 border-l-4 transition-colors ${
      isActive
        ? 'bg-[#ffc107] text-[#003B6F] border-[#003B6F] font-semibold'
        : 'text-white border-transparent hover:bg-white/10 hover:border-white/50'
    }`;

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

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
            <span className="font-bold text-lg">{user?.fullName || user?.username || 'ผู้ดูแลระบบ'}</span>
            <span className="text-xs bg-[#ffc107] text-[#003B6F] font-bold px-2.5 py-0.5 rounded-full mt-1">
              {user?.role || 'Admin'}
            </span>
          </div>
        </div>

        <nav className="flex-1 py-4 flex flex-col gap-1 text-[15px]">
          {user?.role === 'Applicant' ? (
            <>
              <NavLink to="/admin/trainees" className={navLinkClass}>
                <i className="fas fa-history w-5 text-center"></i> ข้อมูลผู้สมัครของฉัน
              </NavLink>
              <NavLink to="/admin/profile" className={navLinkClass}>
                <i className="fas fa-id-card w-5 text-center"></i> ข้อมูลส่วนตัว
              </NavLink>
            </>
          ) : (
            <>
              {canViewMenu('dashboard') && (
                <NavLink to="/admin" end className={navLinkClass}>
                  <i className="fas fa-home w-5 text-center"></i> หน้าแรก (Dashboard)
                </NavLink>
              )}

              <NavLink to="/admin/profile" className={navLinkClass}>
                <i className="fas fa-id-card w-5 text-center"></i> ข้อมูลส่วนตัว (My Profile)
              </NavLink>

              {canViewMenu('trainees') && (
                <NavLink to="/admin/trainees" className={navLinkClass}>
                  <i className="fas fa-users w-5 text-center"></i> ข้อมูลผู้สมัคร/ผู้อบรม
                </NavLink>
              )}

              <NavLink to="/admin/training-import" className={navLinkClass}>
                <i className="fas fa-file-import w-5 text-center text-[#ffc107]"></i> นำเข้าผลการอบรม (Stamp ผล)
              </NavLink>

              {canViewMenu('reports') && (
                <NavLink to="/admin/reports" className={navLinkClass}>
                  <i className="fas fa-file-excel w-5 text-center"></i> รายงาน (Reports)
                </NavLink>
              )}

              {canViewMenu('users') && (
                <NavLink to="/admin/users" className={navLinkClass}>
                  <i className="fas fa-user-cog w-5 text-center"></i> จัดการผู้ใช้งาน & สิทธิ์
                </NavLink>
              )}

              {canViewMenu('config') && (
                <NavLink to="/admin/config" className={navLinkClass}>
                  <i className="fas fa-cogs w-5 text-center"></i> ตั้งค่าระบบ (System Config)
                </NavLink>
              )}

              {canViewMenu('master_data') && (
                <NavLink to="/admin/master-data" className={navLinkClass}>
                  <i className="fas fa-database w-5 text-center"></i> จัดการข้อมูล (Master Data)
                </NavLink>
              )}

              {canViewMenu('audit_logs') && (
                <NavLink to="/admin/audit-logs" className={navLinkClass}>
                  <i className="fas fa-history w-5 text-center"></i> ประวัติการแก้ไข (Audit Logs)
                </NavLink>
              )}

              {canViewMenu('system_logs') && (
                <NavLink to="/admin/system-logs" className={navLinkClass}>
                  <i className="fas fa-terminal w-5 text-center"></i> บันทึกระบบ (System Logs)
                </NavLink>
              )}
            </>
          )}
        </nav>

        <div className="p-4 border-t border-white/20 mt-auto">
          <button 
            onClick={handleLogout}
            className="w-full flex items-center justify-center gap-2 px-4 py-2 border border-white/50 rounded hover:bg-white/10 hover:border-white transition text-white"
          >
            <i className="fas fa-sign-out-alt"></i> ออกจากระบบ
          </button>
        </div>
      </aside>

      {/* Main Content */}
      <main className="flex-1 p-8 w-full overflow-y-auto h-screen">
        <ErrorBoundary name="Admin Main Page">
          <Outlet />
        </ErrorBoundary>
      </main>
    </div>
  );
}
