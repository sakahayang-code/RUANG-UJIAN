import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import QRCode from 'qrcode';
import {
  Printer, IdCard, Award, Calendar, DoorOpen, ListOrdered,
  LayoutGrid, FileSpreadsheet, FileCheck, Layers, Filter,
  CheckCircle2, Download, Search, Edit, Plus, Trash2, Eye
} from 'lucide-react';
import KopSurat from '../components/KopSurat';
import Modal from '../components/Modal';

export default function CetakCenter({ initialTab }) {
  const { sekolah, ujian, showToast, activeTab, setActiveTab } = useApp();

  // Selected cetak sub-tab
  const [currentTab, setCurrentTab] = useState(initialTab || 'kartu-peserta');

  // Shared filter states
  const [kelasList, setKelasList] = useState([]);
  const [ruangList, setRuangList] = useState([]);
  const [mapelList, setMapelList] = useState([]);
  const [selectedKelas, setSelectedKelas] = useState('');
  const [selectedRuang, setSelectedRuang] = useState('');
  const [selectedMapel, setSelectedMapel] = useState('');
  const [selectedSesi, setSelectedSesi] = useState('1');

  // Data states
  const [loading, setLoading] = useState(false);
  const [kartuPesertaData, setKartuPesertaData] = useState([]);
  const [kartuPanitiaData, setKartuPanitiaData] = useState([]);
  const [jadwalData, setJadwalData] = useState({ jadwal_tulis: [], jadwal_praktik: [] });
  const [nomorRuangData, setNomorRuangData] = useState([]);
  const [daftarPesertaData, setDaftarPesertaData] = useState([]);
  const [denahDudukData, setDenahDudukData] = useState([]);
  const [daftarHadirType, setDaftarHadirType] = useState('peserta'); // 'peserta', 'panitia', 'pengawas', 'penguji'
  const [daftarHadirData, setDaftarHadirData] = useState([]);
  const [beritaAcaraList, setBeritaAcaraList] = useState([]);
  const [lpjData, setLpjData] = useState(null);

  // QR Codes cached map: { [pesertaId]: dataUrl }
  const [qrCodeMap, setQrCodeMap] = useState({});

  // Berita Acara Form Modal
  const [baModalOpen, setBaModalOpen] = useState(false);
  const [baForm, setBaForm] = useState({
    tanggal: new Date().toISOString().split('T')[0],
    ruang_id: '',
    sesi: 1,
    mapel_id: '',
    jumlah_seharusnya: 20,
    jumlah_hadir: 20,
    jumlah_tidak_hadir: 0,
    nomor_tidak_hadir: '-',
    catatan_kejadian: 'Pelaksanaan ujian tertib, aman, dan lancar.',
    pengawas_1_nama: '',
    pengawas_1_nip: '',
    pengawas_2_nama: '',
    pengawas_2_nip: ''
  });

  // Sync tab from global activeTab if matches cetak-*
  useEffect(() => {
    if (activeTab.startsWith('cetak-')) {
      const sub = activeTab.replace('cetak-', '');
      setCurrentTab(sub);
    }
  }, [activeTab]);

  // Load classes, rooms, and subjects for filtering
  useEffect(() => {
    const loadMaster = async () => {
      try {
        const [kRes, rRes, mRes] = await Promise.all([
          api.getKelas(),
          api.getRuangan(),
          api.getMapel()
        ]);
        if (kRes.success) setKelasList(kRes.data);
        if (rRes.success) setRuangList(rRes.data);
        if (mRes.success) setMapelList(mRes.data);
      } catch (err) {
        console.error(err);
      }
    };
    loadMaster();
  }, []);

  // Fetch data depending on active sub-tab
  const fetchTabData = async () => {
    setLoading(true);
    try {
      if (currentTab === 'kartu-peserta') {
        const res = await api.getCetakKartuPeserta({
          kelas_id: selectedKelas,
          ruang_id: selectedRuang
        });
        if (res.success) {
          setKartuPesertaData(res.data);
          // Generate QR codes for each student
          const newQrMap = {};
          for (const s of res.data) {
            const qrText = `RUANG-UJIAN|${s.no_peserta}|${s.nama}|${s.nama_kelas}|PIN:${s.password}`;
            const qrUrl = await QRCode.toDataURL(qrText, { width: 100, margin: 1 });
            newQrMap[s.id] = qrUrl;
          }
          setQrCodeMap(newQrMap);
        }
      } else if (currentTab === 'kartu-panitia') {
        const res = await api.getCetakKartuPanitia();
        if (res.success) setKartuPanitiaData(res.data);
      } else if (currentTab === 'jadwal') {
        const res = await api.getCetakJadwal();
        if (res.success) setJadwalData(res);
      } else if (currentTab === 'nomor-ruangan') {
        const res = await api.getCetakNomorRuang();
        if (res.success) setNomorRuangData(res.data);
      } else if (currentTab === 'daftar-peserta') {
        const res = await api.getCetakDaftarPeserta({
          filter_by: selectedRuang ? 'ruang' : (selectedKelas ? 'kelas' : ''),
          filter_id: selectedRuang || selectedKelas || ''
        });
        if (res.success) setDaftarPesertaData(res.data);
      } else if (currentTab === 'denah-duduk') {
        const res = await api.getCetakDenahDuduk(selectedRuang || '');
        if (res.success) setDenahDudukData(res.data);
      } else if (currentTab === 'daftar-hadir') {
        if (daftarHadirType === 'panitia') {
          const res = await api.getCetakDaftarHadirPanitia();
          if (res.success) setDaftarHadirData(res.data);
        } else if (daftarHadirType === 'peserta') {
          const res = await api.getCetakDaftarHadirPeserta({
            ruang_id: selectedRuang,
            mapel_id: selectedMapel,
            sesi: selectedSesi
          });
          if (res.success) setDaftarHadirData(res);
        } else if (daftarHadirType === 'pengawas') {
          const res = await api.getCetakDaftarHadirPengawas();
          if (res.success) setDaftarHadirData(res);
        } else if (daftarHadirType === 'penguji') {
          const res = await api.getCetakDaftarHadirPenguji();
          if (res.success) setDaftarHadirData(res);
        }
      } else if (currentTab === 'berita-acara') {
        const res = await api.getBeritaAcara();
        if (res.success) setBeritaAcaraList(res.data);
      } else if (currentTab === 'lpj') {
        const res = await api.getLPJ();
        if (res.success) setLpjData(res);
      }
    } catch (err) {
      showToast('Gagal memuat data cetak: ' + err.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchTabData();
  }, [currentTab, selectedKelas, selectedRuang, selectedMapel, selectedSesi, daftarHadirType]);

  const cetakTabs = [
    { key: 'kartu-peserta', label: '1. Kartu Peserta', icon: IdCard },
    { key: 'kartu-panitia', label: '2. Kartu Panitia', icon: Award },
    { key: 'jadwal', label: '3. Jadwal (Tulis & Praktik)', icon: Calendar },
    { key: 'nomor-ruangan', label: '4. Nomor Ruangan Ujian', icon: DoorOpen },
    { key: 'daftar-peserta', label: '5. Daftar Peserta (DNT)', icon: ListOrdered },
    { key: 'denah-duduk', label: '6. Denah Tempat Duduk', icon: LayoutGrid },
    { key: 'daftar-hadir', label: '7. Daftar Hadir (4 Form)', icon: FileSpreadsheet },
    { key: 'berita-acara', label: '8. Berita Acara Pelaksanaan', icon: FileCheck },
    { key: 'lpj', label: '9. LPJ Kegiatan Ujian', icon: Layers }
  ];

  const handlePrint = () => {
    window.print();
  };

  // Berita Acara save
  const handleSaveBA = async (e) => {
    e.preventDefault();
    try {
      await api.createBeritaAcara(baForm);
      showToast('Berita acara berhasil disimpan!', 'success');
      setBaModalOpen(false);
      fetchTabData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  return (
    <div className="space-y-6">
      {/* Top Header Controls (No print) */}
      <div className="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <Printer className="w-6 h-6 text-blue-600" />
            <span>Pusat Cetak Dokumen Resmi Ujian</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
            Format cetak kertas standar A4/F4 Indonesia dengan kop surat resmi &amp; tanda tangan
          </p>
        </div>
        <div className="flex items-center gap-2 self-start sm:self-auto">
          <button
            onClick={handlePrint}
            className="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-blue-500/25 transition-all hover:scale-[1.02] active:scale-95"
          >
            <Printer className="w-4 h-4" />
            <span>Cetak / Cetak PDF (A4)</span>
          </button>
        </div>
      </div>

      {/* Cetak 9 Tab Pills (No print) */}
      <div className="no-print flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
        {cetakTabs.map(tab => {
          const Icon = tab.icon;
          const isSelected = currentTab === tab.key;
          return (
            <button
              key={tab.key}
              onClick={() => {
                setCurrentTab(tab.key);
                setActiveTab(`cetak-${tab.key}`);
              }}
              className={`flex items-center gap-2 px-3.5 py-2 rounded-2xl text-xs font-bold transition-all shrink-0 border ${
                isSelected
                  ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-600/20'
                  : 'bg-white text-slate-700 border-slate-200/80 hover:border-slate-300'
              }`}
            >
              <Icon className="w-4 h-4" />
              <span>{tab.label}</span>
            </button>
          );
        })}
      </div>

      {/* Filter Toolbar (No print) */}
      <div className="no-print bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <div className="flex flex-wrap items-center gap-3">
          {/* Kelas filter */}
          {(currentTab === 'kartu-peserta' || currentTab === 'daftar-peserta') && (
            <select
              value={selectedKelas}
              onChange={(e) => setSelectedKelas(e.target.value)}
              className="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-medium bg-white text-slate-700 focus:ring-2 focus:ring-blue-500/20"
            >
              <option value="">Semua Kelas</option>
              {kelasList.map(k => (
                <option key={k.id} value={k.id}>{k.nama_kelas}</option>
              ))}
            </select>
          )}

          {/* Ruangan filter */}
          {(currentTab === 'kartu-peserta' || currentTab === 'daftar-peserta' || currentTab === 'denah-duduk' || (currentTab === 'daftar-hadir' && daftarHadirType === 'peserta')) && (
            <select
              value={selectedRuang}
              onChange={(e) => setSelectedRuang(e.target.value)}
              className="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-medium bg-white text-slate-700 focus:ring-2 focus:ring-blue-500/20"
            >
              <option value="">{currentTab === 'denah-duduk' ? 'Pilih Ruangan...' : 'Semua Ruangan'}</option>
              {ruangList.map(r => (
                <option key={r.id} value={r.id}>{r.nama_ruang} ({r.kode_ruang})</option>
              ))}
            </select>
          )}

          {/* Daftar Hadir Sub-Filter */}
          {currentTab === 'daftar-hadir' && (
            <>
              <div className="flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
                <button
                  onClick={() => setDaftarHadirType('peserta')}
                  className={`px-2.5 py-1 rounded-lg text-xs font-bold transition-all ${
                    daftarHadirType === 'peserta' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600'
                  }`}
                >
                  Peserta
                </button>
                <button
                  onClick={() => setDaftarHadirType('panitia')}
                  className={`px-2.5 py-1 rounded-lg text-xs font-bold transition-all ${
                    daftarHadirType === 'panitia' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600'
                  }`}
                >
                  Panitia
                </button>
                <button
                  onClick={() => setDaftarHadirType('pengawas')}
                  className={`px-2.5 py-1 rounded-lg text-xs font-bold transition-all ${
                    daftarHadirType === 'pengawas' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600'
                  }`}
                >
                  Pengawas
                </button>
                <button
                  onClick={() => setDaftarHadirType('penguji')}
                  className={`px-2.5 py-1 rounded-lg text-xs font-bold transition-all ${
                    daftarHadirType === 'penguji' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600'
                  }`}
                >
                  Penguji Praktik
                </button>
              </div>

              {daftarHadirType === 'peserta' && (
                <>
                  <select
                    value={selectedMapel}
                    onChange={(e) => setSelectedMapel(e.target.value)}
                    className="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-medium bg-white text-slate-700"
                  >
                    <option value="">Pilih Mapel...</option>
                    {mapelList.map(m => (
                      <option key={m.id} value={m.id}>{m.nama_mapel}</option>
                    ))}
                  </select>
                  <select
                    value={selectedSesi}
                    onChange={(e) => setSelectedSesi(e.target.value)}
                    className="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-medium bg-white text-slate-700"
                  >
                    <option value="1">Sesi 1</option>
                    <option value="2">Sesi 2</option>
                    <option value="3">Sesi 3</option>
                  </select>
                </>
              )}
            </>
          )}

          {currentTab === 'berita-acara' && (
            <button
              onClick={() => setBaModalOpen(true)}
              className="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs flex items-center gap-1 shadow-xs"
            >
              <Plus className="w-3.5 h-3.5" />
              <span>Buat Berita Acara Baru</span>
            </button>
          )}
        </div>

        <div className="text-xs text-slate-500 font-medium">
          Format Kertas: <strong className="text-slate-800">A4 Portrait</strong>
        </div>
      </div>

      {/* ========================================================================= */}
      {/* 1. CETAK KARTU PESERTA (Grid 4-Cards per A4 Page) */}
      {/* ========================================================================= */}
      {currentTab === 'kartu-peserta' && (
        <div className="print-container">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 print:grid-cols-2 print:gap-4">
            {kartuPesertaData.map((s, idx) => (
              <div
                key={s.id}
                className="bg-white border-2 border-slate-800 rounded-2xl p-4 shadow-sm relative overflow-hidden avoid-break text-slate-900"
              >
                {/* Mini Kop Kartu */}
                <div className="flex items-center gap-3 border-b-2 border-slate-800 pb-2">
                  <div className="w-12 h-12 shrink-0 flex items-center justify-center">
                    <img src={sekolah?.logo_url || '/icon.svg'} alt="Logo" className="w-10 h-10 object-contain" />
                  </div>
                  <div className="flex-1 text-center font-serif leading-tight">
                    <p className="text-[9px] font-bold uppercase tracking-wider text-slate-700">
                      {sekolah?.kop_baris1 || 'DINAS PENDIDIKAN'}
                    </p>
                    <h4 className="text-xs font-black uppercase text-black leading-none mt-0.5">
                      {sekolah?.nama_sekolah || 'SMA NEGERI 1'}
                    </h4>
                    <p className="text-[10px] font-bold text-blue-700 mt-1 uppercase">
                      KARTU PESERTA UJIAN
                    </p>
                    <p className="text-[8px] font-semibold text-slate-600">
                      TP {sekolah?.tahun_ajaran || '2024/2025'} &bull; Semester {sekolah?.semester || 'Genap'}
                    </p>
                  </div>
                </div>

                {/* Body Kartu */}
                <div className="flex items-center gap-3 mt-3 text-xs">
                  {/* Student Photo Placeholder / Box */}
                  <div className="w-20 h-24 bg-slate-100 border border-slate-300 rounded-xl flex flex-col items-center justify-center text-slate-400 shrink-0 overflow-hidden">
                    <div className="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500 mb-1">
                      {s.jenis_kelamin}
                    </div>
                    <span className="text-[9px] font-semibold uppercase text-slate-400">2 &times; 3</span>
                  </div>

                  {/* Student Details */}
                  <div className="flex-1 text-[11px] leading-tight space-y-1">
                    <div className="flex">
                      <span className="w-20 font-semibold text-slate-600 shrink-0">No. Peserta</span>
                      <span className="font-mono font-bold text-blue-800">: {s.no_peserta}</span>
                    </div>
                    <div className="flex">
                      <span className="w-20 font-semibold text-slate-600 shrink-0">Nama Siswa</span>
                      <span className="font-bold text-black uppercase truncate">: {s.nama}</span>
                    </div>
                    <div className="flex">
                      <span className="w-20 font-semibold text-slate-600 shrink-0">NIS / NISN</span>
                      <span className="font-mono">: {s.nis || '-'} / {s.nisn || '-'}</span>
                    </div>
                    <div className="flex">
                      <span className="w-20 font-semibold text-slate-600 shrink-0">Kelas</span>
                      <span className="font-semibold">: {s.nama_kelas}</span>
                    </div>
                    <div className="flex">
                      <span className="w-20 font-semibold text-slate-600 shrink-0">Ruang / Meja</span>
                      <span className="font-bold text-slate-800">: {s.nama_ruang || 'Ruang 01'} (Meja #{s.no_meja || 1})</span>
                    </div>
                    <div className="flex">
                      <span className="w-20 font-semibold text-slate-600 shrink-0">Sesi Ujian</span>
                      <span className="font-semibold">: Sesi {s.sesi || 1} &bull; PIN: <strong className="font-mono text-blue-700">{s.password || '123456'}</strong></span>
                    </div>
                  </div>
                </div>

                {/* Footer Kartu (QR Code & Signature) */}
                <div className="mt-3 pt-2 border-t border-slate-200 flex items-center justify-between text-[9px]">
                  {/* Authentic QR Code */}
                  <div className="flex items-center gap-1.5">
                    {qrCodeMap[s.id] ? (
                      <img src={qrCodeMap[s.id]} alt="QR" className="w-12 h-12 rounded-xs" />
                    ) : (
                      <div className="w-12 h-12 bg-slate-100 rounded-xs"></div>
                    )}
                    <span className="text-[8px] text-slate-400 font-mono leading-tight">
                      Validasi Resmi<br/>Ruang Ujian
                    </span>
                  </div>

                  {/* Signature */}
                  <div className="text-right">
                    <p>{sekolah?.titimangsa_kota || 'Bandung'}, Mei 2025</p>
                    <p className="font-semibold">Kepala Sekolah,</p>
                    <div className="h-6"></div>
                    <p className="font-bold underline uppercase">{sekolah?.kepala_sekolah || 'Dr. H. Ahmad Fauzi, M.Pd.'}</p>
                    <p className="font-mono">NIP. {sekolah?.nip_kepala_sekolah || '19740512 199903 1 002'}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* ========================================================================= */}
      {/* 2. CETAK KARTU PANITIA (Lanyard ID Card Format) */}
      {/* ========================================================================= */}
      {currentTab === 'kartu-panitia' && (
        <div className="print-container">
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 print:grid-cols-3 print:gap-4">
            {kartuPanitiaData.map((p) => (
              <div
                key={p.id}
                className="bg-white border-2 border-blue-900 rounded-3xl p-5 shadow-sm text-center relative overflow-hidden avoid-break flex flex-col justify-between"
              >
                {/* Lanyard Punch Hole Indicator */}
                <div className="w-8 h-2 rounded-full bg-slate-200 mx-auto mb-3"></div>

                <div>
                  {/* Logo & School Header */}
                  <div className="flex flex-col items-center">
                    <img src={sekolah?.logo_url || '/icon.svg'} alt="Logo" className="w-12 h-12 object-contain mb-1.5" />
                    <p className="text-[9px] font-bold uppercase tracking-wider text-slate-600">
                      {sekolah?.nama_sekolah || 'SMA NEGERI 1 MERDEKA BELAJAR'}
                    </p>
                    <h3 className="text-xs font-black uppercase text-blue-900 mt-0.5">
                      PANITIA PELAKSANA UJIAN
                    </h3>
                    <p className="text-[9px] font-semibold text-slate-500">
                      TP {sekolah?.tahun_ajaran || '2024/2025'}
                    </p>
                  </div>

                  {/* Committee Member Photo Box */}
                  <div className="w-20 h-24 bg-blue-50 border-2 border-blue-200 rounded-2xl mx-auto my-3 flex items-center justify-center text-blue-400 font-bold text-xs shadow-xs">
                    FOTO 3&times;4
                  </div>

                  {/* Details */}
                  <h4 className="font-extrabold text-slate-900 text-sm leading-snug">
                    {p.nama}
                  </h4>
                  <p className="text-[10px] text-slate-500 font-mono mt-0.5">
                    NIP: {p.nip || '-'}
                  </p>

                  <div className="mt-3 inline-block px-3 py-1 rounded-full bg-blue-600 text-white font-extrabold text-xs shadow-xs uppercase tracking-wide">
                    {p.jabatan_panitia}
                  </div>
                </div>

                {/* Footer Barcode */}
                <div className="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[8px] text-slate-400 font-mono">
                  <span>RUANG UJIAN</span>
                  <span>ID #{String(p.id).padStart(4, '0')}</span>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* ========================================================================= */}
      {/* 3. CETAK JADWAL (Tulis & Praktik Format Mading) */}
      {/* ========================================================================= */}
      {currentTab === 'jadwal' && (
        <div className="bg-white p-8 rounded-3xl border border-slate-200/80 shadow-md max-w-4xl mx-auto print-container print:shadow-none print:border-none print:p-0">
          <KopSurat />
          <div className="text-center my-6">
            <h2 className="text-base sm:text-xl font-bold uppercase tracking-wider underline">
              JADWAL RESMI PELAKSANAAN UJIAN TULIS &amp; UJIAN PRAKTIK
            </h2>
            <p className="text-xs font-bold text-slate-700 mt-1 uppercase">
              {ujian?.nama_ujian || 'Penilaian Akhir Tahun (PAT)'} &bull; TAHUN AJARAN {sekolah?.tahun_ajaran || '2024/2025'}
            </p>
          </div>

          {/* Tabel Ujian Tulis */}
          <div className="mb-6">
            <h3 className="font-bold text-xs sm:text-sm text-slate-800 uppercase mb-2 flex items-center gap-1.5">
              <span>A. JADWAL UJIAN TULIS UTAMA</span>
            </h3>
            <table className="w-full text-left border-collapse border border-black text-xs">
              <thead>
                <tr className="bg-slate-100 font-bold border-b border-black text-center">
                  <th className="border border-black p-2 w-8">No</th>
                  <th className="border border-black p-2">Hari &amp; Tanggal</th>
                  <th className="border border-black p-2 w-16">Sesi</th>
                  <th className="border border-black p-2 w-28">Waktu (WIB)</th>
                  <th className="border border-black p-2">Mata Pelajaran</th>
                  <th className="border border-black p-2">Sasaran Kelas</th>
                  <th className="border border-black p-2">Keterangan</th>
                </tr>
              </thead>
              <tbody>
                {jadwalData.jadwal_tulis?.map((j, idx) => (
                  <tr key={j.id} className="border-b border-black">
                    <td className="border border-black p-2 text-center font-mono">{idx + 1}</td>
                    <td className="border border-black p-2 font-bold">{j.hari_tanggal}</td>
                    <td className="border border-black p-2 text-center font-bold">Sesi {j.sesi}</td>
                    <td className="border border-black p-2 text-center font-mono">{j.jam_mulai} - {j.jam_selesai}</td>
                    <td className="border border-black p-2 font-bold">{j.nama_mapel}</td>
                    <td className="border border-black p-2">{j.kelas_tingkat}</td>
                    <td className="border border-black p-2 text-[10px]">{j.keterangan || '-'}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {/* Tabel Ujian Praktik */}
          <div className="mb-8">
            <h3 className="font-bold text-xs sm:text-sm text-slate-800 uppercase mb-2 flex items-center gap-1.5">
              <span>B. JADWAL UJIAN PRAKTIK KEJURUAN / SAINS</span>
            </h3>
            <table className="w-full text-left border-collapse border border-black text-xs">
              <thead>
                <tr className="bg-slate-100 font-bold border-b border-black text-center">
                  <th className="border border-black p-2 w-8">No</th>
                  <th className="border border-black p-2">Hari &amp; Tanggal</th>
                  <th className="border border-black p-2 w-28">Waktu</th>
                  <th className="border border-black p-2">Mata Pelajaran Praktik</th>
                  <th className="border border-black p-2">Tempat / Lab</th>
                  <th className="border border-black p-2">Dewan Penguji</th>
                </tr>
              </thead>
              <tbody>
                {jadwalData.jadwal_praktik?.map((p, idx) => (
                  <tr key={p.id} className="border-b border-black">
                    <td className="border border-black p-2 text-center font-mono">{idx + 1}</td>
                    <td className="border border-black p-2 font-bold">{p.hari_tanggal}</td>
                    <td className="border border-black p-2 text-center font-mono">{p.waktu}</td>
                    <td className="border border-black p-2 font-bold">{p.nama_mapel}</td>
                    <td className="border border-black p-2">{p.tempat || '-'}</td>
                    <td className="border border-black p-2 text-[11px]">
                      {p.penguji_1 && <div>1. {p.penguji_1}</div>}
                      {p.penguji_2 && <div>2. {p.penguji_2}</div>}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {/* Tanda Tangan */}
          <div className="mt-8 flex justify-end text-xs sm:text-sm avoid-break">
            <div className="w-64 text-left">
              <p>{sekolah?.titimangsa_kota || 'Bandung'}, Mei 2025</p>
              <p className="font-semibold">Kepala Sekolah,</p>
              <div className="h-16"></div>
              <p className="font-bold underline uppercase">{sekolah?.kepala_sekolah || 'Dr. H. Ahmad Fauzi, M.Pd.'}</p>
              <p className="font-mono text-xs">NIP. {sekolah?.nip_kepala_sekolah || '19740512 199903 1 002'}</p>
            </div>
          </div>
        </div>
      )}

      {/* ========================================================================= */}
      {/* 4. CETAK NOMOR RUANGAN UJIAN (Plang Kertas Pintu A4) */}
      {/* ========================================================================= */}
      {currentTab === 'nomor-ruangan' && (
        <div className="print-container space-y-6 print:space-y-0">
          {nomorRuangData.map((room) => (
            <div
              key={room.id}
              className="bg-white border-4 border-black p-8 sm:p-12 rounded-3xl text-center max-w-3xl mx-auto shadow-md page-break print:shadow-none print:border-4 print:border-black print:rounded-none"
            >
              <KopSurat />

              <div className="my-8 py-6 border-y-4 border-double border-black">
                <p className="text-lg sm:text-2xl font-bold uppercase tracking-widest text-slate-700">
                  RUANG UJIAN
                </p>
                <h1 className="text-5xl sm:text-7xl font-black text-black tracking-tight my-2">
                  {room.kode_ruang}
                </h1>
                <p className="text-base sm:text-xl font-bold text-slate-800">
                  {room.nama_ruang}
                </p>
              </div>

              <div className="p-4 bg-slate-50 border border-black rounded-2xl inline-block max-w-md w-full">
                <p className="text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                  Rentang Nomor Peserta Ujian:
                </p>
                <p className="text-lg font-mono font-black text-blue-900">
                  {room.rentang_nomor}
                </p>
                <p className="text-xs text-slate-500 font-semibold mt-1">
                  Kapasitas: {room.total_peserta} / {room.kapasitas} Peserta &bull; {room.lokasi}
                </p>
              </div>

              <div className="mt-8 text-xs font-semibold text-slate-600">
                Harap Menjaga Ketertiban dan Ketenangan Selama Ujian Berlangsung
              </div>
            </div>
          ))}
        </div>
      )}

      {/* ========================================================================= */}
      {/* 5. CETAK DAFTAR PESERTA UJIAN (DNT) */}
      {/* ========================================================================= */}
      {currentTab === 'daftar-peserta' && (
        <div className="bg-white p-8 rounded-3xl border border-slate-200/80 shadow-md max-w-4xl mx-auto print-container print:shadow-none print:border-none print:p-0">
          <KopSurat />
          <div className="text-center my-6">
            <h2 className="text-base sm:text-xl font-bold uppercase tracking-wider underline">
              DAFTAR NOMINASI PESERTA UJIAN (DNT)
            </h2>
            <p className="text-xs font-bold text-slate-700 mt-1 uppercase">
              {ujian?.nama_ujian || 'Penilaian Akhir Tahun'} &bull; TAHUN AJARAN {sekolah?.tahun_ajaran || '2024/2025'}
            </p>
          </div>

          <table className="w-full text-left border-collapse border border-black text-xs">
            <thead>
              <tr className="bg-slate-100 font-bold border-b border-black text-center">
                <th className="border border-black p-2 w-8">No</th>
                <th className="border border-black p-2">Nomor Peserta</th>
                <th className="border border-black p-2">NISN / NIS</th>
                <th className="border border-black p-2">Nama Lengkap Siswa</th>
                <th className="border border-black p-2 w-10">L/P</th>
                <th className="border border-black p-2">Kelas</th>
                <th className="border border-black p-2">Ruang Ujian</th>
                <th className="border border-black p-2 text-center w-16">No Meja</th>
              </tr>
            </thead>
            <tbody>
              {daftarPesertaData.map((s, idx) => (
                <tr key={s.id} className="border-b border-black">
                  <td className="border border-black p-2 text-center font-mono">{idx + 1}</td>
                  <td className="border border-black p-2 font-mono font-bold">{s.no_peserta}</td>
                  <td className="border border-black p-2 font-mono">{s.nisn || '-'} / {s.nis || '-'}</td>
                  <td className="border border-black p-2 font-bold">{s.nama}</td>
                  <td className="border border-black p-2 text-center font-bold">{s.jenis_kelamin}</td>
                  <td className="border border-black p-2">{s.nama_kelas}</td>
                  <td className="border border-black p-2">{s.nama_ruang || '-'}</td>
                  <td className="border border-black p-2 text-center font-mono font-bold">{s.no_meja || '-'}</td>
                </tr>
              ))}
            </tbody>
          </table>

          <div className="mt-8 flex justify-end text-xs sm:text-sm avoid-break">
            <div className="w-64 text-left">
              <p>{sekolah?.titimangsa_kota || 'Bandung'}, Mei 2025</p>
              <p className="font-semibold">Kepala Sekolah,</p>
              <div className="h-16"></div>
              <p className="font-bold underline uppercase">{sekolah?.kepala_sekolah || 'Dr. H. Ahmad Fauzi, M.Pd.'}</p>
              <p className="font-mono text-xs">NIP. {sekolah?.nip_kepala_sekolah || '19740512 199903 1 002'}</p>
            </div>
          </div>
        </div>
      )}

      {/* ========================================================================= */}
      {/* 6. CETAK DENAH TEMPAT DUDUK */}
      {/* ========================================================================= */}
      {currentTab === 'denah-duduk' && (
        <div className="print-container space-y-6 print:space-y-0">
          {denahDudukData.map((d) => (
            <div
              key={d.ruang.id}
              className="bg-white p-8 rounded-3xl border border-slate-200/80 shadow-md max-w-4xl mx-auto page-break print:shadow-none print:border-none print:p-0"
            >
              <KopSurat />
              <div className="text-center my-4">
                <h2 className="text-base sm:text-xl font-bold uppercase tracking-wider underline">
                  DENAH TEMPAT DUDUK PESERTA UJIAN
                </h2>
                <p className="text-xs font-bold text-slate-800 mt-1 uppercase">
                  {d.ruang.nama_ruang} ({d.ruang.kode_ruang}) &bull; KAPASITAS: {d.ruang.kapasitas} KURSI
                </p>
              </div>

              {/* Meja Pengawas */}
              <div className="max-w-xs mx-auto my-4 text-center">
                <div className="border-2 border-black p-2 font-bold text-xs uppercase bg-slate-100">
                  MEJA PENGAWAS RUANG (DEPAN)
                </div>
                <p className="text-[10px] text-slate-500 font-semibold mt-1">Pintu Masuk Ruangan</p>
              </div>

              {/* Grid Denah */}
              <div className="my-6">
                <div className="flex flex-col gap-3">
                  {d.grid.map((row, rIdx) => (
                    <div
                      key={rIdx}
                      className="grid gap-3"
                      style={{ gridTemplateColumns: `repeat(${d.ruang.jumlah_kolom}, minmax(0, 1fr))` }}
                    >
                      {row.map((slot) => {
                        const hasStudent = slot.peserta !== null;
                        return (
                          <div
                            key={slot.seatNo}
                            className={`border-2 border-black p-2 text-center rounded-lg ${
                              hasStudent ? 'bg-white' : 'bg-slate-50 border-dashed'
                            }`}
                          >
                            <div className="text-[10px] font-mono font-bold border-b border-black pb-0.5 mb-1">
                              MEJA #{slot.seatNo}
                            </div>
                            {hasStudent ? (
                              <div className="text-left text-[10px] leading-tight space-y-0.5">
                                <p className="font-bold text-black truncate">{slot.peserta.nama}</p>
                                <p className="font-mono text-[9px] font-bold">{slot.peserta.no_peserta}</p>
                                <p className="text-[8px] text-slate-600">{slot.peserta.nama_kelas}</p>
                              </div>
                            ) : (
                              <div className="text-[10px] text-slate-400 py-2 italic">Kosong</div>
                            )}
                          </div>
                        );
                      })}
                    </div>
                  ))}
                </div>
              </div>

              {/* Tanda Tangan */}
              <div className="mt-8 flex justify-between text-xs avoid-break">
                <div className="text-left">
                  <p>Mengetahui,</p>
                  <p className="font-semibold">Ketua Panitia Ujian,</p>
                  <div className="h-14"></div>
                  <p className="font-bold underline uppercase">H. Mulyadi, M.Pd.</p>
                  <p className="font-mono">NIP. 19760815 200212 1 003</p>
                </div>
                <div className="text-left">
                  <p>{sekolah?.titimangsa_kota || 'Bandung'}, Mei 2025</p>
                  <p className="font-semibold">Kepala Sekolah,</p>
                  <div className="h-14"></div>
                  <p className="font-bold underline uppercase">{sekolah?.kepala_sekolah || 'Dr. H. Ahmad Fauzi, M.Pd.'}</p>
                  <p className="font-mono">NIP. {sekolah?.nip_kepala_sekolah || '19740512 199903 1 002'}</p>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* ========================================================================= */}
      {/* 7. CETAK DAFTAR HADIR (4 Kategori) */}
      {/* ========================================================================= */}
      {currentTab === 'daftar-hadir' && (
        <div className="bg-white p-8 rounded-3xl border border-slate-200/80 shadow-md max-w-4xl mx-auto print-container print:shadow-none print:border-none print:p-0">
          <KopSurat />
          <div className="text-center my-6">
            <h2 className="text-base sm:text-xl font-bold uppercase tracking-wider underline">
              DAFTAR HADIR {daftarHadirType.toUpperCase()}
            </h2>
            <p className="text-xs font-bold text-slate-700 mt-1 uppercase">
              {ujian?.nama_ujian || 'Penilaian Akhir Tahun'} &bull; TAHUN AJARAN {sekolah?.tahun_ajaran || '2024/2025'}
            </p>
          </div>

          {/* Type: Peserta */}
          {daftarHadirType === 'peserta' && (
            <div>
              <div className="mb-4 text-xs font-semibold grid grid-cols-2 gap-2 border-b border-black pb-2">
                <div>Mata Pelajaran: <strong>{daftarHadirData?.mapel?.nama_mapel || 'Semua Mapel'}</strong></div>
                <div>Ruangan: <strong>{daftarHadirData?.ruang?.nama_ruang || 'Semua Ruang'}</strong></div>
                <div>Sesi Ujian: <strong>Sesi {selectedSesi}</strong></div>
                <div>Hari / Tanggal: <strong>{new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}</strong></div>
              </div>

              <table className="w-full text-left border-collapse border border-black text-xs">
                <thead>
                  <tr className="bg-slate-100 font-bold border-b border-black text-center">
                    <th className="border border-black p-2 w-8">No</th>
                    <th className="border border-black p-2">Nomor Peserta</th>
                    <th className="border border-black p-2">NISN</th>
                    <th className="border border-black p-2">Nama Lengkap Siswa</th>
                    <th className="border border-black p-2 w-16">Kelas</th>
                    <th className="border border-black p-2 w-14 text-center">Meja</th>
                    <th className="border border-black p-2 text-center w-36">Tanda Tangan</th>
                  </tr>
                </thead>
                <tbody>
                  {daftarHadirData?.data?.map((s, idx) => (
                    <tr key={s.id} className="border-b border-black">
                      <td className="border border-black p-2 text-center font-mono">{idx + 1}</td>
                      <td className="border border-black p-2 font-mono font-bold">{s.no_peserta}</td>
                      <td className="border border-black p-2 font-mono">{s.nisn || '-'}</td>
                      <td className="border border-black p-2 font-bold">{s.nama}</td>
                      <td className="border border-black p-2">{s.nama_kelas}</td>
                      <td className="border border-black p-2 text-center font-mono font-bold">{s.no_meja || '-'}</td>
                      <td className="border border-black p-2 text-left font-mono text-[10px]">
                        <div className="flex items-center justify-between">
                          <span>{idx + 1}. .........</span>
                          {idx % 2 === 1 && <span>&nbsp;</span>}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>

              <div className="mt-8 flex justify-between text-xs avoid-break">
                <div>
                  <p>Jumlah Peserta Seharusnya: {daftarHadirData?.data?.length || 0} Orang</p>
                  <p>Jumlah Hadir: ......... Orang</p>
                  <p>Jumlah Tidak Hadir: ......... Orang</p>
                </div>
                <div className="w-56 text-left">
                  <p>Pengawas Ruang,</p>
                  <div className="h-14"></div>
                  <p className="font-bold underline uppercase">( ........................................ )</p>
                  <p>NIP. ........................................</p>
                </div>
              </div>
            </div>
          )}

          {/* Type: Panitia */}
          {daftarHadirType === 'panitia' && (
            <div>
              <table className="w-full text-left border-collapse border border-black text-xs">
                <thead>
                  <tr className="bg-slate-100 font-bold border-b border-black text-center">
                    <th className="border border-black p-2 w-8">No</th>
                    <th className="border border-black p-2">Nama Lengkap &amp; Gelar</th>
                    <th className="border border-black p-2">NIP / NUPTK</th>
                    <th className="border border-black p-2">Jabatan Kepanitiaan</th>
                    <th className="border border-black p-2 text-center w-36">Tanda Tangan</th>
                  </tr>
                </thead>
                <tbody>
                  {daftarHadirData.map((p, idx) => (
                    <tr key={p.id} className="border-b border-black">
                      <td className="border border-black p-2 text-center font-mono">{idx + 1}</td>
                      <td className="border border-black p-2 font-bold">{p.nama}</td>
                      <td className="border border-black p-2 font-mono">{p.nip || '-'}</td>
                      <td className="border border-black p-2 font-semibold">{p.jabatan_panitia}</td>
                      <td className="border border-black p-2 font-mono text-[10px]">
                        {idx + 1}. .........
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}

          {/* Type: Pengawas */}
          {daftarHadirType === 'pengawas' && (
            <div>
              <table className="w-full text-left border-collapse border border-black text-xs">
                <thead>
                  <tr className="bg-slate-100 font-bold border-b border-black text-center">
                    <th className="border border-black p-2 w-8">No</th>
                    <th className="border border-black p-2">Kode</th>
                    <th className="border border-black p-2">Nama Pengawas</th>
                    <th className="border border-black p-2">Asal Sekolah</th>
                    <th className="border border-black p-2">Penempatan Ruang</th>
                    <th className="border border-black p-2 text-center w-36">Tanda Tangan Hadir</th>
                  </tr>
                </thead>
                <tbody>
                  {daftarHadirData?.pengawas?.map((w, idx) => (
                    <tr key={w.id} className="border-b border-black">
                      <td className="border border-black p-2 text-center font-mono">{idx + 1}</td>
                      <td className="border border-black p-2 text-center font-mono font-bold">{w.kode_pengawas}</td>
                      <td className="border border-black p-2 font-bold">{w.nama}</td>
                      <td className="border border-black p-2">{w.asal_sekolah}</td>
                      <td className="border border-black p-2">Ruang 0{(idx % 3) + 1}</td>
                      <td className="border border-black p-2 font-mono text-[10px]">
                        {idx + 1}. .........
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}

          {/* Type: Penguji Praktik */}
          {daftarHadirType === 'penguji' && (
            <div>
              <table className="w-full text-left border-collapse border border-black text-xs">
                <thead>
                  <tr className="bg-slate-100 font-bold border-b border-black text-center">
                    <th className="border border-black p-2 w-8">No</th>
                    <th className="border border-black p-2">Nama Penguji</th>
                    <th className="border border-black p-2">Mata Pelajaran Praktik</th>
                    <th className="border border-black p-2">Asal Instansi</th>
                    <th className="border border-black p-2 text-center w-36">Tanda Tangan Hadir</th>
                  </tr>
                </thead>
                <tbody>
                  {daftarHadirData?.penguji?.map((u, idx) => (
                    <tr key={u.id} className="border-b border-black">
                      <td className="border border-black p-2 text-center font-mono">{idx + 1}</td>
                      <td className="border border-black p-2 font-bold">{u.nama}</td>
                      <td className="border border-black p-2 font-semibold text-blue-900">{u.mata_pelajaran}</td>
                      <td className="border border-black p-2">{u.asal_instansi}</td>
                      <td className="border border-black p-2 font-mono text-[10px]">
                        {idx + 1}. .........
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      )}

      {/* ========================================================================= */}
      {/* 8. CETAK BERITA ACARA PELAKSANAAN UJIAN */}
      {/* ========================================================================= */}
      {currentTab === 'berita-acara' && (
        <div className="print-container space-y-6 print:space-y-0">
          {beritaAcaraList.map((ba) => (
            <div
              key={ba.id}
              className="bg-white p-8 rounded-3xl border border-slate-200/80 shadow-md max-w-4xl mx-auto page-break print:shadow-none print:border-none print:p-0"
            >
              <KopSurat />
              <div className="text-center my-6">
                <h2 className="text-base sm:text-xl font-bold uppercase tracking-wider underline">
                  BERITA ACARA PELAKSANAAN UJIAN
                </h2>
                <p className="text-xs font-bold text-slate-700 mt-1 uppercase">
                  TAHUN AJARAN {sekolah?.tahun_ajaran || '2024/2025'}
                </p>
              </div>

              <div className="space-y-4 text-xs sm:text-sm text-justify leading-relaxed">
                <p>
                  Pada hari ini, <strong className="uppercase">{new Date(ba.tanggal).toLocaleDateString('id-ID', { weekday: 'long' })}</strong> tanggal <strong className="font-mono">{new Date(ba.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</strong>, telah diselenggarakan kegiatan ujian sekolah sebagai berikut:
                </p>

                <table className="w-full text-xs sm:text-sm border-none! my-3">
                  <tbody>
                    <tr>
                      <td className="w-48 py-1 font-semibold border-none!">1. Nama Sekolah</td>
                      <td className="border-none!">: {sekolah?.nama_sekolah}</td>
                    </tr>
                    <tr>
                      <td className="py-1 font-semibold border-none!">2. Ruang Ujian</td>
                      <td className="border-none!">: {ba.nama_ruang || 'Ruang 01'}</td>
                    </tr>
                    <tr>
                      <td className="py-1 font-semibold border-none!">3. Sesi Ujian</td>
                      <td className="border-none!">: Sesi {ba.sesi || 1}</td>
                    </tr>
                    <tr>
                      <td className="py-1 font-semibold border-none!">4. Mata Pelajaran</td>
                      <td className="border-none! font-bold">: {ba.nama_mapel}</td>
                    </tr>
                    <tr>
                      <td className="py-1 font-semibold border-none!">5. Jumlah Peserta Seharusnya</td>
                      <td className="border-none! font-bold font-mono">: {ba.jumlah_seharusnya} Orang</td>
                    </tr>
                    <tr>
                      <td className="py-1 font-semibold border-none!">6. Jumlah Peserta Hadir</td>
                      <td className="border-none! font-bold font-mono text-emerald-800">: {ba.jumlah_hadir} Orang</td>
                    </tr>
                    <tr>
                      <td className="py-1 font-semibold border-none!">7. Jumlah Peserta Tidak Hadir</td>
                      <td className="border-none! font-bold font-mono text-rose-800">: {ba.jumlah_tidak_hadir} Orang</td>
                    </tr>
                    <tr>
                      <td className="py-1 font-semibold border-none!">8. Nomor Peserta Tidak Hadir</td>
                      <td className="border-none! font-mono">: {ba.nomor_tidak_hadir || '-'}</td>
                    </tr>
                  </tbody>
                </table>

                <div>
                  <p className="font-semibold mb-1">Catatan Kejadian Selama Pelaksanaan Ujian:</p>
                  <div className="p-3 bg-slate-50 border border-black rounded-xl text-xs whitespace-pre-line leading-relaxed">
                    {ba.catatan_kejadian || 'Pelaksanaan ujian berjalan dengan tertib, lancar, dan terkendali tanpa kendala yang berarti.'}
                  </div>
                </div>

                <p className="mt-4">
                  Demikian Berita Acara ini dibuat dengan sebenarnya dan ditandatangani oleh Pengawas Ruang Ujian.
                </p>

                <div className="mt-8 flex justify-between text-xs avoid-break pt-4">
                  <div className="w-60 text-left">
                    <p className="font-semibold">Pengawas Ruang 1,</p>
                    <div className="h-16"></div>
                    <p className="font-bold underline uppercase">{ba.pengawas_1_nama || '( ................................... )'}</p>
                    <p className="font-mono text-xs">NIP. {ba.pengawas_1_nip || '...................................'}</p>
                  </div>
                  <div className="w-60 text-left">
                    <p className="font-semibold">Pengawas Ruang 2,</p>
                    <div className="h-16"></div>
                    <p className="font-bold underline uppercase">{ba.pengawas_2_nama || '( ................................... )'}</p>
                    <p className="font-mono text-xs">NIP. {ba.pengawas_2_nip || '...................................'}</p>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* ========================================================================= */}
      {/* 9. CETAK LPJ KEGIATAN UJIAN (Comprehensive Document) */}
      {/* ========================================================================= */}
      {currentTab === 'lpj' && lpjData && (
        <div className="bg-white p-8 sm:p-12 rounded-3xl border border-slate-200/80 shadow-md max-w-4xl mx-auto print-container print:shadow-none print:border-none print:p-0 font-serif leading-relaxed text-black">
          {/* COVER LPJ */}
          <div className="text-center py-12 border-4 border-double border-black p-8 rounded-2xl page-break">
            <p className="text-sm font-bold uppercase tracking-widest text-slate-700">
              LAPORAN PERTANGGUNGJAWABAN (LPJ)
            </p>
            <h1 className="text-2xl sm:text-3xl font-black uppercase text-black my-4 leading-snug">
              {ujian?.nama_ujian || 'PELAKSANAAN UJIAN SEKOLAH TAHUN AJARAN 2024/2025'}
            </h1>
            <p className="text-xs font-semibold text-slate-600 uppercase">
              DASAR SK: {lpjData.lpj?.nomor_sk || '421.3/SK-042/SMAN1-MB/2025'}
            </p>

            <div className="my-12">
              <img src={sekolah?.logo_url || '/icon.svg'} alt="Logo" className="w-32 h-32 mx-auto object-contain" />
            </div>

            <div className="mt-8">
              <p className="text-xs font-semibold text-slate-500 uppercase">Disusun Oleh:</p>
              <h3 className="text-lg font-bold uppercase text-black mt-1">PANITIA PELAKSANA UJIAN</h3>
              <h4 className="text-base font-black uppercase text-blue-900 mt-1">{sekolah?.nama_sekolah}</h4>
              <p className="text-xs text-slate-700 mt-2">{sekolah?.alamat}, {sekolah?.kabupaten_kota}</p>
              <p className="text-xs font-bold text-slate-800 mt-1 uppercase">TAHUN AJARAN {sekolah?.tahun_ajaran || '2024/2025'}</p>
            </div>
          </div>

          {/* HALAMAN ISI LPJ */}
          <div className="mt-8 space-y-6 text-xs sm:text-sm text-justify">
            <KopSurat />
            <h2 className="text-base font-bold uppercase tracking-wider text-center underline my-4">
              LAPORAN PERTANGGUNGJAWABAN KEGIATAN UJIAN
            </h2>

            <div>
              <h3 className="font-bold text-sm uppercase text-slate-900 mb-1">I. PENDAHULUAN</h3>
              <p className="whitespace-pre-line">{lpjData.lpj?.latar_belakang}</p>
            </div>

            <div>
              <h3 className="font-bold text-sm uppercase text-slate-900 mb-1">II. DASAR HUKUM</h3>
              <div className="whitespace-pre-line pl-4">{lpjData.lpj?.dasar_kegiatan}</div>
            </div>

            <div>
              <h3 className="font-bold text-sm uppercase text-slate-900 mb-1">III. TUJUAN KEGIATAN</h3>
              <div className="whitespace-pre-line pl-4">{lpjData.lpj?.tujuan_kegiatan}</div>
            </div>

            <div>
              <h3 className="font-bold text-sm uppercase text-slate-900 mb-1">IV. REKAPITULASI PELAKSANAAN</h3>
              <ul className="list-disc pl-6 space-y-1">
                <li>Total Peserta Terdaftar: <strong>{lpjData.summary?.totalPeserta || 0} Siswa</strong></li>
                <li>Jumlah Ruangan Digunakan: <strong>{lpjData.summary?.totalRuang || 0} Ruangan</strong></li>
                <li>Jumlah Panitia Pelaksana: <strong>{lpjData.summary?.totalPanitia || 0} Orang</strong></li>
                <li>Jumlah Pengawas Ruang: <strong>{lpjData.summary?.totalPengawas || 0} Orang</strong></li>
              </ul>
            </div>

            <div>
              <h3 className="font-bold text-sm uppercase text-slate-900 mb-1">V. EVALUASI DAN HASIL KETERLAKSANAAN</h3>
              <p className="whitespace-pre-line">{lpjData.lpj?.hasil_evaluasi}</p>
            </div>

            <div>
              <h3 className="font-bold text-sm uppercase text-slate-900 mb-1">VI. KENDALA DAN PEMECAHAN MASALAH</h3>
              <p className="whitespace-pre-line">{lpjData.lpj?.hambatan_solusi}</p>
            </div>

            {/* REALISASI KEUANGAN RAB */}
            <div>
              <h3 className="font-bold text-sm uppercase text-slate-900 mb-2">VII. LAPORAN REALISASI ANGGARAN (RAB)</h3>
              {(() => {
                let fin = { pemasukan: [], pengeluaran: [] };
                try {
                  fin = JSON.parse(lpjData.lpj?.rincian_keuangan_json || '{}');
                } catch {}

                const totalIn = fin.pemasukan?.reduce((acc, c) => acc + c.jumlah, 0) || 0;
                const totalOut = fin.pengeluaran?.reduce((acc, c) => acc + c.jumlah, 0) || 0;
                const saldo = totalIn - totalOut;

                return (
                  <div className="space-y-3">
                    <table className="w-full text-xs border-collapse border border-black">
                      <thead>
                        <tr className="bg-slate-100 font-bold border-b border-black">
                          <th className="border border-black p-2 text-left">Pemasukan / Sumber Dana</th>
                          <th className="border border-black p-2 text-right w-36">Jumlah (Rp)</th>
                        </tr>
                      </thead>
                      <tbody>
                        {fin.pemasukan?.map((inItem, i) => (
                          <tr key={i} className="border-b border-black">
                            <td className="border border-black p-2">{inItem.sumber}</td>
                            <td className="border border-black p-2 text-right font-mono font-bold">
                              {inItem.jumlah.toLocaleString('id-ID')}
                            </td>
                          </tr>
                        ))}
                        <tr className="bg-slate-50 font-bold border-b border-black">
                          <td className="border border-black p-2 text-right">TOTAL PENERIMAAN:</td>
                          <td className="border border-black p-2 text-right font-mono">
                            Rp {totalIn.toLocaleString('id-ID')}
                          </td>
                        </tr>
                      </tbody>
                    </table>

                    <table className="w-full text-xs border-collapse border border-black">
                      <thead>
                        <tr className="bg-slate-100 font-bold border-b border-black">
                          <th className="border border-black p-2 text-left">Pengeluaran / Realisasi Belanja</th>
                          <th className="border border-black p-2 text-right w-36">Jumlah (Rp)</th>
                        </tr>
                      </thead>
                      <tbody>
                        {fin.pengeluaran?.map((outItem, i) => (
                          <tr key={i} className="border-b border-black">
                            <td className="border border-black p-2">{outItem.uraian}</td>
                            <td className="border border-black p-2 text-right font-mono font-bold">
                              {outItem.jumlah.toLocaleString('id-ID')}
                            </td>
                          </tr>
                        ))}
                        <tr className="bg-slate-50 font-bold border-b border-black">
                          <td className="border border-black p-2 text-right">TOTAL PENGELUARAN:</td>
                          <td className="border border-black p-2 text-right font-mono">
                            Rp {totalOut.toLocaleString('id-ID')}
                          </td>
                        </tr>
                        <tr className="bg-emerald-50 font-black border-b border-black text-emerald-900">
                          <td className="border border-black p-2 text-right">SISA SALDO KAS:</td>
                          <td className="border border-black p-2 text-right font-mono">
                            Rp {saldo.toLocaleString('id-ID')}
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                );
              })()}
            </div>

            <div>
              <h3 className="font-bold text-sm uppercase text-slate-900 mb-1">VIII. KESIMPULAN &amp; SARAN</h3>
              <p className="whitespace-pre-line mb-2"><strong>Kesimpulan:</strong> {lpjData.lpj?.kesimpulan}</p>
              <p className="whitespace-pre-line"><strong>Saran:</strong> {lpjData.lpj?.saran}</p>
            </div>

            {/* LEMBAR PENGESAHAN */}
            <div className="mt-12 pt-6 border-t border-black avoid-break">
              <h4 className="font-bold text-center uppercase tracking-wider text-xs sm:text-sm mb-6">
                LEMBAR PENGESAHAN LAPORAN PERTANGGUNGJAWABAN
              </h4>
              <div className="flex justify-between text-xs sm:text-sm">
                <div className="text-left">
                  <p>Mengetahui / Mengesahkan,</p>
                  <p className="font-bold">Ketua Panitia Ujian,</p>
                  <div className="h-20"></div>
                  <p className="font-bold underline uppercase">H. Mulyadi, M.Pd.</p>
                  <p className="font-mono text-xs">NIP. 19760815 200212 1 003</p>
                </div>

                <div className="text-left">
                  <p>{sekolah?.titimangsa_kota || 'Bandung'}, Juni 2025</p>
                  <p className="font-bold">Kepala Sekolah,</p>
                  <div className="h-20"></div>
                  <p className="font-bold underline uppercase">{sekolah?.kepala_sekolah || 'Dr. H. Ahmad Fauzi, M.Pd.'}</p>
                  <p className="font-mono text-xs">NIP. {sekolah?.nip_kepala_sekolah || '19740512 199903 1 002'}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Modal Add Berita Acara */}
      <Modal
        isOpen={baModalOpen}
        onClose={() => setBaModalOpen(false)}
        title="Buat Berita Acara Pelaksanaan Ujian Baru"
      >
        <form onSubmit={handleSaveBA} className="space-y-4">
          <div className="grid grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Tanggal Ujian</label>
              <input
                type="date"
                value={baForm.tanggal}
                onChange={(e) => setBaForm({ ...baForm, tanggal: e.target.value })}
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Ruangan</label>
              <select
                value={baForm.ruang_id}
                onChange={(e) => setBaForm({ ...baForm, ruang_id: e.target.value })}
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
              >
                <option value="">Pilih Ruang...</option>
                {ruangList.map(r => (
                  <option key={r.id} value={r.id}>{r.nama_ruang}</option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Sesi</label>
              <input
                type="number"
                value={baForm.sesi}
                onChange={(e) => setBaForm({ ...baForm, sesi: parseInt(e.target.value) || 1 })}
                min="1"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Mata Pelajaran</label>
            <select
              value={baForm.mapel_id}
              onChange={(e) => setBaForm({ ...baForm, mapel_id: e.target.value })}
              required
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white font-bold"
            >
              <option value="">Pilih Mapel...</option>
              {mapelList.map(m => (
                <option key={m.id} value={m.id}>{m.nama_mapel}</option>
              ))}
            </select>
          </div>

          <div className="grid grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Peserta Seharusnya</label>
              <input
                type="number"
                value={baForm.jumlah_seharusnya}
                onChange={(e) => setBaForm({ ...baForm, jumlah_seharusnya: parseInt(e.target.value) || 0 })}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono"
              />
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Jumlah Hadir</label>
              <input
                type="number"
                value={baForm.jumlah_hadir}
                onChange={(e) => setBaForm({ ...baForm, jumlah_hadir: parseInt(e.target.value) || 0 })}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono"
              />
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Jumlah Tidak Hadir</label>
              <input
                type="number"
                value={baForm.jumlah_tidak_hadir}
                onChange={(e) => setBaForm({ ...baForm, jumlah_tidak_hadir: parseInt(e.target.value) || 0 })}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Nomor Peserta Tidak Hadir</label>
            <input
              type="text"
              value={baForm.nomor_tidak_hadir}
              onChange={(e) => setBaForm({ ...baForm, nomor_tidak_hadir: e.target.value })}
              placeholder="Contoh: 01-001-009-8 (Sakit) atau tanda strip (-)"
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Catatan Kejadian Khusus</label>
            <textarea
              rows={2}
              value={baForm.catatan_kejadian}
              onChange={(e) => setBaForm({ ...baForm, catatan_kejadian: e.target.value })}
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm"
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Pengawas 1 (Nama &amp; NIP)</label>
              <input
                type="text"
                value={baForm.pengawas_1_nama}
                onChange={(e) => setBaForm({ ...baForm, pengawas_1_nama: e.target.value })}
                placeholder="Nama Pengawas 1"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs mb-1.5"
              />
              <input
                type="text"
                value={baForm.pengawas_1_nip}
                onChange={(e) => setBaForm({ ...baForm, pengawas_1_nip: e.target.value })}
                placeholder="NIP Pengawas 1"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Pengawas 2 (Nama &amp; NIP)</label>
              <input
                type="text"
                value={baForm.pengawas_2_nama}
                onChange={(e) => setBaForm({ ...baForm, pengawas_2_nama: e.target.value })}
                placeholder="Nama Pengawas 2"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs mb-1.5"
              />
              <input
                type="text"
                value={baForm.pengawas_2_nip}
                onChange={(e) => setBaForm({ ...baForm, pengawas_2_nip: e.target.value })}
                placeholder="NIP Pengawas 2"
                className="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono"
              />
            </div>
          </div>

          <div className="flex justify-end gap-3 pt-3 border-t border-slate-100">
            <button
              type="button"
              onClick={() => setBaModalOpen(false)}
              className="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100"
            >
              Batal
            </button>
            <button
              type="submit"
              className="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20"
            >
              Simpan Berita Acara
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
