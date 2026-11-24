import React from 'react';
import { useApp } from './context/AppContext';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import IdentitasSekolah from './pages/IdentitasSekolah';
import ManajemenUjian from './pages/ManajemenUjian';
import DataKelas from './pages/DataKelas';
import DataMapel from './pages/DataMapel';
import DataPeserta from './pages/DataPeserta';
import DataPanitia from './pages/DataPanitia';
import DataPengawas from './pages/DataPengawas';
import DataPenguji from './pages/DataPenguji';
import AturRuangan from './pages/AturRuangan';
import ProgramKerja from './pages/ProgramKerja';
import JadwalTulis from './pages/JadwalTulis';
import JadwalPraktik from './pages/JadwalPraktik';
import Persuratan from './pages/Persuratan';
import TataTertib from './pages/TataTertib';
import CetakCenter from './pages/CetakCenter';
import BackupRestore from './pages/BackupRestore';

import Sidebar from './components/Sidebar';
import Header from './components/Header';
import MobileNav from './components/MobileNav';
import Toast from './components/Toast';

export default function App() {
  const { token, loading, activeTab, sidebarOpen } = useApp();

  if (loading) {
    return (
      <div className="min-h-screen bg-slate-900 flex items-center justify-center text-white">
        <div className="flex flex-col items-center gap-3">
          <div className="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
          <p className="text-sm font-semibold tracking-wide">Memuat Ruang Ujian...</p>
        </div>
      </div>
    );
  }

  // If not logged in, show login page
  if (!token) {
    return (
      <>
        <Login />
        <Toast />
      </>
    );
  }

  // Active view router
  const renderContent = () => {
    if (activeTab === 'dashboard') return <Dashboard />;
    if (activeTab === 'identitas-sekolah') return <IdentitasSekolah />;
    if (activeTab === 'manajemen-ujian') return <ManajemenUjian />;
    if (activeTab === 'data-kelas') return <DataKelas />;
    if (activeTab === 'data-mapel') return <DataMapel />;
    if (activeTab === 'data-peserta') return <DataPeserta />;
    if (activeTab === 'data-panitia') return <DataPanitia />;
    if (activeTab === 'data-pengawas') return <DataPengawas />;
    if (activeTab === 'data-penguji') return <DataPenguji />;
    if (activeTab === 'atur-ruangan') return <AturRuangan />;
    if (activeTab === 'program-kerja') return <ProgramKerja />;
    if (activeTab === 'jadwal-tulis') return <JadwalTulis />;
    if (activeTab === 'jadwal-praktik') return <JadwalPraktik />;
    if (activeTab === 'persuratan') return <Persuratan />;
    if (activeTab === 'tata-tertib') return <TataTertib />;
    if (activeTab.startsWith('cetak-')) return <CetakCenter initialTab={activeTab.replace('cetak-', '')} />;
    if (activeTab === 'backup-restore') return <BackupRestore />;

    return <Dashboard />;
  };

  return (
    <div className="min-h-screen bg-slate-50 text-slate-800 flex flex-col font-sans">
      {/* Desktop Sidebar */}
      <Sidebar />

      {/* Main Layout Area */}
      <div className={`flex-1 flex flex-col transition-all duration-300 ${
        sidebarOpen ? 'md:pl-64' : 'md:pl-20'
      }`}>
        {/* Top Header */}
        <Header />

        {/* Content View */}
        <main className="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto pb-24 md:pb-8">
          {renderContent()}
        </main>
      </div>

      {/* Native Android Mobile Bottom Navigation */}
      <MobileNav />

      {/* Floating Toast Notification */}
      <Toast />
    </div>
  );
}
