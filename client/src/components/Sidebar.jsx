import React, { useState } from 'react';
import { useApp } from '../context/AppContext';
import {
  LayoutDashboard, Building2, CalendarRange,
  GraduationCap, BookOpen, Users, UserCheck, ShieldCheck,
  Award, LayoutGrid, ClipboardList, Calendar, Clock,
  FileText, ScrollText, Printer, Database, LogOut,
  ChevronDown, ChevronRight, IdCard, DoorOpen, ListOrdered,
  FileSpreadsheet, FileCheck, Layers, Sparkles
} from 'lucide-react';

export default function Sidebar() {
  const { activeTab, setActiveTab, sekolah, ujian, logout, sidebarOpen, setSidebarOpen } = useApp();

  // Submenu open states
  const [openSections, setOpenSections] = useState({
    menuUtama: true,
    masterData: true,
    persiapan: true,
    cetak: true,
    sistem: true
  });

  const toggleSection = (sec) => {
    setOpenSections(prev => ({ ...prev, [sec]: !prev[sec] }));
  };

  const navItems = [
    {
      id: 'menuUtama',
      title: 'Menu Utama',
      items: [
        { id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard },
        { id: 'identitas-sekolah', label: 'Identitas Sekolah', icon: Building2 },
        { id: 'manajemen-ujian', label: 'Manajemen Ujian', icon: CalendarRange, badge: ujian ? 'Aktif' : null }
      ]
    },
    {
      id: 'masterData',
      title: 'Master Data',
      items: [
        { id: 'data-kelas', label: 'Data Kelas', icon: GraduationCap },
        { id: 'data-mapel', label: 'Data Mata Pelajaran', icon: BookOpen },
        { id: 'data-peserta', label: 'Data Peserta Ujian', icon: Users },
        { id: 'data-panitia', label: 'Data Panitia', icon: UserCheck },
        { id: 'data-pengawas', label: 'Data Pengawas Ruang', icon: ShieldCheck },
        { id: 'data-penguji', label: 'Data Penguji Praktik', icon: Award },
        { id: 'atur-ruangan', label: 'Atur Ruangan', icon: LayoutGrid }
      ]
    },
    {
      id: 'persiapan',
      title: 'Persiapan Kegiatan',
      items: [
        { id: 'program-kerja', label: 'Program Kerja Panitia', icon: ClipboardList },
        { id: 'jadwal-tulis', label: 'Jadwal Ujian Tulis', icon: Calendar },
        { id: 'jadwal-praktik', label: 'Jadwal Ujian Praktik', icon: Clock },
        { id: 'persuratan', label: 'Persuratan (5 Surat)', icon: FileText },
        { id: 'tata-tertib', label: 'Tata Tertib', icon: ScrollText }
      ]
    },
    {
      id: 'cetak',
      title: 'Pusat Cetak Dokumen',
      badge: '9 Menu',
      items: [
        { id: 'cetak-kartu-peserta', label: '1. Kartu Peserta', icon: IdCard },
        { id: 'cetak-kartu-panitia', label: '2. Kartu Panitia', icon: Award },
        { id: 'cetak-jadwal', label: '3. Jadwal (Tulis & Praktik)', icon: Calendar },
        { id: 'cetak-nomor-ruangan', label: '4. Nomor Ruangan Ujian', icon: DoorOpen },
        { id: 'cetak-daftar-peserta', label: '5. Daftar Peserta (DNT)', icon: ListOrdered },
        { id: 'cetak-denah-duduk', label: '6. Denah Tempat Duduk', icon: LayoutGrid },
        { id: 'cetak-daftar-hadir', label: '7. Daftar Hadir (4 Form)', icon: FileSpreadsheet },
        { id: 'cetak-berita-acara', label: '8. Berita Acara Ujian', icon: FileCheck },
        { id: 'cetak-lpj', label: '9. LPJ Kegiatan Ujian', icon: Layers }
      ]
    },
    {
      id: 'sistem',
      title: 'Sistem',
      items: [
        { id: 'backup-restore', label: 'Backup & Restore', icon: Database }
      ]
    }
  ];

  return (
    <aside 
      className={`fixed top-0 bottom-0 left-0 z-40 bg-[#0A2540] text-slate-200 transition-all duration-300 ease-in-out flex flex-col no-print ${
        sidebarOpen ? 'w-64' : 'w-20'
      } border-r border-slate-800 shadow-xl hidden md:flex`}
    >
      {/* Brand Header */}
      <div className="h-16 flex items-center px-4 border-b border-slate-800/80 gap-3 bg-[#081f37] shrink-0">
        <div className="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
          <img src="/icon.svg" alt="App Logo" className="w-6 h-6 object-contain" />
        </div>
        {sidebarOpen && (
          <div className="flex-1 min-w-0">
            <h2 className="font-bold text-white tracking-tight text-base leading-tight flex items-center gap-1.5 truncate">
              Ruang Ujian
              <span className="text-[10px] bg-blue-500/30 text-blue-300 font-semibold px-1.5 py-0.5 rounded-full">v1.0</span>
            </h2>
            <p className="text-[11px] text-slate-400 truncate">
              {sekolah?.nama_sekolah || 'Sistem Ujian Sekolah'}
            </p>
          </div>
        )}
      </div>

      {/* Navigation List */}
      <div className="flex-1 overflow-y-auto px-3 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-700">
        {navItems.map((group) => (
          <div key={group.id} className="space-y-1">
            {sidebarOpen ? (
              <button
                onClick={() => toggleSection(group.id)}
                className="w-full flex items-center justify-between px-2.5 py-1.5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider hover:text-slate-200 transition-colors"
              >
                <div className="flex items-center gap-1.5">
                  <span>{group.title}</span>
                  {group.badge && (
                    <span className="text-[9px] bg-blue-500/20 text-blue-300 px-1.5 py-0.2 rounded-full normal-case font-medium">
                      {group.badge}
                    </span>
                  )}
                </div>
                {openSections[group.id] ? <ChevronDown className="w-3.5 h-3.5" /> : <ChevronRight className="w-3.5 h-3.5" />}
              </button>
            ) : (
              <div className="h-2 border-b border-slate-800/60 my-2"></div>
            )}

            {(openSections[group.id] || !sidebarOpen) && (
              <div className="space-y-0.5">
                {group.items.map((item) => {
                  const Icon = item.icon;
                  const isActive = activeTab === item.id;
                  return (
                    <button
                      key={item.id}
                      onClick={() => setActiveTab(item.id)}
                      title={!sidebarOpen ? item.label : undefined}
                      className={`w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all ${
                        isActive
                          ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-semibold'
                          : 'text-slate-300 hover:bg-slate-800/70 hover:text-white'
                      } ${!sidebarOpen ? 'justify-center px-0' : ''}`}
                    >
                      <Icon className={`w-4 h-4 shrink-0 ${isActive ? 'text-white' : 'text-slate-400'}`} />
                      {sidebarOpen && (
                        <div className="flex-1 flex items-center justify-between text-left truncate">
                          <span className="truncate">{item.label}</span>
                          {item.badge && (
                            <span className="text-[10px] bg-emerald-500/20 text-emerald-300 px-1.5 py-0.5 rounded-full font-medium shrink-0 ml-1">
                              {item.badge}
                            </span>
                          )}
                        </div>
                      )}
                    </button>
                  );
                })}
              </div>
            )}
          </div>
        ))}
      </div>

      {/* User & Logout Footer */}
      <div className="p-3 border-t border-slate-800/80 bg-[#081f37] shrink-0">
        <button
          onClick={logout}
          className={`w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-rose-300 hover:bg-rose-500/10 hover:text-rose-200 transition-colors ${
            !sidebarOpen ? 'justify-center px-0' : ''
          }`}
          title="Keluar / Logout"
        >
          <LogOut className="w-4 h-4 shrink-0 text-rose-400" />
          {sidebarOpen && <span>Keluar (Logout)</span>}
        </button>
      </div>
    </aside>
  );
}
