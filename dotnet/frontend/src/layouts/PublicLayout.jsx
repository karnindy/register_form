import { Outlet } from 'react-router-dom';

export default function PublicLayout() {
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
        <div className="max-w-[800px] mx-auto -mt-[30px] mb-[50px] bg-white p-10 rounded-xl shadow-[0_10px_30px_rgba(0,0,0,0.08)] relative z-10">
          <Outlet />
        </div>
      </main>

      <footer className="text-center py-6 text-textMuted text-sm">
        &copy; {new Date().getFullYear()} Viriyah Insurance. All rights reserved.
      </footer>
    </div>
  );
}
