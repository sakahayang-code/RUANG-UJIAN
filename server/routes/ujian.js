const express = require('express');
const router = express.Router();
const { db } = require('../database/db');
const { authMiddleware } = require('../middleware/auth');

// GET /api/ujian - List all ujian
router.get('/', (req, res) => {
  const list = db.prepare('SELECT * FROM ujian ORDER BY id DESC').all();
  return res.json({ success: true, data: list });
});

// GET /api/ujian/active - Get currently active ujian
router.get('/active', (req, res) => {
  let active = db.prepare('SELECT * FROM ujian WHERE is_active = 1 LIMIT 1').get();
  if (!active) {
    active = db.prepare('SELECT * FROM ujian ORDER BY id DESC LIMIT 1').get();
  }
  return res.json({ success: true, data: active || null });
});

// POST /api/ujian - Create new ujian
router.post('/', authMiddleware, (req, res) => {
  const { nama_ujian, tahun_ajaran, semester, tanggal_mulai, tanggal_selesai, keterangan, is_active } = req.body;
  if (!nama_ujian || !tahun_ajaran || !semester) {
    return res.status(400).json({ success: false, message: 'Nama ujian, tahun ajaran, dan semester wajib diisi.' });
  }

  if (is_active) {
    db.prepare('UPDATE ujian SET is_active = 0').run();
  }

  const result = db.prepare(`
    INSERT INTO ujian (nama_ujian, tahun_ajaran, semester, tanggal_mulai, tanggal_selesai, is_active, keterangan)
    VALUES (?, ?, ?, ?, ?, ?, ?)
  `).run(
    nama_ujian,
    tahun_ajaran,
    semester,
    tanggal_mulai || null,
    tanggal_selesai || null,
    is_active ? 1 : 0,
    keterangan || null
  );

  const created = db.prepare('SELECT * FROM ujian WHERE id = ?').get(result.lastInsertRowid);
  return res.status(201).json({ success: true, message: 'Kegiatan ujian berhasil ditambahkan.', data: created });
});

// PUT /api/ujian/:id
router.put('/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  const { nama_ujian, tahun_ajaran, semester, tanggal_mulai, tanggal_selesai, keterangan, is_active } = req.body;

  if (is_active) {
    db.prepare('UPDATE ujian SET is_active = 0 WHERE id != ?').run(id);
  }

  db.prepare(`
    UPDATE ujian SET
      nama_ujian = ?,
      tahun_ajaran = ?,
      semester = ?,
      tanggal_mulai = ?,
      tanggal_selesai = ?,
      is_active = ?,
      keterangan = ?
    WHERE id = ?
  `).run(
    nama_ujian,
    tahun_ajaran,
    semester,
    tanggal_mulai || null,
    tanggal_selesai || null,
    is_active ? 1 : 0,
    keterangan || null,
    id
  );

  const updated = db.prepare('SELECT * FROM ujian WHERE id = ?').get(id);
  return res.json({ success: true, message: 'Data ujian berhasil diperbarui.', data: updated });
});

// POST /api/ujian/:id/activate
router.post('/:id/activate', authMiddleware, (req, res) => {
  const { id } = req.params;
  db.prepare('UPDATE ujian SET is_active = 0').run();
  db.prepare('UPDATE ujian SET is_active = 1 WHERE id = ?').run(id);
  const active = db.prepare('SELECT * FROM ujian WHERE id = ?').get(id);
  return res.json({ success: true, message: 'Ujian berhasil diaktifkan.', data: active });
});

// DELETE /api/ujian/:id
router.delete('/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  const count = db.prepare('SELECT COUNT(*) as count FROM ujian').get().count;
  if (count <= 1) {
    return res.status(400).json({ success: false, message: 'Tidak dapat menghapus kegiatan ujian terakhir.' });
  }

  db.prepare('DELETE FROM ujian WHERE id = ?').run(id);

  // If active was deleted, make the latest one active
  const hasActive = db.prepare('SELECT id FROM ujian WHERE is_active = 1').get();
  if (!hasActive) {
    const latest = db.prepare('SELECT id FROM ujian ORDER BY id DESC LIMIT 1').get();
    if (latest) {
      db.prepare('UPDATE ujian SET is_active = 1 WHERE id = ?').run(latest.id);
    }
  }

  return res.json({ success: true, message: 'Kegiatan ujian berhasil dihapus.' });
});

module.exports = router;
