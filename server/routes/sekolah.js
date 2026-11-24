const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const { db, uploadsDir } = require('../database/db');
const { authMiddleware } = require('../middleware/auth');

const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    cb(null, uploadsDir);
  },
  filename: function (req, file, cb) {
    const ext = path.extname(file.originalname);
    cb(null, `logo_${Date.now()}${ext}`);
  }
});

const upload = multer({
  storage,
  limits: { fileSize: 5 * 1024 * 1024 },
  fileFilter: (req, file, cb) => {
    if (file.mimetype.startsWith('image/')) {
      cb(null, true);
    } else {
      cb(new Error('Hanya file gambar yang diizinkan!'));
    }
  }
});

// GET /api/sekolah
router.get('/', (req, res) => {
  let sekolah = db.prepare('SELECT * FROM identitas_sekolah WHERE id = 1').get();
  if (!sekolah) {
    return res.status(404).json({ success: false, message: 'Data identitas sekolah belum ada.' });
  }
  return res.json({ success: true, data: sekolah });
});

// PUT /api/sekolah
router.put('/', authMiddleware, (req, res) => {
  const fields = [
    'nama_sekolah', 'npsn', 'nss', 'jenjang', 'status_sekolah', 'alamat',
    'desa_kelurahan', 'kecamatan', 'kabupaten_kota', 'provinsi', 'kode_pos',
    'telepon', 'email', 'website', 'kepala_sekolah', 'nip_kepala_sekolah',
    'tahun_ajaran', 'semester', 'titimangsa_kota', 'logo_url', 'ttd_stempel_url',
    'kop_baris1', 'kop_baris2', 'kop_baris3'
  ];

  const updates = [];
  const values = [];

  for (const f of fields) {
    if (req.body[f] !== undefined) {
      updates.push(`${f} = ?`);
      values.push(req.body[f]);
    }
  }

  if (updates.length === 0) {
    return res.status(400).json({ success: false, message: 'Tidak ada data yang diperbarui.' });
  }

  updates.push(`updated_at = CURRENT_TIMESTAMP`);
  values.push(1);

  const query = `UPDATE identitas_sekolah SET ${updates.join(', ')} WHERE id = ?`;
  db.prepare(query).run(...values);

  const updated = db.prepare('SELECT * FROM identitas_sekolah WHERE id = 1').get();
  return res.json({ success: true, message: 'Identitas sekolah berhasil diperbarui.', data: updated });
});

// POST /api/sekolah/upload-logo
router.post('/upload-logo', authMiddleware, upload.single('logo'), (req, res) => {
  if (!req.file) {
    return res.status(400).json({ success: false, message: 'File gambar wajib diunggah.' });
  }

  const logoUrl = `/uploads/${req.file.filename}`;
  db.prepare('UPDATE identitas_sekolah SET logo_url = ?, updated_at = CURRENT_TIMESTAMP WHERE id = 1').run(logoUrl);

  return res.json({
    success: true,
    message: 'Logo sekolah berhasil diunggah.',
    logo_url: logoUrl
  });
});

module.exports = router;
