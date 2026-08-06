import { Outlet } from 'react-router-dom';
import { useRegistration } from '../context/RegistrationContext';
import SystemClosed from '../pages/public/SystemClosed';

export default function PublicLayout() {
  const { sysConfig } = useRegistration();

  // Check system status
  let isSystemOpen = true;
  
  if (sysConfig) {
    if (sysConfig['SYSTEM_IS_ONLINE'] === 'false') {
      isSystemOpen = false;
    } else if (sysConfig['SYSTEM_OPEN_PERIODS']) {
      try {
        const periods = JSON.parse(sysConfig['SYSTEM_OPEN_PERIODS']);
        if (Array.isArray(periods) && periods.length > 0) {
          const now = new Date();
          let withinPeriod = false;
          
          for (const period of periods) {
            const openTime = period.open ? new Date(period.open) : null;
            const closeTime = period.close ? new Date(period.close) : null;
            
            if (openTime && closeTime) {
              if (now >= openTime && now <= closeTime) withinPeriod = true;
            } else if (openTime && !closeTime) {
              if (now >= openTime) withinPeriod = true;
            } else if (!openTime && closeTime) {
              if (now <= closeTime) withinPeriod = true;
            }
          }
          
          isSystemOpen = withinPeriod;
        }
      } catch (e) {
        console.error("Invalid SYSTEM_OPEN_PERIODS JSON", e);
      }
    }
  }

  if (!isSystemOpen) {
    return <SystemClosed />;
  }

  return (
    <div className="min-h-screen flex flex-col">
      <header className="relative bg-gradient-to-br from-primary to-[#003B6F] text-white py-10 px-5 text-center shadow-md border-b-[5px] border-secondary">
        <div className="absolute left-8 top-1/2 -translate-y-1/2 max-h-[60px] bg-white px-4 py-2 rounded-lg shadow-lg hidden sm:block">
          <img src="/logo.jpg" alt="V Online Learning" className="h-[40px] w-auto" />
        </div>
        <h1 className="text-[28px] font-semibold mb-2">ลงทะเบียนอบรมวิริยะประกันภัย</h1>
        <p className="font-light opacity-90">ฟอร์มลงทะเบียนตัวแทนและนายหน้า (ระบบออนไลน์)</p>
      </header>
      
      <main className="flex-1">
        <div className="max-w-[1100px] mx-auto -mt-[30px] mb-[50px] bg-white p-6 md:p-10 rounded-xl shadow-[0_10px_30px_rgba(0,0,0,0.08)] relative z-10">
          <Outlet />
        </div>
      </main>

      <footer className="text-center py-6 text-textMuted text-sm">
        &copy; {new Date().getFullYear()} Viriyah Insurance. All rights reserved.
      </footer>
    </div>
  );
}
