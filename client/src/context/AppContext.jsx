import React, { createContext, useContext, useState, useEffect } from 'react';
import { api } from '../utils/api';

const AppContext = createContext();

export function AppProvider({ children }) {
  const [token, setToken] = useState(() => localStorage.getItem('ruang_ujian_token') || null);
  const [user, setUser] = useState(() => {
    try {
      const stored = localStorage.getItem('ruang_ujian_user');
      return stored ? JSON.parse(stored) : null;
    } catch {
      return null;
    }
  });

  const [activeTab, setActiveTabState] = useState(() => {
    const params = new URLSearchParams(window.location.search);
    return params.get('tab') || 'dashboard';
  });

  const [sekolah, setSekolah] = useState(null);
  const [ujian, setUjian] = useState(null);
  const [loading, setLoading] = useState(true);
  const [toast, setToast] = useState(null);

  // PWA install prompt
  const [deferredPrompt, setDeferredPrompt] = useState(null);
  const [isInstallable, setIsInstallable] = useState(false);
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const setActiveTab = (tab) => {
    setActiveTabState(tab);
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    window.history.pushState({}, '', url);
    setMobileMenuOpen(false);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const showToast = (message, type = 'success') => {
    setToast({ message, type, id: Date.now() });
    setTimeout(() => {
      setToast((current) => (current && current.message === message ? null : current));
    }, 4000);
  };

  const refreshGlobalData = async () => {
    try {
      const [sekolahRes, ujianRes] = await Promise.all([
        api.getSekolah().catch(() => ({ data: null })),
        api.getActiveUjian().catch(() => ({ data: null }))
      ]);
      if (sekolahRes?.data) setSekolah(sekolahRes.data);
      if (ujianRes?.data) setUjian(ujianRes.data);
    } catch (err) {
      console.error('Error refreshing global data:', err);
    }
  };

  useEffect(() => {
    if (token) {
      refreshGlobalData();
    }
    setLoading(false);

    const handleAuthExpired = () => {
      setToken(null);
      setUser(null);
      showToast('Sesi telah berakhir, silakan login kembali.', 'error');
    };

    window.addEventListener('auth-expired', handleAuthExpired);
    return () => window.removeEventListener('auth-expired', handleAuthExpired);
  }, [token]);

  // Handle PWA BeforeInstallPrompt
  useEffect(() => {
    const handleBeforeInstall = (e) => {
      e.preventDefault();
      setDeferredPrompt(e);
      setIsInstallable(true);
    };

    window.addEventListener('beforeinstallprompt', handleBeforeInstall);

    return () => window.removeEventListener('beforeinstallprompt', handleBeforeInstall);
  }, []);

  const installApp = async () => {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    if (outcome === 'accepted') {
      setIsInstallable(false);
      showToast('Aplikasi Ruang Ujian berhasil diinstall!', 'success');
    }
    setDeferredPrompt(null);
  };

  const login = async (username, password) => {
    const res = await api.login({ username, password });
    if (res.success) {
      localStorage.setItem('ruang_ujian_token', res.token);
      localStorage.setItem('ruang_ujian_user', JSON.stringify(res.user));
      setToken(res.token);
      setUser(res.user);
      showToast(`Selamat datang, ${res.user.nama}!`, 'success');
      await refreshGlobalData();
      return true;
    }
    return false;
  };

  const logout = () => {
    localStorage.removeItem('ruang_ujian_token');
    localStorage.removeItem('ruang_ujian_user');
    setToken(null);
    setUser(null);
    setActiveTab('dashboard');
    showToast('Berhasil keluar dari akun.', 'info');
  };

  return (
    <AppContext.Provider
      value={{
        token,
        user,
        activeTab,
        setActiveTab,
        sekolah,
        setSekolah,
        ujian,
        setUjian,
        refreshGlobalData,
        loading,
        toast,
        showToast,
        login,
        logout,
        isInstallable,
        installApp,
        sidebarOpen,
        setSidebarOpen,
        mobileMenuOpen,
        setMobileMenuOpen
      }}
    >
      {children}
    </AppContext.Provider>
  );
}

export function useApp() {
  const context = useContext(AppContext);
  if (!context) {
    throw new Error('useApp must be used within an AppProvider');
  }
  return context;
}
