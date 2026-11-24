import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import { Calendar, Plus, Edit, Trash2, Clock, Users, ShieldCheck, Printer } from 'lucide-react';
import Modal from '../components/Modal';

export default function JadwalTulis() {
  const { showToast, setActiveTab } = useApp();
  const [list, setList] = useState([]);
  const [mapelList, setMapelList] = useState([]);
  const [ruangList, setRuangList] = useState([]);
  const [pengawasList, setPengawasList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);

  const [formData, setFormData] = useState({
    hari_tanggal: 'Senin, 02 Juni 2025',
    sesi: 1,
    jam_mulai: '07:30',
    jam_selesai: '09:00',
    mapel_id: '',
    kelas_tingkat: 'Semua Kelas',
    ruang_id: '',
    pengawas_1: '',
    pengawas_2: '',
    keterangan: ''
  });

  const fetchData = async () => {
    try {
      const [jadwalRes, mapelRes, ruangRes, pengawasRes] = await Promise.all([
        api.getJadwalTulis(),
        api.getMapel('tulis'),
        api.getRuangan(),
        api.getPengawas()
      ]);
      if (jadwalRes.success) setList(jadwalRes.data);
      if (mapelRes.success) setMapelList(mapelRes.data);
      if (ruangRes.success) setRuangList(ruangRes.data);
      if (pengawasRes.success) setPengawasList(pengawasRes.data);
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
      hari_tanggal: 'Senin, 02 Juni 2025',
      sesi: 1,
      jam_mulai: '07:30',
      jam_selesai: '09:00',
      mapel_id: mapelList.length > 0 ? mapelList[0].id : '',
      kelas_tingkat: 'Semua Kelas',
      ruang_id: ruangList.length > 0 ? ruangList[0].id : '',
      pengawas_1: pengawasList.length > 0 ? pengawasList[0].nama : '',
      pengawas_2: pengawasList.length > 1 ? pengawasList[1].nama : '',
      keterangan: ''
    });
    setModalOpen(true);
  };

  const openEdit = (item) => {
    setEditingItem(item);
    setFormData({
      hari_tanggal: item.hari_tanggal,
      sesi: item.sesi || 1,
      jam_mulai: item.jam_mulai,
      jam_selesai: item.jam_selesai,
      mapel_id: item.mapel_id,
      kelas_tingkat: item.kelas_tingkat,
      ruang_id: item.ruang_id || '',
      pengawas_1: item.pengawas_1 || '',
      pengawas_2: item.pengawas_2 || '',
      keterangan: item.keterangan || ''
    });
    setModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      if (editingItem) {
        await api.updateJadwalTulis(editingItem.id, formData);
        showToast('Jadwal ujian tulis berhasil diperbarui!', 'success');
      } else {
        await api.createJadwalTulis(formData);
        showToast('Jadwal ujian tulis baru berhasil ditambahkan!', 'success');
      }
      setModalOpen(false);
      fetchData();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleDelete = async (id, nama) => {
    if (!window.confirm(`Yakin ingin menghapus jadwal mapel ${nama}?`)) return;
    try {
      await api.deleteJadwalTulis(id);
      showToast('Jadwal ujian tulis berhasil dihapus.', 'success');
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
            <Calendar className="w-6 h-6 text-blue-600" />
            <span>Jadwal Ujian Tulis</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
            Pengaturan sesi, tanggal pelaksanaan, waktu, mata pelajaran, ruang, dan penugasan pengawas
          </p>
        </div>
        <div className="flex items-center gap-2 self-start sm:self-auto">
          <button
            onClick={() => setActiveTab('cetak-jadwal')}
            className="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs flex items-center gap-1.5 transition-all"
          >
            <Printer className="w-4 h-4 text-slate-600" />
            <span>Format Cetak Mading</span>
          </button>
          <button
            onClick={openCreate}
            className="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 flex items-center gap-2 transition-all"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Jadwal Tulis</span>
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
                <th className="py-3.5 px-4 text-center">Sesi</th>
                <th className="py-3.5 px-4">Waktu (WIB)</th>
                <th className="py-3.5 px-4">Mata Pelajaran</th>
                <th className="py-3.5 px-4">Tingkat Kelas</th>
                <th className="py-3.5 px-4">Ruangan</th>
                <th className="py-3.5 px-4">Pengawas Ruang</th>
                <th className="py-3.5 px-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {list.map((item) => (
                <tr key={item.id} className="hover:bg-slate-50/60 transition-colors">
                  <td className="py-3 px-4 font-bold text-slate-900">{item.hari_tanggal}</td>
                  <td className="py-3 px-4 text-center">
                    <span className="bg-blue-50 text-blue-700 font-bold px-2 py-0.5 rounded-md border border-blue-100 text-xs">
                      Sesi {item.sesi}
                    </span>
                  </td>
                  <td className="py-3 px-4 font-mono font-semibold text-slate-700">
                    <span className="inline-flex items-center gap-1">
                      <Clock className="w-3.5 h-3.5 text-slate-400" />
                      {item.jam_mulai} - {item.jam_selesai}
                    </span>
                  </td>
                  <td className="py-3 px-4">
                    <span className="font-bold text-slate-800 block">{item.nama_mapel}</span>
                    {item.keterangan && (
                      <span className="text-[11px] text-slate-500">{item.keterangan}</span>
                    )}
                  </td>
                  <td className="py-3 px-4 font-semibold text-slate-700">{item.kelas_tingkat}</td>
                  <td className="py-3 px-4 text-slate-700">{item.nama_ruang || 'Semua Ruang'}</td>
                  <td className="py-3 px-4 text-xs text-slate-600">
                    {item.pengawas_1 && <div>1. {item.pengawas_1}</div>}
                    {item.pengawas_2 && <div>2. {item.pengawas_2}</div>}
                    {!item.pengawas_1 && !item.pengawas_2 && <span className="text-slate-400 italic">Belum ditentukan</span>}
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
                    Belum ada jadwal ujian tulis yang terdaftar.
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
        title={editingItem ? 'Edit Jadwal Ujian Tulis' : 'Tambah Jadwal Ujian Tulis'}
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
                placeholder="Senin, 02 Juni 2025"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
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
              <label className="block text-xs font-semibold text-slate-700 mb-1">Jam Mulai</label>
              <input
                type="text"
                value={formData.jam_mulai}
                onChange={(e) => setFormData({ ...formData, jam_mulai: e.target.value })}
                placeholder="07:30"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Jam Selesai</label>
              <input
                type="text"
                value={formData.jam_selesai}
                onChange={(e) => setFormData({ ...formData, jam_selesai: e.target.value })}
                placeholder="09:00"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Mata Pelajaran <span className="text-rose-500">*</span>
              </label>
              <select
                value={formData.mapel_id}
                onChange={(e) => setFormData({ ...formData, mapel_id: e.target.value })}
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white font-bold"
              >
                <option value="">-- Pilih Mata Pelajaran --</option>
                {mapelList.map(m => (
                  <option key={m.id} value={m.id}>{m.nama_mapel} ({m.kode_mapel})</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Tingkat Kelas Sasaran</label>
              <input
                type="text"
                value={formData.kelas_tingkat}
                onChange={(e) => setFormData({ ...formData, kelas_tingkat: e.target.value })}
                placeholder="Semua Kelas, X MIPA, dsb."
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Penempatan Ruangan</label>
            <select
              value={formData.ruang_id}
              onChange={(e) => setFormData({ ...formData, ruang_id: e.target.value })}
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
            >
              <option value="">Semua Ruangan Ujian (Serentak)</option>
              {ruangList.map(r => (
                <option key={r.id} value={r.id}>{r.nama_ruang} ({r.kode_ruang})</option>
              ))}
            </select>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Pengawas Ruang 1</label>
              <input
                type="text"
                value={formData.pengawas_1}
                onChange={(e) => setFormData({ ...formData, pengawas_1: e.target.value })}
                placeholder="Nama Pengawas 1"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Pengawas Ruang 2</label>
              <input
                type="text"
                value={formData.pengawas_2}
                onChange={(e) => setFormData({ ...formData, pengawas_2: e.target.value })}
                placeholder="Nama Pengawas 2"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Catatan Ujian</label>
            <input
              type="text"
              value={formData.keterangan}
              onChange={(e) => setFormData({ ...formData, keterangan: e.target.value })}
              placeholder="Contoh: Dilarang membawa kalkulator / Membawa LJK 2B"
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
              Simpan Jadwal Tulis
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
