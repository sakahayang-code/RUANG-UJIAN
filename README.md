# RUANG UJIAN – SISTEM ADMINISTRASI UJIAN SEKOLAH TERPADU

**RUANG UJIAN** adalah aplikasi administrasi penyelenggaraan ujian sekolah (Ujian Tertulis dan Ujian Praktik) terpadu, berbasis **PHP Native** dan **SQLite (PDO SQLite)**, yang dirancang khusus untuk kemudahan instalasi di **XAMPP (pengembangan lokal)** maupun **Rumahweb Shared Hosting (cPanel)** tanpa memerlukan Node.js, VPS, SSH terminal, atau layanan daemon.

---

## 🚫 BATASAN UTAMA APLIKASI (STRICT SCOPE BOUNDARY)

Aplikasi ini **HANYA** mengelola administrasi, logistik, pengawasan, dan pelaporan fisik pelaksanaan ujian.
Aplikasi ini **SECARA TEGAS TIDAK MEMILIKI FITUR**:
- ❌ Input nilai akademik siswa.
- ❌ Koreksi atau pemeriksaan jawaban.
- ❌ Pengolahan nilai ujian / rapor.
- ❌ Perankingan peserta.
- ❌ Penilaian kompetensi atau kelulusan akademik.

*Catatan:* Tersedia checklist administrasi penerimaan berkas nilai dari guru, namun **hanya mencatat status dokumen** (belum diserahkan, sudah diserahkan, selesai) dan **tidak menginput atau mengolah nilai siswa**.

---

## 🚀 TEKNOLOGI & ARSITEKTUR

- **Bahasa**: PHP Native 8.0+ (Arsitektur Modular MVC Bersih)
- **Database**: SQLite 3 via PDO SQLite (Foreign Keys aktif, WAL Mode)
- **Frontend**: HTML5, Modern CSS (Design System Responsif), Vanilla JavaScript (Zero external JS library)
- **Barcode & QR Code**: Pure PHP Standalone SVG Generator (`QrGenerator`) tanpa API eksternal
- **Generator Dokumen Word**: Pure PHP OpenXML Builder (`DocxGenerator`) dengan `ZipArchive`
- **Keamanan**:
  - Prepared Statements pada 100% query database
  - Perlindungan anti-CSRF token pada seluruh form aksi
  - Password di-hash menggunakan `password_hash()` (Bcrypt)
  - Penyimpanan file privat aman (diblokir dari akses URL langsung via `.htaccess` dan disajikan lewat `FileController` dengan verifikasi hak akses)
  - Audit log lengkap aktivitas penting sistem dan riwayat login

---

## 👥 8 HAK AKSES PERAN (RBAC) & AKUN DEFAULT

Sistem dilengkapi matriks 8 peran resmi:

| No | Peran (Role) | Username Default | Password Default | Tanggung Jawab Utama |
|---|---|---|---|---|
| 1 | **Super Admin** | `admin` | `password123` | Akses penuh sistem, manajemen user, backup/restore database |
| 2 | **Kepala Sekolah** | `kepsek` | `password123` | Monitoring dashboard, persetujuan program, pengesahan laporan |
| 3 | **Ketua Panitia** | `ketua` | `password123` | Perencanaan ujian, program kerja & RAB, evaluasi pelaksanaan |
| 4 | **Sekretaris** | `sekretaris` | `password123` | Surat dinas, SK panitia, notula, laporan akhir 18 bab |
| 5 | **Bendahara** | `bendahara` | `password123` | Rencana Anggaran Biaya (RAB) dan realisasi keuangan panitia |
| 6 | **Seksi Naskah** | `seksi_naskah` | `password123` | Checklist soal, penggandaan, distribusi, packing lembar jawaban |
| 7 | **Seksi Tempat** | `seksi_tempat` | `password123` | Pengaturan ruang, denah kursi baris x kolom, label pintu |
| 8 | **Pengawas Ruang** | `pengawas` | `password123` | Presensi peserta ruang, berita acara, catatan insiden ujian |

---

## 📦 FITUR-FITUR UTAMA APLIKASI

1. **Dashboard Utama Dinamis**:
   - Statistik real-time: Peserta, Ruang, Jadwal, Panitia, Pengawas, Penguji, Lembar Jawaban.
   - Peringatan pintar (notifikasi dini jika ada naskah belum kembali, selisih lembar jawaban, ruang tanpa pengawas, atau dokumen belum diserahkan).
2. **Pengaturan Identitas Sekolah**:
   - Kop surat resmi, nama sekolah, NPSN, logo, pejabat penandatangan, format penomoran surat otomatis.
3. **Master Data Lengkap**:
   - Siswa (NIS, NISN, Kelas, Foto, Impor/Ekspor CSV, pencegahan duplikasi).
   - Kelas (Tingkat, Wali Kelas).
   - Guru & Tenaga Kependidikan (NIP, NUPTK, Jabatan).
   - Mata Pelajaran (Kode, Nama, Tingkat, Jenis Ujian: Tertulis/Praktik/Keduanya).
4. **Manajemen Multi-Kegiatan Ujian (Multi-Event & Multi-Tahun)**:
   - Satu sistem dapat mengelola UTS, UAS, PAS, PAT, Ujian Sekolah, Ujian Praktik per semester/tahun pelajaran.
   - Data kegiatan yang sudah selesai diarsipkan permanen.
5. **Administrasi Kepanitiaan**:
   - Struktur panitia, unggah & riwayat versi SK Panitia (PDF preview aman).
   - Generator Surat Undangan Rapat Panitia (cetak & ekspor Word).
   - Generator Surat Tugas (Penyusun Kisi-kisi, Penyusun Soal, Pengawas Ruang, Penguji Praktik).
6. **Program Kerja Panitia 20 Bagian & RAB Otomatis**:
   - Cover, Kata Pengantar, Daftar Isi, Bab I s.d. Bab X, Halaman Pengesahan.
   - Tabel dinamis rencana kerja dan Rencana Anggaran Biaya (RAB) dengan kalkulasi otomatis.
   - Cetak resmi dan ekspor Word DOCX.
7. **Persiapan Naskah & Dokumen Ujian**:
   - Tata Tertib Peserta, Pengawas, dan Penguji Praktik.
   - Checklist penerimaan kisi-kisi, soal, kunci jawaban, dan status dokumen nilai guru.
   - Modul penggandaan naskah soal dengan cadangan yang dapat dikonfigurasi (*bukan angka tetap*).
8. **Manajemen Ujian Tertulis**:
   - Jadwal ujian tertulis per sesi.
   - **Pembagian Ruang Fleksibel**: Mendukung ruang yang berisi gabungan berbagai tingkat dan kelas asal (misal: VII A, VII B, VIII A, IX B).
   - Penataan denah tempat duduk meja per ruang (baris x kolom) dan pengacakan otomatis.
   - Penugasan pengawas dengan sistem deteksi bentrok jadwal otomatis.
   - Pelaksanaan ujian: Presensi digital, berita acara ruang, dan pencatatan insiden.
9. **Manajemen Ujian Praktik**:
   - Jadwal ujian praktik, alokasi stasiun/pos/laboratorium, penugasan penguji, presensi, berita acara, serta unggah dokumentasi foto/video pelaksanaan (tanpa input nilai akademik).
10. **Administrasi Kehadiran Terpadu**:
    - Presensi Panitia, Pengawas Ruang, dan Penguji Praktik (format tanda tangan manual dan digital).
11. **Distribusi Naskah Soal**:
    - Pencatatan serah terima naskah soal per ruang dan mata pelajaran, deteksi selisih antara naskah keluar dan naskah sisa/kembali.
12. **Penerimaan & Pengemasan Lembar Jawaban (3 Mode)**:
    - Verifikasi lembar jawaban fisik terhadap jumlah peserta hadir di ruang ujian.
    - Peringatan selisih otomatis (status `ada_selisih`).
    - **3 Pilihan Mode Pengemasan Amplop Resmi**:
      - **Mode 1 (Per Kelas Asal)**: 1 amplop berisi seluruh mapel untuk 1 kelas asal tertentu.
      - **Mode 2 (Per Mata Pelajaran)**: 1 amplop berisi seluruh kelas asal untuk 1 mata pelajaran tertentu.
      - **Mode 3 (Per Kelas & Mapel)**: 1 amplop khusus untuk 1 kelas asal dan 1 mata pelajaran tertentu.
    - Rekapitulasi penyerahan berkas.
13. **Pusat Cetak Perlengkapan Ujian**:
    - Kartu Peserta Ujian berfoto dengan pure PHP QR Code verifikasi.
    - Name Tag Panitia dan Pengawas Ruang.
    - Plang Label Pintu Ruang Ujian.
    - **Label Amplop Naskah Soal (Ketentuan Resmi: 1 Ruang = 1 Amplop = 1 Label)**: Menghitung rincian naskah utama per kelas gabungan dan naskah cadangan yang dapat dikonfigurasi, *bebas kolom tanda tangan*.
    - **Label Amplop Lembar Jawaban**: Mendukung 3 mode pengemasan amplop.
14. **Berita Acara & Catatan Kejadian Khusus (Insiden)**:
    - Rekapitulasi berita acara tertulis dan praktik.
    - Buku agenda kejadian khusus/insiden selama ujian.
15. **Laporan Akhir Panitia 18 Bab Lengkap**:
    - Terintegrasi langsung dengan database kegiatan (peserta, ruang, kehadiran, distribusi, pengemasan, evaluasi).
    - Editor narasi bab, cetak resmi dengan lembar pengesahan, dan ekspor Word DOCX.
16. **Backup & Restore Database SQLite**:
    - Backup atomik instan SQLite.
    - Validasi keaslian berkas SQLite (`SQLite format 3\000`).
    - **Pencadangan pengaman otomatis (*auto-safety backup*)** yang dibuat sebelum proses pemulihan (restore) dijalankan.
    - Download dan unggah berkas cadangan.

---

## 💻 PANDUAN INSTALASI LOKAL (XAMPP / LARAGON)

1. Pastikan modul PHP di XAMPP memiliki ekstensi `pdo_sqlite`, `sqlite3`, `gd`, dan `zip` aktif pada `php.ini`:
   ```ini
   extension=pdo_sqlite
   extension=sqlite3
   extension=gd
   extension=zip
   ```
2. Letakkan folder aplikasi ke dalam `C:\xampp\htdocs\ruang-ujian`.
3. Buka browser dan akses:
   ```
   http://localhost/ruang-ujian
   ```
4. Jika database belum diinisialisasi, sistem secara otomatis mengarahkan ke halaman pemasangan (`/install`).
5. Klik **"Pasang & Inisialisasi Database Sekarang"**.
6. Login menggunakan akun `admin` / `password123`.

---

## 🌐 PANDUAN DEPLOYMENT KE RUMAHWEB SHARED HOSTING (CPANEL)

1. **Persiapan Berkas**:
   - Kompres seluruh isi folder proyek menjadi `ruang-ujian.zip`.
2. **Unggah ke cPanel**:
   - Masuk ke cPanel Rumahweb -> **File Manager**.
   - Unggah `ruang-ujian.zip` ke dalam direktori `public_html` (atau subdomain).
   - Ekstrak berkas zip tersebut.
3. **Konfigurasi Versi PHP di cPanel**:
   - Buka menu **Select PHP Version** di cPanel.
   - Pilih **PHP 8.1** atau **PHP 8.2**.
   - Pastikan modul berikut tercentang aktif:
     - `pdo_sqlite`
     - `sqlite3`
     - `gd`
     - `zip`
     - `mbstring`
     - `fileinfo`
4. **Izin Akses Folder (File Permissions)**:
   - Pastikan direktori berikut memiliki hak akses writable (`chmod 755` atau `777` jika diperlukan host):
     - `database/storage/`
     - `storage/`
     - `storage/backups/`
     - `storage/uploads/`
5. **Keamanan Ekstra**:
   - Berkas `.htaccess` yang tersedia di direktori `database/storage/` dan `storage/` secara otomatis mencegah pengunduhan berkas privat melalui browser secara langsung.
6. **Akses Aplikasi**:
   - Kunjungi domain Anda: `https://namasekolah.sch.id` (atau subdomain).
   - Sistem siap digunakan langsung!

---

## ⚡ PANDUAN AKTIVASI AUTO DEPLOY (CI/CD KE RUMAHWEB)

Aplikasi ini sudah dilengkapi dengan pipa **CI/CD GitHub Actions** (`.github/workflows/deploy.yml`) dan konfigurasi **cPanel Git** (`.cpanel.yml`). Setiap kali Anda melakukan `git push origin main`, sistem akan secara otomatis menguji kode dan mengunggah perubahan ke hosting tanpa menyentuh database produksi atau berkas yang sudah diunggah oleh siswa/guru.

### Cara 1: Menggunakan GitHub Actions via FTPS (Sangat Direkomendasikan)

1. Buka repositori Anda di GitHub: **`https://github.com/sakahayang-code/RUANG-UJIAN`**.
2. Masuk ke tab **Settings** -> pilih menu **Secrets and variables** -> klik **Actions**.
3. Klik tombol **New repository secret** dan tambahkan secret berikut:

   | Nama Secret | Nilai / Keterangan | Contoh |
   |---|---|---|
   | `FTP_SERVER` | Alamat host FTP server Rumahweb Anda | `ftp.namasekolah.sch.id` atau IP server cPanel |
   | `FTP_USERNAME` | Username cPanel atau akun FTP terdedikasi | `u1234567` |
   | `FTP_PASSWORD` | Password akun cPanel atau akun FTP Anda | `password_anda` |
   | `FTP_TARGET_DIR` | *(Opsional)* Folder target di server hosting | `public_html/` (atau folder subdomain) |
   | `FTP_PORT` | *(Opsional)* Port FTP (default: `21`) | `21` |
   | `FTP_PROTOCOL` | *(Opsional)* Protokol transfer (`ftps` atau `ftp`) | `ftps` |

4. Setelah secret disimpan, setiap Anda melakukan `git push` ke branch `main`, GitHub Actions akan:
   - Menjalankan seluruh pengujian 25 skenario otomatis.
   - Mengunggah berkas terbaru ke hosting Rumahweb secara otomatis.
   - **Melindungi data produksi**: Database SQLite produksi (`database/storage/*.sqlite`) dan berkas unggahan (`storage/uploads/`) **TIDAK AKAN TERTAMPAK / TERHAPUS**.

### Cara 2: Menggunakan cPanel Git™ Version Control

Jika Anda menggunakan fitur Git bawaan di dalam cPanel Rumahweb:
1. Masuk ke cPanel -> menu **Git™ Version Control**.
2. Buat repositori baru dan masukkan Clone URL: `https://github.com/sakahayang-code/RUANG-UJIAN.git`.
3. Tentukan Repository Path di cPanel (misal: `/home/username/repo-ruang-ujian`).
4. Berkas `.cpanel.yml` yang sudah tersedia akan mengeksekusi sinkronisasi otomatis ke direktori `public_html/` saat tombol **Deploy HEAD Commit** diklik atau saat Webhook GitHub dipicu.

---

## 🧪 PENGUJIAN OTOMATIS (TEST SUITE)

Aplikasi telah dilengkapi skrip pengujian otomatis komprehensif yang menguji **Skenario A hingga J** serta verifikasi batasan utama tanpa nilai akademik:

```bash
php tests/test_all_scenarios.php
```

Hasil verifikasi:
- ✅ **Skenario A**: 1 Ruang = 1 Amplop = 1 Label (gabungan kelas VII A, VII B, VIII A, IX B dengan naskah cadangan terkonfigurasi, tanpa kolom tanda tangan).
- ✅ **Skenario B**: Distribusi naskah keluar dan masuk per ruang/mapel dengan deteksi selisih.
- ✅ **Skenario C**: Pengemasan lembar jawaban Mode 1 (Per Kelas Asal).
- ✅ **Skenario D**: Pengemasan lembar jawaban Mode 2 (Per Mata Pelajaran).
- ✅ **Skenario E**: Pengemasan lembar jawaban Mode 3 (Per Kelas & Mata Pelajaran).
- ✅ **Skenario F**: Deteksi selisih lembar jawaban (30 hadir vs 29 diterima, status otomatis `ada_selisih`).
- ✅ **Skenario G**: Program Kerja Panitia 20 Bab & kalkulasi RAB otomatis + ekspor Word DOCX.
- ✅ **Skenario H**: Integrasi kehadiran dengan penugasan pengawas & guru resmi.
- ✅ **Skenario I**: Backup SQLite, validasi header `SQLite format 3`, restore, dan auto-safety backup.
- ✅ **Skenario J**: Matriks 8 Role Pengguna RBAC.
- ✅ **Strict Scope**: Bebas 100% dari kolom nilai, ranking, atau peringkat akademik.
- ✅ **Pure PHP QR**: Generator QR Code SVG mandiri tanpa koneksi internet.
