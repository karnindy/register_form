import React from 'react';

export default function SystemClosed() {
  return (
    <div className="min-h-screen bg-gray-100 flex flex-col justify-center items-center p-4 font-sans">
      <div className="bg-white rounded-lg shadow-xl p-8 max-w-md w-full text-center animate-[fadeIn_0.5s]">
        <div className="text-error mb-4">
          <i className="fas fa-tools text-6xl"></i>
        </div>
        <h1 className="text-2xl font-bold text-gray-800 mb-2">ระบบปิดให้บริการชั่วคราว</h1>
        <p className="text-gray-600 mb-6">
          ขออภัยในความไม่สะดวก ขณะนี้ระบบอยู่นอกช่วงเวลาให้บริการ หรือกำลังอยู่ระหว่างการปรับปรุง กรุณาทำรายการใหม่อีกครั้งในภายหลัง
        </p>
        <div className="bg-gray-50 border border-gray-200 rounded p-4 text-sm text-gray-500">
          สอบถามข้อมูลเพิ่มเติม กรุณาติดต่อฝ่ายสนับสนุน หรือตัวแทน/นายหน้าที่ท่านสังกัด
        </div>
      </div>
    </div>
  );
}
