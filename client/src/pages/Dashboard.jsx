import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import {
  Users, GraduationCap, BookOpen, LayoutGrid, Award,
  ShieldCheck, Calendar, Clock, CheckCircle2, AlertCircle,
  ArrowRight, Printer, Sparkles, Download, Layers,
  ChevronRight, Building2, TrendingUp, AlertTriangle
} from 'lucide-react';

export default function Dashboard() {
  const { setActiveTab, sekolah, ujian, isInstallable, installApp } = useApp();
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchStats = async () => {
    try {
      const res = await api.getDashboardStats();
      if (res.success) {
        setStats(res);
      }
    } catch (err) {
      console.error('Failed to fetch dashboard stats:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchStats();
  }, []);

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[60vh]">
        <div className="flex flex-col items-center gap-3">
          <div className="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
          <p className="text-sm text-slate-500 font-medium">Memuat statistik ujian...</p>
        </div>
      </div>
    );
  }

  const metrics = stats?.metrics;

  return (
    <div className="space-y-6 animate-fade-in">
      {/* 1. Hero Welcome Banner */}
      <div className="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-800 text-white p-6 sm:p-8 shadow-xl shadow-blue-600/10">
        <div className="relative z-10 max-w-3xl">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-white text-xs font-semibold mb-3 border border-white/20">
            <Sparkles className="w-3.5 h-3.5 text-amber-300" />
            <span>Tahun Ajaran {sekolah?.tahun_ajaran || '2024/2025'} &bull; Semester {sekolah?.semester || 'Genap'}</span>
          </div>
          <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight">
            Selamat Datang di Ruang Ujian
          </h1>
          <p className="mt-2 text-sm sm:text-base text-blue-100 leading-relaxed max-w-2xl">
            {ujian?.nama_ujian || 'Pelaksanaan Ujian Sekolah Terpadu'}. Kelola seluruh tahapan administrasi, master data, jadwal, persuratan kedinasan, hingga 9 format cetak resmi dokumen ujian sekolah secara terpadu.
          </p>

          {/* Quick Action Buttons */}
          <div className="mt-6 flex flex-wrap items-center gap-3">
            <button
              onClick={() => setActiveTab('cetak-kartu-peserta')}
              className="px-4 py-2.5 rounded-xl bg-white text-blue-700 hover:bg-blue-50 font-bold text-xs shadow-md transition-all flex items-center gap-2 hover:scale-[1.02] active:scale-95"
            >
              <Printer className="w-4 h-4 text-blue-600" />
              <span>Cetak Kartu Peserta</span>
            </button>
            <button
              onClick={() => setActiveTab('atur-ruangan')}
              className="px-4 py-2.5 rounded-xl bg-blue-500/30 hover:bg-blue-500/40 text-white font-semibold text-xs border border-white/30 transition-all flex items-center gap-2 backdrop-blur-md"
            >
              <LayoutGrid className="w-4 h-4" />
              <span>Atur Ruangan &amp; Kursi</span>
            </button>
            <button
              onClick={() => setActiveTab('persuratan')}
              className="px-4 py-2.5 rounded-xl bg-blue-500/30 hover:bg-blue-500/40 text-white font-semibold text-xs border border-white/30 transition-all flex items-center gap-2 backdrop-blur-md"
            >
              <span>Buat Surat Tugas &rarr;</span>
            </button>
            {isInstallable && (
              <button
                onClick={installApp}
                className="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-xs shadow-md transition-all flex items-center gap-2"
              >
                <Download className="w-4 h-4" />
                <span>Install Aplikasi Android</span>
              </button>
            )}
          </div>
        </div>

        {/* Decorative Watermark Background */}
        <div className="absolute right-0 bottom-0 translate-x-12 translate-y-12 opacity-10 pointer-events-none hidden lg:block">
          <img src="/icon.svg" alt="Ruang Ujian Watermark" className="w-96 h-96" />
        </div>
      </div>

      {/* Alert if unseated participants */}
      {metrics?.pesertaBelumRuang > 0 && (
        <div className="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 flex items-center justify-between gap-3 shadow-xs">
          <div className="flex items-center gap-3">
            <div className="p-2 bg-amber-100 rounded-xl text-amber-700">
              <AlertTriangle className="w-5 h-5" />
            </div>
            <div>
              <p className="text-xs sm:text-sm font-bold text-amber-900">
                Peringatan: Ada {metrics.pesertaBelumRuang} peserta ujian yang belum ditempatkan ke ruangan &amp; meja!
              </p>
              <p className="text-xs text-amber-700 mt-0.5">
                Gunakan fitur otomatisasi untuk membagi peserta ke ruangan ujian dengan 1 klik.
              </p>
            </div>
          </div>
          <button
            onClick={() => setActiveTab('atur-ruangan')}
            className="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs shadow-xs shrink-0"
          >
            Atur Sekarang &rarr;
          </button>
        </div>
      )}

      {/* 2. Bento KPI Metric Cards */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        {/* Card 1: Total Peserta */}
        <div 
          onClick={() => setActiveTab('data-peserta')}
          className="bg-white p-5 rounded-2xl border border-slate-200/70 shadow-xs hover:shadow-md transition-all cursor-pointer group"
        >
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Peserta Ujian</span>
            <div className="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
              <Users className="w-5 h-5" />
            </div>
          </div>
          <div className="mt-3">
            <p className="text-2xl sm:text-3xl font-extrabold text-slate-900 tabular-nums">
              {metrics?.totalPeserta || 0}
            </p>
            <div className="flex items-center gap-2 mt-1.5 text-xs text-slate-500">
              <span className="text-blue-600 font-semibold">{metrics?.pesertaLaki || 0} L</span>
              <span>&bull;</span>
              <span className="text-pink-600 font-semibold">{metrics?.pesertaPerempuan || 0} P</span>
            </div>
          </div>
        </div>

        {/* Card 2: Kelas & Rombel */}
        <div 
          onClick={() => setActiveTab('data-kelas')}
          className="bg-white p-5 rounded-2xl border border-slate-200/70 shadow-xs hover:shadow-md transition-all cursor-pointer group"
        >
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Kelas</span>
            <div className="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
              <GraduationCap className="w-5 h-5" />
            </div>
          </div>
          <div className="mt-3">
            <p className="text-2xl sm:text-3xl font-extrabold text-slate-900 tabular-nums">
              {metrics?.totalKelas || 0}
            </p>
            <p className="mt-1.5 text-xs text-emerald-600 font-medium flex items-center gap-1">
              <span>Rombongan Belajar Aktif</span>
            </p>
          </div>
        </div>

        {/* Card 3: Mata Pelajaran */}
        <div 
          onClick={() => setActiveTab('data-mapel')}
          className="bg-white p-5 rounded-2xl border border-slate-200/70 shadow-xs hover:shadow-md transition-all cursor-pointer group"
        >
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Mata Pelajaran</span>
            <div className="w-10 h-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
              <BookOpen className="w-5 h-5" />
            </div>
          </div>
          <div className="mt-3">
            <p className="text-2xl sm:text-3xl font-extrabold text-slate-900 tabular-nums">
              {(metrics?.totalMapelTulis || 0) + (metrics?.totalMapelPraktik || 0)}
            </p>
            <div className="flex items-center gap-2 mt-1.5 text-xs text-slate-500">
              <span className="font-semibold text-slate-700">{metrics?.totalMapelTulis || 0} Tulis</span>
              <span>&bull;</span>
              <span className="font-semibold text-slate-700">{metrics?.totalMapelPraktik || 0} Praktik</span>
            </div>
          </div>
        </div>

        {/* Card 4: Ruangan Ujian */}
        <div 
          onClick={() => setActiveTab('atur-ruangan')}
          className="bg-white p-5 rounded-2xl border border-slate-200/70 shadow-xs hover:shadow-md transition-all cursor-pointer group"
        >
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ruangan Ujian</span>
            <div className="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
              <LayoutGrid className="w-5 h-5" />
            </div>
          </div>
          <div className="mt-3">
            <p className="text-2xl sm:text-3xl font-extrabold text-slate-900 tabular-nums">
              {metrics?.totalRuang || 0}
            </p>
            <p className="mt-1.5 text-xs text-slate-500">
              Kapasitas: <strong className="text-slate-800">{metrics?.kapasitasTotal || 0} Kursi</strong>
            </p>
          </div>
        </div>
      </div>

      {/* 3. Middle Section: Progress Proker & Personel Panitia */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Progress Proker Card */}
        <div className="bg-white p-6 rounded-3xl border border-slate-200/70 shadow-xs">
          <div className="flex items-center justify-between mb-4">
            <h3 className="font-bold text-slate-800 text-sm flex items-center gap-2">
              <Layers className="w-4 h-4 text-blue-600" />
              <span>Program Kerja Panitia</span>
            </h3>
            <button
              onClick={() => setActiveTab('program-kerja')}
              className="text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-0.5"
            >
              <span>Detail</span>
              <ChevronRight className="w-3.5 h-3.5" />
            </button>
          </div>

          <div className="space-y-4">
            <div>
              <div className="flex items-center justify-between text-xs mb-1.5">
                <span className="text-slate-500">Keterlaksanaan Kegiatan</span>
                <span className="font-bold text-slate-900">{metrics?.proker?.persentase || 0}% Selesai</span>
              </div>
              <div className="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                <div 
                  className="h-full bg-gradient-to-r from-blue-600 to-emerald-500 rounded-full transition-all duration-500"
                  style={{ width: `${metrics?.proker?.persentase || 0}%` }}
                ></div>
              </div>
            </div>

            <div className="grid grid-cols-3 gap-2 pt-2 text-center">
              <div className="p-2.5 rounded-xl bg-emerald-50 border border-emerald-100">
                <p className="text-base font-bold text-emerald-700">{metrics?.proker?.selesai || 0}</p>
                <p className="text-[10px] text-emerald-600 font-medium">Selesai</p>
              </div>
              <div className="p-2.5 rounded-xl bg-amber-50 border border-amber-100">
                <p className="text-base font-bold text-amber-700">{metrics?.proker?.sedang || 0}</p>
                <p className="text-[10px] text-amber-600 font-medium">Sedang</p>
              </div>
              <div className="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                <p className="text-base font-bold text-slate-700">{metrics?.proker?.belum || 0}</p>
                <p className="text-[10px] text-slate-500 font-medium">Belum</p>
              </div>
            </div>

            <div className="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
              <span className="text-slate-600 font-medium">Total Anggaran RAB:</span>
              <strong className="text-slate-900 font-bold font-mono">
                Rp {(metrics?.proker?.totalAnggaran || 0).toLocaleString('id-ID')}
              </strong>
            </div>
          </div>
        </div>

        {/* Personel Panitia & Pengawas */}
        <div className="bg-white p-6 rounded-3xl border border-slate-200/70 shadow-xs">
          <h3 className="font-bold text-slate-800 text-sm mb-4 flex items-center gap-2">
            <Award className="w-4 h-4 text-purple-600" />
            <span>Personel Ujian Sekolah</span>
          </h3>

          <div className="space-y-3">
            <div 
              onClick={() => setActiveTab('data-panitia')}
              className="p-3.5 rounded-2xl border border-slate-100 hover:border-slate-200 bg-slate-50/50 hover:bg-slate-50 flex items-center justify-between cursor-pointer transition-colors"
            >
              <div className="flex items-center gap-3">
                <div className="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                  P
                </div>
                <div>
                  <p className="text-xs font-bold text-slate-800">Panitia Pelaksana</p>
                  <p className="text-[11px] text-slate-500">Ketua, Sekretaris, Bendahara, Seksi</p>
                </div>
              </div>
              <span className="text-sm font-extrabold text-blue-600">{metrics?.totalPanitia || 0} Orang</span>
            </div>

            <div 
              onClick={() => setActiveTab('data-pengawas')}
              className="p-3.5 rounded-2xl border border-slate-100 hover:border-slate-200 bg-slate-50/50 hover:bg-slate-50 flex items-center justify-between cursor-pointer transition-colors"
            >
              <div className="flex items-center gap-3">
                <div className="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm">
                  W
                </div>
                <div>
                  <p className="text-xs font-bold text-slate-800">Pengawas Ruang</p>
                  <p className="text-[11px] text-slate-500">Guru pengawas ruang ujian tulis</p>
                </div>
              </div>
              <span className="text-sm font-extrabold text-emerald-600">{metrics?.totalPengawas || 0} Orang</span>
            </div>

            <div 
              onClick={() => setActiveTab('data-penguji')}
              className="p-3.5 rounded-2xl border border-slate-100 hover:border-slate-200 bg-slate-50/50 hover:bg-slate-50 flex items-center justify-between cursor-pointer transition-colors"
            >
              <div className="flex items-center gap-3">
                <div className="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-sm">
                  U
                </div>
                <div>
                  <p className="text-xs font-bold text-slate-800">Penguji Praktik</p>
                  <p className="text-[11px] text-slate-500">Penguji internal &amp; eksternal DU/DI</p>
                </div>
              </div>
              <span className="text-sm font-extrabold text-amber-600">{metrics?.totalPenguji || 0} Orang</span>
            </div>
          </div>
        </div>

        {/* Pusat Cetak 9 Dokumen Cepat */}
        <div className="bg-white p-6 rounded-3xl border border-slate-200/70 shadow-xs">
          <div className="flex items-center justify-between mb-4">
            <h3 className="font-bold text-slate-800 text-sm flex items-center gap-2">
              <Printer className="w-4 h-4 text-emerald-600" />
              <span>Akses Cepat Dokumen Cetak</span>
            </h3>
            <span className="text-[10px] bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full font-semibold">
              9 Dokumen
            </span>
          </div>

          <div className="grid grid-cols-2 gap-2 text-xs">
            <button
              onClick={() => setActiveTab('cetak-kartu-peserta')}
              className="p-2.5 rounded-xl border border-slate-200/80 hover:bg-blue-50 hover:border-blue-200 text-left transition-colors flex flex-col gap-1"
            >
              <span className="font-bold text-slate-800 truncate">1. Kartu Peserta</span>
              <span className="text-[10px] text-slate-500">QR Code + Foto</span>
            </button>
            <button
              onClick={() => setActiveTab('cetak-kartu-panitia')}
              className="p-2.5 rounded-xl border border-slate-200/80 hover:bg-blue-50 hover:border-blue-200 text-left transition-colors flex flex-col gap-1"
            >
              <span className="font-bold text-slate-800 truncate">2. Kartu Panitia</span>
              <span className="text-[10px] text-slate-500">ID Lanyard</span>
            </button>
            <button
              onClick={() => setActiveTab('cetak-jadwal')}
              className="p-2.5 rounded-xl border border-slate-200/80 hover:bg-blue-50 hover:border-blue-200 text-left transition-colors flex flex-col gap-1"
            >
              <span className="font-bold text-slate-800 truncate">3. Jadwal Ujian</span>
              <span className="text-[10px] text-slate-500">Format Mading</span>
            </button>
            <button
              onClick={() => setActiveTab('cetak-nomor-ruangan')}
              className="p-2.5 rounded-xl border border-slate-200/80 hover:bg-blue-50 hover:border-blue-200 text-left transition-colors flex flex-col gap-1"
            >
              <span className="font-bold text-slate-800 truncate">4. Nomor Ruang</span>
              <span className="text-[10px] text-slate-500">Plang Pintu</span>
            </button>
            <button
              onClick={() => setActiveTab('cetak-daftar-peserta')}
              className="p-2.5 rounded-xl border border-slate-200/80 hover:bg-blue-50 hover:border-blue-200 text-left transition-colors flex flex-col gap-1"
            >
              <span className="font-bold text-slate-800 truncate">5. Daftar Nominasi</span>
              <span className="text-[10px] text-slate-500">DNT Resmi</span>
            </button>
            <button
              onClick={() => setActiveTab('cetak-denah-duduk')}
              className="p-2.5 rounded-xl border border-slate-200/80 hover:bg-blue-50 hover:border-blue-200 text-left transition-colors flex flex-col gap-1"
            >
              <span className="font-bold text-slate-800 truncate">6. Denah Kursi</span>
              <span className="text-[10px] text-slate-500">Visual Grid 2D</span>
            </button>
            <button
              onClick={() => setActiveTab('cetak-daftar-hadir')}
              className="p-2.5 rounded-xl border border-slate-200/80 hover:bg-blue-50 hover:border-blue-200 text-left transition-colors flex flex-col gap-1"
            >
              <span className="font-bold text-slate-800 truncate">7. Daftar Hadir</span>
              <span className="text-[10px] text-slate-500">4 Kategori Form</span>
            </button>
            <button
              onClick={() => setActiveTab('cetak-berita-acara')}
              className="p-2.5 rounded-xl border border-slate-200/80 hover:bg-blue-50 hover:border-blue-200 text-left transition-colors flex flex-col gap-1"
            >
              <span className="font-bold text-slate-800 truncate">8. Berita Acara</span>
              <span className="text-[10px] text-slate-500">Format Resmi</span>
            </button>
            <button
              onClick={() => setActiveTab('cetak-lpj')}
              className="col-span-2 p-2.5 rounded-xl bg-blue-50 border border-blue-200 hover:bg-blue-100 text-left transition-colors flex items-center justify-between"
            >
              <div>
                <span className="font-bold text-blue-900 block">9. LPJ Kegiatan Ujian Lengkap</span>
                <span className="text-[10px] text-blue-700">Cover, SK, Evaluasi, &amp; Laporan Keuangan RAB</span>
              </div>
              <ArrowRight className="w-4 h-4 text-blue-600" />
            </button>
          </div>
        </div>
      </div>

      {/* 4. Ruangan Ujian & Jadwal Section */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Status Ruangan Ujian */}
        <div className="bg-white p-6 rounded-3xl border border-slate-200/70 shadow-xs">
          <div className="flex items-center justify-between mb-4">
            <h3 className="font-bold text-slate-800 text-sm flex items-center gap-2">
              <Building2 className="w-4 h-4 text-blue-600" />
              <span>Status Keterisian Ruangan Ujian</span>
            </h3>
            <button
              onClick={() => setActiveTab('atur-ruangan')}
              className="text-xs text-blue-600 hover:text-blue-700 font-semibold"
            >
              Kelola Ruang &rarr;
            </button>
          </div>

          <div className="space-y-3">
            {stats?.ruanganList?.map((room) => {
              const percentage = room.kapasitas > 0 ? Math.round((room.terisi / room.kapasitas) * 100) : 0;
              const isFull = room.terisi >= room.kapasitas;
              return (
                <div key={room.id} className="p-3.5 rounded-2xl border border-slate-100 bg-slate-50/50">
                  <div className="flex items-center justify-between text-xs mb-1.5">
                    <div>
                      <strong className="text-slate-800 font-bold">{room.nama_ruang}</strong>
                      <span className="text-[11px] text-slate-500 ml-2">({room.kode_ruang})</span>
                    </div>
                    <span className="text-xs font-semibold text-slate-600">
                      <strong className="text-blue-600">{room.terisi}</strong> / {room.kapasitas} Kursi
                    </span>
                  </div>
                  <div className="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                    <div 
                      className={`h-full rounded-full transition-all ${
                        isFull ? 'bg-emerald-500' : 'bg-blue-600'
                      }`}
                      style={{ width: `${Math.min(percentage, 100)}%` }}
                    ></div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Jadwal Ujian Tulis Mendatang */}
        <div className="bg-white p-6 rounded-3xl border border-slate-200/70 shadow-xs">
          <div className="flex items-center justify-between mb-4">
            <h3 className="font-bold text-slate-800 text-sm flex items-center gap-2">
              <Calendar className="w-4 h-4 text-blue-600" />
              <span>Jadwal Ujian Tulis</span>
            </h3>
            <button
              onClick={() => setActiveTab('jadwal-tulis')}
              className="text-xs text-blue-600 hover:text-blue-700 font-semibold"
            >
              Lihat Semua &rarr;
            </button>
          </div>

          <div className="space-y-2.5">
            {stats?.jadwalTulis?.length > 0 ? (
              stats.jadwalTulis.map((j) => (
                <div key={j.id} className="p-3 rounded-2xl border border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs">
                  <div>
                    <p className="font-bold text-slate-800">{j.nama_mapel}</p>
                    <p className="text-[11px] text-slate-500 mt-0.5">
                      {j.hari_tanggal} &bull; Sesi {j.sesi} ({j.jam_mulai} - {j.jam_selesai})
                    </p>
                  </div>
                  <div className="text-right">
                    <span className="text-[10px] font-semibold bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full border border-blue-100">
                      {j.kelas_tingkat}
                    </span>
                  </div>
                </div>
              ))
            ) : (
              <p className="text-xs text-slate-500 text-center py-6">Belum ada jadwal ujian tulis yang ditambahkan.</p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
