import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import PublicLayout from './layouts/PublicLayout';
import AdminLayout from './layouts/AdminLayout';
import RegistrationForm from './pages/public/RegistrationForm';
import SuccessPage from './pages/public/SuccessPage';
import UploadPage from './pages/public/UploadPage';
import EnquiryPage from './pages/public/EnquiryPage';
import { RegistrationProvider } from './context/RegistrationContext';

// Admin Pages
import Login from './pages/admin/Login';
import Dashboard from './pages/admin/Dashboard';
import TraineesList from './pages/admin/TraineesList';
import AuditLogs from './pages/admin/AuditLogs';
import SystemLogs from './pages/admin/SystemLogs';
import ConfigPage from './pages/admin/ConfigPage';

import MasterDataDashboard from './pages/admin/MasterDataDashboard';
import MasterDataCrud from './pages/admin/MasterDataCrud';
import LocationMasterCrud from './pages/admin/LocationMasterCrud';
import AgentMasterCrud from './pages/admin/AgentMasterCrud';
import RenewDateCrud from './pages/admin/RenewDateCrud';
import RenewBasicCrud from './pages/admin/RenewBasicCrud';
import RenewMappingCrud from './pages/admin/RenewMappingCrud';
import OicFieldMappingCrud from './pages/admin/OicFieldMappingCrud';
import OicValueMappingCrud from './pages/admin/OicValueMappingCrud';
import ReportsDashboard from './pages/admin/ReportsDashboard';
import ReportRemarks from './pages/admin/ReportRemarks';
import ExportDataReport from './pages/admin/ExportDataReport';

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        {/* Public Routes */}
        <Route path="/" element={
          <RegistrationProvider>
            <PublicLayout />
          </RegistrationProvider>
        }>
          <Route index element={<RegistrationForm />} />
          <Route path="success" element={<SuccessPage />} />
          <Route path="upload" element={<UploadPage />} />
          <Route path="enquiry" element={<EnquiryPage />} />
        </Route>

        {/* Admin Login Route */}
        <Route path="/admin/login" element={<Login />} />

        {/* Admin Routes */}
        <Route path="/admin" element={<AdminLayout />}>
          <Route index element={<Dashboard />} />
          <Route path="trainees" element={<TraineesList />} />
          <Route path="audit-logs" element={<AuditLogs />} />
          <Route path="system-logs" element={<SystemLogs />} />
          <Route path="config" element={<ConfigPage />} />
          
          {/* Reports Routes */}
          <Route path="reports" element={<ReportsDashboard />} />
          <Route path="reports/remarks" element={<ReportRemarks />} />
          <Route path="reports/export" element={<ExportDataReport />} />
          
          {/* Master Data Routes */}
          <Route path="master-data" element={<MasterDataDashboard />} />
          <Route path="master-data/provinces" element={<LocationMasterCrud type="provinces" />} />
          <Route path="master-data/districts" element={<LocationMasterCrud type="districts" />} />
          <Route path="master-data/subdistricts" element={<LocationMasterCrud type="subdistricts" />} />
          <Route path="master-data/agent-regions" element={<AgentMasterCrud type="agent-regions" />} />
          <Route path="master-data/agent-branches" element={<AgentMasterCrud type="agent-branches" />} />
          <Route path="master-data/renewdates" element={<RenewDateCrud />} />
          <Route path="master-data/renewbasic" element={<RenewBasicCrud />} />
          <Route path="master-data/renewmappings" element={<RenewMappingCrud />} />
          <Route path="master-data/oic-field-mapping" element={<OicFieldMappingCrud />} />
          <Route path="master-data/oic-value-mapping" element={<OicValueMappingCrud />} />
          <Route path="master-data/:type" element={<MasterDataCrud />} />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}
