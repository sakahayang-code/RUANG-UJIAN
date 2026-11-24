const Database = require('better-sqlite3');
const path = require('path');
const fs = require('fs');
const bcrypt = require('bcryptjs');

const dbDir = path.join(__dirname, '../data');
if (!fs.existsSync(dbDir)) {
  fs.mkdirSync(dbDir, { recursive: true });
}

const uploadsDir = path.join(__dirname, '../uploads');
if (!fs.existsSync(uploadsDir)) {
  fs.mkdirSync(uploadsDir, { recursive: true });
}

const dbPath = path.join(dbDir, 'ruang_ujian.db');
const db = new Database(dbPath);

// Enable foreign keys & WAL mode for performance
db.pragma('journal_mode = WAL');
db.pragma('foreign_keys = ON');

function initDatabase() {
  db.exec(`
    -- Users table
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT UNIQUE NOT NULL,
      password TEXT NOT NULL,
      nama TEXT NOT NULL,
      role TEXT NOT NULL DEFAULT 'admin',
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Identitas Sekolah table (Single Record id=1)
    CREATE TABLE IF NOT EXISTS identitas_sekolah (
      id INTEGER PRIMARY KEY,
      nama_sekolah TEXT NOT NULL,
      npsn TEXT,
      nss TEXT,
      jenjang TEXT DEFAULT 'SMA',
      status_sekolah TEXT DEFAULT 'Negeri',
      alamat TEXT,
      desa_kelurahan TEXT,
      kecamatan TEXT,
      kabupaten_kota TEXT,
      provinsi TEXT,
      kode_pos TEXT,
      telepon TEXT,
      email TEXT,
      website TEXT,
      kepala_sekolah TEXT,
      nip_kepala_sekolah TEXT,
      tahun_ajaran TEXT DEFAULT '2024/2025',
      semester TEXT DEFAULT 'Genap',
      titimangsa_kota TEXT DEFAULT 'Kota Nusantara',
      logo_url TEXT,
      ttd_stempel_url TEXT,
      kop_baris1 TEXT DEFAULT 'PEMERINTAH PROVINSI JAWA BARAT',
      kop_baris2 TEXT DEFAULT 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
      kop_baris3 TEXT DEFAULT 'CABANG DINAS PENDIDIKAN WILAYAH VII',
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Manajemen Ujian
    CREATE TABLE IF NOT EXISTS ujian (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      nama_ujian TEXT NOT NULL,
      tahun_ajaran TEXT NOT NULL,
      semester TEXT NOT NULL,
      tanggal_mulai TEXT,
      tanggal_selesai TEXT,
      is_active INTEGER DEFAULT 1,
      keterangan TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Master Data Kelas
    CREATE TABLE IF NOT EXISTS kelas (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      tingkat TEXT NOT NULL,
      nama_kelas TEXT NOT NULL UNIQUE,
      jurusan TEXT,
      wali_kelas TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Master Data Mata Pelajaran
    CREATE TABLE IF NOT EXISTS mata_pelajaran (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      kode_mapel TEXT NOT NULL,
      nama_mapel TEXT NOT NULL,
      jenis TEXT NOT NULL DEFAULT 'tulis' CHECK (jenis IN ('tulis', 'praktik')),
      tingkat TEXT DEFAULT 'Semua',
      durasi_menit INTEGER DEFAULT 90,
      jumlah_soal INTEGER DEFAULT 40,
      kriteria_penilaian TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Master Data Ruangan
    CREATE TABLE IF NOT EXISTS ruangan (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      kode_ruang TEXT NOT NULL UNIQUE,
      nama_ruang TEXT NOT NULL,
      kapasitas INTEGER DEFAULT 20,
      jumlah_baris INTEGER DEFAULT 5,
      jumlah_kolom INTEGER DEFAULT 4,
      lokasi TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Master Data Peserta Ujian
    CREATE TABLE IF NOT EXISTS peserta (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      ujian_id INTEGER REFERENCES ujian(id) ON DELETE CASCADE,
      no_peserta TEXT NOT NULL UNIQUE,
      nis TEXT,
      nisn TEXT,
      nama TEXT NOT NULL,
      jenis_kelamin TEXT DEFAULT 'L' CHECK (jenis_kelamin IN ('L', 'P')),
      kelas_id INTEGER REFERENCES kelas(id) ON DELETE SET NULL,
      nama_kelas TEXT,
      sesi INTEGER DEFAULT 1,
      ruang_id INTEGER REFERENCES ruangan(id) ON DELETE SET NULL,
      nama_ruang TEXT,
      no_meja INTEGER DEFAULT 0,
      password TEXT DEFAULT '123456',
      foto TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Master Data Panitia
    CREATE TABLE IF NOT EXISTS panitia (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      ujian_id INTEGER REFERENCES ujian(id) ON DELETE CASCADE,
      nama TEXT NOT NULL,
      nip TEXT,
      jabatan_struktural TEXT,
      jabatan_panitia TEXT NOT NULL,
      no_hp TEXT,
      tugas TEXT,
      urutan INTEGER DEFAULT 0,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Master Data Pengawas Ruang
    CREATE TABLE IF NOT EXISTS pengawas (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      kode_pengawas TEXT,
      nama TEXT NOT NULL,
      nip TEXT,
      asal_sekolah TEXT,
      mata_pelajaran_ampu TEXT,
      no_hp TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Master Data Penguji Praktik
    CREATE TABLE IF NOT EXISTS penguji_praktik (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      nama TEXT NOT NULL,
      nip TEXT,
      mata_pelajaran TEXT NOT NULL,
      asal_instansi TEXT,
      no_hp TEXT,
      keterangan TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Program Kerja Panitia
    CREATE TABLE IF NOT EXISTS program_kerja (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      ujian_id INTEGER REFERENCES ujian(id) ON DELETE CASCADE,
      tahap TEXT NOT NULL CHECK (tahap IN ('Persiapan', 'Pelaksanaan', 'Evaluasi & Pelaporan')),
      kegiatan TEXT NOT NULL,
      waktu_pelaksanaan TEXT NOT NULL,
      penanggung_jawab TEXT NOT NULL,
      anggaran REAL DEFAULT 0,
      status TEXT DEFAULT 'Belum' CHECK (status IN ('Belum', 'Sedang', 'Selesai')),
      keterangan TEXT,
      urutan INTEGER DEFAULT 0
    );

    -- Jadwal Ujian Tulis
    CREATE TABLE IF NOT EXISTS jadwal_tulis (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      ujian_id INTEGER REFERENCES ujian(id) ON DELETE CASCADE,
      hari_tanggal TEXT NOT NULL,
      sesi INTEGER DEFAULT 1,
      jam_mulai TEXT NOT NULL,
      jam_selesai TEXT NOT NULL,
      mapel_id INTEGER REFERENCES mata_pelajaran(id) ON DELETE CASCADE,
      nama_mapel TEXT NOT NULL,
      kelas_tingkat TEXT NOT NULL,
      ruang_id INTEGER REFERENCES ruangan(id) ON DELETE SET NULL,
      nama_ruang TEXT,
      pengawas_1 TEXT,
      pengawas_2 TEXT,
      keterangan TEXT
    );

    -- Jadwal Ujian Praktik
    CREATE TABLE IF NOT EXISTS jadwal_praktik (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      ujian_id INTEGER REFERENCES ujian(id) ON DELETE CASCADE,
      hari_tanggal TEXT NOT NULL,
      waktu TEXT NOT NULL,
      mapel_id INTEGER REFERENCES mata_pelajaran(id) ON DELETE CASCADE,
      nama_mapel TEXT NOT NULL,
      kelas_tingkat TEXT NOT NULL,
      tempat TEXT,
      penguji_1 TEXT,
      penguji_2 TEXT,
      kelompok_peserta TEXT
    );

    -- Persuratan
    CREATE TABLE IF NOT EXISTS surat (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      ujian_id INTEGER REFERENCES ujian(id) ON DELETE CASCADE,
      jenis_surat TEXT NOT NULL,
      nomor_surat TEXT NOT NULL,
      perihal TEXT NOT NULL,
      lampiran TEXT DEFAULT '-',
      tanggal_surat TEXT NOT NULL,
      tempat_surat TEXT NOT NULL,
      dasar_hukum TEXT,
      isi_surat TEXT,
      tujuan_penerima TEXT,
      daftar_petugas_json TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Tata Tertib
    CREATE TABLE IF NOT EXISTS tata_tertib (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      kategori TEXT UNIQUE NOT NULL,
      judul TEXT NOT NULL,
      isi_poin_json TEXT NOT NULL,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- Berita Acara Pelaksanaan Ujian
    CREATE TABLE IF NOT EXISTS berita_acara (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      ujian_id INTEGER REFERENCES ujian(id) ON DELETE CASCADE,
      tanggal TEXT NOT NULL,
      ruang_id INTEGER,
      nama_ruang TEXT,
      sesi INTEGER DEFAULT 1,
      mapel_id INTEGER,
      nama_mapel TEXT,
      jumlah_seharusnya INTEGER DEFAULT 0,
      jumlah_hadir INTEGER DEFAULT 0,
      jumlah_tidak_hadir INTEGER DEFAULT 0,
      nomor_tidak_hadir TEXT,
      catatan_kejadian TEXT,
      pengawas_1_nama TEXT,
      pengawas_1_nip TEXT,
      pengawas_2_nama TEXT,
      pengawas_2_nip TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    -- LPJ Kegiatan Ujian
    CREATE TABLE IF NOT EXISTS lpj (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      ujian_id INTEGER UNIQUE REFERENCES ujian(id) ON DELETE CASCADE,
      nomor_sk TEXT,
      tentang_sk TEXT,
      latar_belakang TEXT,
      dasar_kegiatan TEXT,
      tujuan_kegiatan TEXT,
      hasil_evaluasi TEXT,
      hambatan_solusi TEXT,
      kesimpulan TEXT,
      saran TEXT,
      rincian_keuangan_json TEXT,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
  `);

  // Seed default admin
  const userCount = db.prepare('SELECT COUNT(*) as count FROM users').get().count;
  if (userCount === 0) {
    const salt = bcrypt.genSaltSync(10);
    const hash = bcrypt.hashSync('admin123', salt);
    db.prepare('INSERT INTO users (username, password, nama, role) VALUES (?, ?, ?, ?)').run(
      'admin',
      hash,
      'Administrator Utama',
      'admin'
    );
  }

  // Seed default Identitas Sekolah
  const sekolahCount = db.prepare('SELECT COUNT(*) as count FROM identitas_sekolah').get().count;
  if (sekolahCount === 0) {
    db.prepare(`
      INSERT INTO identitas_sekolah (
        id, nama_sekolah, npsn, nss, jenjang, status_sekolah, alamat,
        desa_kelurahan, kecamatan, kabupaten_kota, provinsi, kode_pos,
        telepon, email, website, kepala_sekolah, nip_kepala_sekolah,
        tahun_ajaran, semester, titimangsa_kota,
        kop_baris1, kop_baris2, kop_baris3
      ) VALUES (
        1, 'SMA NEGERI 1 MERDEKA BELAJAR', '20214567', '301026001001', 'SMA', 'Negeri',
        'Jl. Pendidikan Karakter No. 124, Kompleks Cendekia',
        'Sukamaju', 'Coblong', 'Kota Bandung', 'Jawa Barat', '40132',
        '(022) 2501234', 'info@sman1merdekabelajar.sch.id', 'https://sman1merdekabelajar.sch.id',
        'Dr. H. Ahmad Fauzi, M.Pd.', '19740512 199903 1 002',
        '2024/2025', 'Genap', 'Bandung',
        'PEMERINTAH DAERAH PROVINSI JAWA BARAT',
        'DINAS PENDIDIKAN',
        'CABANG DINAS PENDIDIKAN WILAYAH VII'
      )
    `).run();
  }

  // Seed default Ujian
  const ujianCount = db.prepare('SELECT COUNT(*) as count FROM ujian').get().count;
  let activeUjianId = 1;
  if (ujianCount === 0) {
    const res = db.prepare(`
      INSERT INTO ujian (nama_ujian, tahun_ajaran, semester, tanggal_mulai, tanggal_selesai, is_active, keterangan)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `).run(
      'Penilaian Akhir Tahun (PAT) & Asesmen Sumatif Akhir Semester Genap',
      '2024/2025',
      'Genap',
      '2025-06-02',
      '2025-06-10',
      1,
      'Pelaksanaan Ujian Tulis dan Ujian Praktik Tahun Ajaran 2024/2025'
    );
    activeUjianId = res.lastInsertRowid;
  } else {
    const u = db.prepare('SELECT id FROM ujian WHERE is_active = 1 LIMIT 1').get();
    if (u) activeUjianId = u.id;
  }

  // Seed Kelas
  const kelasCount = db.prepare('SELECT COUNT(*) as count FROM kelas').get().count;
  if (kelasCount === 0) {
    const insertKelas = db.prepare('INSERT INTO kelas (tingkat, nama_kelas, jurusan, wali_kelas) VALUES (?, ?, ?, ?)');
    const classes = [
      ['X', 'X MIPA 1', 'MIPA', 'Siti Rahmawati, S.Pd.'],
      ['X', 'X MIPA 2', 'MIPA', 'Budi Santoso, M.Pd.'],
      ['XI', 'XI MIPA 1', 'MIPA', 'Dewi Lestari, S.Si.'],
      ['XI', 'XI IPS 1', 'IPS', 'Drs. Hendra Gunawan'],
      ['XII', 'XII MIPA 1', 'MIPA', 'Ir. Agus Pratama, M.Kom.'],
      ['XII', 'XII IPS 1', 'IPS', 'Nuraini, S.Pd., M.M.']
    ];
    for (const c of classes) {
      insertKelas.run(...c);
    }
  }

  // Seed Mata Pelajaran
  const mapelCount = db.prepare('SELECT COUNT(*) as count FROM mata_pelajaran').get().count;
  if (mapelCount === 0) {
    const insertMapel = db.prepare('INSERT INTO mata_pelajaran (kode_mapel, nama_mapel, jenis, tingkat, durasi_menit, jumlah_soal, kriteria_penilaian) VALUES (?, ?, ?, ?, ?, ?, ?)');
    // Ujian Tulis
    const tulisList = [
      ['PAI', 'Pendidikan Agama Islam dan Budi Pekerti', 'tulis', 'Semua', 90, 40, null],
      ['PPKN', 'Pendidikan Pancasila dan Kewarganegaraan', 'tulis', 'Semua', 90, 40, null],
      ['BIND', 'Bahasa Indonesia', 'tulis', 'Semua', 90, 45, null],
      ['MTK', 'Matematika Umum', 'tulis', 'Semua', 120, 35, null],
      ['BING', 'Bahasa Inggris', 'tulis', 'Semua', 90, 40, null],
      ['FIS', 'Fisika', 'tulis', 'X,XI,XII', 90, 35, null],
      ['KIM', 'Kimia', 'tulis', 'X,XI,XII', 90, 35, null],
      ['BIO', 'Biologi', 'tulis', 'X,XI,XII', 90, 40, null],
      ['EKO', 'Ekonomi', 'tulis', 'X,XI,XII', 90, 40, null],
      ['SOS', 'Sosiologi', 'tulis', 'X,XI,XII', 90, 40, null]
    ];
    for (const m of tulisList) {
      insertMapel.run(...m);
    }
    // Ujian Praktik
    const praktikList = [
      ['PR-INF', 'Praktik Informatika / Komputer', 'praktik', 'Semua', 60, 0, 'Penguasaan Tools, Algoritma Pemecahan Masalah, Presentasi Kode'],
      ['PR-IPA', 'Praktik Fisika & Kimia Terpadu', 'praktik', 'X,XI,XII', 60, 0, 'Ketepatan Percobaan Lab, Analisis Data, Laporan Ilmiah'],
      ['PR-ING', 'Praktik Bahasa Inggris (Speaking & Conversation)', 'praktik', 'Semua', 45, 0, 'Fluency, Pronunciation, Grammar, Comprehension'],
      ['PR-SBK', 'Praktik Seni Budaya (Kriya & Seni Rupa)', 'praktik', 'Semua', 90, 0, 'Kreativitas, Estetika, Kerapian Hasil Karya'],
      ['PR-PJK', 'Praktik Pendidikan Jasmani dan Olahraga', 'praktik', 'Semua', 60, 0, 'Kebugaran Jasmani, Teknik Dasar Atletik & Senam']
    ];
    for (const m of praktikList) {
      insertMapel.run(...m);
    }
  }

  // Seed Ruangan
  const ruangCount = db.prepare('SELECT COUNT(*) as count FROM ruangan').get().count;
  if (ruangCount === 0) {
    const insertRuang = db.prepare('INSERT INTO ruangan (kode_ruang, nama_ruang, kapasitas, jumlah_baris, jumlah_kolom, lokasi) VALUES (?, ?, ?, ?, ?, ?)');
    const rooms = [
      ['R.01', 'Ruang Ujian 01 (Kelas X-1)', 20, 5, 4, 'Gedung A Lantai 1'],
      ['R.02', 'Ruang Ujian 02 (Kelas X-2)', 20, 5, 4, 'Gedung A Lantai 1'],
      ['R.03', 'Ruang Ujian 03 (Kelas XI-1)', 20, 5, 4, 'Gedung B Lantai 2'],
      ['R.04', 'Ruang Ujian 04 (Kelas XII-1)', 20, 5, 4, 'Gedung B Lantai 2'],
      ['LAB.KOM', 'Laboratorium Komputer CBT', 36, 6, 6, 'Gedung Multimedia']
    ];
    for (const r of rooms) {
      insertRuang.run(...r);
    }
  }

  // Seed Peserta Ujian
  const pesertaCount = db.prepare('SELECT COUNT(*) as count FROM peserta').get().count;
  if (pesertaCount === 0) {
    const insertPeserta = db.prepare(`
      INSERT INTO peserta (ujian_id, no_peserta, nis, nisn, nama, jenis_kelamin, kelas_id, nama_kelas, sesi, ruang_id, nama_ruang, no_meja, password)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    const students = [
      ['01-001-001-8', '24250101', '0081234567', 'Aditya Pratama Nugraha', 'L', 1, 'X MIPA 1', 1, 1, 'Ruang Ujian 01 (Kelas X-1)', 1, 'P@ss01'],
      ['01-001-002-7', '24250102', '0081234568', 'Aisyah Putri Maharani', 'P', 1, 'X MIPA 1', 1, 1, 'Ruang Ujian 01 (Kelas X-1)', 2, 'P@ss02'],
      ['01-001-003-6', '24250103', '0081234569', 'Alif Rizky Ramadhan', 'L', 1, 'X MIPA 1', 1, 1, 'Ruang Ujian 01 (Kelas X-1)', 3, 'P@ss03'],
      ['01-001-004-5', '24250104', '0081234570', 'Amanda Cahyani Dewi', 'P', 1, 'X MIPA 1', 1, 1, 'Ruang Ujian 01 (Kelas X-1)', 4, 'P@ss04'],
      ['01-001-005-4', '24250105', '0081234571', 'Bagus Setiawan', 'L', 1, 'X MIPA 1', 1, 1, 'Ruang Ujian 01 (Kelas X-1)', 5, 'P@ss05'],
      ['01-001-006-3', '24250106', '0081234572', 'Cantika Amelia Zahra', 'P', 1, 'X MIPA 1', 1, 1, 'Ruang Ujian 01 (Kelas X-1)', 6, 'P@ss06'],
      ['01-001-007-2', '24250107', '0081234573', 'Daffa Ihsan Fadhilah', 'L', 1, 'X MIPA 1', 1, 1, 'Ruang Ujian 01 (Kelas X-1)', 7, 'P@ss07'],
      ['01-001-008-9', '24250108', '0081234574', 'Dinda Ayu Lestari', 'P', 1, 'X MIPA 1', 1, 1, 'Ruang Ujian 01 (Kelas X-1)', 8, 'P@ss08'],
      ['01-001-009-8', '24250109', '0081234575', 'Fajar Nur Hidayat', 'L', 1, 'X MIPA 1', 1, 1, 'Ruang Ujian 01 (Kelas X-1)', 9, 'P@ss09'],
      ['01-001-010-7', '24250110', '0081234576', 'Gita Permata Sari', 'P', 1, 'X MIPA 1', 1, 1, 'Ruang Ujian 01 (Kelas X-1)', 10, 'P@ss10'],

      ['01-002-001-2', '24250201', '0082234501', 'Hafiz Kurniawan', 'L', 2, 'X MIPA 2', 1, 2, 'Ruang Ujian 02 (Kelas X-2)', 1, 'P@ss11'],
      ['01-002-002-1', '24250202', '0082234502', 'Indah Permatasari', 'P', 2, 'X MIPA 2', 1, 2, 'Ruang Ujian 02 (Kelas X-2)', 2, 'P@ss12'],
      ['01-002-003-0', '24250203', '0082234503', 'Jonathan Aditya', 'L', 2, 'X MIPA 2', 1, 2, 'Ruang Ujian 02 (Kelas X-2)', 3, 'P@ss13'],
      ['01-002-004-9', '24250204', '0082234504', 'Keysha Azzahra', 'P', 2, 'X MIPA 2', 1, 2, 'Ruang Ujian 02 (Kelas X-2)', 4, 'P@ss14'],
      ['01-002-005-8', '24250205', '0082234505', 'Luthfi Hakim', 'L', 2, 'X MIPA 2', 1, 2, 'Ruang Ujian 02 (Kelas X-2)', 5, 'P@ss15'],

      ['02-003-001-5', '23240101', '0071234501', 'Muhammad Zaky Farhan', 'L', 3, 'XI MIPA 1', 1, 3, 'Ruang Ujian 03 (Kelas XI-1)', 1, 'P@ss16'],
      ['02-003-002-4', '23240102', '0071234502', 'Nabila Syakira', 'P', 3, 'XI MIPA 1', 1, 3, 'Ruang Ujian 03 (Kelas XI-1)', 2, 'P@ss17'],
      ['02-003-003-3', '23240103', '0071234503', 'Raffi Ahmad Baihaqi', 'L', 3, 'XI MIPA 1', 1, 3, 'Ruang Ujian 03 (Kelas XI-1)', 3, 'P@ss18'],
      ['02-003-004-2', '23240104', '0071234504', 'Salma Nuraini', 'P', 3, 'XI MIPA 1', 1, 3, 'Ruang Ujian 03 (Kelas XI-1)', 4, 'P@ss19'],
      ['02-003-005-1', '23240105', '0071234505', 'Teguh Prasetyo', 'L', 3, 'XI MIPA 1', 1, 3, 'Ruang Ujian 03 (Kelas XI-1)', 5, 'P@ss20']
    ];

    for (const s of students) {
      insertPeserta.run(activeUjianId, ...s);
    }
  }

  // Seed Panitia
  const panitiaCount = db.prepare('SELECT COUNT(*) as count FROM panitia').get().count;
  if (panitiaCount === 0) {
    const insertPanitia = db.prepare(`
      INSERT INTO panitia (ujian_id, nama, nip, jabatan_struktural, jabatan_panitia, no_hp, tugas, urutan)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    `);

    const committees = [
      [activeUjianId, 'Dr. H. Ahmad Fauzi, M.Pd.', '19740512 199903 1 002', 'Kepala Sekolah', 'Penanggung Jawab', '081234567890', 'Bertanggung jawab penuh atas kelancaran seluruh rangkaian kegiatan ujian', 1],
      [activeUjianId, 'H. Mulyadi, M.Pd.', '19760815 200212 1 003', 'Wakasek Kurikulum', 'Ketua Panitia', '081298765432', 'Memimpin dan mengkoordinasikan seluruh divisi teknis ujian sekolah', 2],
      [activeUjianId, 'Siti Nurjanah, S.Pd.', '19820320 200801 2 011', 'Guru Bahasa Indonesia', 'Sekretaris', '081311223344', 'Mengelola administrasi persuratan, jadwal, daftar hadir, dan pelaporan', 3],
      [activeUjianId, 'Dra. Hj. Euis Karlina', '19691204 199403 2 004', 'Guru Ekonomi / Bendahara', 'Bendahara', '081544556677', 'Mengelola anggaran, biaya operasional, dan laporan pertanggungjawaban keuangan', 4],
      [activeUjianId, 'Ir. Agus Pratama, M.Kom.', '19850110 201001 1 015', 'Kepala Lab Komputer', 'Seksi Naskah & CBT', '081788990011', 'Menyiapkan bank soal, penggandaan/upload naskah, dan verifikasi kelayakan naskah', 5],
      [activeUjianId, 'Bambang Triyono, S.Pd.', '19830722 200902 1 008', 'Guru PJOK', 'Seksi Ruangan & Perlengkapan', '081900112233', 'Menata ruangan ujian, nomor meja, denah duduk, dan kebersihan lingkungan ujian', 6],
      [activeUjianId, 'Rina Marlina, S.Pd.', '19880415 201403 2 005', 'Guru BK', 'Seksi Konsumsi', '081222334455', 'Menyiapkan konsumsi bagi pengawas ruang, panitia, dan teknisi setiap sesi', 7]
    ];

    for (const c of committees) {
      insertPanitia.run(...c);
    }
  }

  // Seed Pengawas
  const pengawasCount = db.prepare('SELECT COUNT(*) as count FROM pengawas').get().count;
  if (pengawasCount === 0) {
    const insertPengawas = db.prepare(`
      INSERT INTO pengawas (kode_pengawas, nama, nip, asal_sekolah, mata_pelajaran_ampu, no_hp)
      VALUES (?, ?, ?, ?, ?, ?)
    `);

    const invigilators = [
      ['P01', 'Drs. Hendra Gunawan', '19680315 199303 1 005', 'SMA Negeri 1 Merdeka Belajar', 'Sosiologi', '081211112222'],
      ['P02', 'Dewi Lestari, S.Si.', '19840920 200801 2 015', 'SMA Negeri 1 Merdeka Belajar', 'Fisika', '081233334444'],
      ['P03', 'Budi Santoso, M.Pd.', '19790510 200501 1 009', 'SMA Negeri 1 Merdeka Belajar', 'Matematika', '081255556666'],
      ['P04', 'Siti Rahmawati, S.Pd.', '19861112 201101 2 012', 'SMA Negeri 1 Merdeka Belajar', 'Biologi', '081277778888'],
      ['P05', 'Nuraini, S.Pd., M.M.', '19750718 200012 2 003', 'SMA Negeri 1 Merdeka Belajar', 'Ekonomi', '081299990000'],
      ['P06', 'Deni Setiawan, S.Pd.', '19900214 201903 1 008', 'SMA Negeri 2 Mitra', 'Bahasa Inggris', '081322221111'],
      ['P07', 'Farida Aryani, S.Pd.', '19870625 201502 2 007', 'SMA Negeri 2 Mitra', 'PPKn', '081344443333'],
      ['P08', 'Wawan Hermawan, M.Pd.', '19781203 200604 1 011', 'SMA Swasta Teladan', 'Kimia', '081366665555']
    ];

    for (const p of invigilators) {
      insertPengawas.run(...p);
    }
  }

  // Seed Penguji Praktik
  const pengujiCount = db.prepare('SELECT COUNT(*) as count FROM penguji_praktik').get().count;
  if (pengujiCount === 0) {
    const insertPenguji = db.prepare(`
      INSERT INTO penguji_praktik (nama, nip, mata_pelajaran, asal_instansi, no_hp, keterangan)
      VALUES (?, ?, ?, ?, ?, ?)
    `);

    const examiners = [
      ['Ir. Agus Pratama, M.Kom.', '19850110 201001 1 015', 'Praktik Informatika / Komputer', 'SMA Negeri 1 Merdeka Belajar', '081788990011', 'Penguji Utama Bidang Pemrograman & Web'],
      ['Rizal Mahfudz, S.Kom.', '-', 'Praktik Informatika / Komputer', 'PT Inovasi Solusi Digital', '081822334455', 'Penguji Eksternal / Mitra Industri'],
      ['Dewi Lestari, S.Si.', '19840920 200801 2 015', 'Praktik Fisika & Kimia Terpadu', 'SMA Negeri 1 Merdeka Belajar', '081233334444', 'Penguji Praktikum Sains Terapan'],
      ['Deni Setiawan, S.Pd.', '19900214 201903 1 008', 'Praktik Bahasa Inggris (Speaking)', 'SMA Negeri 1 Merdeka Belajar', '081322221111', 'Penguji Kemampuan Berbicara & Dialog'],
      ['Bambang Triyono, S.Pd.', '19830722 200902 1 008', 'Praktik Pendidikan Jasmani dan Olahraga', 'SMA Negeri 1 Merdeka Belajar', '081900112233', 'Penguji Uji Fisik & Ketangkasan'],
      ['Anisa Putri, S.Sn.', '19920811 202012 2 018', 'Praktik Seni Budaya (Kriya & Seni Rupa)', 'SMA Negeri 1 Merdeka Belajar', '081266778899', 'Penguji Karya Estetika & Kerajinan Tangan']
    ];

    for (const e of examiners) {
      insertPenguji.run(...e);
    }
  }

  // Seed Program Kerja Panitia
  const prokerCount = db.prepare('SELECT COUNT(*) as count FROM program_kerja').get().count;
  if (prokerCount === 0) {
    const insertProker = db.prepare(`
      INSERT INTO program_kerja (ujian_id, tahap, kegiatan, waktu_pelaksanaan, penanggung_jawab, anggaran, status, keterangan, urutan)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    const prokerData = [
      // Tahap Persiapan
      [activeUjianId, 'Persiapan', 'Rapat Pembentukan Panitia dan Penyusunan SK Kepanitiaan', '10 Mei 2025', 'Ketua Panitia', 250000, 'Selesai', 'Dihadiri seluruh dewan guru dan staf TU', 1],
      [activeUjianId, 'Persiapan', 'Penyusunan Kisi-kisi dan Penulisan Naskah Soal Ujian Tulis & Praktik', '12 - 20 Mei 2025', 'Seksi Naskah & Soal', 750000, 'Selesai', 'Sesuai dengan kurikulum merdeka dan K-13', 2],
      [activeUjianId, 'Persiapan', 'Verifikasi Data Peserta, Penetapan Ruangan, dan Cetak Kartu Peserta', '21 - 25 Mei 2025', 'Sekretaris', 500000, 'Selesai', 'Sinkronisasi data dapodik dan kelas', 3],
      [activeUjianId, 'Persiapan', 'Penataan Ruangan Ujian, Penempelan Nomor Meja, dan Denah Duduk', '28 - 30 Mei 2025', 'Seksi Ruangan', 300000, 'Selesai', 'Ruang disterilkan dan disusun rapi 20 kursi per ruang', 4],
      // Tahap Pelaksanaan
      [activeUjianId, 'Pelaksanaan', 'Pelaksanaan Ujian Praktik Terpadu', '26 - 30 Mei 2025', 'Koordinator Praktik', 1200000, 'Selesai', 'Berjalan tertib di laboratorium dan aula', 5],
      [activeUjianId, 'Pelaksanaan', 'Pelaksanaan Ujian Tulis Utama Sesi 1 & 2', '02 - 10 Juni 2025', 'Ketua Panitia & Seluruh Panitia', 4500000, 'Sedang', 'Ujian utama 8 hari kerja berlangsung', 6],
      [activeUjianId, 'Pelaksanaan', 'Pelaksanaan Ujian Susulan bagi Siswa Berhalangan Sah', '12 - 13 Juni 2025', 'Sekretaris', 400000, 'Belum', 'Bagi siswa sakit dengan surat keterangan dokter', 7],
      // Tahap Evaluasi & Pelaporan
      [activeUjianId, 'Evaluasi & Pelaporan', 'Koreksi Hasil Ujian dan Pengolahan Nilai Akhir', '14 - 18 Juni 2025', 'Seksi Naskah & Tim IT', 600000, 'Belum', 'Pengumpulan nilai murni ke wali kelas', 8],
      [activeUjianId, 'Evaluasi & Pelaporan', 'Rapat Evaluasi Keterlaksanaan Ujian dan Pleno Kelulusan', '19 Juni 2025', 'Kepala Sekolah & Panitia', 500000, 'Belum', 'Evaluasi seluruh kendala teknis dan hasil', 9],
      [activeUjianId, 'Evaluasi & Pelaporan', 'Penyusunan dan Pengesahan Laporan Pertanggungjawaban (LPJ)', '20 - 23 Juni 2025', 'Ketua & Sekretaris Panitia', 350000, 'Belum', 'Diserahkan kepada Kepala Sekolah dan Dinas', 10]
    ];

    for (const p of prokerData) {
      insertProker.run(...p);
    }
  }

  // Seed Jadwal Ujian Tulis
  const jadwalTulisCount = db.prepare('SELECT COUNT(*) as count FROM jadwal_tulis').get().count;
  if (jadwalTulisCount === 0) {
    const insertJadwalTulis = db.prepare(`
      INSERT INTO jadwal_tulis (ujian_id, hari_tanggal, sesi, jam_mulai, jam_selesai, mapel_id, nama_mapel, kelas_tingkat, ruang_id, nama_ruang, pengawas_1, pengawas_2, keterangan)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    const schedules = [
      [activeUjianId, 'Senin, 02 Juni 2025', 1, '07:30', '09:00', 1, 'Pendidikan Agama Islam dan Budi Pekerti', 'Semua Kelas', 1, 'Ruang Ujian 01 (Kelas X-1)', 'Drs. Hendra Gunawan', 'Dewi Lestari, S.Si.', 'Membawa alat tulis lengkap'],
      [activeUjianId, 'Senin, 02 Juni 2025', 1, '09:30', '11:00', 2, 'Pendidikan Pancasila dan Kewarganegaraan', 'Semua Kelas', 1, 'Ruang Ujian 01 (Kelas X-1)', 'Budi Santoso, M.Pd.', 'Siti Rahmawati, S.Pd.', 'Soal pilihan ganda & uraian'],
      [activeUjianId, 'Selasa, 03 Juni 2025', 1, '07:30', '09:00', 3, 'Bahasa Indonesia', 'Semua Kelas', 1, 'Ruang Ujian 01 (Kelas X-1)', 'Nuraini, S.Pd., M.M.', 'Deni Setiawan, S.Pd.', 'Membawa kartu peserta'],
      [activeUjianId, 'Selasa, 03 Juni 2025', 1, '09:30', '11:30', 4, 'Matematika Umum', 'Semua Kelas', 1, 'Ruang Ujian 01 (Kelas X-1)', 'Farida Aryani, S.Pd.', 'Wawan Hermawan, M.Pd.', 'Dilarang menggunakan kalkulator'],
      [activeUjianId, 'Rabu, 04 Juni 2025', 1, '07:30', '09:00', 5, 'Bahasa Inggris', 'Semua Kelas', 1, 'Ruang Ujian 01 (Kelas X-1)', 'Drs. Hendra Gunawan', 'Farida Aryani, S.Pd.', 'Listening dan Reading'],
      [activeUjianId, 'Kamis, 05 Juni 2025', 1, '07:30', '09:00', 6, 'Fisika', 'Kelas X, XI, XII MIPA', 1, 'Ruang Ujian 01 (Kelas X-1)', 'Budi Santoso, M.Pd.', 'Dewi Lestari, S.Si.', 'Peminatan MIPA']
    ];

    for (const j of schedules) {
      insertJadwalTulis.run(...j);
    }
  }

  // Seed Jadwal Ujian Praktik
  const jadwalPraktikCount = db.prepare('SELECT COUNT(*) as count FROM jadwal_praktik').get().count;
  if (jadwalPraktikCount === 0) {
    const insertJadwalPraktik = db.prepare(`
      INSERT INTO jadwal_praktik (ujian_id, hari_tanggal, waktu, mapel_id, nama_mapel, kelas_tingkat, tempat, penguji_1, penguji_2, kelompok_peserta)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    const pSchedules = [
      [activeUjianId, 'Senin, 26 Mei 2025', '08:00 - 11:30', 11, 'Praktik Informatika / Komputer', 'Kelas X MIPA 1 & 2', 'Laboratorium Komputer CBT', 'Ir. Agus Pratama, M.Kom.', 'Rizal Mahfudz, S.Kom.', 'Kelompok A & B (Sesi Pagi)'],
      [activeUjianId, 'Selasa, 27 Mei 2025', '08:00 - 11:30', 12, 'Praktik Fisika & Kimia Terpadu', 'Kelas XI MIPA 1', 'Laboratorium IPA Terpadu', 'Dewi Lestari, S.Si.', 'Wawan Hermawan, M.Pd.', 'Seluruh Siswa XI MIPA 1'],
      [activeUjianId, 'Rabu, 28 Mei 2025', '08:00 - 11:00', 13, 'Praktik Bahasa Inggris (Speaking & Conversation)', 'Kelas X & XI', 'Ruang Audio Visual / Bahasa', 'Deni Setiawan, S.Pd.', 'Siti Nurjanah, S.Pd.', 'Uji Dialog & Monolog'],
      [activeUjianId, 'Kamis, 29 Mei 2025', '07:30 - 10:30', 15, 'Praktik Pendidikan Jasmani dan Olahraga', 'Semua Kelas', 'Lapangan Olahraga Utama', 'Bambang Triyono, S.Pd.', 'H. Mulyadi, M.Pd.', 'Uji Kebugaran Jasmani'],
      [activeUjianId, 'Jumat, 30 Mei 2025', '08:00 - 10:30', 14, 'Praktik Seni Budaya (Kriya & Seni Rupa)', 'Kelas XII MIPA & IPS', 'Ruang Kesenian', 'Anisa Putri, S.Sn.', 'Siti Rahmawati, S.Pd.', 'Pameran Karya & Penilaian Langsung']
    ];

    for (const p of pSchedules) {
      insertJadwalPraktik.run(...p);
    }
  }

  // Seed Persuratan
  const suratCount = db.prepare('SELECT COUNT(*) as count FROM surat').get().count;
  if (suratCount === 0) {
    const insertSurat = db.prepare(`
      INSERT INTO surat (ujian_id, jenis_surat, nomor_surat, perihal, lampiran, tanggal_surat, tempat_surat, dasar_hukum, isi_surat, tujuan_penerima, daftar_petugas_json)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    const letters = [
      // 1. Undangan Rapat
      [
        activeUjianId,
        'undangan_rapat',
        '421.3/089/SMAN1-MB/V/2025',
        'Undangan Rapat Koordinasi dan Persiapan Ujian Sekolah Tahun Ajaran 2024/2025',
        '1 (satu) Berkas Susunan Acara',
        '2025-05-10',
        'Bandung',
        'Program Kerja Sekolah Tahun Pelajaran 2024/2025 dan Kalender Pendidikan Dinas Pendidikan Provinsi Jawa Barat.',
        'Sehubungan dengan akan dilaksanakannya kegiatan Penilaian Akhir Tahun (PAT) dan Asesmen Sumatif Akhir Jenjang, kami mengundang Bapak/Ibu Dewan Guru serta Panitia Ujian untuk menghadiri rapat koordinasi teknis yang akan dilaksanakan pada:\n\nHari/Tanggal : Rabu, 14 Mei 2025\nWaktu : Pukul 09.00 WIB s.d. Selesai\nTempat : Ruang Rapat Guru SMAN 1 Merdeka Belajar\nAgenda : 1. Pembagian Tugas Kepanitiaan\n         2. Penetapan Jadwal & Kisi-Kisi Soal\n         3. Teknis Pengawasan dan Penilaian\n\nMengingat pentingnya agenda rapat ini, kami memohon kehadiran Bapak/Ibu tepat pada waktunya.',
        'Bapak/Ibu Dewan Guru & Staf Tata Usaha',
        JSON.stringify([
          { nama: 'H. Mulyadi, M.Pd.', nip: '19760815 200212 1 003', jabatan: 'Ketua Panitia' },
          { nama: 'Siti Nurjanah, S.Pd.', nip: '19820320 200801 2 011', jabatan: 'Sekretaris' },
          { nama: 'Dra. Hj. Euis Karlina', nip: '19691204 199403 2 004', jabatan: 'Bendahara' }
        ])
      ],
      // 2. Surat Tugas Pembuat Soal & Kisi-Kisi Ujian Tulis
      [
        activeUjianId,
        'tugas_pembuat_soal_tulis',
        '800/102/SMAN1-MB/V/2025',
        'Surat Tugas Penyusunan Kisi-kisi dan Penulisan Naskah Soal Ujian Tulis',
        '-',
        '2025-05-12',
        'Bandung',
        '1. Permendikbudristek No. 21 Tahun 2022 tentang Standar Penilaian Pendidikan;\n2. Keputusan Kepala SMA Negeri 1 Merdeka Belajar tentang Pembentukan Panitia Ujian Sekolah TP 2024/2025.',
        'Kepala SMA Negeri 1 Merdeka Belajar menugaskan kepada Guru Mata Pelajaran yang tercantum namanya dalam lampiran surat ini untuk melaksanakan tugas sebagai Penyusun Kisi-kisi, Naskah Soal, dan Kunci Jawaban Ujian Tulis TP 2024/2025 dengan penuh tanggung jawab serta menjaga kerahasiaan dokumen naskah.',
        'Guru Pengampu Mata Pelajaran Terlampir',
        JSON.stringify([
          { nama: 'Siti Rahmawati, S.Pd.', nip: '19861112 201101 2 012', mapel: 'Biologi', kelas: 'X, XI, XII MIPA' },
          { nama: 'Budi Santoso, M.Pd.', nip: '19790510 200501 1 009', mapel: 'Matematika Umum', kelas: 'Semua Tingkat' },
          { nama: 'Dewi Lestari, S.Si.', nip: '19840920 200801 2 015', mapel: 'Fisika', kelas: 'X, XI, XII MIPA' },
          { nama: 'Nuraini, S.Pd., M.M.', nip: '19750718 200012 2 003', mapel: 'Ekonomi', kelas: 'X, XI, XII IPS' },
          { nama: 'Deni Setiawan, S.Pd.', 'nip': '19900214 201903 1 008', mapel: 'Bahasa Inggris', kelas: 'Semua Tingkat' }
        ])
      ],
      // 3. Surat Tugas Pembuat Soal & Kisi-Kisi Ujian Praktik
      [
        activeUjianId,
        'tugas_pembuat_soal_praktik',
        '800/103/SMAN1-MB/V/2025',
        'Surat Tugas Penyusunan Kisi-kisi dan Instrumen Ujian Praktik',
        '-',
        '2025-05-12',
        'Bandung',
        '1. Kurikulum Operasional Satuan Pendidikan (KOSP) SMAN 1 Merdeka Belajar;\n2. Kalender Pendidikan Dinas Pendidikan Provinsi Jawa Barat.',
        'Kepala Sekolah memberikan penugasan resmi kepada guru mata pelajaran praktik yang namanya tertera pada daftar lampiran untuk menyusun instrumen penilaian unjuk kerja, rubrik asesmen, dan panduan teknis Ujian Praktik TP 2024/2025.',
        'Guru Pengampu Mata Pelajaran Praktik',
        JSON.stringify([
          { nama: 'Ir. Agus Pratama, M.Kom.', nip: '19850110 201001 1 015', mapel: 'Informatika / Komputer', ruang: 'Lab Komputer' },
          { nama: 'Dewi Lestari, S.Si.', nip: '19840920 200801 2 015', mapel: 'Fisika & Kimia Terpadu', ruang: 'Lab IPA' },
          { nama: 'Bambang Triyono, S.Pd.', nip: '19830722 200902 1 008', mapel: 'PJOK', ruang: 'Lapangan Utama' },
          { nama: 'Anisa Putri, S.Sn.', nip: '19920811 202012 2 018', mapel: 'Seni Budaya', ruang: 'Ruang Kesenian' }
        ])
      ],
      // 4. Surat Tugas Pengawas Ruang
      [
        activeUjianId,
        'tugas_pengawas_ruang',
        '800/105/SMAN1-MB/V/2025',
        'Surat Perintah Tugas Pengawas Ruang Ujian Tulis',
        '1 (satu) Berkas Jadwal Pengawasan',
        '2025-05-25',
        'Bandung',
        'Keputusan Kepala Cabang Dinas Pendidikan Wilayah VII perihal Standar Operasional Prosedur Pengawasan Ujian Sekolah.',
        'Kepala Sekolah menugaskan nama-nama guru terlampir untuk bertindak sebagai Pengawas Ruang Ujian Tulis TP 2024/2025 dengan jadwal dan penempatan ruang tertera, serta wajib menegakkan tata tertib pengawas secara disiplin dan profesional.',
        'Bapak/Ibu Guru Pengawas Ruang Ujian',
        JSON.stringify([
          { nama: 'Drs. Hendra Gunawan', nip: '19680315 199303 1 005', ruang: 'Ruang 01', tugas: 'Pengawas 1' },
          { nama: 'Dewi Lestari, S.Si.', nip: '19840920 200801 2 015', ruang: 'Ruang 01', tugas: 'Pengawas 2' },
          { nama: 'Budi Santoso, M.Pd.', nip: '19790510 200501 1 009', ruang: 'Ruang 02', tugas: 'Pengawas 1' },
          { nama: 'Siti Rahmawati, S.Pd.', nip: '19861112 201101 2 012', ruang: 'Ruang 02', tugas: 'Pengawas 2' },
          { nama: 'Nuraini, S.Pd., M.M.', nip: '19750718 200012 2 003', ruang: 'Ruang 03', tugas: 'Pengawas 1' },
          { nama: 'Deni Setiawan, S.Pd.', nip: '19900214 201903 1 008', ruang: 'Ruang 03', tugas: 'Pengawas 2' }
        ])
      ],
      // 5. Surat Tugas Penguji Praktik
      [
        activeUjianId,
        'tugas_penguji_praktik',
        '800/106/SMAN1-MB/V/2025',
        'Surat Perintah Tugas Tim Penguji Ujian Praktik',
        '-',
        '2025-05-20',
        'Bandung',
        'Pedoman Operasional Penilaian Akhir Jenjang dan Ujian Praktik Kejuruan/Sains Tahun Ajaran 2024/2025.',
        'Kepala Sekolah menugaskan nama-nama berikut untuk menjadi Dewan Penguji Ujian Praktik, menguji kompetensi siswa, mendokumentasikan nilai unjuk kerja, serta menandatangani berita acara pengujian praktik.',
        'Tim Penguji Ujian Praktik',
        JSON.stringify([
          { nama: 'Ir. Agus Pratama, M.Kom.', nip: '19850110 201001 1 015', mapel: 'Praktik Informatika', peran: 'Penguji Internal' },
          { nama: 'Rizal Mahfudz, S.Kom.', nip: '-', mapel: 'Praktik Informatika', peran: 'Penguji Eksternal / DU/DI' },
          { nama: 'Dewi Lestari, S.Si.', nip: '19840920 200801 2 015', mapel: 'Praktik Fisika & Kimia', peran: 'Penguji Utama' },
          { nama: 'Deni Setiawan, S.Pd.', nip: '19900214 201903 1 008', mapel: 'Praktik Bahasa Inggris', peran: 'Penguji Utama' },
          { nama: 'Bambang Triyono, S.Pd.', nip: '19830722 200902 1 008', mapel: 'Praktik PJOK', peran: 'Penguji Utama' }
        ])
      ]
    ];

    for (const l of letters) {
      insertSurat.run(...l);
    }
  }

  // Seed Tata Tertib
  const tatibCount = db.prepare('SELECT COUNT(*) as count FROM tata_tertib').get().count;
  if (tatibCount === 0) {
    const insertTatib = db.prepare('INSERT INTO tata_tertib (kategori, judul, isi_poin_json) VALUES (?, ?, ?)');
    
    // Peserta
    insertTatib.run(
      'peserta',
      'TATA TERTIB PESERTA UJIAN',
      JSON.stringify([
        'Peserta ujian memasuki ruangan setelah tanda masuk dibunyikan, yakni 15 (lima belas) menit sebelum ujian dimulai.',
        'Peserta yang terlambat hadir hanya diperkenankan mengikuti ujian setelah mendapat izin dari Ketua Panitia, tanpa penambahan waktu ujian.',
        'Peserta dilarang membawa catatan, buku, perangkat komunikasi/HP, kalkulator, dan senjata tajam ke dalam ruang ujian kecuali alat tulis yang diizinkan.',
        'Peserta wajib membawa dan meletakkan Kartu Peserta Ujian di atas meja masing-masing selama ujian berlangsung.',
        'Peserta mengisi daftar hadir dengan menggunakan pulpen hitam/biru yang disediakan atau dibawa sendiri.',
        'Peserta yang memerlukan penjelasan naskah soal dapat bertanya kepada Pengawas Ruang dengan cara mengacungkan tangan terlebih dahulu.',
        'Selama ujian berlangsung, peserta dilarang: mencontek, bekerjasama, memberi atau menerima bantuan jawaban, menggantikan atau digantikan orang lain.',
        'Peserta yang telah selesai mengerjakan soal sebelum waktu habis diperbolehkan meninggalkan ruangan setelah menyerahkan lembar jawaban dan mendapat izin dari Pengawas.',
        'Peserta harus berhenti mengerjakan soal tepat pada saat bel tanda selesai berbunyi dan tetap tenang di tempat duduk masing-masing.'
      ])
    );

    // Pengawas
    insertTatib.run(
      'pengawas',
      'TATA TERTIB PENGAWAS RUANG UJIAN',
      JSON.stringify([
        'Pengawas ruang wajib hadir di ruang sekretariat panitia ujian 30 (tiga puluh) menit sebelum waktu ujian dimulai.',
        'Pengawas ruang menerima pengarahan dan menerima dokumen administrasi ujian (amplop soal, lembar jawaban, daftar hadir, berita acara, denah duduk).',
        'Pengawas ruang memasuki ruang ujian 15 menit sebelum waktu mulai untuk memeriksa kesiapan ruang dan kelengkapan peserta.',
        'Pengawas memeriksa dan mencocokkan identitas peserta pada kartu ujian dengan daftar hadir resmi.',
        'Pengawas membacakan tata tertib peserta ujian dan memimpin doa bersama sebelum ujian dimulai.',
        'Pengawas membuka sampul naskah soal di depan peserta ujian dan memperlihatkan bahwa sampul dalam keadaan tersegel baik.',
        'Selama ujian berlangsung, pengawas ruang dilarang: merokok, membaca koran/buku di luar naskah, bermain gawai/HP, tertidur, atau memberikan petunjuk jawaban kepada peserta.',
        'Pengawas mengisi dan menandatangani Berita Acara Pelaksanaan Ujian dan Daftar Hadir Pengawas serta Peserta.',
        'Setelah ujian selesai, pengawas mengumpulkan, menghitung kecukupan lembar jawaban, memasukkannya ke dalam amplop pengembalian, dan menyerahkannya langsung ke sekretariat panitia.'
      ])
    );

    // Penguji Praktik
    insertTatib.run(
      'penguji',
      'TATA TERTIB PENGUJI PRAKTIK',
      JSON.stringify([
        'Penguji praktik wajib hadir 30 menit sebelum jadwal pengujian dimulai untuk memeriksa kelayakan alat, bahan, dan laboratorium tempat ujian.',
        'Penguji mengenakan pakaian dinas rapi / jas laboratorium / perlengkapan keselamatan kerja (K3) sesuai karakteristik mata pelajaran praktik.',
        'Penguji memanggil peserta ujian sesuai urutan nomor daftar hadir atau kelompok yang telah ditentukan oleh panitia.',
        'Penguji memberikan arahan teknis singkat, petunjuk keselamatan kerja, dan batasan waktu pengerjaan modul praktik kepada peserta.',
        'Penguji melakukan observasi langsung secara objektif, adil, profesional, dan tidak diskriminatif terhadap setiap peserta ujian.',
        'Penguji mengisi lembar instrumen asesmen dan rubrik penilaian secara realtime saat peserta sedang melakukan demonstrasi / unjuk kerja.',
        'Penguji berhak menghentikan tindakan peserta jika dinilai membahayakan keselamatan diri sendiri, orang lain, atau merusak alat laboratorium.',
        'Penguji menandatangani Berita Acara Pengujian Praktik dan merekapitulasi seluruh nilai sebelum diserahkan ke bagian evaluasi panitia.'
      ])
    );
  }

  // Seed Berita Acara
  const baCount = db.prepare('SELECT COUNT(*) as count FROM berita_acara').get().count;
  if (baCount === 0) {
    const insertBA = db.prepare(`
      INSERT INTO berita_acara (
        ujian_id, tanggal, ruang_id, nama_ruang, sesi, mapel_id, nama_mapel,
        jumlah_seharusnya, jumlah_hadir, jumlah_tidak_hadir, nomor_tidak_hadir,
        catatan_kejadian, pengawas_1_nama, pengawas_1_nip, pengawas_2_nama, pengawas_2_nip
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    insertBA.run(
      activeUjianId,
      '2025-06-02',
      1,
      'Ruang Ujian 01 (Kelas X-1)',
      1,
      1,
      'Pendidikan Agama Islam dan Budi Pekerti',
      20,
      20,
      0,
      '-',
      'Pelaksanaan ujian berlangsung tertib, aman, dan lancar. Tidak ditemukan pelanggaran maupun kecurangan.',
      'Drs. Hendra Gunawan',
      '19680315 199303 1 005',
      'Dewi Lestari, S.Si.',
      '19840920 200801 2 015'
    );
  }

  // Seed LPJ
  const lpjCount = db.prepare('SELECT COUNT(*) as count FROM lpj').get().count;
  if (lpjCount === 0) {
    const insertLPJ = db.prepare(`
      INSERT INTO lpj (
        ujian_id, nomor_sk, tentang_sk, latar_belakang, dasar_kegiatan, tujuan_kegiatan,
        hasil_evaluasi, hambatan_solusi, kesimpulan, saran, rincian_keuangan_json
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `);

    insertLPJ.run(
      activeUjianId,
      '421.3/SK-042/SMAN1-MB/2025',
      'Pembentukan Panitia Penilaian Akhir Tahun (PAT) dan Asesmen Sumatif TP 2024/2025',
      'Penilaian Akhir Tahun (PAT) merupakan salah satu tahapan penting dalam siklus pembelajaran kurikulum nasional untuk mengukur capaian kompetensi peserta didik selama satu semester. Untuk memastikan akuntabilitas, transparansi, dan standarisasi proses pengujian, panitia menyusun laporan pertanggungjawaban ini sebagai wujud evaluasi menyeluruh.',
      '1. Undang-Undang No. 20 Tahun 2003 tentang Sistem Pendidikan Nasional;\n2. Peraturan Menteri Pendidikan, Kebudayaan, Riset, dan Teknologi No. 21 Tahun 2022 tentang Standar Penilaian Pendidikan;\n3. Kalender Pendidikan Dinas Pendidikan Provinsi Jawa Barat TP 2024/2025;\n4. Surat Keputusan Kepala SMAN 1 Merdeka Belajar No. 421.3/SK-042/SMAN1-MB/2025.',
      '1. Mengukur tingkat pemahaman dan pencapaian kompetensi akademik siswa pada setiap mata pelajaran;\n2. Memberikan umpan balik (feedback) objektif bagi guru dan orang tua dalam proses pembinaan belajar;\n3. Sebagai salah satu kriteria penentu kenaikan kelas dan kelulusan siswa.',
      'Secara keseluruhan, pelaksanaan Ujian Tulis dan Ujian Praktik berjalan dengan lancar dan kondusif. Tingkat kehadiran peserta mencapai 99,2% dengan 1 orang berhalangan karena rawat inap dan telah mengikuti ujian susulan. Seluruh naskah soal, kunci jawaban, dan rubrik penilaian telah diverifikasi sesuai kaidah ilmiah.',
      'Hambatan: Terdapat pemadaman listrik lokal selama 15 menit pada hari ketiga di Lab CBT.\nSolusi: Genset sekolah segera diaktifkan dan server UPS berfungsi dengan baik sehingga waktu ujian peserta tidak berkurang dan data tersimpan aman.',
      'Penyelenggaraan Ujian PAT TP 2024/2025 di SMA Negeri 1 Merdeka Belajar telah berhasil dilaksanakan dengan prinsip kejujuran, akuntabilitas, dan efisiensi anggaran sesuai petunjuk teknis yang berlaku.',
      '1. Disarankan untuk menambah kapasitas cadangan daya listrik otomatis (Automatic Transfer Switch - ATS) untuk Lab Komputer CBT;\n2. Pemeliharaan berkala AC dan ventilasi ruang ujian guna meningkatkan kenyamanan peserta didik.',
      JSON.stringify({
        pemasukan: [
          { sumber: 'Dana Bantuan Operasional Sekolah (BOS) Reguler', jumlah: 8500000 },
          { sumber: 'Dana Komite Sekolah / Partisipasi Masyarakat', jumlah: 3500000 }
        ],
        pengeluaran: [
          { uraian: 'Penggandaan Naskah Soal, LJK, dan Administrasi', jumlah: 2450000 },
          { uraian: 'Konsumsi Pengawas Ruang, Panitia, dan Teknisi (8 Hari)', jumlah: 3800000 },
          { uraian: 'Honorarium Pengawas Ruang Ujian (8 Hari)', jumlah: 2800000 },
          { uraian: 'Honorarium Panitia dan Penguji Praktik', jumlah: 2100000 },
          { uraian: 'Pengadaan ATK, Spanduk, dan Nomor Meja/Ruangan', jumlah: 850000 }
        ]
      })
    );
  }
}

// Initialize immediately
initDatabase();

module.exports = {
  db,
  dbPath,
  uploadsDir,
  initDatabase
};
