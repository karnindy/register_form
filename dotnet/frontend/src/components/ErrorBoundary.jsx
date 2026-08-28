import React from 'react';
import { AlertTriangle, RefreshCw, Home } from 'lucide-react';

export default class ErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null, errorInfo: null };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true, error };
  }

  componentDidCatch(error, errorInfo) {
    console.error("ErrorBoundary caught an error:", error, errorInfo);
    this.setState({ error, errorInfo });
  }

  handleReset = () => {
    this.setState({ hasError: false, error: null, errorInfo: null });
    window.location.reload();
  };

  render() {
    if (this.state.hasError) {
      return (
        <div className="min-h-screen bg-slate-50 flex items-center justify-center p-6 font-sans">
          <div className="bg-white max-w-2xl w-full p-8 rounded-2xl shadow-xl border border-red-100 animate-in fade-in zoom-in-95 duration-200">
            <div className="w-14 h-14 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <AlertTriangle className="w-8 h-8" />
            </div>
            
            <h2 className="text-xl font-bold text-center text-slate-800 mb-2">
              เกิดข้อผิดพลาดในการแสดงผลหน้านี้ (Render Error)
            </h2>
            
            <p className="text-sm text-center text-slate-500 mb-6">
              ระบบตรวจพบข้อผิดพลาดใน Component: <strong>{this.props.name || 'Application'}</strong>
            </p>

            <div className="bg-slate-900 text-red-400 p-4 rounded-xl font-mono text-xs overflow-x-auto mb-6 max-h-60">
              <div className="font-bold text-white mb-1">
                {this.state.error?.name}: {this.state.error?.message}
              </div>
              <div className="text-slate-400 whitespace-pre-wrap text-[11px] mt-2">
                {this.state.error?.stack}
              </div>
            </div>

            <div className="flex justify-center gap-3">
              <button
                onClick={this.handleReset}
                className="inline-flex items-center gap-2 bg-primary hover:bg-[#002882] text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow transition-all"
              >
                <RefreshCw className="w-4 h-4" />
                โหลดหน้านี้ใหม่
              </button>
              <a
                href="/admin"
                className="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2.5 rounded-xl font-bold text-sm transition-all"
              >
                <Home className="w-4 h-4" />
                กลับหน้าหลัก
              </a>
            </div>
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}
