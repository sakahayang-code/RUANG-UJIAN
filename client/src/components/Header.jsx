import React, { useState } from 'react';
import { useApp } from '../context/AppContext';
import {
  Menu, X, Download, Printer, User, LogOut, Key,
  Sparkles, CheckCircle2, Building, Calendar, ChevronDown
} from 'lucide-react';
import Modal from './Modal';
import { api } from '../utils/api';

export default function Header() {
  const {
    sidebarOpen, setSidebarOpen,
    sekolah, ujian, user, logout,
    isInstallable, installApp,
    setActiveTab, showToast,
    setMobileMenuOpen
  } = useApp();

  const [profileDropdownOpen, setProfileDropdownOpen] = useState(false);
  const [passwordModalOpen, setPasswordModalOpen] = useState(false);
  const [oldPassword, setOldPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [loadingPass, setLoadingPass] = useState(false);

  const handleChangePassword = async (e) => {
    e.preventDefault();
    if (!oldPassword || !newPassword) {
      showToast('Harap lengkapi semua isian.', 'warning');
      return;
    }
    setLoadingPass(true);
    try {
      await api.changePassword({ oldPassword, newPassword });
      showToast('Password admin berhasil diubah!', 'success');
      setPasswordModalOpen(false);
      setOldPassword('');
      setNewPassword('');
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setLoadingPass(false);
    }
  };

  return (
    <>
      <header className="sticky top-0 z-30 h-16 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-4 md:px-6 flex items-center justify-between no-print shadow-xs">
        {/* Left Section */}
        <div className="flex items-center gap-3">
          {/* Toggle sidebar (desktop) */}
          <button
            onClick={() => setSidebarOpen(!sidebarOpen)}
            className="hidden md:flex p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors"
            title="Sembunyikan / Tampilkan Menu"
          >
            <Menu className="w-5 h-5" />
          </button>

          {/* Toggle mobile menu drawer */}
          <button
            onClick={() => setMobileMenuOpen(true)}
            className="flex md:hidden p-2 text-slate-700 hover:bg-slate-100 rounded-xl"
            title="Menu Navigasi"
          >
            <Menu className="w-5 h-5" />
          </button>

          {/* School and Exam Badge */}
          <div className="flex items-center gap-2 truncate">
            <span className="font-bold text-slate-800 text-sm md:text-base truncate hidden sm:inline">
              {sekolah?.nama_sekolah || 'Ruang Ujian'}
            </span>
            {ujian && (
              <div 
                onClick={() => setActiveTab('manajemen-ujian')}
                className="cursor-pointer inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200/70 rounded-full text-xs font-semibold tracking-tight transition-colors truncate"
              >
                <span className="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                <span className="truncate max-w-[200px] sm:max-w-xs">{ujian.nama_ujian}</span>
              </div>
            )}
          </div>
        </div>

        {/* Right Section Actions */}
        <div className="flex items-center gap-2">
          {/* PWA Install Button */}
          {isInstallable && (
            <button
              onClick={installApp}
              className="flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-xs font-semibold shadow-xs shadow-emerald-500/20 transition-all hover:scale-[1.02] active:scale-95"
            >
              <Download className="w-4 h-4" />
              <span className="hidden sm:inline">Install Android / PWA</span>
            </button>
          )}

          {/* Quick Print Hub Button */}
          <button
            onClick={() => setActiveTab('cetak-kartu-peserta')}
            className="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-semibold transition-colors border border-blue-200"
          >
            <Printer className="w-4 h-4 text-blue-600" />
            <span>Pusat Cetak</span>
          </button>

          {/* User Profile Dropdown */}
          <div className="relative">
            <button
              onClick={() => setProfileDropdownOpen(!profileDropdownOpen)}
              className="flex items-center gap-2 p-1.5 sm:px-3 sm:py-1.5 text-slate-700 hover:bg-slate-100 rounded-xl transition-colors text-xs font-medium"
            >
              <div className="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                {user?.nama ? user.nama.charAt(0) : 'A'}
              </div>
              <span className="hidden md:inline font-semibold text-slate-800">{user?.nama || 'Admin'}</span>
              <ChevronDown className="w-3.5 h-3.5 text-slate-400 hidden sm:inline" />
            </button>

            {profileDropdownOpen && (
              <>
                <div 
                  className="fixed inset-0 z-30" 
                  onClick={() => setProfileDropdownOpen(false)}
                ></div>
                <div className="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-40 animate-fade-in">
                  <div className="px-4 py-2 border-b border-slate-100">
                    <p className="text-xs font-semibold text-slate-800">{user?.nama || 'Administrator'}</p>
                    <p className="text-[11px] text-slate-500 truncate">@{user?.username || 'admin'}</p>
                    <span className="inline-block mt-1 text-[10px] bg-blue-50 text-blue-700 font-semibold px-2 py-0.5 rounded-full border border-blue-100">
                      Hak Akses: {user?.role || 'admin'}
                    </span>
                  </div>

                  <button
                    onClick={() => {
                      setProfileDropdownOpen(false);
                      setPasswordModalOpen(true);
                    }}
                    className="w-full flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 transition-colors"
                  >
                    <Key className="w-4 h-4 text-slate-400" />
                    <span>Ubah Password</span>
                  </button>

                  <button
                    onClick={() => {
                      setProfileDropdownOpen(false);
                      setActiveTab('identitas-sekolah');
                    }}
                    className="w-full flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 transition-colors"
                  >
                    <Building className="w-4 h-4 text-slate-400" />
                    <span>Identitas Sekolah</span>
                  </button>

                  <div className="border-t border-slate-100 my-1"></div>

                  <button
                    onClick={() => {
                      setProfileDropdownOpen(false);
                      logout();
                    }}
                    className="w-full flex items-center gap-2.5 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 transition-colors font-medium"
                  >
                    <LogOut className="w-4 h-4 text-rose-500" />
                    <span>Keluar (Logout)</span>
                  </button>
                </div>
              </>
            )}
          </div>
        </div>
      </header>

      {/* Change Password Modal */}
      <Modal
        isOpen={passwordModalOpen}
        onClose={() => setPasswordModalOpen(false)}
        title="Ubah Password Admin"
      >
        <form onSubmit={handleChangePassword} className="space-y-4">
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Password Lama</label>
            <input
              type="password"
              value={oldPassword}
              onChange={(e) => setOldPassword(e.target.value)}
              placeholder="Masukkan password saat ini (default: admin123)"
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
              required
            />
          </div>
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Password Baru</label>
            <input
              type="password"
              value={newPassword}
              onChange={(e) => setNewPassword(e.target.value)}
              placeholder="Masukkan password baru"
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-hidden focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
              required
            />
          </div>
          <div className="flex justify-end gap-3 pt-3 border-t border-slate-100">
            <button
              type="button"
              onClick={() => setPasswordModalOpen(false)}
              className="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100"
            >
              Batal
            </button>
            <button
              type="submit"
              disabled={loadingPass}
              className="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20"
            >
              {loadingPass ? 'Menyimpan...' : 'Simpan Password Baru'}
            </button>
          </div>
        </form>
      </Modal>
    </>
  );
}
