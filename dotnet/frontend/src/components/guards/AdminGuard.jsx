import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';

export default function AdminGuard({ children }) {
  const { isAuthenticated, isAdminOrViewer, isLoading, user } = useAuth();
  const location = useLocation();

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
      </div>
    );
  }

  if (!isAuthenticated) {
    return <Navigate to="/admin/login" state={{ from: location }} replace />;
  }

  // If user is an Applicant, allow them into Admin area (e.g. /admin/trainees, /admin/profile)
  if (!isAdminOrViewer && user?.role === 'Applicant') {
    if (location.pathname === '/admin' || location.pathname === '/admin/') {
      return <Navigate to="/admin/trainees" replace />;
    }
  }

  return children;
}
