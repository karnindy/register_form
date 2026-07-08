import { useRegistration } from '../../../context/RegistrationContext';
import StepIndicator from '../../../components/StepIndicator';

import Tab1Consent from './Tab1_Consent';
import Tab2PersonalInfo from './Tab2_PersonalInfo';
import Tab3Address from './Tab3_Address';
import Tab4License from './Tab4_License';
import Tab5Course from './Tab5_Course';
import Tab6Confirm from './Tab6_Confirm';

export default function RegistrationForm() {
  const { currentStep } = useRegistration();

  const renderStep = () => {
    switch (currentStep) {
      case 1: return <Tab1Consent />;
      case 2: return <Tab2PersonalInfo />;
      case 3: return <Tab3Address />;
      case 4: return <Tab4License />;
      case 5: return <Tab5Course />;
      case 6: return <Tab6Confirm />;
      default: return <Tab1Consent />;
    }
  };

  return (
    <div>
      <h2 className="text-primary text-[22px] mb-[25px] pb-[10px] border-b-2 border-border flex items-center gap-[10px]">
        <i className="fas fa-file-signature"></i> แบบฟอร์มลงทะเบียน
      </h2>
      
      <StepIndicator />
      
      <div className="mt-8">
        {renderStep()}
      </div>
    </div>
  );
}
