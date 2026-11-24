import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import { BookOpen, Plus, Edit, Trash2, Search, FileEdit, Award, Clock } from 'lucide-react';
import Modal from '../components/Modal';

export default function DataMapel() {
  const { showToast } = useApp();
  const [activeTab, setActiveTab] = useState('tulis'); // 'tulis' or 'praktik'
  const [list, setList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [searchTerm, setSearchTerm] = useState('');

  const [formData, setFormData] = useState({
    kode_mapel: '',
    nama_mapel: '',
    jenis: 'tulis',
    tingkat: 'Semua',
    durasi_menit: 90,
    jumlah_soal: 40,
    kriteria_penilaian: ''
  });

  const fetchList = async () => {
    try {
      const res = await api.getMapel(activeTab);
      if (res.success) {
        setList(res.data);
      }
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchList();
  }, [activeTab]);

  const openCreate = () => {
    setEditingItem(null);
    setFormData({
      kode_mapel: '',
      nama_mapel: '',
      jenis: activeTab,
      tingkat: 'Semua',
      durasi_menit: activeTab === 'tulis' ? 90 : 60,
      jumlah_soal: activeTab === 'tulis' ? 40 : 0,
      kriteria_penilaian: ''
    });
    setModalOpen(true);
  };

  const openEdit = (item) => {
    setEditingItem(item);
    setFormData({
      kode_mapel: item.kode_mapel,
      nama_mapel: item.nama_mapel,
      jenis: item.jenis,
      tingkat: item.tingkat || 'Semua',
      durasi_menit: item.durasi_menit || 90,
      jumlah_soal: item.jumlah_soal || 0,
      kriteria_penilaian: item.kriteria_penilaian || ''
    });
    setModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      if (editingItem) {
        await api.updateMapel(editingItem.id, formData);
        showToast('Mata pelajaran berhasil diperbarui!', 'success');
      } else {
        await api.createMapel(formData);
        showToast('Mata pelajaran berhasil ditambahkan!', 'success');
      }
      setModalOpen(false);
      fetchList();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleDelete = async (id, nama) => {
    if (!window.confirm(`Yakin ingin menghapus mata pelajaran ${nama}?`)) return;
    try {
      await api.deleteMapel(id);
      showToast('Mata pelajaran berhasil dihapus.', 'success');
      fetchList();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const filteredList = list.filter(m =>
    m.nama_mapel.toLowerCase().includes(searchTerm.toLowerCase()) ||
    m.kode_mapel.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <BookOpen className="w-6 h-6 text-blue-600" />
            <span>Master Data Mata Pelajaran</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
            Daftar mata pelajaran ujian tulis dan mata pelajaran ujian praktik
          </p>
        </div>
        <button
          onClick={openCreate}
          className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 flex items-center gap-2 transition-all self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Mapel {activeTab === 'tulis' ? 'Ujian Tulis' : 'Ujian Praktik'}</span>
        </button>
      </div>

      {/* Tab Switcher: Ujian Tulis vs Ujian Praktik */}
      <div className="flex items-center gap-2 p-1.5 bg-slate-200/60 rounded-2xl w-fit">
        <button
          onClick={() => setActiveTab('tulis')}
          className={`flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all ${
            activeTab === 'tulis'
              ? 'bg-white text-blue-700 shadow-xs'
              : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <FileEdit className="w-4 h-4" />
          <span>Mata Pelajaran Ujian Tulis</span>
        </button>
        <button
          onClick={() => setActiveTab('praktik')}
          className={`flex items-center gap-2 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all ${
            activeTab === 'praktik'
              ? 'bg-white text-blue-700 shadow-xs'
              : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <Award className="w-4 h-4" />
          <span>Mata Pelajaran Ujian Praktik</span>
        </button>
      </div>

      {/* Filter and Search */}
      <div className="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row gap-3 items-center justify-between">
        <div className="relative w-full sm:w-72">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            placeholder="Cari nama atau kode mapel..."
            className="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
          />
        </div>
        <div className="text-xs text-slate-500 font-medium">
          Total: <strong className="text-slate-800">{list.length}</strong> Mapel ({activeTab === 'tulis' ? 'Ujian Tulis' : 'Ujian Praktik'})
        </div>
      </div>

      {/* Data Table */}
      <div className="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse text-xs sm:text-sm">
            <thead>
              <tr className="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                <th className="py-3.5 px-4">No</th>
                <th className="py-3.5 px-4">Kode</th>
                <th className="py-3.5 px-4">Nama Mata Pelajaran</th>
                <th className="py-3.5 px-4">Sasaran Kelas</th>
                <th className="py-3.5 px-4 text-center">Durasi</th>
                {activeTab === 'tulis' ? (
                  <th className="py-3.5 px-4 text-center">Jumlah Soal</th>
                ) : (
                  <th className="py-3.5 px-4">Kriteria / Rubrik Penilaian</th>
                )}
                <th className="py-3.5 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filteredList.map((item, idx) => (
                <tr key={item.id} className="hover:bg-slate-50/60 transition-colors">
                  <td className="py-3 px-4 text-slate-400 font-mono">{idx + 1}</td>
                  <td className="py-3 px-4 font-mono font-bold text-blue-700">
                    <span className="bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                      {item.kode_mapel}
                    </span>
                  </td>
                  <td className="py-3 px-4 font-bold text-slate-800">{item.nama_mapel}</td>
                  <td className="py-3 px-4 text-slate-600">{item.tingkat || 'Semua'}</td>
                  <td className="py-3 px-4 text-center font-medium text-slate-700">
                    <span className="inline-flex items-center gap-1">
                      <Clock className="w-3 h-3 text-slate-400" />
                      {item.durasi_menit} Menit
                    </span>
                  </td>
                  {activeTab === 'tulis' ? (
                    <td className="py-3 px-4 text-center font-bold text-slate-800">
                      {item.jumlah_soal} Butir
                    </td>
                  ) : (
                    <td className="py-3 px-4 text-slate-600 max-w-xs truncate" title={item.kriteria_penilaian}>
                      {item.kriteria_penilaian || '-'}
                    </td>
                  )}
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
                        onClick={() => handleDelete(item.id, item.nama_mapel)}
                        className="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors"
                        title="Hapus"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
              {filteredList.length === 0 && (
                <tr>
                  <td colSpan={7} className="py-8 text-center text-slate-400 text-xs">
                    Belum ada mata pelajaran dalam kategori ini.
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
        title={editingItem ? `Edit Mapel ${formData.jenis === 'tulis' ? 'Ujian Tulis' : 'Ujian Praktik'}` : `Tambah Mapel ${formData.jenis === 'tulis' ? 'Ujian Tulis' : 'Ujian Praktik'}`}
      >
        <form onSubmit={handleSave} className="space-y-4">
          <div className="grid grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Kode Mapel <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={formData.kode_mapel}
                onChange={(e) => setFormData({ ...formData, kode_mapel: e.target.value.toUpperCase() })}
                placeholder="MTK, FIS, PAI..."
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 uppercase"
              />
            </div>

            <div className="col-span-2">
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Nama Mata Pelajaran <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={formData.nama_mapel}
                onChange={(e) => setFormData({ ...formData, nama_mapel: e.target.value })}
                placeholder="Contoh: Matematika Umum"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>

          <div className="grid grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Jenis Ujian</label>
              <select
                value={formData.jenis}
                onChange={(e) => setFormData({ ...formData, jenis: e.target.value })}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
              >
                <option value="tulis">Ujian Tulis</option>
                <option value="praktik">Ujian Praktik</option>
              </select>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Sasaran Tingkat</label>
              <input
                type="text"
                value={formData.tingkat}
                onChange={(e) => setFormData({ ...formData, tingkat: e.target.value })}
                placeholder="Semua, X, XI, XII..."
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Durasi (Menit)</label>
              <input
                type="number"
                value={formData.durasi_menit}
                onChange={(e) => setFormData({ ...formData, durasi_menit: parseInt(e.target.value) || 0 })}
                min="10"
                max="300"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>
          </div>

          {formData.jenis === 'tulis' ? (
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Jumlah Butir Soal</label>
              <input
                type="number"
                value={formData.jumlah_soal}
                onChange={(e) => setFormData({ ...formData, jumlah_soal: parseInt(e.target.value) || 0 })}
                min="0"
                max="200"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>
          ) : (
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Kriteria Penilaian / Rubrik Asesmen</label>
              <textarea
                rows={3}
                value={formData.kriteria_penilaian}
                onChange={(e) => setFormData({ ...formData, kriteria_penilaian: e.target.value })}
                placeholder="Indikator pencapaian kompetensi, aspek unjuk kerja..."
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          )}

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
              Simpan Mata Pelajaran
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
