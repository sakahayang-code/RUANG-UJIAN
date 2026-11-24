import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import { Award, Plus, Edit, Trash2, Search, Phone, Building } from 'lucide-react';
import Modal from '../components/Modal';

export default function DataPenguji() {
  const { showToast } = useApp();
  const [list, setList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [searchTerm, setSearchTerm] = useState('');

  const [formData, setFormData] = useState({
    nama: '',
    nip: '',
    mata_pelajaran: '',
    asal_instansi: '',
    no_hp: '',
    keterangan: ''
  });

  const fetchList = async () => {
    try {
      const res = await api.getPenguji();
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
      mata_pelajaran: '',
      asal_instansi: 'SMA Negeri 1 Merdeka Belajar',
      no_hp: '',
      keterangan: 'Penguji Utama'
    });
    setModalOpen(true);
  };

  const openEdit = (item) => {
    setEditingItem(item);
    setFormData({
      nama: item.nama,
      nip: item.nip || '',
      mata_pelajaran: item.mata_pelajaran,
      asal_instansi: item.asal_instansi || '',
      no_hp: item.no_hp || '',
      keterangan: item.keterangan || ''
    });
    setModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      if (editingItem) {
        await api.updatePenguji(editingItem.id, formData);
        showToast('Data penguji praktik berhasil diperbarui!', 'success');
      } else {
        await api.createPenguji(formData);
        showToast('Penguji praktik baru berhasil ditambahkan!', 'success');
      }
      setModalOpen(false);
      fetchList();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleDelete = async (id, nama) => {
    if (!window.confirm(`Yakin ingin menghapus penguji praktik ${nama}?`)) return;
    try {
      await api.deletePenguji(id);
      showToast('Penguji praktik berhasil dihapus.', 'success');
      fetchList();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const filteredList = list.filter(p =>
    p.nama.toLowerCase().includes(searchTerm.toLowerCase()) ||
    p.mata_pelajaran.toLowerCase().includes(searchTerm.toLowerCase()) ||
    (p.asal_instansi && p.asal_instansi.toLowerCase().includes(searchTerm.toLowerCase()))
  );

  return (
    <div className="space-y-6 animate-fade-in">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <Award className="w-6 h-6 text-blue-600" />
            <span>Master Data Penguji Praktik</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
            Daftar tim penguji internal dan eksternal (Mitra DUDI / Industri) untuk Ujian Praktik
          </p>
        </div>
        <button
          onClick={openCreate}
          className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 flex items-center gap-2 transition-all self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Penguji Praktik</span>
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
            placeholder="Cari penguji, mapel, instansi..."
            className="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
          />
        </div>
        <div className="text-xs text-slate-500 font-medium">
          Total Penguji: <strong className="text-slate-900">{list.length}</strong> Orang
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse text-xs sm:text-sm">
            <thead>
              <tr className="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                <th className="py-3.5 px-4">No</th>
                <th className="py-3.5 px-4">Nama Lengkap &amp; Gelar</th>
                <th className="py-3.5 px-4">NIP</th>
                <th className="py-3.5 px-4">Bidang Mapel Praktik</th>
                <th className="py-3.5 px-4">Asal Sekolah / Lembaga</th>
                <th className="py-3.5 px-4">Kontak / WA</th>
                <th className="py-3.5 px-4">Keterangan / Peran</th>
                <th className="py-3.5 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filteredList.map((item, idx) => (
                <tr key={item.id} className="hover:bg-slate-50/60 transition-colors">
                  <td className="py-3 px-4 text-slate-400 font-mono">{idx + 1}</td>
                  <td className="py-3 px-4 font-bold text-slate-900">{item.nama}</td>
                  <td className="py-3 px-4 font-mono text-slate-600 text-xs">{item.nip || '-'}</td>
                  <td className="py-3 px-4 font-semibold text-blue-700">
                    <span className="bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                      {item.mata_pelajaran}
                    </span>
                  </td>
                  <td className="py-3 px-4 text-slate-700">{item.asal_instansi || '-'}</td>
                  <td className="py-3 px-4 font-mono text-xs text-slate-600">{item.no_hp || '-'}</td>
                  <td className="py-3 px-4 text-slate-600 text-xs">{item.keterangan || '-'}</td>
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
                    Tidak ada data penguji praktik.
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
        title={editingItem ? 'Edit Data Penguji Praktik' : 'Tambah Penguji Praktik Baru'}
      >
        <form onSubmit={handleSave} className="space-y-4">
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">
              Nama Lengkap &amp; Gelar <span className="text-rose-500">*</span>
            </label>
            <input
              type="text"
              value={formData.nama}
              onChange={(e) => setFormData({ ...formData, nama: e.target.value })}
              placeholder="Ir. Agus Pratama, M.Kom."
              required
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">NIP (Jika Ada)</label>
              <input
                type="text"
                value={formData.nip}
                onChange={(e) => setFormData({ ...formData, nip: e.target.value })}
                placeholder="NIP atau tanda strip (-)"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Mata Pelajaran Praktik <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={formData.mata_pelajaran}
                onChange={(e) => setFormData({ ...formData, mata_pelajaran: e.target.value })}
                placeholder="Praktik Informatika / Komputer"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Asal Lembaga / Instansi / DUDI</label>
              <input
                type="text"
                value={formData.asal_instansi}
                onChange={(e) => setFormData({ ...formData, asal_instansi: e.target.value })}
                placeholder="SMA Negeri 1 / PT Inovasi..."
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp / HP</label>
              <input
                type="text"
                value={formData.no_hp}
                onChange={(e) => setFormData({ ...formData, no_hp: e.target.value })}
                placeholder="081788990011"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Peran / Keterangan Tambahan</label>
            <input
              type="text"
              value={formData.keterangan}
              onChange={(e) => setFormData({ ...formData, keterangan: e.target.value })}
              placeholder="Penguji Internal, Penguji Eksternal / DU/DI, dll."
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
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
              Simpan Data Penguji
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
