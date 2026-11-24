const API_BASE = '/api';

export async function apiRequest(endpoint, options = {}) {
  const token = localStorage.getItem('ruang_ujian_token');
  const headers = {
    ...options.headers,
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  // If not FormData, set JSON content type
  if (!(options.body instanceof FormData) && !headers['Content-Type']) {
    headers['Content-Type'] = 'application/json';
  }

  try {
    const res = await fetch(`${API_BASE}${endpoint}`, {
      ...options,
      headers
    });

    if (res.status === 401) {
      localStorage.removeItem('ruang_ujian_token');
      localStorage.removeItem('ruang_ujian_user');
      window.dispatchEvent(new Event('auth-expired'));
      throw new Error('Sesi telah berakhir. Silakan login kembali.');
    }

    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.message || 'Terjadi kesalahan pada server.');
    }

    return data;
  } catch (error) {
    console.error(`API Error [${endpoint}]:`, error);
    throw error;
  }
}

export const api = {
  // Auth
  login: (credentials) => apiRequest('/auth/login', { method: 'POST', body: JSON.stringify(credentials) }),
  getMe: () => apiRequest('/auth/me'),
  changePassword: (data) => apiRequest('/auth/change-password', { method: 'POST', body: JSON.stringify(data) }),

  // Identitas Sekolah
  getSekolah: () => apiRequest('/sekolah'),
  updateSekolah: (data) => apiRequest('/sekolah', { method: 'PUT', body: JSON.stringify(data) }),
  uploadLogo: (formData) => apiRequest('/sekolah/upload-logo', { method: 'POST', body: formData }),

  // Ujian
  getUjianList: () => apiRequest('/ujian'),
  getActiveUjian: () => apiRequest('/ujian/active'),
  createUjian: (data) => apiRequest('/ujian', { method: 'POST', body: JSON.stringify(data) }),
  updateUjian: (id, data) => apiRequest(`/ujian/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteUjian: (id) => apiRequest(`/ujian/${id}`, { method: 'DELETE' }),
  activateUjian: (id) => apiRequest(`/ujian/${id}/activate`, { method: 'POST' }),

  // Master Kelas
  getKelas: () => apiRequest('/master/kelas'),
  createKelas: (data) => apiRequest('/master/kelas', { method: 'POST', body: JSON.stringify(data) }),
  updateKelas: (id, data) => apiRequest(`/master/kelas/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteKelas: (id) => apiRequest(`/master/kelas/${id}`, { method: 'DELETE' }),

  // Master Mapel
  getMapel: (jenis) => apiRequest(`/master/mapel${jenis ? `?jenis=${jenis}` : ''}`),
  createMapel: (data) => apiRequest('/master/mapel', { method: 'POST', body: JSON.stringify(data) }),
  updateMapel: (id, data) => apiRequest(`/master/mapel/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteMapel: (id) => apiRequest(`/master/mapel/${id}`, { method: 'DELETE' }),

  // Master Ruangan
  getRuangan: () => apiRequest('/master/ruangan'),
  createRuangan: (data) => apiRequest('/master/ruangan', { method: 'POST', body: JSON.stringify(data) }),
  updateRuangan: (id, data) => apiRequest(`/master/ruangan/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteRuangan: (id) => apiRequest(`/master/ruangan/${id}`, { method: 'DELETE' }),
  autoAssignRuangan: (clearExisting) => apiRequest('/master/ruangan/auto-assign', { method: 'POST', body: JSON.stringify({ clearExisting }) }),

  // Master Peserta
  getPeserta: (params = {}) => {
    const qs = new URLSearchParams(params).toString();
    return apiRequest(`/master/peserta${qs ? `?${qs}` : ''}`);
  },
  createPeserta: (data) => apiRequest('/master/peserta', { method: 'POST', body: JSON.stringify(data) }),
  updatePeserta: (id, data) => apiRequest(`/master/peserta/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deletePeserta: (id) => apiRequest(`/master/peserta/${id}`, { method: 'DELETE' }),
  batchImportPeserta: (data) => apiRequest('/master/peserta/batch-import', { method: 'POST', body: JSON.stringify({ data }) }),

  // Master Panitia
  getPanitia: () => apiRequest('/master/panitia'),
  createPanitia: (data) => apiRequest('/master/panitia', { method: 'POST', body: JSON.stringify(data) }),
  updatePanitia: (id, data) => apiRequest(`/master/panitia/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deletePanitia: (id) => apiRequest(`/master/panitia/${id}`, { method: 'DELETE' }),

  // Master Pengawas
  getPengawas: () => apiRequest('/master/pengawas'),
  createPengawas: (data) => apiRequest('/master/pengawas', { method: 'POST', body: JSON.stringify(data) }),
  updatePengawas: (id, data) => apiRequest(`/master/pengawas/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deletePengawas: (id) => apiRequest(`/master/pengawas/${id}`, { method: 'DELETE' }),

  // Master Penguji Praktik
  getPenguji: () => apiRequest('/master/penguji'),
  createPenguji: (data) => apiRequest('/master/penguji', { method: 'POST', body: JSON.stringify(data) }),
  updatePenguji: (id, data) => apiRequest(`/master/penguji/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deletePenguji: (id) => apiRequest(`/master/penguji/${id}`, { method: 'DELETE' }),

  // Persiapan - Program Kerja
  getProker: () => apiRequest('/persiapan/proker'),
  createProker: (data) => apiRequest('/persiapan/proker', { method: 'POST', body: JSON.stringify(data) }),
  updateProker: (id, data) => apiRequest(`/persiapan/proker/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteProker: (id) => apiRequest(`/persiapan/proker/${id}`, { method: 'DELETE' }),

  // Persiapan - Jadwal Tulis
  getJadwalTulis: () => apiRequest('/persiapan/jadwal-tulis'),
  createJadwalTulis: (data) => apiRequest('/persiapan/jadwal-tulis', { method: 'POST', body: JSON.stringify(data) }),
  updateJadwalTulis: (id, data) => apiRequest(`/persiapan/jadwal-tulis/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteJadwalTulis: (id) => apiRequest(`/persiapan/jadwal-tulis/${id}`, { method: 'DELETE' }),

  // Persiapan - Jadwal Praktik
  getJadwalPraktik: () => apiRequest('/persiapan/jadwal-praktik'),
  createJadwalPraktik: (data) => apiRequest('/persiapan/jadwal-praktik', { method: 'POST', body: JSON.stringify(data) }),
  updateJadwalPraktik: (id, data) => apiRequest(`/persiapan/jadwal-praktik/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteJadwalPraktik: (id) => apiRequest(`/persiapan/jadwal-praktik/${id}`, { method: 'DELETE' }),

  // Persiapan - Persuratan
  getSuratList: () => apiRequest('/persiapan/surat'),
  getSuratDetail: (id) => apiRequest(`/persiapan/surat/${id}`),
  createSurat: (data) => apiRequest('/persiapan/surat', { method: 'POST', body: JSON.stringify(data) }),
  updateSurat: (id, data) => apiRequest(`/persiapan/surat/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteSurat: (id) => apiRequest(`/persiapan/surat/${id}`, { method: 'DELETE' }),

  // Persiapan - Tata Tertib
  getTatibList: () => apiRequest('/persiapan/tatib'),
  getTatibCategory: (kategori) => apiRequest(`/persiapan/tatib/${kategori}`),
  updateTatib: (kategori, data) => apiRequest(`/persiapan/tatib/${kategori}`, { method: 'PUT', body: JSON.stringify(data) }),

  // Cetak Endpoints
  getCetakKartuPeserta: (params = {}) => {
    const qs = new URLSearchParams(params).toString();
    return apiRequest(`/cetak/kartu-peserta${qs ? `?${qs}` : ''}`);
  },
  getCetakKartuPanitia: () => apiRequest('/cetak/kartu-panitia'),
  getCetakJadwal: () => apiRequest('/cetak/jadwal'),
  getCetakNomorRuang: () => apiRequest('/cetak/nomor-ruang'),
  getCetakDaftarPeserta: (params = {}) => {
    const qs = new URLSearchParams(params).toString();
    return apiRequest(`/cetak/daftar-peserta${qs ? `?${qs}` : ''}`);
  },
  getCetakDenahDuduk: (ruangId) => apiRequest(`/cetak/denah-duduk${ruangId ? `?ruang_id=${ruangId}` : ''}`),
  getCetakDaftarHadirPanitia: () => apiRequest('/cetak/daftar-hadir/panitia'),
  getCetakDaftarHadirPeserta: (params = {}) => {
    const qs = new URLSearchParams(params).toString();
    return apiRequest(`/cetak/daftar-hadir/peserta${qs ? `?${qs}` : ''}`);
  },
  getCetakDaftarHadirPengawas: () => apiRequest('/cetak/daftar-hadir/pengawas'),
  getCetakDaftarHadirPenguji: () => apiRequest('/cetak/daftar-hadir/penguji'),
  getBeritaAcara: () => apiRequest('/cetak/berita-acara'),
  createBeritaAcara: (data) => apiRequest('/cetak/berita-acara', { method: 'POST', body: JSON.stringify(data) }),
  updateBeritaAcara: (id, data) => apiRequest(`/cetak/berita-acara/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
  deleteBeritaAcara: (id) => apiRequest(`/cetak/berita-acara/${id}`, { method: 'DELETE' }),
  getLPJ: () => apiRequest('/cetak/lpj'),
  updateLPJ: (data) => apiRequest('/cetak/lpj', { method: 'PUT', body: JSON.stringify(data) }),

  // Sistem
  getSistemInfo: () => apiRequest('/sistem/info'),
  restoreJson: (data) => apiRequest('/sistem/restore/json', { method: 'POST', body: JSON.stringify({ data }) }),
  restoreDb: (formData) => apiRequest('/sistem/restore/db', { method: 'POST', body: formData }),
  resetDemoData: () => apiRequest('/sistem/reset', { method: 'POST' }),

  // Dashboard Stats
  getDashboardStats: () => apiRequest('/stats/dashboard'),
};
