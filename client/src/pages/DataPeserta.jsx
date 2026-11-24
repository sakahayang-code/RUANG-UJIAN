import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import {
  Users, Plus, Edit, Trash2, Search, Filter, Upload,
  Download, FileSpreadsheet, Key, CheckCircle2, AlertCircle
} from 'lucide-react';
import Modal from '../components/Modal';

export default function DataPeserta() {
  const { showToast } = useApp();
  const [list, setList] = useState([]);
  const [kelasList, setKelasList] = useState([]);
  const [ruangList, setRuangList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [importModalOpen, setImportModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);

  const [searchTerm, setSearchTerm] = useState('');
  const [selectedKelas, setSelectedKelas] = useState('');
  const [selectedRuang, setSelectedRuang] = useState('');

  const [formData, setFormData] = useState({
    no_peserta: '',
    nis: '',
    nisn: '',
    nama: '',
    jenis_kelamin: 'L',
    kelas_id: '',
    sesi: 1,
    ruang_id: '',
    no_meja: 0,
    password: '123456'
  });

  const [importCsvText, setImportCsvText] = useState('');

  const fetchData = async () => {
    try {
      const [pesertaRes, kelasRes, ruangRes] = await Promise.all([
        api.getPeserta({
          kelas_id: selectedKelas,
          ruang_id: selectedRuang,
          search: searchTerm
        }),
        api.getKelas(),
        api.getRuangan()
      ]);
      if (pesertaRes.success) setList(pesertaRes.data);
      if (kelasRes.success) setKelasList(kelasRes.data);
      if (ruangRes.success) setRuangList(ruangRes.data);
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, [selectedKelas, selectedRuang, searchTerm]);

  const openCreate = () => {
    setEditingItem(null);
    const nextNum = list.length + 1;
    setFormData({
      no_peserta: `01-001-${String(nextNum).padStart(3, '0')}-0`,
      nis: '',
      nisn: '',
      nama: '',
      jenis_kelamin: 'L',
      kelas_id: kelasList.length > 0 ? kelasList[0].id : '',
      sesi: 1,
      ruang_id: '',
      no_meja: 0,
      password: '123456'
    });
    setModalOpen(true);
  };

  const openEdit = (item) => {
    setEditingItem(item);
    setFormData({
      no_peserta: item.no_peserta,
      nis: item.nis || '',
      nisn: item.nisn || '',
      nama: item.nama,
      jenis_kelamin: item.jenis_kelamin || 'L',
      kelas_id: item.kelas_id || '',
      sesi: item.sesi || 1,
      ruang_id: item.ruang_id || '',
      no_meja: item.no_meja || 0,
      password: item.password || '123456'
    });
    setModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      if (editingItem) {
        await api.updatePeserta(editingItem.id, formData);
        showToast('Data peserta berhasil diperbarui!', 'success');
      } else {
        await api.createPeserta(formData);
        showToast('Peserta ujian baru berhasil ditambahkan!', 'success');
      }
      setModalOpen(false);
      fetchData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleDelete = async (id, nama) => {
    if (!window.confirm(`Yakin ingin menghapus peserta ${nama}?`)) return;
    try {
      await api.deletePeserta(id);
      showToast('Peserta berhasil dihapus.', 'success');
      fetchData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleImportCsv = async (e) => {
    e.preventDefault();
    if (!importCsvText.trim()) return;

    try {
      const lines = importCsvText.trim().split('\n');
      const dataToImport = [];

      for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trim();
        if (!line) continue;
        const parts = line.split(/[,\t]/);
        if (parts.length < 2) continue;

        // format: no_peserta, nama, nis, nisn, jk, nama_kelas
        const no_peserta = parts[0]?.trim();
        const nama = parts[1]?.trim();
        const nis = parts[2]?.trim() || '';
        const nisn = parts[3]?.trim() || '';
        const jk = parts[4]?.trim().toUpperCase() === 'P' ? 'P' : 'L';
        const nama_kelas = parts[5]?.trim() || '';

        // Match kelas_id
        let matchedKelasId = null;
        if (nama_kelas) {
          const k = kelasList.find(c => c.nama_kelas.toLowerCase() === nama_kelas.toLowerCase());
          if (k) matchedKelasId = k.id;
        }

        if (no_peserta && nama) {
          dataToImport.push({
            no_peserta,
            nama,
            nis,
            nisn,
            jenis_kelamin: jk,
            kelas_id: matchedKelasId,
            nama_kelas: nama_kelas || null,
            sesi: 1,
            password: '123456'
          });
        }
      }

      if (dataToImport.length === 0) {
        showToast('Tidak ada data valid yang dapat diproses.', 'warning');
        return;
      }

      await api.batchImportPeserta(dataToImport);
      showToast(`Berhasil mengimpor ${dataToImport.length} peserta ujian!`, 'success');
      setImportModalOpen(false);
      setImportCsvText('');
      fetchData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <Users className="w-6 h-6 text-blue-600" />
            <span>Master Data Peserta Ujian</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
            Daftar lengkap siswa peserta ujian, nomor peserta, ruang, dan nomor meja
          </p>
        </div>
        <div className="flex items-center gap-2 self-start sm:self-auto">
          <button
            onClick={() => setImportModalOpen(true)}
            className="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs flex items-center gap-1.5 transition-all"
          >
            <Upload className="w-4 h-4 text-slate-600" />
            <span>Impor Data CSV</span>
          </button>
          <button
            onClick={openCreate}
            className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 flex items-center gap-2 transition-all"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Peserta</span>
          </button>
        </div>
      </div>

      {/* Filter and Search Bar */}
      <div className="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row gap-3 items-center justify-between">
        <div className="flex flex-wrap items-center gap-3 w-full md:w-auto">
          {/* Search */}
          <div className="relative w-full sm:w-64">
            <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder="Cari nama, no peserta, NISN..."
              className="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
            />
          </div>

          {/* Filter Kelas */}
          <select
            value={selectedKelas}
            onChange={(e) => setSelectedKelas(e.target.value)}
            className="px-3 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm bg-white text-slate-700 focus:ring-2 focus:ring-blue-500/20"
          >
            <option value="">Semua Kelas</option>
            {kelasList.map(k => (
              <option key={k.id} value={k.id}>{k.nama_kelas}</option>
            ))}
          </select>

          {/* Filter Ruang */}
          <select
            value={selectedRuang}
            onChange={(e) => setSelectedRuang(e.target.value)}
            className="px-3 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm bg-white text-slate-700 focus:ring-2 focus:ring-blue-500/20"
          >
            <option value="">Semua Ruangan</option>
            {ruangList.map(r => (
              <option key={r.id} value={r.id}>{r.nama_ruang}</option>
            ))}
          </select>
        </div>

        <div className="text-xs text-slate-500 font-medium self-end md:self-auto">
          Ditemukan: <strong className="text-slate-900">{list.length}</strong> Siswa
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse text-xs sm:text-sm">
            <thead>
              <tr className="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                <th className="py-3.5 px-4">No</th>
                <th className="py-3.5 px-4">Nomor Peserta</th>
                <th className="py-3.5 px-4">Nama Lengkap Siswa</th>
                <th className="py-3.5 px-4">L/P</th>
                <th className="py-3.5 px-4">Kelas</th>
                <th className="py-3.5 px-4">Ruang Ujian</th>
                <th className="py-3.5 px-4 text-center">No Meja</th>
                <th className="py-3.5 px-4 text-center">Sesi</th>
                <th className="py-3.5 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {list.map((item, idx) => (
                <tr key={item.id} className="hover:bg-slate-50/60 transition-colors">
                  <td className="py-3 px-4 text-slate-400 font-mono">{idx + 1}</td>
                  <td className="py-3 px-4 font-mono font-bold text-blue-700">
                    <span className="bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                      {item.no_peserta}
                    </span>
                  </td>
                  <td className="py-3 px-4">
                    <div className="font-bold text-slate-900">{item.nama}</div>
                    <div className="text-[11px] text-slate-500 font-mono mt-0.5">
                      NIS: {item.nis || '-'} &bull; NISN: {item.nisn || '-'}
                    </div>
                  </td>
                  <td className="py-3 px-4">
                    <span className={`px-2 py-0.5 rounded-full font-bold text-[11px] ${
                      item.jenis_kelamin === 'L' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700'
                    }`}>
                      {item.jenis_kelamin}
                    </span>
                  </td>
                  <td className="py-3 px-4 font-semibold text-slate-800">{item.nama_kelas || '-'}</td>
                  <td className="py-3 px-4 text-slate-700">
                    {item.nama_ruang ? (
                      <span className="text-slate-800 font-medium">{item.nama_ruang}</span>
                    ) : (
                      <span className="text-amber-600 font-medium italic">Belum diatur</span>
                    )}
                  </td>
                  <td className="py-3 px-4 text-center font-bold text-slate-800 font-mono">
                    {item.no_meja ? `Meja ${item.no_meja}` : '-'}
                  </td>
                  <td className="py-3 px-4 text-center">
                    <span className="bg-slate-100 text-slate-700 px-2 py-0.5 rounded-lg text-xs font-semibold">
                      Sesi {item.sesi}
                    </span>
                  </td>
                  <td className="py-3 px-4 text-right">
                    <div className="flex items-center justify-end gap-1">
                      <button
                        onClick={() => openEdit(item)}
                        className="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors"
                        title="Edit"
                      >
                        <Edit className="w-4 h-4" />
                      </button>
                      <button
                        onClick={() => handleDelete(item.id, item.nama)}
                        className="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors"
                        title="Hapus"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
              {list.length === 0 && (
                <tr>
                  <td colSpan={9} className="py-8 text-center text-slate-400 text-xs">
                    Tidak ada peserta ujian yang ditemukan.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Add/Edit */}
      <Modal
        isOpen={modalOpen}
        onClose={() => setModalOpen(false)}
        title={editingItem ? 'Edit Data Peserta Ujian' : 'Tambah Peserta Ujian Baru'}
      >
        <form onSubmit={handleSave} className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Nomor Peserta <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={formData.no_peserta}
                onChange={(e) => setFormData({ ...formData, no_peserta: e.target.value })}
                placeholder="01-001-001-8"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Nama Lengkap Siswa <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={formData.nama}
                onChange={(e) => setFormData({ ...formData, nama: e.target.value })}
                placeholder="Nama sesuai ijazah/rapor"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>

          <div className="grid grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">NIS</label>
              <input
                type="text"
                value={formData.nis}
                onChange={(e) => setFormData({ ...formData, nis: e.target.value })}
                placeholder="Nomor Induk Siswa"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">NISN</label>
              <input
                type="text"
                value={formData.nisn}
                onChange={(e) => setFormData({ ...formData, nisn: e.target.value })}
                placeholder="10 digit nomor nasional"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
              <select
                value={formData.jenis_kelamin}
                onChange={(e) => setFormData({ ...formData, jenis_kelamin: e.target.value })}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
              >
                <option value="L">Laki-Laki (L)</option>
                <option value="P">Perempuan (P)</option>
              </select>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Kelas</label>
              <select
                value={formData.kelas_id}
                onChange={(e) => setFormData({ ...formData, kelas_id: e.target.value })}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
              >
                <option value="">-- Pilih Kelas --</option>
                {kelasList.map(k => (
                  <option key={k.id} value={k.id}>{k.nama_kelas}</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Sesi Ujian</label>
              <select
                value={formData.sesi}
                onChange={(e) => setFormData({ ...formData, sesi: parseInt(e.target.value) || 1 })}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
              >
                <option value="1">Sesi 1 (Pagi)</option>
                <option value="2">Sesi 2 (Siang)</option>
                <option value="3">Sesi 3 (Sore)</option>
              </select>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Ruang Ujian</label>
              <select
                value={formData.ruang_id}
                onChange={(e) => setFormData({ ...formData, ruang_id: e.target.value })}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
              >
                <option value="">-- Belum Ditentukan --</option>
                {ruangList.map(r => (
                  <option key={r.id} value={r.id}>{r.nama_ruang} ({r.kode_ruang})</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Nomor Meja</label>
              <input
                type="number"
                value={formData.no_meja}
                onChange={(e) => setFormData({ ...formData, no_meja: parseInt(e.target.value) || 0 })}
                min="0"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">PIN / Password Login CBT</label>
            <input
              type="text"
              value={formData.password}
              onChange={(e) => setFormData({ ...formData, password: e.target.value })}
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
            />
          </div>

          <div className="flex justify-end gap-3 pt-3 border-t border-slate-100">
            <button
              type="button"
              onClick={() => setModalOpen(false)}
              className="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100"
            >
              Batal
            </button>
            <button
              type="submit"
              className="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20"
            >
              Simpan Data Peserta
            </button>
          </div>
        </form>
      </Modal>

      {/* Modal Batch Import CSV */}
      <Modal
        isOpen={importModalOpen}
        onClose={() => setImportModalOpen(false)}
        title="Impor Peserta Massal (CSV / Tab Delimited)"
      >
        <form onSubmit={handleImportCsv} className="space-y-4">
          <div className="p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-900 leading-relaxed">
            <p className="font-bold mb-1">Format Baris (Pisahkan dengan koma atau tab):</p>
            <code>No_Peserta, Nama_Siswa, NIS, NISN, JK (L/P), Nama_Kelas</code>
            <p className="mt-1 text-slate-600">Contoh:<br/>01-001-021-4, Bintang Ramadhan, 24250121, 0081234599, L, X MIPA 1</p>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">
              Tempelkan (Paste) Teks CSV di Bawah:
            </label>
            <textarea
              rows={8}
              value={importCsvText}
              onChange={(e) => setImportCsvText(e.target.value)}
              placeholder="01-001-021-4, Bintang Ramadhan, 24250121, 0081234599, L, X MIPA 1&#10;01-001-022-3, Cinta Laura, 24250122, 0081234598, P, X MIPA 1"
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              required
            />
          </div>

          <div className="flex justify-end gap-3 pt-3 border-t border-slate-100">
            <button
              type="button"
              onClick={() => setImportModalOpen(false)}
              className="px-4 py-2 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100"
            >
              Batal
            </button>
            <button
              type="submit"
              className="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20"
            >
              Proses Impor
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
