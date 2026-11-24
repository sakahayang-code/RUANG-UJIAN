import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import { Clock, Plus, Edit, Trash2, Award, Building, Users, Printer } from 'lucide-react';
import Modal from '../components/Modal';

export default function JadwalPraktik() {
  const { showToast, setActiveTab } = useApp();
  const [list, setList] = useState([]);
  const [mapelList, setMapelList] = useState([]);
  const [pengujiList, setPengujiList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);

  const [formData, setFormData] = useState({
    hari_tanggal: 'Senin, 26 Mei 2025',
    waktu: '08:00 - 11:30',
    mapel_id: '',
    kelas_tingkat: 'Kelas X & XI',
    tempat: 'Laboratorium Komputer CBT',
    penguji_1: '',
    penguji_2: '',
    kelompok_peserta: 'Kelompok A & B (Sesi Pagi)'
  });

  const fetchData = async () => {
    try {
      const [jadwalRes, mapelRes, pengujiRes] = await Promise.all([
        api.getJadwalPraktik(),
        api.getMapel('praktik'),
        api.getPenguji()
      ]);
      if (jadwalRes.success) setList(jadwalRes.data);
      if (mapelRes.success) setMapelList(mapelRes.data);
      if (pengujiRes.success) setPengujiList(pengujiRes.data);
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const openCreate = () => {
    setEditingItem(null);
    setFormData({
      hari_tanggal: 'Senin, 26 Mei 2025',
      waktu: '08:00 - 11:30',
      mapel_id: mapelList.length > 0 ? mapelList[0].id : '',
      kelas_tingkat: 'Semua Kelas',
      tempat: 'Laboratorium Terpadu',
      penguji_1: pengujiList.length > 0 ? pengujiList[0].nama : '',
      penguji_2: pengujiList.length > 1 ? pengujiList[1].nama : '',
      kelompok_peserta: 'Seluruh Siswa Terdaftar'
    });
    setModalOpen(true);
  };

  const openEdit = (item) => {
    setEditingItem(item);
    setFormData({
      hari_tanggal: item.hari_tanggal,
      waktu: item.waktu,
      mapel_id: item.mapel_id,
      kelas_tingkat: item.kelas_tingkat,
      tempat: item.tempat || '',
      penguji_1: item.penguji_1 || '',
      penguji_2: item.penguji_2 || '',
      kelompok_peserta: item.kelompok_peserta || ''
    });
    setModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      if (editingItem) {
        await api.updateJadwalPraktik(editingItem.id, formData);
        showToast('Jadwal ujian praktik berhasil diperbarui!', 'success');
      } else {
        await api.createJadwalPraktik(formData);
        showToast('Jadwal ujian praktik baru berhasil ditambahkan!', 'success');
      }
      setModalOpen(false);
      fetchData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleDelete = async (id, nama) => {
    if (!window.confirm(`Yakin ingin menghapus jadwal praktik mapel ${nama}?`)) return;
    try {
      await api.deleteJadwalPraktik(id);
      showToast('Jadwal ujian praktik berhasil dihapus.', 'success');
      fetchData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  return (
    <div className="space-y-6 animate-fade-in">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <Clock className="w-6 h-6 text-blue-600" />
            <span>Jadwal Ujian Praktik</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
            Jadwal pengujian unjuk kerja, demonstrasi laboratorium, bengkel, dan penguji praktik
          </p>
        </div>
        <div className="flex items-center gap-2 self-start sm:self-auto">
          <button
            onClick={() => setActiveTab('cetak-jadwal')}
            className="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs flex items-center gap-1.5 transition-all"
          >
            <Printer className="w-4 h-4 text-slate-600" />
            <span>Format Cetak</span>
          </button>
          <button
            onClick={openCreate}
            className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 flex items-center gap-2 transition-all"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Jadwal Praktik</span>
          </button>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse text-xs sm:text-sm">
            <thead>
              <tr className="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                <th className="py-3.5 px-4">Hari &amp; Tanggal</th>
                <th className="py-3.5 px-4">Waktu</th>
                <th className="py-3.5 px-4">Mata Pelajaran Praktik</th>
                <th className="py-3.5 px-4">Tingkat Kelas</th>
                <th className="py-3.5 px-4">Tempat / Laboratorium</th>
                <th className="py-3.5 px-4">Dewan Penguji Praktik</th>
                <th className="py-3.5 px-4">Kelompok Peserta</th>
                <th className="py-3.5 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {list.map((item) => (
                <tr key={item.id} className="hover:bg-slate-50/60 transition-colors">
                  <td className="py-3 px-4 font-bold text-slate-900">{item.hari_tanggal}</td>
                  <td className="py-3 px-4 font-mono font-semibold text-slate-700">{item.waktu}</td>
                  <td className="py-3 px-4">
                    <span className="font-bold text-blue-700 block">{item.nama_mapel}</span>
                  </td>
                  <td className="py-3 px-4 font-semibold text-slate-700">{item.kelas_tingkat}</td>
                  <td className="py-3 px-4 text-slate-800 font-medium">{item.tempat || '-'}</td>
                  <td className="py-3 px-4 text-xs text-slate-700">
                    {item.penguji_1 && <div>1. {item.penguji_1}</div>}
                    {item.penguji_2 && <div>2. {item.penguji_2}</div>}
                    {!item.penguji_1 && !item.penguji_2 && <span className="text-slate-400 italic">Belum ditentukan</span>}
                  </td>
                  <td className="py-3 px-4 text-xs text-slate-600">{item.kelompok_peserta || '-'}</td>
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
              {list.length === 0 && (
                <tr>
                  <td colSpan={8} className="py-8 text-center text-slate-400 text-xs">
                    Belum ada jadwal ujian praktik yang terdaftar.
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
        title={editingItem ? 'Edit Jadwal Ujian Praktik' : 'Tambah Jadwal Ujian Praktik'}
      >
        <form onSubmit={handleSave} className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Hari &amp; Tanggal <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={formData.hari_tanggal}
                onChange={(e) => setFormData({ ...formData, hari_tanggal: e.target.value })}
                placeholder="Senin, 26 Mei 2025"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Waktu Pelaksanaan <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={formData.waktu}
                onChange={(e) => setFormData({ ...formData, waktu: e.target.value })}
                placeholder="08:00 - 11:30 WIB"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Mata Pelajaran Praktik <span className="text-rose-500">*</span>
              </label>
              <select
                value={formData.mapel_id}
                onChange={(e) => setFormData({ ...formData, mapel_id: e.target.value })}
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white font-bold"
              >
                <option value="">-- Pilih Mapel Praktik --</option>
                {mapelList.map(m => (
                  <option key={m.id} value={m.id}>{m.nama_mapel} ({m.kode_mapel})</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Sasaran Kelas</label>
              <input
                type="text"
                value={formData.kelas_tingkat}
                onChange={(e) => setFormData({ ...formData, kelas_tingkat: e.target.value })}
                placeholder="Kelas X MIPA 1 & 2..."
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">
              Tempat / Laboratorium / Bengkel
            </label>
            <input
              type="text"
              value={formData.tempat}
              onChange={(e) => setFormData({ ...formData, tempat: e.target.value })}
              placeholder="Laboratorium Komputer CBT, Lab IPA, Lapangan Olahraga..."
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Penguji Praktik 1</label>
              <input
                type="text"
                value={formData.penguji_1}
                onChange={(e) => setFormData({ ...formData, penguji_1: e.target.value })}
                placeholder="Nama Penguji 1"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Penguji Praktik 2</label>
              <input
                type="text"
                value={formData.penguji_2}
                onChange={(e) => setFormData({ ...formData, penguji_2: e.target.value })}
                placeholder="Nama Penguji 2 (atau Penguji DU/DI)"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Pembagian Kelompok / Sesi</label>
            <input
              type="text"
              value={formData.kelompok_peserta}
              onChange={(e) => setFormData({ ...formData, kelompok_peserta: e.target.value })}
              placeholder="Kelompok A (Absen 01-18), Kelompok B (Absen 19-36)..."
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
              Simpan Jadwal Praktik
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
