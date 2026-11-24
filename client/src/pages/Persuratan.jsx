import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import { FileText, Printer, Save, Plus, Trash2, Edit3, CheckCircle2, ChevronRight, Eye } from 'lucide-react';
import KopSurat from '../components/KopSurat';

export default function Persuratan() {
  const { sekolah, ujian, showToast } = useApp();
  const [letters, setLetters] = useState([]);
  const [selectedLetter, setSelectedLetter] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  // Edit fields
  const [nomorSurat, setNomorSurat] = useState('');
  const [perihal, setPerihal] = useState('');
  const [lampiran, setLampiran] = useState('-');
  const [tanggalSurat, setTanggalSurat] = useState('');
  const [tempatSurat, setTempatSurat] = useState('Bandung');
  const [dasarHukum, setDasarHukum] = useState('');
  const [isiSurat, setIsiSurat] = useState('');
  const [tujuanPenerima, setTujuanPenerima] = useState('');
  const [daftarPetugas, setDaftarPetugas] = useState([]);

  // Letter types map
  const letterTypes = [
    { key: 'undangan_rapat', title: '1. Surat Undangan Rapat' },
    { key: 'tugas_pembuat_soal_tulis', title: '2. Surat Tugas Pembuat Soal Tulis' },
    { key: 'tugas_pembuat_soal_praktik', title: '3. Surat Tugas Pembuat Soal Praktik' },
    { key: 'tugas_pengawas_ruang', title: '4. Surat Tugas Pengawas Ruang' },
    { key: 'tugas_penguji_praktik', title: '5. Surat Tugas Penguji Praktik' }
  ];

  const fetchLetters = async () => {
    try {
      const res = await api.getSuratList();
      if (res.success && res.data.length > 0) {
        setLetters(res.data);
        selectLetter(res.data[0]);
      }
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchLetters();
  }, []);

  const selectLetter = (letter) => {
    setSelectedLetter(letter);
    setNomorSurat(letter.nomor_surat || '');
    setPerihal(letter.perihal || '');
    setLampiran(letter.lampiran || '-');
    setTanggalSurat(letter.tanggal_surat || new Date().toISOString().split('T')[0]);
    setTempatSurat(letter.tempat_surat || sekolah?.titimangsa_kota || 'Bandung');
    setDasarHukum(letter.dasar_hukum || '');
    setIsiSurat(letter.isi_surat || '');
    setTujuanPenerima(letter.tujuan_penerima || '');

    try {
      const petugas = JSON.parse(letter.daftar_petugas_json || '[]');
      setDaftarPetugas(Array.isArray(petugas) ? petugas : []);
    } catch {
      setDaftarPetugas([]);
    }
  };

  const handleSelectType = (key) => {
    const found = letters.find(l => l.jenis_surat === key);
    if (found) {
      selectLetter(found);
    }
  };

  const handleSave = async () => {
    if (!selectedLetter) return;
    setSaving(true);
    try {
      const payload = {
        jenis_surat: selectedLetter.jenis_surat,
        nomor_surat: nomorSurat,
        perihal,
        lampiran,
        tanggal_surat: tanggalSurat,
        tempat_surat: tempatSurat,
        dasar_hukum: dasarHukum,
        isi_surat: isiSurat,
        tujuan_penerima: tujuanPenerima,
        daftar_petugas_json: daftarPetugas
      };
      await api.updateSurat(selectedLetter.id, payload);
      showToast('Naskah surat berhasil disimpan!', 'success');
      fetchLetters();
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setSaving(false);
    }
  };

  const addPetugasRow = () => {
    setDaftarPetugas([...daftarPetugas, { nama: '', nip: '-', tugas: '', mapel: '', ruang: '' }]);
  };

  const removePetugasRow = (index) => {
    setDaftarPetugas(daftarPetugas.filter((_, i) => i !== index));
  };

  const updatePetugasRow = (index, field, value) => {
    const updated = [...daftarPetugas];
    updated[index][field] = value;
    setDaftarPetugas(updated);
  };

  const handlePrint = () => {
    window.print();
  };

  // Format date indonesian
  const formatTanggalIndo = (dateStr) => {
    if (!dateStr) return '';
    try {
      const d = new Date(dateStr);
      return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    } catch {
      return dateStr;
    }
  };

  return (
    <div className="space-y-6">
      {/* Page Header (No print) */}
      <div className="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <FileText className="w-6 h-6 text-blue-600" />
            <span>Persuratan Resmi Kegiatan Ujian</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
            Generator surat dinas sekolah standar: Surat Undangan, Surat Tugas Pembuat Soal, Pengawas &amp; Penguji Praktik
          </p>
        </div>
        <div className="flex items-center gap-2 self-start sm:self-auto">
          <button
            onClick={handleSave}
            disabled={saving}
            className="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs flex items-center gap-1.5 transition-all shadow-xs"
          >
            <Save className="w-4 h-4" />
            <span>{saving ? 'Menyimpan...' : 'Simpan Surat'}</span>
          </button>
          <button
            onClick={handlePrint}
            className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs flex items-center gap-1.5 shadow-md shadow-blue-500/20 transition-all"
          >
            <Printer className="w-4 h-4" />
            <span>Cetak Surat (PDF / Print)</span>
          </button>
        </div>
      </div>

      {/* 5 Surat Category Buttons (No print) */}
      <div className="no-print grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
        {letterTypes.map((type) => {
          const isSelected = selectedLetter?.jenis_surat === type.key;
          return (
            <button
              key={type.key}
              onClick={() => handleSelectType(type.key)}
              className={`p-3 rounded-2xl text-left border transition-all ${
                isSelected
                  ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-600/20 font-bold'
                  : 'bg-white text-slate-700 border-slate-200/80 hover:border-slate-300 font-semibold'
              } text-xs leading-snug`}
            >
              {type.title}
            </button>
          );
        })}
      </div>

      {/* Layout Editor (No print) */}
      <div className="no-print bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
        <h3 className="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">
          Editor Parameter Naskah Surat: {selectedLetter?.perihal}
        </h3>

        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Nomor Surat Resmi</label>
            <input
              type="text"
              value={nomorSurat}
              onChange={(e) => setNomorSurat(e.target.value)}
              className="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm font-mono font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Perihal / Hal</label>
            <input
              type="text"
              value={perihal}
              onChange={(e) => setPerihal(e.target.value)}
              className="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Tanggal Surat</label>
            <input
              type="date"
              value={tanggalSurat}
              onChange={(e) => setTanggalSurat(e.target.value)}
              className="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
            />
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Dasar Hukum / Konsideran</label>
            <textarea
              rows={3}
              value={dasarHukum}
              onChange={(e) => setDasarHukum(e.target.value)}
              className="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Redaksi Isi / Diktum Surat</label>
            <textarea
              rows={3}
              value={isiSurat}
              onChange={(e) => setIsiSurat(e.target.value)}
              className="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
            />
          </div>
        </div>

        {/* Tabel Petugas yang ditugaskan */}
        <div className="pt-2">
          <div className="flex items-center justify-between mb-2">
            <label className="text-xs font-bold text-slate-800">
              Daftar Petugas / Dewan Guru yang Ditugaskan ({daftarPetugas.length} Orang)
            </label>
            <button
              type="button"
              onClick={addPetugasRow}
              className="text-xs text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1"
            >
              <Plus className="w-3.5 h-3.5" />
              <span>Tambah Baris Petugas</span>
            </button>
          </div>

          <div className="space-y-2 max-h-56 overflow-y-auto p-2 bg-slate-50 rounded-2xl border border-slate-100">
            {daftarPetugas.map((row, idx) => (
              <div key={idx} className="flex items-center gap-2 bg-white p-2 rounded-xl border border-slate-200">
                <span className="text-xs font-mono font-bold text-slate-400 w-6 text-center">{idx + 1}</span>
                <input
                  type="text"
                  placeholder="Nama Lengkap & Gelar"
                  value={row.nama || ''}
                  onChange={(e) => updatePetugasRow(idx, 'nama', e.target.value)}
                  className="flex-1 px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs"
                />
                <input
                  type="text"
                  placeholder="NIP / NUPTK"
                  value={row.nip || ''}
                  onChange={(e) => updatePetugasRow(idx, 'nip', e.target.value)}
                  className="w-36 px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-mono"
                />
                <input
                  type="text"
                  placeholder="Tugas / Mapel / Ruang"
                  value={row.tugas || row.mapel || row.jabatan || row.ruang || ''}
                  onChange={(e) => updatePetugasRow(idx, 'tugas', e.target.value)}
                  className="w-48 px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs"
                />
                <button
                  type="button"
                  onClick={() => removePetugasRow(idx)}
                  className="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg"
                >
                  <Trash2 className="w-3.5 h-3.5" />
                </button>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* PRINT SHEET CONTAINER (Siap Cetak Kertas A4 Kedinasan) */}
      <div className="bg-white p-8 sm:p-12 rounded-3xl border border-slate-200/80 shadow-md max-w-4xl mx-auto print-container print:shadow-none print:border-none print:p-0 print:m-0 font-serif text-black leading-relaxed">
        {/* Kop Surat Sekolah */}
        <KopSurat />

        {/* Nomor & Perihal Header */}
        <div className="mt-4 mb-6 text-xs sm:text-sm">
          {selectedLetter?.jenis_surat === 'undangan_rapat' ? (
            <div className="flex justify-between items-start gap-4">
              <table className="w-auto border-none! text-xs sm:text-sm">
                <tbody>
                  <tr>
                    <td className="border-none! py-0.5 pr-3 font-semibold w-24">Nomor</td>
                    <td className="border-none! py-0.5 pr-2">:</td>
                    <td className="border-none! py-0.5 font-mono">{nomorSurat}</td>
                  </tr>
                  <tr>
                    <td className="border-none! py-0.5 pr-3 font-semibold">Sifat</td>
                    <td className="border-none! py-0.5 pr-2">:</td>
                    <td className="border-none! py-0.5">Penting / Segera</td>
                  </tr>
                  <tr>
                    <td className="border-none! py-0.5 pr-3 font-semibold">Lampiran</td>
                    <td className="border-none! py-0.5 pr-2">:</td>
                    <td className="border-none! py-0.5">{lampiran}</td>
                  </tr>
                  <tr>
                    <td className="border-none! py-0.5 pr-3 font-semibold">Perihal</td>
                    <td className="border-none! py-0.5 pr-2">:</td>
                    <td className="border-none! py-0.5 font-bold">{perihal}</td>
                  </tr>
                </tbody>
              </table>

              <div className="text-right text-xs sm:text-sm">
                <p>{tempatSurat}, {formatTanggalIndo(tanggalSurat)}</p>
                <div className="mt-4 text-left">
                  <p>Kepada Yth,</p>
                  <p className="font-bold">{tujuanPenerima || 'Bapak/Ibu Dewan Guru & Staf'}</p>
                  <p>di Tempat</p>
                </div>
              </div>
            </div>
          ) : (
            <div className="text-center my-4">
              <h2 className="text-base sm:text-lg font-bold tracking-wider uppercase underline underline-offset-4">
                SURAT TUGAS
              </h2>
              <p className="text-xs sm:text-sm font-mono mt-0.5">Nomor: {nomorSurat}</p>
            </div>
          )}
        </div>

        {/* Isi Surat Dinas */}
        <div className="space-y-4 text-xs sm:text-sm text-justify leading-relaxed">
          {selectedLetter?.jenis_surat !== 'undangan_rapat' && (
            <div>
              <p className="font-bold mb-1">Dasar:</p>
              <div className="whitespace-pre-line pl-4 text-slate-800">
                {dasarHukum}
              </div>
              <div className="text-center font-bold tracking-widest my-3 uppercase">
                MEMUTUSKAN / MENUGASKAN:
              </div>
              <p>Kepada Dewan Guru / Staf yang namanya tercantum di bawah ini:</p>
            </div>
          )}

          <div className="whitespace-pre-line text-slate-900 leading-relaxed">
            {isiSurat}
          </div>

          {/* Tabel Petugas Lampiran jika ada */}
          {daftarPetugas.length > 0 && (
            <div className="my-4">
              <table className="w-full text-left border-collapse border border-black text-xs">
                <thead>
                  <tr className="bg-slate-100 font-bold border-b border-black">
                    <th className="border border-black p-2 text-center w-8">No</th>
                    <th className="border border-black p-2">Nama Lengkap &amp; Gelar</th>
                    <th className="border border-black p-2">NIP / NUPTK</th>
                    <th className="border border-black p-2">Tugas / Mata Pelajaran / Ruang</th>
                  </tr>
                </thead>
                <tbody>
                  {daftarPetugas.map((p, idx) => (
                    <tr key={idx} className="border-b border-black">
                      <td className="border border-black p-2 text-center font-mono">{idx + 1}</td>
                      <td className="border border-black p-2 font-bold">{p.nama}</td>
                      <td className="border border-black p-2 font-mono">{p.nip || '-'}</td>
                      <td className="border border-black p-2">{p.tugas || p.mapel || p.jabatan || p.ruang || '-'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}

          <p className="mt-4">
            Demikian surat ini dibuat agar dapat dilaksanakan dengan sebaik-baiknya dan penuh rasa tanggung jawab.
          </p>
        </div>

        {/* Tanda Tangan Kepala Sekolah */}
        <div className="mt-10 flex justify-end text-xs sm:text-sm avoid-break">
          <div className="w-64 text-left">
            <p>{tempatSurat}, {formatTanggalIndo(tanggalSurat)}</p>
            <p className="font-semibold">Kepala Sekolah,</p>
            <div className="h-20 flex items-center">
              {/* Optional stempel / tanda tangan digital placeholder */}
            </div>
            <p className="font-bold underline uppercase">{sekolah?.kepala_sekolah || 'Dr. H. Ahmad Fauzi, M.Pd.'}</p>
            <p className="font-mono text-xs">NIP. {sekolah?.nip_kepala_sekolah || '19740512 199903 1 002'}</p>
          </div>
        </div>
      </div>
    </div>
  );
}
