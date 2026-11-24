# Ruang Ujian 🎓
> **Sistem Manajemen Administrasi & Pelaksanaan Ujian Sekolah Modern**
> Mendukung Mode Tampilan Desktop & PWA Native Android &bull; Berbasis SQLite &bull; Siap Cetak Format Resmi A4/F4

![Ruang Ujian Banner](client/public/icon.svg)

---

## 📌 Ringkasan Aplikasi

**Ruang Ujian** adalah aplikasi terpadu untuk mengelola seluruh siklus administrasi, logistik, pengawasan, dan pelaporan kegiatan ujian sekolah (seperti PAT, PAS, STS, ASAJ, dan Ujian Praktik Kejuruan/Sains). Dirancang dengan standar kedinasan pendidikan Indonesia, dilengkapi kop surat dinamis, tanda tangan kepala sekolah, nomor surat otomatis, QR code verifikasi peserta, visual denah kursi 2D, serta ekspor & impor data.

- **Nama Aplikasi**: Ruang Ujian
- **Database**: SQLite (`ruang_ujian.db` via `better-sqlite3`)
- **UI/UX**: Modern Clean Aesthetic (Cobalt Blue `#1E60FF`, Deep Navy `#0A2540`, Slate-50)
- **Mode Tampilan**: Tampilan Desktop (Sidebar responsif) dan PWA Native Android (Bottom Navigation Bar, Installable Standalone, Offline Cache)
- **Akun Default**: `admin` / `admin123`

---

## 🚀 Fitur & Modul Aplikasi

### 1. Menu Utama
- **Dashboard**:
  - Bento KPI Metric Cards (Total Peserta L/P, Total Kelas, Mapel Tulis & Praktik, Ruangan & Kapasitas Kursi).
  - Status Progres Program Kerja Panitia & Total Estimasi Anggaran RAB.
  - Ringkasan Keterisian Ruangan Ujian (Progress Bar kapasitas).
  - Jadwal Ujian Tulis & Praktik mendatang.
  - Tombol aksi cepat: Cetak Kartu Peserta, Atur Ruangan, Buat Surat Tugas, Install PWA.
- **Identitas Sekolah**:
  - Profil Satuan Pendidikan (Nama Sekolah, NPSN, NSS, Jenjang SD/SMP/SMA/SMK, Status Negeri/Swasta, Alamat, Kontak, Website).
  - Editor 3-baris teks Kop Surat Kedinasan (Pemerintah Provinsi, Dinas Pendidikan, Cabang Dinas).
  - Upload & Pratinjau Logo Sekolah.
  - Data Kepala Sekolah, NIP, Tahun Ajaran, Semester, dan Titimangsa Kota Surat.
  - Pratinjau Langsung (*Live Preview*) Kop Surat resmi.
- **Manajemen Ujian**:
  - Manajemen multi-kegiatan ujian (PAT, PAS, STS, ASAJ).
  - Pengalihan kegiatan ujian aktif dalam 1 klik (*Active Switcher*).
  - Periode tanggal pelaksanaan dan arsip kegiatan lama.

---

### 2. Master Data
1. **Data Kelas**:
   - Tingkat (X, XI, XII atau 7, 8, 9 atau SD), Nama Rombel, Jurusan/Program, Wali Kelas, dan Counter Jumlah Siswa otomatis.
2. **Data Mata Pelajaran**:
   - Tab **Mata Pelajaran Ujian Tulis**: Kode mapel, nama, alokasi waktu menit, butir soal.
   - Tab **Mata Pelajaran Ujian Praktik**: Kode mapel, kriteria / rubrik penilaian asesmen unjuk kerja.
3. **Data Peserta Ujian**:
   - Nomor Peserta (format resmi), NIS, NISN, Nama Lengkap, Jenis Kelamin (L/P), Kelas, Ruang, Nomor Meja, Sesi, PIN/Password CBT.
   - Filter pencarian cepat berdasarkan Kelas, Ruangan, dan Nama/NISN.
   - **Impor Massal (Batch Import)** dari CSV / Tab Delimited.
4. **Data Panitia**:
   - Susunan kepanitiaan ujian: Penanggung Jawab, Ketua, Sekretaris, Bendahara, Seksi Naskah, Seksi Ruangan, Seksi Konsumsi.
   - Uraian tugas pokok dan nomor kontak WhatsApp.
5. **Data Pengawas Ruang**:
   - Kode pengawas (P01, P02, dst.), Nama Lengkap & Gelar, NIP, Asal Sekolah (pengawasan silang), Mapel ampu, dan kontak.
6. **Data Penguji Praktik**:
   - Nama Penguji, NIP, Bidang Mapel Praktik, Asal Instansi/DU-DI Mitra, dan Keterangan Peran Penguji.
7. **Atur Ruangan & Kursi**:
   - Kode ruang, Nama ruang, Kapasitas kursi, Dimensi baris x kolom meja, dan Lokasi gedung.
   - **Tombol Ajaib: 1-Click Pembagian Peserta Otomatis** ke seluruh ruangan dan nomor meja!
   - Visualisator Denah Meja 2D interaktif (Meja Pengawas di depan & susunan meja siswa).

---

### 3. Persiapan Kegiatan
1. **Program Kerja Panitia**:
   - Pengelompokan 3 Tahap: *Tahap Persiapan*, *Tahap Pelaksanaan*, dan *Tahap Evaluasi & Pelaporan*.
   - Uraian kegiatan, waktu pelaksanaan, penanggung jawab, alokasi anggaran (RAB), dan status (*Belum / Sedang / Selesai*).
2. **Jadwal Ujian Tulis**:
   - Hari & Tanggal, Sesi 1/2/3, Waktu Mulai-Selesai, Mapel, Tingkat Kelas, Ruang, dan Penugasan Pengawas 1 & 2.
3. **Jadwal Ujian Praktik**:
   - Hari & Tanggal, Waktu, Mapel Praktik, Tempat/Lab/Bengkel, Penguji 1 & 2, dan Kelompok Peserta.
4. **Persuratan (5 Format Surat Kedinasan Lengkap)**:
   - 1. Surat Undangan Rapat Panitia & Guru
   - 2. Surat Tugas Pembuat Soal & Kisi-Kisi Ujian Tulis
   - 3. Surat Tugas Pembuat Soal & Kisi-Kisi Ujian Praktik
   - 4. Surat Tugas Pengawas Ruang Ujian
   - 5. Surat Tugas Penguji Praktik
   - Dilengkapi konsideran dasar hukum, diktum penugasan, tabel lampiran nama guru bertugas, titimangsa, dan tanda tangan Kepala Sekolah.
5. **Tata Tertib**:
   - Tata Tertib Peserta Ujian (9 poin aturan resmi)
   - Tata Tertib Pengawas Ruang (9 poin SOP pengawasan)
   - Tata Tertib Penguji Praktik (8 poin standar asesmen unjuk kerja)
   - Setiap butir dapat diedit secara fleksibel dan siap cetak tempel pintu.

---

### 4. Pusat Cetak Dokumen Resmi (Siap Cetak Kertas A4/F4)
1. **Kartu Peserta**: Format 4 kartu per lembar A4, dilengkapi foto/avatar, QR Code verifikasi, dan PIN CBT.
2. **Kartu Panitia**: ID Card gantungan lanyard vertikal dengan logo sekolah, foto, barcode, dan jabatan kepanitiaan.
3. **Jadwal Ujian**: Jadwal Ujian Tulis & Praktik format resmi papan pengumuman/mading sekolah.
4. **Nomor Ruangan Ujian**: Plang pintu ukuran A4 berisi nama ruangan, nomor ruangan besar, dan rentang nomor peserta.
5. **Daftar Peserta (DNT)**: Daftar Nominasi Tetap resmi per ruang atau per kelas.
6. **Denah Tempat Duduk**: Layout grafis 2D letak meja peserta dan meja pengawas siap tempel di pintu kelas.
7. **Daftar Hadir (4 Jenis)**:
   - Daftar Hadir Peserta Ujian (dengan kolom paraf sesi 1, 2, dst.)
   - Daftar Hadir Panitia
   - Daftar Hadir Pengawas Ruang
   - Daftar Hadir Penguji Praktik
8. **Berita Acara Pelaksanaan Ujian**: Formulir berita acara resmi mencatat jumlah hadir, tidak hadir, catatan kejadian khusus, dan tanda tangan pengawas.
9. **Laporan Pertanggungjawaban (LPJ)**: Dokumen LPJ komprehensif (Cover resmi, Kata Pengantar, SK Panitia, Realisasi Jadwal, Rekapitulasi, Laporan Keuangan RAB Pemasukan & Pengeluaran, Lembar Pengesahan Ketua Panitia & Kepala Sekolah).

---

### 5. Sistem & Keamanan
- **Backup Database**: Unduh file asli SQLite (`.sqlite`) langsung dalam 1 klik.
- **Backup JSON**: Ekspor seluruh basis data ke format JSON portabel.
- **Restore Database**: Unggah file `.sqlite` atau `.json` untuk pemulihan cepat.
- **Reset Data Demo**: Kembalikan dataset demo sekolah kapan saja.
- **Manajemen Akun & Sesi**: Ganti password admin, enkripsi bcrypt, token JWT, tombol Logout.

---

## 📱 Mode PWA Native Android
Aplikasi ini mendukung instalasi sebagai aplikasi mandiri (*Progressive Web App*) di perangkat Android, Windows, Mac, maupun Linux:
- `manifest.webmanifest` lengkap dengan ikon resolusi tinggi dan warna tema `#1E60FF`.
- `sw.js` (Service Worker) untuk caching aset statis dan navigasi responsif.
- Tombol **"Install Aplikasi Android"** muncul otomatis di header dan dashboard.
- Antarmuka ponsel dilengkapi **Bottom Navigation Bar** standar aplikasi native Android.

---

## 🛠️ Panduan Menjalankan Aplikasi

### Persyaratan Sistem
- Node.js versi 18 atau lebih baru (direkomendasikan v20 / v22)
- npm versi 9+

### Instalasi & Menjalankan

1. **Clone repositori**:
   ```bash
   git clone https://github.com/sakahayang-code/RUANG-UJIAN.git
   cd RUANG-UJIAN
   ```

2. **Instal Dependensi**:
   ```bash
   npm install
   cd client && npm install && cd ..
   ```

3. **Build Frontend**:
   ```bash
   npm run build
   ```

4. **Jalankan Server**:
   ```bash
   npm start
   ```
   Aplikasi akan berjalan di: **`http://localhost:5000`**

5. **Login Akun Default**:
   - **Username**: `admin`
   - **Password**: `admin123`

---

## 📂 Struktur Direktori Proyek

```
RUANG-UJIAN/
├── server/
│   ├── database/
│   │   └── db.js            # Inisialisasi SQLite, schema 18 tabel, & seed demo
│   ├── routes/
│   │   ├── auth.js          # Autentikasi & ubah password
│   │   ├── sekolah.js       # Identitas sekolah & upload logo
│   │   ├── ujian.js         # Manajemen ujian aktif
│   │   ├── master.js        # Kelas, mapel, peserta, panitia, pengawas, penguji, ruangan
│   │   ├── persiapan.js     # Proker, jadwal tulis & praktik, surat, tata tertib
│   │   ├── cetak.js         # Endpoint khusus 9 dokumen cetak resmi
│   │   ├── sistem.js        # Backup, restore SQLite/JSON, reset
│   │   └── stats.js         # Metrik & statistik dashboard
│   ├── data/                # Tempat file ruang_ujian.db tersimpan
│   ├── uploads/             # Direktori logo dan tanda tangan sekolah
│   └── index.js             # Express server utama
├── client/
│   ├── public/
│   │   ├── manifest.webmanifest # PWA Manifest
│   │   ├── sw.js            # PWA Service Worker
│   │   ├── icon.svg         # Ikon aplikasi vektor
│   │   └── favicon.svg
│   ├── src/
│   │   ├── components/      # Sidebar, Header, MobileNav, KopSurat, Modal, Toast
│   │   ├── context/         # AppContext (Auth, PWA, Navigation state)
│   │   ├── pages/           # Seluruh halaman modul aplikasi
│   │   ├── utils/api.js     # Fetch API wrapper
│   │   ├── App.jsx
│   │   └── main.jsx
│   ├── vite.config.js
│   └── package.json
├── package.json
└── README.md
```

---

## 📄 Lisensi
Hak Cipta &copy; 2025 Ruang Ujian. Dikembangkan untuk kemudahan tata kelola ujian sekolah di Indonesia.
