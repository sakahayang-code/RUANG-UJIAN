import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import { LayoutGrid, Plus, Edit, Trash2, Users, Wand2, Eye, Sparkles } from 'lucide-react';
import Modal from '../components/Modal';

export default function AturRuangan() {
  const { showToast, setActiveTab } = useApp();
  const [list, setList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);

  // Desk visualizer state
  const [viewingRoom, setViewingRoom] = useState(null);
  const [roomGridData, setRoomGridData] = useState(null);
  const [loadingGrid, setLoadingGrid] = useState(false);
  const [autoAssigning, setAutoAssigning] = useState(false);

  const [formData, setFormData] = useState({
    kode_ruang: '',
    nama_ruang: '',
    kapasitas: 20,
    jumlah_baris: 5,
    jumlah_kolom: 4,
    lokasi: ''
  });

  const fetchList = async () => {
    try {
      const res = await api.getRuangan();
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
    const nextNum = list.length + 1;
    setFormData({
      kode_ruang: `R.${String(nextNum).padStart(2, '0')}`,
      nama_ruang: `Ruang Ujian ${String(nextNum).padStart(2, '0')}`,
      kapasitas: 20,
      jumlah_baris: 5,
      jumlah_kolom: 4,
      lokasi: 'Gedung Utama Lantai 1'
    });
    setModalOpen(true);
  };

  const openEdit = (item) => {
    setEditingItem(item);
    setFormData({
      kode_ruang: item.kode_ruang,
      nama_ruang: item.nama_ruang,
      kapasitas: item.kapasitas || 20,
      jumlah_baris: item.jumlah_baris || 5,
      jumlah_kolom: item.jumlah_kolom || 4,
      lokasi: item.lokasi || ''
    });
    setModalOpen(true);
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      if (editingItem) {
        await api.updateRuangan(editingItem.id, formData);
        showToast('Data ruangan berhasil diperbarui!', 'success');
      } else {
        await api.createRuangan(formData);
        showToast('Ruangan baru berhasil ditambahkan!', 'success');
      }
      setModalOpen(false);
      fetchList();
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleDelete = async (id, nama) => {
    if (!window.confirm(`Yakin ingin menghapus ruangan ${nama}? Peserta di ruangan ini akan dikosongkan penempatannya.`)) return;
    try {
      await api.deleteRuangan(id);
      showToast('Ruangan berhasil dihapus.', 'success');
      fetchList();
      if (viewingRoom?.id === id) setViewingRoom(null);
    } catch (err) {
      showToast(err.message, 'error');
    }
  };

  const handleAutoAssign = async (clearExisting = false) => {
    setAutoAssigning(true);
    try {
      const res = await api.autoAssignRuangan(clearExisting);
      if (res.success) {
        showToast(res.message, 'success');
        await fetchList();
        if (viewingRoom) openVisualizer(viewingRoom);
      }
    } catch (err) {
      showToast(err.message, 'error');
    } finally {
      setAutoAssigning(false);
    }
  };

  const openVisualizer = async (room) => {
    setViewingRoom(room);
    setLoadingGrid(true);
    try {
      const res = await api.getCetakDenahDuduk(room.id);
      if (res.success && res.data.length > 0) {
        setRoomGridData(res.data[0]);
      }
    } catch (err) {
      showToast('Gagal memuat denah kursi.', 'error');
    } finally {
      setLoadingGrid(false);
    }
  };

  return (
    <div className="space-y-6 animate-fade-in">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <LayoutGrid className="w-6 h-6 text-blue-600" />
            <span>Master Atur Ruangan &amp; Kursi</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
            Konfigurasi ruangan ujian, tata letak baris x kolom meja, dan penempatan otomatis peserta ujian
          </p>
        </div>
        <div className="flex flex-wrap items-center gap-2 self-start sm:self-auto">
          <button
            onClick={() => handleAutoAssign(false)}
            disabled={autoAssigning}
            className="px-3.5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold text-xs shadow-md shadow-blue-500/20 flex items-center gap-2 transition-all"
            title="Tempatkan peserta yang belum punya ruangan secara otomatis"
          >
            <Wand2 className="w-4 h-4" />
            <span>{autoAssigning ? 'Memproses...' : 'Bagi Peserta Otomatis'}</span>
          </button>
          <button
            onClick={openCreate}
            className="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs shadow-md shadow-slate-900/10 flex items-center gap-2 transition-all"
          >
            <Plus className="w-4 h-4" />
            <span>Tambah Ruangan</span>
          </button>
        </div>
      </div>

      {/* Ruangan List Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {list.map((room) => {
          const isSelected = viewingRoom?.id === room.id;
          const percentage = room.kapasitas > 0 ? Math.round((room.terisi_peserta / room.kapasitas) * 100) : 0;
          return (
            <div
              key={room.id}
              className={`bg-white rounded-3xl p-5 border transition-all ${
                isSelected
                  ? 'border-blue-600 ring-2 ring-blue-600/10 shadow-md'
                  : 'border-slate-200/80 shadow-xs hover:border-slate-300'
              }`}
            >
              <div className="flex items-start justify-between">
                <div>
                  <span className="px-2.5 py-1 rounded-xl bg-blue-50 text-blue-700 font-bold text-xs font-mono border border-blue-100">
                    {room.kode_ruang}
                  </span>
                  <h3 className="font-bold text-slate-900 text-base mt-2">{room.nama_ruang}</h3>
                  <p className="text-xs text-slate-500 mt-0.5">{room.lokasi || 'Lokasi belum diatur'}</p>
                </div>
              </div>

              {/* Progress keterisian */}
              <div className="mt-4 pt-3 border-t border-slate-100">
                <div className="flex items-center justify-between text-xs mb-1.5">
                  <span className="text-slate-500 font-medium">Keterisian Kursi:</span>
                  <span className="font-bold text-slate-800">
                    <strong className="text-blue-600">{room.terisi_peserta}</strong> / {room.kapasitas} Kursi ({percentage}%)
                  </span>
                </div>
                <div className="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                  <div
                    className={`h-full rounded-full transition-all ${
                      room.terisi_peserta >= room.kapasitas ? 'bg-emerald-500' : 'bg-blue-600'
                    }`}
                    style={{ width: `${Math.min(percentage, 100)}%` }}
                  ></div>
                </div>
                <div className="flex items-center justify-between text-[11px] text-slate-400 mt-2 font-mono">
                  <span>Layout Meja:</span>
                  <span>{room.jumlah_baris} Baris &times; {room.jumlah_kolom} Kolom</span>
                </div>
              </div>

              {/* Action buttons */}
              <div className="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                <button
                  onClick={() => openVisualizer(room)}
                  className="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-xs transition-colors flex items-center gap-1.5"
                >
                  <Eye className="w-3.5 h-3.5" />
                  <span>Lihat Denah Kursi</span>
                </button>
                <div className="flex items-center gap-1">
                  <button
                    onClick={() => openEdit(room)}
                    className="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors"
                    title="Edit"
                  >
                    <Edit className="w-4 h-4" />
                  </button>
                  <button
                    onClick={() => handleDelete(room.id, room.nama_ruang)}
                    className="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors"
                    title="Hapus"
                  >
                    <Trash2 className="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {/* Visual Desk Grid Viewer */}
      {viewingRoom && (
        <div className="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4 animate-fade-in">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div>
              <h2 className="text-base font-bold text-slate-900 flex items-center gap-2">
                <LayoutGrid className="w-5 h-5 text-blue-600" />
                <span>Denah Fisik Meja Ujian: {viewingRoom.nama_ruang} ({viewingRoom.kode_ruang})</span>
              </h2>
              <p className="text-xs text-slate-500 mt-0.5">
                Kapasitas: {viewingRoom.kapasitas} Kursi ({viewingRoom.jumlah_baris} Baris &times; {viewingRoom.jumlah_kolom} Kolom) &bull; Terisi: {roomGridData?.total_peserta || 0} Siswa
              </p>
            </div>
            <div className="flex items-center gap-2">
              <button
                onClick={() => setActiveTab('cetak-denah-duduk')}
                className="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs flex items-center gap-1.5 transition-all shadow-xs"
              >
                <span>Cetak Denah Resmi &rarr;</span>
              </button>
              <button
                onClick={() => setViewingRoom(null)}
                className="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors"
              >
                Tutup
              </button>
            </div>
          </div>

          {/* Teacher / Invigilator Desk Representation */}
          <div className="max-w-md mx-auto my-3 text-center">
            <div className="p-2.5 rounded-xl bg-slate-800 text-white text-xs font-bold shadow-md uppercase tracking-wider flex items-center justify-center gap-2">
              <span>MEJA PENGAWAS RUANG (DEPAN KELAS / PAPAN TULIS)</span>
            </div>
            <div className="text-[10px] text-slate-400 font-semibold mt-1">Pintu Masuk Ruangan &darr;</div>
          </div>

          {/* The 2D Grid Layout */}
          {loadingGrid ? (
            <div className="p-12 text-center text-xs text-slate-400">Memuat denah kursi...</div>
          ) : roomGridData ? (
            <div className="overflow-x-auto p-4 bg-slate-50/60 rounded-2xl border border-slate-100">
              <div className="flex flex-col gap-3 min-w-[500px]">
                {roomGridData.grid.map((row, rIdx) => (
                  <div key={rIdx} className="grid gap-3" style={{ gridTemplateColumns: `repeat(${viewingRoom.jumlah_kolom}, minmax(0, 1fr))` }}>
                    {row.map((slot) => {
                      const isOccupied = slot.peserta !== null;
                      return (
                        <div
                          key={slot.seatNo}
                          className={`p-3 rounded-2xl border text-center transition-all ${
                            isOccupied
                              ? 'bg-white border-blue-200 shadow-xs ring-1 ring-blue-500/10'
                              : 'bg-slate-100/70 border-dashed border-slate-200 text-slate-400'
                          }`}
                        >
                          <div className="flex items-center justify-between text-[10px] font-mono font-bold mb-1">
                            <span className={`px-1.5 py-0.5 rounded-md ${isOccupied ? 'bg-blue-50 text-blue-700' : 'bg-slate-200 text-slate-500'}`}>
                              Meja #{slot.seatNo}
                            </span>
                            {isOccupied && (
                              <span className="text-slate-400 font-normal">{slot.peserta.nama_kelas}</span>
                            )}
                          </div>
                          {isOccupied ? (
                            <div>
                              <p className="font-bold text-slate-800 text-xs truncate" title={slot.peserta.nama}>
                                {slot.peserta.nama}
                              </p>
                              <p className="text-[10px] text-blue-600 font-mono font-semibold truncate mt-0.5">
                                {slot.peserta.no_peserta}
                              </p>
                            </div>
                          ) : (
                            <p className="text-[11px] text-slate-400 italic py-2">Kosong</p>
                          )}
                        </div>
                      );
                    })}
                  </div>
                ))}
              </div>
            </div>
          ) : null}
        </div>
      )}

      {/* Modal Add/Edit */}
      <Modal
        isOpen={modalOpen}
        onClose={() => setModalOpen(false)}
        title={editingItem ? 'Edit Data Ruangan' : 'Tambah Ruangan Baru'}
      >
        <form onSubmit={handleSave} className="space-y-4">
          <div className="grid grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Kode Ruang <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={formData.kode_ruang}
                onChange={(e) => setFormData({ ...formData, kode_ruang: e.target.value })}
                placeholder="R.01"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
            <div className="col-span-2">
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Nama Ruangan <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={formData.nama_ruang}
                onChange={(e) => setFormData({ ...formData, nama_ruang: e.target.value })}
                placeholder="Ruang Ujian 01 (Kelas X-1)"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>

          <div className="grid grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Kapasitas Kursi</label>
              <input
                type="number"
                value={formData.kapasitas}
                onChange={(e) => setFormData({ ...formData, kapasitas: parseInt(e.target.value) || 0 })}
                min="1"
                max="100"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono font-bold"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Jumlah Baris Meja</label>
              <input
                type="number"
                value={formData.jumlah_baris}
                onChange={(e) => setFormData({ ...formData, jumlah_baris: parseInt(e.target.value) || 1 })}
                min="1"
                max="20"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Jumlah Kolom Meja</label>
              <input
                type="number"
                value={formData.jumlah_kolom}
                onChange={(e) => setFormData({ ...formData, jumlah_kolom: parseInt(e.target.value) || 1 })}
                min="1"
                max="20"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">Lokasi / Gedung</label>
            <input
              type="text"
              value={formData.lokasi}
              onChange={(e) => setFormData({ ...formData, lokasi: e.target.value })}
              placeholder="Gedung A Lantai 1, Lab Multimedia, dsb."
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
              Simpan Ruangan
            </button>
          </div>
        </form>
      </Modal>
    </div>
  );
}
