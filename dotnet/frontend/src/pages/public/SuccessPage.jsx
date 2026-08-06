import { useLocation, useNavigate } from 'react-router-dom';
import Button from '../../components/Button';

export default function SuccessPage() {
  const location = useLocation();
  const navigate = useNavigate();
  const searchParams = new URLSearchParams(location.search);
  const nationalId = searchParams.get('national_id') || '';
  const phone = searchParams.get('phone') || '';

  return (
    <div className="max-w-3xl mx-auto my-10 animate-[fadeIn_0.5s]">
      <div className="bg-white rounded-lg shadow-xl overflow-hidden">


        <div className="p-8">
          <div className="bg-[#1C3672] text-white py-4 px-6 rounded-md mb-8 shadow-sm">
            <h2 className="text-lg font-semibold text-center">ขั้นตอนถัดไป — กรุณาดำเนินการตามลำดับด้านล่าง</h2>
            <p className="text-center text-sm mt-1 text-blue-100">ระบบได้รับข้อมูลการลงทะเบียนของท่านเรียบร้อยแล้ว</p>
          </div>

          <div className="space-y-6">
            {/* Step 1 */}
            <div className="flex bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
              <div className="bg-[#3B66D6] text-white w-16 flex items-center justify-center text-2xl font-bold">1</div>
              <div className="p-5 flex-1 flex flex-col justify-center">
                <div className="flex items-center gap-3 mb-2">
                  <div className="w-10 h-10 rounded-full bg-blue-100 text-[#3B66D6] flex items-center justify-center text-lg">
                    <i className="fas fa-cloud-upload-alt"></i>
                  </div>
                  <div>
                    <h3 className="font-bold text-gray-800">อัปโหลดภาพประกอบการลงทะเบียน</h3>
                    <p className="text-sm text-gray-500">กรุณาอัปโหลดภาพเอกสารประกอบการลงทะเบียน เช่น รูปถ่ายหน้าบัตรประชาชน, ใบอนุญาต เป็นต้น</p>
                  </div>
                </div>
                <div className="mt-4 flex">
                  <Button 
                    onClick={() => navigate(`/upload?national_id=${nationalId}&phone=${phone}`)}
                    className="!py-2 !px-4 text-sm"
                  >
                    <i className="fas fa-arrow-right mr-2"></i> ไปอัปโหลดเอกสาร
                  </Button>
                </div>
              </div>
            </div>

            {/* Step 2 */}
            <div className="flex bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
              <div className="bg-[#6B7FD7] text-white w-16 flex items-center justify-center text-2xl font-bold">2</div>
              <div className="p-5 flex-1 flex flex-col justify-center">
                <div className="flex items-center gap-3 mb-2">
                  <div className="w-10 h-10 rounded-full bg-indigo-50 text-[#6B7FD7] flex items-center justify-center text-lg">
                    <i className="fas fa-search"></i>
                  </div>
                  <div>
                    <h3 className="font-bold text-gray-800">ตรวจสอบข้อมูลที่ลงทะเบียน</h3>
                    <p className="text-sm text-gray-500">ท่านสามารถตรวจสอบข้อมูลที่กรอกไว้ได้ทุกเมื่อต้องการ หากพบข้อผิดพลาดกรุณาติดต่อเจ้าหน้าที่</p>
                  </div>
                </div>
                <div className="mt-4 flex">
                  <button 
                    onClick={() => navigate('/enquiry')}
                    className="px-4 py-2 border border-[#6B7FD7] text-[#6B7FD7] hover:bg-indigo-50 rounded-md text-sm font-medium transition-colors flex items-center"
                  >
                    <i className="fas fa-list-ul mr-2"></i> ตรวจสอบข้อมูล
                  </button>
                </div>
              </div>
            </div>

            {/* Step 3 */}
            <div className="flex bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
              <div className="bg-[#5C9055] text-white w-16 flex items-center justify-center text-2xl font-bold">3</div>
              <div className="p-5 flex-1 flex flex-col justify-center">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-full bg-green-50 text-[#5C9055] flex items-center justify-center text-lg">
                    <i className="fas fa-heart"></i>
                  </div>
                  <div>
                    <h3 className="font-bold text-gray-800">ขอบคุณที่ให้ความไว้วางใจ</h3>
                    <p className="text-sm text-gray-500">ศูนย์ฝึกอบรมและพัฒนานักประกันภัย บมจ.วิริยะประกันภัย ขอขอบคุณเป็นอย่างสูงในความร่วมมือและยินดีต้อนรับท่านในการอบรม</p>
                  </div>
                </div>
              </div>
            </div>
            
          </div>
        </div>
      </div>
    </div>
  );
}
