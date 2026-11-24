import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import { UserCheck, Plus, Edit, Trash2, Phone, Briefcase } from 'lucide-react';
import Modal from '../components/Modal';

export default function DataPanitia() {
  const { showToast } = useApp();
  const [list, setList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);

  const [formData, setFormData] = useState({
    nama: '',
    nip: '',
    jabatan_struktural: '',
    jabatan_panitia: 'Ketua Panitia',
    no_hp: '',
    tugas: '',
    urutan: 1
  });

  const fetchList = async () => {
    try {
      const res = await api.getPanitia();
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
      nama: '',
      nip: '',
      jabatan_struktural: '',
      jabatan_panitia: 'Seksi Kegiatan',
      no_hp: '',
      tugas: '',
      urutan: list.length + 1
    });
    setModalOpen(true);
  };

  const openEdit = (item) => {
    setEditingItem(item);
    setFormData({
      nama: item.nama,
      nip: item.nip || '',
      jabatan_struktural: item.jabatan_struktural || '',
      jabatan_panitia: item.jabatan_panitia,
      no_hp: item.no_hp || '',
      tugas: item.tugas || '',
      urutan: item.urutan || 1
    });
    setModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      if (editingItem) {
        await api.updatePanitia(editingItem.id, formData);
        showToast('Data panitia berhasil diperbarui!', 'success');
      } else {
        await api.createPanitia(formData);
        showToast('Panitia baru berhasil ditambahkan!', 'success');
      }
      setModalOpen(false);
      fetchList();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleDelete = async (id, nama) => {
    if (!window.confirm(`Yakin ingin menghapus panitia ${nama}?`)) return;
    try {
      await api.deletePanitia(id);
      showToast('Panitia berhasil dihapus.', 'success');
      fetchList();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  return (
    <div className="space-y-6 animate-fade-in">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <UserCheck className="w-6 h-6 text-blue-600" />
            <span>Master Data Panitia Ujian</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
            Susunan kepanitiaan ujian sekolah, struktur organisasi, dan uraian tugas
          </p>
        </div>
        <button
          onClick={openCreate}
          className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 flex items-center gap-2 transition-all self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Anggota Panitia</span>
        </button>
      </div>

      {/* Panitia Cards Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {list.map((item) => (
          <div
            key={item.id}
            className="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between"
          >
            <div>
              <div className="flex items-start justify-between gap-2">
                <span className="px-2.5 py-1 rounded-xl bg-blue-50 text-blue-700 font-bold text-xs border border-blue-100">
                  {item.jabatan_panitia}
                </span>
                <span className="text-[11px] font-mono font-semibold text-slate-400">
                  Urutan #{item.urutan}
                </span>
              </div>

              <h3 className="font-bold text-slate-900 text-base mt-3 leading-snug">
                {item.nama}
              </h3>

              <p className="text-xs text-slate-500 font-mono mt-0.5">
                NIP: {item.nip || '-'}
              </p>

              {item.jabatan_struktural && (
                <p className="text-xs text-slate-600 font-medium mt-1">
                  {item.jabatan_struktural}
                </p>
              )}

              {item.tugas && (
                <div className="mt-3 p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-600 leading-relaxed">
                  <strong className="text-slate-700 block text-[11px] uppercase font-bold mb-0.5">Uraian Tugas:</strong>
                  {item.tugas}
                </div>
              )}
            </div>

            <div className="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
              <div className="flex items-center gap-1.5 truncate">
                {item.no_hp && (
                  <>
                    <Phone className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                    <span className="font-mono truncate">{item.no_hp}</span>
                  </>
                )}
              </div>
              <div className="flex items-center gap-1 shrink-0">
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
            </div>
          </div>
        ))}
      </div>

      {/* Modal */}
      <Modal
        isOpen={modalOpen}
        onClose={() => setModalOpen(false)}
        title={editingItem ? 'Edit Data Panitia' : 'Tambah Panitia Baru'}
      >
        <form onSubmit={handleSave} className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="col-span-2">
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Nama Lengkap &amp; Gelar <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={formData.nama}
                onChange={(e) => setFormData({ ...formData, nama: e.target.value })}
                placeholder="Dr. H. Ahmad Fauzi, M.Pd."
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">NIP / NUPTK</label>
              <input
                type="text"
                value={formData.nip}
                onChange={(e) => setFormData({ ...formData, nip: e.target.value })}
                placeholder="19740512 199903 1 002"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Jabatan Kepanitiaan <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={formData.jabatan_panitia}
                onChange={(e) => setFormData({ ...formData, jabatan_panitia: e.target.value })}
                placeholder="Ketua, Sekretaris, Bendahara, Seksi..."
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Jabatan di Sekolah</label>
              <input
                type="text"
                value={formData.jabatan_struktural}
                onChange={(e) => setFormData({ ...formData, jabatan_struktural: e.target.value })}
                placeholder="Wakasek Kurikulum / Guru..."
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp / HP</label>
              <input
                type="text"
                value={formData.no_hp}
                onChange={(e) => setFormData({ ...formData, no_hp: e.target.value })}
                placeholder="081234567890"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Uraian Tugas Pokok</label>
            <textarea
              rows={3}
              value={formData.tugas}
              onChange={(e) => setFormData({ ...formData, tugas: e.target.value })}
              placeholder="Deskripsi tugas dan tanggung jawab..."
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Urutan Struktur (Angka)</label>
            <input
              type="number"
              value={formData.urutan}
              onChange={(e) => setFormData({ ...formData, urutan: parseInt(e.target.value) || 1 })}
              min="1"
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
              Simpan Data Panitia
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
