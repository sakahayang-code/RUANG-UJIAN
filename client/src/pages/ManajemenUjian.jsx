import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import { CalendarRange, Plus, Edit, Trash2, CheckCircle2, Clock, Calendar, Sparkles } from 'lucide-react';
import Modal from '../components/Modal';

export default function ManajemenUjian() {
  const { ujian: activeUjian, refreshGlobalData, showToast } = useApp();
  const [list, setList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);

  const [formData, setFormData] = useState({
    nama_ujian: '',
    tahun_ajaran: '2024/2025',
    semester: 'Genap',
    tanggal_mulai: '',
    tanggal_selesai: '',
    keterangan: '',
    is_active: 0
  });

  const fetchList = async () => {
    try {
      const res = await api.getUjianList();
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
  }, []);

  const openCreate = () => {
    setEditingItem(null);
    setFormData({
      nama_ujian: '',
      tahun_ajaran: '2024/2025',
      semester: 'Genap',
      tanggal_mulai: '',
      tanggal_selesai: '',
      keterangan: '',
      is_active: list.length === 0 ? 1 : 0
    });
    setModalOpen(true);
  };

  const openEdit = (item) => {
    setEditingItem(item);
    setFormData({
      nama_ujian: item.nama_ujian,
      tahun_ajaran: item.tahun_ajaran,
      semester: item.semester,
      tanggal_mulai: item.tanggal_mulai || '',
      tanggal_selesai: item.tanggal_selesai || '',
      keterangan: item.keterangan || '',
      is_active: item.is_active
    });
    setModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      if (editingItem) {
        await api.updateUjian(editingItem.id, formData);
        showToast('Kegiatan ujian berhasil diperbarui!', 'success');
      } else {
        await api.createUjian(formData);
        showToast('Kegiatan ujian baru berhasil ditambahkan!', 'success');
      }
      setModalOpen(false);
      await fetchList();
      await refreshGlobalData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleActivate = async (id) => {
    try {
      await api.activateUjian(id);
      showToast('Ujian aktif berhasil diubah!', 'success');
      await fetchList();
      await refreshGlobalData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Apakah Anda yakin ingin menghapus kegiatan ujian ini?')) return;
    try {
      await api.deleteUjian(id);
      showToast('Kegiatan ujian berhasil dihapus.', 'success');
      await fetchList();
      await refreshGlobalData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  return (
    <div className="space-y-6 animate-fade-in">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <CalendarRange className="w-6 h-6 text-blue-600" />
            <span>Manajemen Kegiatan Ujian</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
            Kelola periode kegiatan ujian sekolah (PAT, PAS, ASAJ, PTS) dan tentukan kegiatan yang sedang aktif
          </p>
        </div>
        <button
          onClick={openCreate}
          className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 flex items-center gap-2 transition-all self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Kegiatan Ujian</span>
        </button>
      </div>

      {/* List Ujian Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {list.map((item) => {
          const isActive = item.is_active === 1;
          return (
            <div
              key={item.id}
              className={`bg-white rounded-3xl p-6 border transition-all ${
                isActive
                  ? 'border-blue-500 ring-2 ring-blue-500/10 shadow-md shadow-blue-500/5'
                  : 'border-slate-200/80 shadow-xs hover:border-slate-300'
              }`}
            >
              <div className="flex items-start justify-between gap-3">
                <div className="flex-1">
                  <div className="flex items-center gap-2 flex-wrap">
                    {isActive ? (
                      <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                        <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Sedang Aktif Digunakan
                      </span>
                    ) : (
                      <span className="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[11px] font-semibold">
                        Arsip Kegiatan
                      </span>
                    )}
                    <span className="text-xs text-slate-500 font-medium">
                      TP {item.tahun_ajaran} &bull; Semester {item.semester}
                    </span>
                  </div>

                  <h3 className="font-bold text-slate-900 text-base sm:text-lg mt-2 leading-snug">
                    {item.nama_ujian}
                  </h3>

                  <div className="flex items-center gap-4 mt-3 text-xs text-slate-500">
                    <div className="flex items-center gap-1.5">
                      <Calendar className="w-4 h-4 text-slate-400" />
                      <span>
                        {item.tanggal_mulai ? `${item.tanggal_mulai} s.d. ${item.tanggal_selesai}` : 'Tanggal belum diatur'}
                      </span>
                    </div>
                  </div>

                  {item.keterangan && (
                    <p className="mt-2.5 text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                      {item.keterangan}
                    </p>
                  )}
                </div>
              </div>

              {/* Card Footer Actions */}
              <div className="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                <div>
                  {!isActive && (
                    <button
                      onClick={() => handleActivate(item.id)}
                      className="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold transition-colors"
                    >
                      Aktifkan Ujian Ini
                    </button>
                  )}
                </div>
                <div className="flex items-center gap-1">
                  <button
                    onClick={() => openEdit(item)}
                    className="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors"
                    title="Edit"
                  >
                    <Edit className="w-4 h-4" />
                  </button>
                  {list.length > 1 && (
                    <button
                      onClick={() => handleDelete(item.id)}
                      className="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors"
                      title="Hapus"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  )}
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {/* Modal Add/Edit */}
      <Modal
        isOpen={modalOpen}
        onClose={() => setModalOpen(false)}
        title={editingItem ? 'Edit Kegiatan Ujian' : 'Tambah Kegiatan Ujian Baru'}
      >
        <form onSubmit={handleSave} className="space-y-4">
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">
              Nama Kegiatan Ujian <span className="text-rose-500">*</span>
            </label>
            <input
              type="text"
              value={formData.nama_ujian}
              onChange={(e) => setFormData({ ...formData, nama_ujian: e.target.value })}
              placeholder="Contoh: Penilaian Akhir Tahun (PAT) TP 2024/2025"
              required
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Tahun Ajaran</label>
              <input
                type="text"
                value={formData.tahun_ajaran}
                onChange={(e) => setFormData({ ...formData, tahun_ajaran: e.target.value })}
                placeholder="2024/2025"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Semester</label>
              <select
                value={formData.semester}
                onChange={(e) => setFormData({ ...formData, semester: e.target.value })}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
              >
                <option value="Ganjil">Ganjil</option>
                <option value="Genap">Genap</option>
              </select>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai</label>
              <input
                type="date"
                value={formData.tanggal_mulai}
                onChange={(e) => setFormData({ ...formData, tanggal_mulai: e.target.value })}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Tanggal Selesai</label>
              <input
                type="date"
                value={formData.tanggal_selesai}
                onChange={(e) => setFormData({ ...formData, tanggal_selesai: e.target.value })}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Catatan</label>
            <textarea
              rows={3}
              value={formData.keterangan}
              onChange={(e) => setFormData({ ...formData, keterangan: e.target.value })}
              placeholder="Catatan tambahan mengenai pelaksanaan ujian..."
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
            />
          </div>

          <div className="flex items-center gap-2 pt-1">
            <input
              type="checkbox"
              id="is_active"
              checked={formData.is_active === 1}
              onChange={(e) => setFormData({ ...formData, is_active: e.target.checked ? 1 : 0 })}
              className="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500"
            />
            <label htmlFor="is_active" className="text-xs font-semibold text-slate-700 cursor-pointer">
              Tetapkan sebagai kegiatan ujian aktif saat ini
            </label>
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
              Simpan Kegiatan
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
