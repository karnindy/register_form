import { useState, useEffect } from 'react';

export default function ConfigPage() {
  const [configs, setConfigs] = useState({});
  const [defaultConfigs, setDefaultConfigs] = useState({});
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [saveMessage, setSaveMessage] = useState('');

  useEffect(() => {
    const fetchConfigs = async () => {
      try {
        const response = await fetch('http://localhost:8085/api/config');
        if (response.ok) {
          const data = await response.json();
          const mapped = {};
          const defaults = {};
          data.forEach(item => {
            mapped[item.key] = item.value;
            defaults[item.key] = item.defaultValue;
          });
          setConfigs(mapped);
          setDefaultConfigs(defaults);
        }
      } catch (error) {
        console.error("Failed to fetch configs", error);
      } finally {
        setIsLoading(false);
      }
    };
    fetchConfigs();
  }, []);

  const handleChange = (key, value) => {
    setConfigs(prev => ({ ...prev, [key]: value }));
  };

  const handleSave = async () => {
    setIsSaving(true);
    setSaveMessage('');
    try {
      const response = await fetch('http://localhost:8085/api/config', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(configs)
      });
      if (response.ok) {
        setSaveMessage('บันทึกการตั้งค่าสำเร็จ');
        setTimeout(() => setSaveMessage(''), 3000);
      } else {
        setSaveMessage('เกิดข้อผิดพลาดในการบันทึก');
      }
    } catch (error) {
      console.error("Failed to save configs", error);
      setSaveMessage('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    } finally {
      setIsSaving(false);
    }
  };

  if (isLoading) {
    return <div className="p-8 text-center text-gray-500"><i className="fas fa-spinner fa-spin text-2xl mb-2"></i><br/>กำลังโหลดการตั้งค่า...</div>;
  }

  return (
    <div className="bg-white rounded-lg shadow p-6">
      <h2 className="text-2xl font-bold text-primary mb-6"><i className="fas fa-cogs mr-2"></i> ตั้งค่าระบบ (System Configuration)</h2>
      
      {saveMessage && (
        <div className={`p-4 mb-6 rounded-md ${saveMessage.includes('สำเร็จ') ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'}`}>
          {saveMessage}
        </div>
      )}

      <div className="space-y-8">
        <section className="border border-border rounded-md p-5 bg-gray-50">
          <h3 className="text-lg font-semibold text-textMain mb-4 border-b pb-2"><i className="fas fa-server mr-2 text-primary"></i> การตั้งค่าทั่วไปของระบบ (System Settings)</h3>
          
          <div className="space-y-6">
            <div>
              <label className="block text-sm font-medium text-textMain mb-1">โหมดการทำงาน (APP_ENV)</label>
              <select 
                className="w-full md:w-1/2 px-3 py-2 border border-border rounded-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                value={configs['APP_ENV'] || 'prd'}
                onChange={(e) => handleChange('APP_ENV', e.target.value)}
              >
                <option value="prd">Production (prd) - ข้อมูลจริง</option>
                <option value="uat">UAT - ระบบทดสอบ</option>
                <option value="dev">Development (dev) - สำหรับนักพัฒนา</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-textMain mb-1">Session Timeout (วินาที)</label>
              <input 
                type="number" 
                className="w-full md:w-1/2 px-3 py-2 border border-border rounded-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                value={configs['SESSION_TIMEOUT'] || '3600'}
                onChange={(e) => handleChange('SESSION_TIMEOUT', e.target.value)}
              />
              <p className="text-sm text-gray-500 mt-1">เช่น 3600 คือ 1 ชั่วโมง</p>
            </div>

            <div className="border-t border-gray-200 pt-4">
              <label className="flex items-center gap-3 cursor-pointer">
                <input 
                  type="checkbox" 
                  className="w-5 h-5 accent-primary"
                  checked={configs['SYSTEM_IS_ONLINE'] === 'true'}
                  onChange={(e) => handleChange('SYSTEM_IS_ONLINE', e.target.checked ? 'true' : 'false')}
                />
                <span className="font-medium text-textMain">เปิดใช้งานระบบ (SYSTEM_IS_ONLINE)</span>
              </label>
              <p className="text-sm text-gray-500 mt-1 ml-8">หากไม่ติ๊กเลือก ผู้ใช้งานจะไม่สามารถเข้าแบบฟอร์มลงทะเบียนได้เลย และจะเห็นหน้าเว็บปิดปรับปรุง</p>
            </div>

            <div className="border-t border-gray-200 pt-4">
              <label className="block text-sm font-medium text-textMain mb-1">ช่วงเวลาเปิดระบบ (SYSTEM_OPEN_PERIODS)</label>
              <textarea 
                className="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary font-mono text-sm"
                rows="6"
                value={configs['SYSTEM_OPEN_PERIODS'] || '[]'}
                onChange={(e) => handleChange('SYSTEM_OPEN_PERIODS', e.target.value)}
              ></textarea>
              <p className="text-sm text-gray-500 mt-1">รูปแบบ JSON Array ตัวอย่าง: <code>[&#123;"open": "2026-06-01 08:00:00", "close": "2026-06-15 23:59:59"&#125;]</code> (ปล่อยเป็น <code>[]</code> ถ้าให้เปิดตลอดเวลา)</p>
            </div>
          </div>
        </section>

        <section className="border border-border rounded-md p-5 bg-gray-50">
          <h3 className="text-lg font-semibold text-textMain mb-4 border-b pb-2"><i className="fas fa-id-card mr-2 text-primary"></i> ตั้งค่าส่วนที่ 4. ข้อมูลใบอนุญาตตัวแทน/นายหน้า</h3>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div className="col-span-full">
              <label className="block text-sm font-medium text-textMain mb-1">ประเภทใบอนุญาตที่เปิดรับสมัคร</label>
              <select 
                className="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                value={configs['tab4_allowed_agent_types'] || 'both'}
                onChange={(e) => handleChange('tab4_allowed_agent_types', e.target.value)}
              >
                <option value="both">แสดงทั้งคู่ (เลือกได้อย่างใดอย่างหนึ่ง)</option>
                <option value="agent">บังคับเลือกเฉพาะ ตัวแทนประกันวินาศภัย</option>
                <option value="broker">บังคับเลือกเฉพาะ นายหน้าประกันวินาศภัย</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-textMain mb-1">ข้อความหัวข้อ สาขา</label>
              <input 
                type="text" 
                className="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                value={configs['tab4_label_branch'] || ''}
                onChange={(e) => handleChange('tab4_label_branch', e.target.value)}
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-textMain mb-1">ข้อความแนะนำ สาขา (Hint)</label>
              <input 
                type="text" 
                className="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                value={configs['tab4_hint_branch'] || ''}
                onChange={(e) => handleChange('tab4_hint_branch', e.target.value)}
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-textMain mb-1">ค่า Default สำหรับ สาขา (ชื่อสาขา)</label>
              <input 
                type="text" 
                className="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                value={configs['tab4_default_branch'] || ''}
                onChange={(e) => handleChange('tab4_default_branch', e.target.value)}
                placeholder="เว้นว่างไว้หากไม่ต้องการ Default"
              />
            </div>
            <div className="md:col-span-1 hidden md:block"></div>

            <div>
              <label className="block text-sm font-medium text-textMain mb-1">ข้อความหัวข้อ ภาค</label>
              <input 
                type="text" 
                className="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                value={configs['tab4_label_region'] || ''}
                onChange={(e) => handleChange('tab4_label_region', e.target.value)}
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-textMain mb-1">ข้อความแนะนำ ภาค (Hint)</label>
              <input 
                type="text" 
                className="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                value={configs['tab4_hint_region'] || ''}
                onChange={(e) => handleChange('tab4_hint_region', e.target.value)}
              />
            </div>

            <div className="col-span-full border-t border-gray-200 mt-2 pt-4"></div>

            <div>
              <label className="block text-sm font-medium text-textMain mb-1">ข้อความหัวข้อ รหัสตัวแทน</label>
              <input 
                type="text" 
                className="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                value={configs['tab4_label_agentcode'] || ''}
                onChange={(e) => handleChange('tab4_label_agentcode', e.target.value)}
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-textMain mb-1">ข้อความแนะนำ รหัสตัวแทน (Hint)</label>
              <textarea 
                className="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                rows="2"
                value={configs['tab4_hint_agentcode'] || ''}
                onChange={(e) => handleChange('tab4_hint_agentcode', e.target.value)}
              ></textarea>
            </div>
            <div>
              <label className="block text-sm font-medium text-textMain mb-1">ค่า Default สำหรับ รหัสตัวแทน</label>
              <input 
                type="text" 
                className="w-full px-3 py-2 border border-border rounded-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                value={configs['tab4_default_agentcode'] || ''}
                onChange={(e) => handleChange('tab4_default_agentcode', e.target.value)}
                placeholder="เว้นว่างไว้หากไม่ต้องการ Default"
              />
            </div>

          </div>
        </section>
      </div>

      <div className="mt-8 pt-5 border-t border-border flex justify-between">
        <button 
          onClick={() => {
            if (confirm('คุณต้องการคืนค่าการตั้งค่าทั้งหมดกลับเป็นค่าที่ Backup ไว้ใช่หรือไม่?')) {
              setConfigs({...defaultConfigs});
            }
          }}
          className="px-6 py-2 rounded border border-gray-300 text-gray-700 font-medium hover:bg-gray-100 transition"
        >
          <i className="fas fa-undo mr-2"></i> คืนค่าที่ Backup (Restore)
        </button>
        <button 
          onClick={handleSave}
          disabled={isSaving}
          className={`px-6 py-2 rounded text-white font-medium transition ${isSaving ? 'bg-gray-400 cursor-not-allowed' : 'bg-primary hover:bg-secondary'}`}
        >
          {isSaving ? <><i className="fas fa-spinner fa-spin mr-2"></i> กำลังบันทึก...</> : <><i className="fas fa-save mr-2"></i> บันทึกการตั้งค่า</>}
        </button>
      </div>
    </div>
  );
}
