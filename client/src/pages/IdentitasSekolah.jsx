import React, { useState, useEffect } from 'react';
import { useApp } from '../context/AppContext';
import { api } from '../utils/api';
import { Building2, Save, Upload, CheckCircle2, Eye, Image } from 'lucide-react';
import KopSurat from '../components/KopSurat';

export default function IdentitasSekolah() {
  const { sekolah, setSekolah, refreshGlobalData, showToast } = useApp();
  const [formData, setFormData] = useState({
    nama_sekolah: '',
    npsn: '',
    nss: '',
    jenjang: 'SMA',
    status_sekolah: 'Negeri',
    alamat: '',
    desa_kelurahan: '',
    kecamatan: '',
    kabupaten_kota: '',
    provinsi: '',
    kode_pos: '',
    telepon: '',
    email: '',
    website: '',
    kepala_sekolah: '',
    nip_kepala_sekolah: '',
    tahun_ajaran: '2024/2025',
    semester: 'Genap',
    titimangsa_kota: 'Bandung',
    kop_baris1: 'PEMERINTAH DAERAH PROVINSI JAWA BARAT',
    kop_baris2: 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
    kop_baris3: 'CABANG DINAS PENDIDIKAN WILAYAH VII',
    logo_url: ''
  });
  const [loading, setLoading] = useState(false);
  const [uploadingLogo, setUploadingLogo] = useState(false);

  useEffect(() => {
    if (sekolah) {
      setFormData(prev => ({ ...prev, ...sekolah }));
    }
  }, [sekolah]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      const res = await api.updateSekolah(formData);
      if (res.success) {
        showToast('Identitas sekolah berhasil diperbarui!', 'success');
        await refreshGlobalData();
      }
    } catch (err) {
      showToast(err.message || 'Gagal menyimpan identitas sekolah.', 'error');
    } finally {
      setLoading(false);
    }
  };

  const handleLogoUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const data = new FormData();
    data.append('logo', file);

    setUploadingLogo(true);
    try {
      const res = await api.uploadLogo(data);
      if (res.success) {
        showToast('Logo sekolah berhasil diunggah!', 'success');
        setFormData(prev => ({ ...prev, logo_url: res.logo_url }));
        await refreshGlobalData();
      }
    } catch (err) {
      showToast(err.message || 'Gagal mengunggah logo.', 'error');
    } finally {
      setUploadingLogo(false);
    }
  };

  return (
    <div className="space-y-6 animate-fade-in">
      {/* Header Page */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <Building2 className="w-6 h-6 text-blue-600" />
            <span>Identitas Sekolah</span>
          </h1>
          <p className="text-xs sm:text-sm text-slate-500 mt-0.5">
            Kelola profil resmi sekolah, kop surat kedinasan, logo, dan penandatangan dokumen ujian
          </p>
        </div>
      </div>

      {/* Live Preview Kop Surat Card */}
      <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div className="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
          <h2 className="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
            <Eye className="w-4 h-4 text-blue-600" />
            <span>Pratinjau Langsung Kop Surat Dokumen Resmi</span>
          </h2>
          <span className="text-[11px] text-slate-500">Tampilan ini otomatis digunakan pada seluruh dokumen cetak</span>
        </div>
        <div className="p-4 bg-slate-50/50 rounded-2xl border border-slate-100">
          <KopSurat customSekolah={formData} />
        </div>
      </div>

      {/* Form Identitas */}
      <form onSubmit={handleSubmit} className="space-y-6">
        {/* Kop Surat Header Settings */}
        <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
          <h2 className="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">
            1. Susunan Teks Kop Surat Resmi
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Kop Baris 1 (Pemerintah/Yayasan)
              </label>
              <input
                type="text"
                name="kop_baris1"
                value={formData.kop_baris1 || ''}
                onChange={handleChange}
                placeholder="Contoh: PEMERINTAH DAERAH PROVINSI JAWA BARAT"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Kop Baris 2 (Dinas Pendidikan)
              </label>
              <input
                type="text"
                name="kop_baris2"
                value={formData.kop_baris2 || ''}
                onChange={handleChange}
                placeholder="Contoh: DINAS PENDIDIKAN DAN KEBUDAYAAN"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Kop Baris 3 (Cabang Dinas / UPTD)
              </label>
              <input
                type="text"
                name="kop_baris3"
                value={formData.kop_baris3 || ''}
                onChange={handleChange}
                placeholder="Contoh: CABANG DINAS PENDIDIKAN WILAYAH VII"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>
        </div>

        {/* Data Profil Sekolah */}
        <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
          <div className="flex items-center justify-between border-b border-slate-100 pb-2">
            <h2 className="text-sm font-bold text-slate-900">
              2. Data Pokok Satuan Pendidikan
            </h2>
            {/* Logo Upload button */}
            <label className="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-semibold transition-colors border border-blue-200">
              <Upload className="w-3.5 h-3.5" />
              <span>{uploadingLogo ? 'Mengunggah...' : 'Upload Logo Baru'}</span>
              <input
                type="file"
                accept="image/*"
                onChange={handleLogoUpload}
                className="hidden"
                disabled={uploadingLogo}
              />
            </label>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="sm:col-span-2">
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Nama Sekolah <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                name="nama_sekolah"
                value={formData.nama_sekolah || ''}
                onChange={handleChange}
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">NPSN</label>
              <input
                type="text"
                name="npsn"
                value={formData.npsn || ''}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">NSS</label>
              <input
                type="text"
                name="nss"
                value={formData.nss || ''}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Jenjang</label>
              <select
                name="jenjang"
                value={formData.jenjang || 'SMA'}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
              >
                <option value="SD">SD / MI</option>
                <option value="SMP">SMP / MTs</option>
                <option value="SMA">SMA / MA</option>
                <option value="SMK">SMK / MAK</option>
              </select>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Status Sekolah</label>
              <select
                name="status_sekolah"
                value={formData.status_sekolah || 'Negeri'}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
              >
                <option value="Negeri">Negeri</option>
                <option value="Swasta">Swasta</option>
              </select>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Tahun Ajaran Aktif</label>
              <input
                type="text"
                name="tahun_ajaran"
                value={formData.tahun_ajaran || ''}
                onChange={handleChange}
                placeholder="2024/2025"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Semester Aktif</label>
              <select
                name="semester"
                value={formData.semester || 'Genap'}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 bg-white"
              >
                <option value="Ganjil">Ganjil</option>
                <option value="Genap">Genap</option>
              </select>
            </div>
          </div>

          {/* Alamat Lengkap */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
            <div className="sm:col-span-2 lg:col-span-3">
              <label className="block text-xs font-semibold text-slate-700 mb-1">Alamat Jalan</label>
              <input
                type="text"
                name="alamat"
                value={formData.alamat || ''}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Desa / Kelurahan</label>
              <input
                type="text"
                name="desa_kelurahan"
                value={formData.desa_kelurahan || ''}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Kecamatan</label>
              <input
                type="text"
                name="kecamatan"
                value={formData.kecamatan || ''}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Kabupaten / Kota</label>
              <input
                type="text"
                name="kabupaten_kota"
                value={formData.kabupaten_kota || ''}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Provinsi</label>
              <input
                type="text"
                name="provinsi"
                value={formData.provinsi || ''}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Kode Pos</label>
              <input
                type="text"
                name="kode_pos"
                value={formData.kode_pos || ''}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Telepon / Fax</label>
              <input
                type="text"
                name="telepon"
                value={formData.telepon || ''}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Email Sekolah</label>
              <input
                type="email"
                name="email"
                value={formData.email || ''}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">Website Resmi</label>
              <input
                type="text"
                name="website"
                value={formData.website || ''}
                onChange={handleChange}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>
        </div>

        {/* Kepala Sekolah & Pengesahan */}
        <div className="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
          <h2 className="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">
            3. Pejabat Pengesahan &amp; Titimangsa Surat
          </h2>
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Nama Kepala Sekolah (Lengkap dengan Gelar)
              </label>
              <input
                type="text"
                name="kepala_sekolah"
                value={formData.kepala_sekolah || ''}
                onChange={handleChange}
                placeholder="Dr. H. Ahmad Fauzi, M.Pd."
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                NIP Kepala Sekolah
              </label>
              <input
                type="text"
                name="nip_kepala_sekolah"
                value={formData.nip_kepala_sekolah || ''}
                onChange={handleChange}
                placeholder="19740512 199903 1 002"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 font-mono"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Titimangsa Kota Surat
              </label>
              <input
                type="text"
                name="titimangsa_kota"
                value={formData.titimangsa_kota || ''}
                onChange={handleChange}
                placeholder="Bandung"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
              />
            </div>
          </div>
        </div>

        {/* Submit Button */}
        <div className="flex justify-end">
          <button
            type="submit"
            disabled={loading}
            className="px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-lg shadow-blue-600/30 flex items-center gap-2 transition-all hover:scale-[1.01] active:scale-95 disabled:opacity-70"
          >
            <Save className="w-4 h-4" />
            <span>{loading ? 'Menyimpan Perubahan...' : 'Simpan Identitas Sekolah'}</span>
          </button>
        </div>
      </form>
    </div>
  );
}
