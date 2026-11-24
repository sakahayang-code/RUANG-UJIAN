import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import { ClipboardList, Plus, Edit, Trash2, CheckCircle2, Clock, AlertCircle, DollarSign } from 'lucide-react';
import Modal from '../components/Modal';

export default function ProgramKerja() {
  const { showToast } = useApp();
  const [list, setList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);

  const [formData, setFormData] = useState({
    tahap: 'Persiapan',
    kegiatan: '',
    waktu_pelaksanaan: '',
    penanggung_jawab: '',
    anggaran: 0,
    status: 'Belum',
    keterangan: '',
    urutan: 1
  });

  const fetchList = async () => {
    try {
      const res = await api.getProker();
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

  const openCreate = (defaultTahap = 'Persiapan') => {
    setEditingItem(null);
    setFormData({
      tahap: defaultTahap,
      kegiatan: '',
      waktu_pelaksanaan: '',
      penanggung_jawab: '',
      anggaran: 0,
      status: 'Belum',
      keterangan: '',
      urutan: list.length + 1
    });
    setModalOpen(true);
  };

  const openEdit = (item) => {
    setEditingItem(item);
    setFormData({
      tahap: item.tahap,
      kegiatan: item.kegiatan,
      waktu_pelaksanaan: item.waktu_pelaksanaan,
      penanggung_jawab: item.penanggung_jawab,
      anggaran: item.anggaran || 0,
      status: item.status,
      keterangan: item.keterangan || '',
      urutan: item.urutan || 1
    });
    setModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      if (editingItem) {
        await api.updateProker(editingItem.id, formData);
        showToast('Program kerja berhasil diperbarui!', 'success');
      } else {
        await api.createProker(formData);
        showToast('Program kerja baru berhasil ditambahkan!', 'success');
      }
      setModalOpen(false);
      fetchList();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleDelete = async (id, nama) => {
    if (!window.confirm(`Yakin ingin menghapus kegiatan "${nama}"?`)) return;
    try {
      await api.deleteProker(id);
      showToast('Program kerja berhasil dihapus.', 'success');
      fetchList();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const toggleStatus = async (item) => {
    const nextStatus = item.status === 'Belum' ? 'Sedang' : (item.status === 'Sedang' ? 'Selesai' : 'Belum');
    try {
      await api.updateProker(item.id, { ...item, status: nextStatus });
      fetchList();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const stages = ['Persiapan', 'Pelaksanaan', 'Evaluasi & Pelaporan'];
  const totalAnggaran = list.reduce((acc, curr) => acc + (curr.anggaran || 0), 0);
  const totalSelesai = list.filter(i => i.status === 'Selesai').length;
  const persentase = list.length > 0 ? Math.round((totalSelesai / list.length) * 100) : 0;

  return (
    <div className="space-y-6 animate-fade-in">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <ClipboardList className="w-6 h-6 text-blue-600" />
            <span>Program Kerja Panitia Ujian</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
            Rencana kerja sistematis kepanitiaan ujian: tahap persiapan, pelaksanaan, hingga evaluasi &amp; pelaporan
          </p>
        </div>
        <button
          onClick={() => openCreate('Persiapan')}
          className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 flex items-center gap-2 transition-all self-start sm:self-auto"
        >
          <Plus className="w-4 h-4" />
          <span>Tambah Program Kerja</span>
        </button>
      </div>

      {/* Progress & Budget Summary Banner */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <span className="text-xs font-semibold text-slate-500 uppercase">Progress Kegiatan</span>
            <p className="text-2xl font-extrabold text-slate-900 mt-1">{persentase}% Selesai</p>
            <p className="text-xs text-slate-500 mt-0.5">{totalSelesai} dari {list.length} kegiatan rampung</p>
          </div>
          <div className="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
            {persentase}%
          </div>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <span className="text-xs font-semibold text-slate-500 uppercase">Total Estimasi Anggaran (RAB)</span>
            <p className="text-2xl font-extrabold text-blue-600 font-mono mt-1">
              Rp {totalAnggaran.toLocaleString('id-ID')}
            </p>
            <p className="text-xs text-slate-500 mt-0.5">Alokasi seluruh tahapan kegiatan</p>
          </div>
          <div className="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
            RAB
          </div>
        </div>

        <div className="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <span className="text-xs font-semibold text-slate-500 uppercase">Status Operasional</span>
            <div className="flex items-center gap-2 mt-2">
              <span className="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold">
                {list.filter(i => i.status === 'Selesai').length} Selesai
              </span>
              <span className="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-xs font-bold">
                {list.filter(i => i.status === 'Sedang').length} Berjalan
              </span>
              <span className="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                {list.filter(i => i.status === 'Belum').length} Belum
              </span>
            </div>
          </div>
        </div>
      </div>

      {/* Program Kerja by Stages */}
      {stages.map((stageName) => {
        const stageItems = list.filter(item => item.tahap === stageName);
        return (
          <div key={stageName} className="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div className="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
              <div className="flex items-center gap-2.5">
                <span className="w-3 h-3 rounded-full bg-blue-600"></span>
                <h3 className="font-bold text-slate-800 text-sm sm:text-base">
                  Tahap {stageName}
                </h3>
                <span className="text-xs text-slate-500 font-medium">({stageItems.length} Kegiatan)</span>
              </div>
              <button
                onClick={() => openCreate(stageName)}
                className="text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1"
              >
                <Plus className="w-3.5 h-3.5" />
                <span>Tambah di Tahap Ini</span>
              </button>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                  <tr className="bg-white border-b border-slate-100 text-slate-500 font-semibold text-[11px] uppercase tracking-wider">
                    <th className="py-3 px-4">Urutan</th>
                    <th className="py-3 px-4">Uraian Kegiatan</th>
                    <th className="py-3 px-4">Jadwal / Waktu Pelaksanaan</th>
                    <th className="py-3 px-4">Penanggung Jawab</th>
                    <th className="py-3 px-4 text-right">Alokasi Biaya (Rp)</th>
                    <th className="py-3 px-4 text-center">Status</th>
                    <th className="py-3 px-4 text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {stageItems.map((item) => (
                    <tr key={item.id} className="hover:bg-slate-50/60 transition-colors">
                      <td className="py-3 px-4 text-slate-400 font-mono">#{item.urutan}</td>
                      <td className="py-3 px-4 font-bold text-slate-800 max-w-xs">{item.kegiatan}</td>
                      <td className="py-3 px-4 text-slate-600">{item.waktu_pelaksanaan}</td>
                      <td className="py-3 px-4 font-semibold text-slate-700">{item.penanggung_jawab}</td>
                      <td className="py-3 px-4 text-right font-mono font-semibold text-slate-800">
                        {item.anggaran ? item.anggaran.toLocaleString('id-ID') : '-'}
                      </td>
                      <td className="py-3 px-4 text-center">
                        <button
                          onClick={() => toggleStatus(item)}
                          className={`px-3 py-1 rounded-full text-xs font-bold transition-all shadow-2xs ${
                            item.status === 'Selesai'
                              ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                              : item.status === 'Sedang'
                              ? 'bg-amber-50 text-amber-700 border border-amber-200'
                              : 'bg-slate-100 text-slate-600 border border-slate-200'
                          }`}
                          title="Klik untuk mengubah status"
                        >
                          {item.status}
                        </button>
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
                            onClick={() => handleDelete(item.id, item.kegiatan)}
                            className="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors"
                            title="Hapus"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                  {stageItems.length === 0 && (
                    <tr>
                      <td colSpan={7} className="py-6 text-center text-slate-400 text-xs">
                        Belum ada kegiatan yang didaftarkan pada tahap {stageName}.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        );
      })}

      {/* Modal */}
      <Modal
        isOpen={modalOpen}
        onClose={() => setModalOpen(false)}
        title={editingItem ? 'Edit Program Kerja' : 'Tambah Program Kerja Baru'}
      >
        <form onSubmit={handleSave} className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Tahap Kegiatan</label>
              <select
                value={formData.tahap}
                onChange={(e) => setFormData({ ...formData, tahap: e.target.value })}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
              >
                <option value="Persiapan">Tahap Persiapan</option>
                <option value="Pelaksanaan">Tahap Pelaksanaan</option>
                <option value="Evaluasi & Pelaporan">Tahap Evaluasi &amp; Pelaporan</option>
              </select>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Status Progres</label>
              <select
                value={formData.status}
                onChange={(e) => setFormData({ ...formData, status: e.target.value })}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
              >
                <option value="Belum">Belum Terlaksana</option>
                <option value="Sedang">Sedang Berlangsung</option>
                <option value="Selesai">Telah Selesai</option>
              </select>
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">
              Uraian Kegiatan <span className="text-rose-500">*</span>
            </label>
            <input
              type="text"
              value={formData.kegiatan}
              onChange={(e) => setFormData({ ...formData, kegiatan: e.target.value })}
              placeholder="Contoh: Rapat Pembentukan Panitia dan Penyusunan SK"
              required
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Waktu Pelaksanaan <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={formData.waktu_pelaksanaan}
                onChange={(e) => setFormData({ ...formData, waktu_pelaksanaan: e.target.value })}
                placeholder="10 - 15 Mei 2025"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Penanggung Jawab <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={formData.penanggung_jawab}
                onChange={(e) => setFormData({ ...formData, penanggung_jawab: e.target.value })}
                placeholder="Ketua Panitia / Seksi Naskah"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Estimasi Anggaran (Rp)</label>
              <input
                type="number"
                value={formData.anggaran}
                onChange={(e) => setFormData({ ...formData, anggaran: parseFloat(e.target.value) || 0 })}
                min="0"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Urutan (Angka)</label>
              <input
                type="number"
                value={formData.urutan}
                onChange={(e) => setFormData({ ...formData, urutan: parseInt(e.target.value) || 1 })}
                min="1"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Catatan</label>
            <textarea
              rows={2}
              value={formData.keterangan}
              onChange={(e) => setFormData({ ...formData, keterangan: e.target.value })}
              placeholder="Catatan hasil pelaksanaan..."
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
              Simpan Program Kerja
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
