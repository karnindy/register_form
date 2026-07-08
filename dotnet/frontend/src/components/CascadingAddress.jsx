import { useState, useEffect } from 'react';
import Select from 'react-select';
import { useRegistration } from '../context/RegistrationContext';

export default function CascadingAddress({ prefix = "" }) {
  const { formData, updateData, masterData } = useRegistration();

  const [districts, setDistricts] = useState([]);
  const [subDistricts, setSubDistricts] = useState([]);
  const [loadingDistricts, setLoadingDistricts] = useState(false);
  const [loadingSubDistricts, setLoadingSubDistricts] = useState(false);

  const provinceField = `${prefix}province`;
  const districtField = `${prefix}district`;
  const subDistrictField = `${prefix}subDistrict`;
  const zipcodeField = `${prefix}zipcode`;

  const provinceOptions = masterData.provinces?.map(p => ({ 
    value: p.provinceId, 
    label: p.provinceThai 
  })) || [];

  // Fetch districts when province changes
  useEffect(() => {
    const provinceId = formData[`${provinceField}Id`];
    if (provinceId) {
      setLoadingDistricts(true);
      fetch(`http://localhost:8085/api/masterdata/districts/${provinceId}`)
        .then(res => res.json())
        .then(data => {
          setDistricts(data.map(d => ({ value: d.districtId, label: d.districtThai })));
          setLoadingDistricts(false);
        })
        .catch(() => setLoadingDistricts(false));
    } else {
      setDistricts([]);
    }
  }, [formData[`${provinceField}Id`]]);

  // Fetch sub-districts when district changes
  useEffect(() => {
    const districtId = formData[`${districtField}Id`];
    if (districtId) {
      setLoadingSubDistricts(true);
      fetch(`http://localhost:8085/api/masterdata/subdistricts/${districtId}`)
        .then(res => res.json())
        .then(data => {
          setSubDistricts(data.map(s => ({ value: s.subDistrictId, label: s.subDistrictThai, zipcode: s.zipcode })));
          setLoadingSubDistricts(false);
        })
        .catch(() => setLoadingSubDistricts(false));
    } else {
      setSubDistricts([]);
    }
  }, [formData[`${districtField}Id`]]);

  const handleProvinceChange = (selected) => {
    updateData({
      [provinceField]: selected ? selected.label : '',
      [`${provinceField}Id`]: selected ? selected.value : null,
      [districtField]: '',
      [`${districtField}Id`]: null,
      [subDistrictField]: '',
      [`${subDistrictField}Id`]: null,
      [zipcodeField]: ''
    });
  };

  const handleDistrictChange = (selected) => {
    updateData({
      [districtField]: selected ? selected.label : '',
      [`${districtField}Id`]: selected ? selected.value : null,
      [subDistrictField]: '',
      [`${subDistrictField}Id`]: null,
      [zipcodeField]: ''
    });
  };

  const handleSubDistrictChange = (selected) => {
    updateData({
      [subDistrictField]: selected ? selected.label : '',
      [`${subDistrictField}Id`]: selected ? selected.value : null,
      [zipcodeField]: selected ? selected.zipcode : ''
    });
  };

  const customStyles = {
    control: (provided, state) => ({
      ...provided,
      padding: '0.4rem',
      borderRadius: '0.375rem',
      borderColor: state.isFocused ? '#1d4ed8' : '#e5e7eb',
      boxShadow: state.isFocused ? '0 0 0 4px rgba(29, 78, 216, 0.1)' : 'none',
      '&:hover': {
        borderColor: '#1d4ed8'
      }
    }),
    option: (provided, state) => ({
      ...provided,
      fontFamily: 'Sarabun, sans-serif'
    }),
    singleValue: (provided) => ({
      ...provided,
      fontFamily: 'Sarabun, sans-serif'
    })
  };

  return (
    <>
      <div className="mb-5">
        <label className="block mb-2 font-medium text-textMain after:content-['_*'] after:text-error">จังหวัด</label>
        <Select
          options={provinceOptions}
          value={provinceOptions.find(o => o.value === formData[`${provinceField}Id`]) || null}
          onChange={handleProvinceChange}
          placeholder="-- ค้นหา/เลือกจังหวัด --"
          isClearable
          styles={customStyles}
        />
      </div>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
        <div className="mb-5">
          <label className="block mb-2 font-medium text-textMain after:content-['_*'] after:text-error">อำเภอ/เขต</label>
          <Select
            options={districts}
            value={districts.find(o => o.value === formData[`${districtField}Id`]) || null}
            onChange={handleDistrictChange}
            placeholder="-- ค้นหา/เลือกอำเภอ --"
            isDisabled={!formData[`${provinceField}Id`]}
            isLoading={loadingDistricts}
            isClearable
            styles={customStyles}
          />
        </div>
        <div className="mb-5">
          <label className="block mb-2 font-medium text-textMain after:content-['_*'] after:text-error">ตำบล/แขวง</label>
          <Select
            options={subDistricts}
            value={subDistricts.find(o => o.value === formData[`${subDistrictField}Id`]) || null}
            onChange={handleSubDistrictChange}
            placeholder="-- ค้นหา/เลือกตำบล --"
            isDisabled={!formData[`${districtField}Id`]}
            isLoading={loadingSubDistricts}
            isClearable
            styles={customStyles}
          />
        </div>
        <div className="mb-5">
          <label className="block mb-2 font-medium text-textMain after:content-['_*'] after:text-error">รหัสไปรษณีย์</label>
          <input
            id={zipcodeField}
            type="text"
            readOnly
            className="w-full px-4 py-3 border rounded-md font-sarabun text-[15px] transition-colors border-border bg-gray-50 focus:outline-none"
            value={formData[zipcodeField] || ''}
          />
        </div>
      </div>
    </>
  );
}
