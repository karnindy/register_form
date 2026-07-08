import { Link } from 'react-router-dom';

export default function MasterDataDashboard() {
  const masterDataLinks = [
    { type: 'titles', title: 'จัดการคำนำหน้าชื่อ', icon: 'fa-list', color: 'text-primary' },
    { type: 'religion', title: 'จัดการศาสนา', icon: 'fa-praying-hands', color: 'text-primary' },
    { type: 'gender', title: 'จัดการเพศ', icon: 'fa-venus-mars', color: 'text-primary' },
    { type: 'blood', title: 'จัดการกรุ๊ปเลือด', icon: 'fa-droplet', color: 'text-primary' },
    { type: 'renewcourse', title: 'จัดการชื่อวิชา (Subjects)', icon: 'fa-book-open', color: 'text-warning' }
  ];

  return (
    <div className="max-w-6xl mx-auto">
      <div className="mb-8 border-b-2 border-primary pb-4 flex justify-between items-center">
        <div>
          <h2 className="text-3xl font-bold text-primary mb-2">จัดการข้อมูล (Master Data)</h2>
          <p className="text-gray-500 text-sm">เลือกระบบจัดการฐานข้อมูลที่คุณต้องการ</p>
        </div>
        <Link to="/admin" className="px-4 py-2 border border-gray-400 text-gray-600 rounded hover:bg-gray-100 transition-colors">
          <i className="fas fa-arrow-left mr-2"></i> กลับหน้าหลัก
        </Link>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        {masterDataLinks.map((link) => (
          <div key={link.type} className="bg-white rounded-lg shadow-sm border border-gray-200 p-8 flex flex-col items-center text-center transition-shadow hover:shadow-md h-full">
            <i className={`fas ${link.icon} text-5xl ${link.color} mb-4`}></i>
            <h4 className="text-lg font-bold text-gray-800 mb-2">{link.title}</h4>
            <p className="text-gray-500 text-xs mb-6 flex-1">เพิ่ม ลบ หรือแก้ไขข้อมูลในระบบ</p>
            <Link to={`/admin/master-data/${link.type}`} className="px-6 py-2 border border-primary text-primary rounded hover:bg-primary hover:text-white transition-colors text-sm w-full">
              จัดการข้อมูล
            </Link>
          </div>
        ))}
      </div>
    </div>
  );
}
