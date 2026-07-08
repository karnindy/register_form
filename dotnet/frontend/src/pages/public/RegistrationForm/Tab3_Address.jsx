import { useRegistration } from '../../../context/RegistrationContext';
import Button from '../../../components/Button';
import Input from '../../../components/Input';
import Select from '../../../components/Select';

export default function Tab3Address() {
  const { nextStep, prevStep, formData, updateData, masterData } = useRegistration();

  const handleChange = (e) => {
    updateData({ [e.target.id]: e.target.value });
  };

  const provinceOptions = masterData.provinces.map(p => ({ value: p.provinceId, label: p.provinceThai })) || [];

  return (
    <div className="animate-[fadeIn_0.5s]">
      <h3 className="text-xl font-semibold mb-6 text-primary border-b pb-2">ส่วนที่ 3: ที่อยู่ (Address)</h3>
      
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <Input label="บ้านเลขที่" id="houseNo" required value={formData.houseNo || ''} onChange={handleChange} />
        <Input label="หมู่" id="moo" value={formData.moo || ''} onChange={handleChange} />
        <Input label="หมู่บ้าน/อาคาร/ชั้น" id="village" value={formData.village || ''} onChange={handleChange} />
        <Input label="ซอย" id="soi" value={formData.soi || ''} onChange={handleChange} />
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
        <Input label="ถนน" id="road" value={formData.road || ''} onChange={handleChange} />
        <Select label="จังหวัด" id="province" required options={provinceOptions} value={formData.province || ''} onChange={handleChange} />
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
        <Select label="อำเภอ/เขต" id="district" required options={[]} value={formData.district || ''} onChange={handleChange} />
        <Select label="ตำบล/แขวง" id="subDistrict" required options={[]} value={formData.subDistrict || ''} onChange={handleChange} />
        <Input label="รหัสไปรษณีย์" id="zipcode" required value={formData.zipcode || ''} onChange={handleChange} />
      </div>

      <div className="mt-8 mb-4 flex items-center gap-3">
        <input 
          type="checkbox" 
          id="sameAddress" 
          className="w-5 h-5 accent-primary cursor-pointer"
          checked={formData.sameAddress || false}
          onChange={(e) => updateData({ sameAddress: e.target.checked })}
        />
        <label htmlFor="sameAddress" className="font-semibold cursor-pointer text-primary">
          ที่อยู่สำหรับจัดส่งเอกสารเหมือนกับที่อยู่ตามทะเบียนบ้าน
        </label>
      </div>

      {!formData.sameAddress && (
        <div className="p-4 border border-border bg-gray-50 rounded-lg">
          <h4 className="font-semibold mb-4 text-textMain">ที่อยู่สำหรับจัดส่งเอกสาร</h4>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <Input label="บ้านเลขที่" id="shipHouseNo" required value={formData.shipHouseNo || ''} onChange={handleChange} />
            <Input label="หมู่" id="shipMoo" value={formData.shipMoo || ''} onChange={handleChange} />
            <Input label="หมู่บ้าน/อาคาร" id="shipVillage" value={formData.shipVillage || ''} onChange={handleChange} />
            <Input label="ซอย" id="shipSoi" value={formData.shipSoi || ''} onChange={handleChange} />
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
            <Input label="ถนน" id="shipRoad" value={formData.shipRoad || ''} onChange={handleChange} />
            <Select label="จังหวัด" id="shipProvince" required options={[]} value={formData.shipProvince || ''} onChange={handleChange} />
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
            <Select label="อำเภอ/เขต" id="shipDistrict" required options={[]} value={formData.shipDistrict || ''} onChange={handleChange} />
            <Select label="ตำบล/แขวง" id="shipSubDistrict" required options={[]} value={formData.shipSubDistrict || ''} onChange={handleChange} />
            <Input label="รหัสไปรษณีย์" id="shipZipcode" required value={formData.shipZipcode || ''} onChange={handleChange} />
          </div>
        </div>
      )}

      <div className="flex justify-between mt-10 pt-5 border-t border-border">
        <Button variant="secondary" onClick={prevStep}><i className="fas fa-arrow-left"></i> ย้อนกลับ</Button>
        <Button onClick={nextStep}>ถัดไป <i className="fas fa-arrow-right"></i></Button>
      </div>
    </div>
  );
}
