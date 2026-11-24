import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import { ScrollText, Printer, Save, Plus, Trash2, Edit3, CheckCircle2 } from 'lucide-react';
import KopSurat from '../components/KopSurat';

export default function TataTertib() {
  const { sekolah, showToast } = useApp();
  const [activeKategori, setActiveKategori] = useState('peserta'); // 'peserta', 'pengawas', 'penguji'
  const [judul, setJudul] = useState('');
  const [poinRules, setPoinRules] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  const kategoriTabs = [
    { key: 'peserta', label: '1. Tata Tertib Peserta Ujian' },
    { key: 'pengawas', label: '2. Tata Tertib Pengawas Ruang' },
    { key: 'penguji', label: '3. Tata Tertib Penguji Praktik' }
  ];

  const fetchTatib = async (kategori) => {
    setLoading(true);
    try {
      const res = await api.getTatibCategory(kategori);
      if (res.success && res.data) {
        setJudul(res.data.judul);
        try {
          const parsed = JSON.parse(res.data.isi_poin_json);
          setPoinRules(Array.isArray(parsed) ? parsed : []);
        } catch {
          setPoinRules([]);
        }
      }
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchTatib(activeKategori);
  }, [activeKategori]);

  const handleSave = async () => {
    setSaving(true);
    try {
      await api.updateTatib(activeKategori, {
        judul,
        isi_poin_json: poinRules
      });
      showToast('Tata tertib berhasil diperbarui!', 'success');
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setSaving(false);
    }
  };

  const addPoint = () => {
    setPoinRules([...poinRules, '']);
  };

  const updatePoint = (index, value) => {
    const updated = [...poinRules];
    updated[index] = value;
    setPoinRules(updated);
  };

  const removePoint = (index) => {
    setPoinRules(poinRules.filter((_, i) => i !== index));
  };

  const handlePrint = () => {
    window.print();
  };

  return (
    <div className="space-y-6">
      {/* Top action bar (No print) */}
      <div className="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <ScrollText className="w-6 h-6 text-blue-600" />
            <span>Tata Tertib Ujian Sekolah</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
            Penyusunan aturan resmi dan lembar tata tertib siap cetak untuk ditempel di ruang ujian
          </p>
        </div>
        <div className="flex items-center gap-2 self-start sm:self-auto">
          <button
            onClick={handleSave}
            disabled={saving}
            className="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs flex items-center gap-1.5 transition-all shadow-xs"
          >
            <Save className="w-4 h-4" />
            <span>{saving ? 'Menyimpan...' : 'Simpan Tata Tertib'}</span>
          </button>
          <button
            onClick={handlePrint}
            className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs flex items-center gap-1.5 shadow-md shadow-blue-500/20 transition-all"
          >
            <Printer className="w-4 h-4" />
            <span>Cetak Lembar Tata Tertib</span>
          </button>
        </div>
      </div>

      {/* Tabs (No print) */}
      <div className="no-print flex flex-wrap gap-2.5">
        {kategoriTabs.map(tab => (
          <button
            key={tab.key}
            onClick={() => setActiveKategori(tab.key)}
            className={`px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold border transition-all ${
              activeKategori === tab.key
                ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-600/20'
                : 'bg-white text-slate-700 border-slate-200/80 hover:border-slate-300'
            }`}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {/* Editor Box (No print) */}
      <div className="no-print bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
        <div>
          <label className="block text-xs font-semibold text-slate-700 mb-1">Judul Dokumen Tata Tertib</label>
          <input
            type="text"
            value={judul}
            onChange={(e) => setJudul(e.target.value)}
            className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 uppercase"
          />
        </div>

        <div>
          <div className="flex items-center justify-between mb-2">
            <label className="text-xs font-bold text-slate-800">
              Poin-Poin Peraturan ({poinRules.length} Butir)
            </label>
            <button
              type="button"
              onClick={addPoint}
              className="text-xs text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1"
            >
              <Plus className="w-3.5 h-3.5" />
              <span>Tambah Butir Tata Tertib</span>
            </button>
          </div>

          <div className="space-y-2.5 max-h-80 overflow-y-auto p-2 bg-slate-50 rounded-2xl border border-slate-100">
            {poinRules.map((point, idx) => (
              <div key={idx} className="flex items-start gap-2 bg-white p-2.5 rounded-xl border border-slate-200">
                <span className="text-xs font-mono font-bold text-slate-500 w-6 pt-1 text-center">{idx + 1}.</span>
                <textarea
                  rows={2}
                  value={point}
                  onChange={(e) => updatePoint(idx, e.target.value)}
                  placeholder="Tuliskan butir tata tertib..."
                  className="flex-1 px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-800 focus:ring-2 focus:ring-blue-500/20"
                />
                <button
                  type="button"
                  onClick={() => removePoint(idx)}
                  className="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg mt-1"
                >
                  <Trash2 className="w-4 h-4" />
                </button>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* PRINT SHEET CONTAINER */}
      <div className="bg-white p-8 sm:p-12 rounded-3xl border border-slate-200/80 shadow-md max-w-4xl mx-auto print-container print:shadow-none print:border-none print:p-0 print:m-0 font-serif text-black leading-relaxed">
        {/* Kop Surat */}
        <KopSurat />

        <div className="text-center my-6">
          <h2 className="text-base sm:text-xl font-bold uppercase tracking-wider underline underline-offset-4">
            {judul}
          </h2>
          <p className="text-xs sm:text-sm font-semibold text-slate-800 mt-1 uppercase">
            TAHUN AJARAN {sekolah?.tahun_ajaran || '2024/2025'}
          </p>
        </div>

        {/* List of rules */}
        <div className="my-6">
          <ol className="list-decimal list-outside pl-6 space-y-3 text-xs sm:text-sm text-justify leading-relaxed">
            {poinRules.map((rule, idx) => (
              <li key={idx} className="pl-1">
                {rule}
              </li>
            ))}
          </ol>
        </div>

        {/* Tanda Tangan */}
        <div className="mt-12 flex justify-end text-xs sm:text-sm avoid-break">
          <div className="w-64 text-left">
            <p>{sekolah?.titimangsa_kota || 'Bandung'}, {new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</p>
            <p className="font-semibold">Kepala Sekolah,</p>
            <div className="h-20"></div>
            <p className="font-bold underline uppercase">{sekolah?.kepala_sekolah || 'Dr. H. Ahmad Fauzi, M.Pd.'}</p>
            <p className="font-mono text-xs">NIP. {sekolah?.nip_kepala_sekolah || '19740512 199903 1 002'}</p>
          </div>
        </div>
      </div>
    </div>
  );
}
