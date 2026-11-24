import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import { ShieldCheck, Plus, Edit, Trash2, Search, Phone, Building } from 'lucide-react';
import Modal from '../components/Modal';

export default function DataPengawas() {
  const { showToast } = useApp();
  const [list, setList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [searchTerm, setSearchTerm] = useState('');

  const [formData, setFormData] = useState({
    kode_pengawas: '',
    nama: '',
    nip: '',
    asal_sekolah: '',
    mata_pelajaran_ampu: '',
    no_hp: ''
  });

  const fetchList = async () => {
    try {
      const res = await api.getPengawas();
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
    const nextCode = `P${String(list.length + 1).padStart(2, '0')}`;
    setFormData({
      kode_pengawas: nextCode,
      nama: '',
      nip: '',
      asal_sekolah: 'SMA Negeri 1 Merdeka Belajar',
      mata_pelajaran_ampu: '',
      no_hp: ''
    });
    setModalOpen(true);
  };

  const openEdit = (item) => {
    setEditingItem(item);
    setFormData({
      kode_pengawas: item.kode_pengawas || '',
      nama: item.nama,
      nip: item.nip || '',
      asal_sekolah: item.asal_sekolah || '',
      mata_pelajaran_ampu: item.mata_pelajaran_ampu || '',
      no_hp: item.no_hp || ''
    });
    setModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      if (editingItem) {
        await api.updatePengawas(editingItem.id, formData);
        showToast('Data pengawas ruang berhasil diperbarui!', 'success');
      } else {
        await api.createPengawas(formData);
        showToast('Pengawas ruang baru berhasil ditambahkan!', 'success');
      }
      setModalOpen(false);
      fetchList();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleDelete = async (id, nama) => {
    if (!window.confirm(`Yakin ingin menghapus pengawas ${nama}?`)) return;
    try {
      await api.deletePengawas(id);
      showToast('Pengawas ruang berhasil dihapus.', 'success');
      fetchList();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const filteredList = list.filter(p =>
    p.nama.toLowerCase().includes(searchTerm.toLowerCase()) ||
    (p.kode_pengawas && p.kode_pengawas.toLowerCase().includes(searchTerm.toLowerCase())) ||
    (p.asal_sekolah && p.asal_sekolah.toLowerCase().includes(searchTerm.toLowerCase()))
  );

  return (
    <div className="space-y-6 animate-fade-in">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <ShieldCheck className="w-6 h-6 text-blue-600" />
            <span>Master Data Pengawas Ruang</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
            Daftar guru pengawas ruang ujian tulis (internal maupun silang antar sekolah)
          </p>
        </div>
        <button
          onClick={openCreate}
          className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 flex items-center gap-2 transition-all self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Pengawas Ruang</span>
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
            placeholder="Cari nama, kode pengawas, sekolah..."
            className="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
          />
        </div>
        <div className="text-xs text-slate-500 font-medium">
          Total Pengawas: <strong className="text-slate-900">{list.length}</strong> Guru
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse text-xs sm:text-sm">
            <thead>
              <tr className="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                <th className="py-3.5 px-4">No</th>
                <th className="py-3.5 px-4">Kode</th>
                <th className="py-3.5 px-4">Nama Lengkap &amp; Gelar</th>
                <th className="py-3.5 px-4">NIP / NUPTK</th>
                <th className="py-3.5 px-4">Asal Sekolah</th>
                <th className="py-3.5 px-4">Mata Pelajaran Ampu</th>
                <th className="py-3.5 px-4">Kontak / WA</th>
                <th className="py-3.5 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filteredList.map((item, idx) => (
                <tr key={item.id} className="hover:bg-slate-50/60 transition-colors">
                  <td className="py-3 px-4 text-slate-400 font-mono">{idx + 1}</td>
                  <td className="py-3 px-4 font-mono font-bold text-blue-700">
                    <span className="bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                      {item.kode_pengawas || '-'}
                    </span>
                  </td>
                  <td className="py-3 px-4 font-bold text-slate-900">{item.nama}</td>
                  <td className="py-3 px-4 font-mono text-slate-600 text-xs">{item.nip || '-'}</td>
                  <td className="py-3 px-4 text-slate-700">{item.asal_sekolah || '-'}</td>
                  <td className="py-3 px-4 text-slate-600">{item.mata_pelajaran_ampu || '-'}</td>
                  <td className="py-3 px-4 font-mono text-xs text-slate-600">{item.no_hp || '-'}</td>
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
              {filteredList.length === 0 && (
                <tr>
                  <td colSpan={8} className="py-8 text-center text-slate-400 text-xs">
                    Tidak ada data pengawas ruang yang sesuai.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal */}
      <Modal
        isOpen={modalOpen}
        onClose={() => setModalOpen(false)}
        title={editingItem ? 'Edit Data Pengawas Ruang' : 'Tambah Pengawas Ruang Baru'}
      >
        <form onSubmit={handleSave} className="space-y-4">
          <div className="grid grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Kode Pengawas</label>
              <input
                type="text"
                value={formData.kode_pengawas}
                onChange={(e) => setFormData({ ...formData, kode_pengawas: e.target.value })}
                placeholder="P01"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
            <div className="col-span-2">
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Nama Lengkap &amp; Gelar <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={formData.nama}
                onChange={(e) => setFormData({ ...formData, nama: e.target.value })}
                placeholder="Drs. Hendra Gunawan"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">NIP / NUPTK</label>
              <input
                type="text"
                value={formData.nip}
                onChange={(e) => setFormData({ ...formData, nip: e.target.value })}
                placeholder="19680315 199303 1 005"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Asal Sekolah</label>
              <input
                type="text"
                value={formData.asal_sekolah}
                onChange={(e) => setFormData({ ...formData, asal_sekolah: e.target.value })}
                placeholder="SMA Negeri 1 Merdeka Belajar"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Mata Pelajaran yang Diampu</label>
              <input
                type="text"
                value={formData.mata_pelajaran_ampu}
                onChange={(e) => setFormData({ ...formData, mata_pelajaran_ampu: e.target.value })}
                placeholder="Sosiologi, Matematika..."
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp / HP</label>
              <input
                type="text"
                value={formData.no_hp}
                onChange={(e) => setFormData({ ...formData, no_hp: e.target.value })}
                placeholder="081211112222"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>
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
              Simpan Data Pengawas
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
