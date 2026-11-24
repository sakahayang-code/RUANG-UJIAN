const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const { db, dbPath, initDatabase } = require('../database/db');
const { authMiddleware } = require('../middleware/auth');

const tempUpload = multer({
  dest: path.join(__dirname, '../data/temp'),
  limits: { fileSize: 50 * 1024 * 1024 }
});

// GET /api/sistem/info
router.get('/info', authMiddleware, (req, res) => {
  let fileSize = 0;
  let lastModified = null;
  if (fs.existsSync(dbPath)) {
    const stats = fs.statSync(dbPath);
    fileSize = stats.size;
    lastModified = stats.mtime;
  }

  const counts = {
    peserta: db.prepare('SELECT COUNT(*) as c FROM peserta').get().c,
    kelas: db.prepare('SELECT COUNT(*) as c FROM kelas').get().c,
    mapel: db.prepare('SELECT COUNT(*) as c FROM mata_pelajaran').get().c,
    ruangan: db.prepare('SELECT COUNT(*) as c FROM ruangan').get().c,
    panitia: db.prepare('SELECT COUNT(*) as c FROM panitia').get().c,
    pengawas: db.prepare('SELECT COUNT(*) as c FROM pengawas').get().c,
    penguji: db.prepare('SELECT COUNT(*) as c FROM penguji_praktik').get().c,
    proker: db.prepare('SELECT COUNT(*) as c FROM program_kerja').get().c,
    jadwalTulis: db.prepare('SELECT COUNT(*) as c FROM jadwal_tulis').get().c,
    jadwalPraktik: db.prepare('SELECT COUNT(*) as c FROM jadwal_praktik').get().c,
    surat: db.prepare('SELECT COUNT(*) as c FROM surat').get().c
  };

  return res.json({
    success: true,
    fileSize: (fileSize / 1024).toFixed(2) + ' KB',
    fileSizeBytes: fileSize,
    lastModified,
    counts
  });
});

// GET /api/sistem/backup/db - Download SQLite database directly
router.get('/backup/db', (req, res) => {
  if (!fs.existsSync(dbPath)) {
    return res.status(404).send('Database file tidak ditemukan.');
  }

  // Check checkpoint to ensure WAL is flushed
  try {
    db.pragma('wal_checkpoint(TRUNCATE)');
  } catch (err) {
    console.error('WAL checkpoint error:', err);
  }

  const filename = `ruang_ujian_backup_${new Date().toISOString().replace(/[:.]/g, '-')}.sqlite`;
  res.setHeader('Content-Disposition', `attachment; filename="${filename}"`);
  res.setHeader('Content-Type', 'application/vnd.sqlite3');
  return res.download(dbPath, filename);
});

// GET /api/sistem/backup/json - Export all tables as JSON
router.get('/backup/json', (req, res) => {
  const tables = [
    'users', 'identitas_sekolah', 'ujian', 'kelas', 'mata_pelajaran',
    'ruangan', 'peserta', 'panitia', 'pengawas', 'penguji_praktik',
    'program_kerja', 'jadwal_tulis', 'jadwal_praktik', 'surat',
    'tata_tertib', 'berita_acara', 'lpj'
  ];

  const backupData = {
    appName: 'Ruang Ujian',
    exportedAt: new Date().toISOString(),
    version: '1.0.0',
    data: {}
  };

  for (const table of tables) {
    try {
      backupData.data[table] = db.prepare(`SELECT * FROM ${table}`).all();
    } catch (e) {
      backupData.data[table] = [];
    }
  }

  const filename = `ruang_ujian_export_${new Date().toISOString().slice(0, 10)}.json`;
  res.setHeader('Content-Disposition', `attachment; filename="${filename}"`);
  res.setHeader('Content-Type', 'application/json');
  return res.json(backupData);
});

// POST /api/sistem/restore/db - Upload & replace SQLite DB
router.post('/restore/db', authMiddleware, tempUpload.single('database_file'), (req, res) => {
  if (!req.file) {
    return res.status(400).json({ success: false, message: 'File database SQLite wajib diunggah.' });
  }

  const uploadedPath = req.file.path;
  const backupBackupPath = `${dbPath}.bak_${Date.now()}`;

  try {
    // Verify file is a valid sqlite database by opening with better-sqlite3
    const testDb = new (require('better-sqlite3'))(uploadedPath);
    testDb.prepare("SELECT name FROM sqlite_master WHERE type='table'").all();
    testDb.close();

    // Backup current db
    if (fs.existsSync(dbPath)) {
      fs.copyFileSync(dbPath, backupBackupPath);
    }

    // Replace
    fs.copyFileSync(uploadedPath, dbPath);
    fs.unlinkSync(uploadedPath);

    return res.json({
      success: true,
      message: 'Database berhasil dipulihkan! Silakan muat ulang halaman untuk memuat data terbaru.'
    });
  } catch (err) {
    if (fs.existsSync(uploadedPath)) fs.unlinkSync(uploadedPath);
    return res.status(400).json({
      success: false,
      message: 'Gagal memulihkan database. File yang diunggah bukan database SQLite valid: ' + err.message
    });
  }
});

// POST /api/sistem/restore/json - Restore from JSON backup
router.post('/restore/json', authMiddleware, (req, res) => {
  const { data } = req.body;
  if (!data || typeof data !== 'object') {
    return res.status(400).json({ success: false, message: 'Format data JSON tidak valid.' });
  }

  const tables = [
    'identitas_sekolah', 'ujian', 'kelas', 'mata_pelajaran',
    'ruangan', 'peserta', 'panitia', 'pengawas', 'penguji_praktik',
    'program_kerja', 'jadwal_tulis', 'jadwal_praktik', 'surat',
    'tata_tertib', 'berita_acara', 'lpj'
  ];

  try {
    const restoreTx = db.transaction(() => {
      for (const table of tables) {
        if (Array.isArray(data[table]) && data[table].length > 0) {
          db.prepare(`DELETE FROM ${table}`).run();
          const sampleRow = data[table][0];
          const cols = Object.keys(sampleRow);
          const placeholders = cols.map(() => '?').join(', ');
          const insertStmt = db.prepare(`INSERT INTO ${table} (${cols.join(', ')}) VALUES (${placeholders})`);

          for (const row of data[table]) {
            const vals = cols.map(c => row[c]);
            insertStmt.run(...vals);
          }
        }
      }
    });

    restoreTx();
    return res.json({ success: true, message: 'Seluruh data berhasil dipulihkan dari cadangan JSON.' });
  } catch (err) {
    return res.status(500).json({ success: false, message: 'Gagal memulihkan data: ' + err.message });
  }
});

// POST /api/sistem/reset - Reset to default demo data
router.post('/reset', authMiddleware, (req, res) => {
  try {
    const tables = [
      'peserta', 'panitia', 'pengawas', 'penguji_praktik',
      'jadwal_tulis', 'jadwal_praktik', 'program_kerja',
      'berita_acara', 'lpj', 'surat', 'tata_tertib',
      'ruangan', 'mata_pelajaran', 'kelas', 'ujian', 'identitas_sekolah'
    ];

    for (const t of tables) {
      db.prepare(`DELETE FROM ${t}`).run();
    }

    initDatabase();
    return res.json({ success: true, message: 'Sistem berhasil direset ke data awal demo sekolah.' });
  } catch (err) {
    return res.status(500).json({ success: false, message: 'Gagal mereset sistem: ' + err.message });
  }
});

module.exports = router;
