import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import PublicLayout from './layouts/PublicLayout';
import AdminLayout from './layouts/AdminLayout';
import RegistrationForm from './pages/public/RegistrationForm';
import { RegistrationProvider } from './context/RegistrationContext';

// Admin Pages
import Login from './pages/admin/Login';
import Dashboard from './pages/admin/Dashboard';
import TraineesList from './pages/admin/TraineesList';
import AuditLogs from './pages/admin/AuditLogs';
import SystemLogs from './pages/admin/SystemLogs';

import MasterDataDashboard from './pages/admin/MasterDataDashboard';
import MasterDataCrud from './pages/admin/MasterDataCrud';
import ReportsDashboard from './pages/admin/ReportsDashboard';
import ReportRemarks from './pages/admin/ReportRemarks';

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
          <Route path="success" element={<div className="p-10 text-center font-bold text-success text-2xl">ลงทะเบียนสำเร็จ!</div>} />
        </Route>

        {/* Admin Login Route */}
        <Route path="/admin/login" element={<Login />} />

        {/* Admin Routes */}
        <Route path="/admin" element={<AdminLayout />}>
          <Route index element={<Dashboard />} />
          <Route path="trainees" element={<TraineesList />} />
          <Route path="audit-logs" element={<AuditLogs />} />
          <Route path="system-logs" element={<SystemLogs />} />
          
          {/* Reports Routes */}
          <Route path="reports" element={<ReportsDashboard />} />
          <Route path="reports/remarks" element={<ReportRemarks />} />
          
          {/* Master Data Routes */}
          <Route path="master-data" element={<MasterDataDashboard />} />
          <Route path="master-data/:type" element={<MasterDataCrud />} />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}
