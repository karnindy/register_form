import { createContext, useContext, useState, useEffect } from 'react';

const RegistrationContext = createContext();

export function RegistrationProvider({ children }) {
  const [currentStep, setCurrentStep] = useState(1);
  const [masterData, setMasterData] = useState({
    provinces: [],
    titles: [],
    blood: [],
    gender: [],
    religion: []
  });
  const [formData, setFormData] = useState({
    pdpaConsent: false,
    idCard: '',
    firstNameTh: '',
    lastNameTh: '',
    // ... other fields
  });

  useEffect(() => {
    // Fetch master data on load
    const fetchMasterData = async () => {
      try {
        const [provRes, titlesRes, bloodRes, genderRes, religionRes] = await Promise.all([
          fetch('http://localhost:8085/api/masterdata/provinces'),
          fetch('http://localhost:8085/api/masterdata/titles'),
          fetch('http://localhost:8085/api/masterdata/blood'),
          fetch('http://localhost:8085/api/masterdata/gender'),
          fetch('http://localhost:8085/api/masterdata/religion')
        ]);
        
        const provinces = provRes.ok ? await provRes.json() : [];
        const titles = titlesRes.ok ? await titlesRes.json() : [];
        const blood = bloodRes.ok ? await bloodRes.json() : [];
        const gender = genderRes.ok ? await genderRes.json() : [];
        const religion = religionRes.ok ? await religionRes.json() : [];
        
        setMasterData(prev => ({ ...prev, provinces, titles, blood, gender, religion }));
      } catch (err) {
        console.error("Failed to load master data", err);
      }
    };
    
    fetchMasterData();
  }, []);

  const nextStep = () => setCurrentStep((prev) => Math.min(prev + 1, 6));
  const prevStep = () => setCurrentStep((prev) => Math.max(prev - 1, 1));
  
  const updateData = (newData) => {
    setFormData((prev) => ({ ...prev, ...newData }));
  };

  return (
    <RegistrationContext.Provider value={{ currentStep, formData, updateData, nextStep, prevStep, setStep: setCurrentStep, masterData }}>
      {children}
    </RegistrationContext.Provider>
  );
}

export function useRegistration() {
  return useContext(RegistrationContext);
}
