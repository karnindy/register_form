import { useState, useEffect, useRef } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { 
  Users, Calendar, TrendingUp, CheckCircle, Clock, 
  Briefcase, UserCheck, RefreshCw, BarChart3, PieChart, 
  ArrowRight, ShieldCheck, FileSpreadsheet, Settings, Database, Activity,
  Filter, RotateCcw, ChevronLeft, ChevronRight
} from 'lucide-react';

const API_BASE_URL = 'http://localhost:8085/api';

const formatDateForInput = (d) => {
  const pad = (n) => String(n).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
};

export default function Dashboard() {
  const { token, user } = useAuth();
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [lastUpdated, setLastUpdated] = useState(new Date());
  const [hoveredBar, setHoveredBar] = useState(null);

  // Trend Controls
  const [trendPreset, setTrendPreset] = useState('30'); // '7', '14', '30', '60', 'custom'
  const [trendStartDate, setTrendStartDate] = useState(() => {
    const d = new Date();
    d.setDate(d.getDate() - 29);
    return formatDateForInput(d);
  });
  const [trendEndDate, setTrendEndDate] = useState(() => formatDateForInput(new Date()));

  // Course Filter Controls
  const [coursePreset, setCoursePreset] = useState('all'); // 'all', 'today', 'thisMonth', 'custom'
  const [courseStartDate, setCourseStartDate] = useState('');
  const [courseEndDate, setCourseEndDate] = useState('');

  const trendScrollRef = useRef(null);

  const fetchStats = async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams();
      if (trendStartDate) params.append('trendStartDate', trendStartDate);
      if (trendEndDate) params.append('trendEndDate', trendEndDate);
      if (courseStartDate) params.append('courseStartDate', courseStartDate);
      if (courseEndDate) params.append('courseEndDate', courseEndDate);

      const res = await fetch(`${API_BASE_URL}/admin/dashboard-stats?${params.toString()}`, {
        headers: {
          'Authorization': token ? `Bearer ${token}` : ''
        }
      });
      if (res.ok) {
        const data = await res.json();
        setStats(data);
        setLastUpdated(new Date());
      }
    } catch (err) {
      console.error('Failed to fetch dashboard stats:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchStats();
  }, [token, trendStartDate, trendEndDate, courseStartDate, courseEndDate]);

  // Scroll to rightmost (most recent date) when dailyTrend changes
  useEffect(() => {
    if (trendScrollRef.current) {
      trendScrollRef.current.scrollLeft = trendScrollRef.current.scrollWidth;
    }
  }, [stats?.dailyTrend]);

  // Trend Preset Click
  const handleTrendPreset = (days) => {
    setTrendPreset(days.toString());
    const end = new Date();
    const start = new Date();
    start.setDate(start.getDate() - (days - 1));
    setTrendStartDate(formatDateForInput(start));
    setTrendEndDate(formatDateForInput(end));
  };

  // Course Preset Click
  const handleCoursePreset = (preset) => {
    setCoursePreset(preset);
    const today = new Date();
    if (preset === 'all') {
      setCourseStartDate('');
      setCourseEndDate('');
    } else if (preset === 'today') {
      const dStr = formatDateForInput(today);
      setCourseStartDate(dStr);
      setCourseEndDate(dStr);
    } else if (preset === 'thisMonth') {
      const start = new Date(today.getFullYear(), today.getMonth(), 1);
      setCourseStartDate(formatDateForInput(start));
      setCourseEndDate(formatDateForInput(today));
    }
  };

  const maxDailyCount = stats?.dailyTrend?.reduce((max, d) => Math.max(max, d.count), 0) || 1;

  // Distinct vibrant colors for courses
  const courseColors = [
    'bg-blue-500 text-blue-600 border-blue-200',
    'bg-emerald-500 text-emerald-600 border-emerald-200',
    'bg-amber-500 text-amber-600 border-amber-200',
    'bg-purple-500 text-purple-600 border-purple-200',
    'bg-rose-500 text-rose-600 border-rose-200',
    'bg-indigo-500 text-indigo-600 border-indigo-200',
    'bg-cyan-500 text-cyan-600 border-cyan-200',
    'bg-orange-500 text-orange-600 border-orange-200',
    'bg-teal-500 text-teal-600 border-teal-200',
    'bg-pink-500 text-pink-600 border-pink-200',
  ];

  const scrollTrend = (direction) => {
    if (trendScrollRef.current) {
      trendScrollRef.current.scrollBy({ left: direction * 200, behavior: 'smooth' });
    }
  };

  return (
    <div className="max-w-7xl mx-auto space-y-6 pb-12">
      {/* Header Bar */}
      <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div className="flex items-center gap-2 text-primary font-bold text-sm tracking-wide uppercase mb-1">
            <Activity className="w-4 h-4 animate-pulse text-[#ffc107]" />
            <span>Executive Dashboard</span>
          </div>
          <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-800">
            ระบบรายงานและสถิติผู้สมัครอบรม
          </h1>
          <p className="text-slate-500 text-sm mt-1">
            แสดงสถิติ Real-time ยอดผู้สมัครรวม, ยอดสมัครวันนี้, สัดส่วนแยกตามหลักสูตร และแนวโน้มการสมัคร
          </p>
        </div>

        <div className="flex items-center gap-3 self-start md:self-auto">
          <div className="text-right text-xs text-slate-500 hidden sm:block">
            <div>อัปเดตล่าสุด</div>
            <div className="font-semibold text-slate-700">
              {lastUpdated.toLocaleTimeString('th-TH')}
            </div>
          </div>
          <button
            onClick={fetchStats}
            disabled={loading}
            className="flex items-center gap-2 px-4 py-2.5 bg-primary text-white rounded-lg hover:bg-primary/90 transition shadow-sm font-medium text-sm disabled:opacity-50 cursor-pointer"
          >
            <RefreshCw className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} />
            <span>{loading ? 'กำลังโหลด...' : 'รีเฟรชข้อมูล'}</span>
          </button>
        </div>
      </div>

      {/* KPI Cards Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div className="bg-gradient-to-br from-blue-600 to-indigo-700 text-white rounded-xl p-5 shadow-sm relative overflow-hidden">
          <div className="absolute -right-3 -bottom-3 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
          <div className="flex justify-between items-start">
            <span className="text-blue-100 text-xs font-semibold uppercase tracking-wider">ยอดผู้สมัครรวมทั้งหมด</span>
            <div className="p-2 bg-white/15 rounded-lg backdrop-blur-sm">
              <Users className="w-5 h-5 text-white" />
            </div>
          </div>
          <div className="mt-4">
            <div className="text-3xl font-extrabold">
              {stats?.totalApplicants?.toLocaleString() ?? 0}
              <span className="text-sm font-normal text-blue-200 ml-1.5">คน</span>
            </div>
            <div className="mt-2 text-xs text-blue-100 flex items-center gap-1.5">
              <span className="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
              <span>ข้อมูลทั้งหมดในฐานข้อมูล</span>
            </div>
          </div>
        </div>

        <div className="bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-xl p-5 shadow-sm relative overflow-hidden">
          <div className="absolute -right-3 -bottom-3 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
          <div className="flex justify-between items-start">
            <span className="text-emerald-100 text-xs font-semibold uppercase tracking-wider">ยอดสมัครวันนี้</span>
            <div className="p-2 bg-white/15 rounded-lg backdrop-blur-sm">
              <Calendar className="w-5 h-5 text-white" />
            </div>
          </div>
          <div className="mt-4">
            <div className="text-3xl font-extrabold">
              {stats?.todayApplicants?.toLocaleString() ?? 0}
              <span className="text-sm font-normal text-emerald-200 ml-1.5">คน</span>
            </div>
            <div className="mt-2 text-xs text-emerald-100 flex items-center justify-between">
              <span>ประจำวันที่ {new Date().toLocaleDateString('th-TH', { day: 'numeric', month: 'short' })}</span>
              <span className="bg-white/20 px-2 py-0.5 rounded text-[11px] font-bold">วันนี้</span>
            </div>
          </div>
        </div>

        <div className="bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-xl p-5 shadow-sm relative overflow-hidden">
          <div className="absolute -right-3 -bottom-3 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
          <div className="flex justify-between items-start">
            <span className="text-amber-100 text-xs font-semibold uppercase tracking-wider">ยอดสมัครเดือนนี้</span>
            <div className="p-2 bg-white/15 rounded-lg backdrop-blur-sm">
              <TrendingUp className="w-5 h-5 text-white" />
            </div>
          </div>
          <div className="mt-4">
            <div className="text-3xl font-extrabold">
              {stats?.thisMonthApplicants?.toLocaleString() ?? 0}
              <span className="text-sm font-normal text-amber-200 ml-1.5">คน</span>
            </div>
            <div className="mt-2 text-xs text-amber-100">
              <span>ประจำเดือน {new Date().toLocaleDateString('th-TH', { month: 'long', year: 'numeric' })}</span>
            </div>
          </div>
        </div>

        <div className="bg-gradient-to-br from-purple-600 to-violet-800 text-white rounded-xl p-5 shadow-sm relative overflow-hidden">
          <div className="absolute -right-3 -bottom-3 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
          <div className="flex justify-between items-start">
            <span className="text-purple-100 text-xs font-semibold uppercase tracking-wider">สัดส่วนประเภทใบอนุญาต</span>
            <div className="p-2 bg-white/15 rounded-lg backdrop-blur-sm">
              <Briefcase className="w-5 h-5 text-white" />
            </div>
          </div>
          <div className="mt-4 flex items-center justify-between">
            <div>
              <div className="text-xs text-purple-200">ตัวแทนประกัน</div>
              <div className="text-xl font-bold">{stats?.agentCount ?? 0} <span className="text-xs font-normal">คน</span></div>
            </div>
            <div className="h-8 w-px bg-white/20"></div>
            <div>
              <div className="text-xs text-purple-200">นายหน้าประกัน</div>
              <div className="text-xl font-bold">{stats?.brokerCount ?? 0} <span className="text-xs font-normal">คน</span></div>
            </div>
          </div>
          <div className="mt-3 text-[11px] text-purple-200 bg-white/10 rounded px-2 py-1 flex justify-between items-center">
            <span>ยืนยันข้อมูลแล้ว: <b>{stats?.confirmedCount ?? 0}</b></span>
            <span>รอยืนยัน: <b>{stats?.pendingCount ?? 0}</b></span>
          </div>
        </div>
      </div>

      {/* Main Charts & Analytics Row */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {/* Left Column: Horizontal Scrollable Registration Trend (7 Cols) */}
        <div className="lg:col-span-7 bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
          <div>
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
              <div className="flex items-center gap-2">
                <BarChart3 className="w-5 h-5 text-primary" />
                <h3 className="font-bold text-slate-800 text-base sm:text-lg">
                  กราฟแนวโน้มการสมัคร
                </h3>
              </div>

              {/* Quick Presets & Controls */}
              <div className="flex items-center gap-1.5 flex-wrap">
                {[
                  { label: '7 วัน', val: 7 },
                  { label: '14 วัน', val: 14 },
                  { label: '30 วัน', val: 30 },
                  { label: '60 วัน', val: 60 },
                ].map(p => (
                  <button
                    key={p.val}
                    onClick={() => handleTrendPreset(p.val)}
                    className={`px-2.5 py-1 text-xs rounded-md font-medium transition cursor-pointer ${
                      trendPreset === p.val.toString()
                        ? 'bg-primary text-white shadow-xs'
                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                    }`}
                  >
                    {p.label}
                  </button>
                ))}
              </div>
            </div>

            {/* Custom Date Range Picker for Trend */}
            <div className="flex flex-wrap items-center gap-2 text-xs bg-slate-50 p-2 rounded-lg border border-slate-200 mb-3">
              <span className="text-slate-500 font-medium">ช่วงวันที่:</span>
              <input
                type="date"
                value={trendStartDate}
                onChange={(e) => {
                  setTrendPreset('custom');
                  setTrendStartDate(e.target.value);
                }}
                className="bg-white border border-slate-300 rounded px-2 py-1 text-xs text-slate-700 focus:ring-1 focus:ring-primary focus:border-primary"
              />
              <span className="text-slate-400">ถึง</span>
              <input
                type="date"
                value={trendEndDate}
                onChange={(e) => {
                  setTrendPreset('custom');
                  setTrendEndDate(e.target.value);
                }}
                className="bg-white border border-slate-300 rounded px-2 py-1 text-xs text-slate-700 focus:ring-1 focus:ring-primary focus:border-primary"
              />
              <span className="text-slate-400 text-[11px] ml-auto">
                (เลื่อนซ้าย-ขวา เพื่อดูวันย้อนหลัง)
              </span>
            </div>
          </div>

          {/* Scrollable Container with navigation buttons */}
          <div className="relative pt-2 pb-2">
            {/* Scroll Left/Right Helper Buttons */}
            <div className="flex justify-end gap-1 mb-2">
              <button
                onClick={() => scrollTrend(-1)}
                title="เลื่อนไปทางซ้าย"
                className="p-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 transition cursor-pointer"
              >
                <ChevronLeft className="w-4 h-4" />
              </button>
              <button
                onClick={() => scrollTrend(1)}
                title="เลื่อนไปทางขวา"
                className="p-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 transition cursor-pointer"
              >
                <ChevronRight className="w-4 h-4" />
              </button>
            </div>

            {/* Scrollable Bar Area */}
            <div 
              ref={trendScrollRef}
              className="overflow-x-auto pb-3 pt-4 custom-scrollbar scroll-smooth"
              style={{ scrollbarWidth: 'thin' }}
            >
              <div 
                className="flex items-end gap-2 h-48 sm:h-56 border-b border-slate-200 px-2"
                style={{ minWidth: `${Math.max((stats?.dailyTrend?.length || 0) * 38, 420)}px` }}
              >
                {stats?.dailyTrend?.map((item, idx) => {
                  const heightPercent = maxDailyCount > 0 ? (item.count / maxDailyCount) * 100 : 0;
                  const isHovered = hoveredBar === idx;
                  const isToday = idx === stats.dailyTrend.length - 1;

                  return (
                    <div
                      key={idx}
                      className="flex-1 min-w-[32px] max-w-[48px] flex flex-col items-center h-full justify-end group cursor-pointer relative"
                      onMouseEnter={() => setHoveredBar(idx)}
                      onMouseLeave={() => setHoveredBar(null)}
                    >
                      {/* Tooltip on Hover */}
                      {isHovered && (
                        <div className="absolute -top-12 z-20 bg-slate-800 text-white text-xs rounded-md py-1 px-2.5 shadow-lg whitespace-nowrap pointer-events-none transition-all">
                          <div className="font-bold">{item.label}</div>
                          <div className="text-emerald-300 font-semibold">{item.count} คน</div>
                        </div>
                      )}

                      {/* Bar */}
                      <div className="w-full flex items-end justify-center h-full pb-1">
                        <div
                          style={{ height: `${Math.max(heightPercent, 4)}%` }}
                          className={`w-full rounded-t-md transition-all duration-300 ${
                            isToday
                              ? 'bg-gradient-to-t from-emerald-600 to-teal-400 shadow-md shadow-emerald-200'
                              : isHovered
                              ? 'bg-gradient-to-t from-primary to-blue-400 shadow-md'
                              : item.count > 0
                              ? 'bg-gradient-to-t from-[#243c84] to-blue-500 opacity-90'
                              : 'bg-slate-200'
                          }`}
                        >
                          {item.count > 0 && (
                            <div className="text-[10px] text-center font-bold text-white pt-0.5">
                              {item.count}
                            </div>
                          )}
                        </div>
                      </div>

                      {/* X-axis Label */}
                      <span className={`text-[10px] mt-2 truncate w-full text-center ${
                        isToday ? 'font-bold text-emerald-700' : 'text-slate-500'
                      }`}>
                        {item.label}
                      </span>
                    </div>
                  );
                })}
              </div>
            </div>
            
            {/* Chart Legend */}
            <div className="flex items-center justify-between text-xs text-slate-500 mt-3 px-2">
              <div className="flex items-center gap-4">
                <div className="flex items-center gap-1.5">
                  <span className="w-3 h-3 rounded-sm bg-[#243c84]"></span>
                  <span>วันก่อนหน้า</span>
                </div>
                <div className="flex items-center gap-1.5">
                  <span className="w-3 h-3 rounded-sm bg-emerald-500"></span>
                  <span>วันนี้</span>
                </div>
              </div>
              <div>รวมช่วงนี้: <b>{stats?.dailyTrend?.reduce((sum, d) => sum + d.count, 0) ?? 0} คน</b> ({stats?.dailyTrend?.length ?? 0} วัน)</div>
            </div>
          </div>
        </div>

        {/* Right Column: Course Distribution (5 Cols) */}
        <div className="lg:col-span-5 bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
          <div>
            <div className="flex items-center justify-between mb-2">
              <div className="flex items-center gap-2">
                <PieChart className="w-5 h-5 text-amber-500" />
                <h3 className="font-bold text-slate-800 text-base sm:text-lg">
                  สัดส่วนแยกตามวิชา / หลักสูตร
                </h3>
              </div>
              <span className="text-xs text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full font-medium">
                {stats?.courseDistribution?.length ?? 0} รายการ
              </span>
            </div>

            {/* Course Filter Controls */}
            <div className="bg-slate-50 border border-slate-200 p-2.5 rounded-lg mb-3.5 space-y-2">
              <div className="flex items-center justify-between">
                <div className="flex items-center gap-1 text-xs text-slate-600 font-semibold">
                  <Filter className="w-3.5 h-3.5 text-primary" />
                  <span>กรองตามช่วงวันที่:</span>
                </div>

                <div className="flex items-center gap-1">
                  {[
                    { key: 'all', label: 'ทั้งหมด' },
                    { key: 'today', label: 'วันนี้' },
                    { key: 'thisMonth', label: 'เดือนนี้' }
                  ].map(btn => (
                    <button
                      key={btn.key}
                      onClick={() => handleCoursePreset(btn.key)}
                      className={`px-2 py-0.5 text-[11px] rounded font-medium transition cursor-pointer ${
                        coursePreset === btn.key && !courseStartDate
                          ? 'bg-primary text-white'
                          : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'
                      }`}
                    >
                      {btn.label}
                    </button>
                  ))}
                </div>
              </div>

              <div className="flex items-center gap-2 text-xs">
                <input
                  type="date"
                  value={courseStartDate}
                  onChange={(e) => {
                    setCoursePreset('custom');
                    setCourseStartDate(e.target.value);
                  }}
                  className="bg-white border border-slate-300 rounded px-2 py-1 text-xs text-slate-700 w-full focus:ring-1 focus:ring-primary focus:border-primary"
                />
                <span className="text-slate-400">ถึง</span>
                <input
                  type="date"
                  value={courseEndDate}
                  onChange={(e) => {
                    setCoursePreset('custom');
                    setCourseEndDate(e.target.value);
                  }}
                  className="bg-white border border-slate-300 rounded px-2 py-1 text-xs text-slate-700 w-full focus:ring-1 focus:ring-primary focus:border-primary"
                />
                {(courseStartDate || courseEndDate) && (
                  <button
                    onClick={() => handleCoursePreset('all')}
                    title="ล้างตัวกรอง"
                    className="p-1 rounded hover:bg-slate-200 text-slate-500 cursor-pointer"
                  >
                    <RotateCcw className="w-3.5 h-3.5" />
                  </button>
                )}
              </div>
            </div>

            {/* Course List with Visual Progress Bars */}
            <div className="space-y-3.5 max-h-72 overflow-y-auto pr-1">
              {stats?.courseDistribution && stats.courseDistribution.length > 0 ? (
                stats.courseDistribution.map((c, idx) => {
                  const colorClass = courseColors[idx % courseColors.length];
                  const cName = c.courseName || c.CourseName || 'ไม่ระบุวิชา';
                  const cCount = c.count ?? c.Count ?? 0;
                  const cPct = c.percentage ?? c.Percentage ?? 0;
                  const cId = c.courseId || c.CourseId || idx;

                  return (
                    <div key={cId} className="space-y-1">
                      <div className="flex justify-between text-xs font-medium text-slate-700">
                        <span className="truncate pr-2 font-semibold" title={cName}>
                          <span className="text-slate-400 mr-1.5">#{idx + 1}</span>
                          {cName}
                        </span>
                        <span className="shrink-0 text-slate-600 font-bold">
                          {cCount} คน <span className="text-slate-400 font-normal">({cPct}%)</span>
                        </span>
                      </div>
                      <div className="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div
                          className={`h-2.5 rounded-full transition-all duration-500 ${colorClass.split(' ')[0]}`}
                          style={{ width: `${Math.max(cPct, 2)}%` }}
                        ></div>
                      </div>
                    </div>
                  );
                })
              ) : (
                <div className="text-center py-8 text-slate-400 text-sm">
                  {courseStartDate || courseEndDate 
                    ? 'ไม่พบข้อมูลผู้สมัครในช่วงวันที่ที่เลือก' 
                    : 'ยังไม่มีข้อมูลผู้สมัครตามวิชา'}
                </div>
              )}
            </div>
          </div>

          <div className="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center text-xs text-slate-500">
            <span>รวมทั้งหมดตามวิชา</span>
            <span className="font-bold text-slate-800">
              {stats?.courseDistribution?.reduce((sum, c) => sum + (c.count ?? c.Count ?? 0), 0) ?? 0} คน/รายการ
            </span>
          </div>
        </div>
      </div>

      {/* Bottom Row: Recent Registrations & System Shortcuts */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {/* Recent Registrations Table (8 Cols) */}
        <div className="lg:col-span-8 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
          <div className="flex items-center justify-between mb-4">
            <div className="flex items-center gap-2">
              <UserCheck className="w-5 h-5 text-emerald-600" />
              <h3 className="font-bold text-slate-800 text-base sm:text-lg">
                ผู้สมัครล่าสุด (Recent Registrations)
              </h3>
            </div>
            <Link
              to="/admin/trainees"
              className="text-xs font-semibold text-primary hover:text-primary/80 flex items-center gap-1"
            >
              <span>ดูทั้งหมด</span>
              <ArrowRight className="w-3.5 h-3.5" />
            </Link>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="bg-slate-50 text-slate-600 text-xs uppercase font-bold border-b border-slate-200">
                <tr>
                  <th className="py-3 px-3">ชื่อ - นามสกุล</th>
                  <th className="py-3 px-3">หลักสูตร</th>
                  <th className="py-3 px-3">วันที่สมัคร</th>
                  <th className="py-3 px-3 text-center">สถานะ</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 text-xs sm:text-sm">
                {stats?.recentRegistrations && stats.recentRegistrations.length > 0 ? (
                  stats.recentRegistrations.map((trainee, idx) => {
                    const tName = trainee.name || trainee.Name || '-';
                    const tNationId = trainee.nationId || trainee.NationId || '';
                    const tCourse = trainee.courseName || trainee.CourseName || '-';
                    const tDate = trainee.date || trainee.Date || '-';
                    const tConfirmed = trainee.confirmed ?? trainee.Confirmed ?? false;

                    return (
                      <tr key={idx} className="hover:bg-slate-50/80 transition-colors">
                        <td className="py-3 px-3 font-semibold text-slate-800">
                          <div>{tName}</div>
                          <div className="text-[11px] text-slate-400 font-mono">{tNationId}</div>
                        </td>
                        <td className="py-3 px-3 text-slate-600">
                          <span className="inline-block max-w-[220px] truncate" title={tCourse}>
                            {tCourse}
                          </span>
                        </td>
                        <td className="py-3 px-3 text-slate-500 whitespace-nowrap font-mono text-xs">
                          {tDate}
                        </td>
                        <td className="py-3 px-3 text-center whitespace-nowrap">
                          {tConfirmed ? (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-semibold">
                              <CheckCircle className="w-3 h-3" /> ยืนยันแล้ว
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-semibold">
                              <Clock className="w-3 h-3" /> รอยืนยัน
                            </span>
                          )}
                        </td>
                      </tr>
                    );
                  })
                ) : (
                  <tr>
                    <td colSpan={4} className="py-6 text-center text-slate-400 text-xs">
                      ยังไม่มีรายการผู้สมัครล่าสุด
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>

        {/* System Shortcuts & Tools (4 Cols) */}
        <div className="lg:col-span-4 bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
          <div>
            <h3 className="font-bold text-slate-800 text-base sm:text-lg mb-4 flex items-center gap-2">
              <ShieldCheck className="w-5 h-5 text-primary" />
              <span>ทางลัดเมนูหลัก (Shortcuts)</span>
            </h3>

            <div className="space-y-2.5">
              <Link
                to="/admin/trainees"
                className="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:border-primary hover:bg-blue-50/40 transition group"
              >
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-blue-100 text-blue-700 rounded-md group-hover:bg-primary group-hover:text-white transition">
                    <Users className="w-4 h-4" />
                  </div>
                  <div>
                    <div className="text-sm font-bold text-slate-800">ข้อมูลผู้สมัคร/ผู้อบรม</div>
                    <div className="text-xs text-slate-500">จัดการ ค้นหา และแก้ไขข้อมูล</div>
                  </div>
                </div>
                <ArrowRight className="w-4 h-4 text-slate-400 group-hover:text-primary group-hover:translate-x-0.5 transition" />
              </Link>

              <Link
                to="/admin/reports/export"
                className="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/40 transition group"
              >
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-emerald-100 text-emerald-700 rounded-md group-hover:bg-emerald-600 group-hover:text-white transition">
                    <FileSpreadsheet className="w-4 h-4" />
                  </div>
                  <div>
                    <div className="text-sm font-bold text-slate-800">ส่งออกรายงาน (Export)</div>
                    <div className="text-xs text-slate-500">ดาวน์โหลด Excel / CSV</div>
                  </div>
                </div>
                <ArrowRight className="w-4 h-4 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition" />
              </Link>

              <Link
                to="/admin/master-data"
                className="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:border-amber-500 hover:bg-amber-50/40 transition group"
              >
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-amber-100 text-amber-700 rounded-md group-hover:bg-amber-500 group-hover:text-white transition">
                    <Database className="w-4 h-4" />
                  </div>
                  <div>
                    <div className="text-sm font-bold text-slate-800">จัดการข้อมูล (Master Data)</div>
                    <div className="text-xs text-slate-500">หลักสูตร วันอบรม สาขา</div>
                  </div>
                </div>
                <ArrowRight className="w-4 h-4 text-slate-400 group-hover:text-amber-500 group-hover:translate-x-0.5 transition" />
              </Link>

              <Link
                to="/admin/config"
                className="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:border-purple-500 hover:bg-purple-50/40 transition group"
              >
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-purple-100 text-purple-700 rounded-md group-hover:bg-purple-600 group-hover:text-white transition">
                    <Settings className="w-4 h-4" />
                  </div>
                  <div>
                    <div className="text-sm font-bold text-slate-800">ตั้งค่าระบบ (System Config)</div>
                    <div className="text-xs text-slate-500">เปิด-ปิดระบบ และแบบฟอร์ม</div>
                  </div>
                </div>
                <ArrowRight className="w-4 h-4 text-slate-400 group-hover:text-purple-600 group-hover:translate-x-0.5 transition" />
              </Link>
            </div>
          </div>

          <div className="mt-4 pt-3 border-t border-slate-100 text-center text-xs text-slate-400">
            ระบบจัดการ Viriyah Registration Portal
          </div>
        </div>
      </div>
    </div>
  );
}
