import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import {
  Database, Download, Upload, RefreshCw, AlertTriangle,
  CheckCircle2, HardDrive, FileJson, ShieldAlert, Sparkles
} from 'lucide-react';

export default function BackupRestore() {
  const { showToast, refreshGlobalData } = useApp();
  const [info, setInfo] = useState(null);
  const [loading, setLoading] = useState(true);
  const [restoringDb, setRestoringDb] = useState(false);
  const [restoringJson, setRestoringJson] = useState(false);
  const [resetting, setResetting] = useState(false);

  const fetchInfo = async () => {
    try {
      const res = await api.getSistemInfo();
      if (res.success) {
        setInfo(res);
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchInfo();
  }, []);

  const handleDownloadDb = () => {
    window.location.href = '/api/sistem/backup/db';
    showToast('Mengunduh file database SQLite (.sqlite)...', 'info');
  };

  const handleDownloadJson = () => {
    window.location.href = '/api/sistem/backup/json';
    showToast('Mengunduh cadangan data format JSON...', 'info');
  };

  const handleRestoreDb = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    if (!window.confirm('PERINGATAN: Memulihkan database SQLite akan menimpa data yang sedang berjalan. Lanjutkan?')) {
      e.target.value = '';
      return;
    }

    const formData = new FormData();
    formData.append('database_file', file);

    setRestoringDb(true);
    try {
      const res = await api.restoreDb(formData);
      showToast(res.message, 'success');
      await fetchInfo();
      await refreshGlobalData();
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setRestoringDb(false);
      e.target.value = '';
    }
  };

  const handleRestoreJson = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    if (!window.confirm('PERINGATAN: Memulihkan data dari JSON akan memperbarui tabel yang ada. Lanjutkan?')) {
      e.target.value = '';
      return;
    }

    setRestoringJson(true);
    try {
      const reader = new FileReader();
      reader.onload = async (event) => {
        try {
          const json = JSON.parse(event.target.result);
          if (!json.data) throw new Error('File JSON tidak memiliki struktur data cadangan yang valid.');
          const res = await api.restoreJson(json.data);
          showToast(res.message, 'success');
          await fetchInfo();
          await refreshGlobalData();
        } catch (err) {
          showToast('File JSON tidak valid: ' + err.message, 'error');
        } finally {
          setRestoringJson(false);
          e.target.value = '';
        }
      };
      reader.readAsText(file);
    } catch (err) {
      showToast(err.message, 'error');
      setRestoringJson(false);
      e.target.value = '';
    }
  };

  const handleResetDemo = async () => {
    if (!window.confirm('PERINGATAN KERAS: Semua data ujian saat ini akan digantikan dengan data awal demo sekolah. Tindakan ini tidak dapat dibatalkan. Lanjutkan?')) {
      return;
    }

    setResetting(true);
    try {
      const res = await api.resetDemoData();
      showToast(res.message, 'success');
      await fetchInfo();
      await refreshGlobalData();
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setResetting(false);
    }
  };

  return (
    <div className="space-y-6 animate-fade-in">
      <div>
        <h1 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
          <Database className="w-6 h-6 text-blue-600" />
          <span>Backup &amp; Restore Sistem</span>
        </h1>
        <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
          Cadangkan seluruh basis data SQLite sekolah secara aman atau pulihkan dari file cadangan kapan saja
        </p>
      </div>

      {/* Database Status Card */}
      <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div className="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
              <HardDrive className="w-5 h-5" />
            </div>
            <div>
              <h3 className="font-bold text-slate-900 text-sm">Status Database SQLite</h3>
              <p className="text-xs text-slate-500">File Engine: <strong className="font-mono text-slate-700">better-sqlite3 (WAL Mode)</strong></p>
            </div>
          </div>
          <div className="text-right">
            <span className="text-xs font-mono font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
              Database Online &bull; {info?.fileSize || '0 KB'}
            </span>
          </div>
        </div>

        {/* Record Counts Grid */}
        <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 text-center">
          <div className="p-3 bg-slate-50 rounded-2xl border border-slate-100">
            <p className="text-lg font-extrabold text-blue-700 font-mono">{info?.counts?.peserta || 0}</p>
            <p className="text-[10px] text-slate-500 font-semibold uppercase">Peserta Ujian</p>
          </div>
          <div className="p-3 bg-slate-50 rounded-2xl border border-slate-100">
            <p className="text-lg font-extrabold text-slate-800 font-mono">{info?.counts?.kelas || 0}</p>
            <p className="text-[10px] text-slate-500 font-semibold uppercase">Kelas</p>
          </div>
          <div className="p-3 bg-slate-50 rounded-2xl border border-slate-100">
            <p className="text-lg font-extrabold text-slate-800 font-mono">{info?.counts?.mapel || 0}</p>
            <p className="text-[10px] text-slate-500 font-semibold uppercase">Mata Pelajaran</p>
          </div>
          <div className="p-3 bg-slate-50 rounded-2xl border border-slate-100">
            <p className="text-lg font-extrabold text-slate-800 font-mono">{info?.counts?.ruangan || 0}</p>
            <p className="text-[10px] text-slate-500 font-semibold uppercase">Ruangan</p>
          </div>
          <div className="p-3 bg-slate-50 rounded-2xl border border-slate-100">
            <p className="text-lg font-extrabold text-slate-800 font-mono">{info?.counts?.panitia || 0}</p>
            <p className="text-[10px] text-slate-500 font-semibold uppercase">Panitia</p>
          </div>
          <div className="p-3 bg-slate-50 rounded-2xl border border-slate-100">
            <p className="text-lg font-extrabold text-slate-800 font-mono">{info?.counts?.pengawas || 0}</p>
            <p className="text-[10px] text-slate-500 font-semibold uppercase">Pengawas</p>
          </div>
        </div>
      </div>

      {/* Backup and Restore Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {/* Backup Card */}
        <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4">
          <div>
            <div className="flex items-center gap-2.5 text-blue-700 font-bold text-sm mb-1">
              <Download className="w-5 h-5" />
              <span>1. Ekspor &amp; Unduh Cadangan Data (Backup)</span>
            </div>
            <p className="text-xs text-slate-500 leading-relaxed">
              Unduh cadangan data untuk disimpan di komputer lokal, flashdisk, atau Google Drive Anda. Tersedia format biner SQLite asli dan format JSON portabel.
            </p>
          </div>

          <div className="space-y-3 pt-2">
            <button
              onClick={handleDownloadDb}
              className="w-full p-4 rounded-2xl bg-blue-50 hover:bg-blue-100 text-blue-900 border border-blue-200 flex items-center justify-between text-xs font-bold transition-all shadow-2xs hover:scale-[1.01]"
            >
              <div className="flex items-center gap-3">
                <Database className="w-5 h-5 text-blue-600 shrink-0" />
                <div className="text-left">
                  <span>Unduh Database SQLite Penuh (.sqlite)</span>
                  <p className="text-[10px] text-blue-600 font-normal">File asli database ruang_ujian.db</p>
                </div>
              </div>
              <Download className="w-4 h-4 text-blue-600" />
            </button>

            <button
              onClick={handleDownloadJson}
              className="w-full p-4 rounded-2xl bg-purple-50 hover:bg-purple-100 text-purple-900 border border-purple-200 flex items-center justify-between text-xs font-bold transition-all shadow-2xs hover:scale-[1.01]"
            >
              <div className="flex items-center gap-3">
                <FileJson className="w-5 h-5 text-purple-600 shrink-0" />
                <div className="text-left">
                  <span>Unduh Format JSON Portabel (.json)</span>
                  <p className="text-[10px] text-purple-600 font-normal">Dapat dibuka &amp; dibaca di semua platform</p>
                </div>
              </div>
              <Download className="w-4 h-4 text-purple-600" />
            </button>
          </div>
        </div>

        {/* Restore Card */}
        <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4">
          <div>
            <div className="flex items-center gap-2.5 text-amber-700 font-bold text-sm mb-1">
              <Upload className="w-5 h-5" />
              <span>2. Pulihkan Data (Restore)</span>
            </div>
            <p className="text-xs text-slate-500 leading-relaxed">
              Unggah file cadangan (.sqlite atau .json) yang telah disimpan sebelumnya untuk mengembalikan seluruh pengaturan, peserta, dan jadwal ujian.
            </p>
          </div>

          <div className="space-y-3 pt-2">
            <label className="cursor-pointer w-full p-4 rounded-2xl bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 flex items-center justify-between text-xs font-bold transition-all shadow-2xs hover:scale-[1.01]">
              <div className="flex items-center gap-3">
                <Database className="w-5 h-5 text-amber-600 shrink-0" />
                <div className="text-left">
                  <span>{restoringDb ? 'Memulihkan Database...' : 'Unggah & Pulihkan File SQLite (.sqlite)'}</span>
                  <p className="text-[10px] text-amber-600 font-normal">Menimpa database aktif dengan file cadangan</p>
                </div>
              </div>
              <Upload className="w-4 h-4 text-amber-600" />
              <input
                type="file"
                accept=".sqlite,.db"
                onChange={handleRestoreDb}
                className="hidden"
                disabled={restoringDb}
              />
            </label>

            <label className="cursor-pointer w-full p-4 rounded-2xl bg-teal-50 hover:bg-teal-100 text-teal-900 border border-teal-200 flex items-center justify-between text-xs font-bold transition-all shadow-2xs hover:scale-[1.01]">
              <div className="flex items-center gap-3">
                <FileJson className="w-5 h-5 text-teal-600 shrink-0" />
                <div className="text-left">
                  <span>{restoringJson ? 'Memproses JSON...' : 'Unggah & Pulihkan File JSON (.json)'}</span>
                  <p className="text-[10px] text-teal-600 font-normal">Memulihkan data secara transaksional</p>
                </div>
              </div>
              <Upload className="w-4 h-4 text-teal-600" />
              <input
                type="file"
                accept=".json"
                onChange={handleRestoreJson}
                className="hidden"
                disabled={restoringJson}
              />
            </label>
          </div>
        </div>
      </div>

      {/* Demo Reset Card */}
      <div className="bg-rose-50/60 rounded-3xl p-6 border border-rose-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div className="flex items-center gap-3.5">
          <div className="w-11 h-11 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
            <ShieldAlert className="w-6 h-6" />
          </div>
          <div>
            <h4 className="font-bold text-slate-900 text-sm">Reset Data Ujian ke Awal (Demo Sekolah)</h4>
            <p className="text-xs text-slate-500 mt-0.5">
              Mengembalikan seluruh data simulasi sekolah (SMA Negeri 1 Merdeka Belajar, 20 peserta, ruang, dan jadwal).
            </p>
          </div>
        </div>
        <button
          onClick={handleResetDemo}
          disabled={resetting}
          className="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition-all shrink-0 hover:scale-[1.02] active:scale-95"
        >
          {resetting ? 'Mereset...' : 'Reset ke Data Demo'}
        </button>
      </div>
    </div>
  );
}
