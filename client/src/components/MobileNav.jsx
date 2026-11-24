import React from 'react';
import { useApp } from '../context/AppContext';
import {
  LayoutDashboard, Database, Calendar, Printer, Menu, X,
  GraduationCap, BookOpen, Users, UserCheck, ShieldCheck, Award,
  LayoutGrid, ClipboardList, Clock, FileText, ScrollText,
  IdCard, DoorOpen, ListOrdered, FileSpreadsheet, FileCheck, Layers,
  Building2, CalendarRange, LogOut
} from 'lucide-react';

export default function MobileNav() {
  const {
    activeTab, setActiveTab,
    mobileMenuOpen, setMobileMenuOpen,
    sekolah, ujian, logout
  } = useApp();

  const primaryTabs = [
    { id: 'dashboard', label: 'Beranda', icon: LayoutDashboard },
    { id: 'data-peserta', label: 'Peserta', icon: Users },
    { id: 'jadwal-tulis', label: 'Jadwal', icon: Calendar },
    { id: 'cetak-kartu-peserta', label: 'Cetak', icon: Printer },
  ];

  const fullMenuGroups = [
    {
      title: 'Menu Utama',
      items: [
        { id: 'dashboard', label: 'Dashboard Ringkasan', icon: LayoutDashboard },
        { id: 'identitas-sekolah', label: 'Identitas Sekolah', icon: Building2 },
        { id: 'manajemen-ujian', label: 'Manajemen Ujian', icon: CalendarRange }
      ]
    },
    {
      title: 'Master Data',
      items: [
        { id: 'data-kelas', label: 'Data Kelas', icon: GraduationCap },
        { id: 'data-mapel', label: 'Data Mata Pelajaran', icon: BookOpen },
        { id: 'data-peserta', label: 'Data Peserta Ujian', icon: Users },
        { id: 'data-panitia', label: 'Data Panitia', icon: UserCheck },
        { id: 'data-pengawas', label: 'Data Pengawas Ruang', icon: ShieldCheck },
        { id: 'data-penguji', label: 'Data Penguji Praktik', icon: Award },
        { id: 'atur-ruangan', label: 'Atur Ruangan & Kursi', icon: LayoutGrid }
      ]
    },
    {
      title: 'Persiapan Kegiatan',
      items: [
        { id: 'program-kerja', label: 'Program Kerja Panitia', icon: ClipboardList },
        { id: 'jadwal-tulis', label: 'Jadwal Ujian Tulis', icon: Calendar },
        { id: 'jadwal-praktik', label: 'Jadwal Ujian Praktik', icon: Clock },
        { id: 'persuratan', label: 'Persuratan (5 Format Surat)', icon: FileText },
        { id: 'tata-tertib', label: 'Tata Tertib', icon: ScrollText }
      ]
    },
    {
      title: 'Pusat Cetak Dokumen Resmi',
      items: [
        { id: 'cetak-kartu-peserta', label: '1. Kartu Peserta Ujian', icon: IdCard },
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
      title: 'Sistem',
      items: [
        { id: 'backup-restore', label: 'Backup & Restore Database', icon: Database }
      ]
    }
  ];

  return (
    <>
      {/* Native Android Bottom Navigation Bar */}
      <nav className="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 py-1.5 px-3 flex items-center justify-around md:hidden no-print shadow-lg safe-area-pb">
        {primaryTabs.map((tab) => {
          const Icon = tab.icon;
          const isActive = activeTab === tab.id;
          return (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={`flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition-all ${
                isActive ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-800'
              }`}
            >
              <div className={`p-1 rounded-full ${isActive ? 'bg-blue-100/70' : ''}`}>
                <Icon className={`w-5 h-5 ${isActive ? 'text-blue-600 stroke-[2.3]' : 'text-slate-500'}`} />
              </div>
              <span className="text-[10px] mt-0.5 tracking-tight">{tab.label}</span>
            </button>
          );
        })}

        {/* Menu Drawer Toggle */}
        <button
          onClick={() => setMobileMenuOpen(true)}
          className={`flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition-all ${
            mobileMenuOpen ? 'text-blue-600 font-semibold' : 'text-slate-500 hover:text-slate-800'
          }`}
        >
          <div className="p-1 rounded-full">
            <Menu className="w-5 h-5 text-slate-500" />
          </div>
          <span className="text-[10px] mt-0.5 tracking-tight">Semua Menu</span>
        </button>
      </nav>

      {/* Slide-out Mobile Menu Drawer */}
      {mobileMenuOpen && (
        <div className="fixed inset-0 z-50 flex md:hidden no-print">
          {/* Backdrop */}
          <div 
            className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            onClick={() => setMobileMenuOpen(false)}
          ></div>

          {/* Drawer Content */}
          <div className="relative w-4/5 max-w-xs bg-[#0A2540] text-slate-200 h-full shadow-2xl flex flex-col z-10 animate-slide-right">
            {/* Drawer Header */}
            <div className="p-4 border-b border-slate-800 flex items-center justify-between bg-[#081f37]">
              <div className="flex items-center gap-2.5">
                <img src="/icon.svg" alt="Ruang Ujian" className="w-8 h-8 rounded-lg" />
                <div>
                  <h3 className="font-bold text-white text-sm">Ruang Ujian</h3>
                  <p className="text-[11px] text-slate-400 truncate max-w-[170px]">
                    {sekolah?.nama_sekolah || 'Aplikasi Ujian'}
                  </p>
                </div>
              </div>
              <button
                onClick={() => setMobileMenuOpen(false)}
                className="p-1 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Menu Groups */}
            <div className="flex-1 overflow-y-auto p-3 space-y-4">
              {fullMenuGroups.map((group, idx) => (
                <div key={idx} className="space-y-1">
                  <p className="text-[10px] uppercase font-bold tracking-wider text-slate-400 px-2 py-1">
                    {group.title}
                  </p>
                  <div className="space-y-0.5">
                    {group.items.map((item) => {
                      const Icon = item.icon;
                      const isActive = activeTab === item.id;
                      return (
                        <button
                          key={item.id}
                          onClick={() => setActiveTab(item.id)}
                          className={`w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all ${
                            isActive
                              ? 'bg-blue-600 text-white font-semibold shadow-md'
                              : 'text-slate-300 hover:bg-slate-800/80 hover:text-white'
                          }`}
                        >
                          <Icon className={`w-4 h-4 shrink-0 ${isActive ? 'text-white' : 'text-slate-400'}`} />
                          <span className="truncate">{item.label}</span>
                        </button>
                      );
                    })}
                  </div>
                </div>
              ))}
            </div>

            {/* Drawer Footer */}
            <div className="p-3 border-t border-slate-800 bg-[#081f37]">
              <button
                onClick={() => {
                  setMobileMenuOpen(false);
                  logout();
                }}
                className="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-rose-500/10 text-rose-300 hover:bg-rose-500/20 text-xs font-semibold"
              >
                <LogOut className="w-4 h-4" />
                <span>Keluar (Logout)</span>
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
