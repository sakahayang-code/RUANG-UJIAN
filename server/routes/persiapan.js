const express = require('express');
const router = express.Router();
const { db } = require('../database/db');
const { authMiddleware } = require('../middleware/auth');

function getActiveUjianId() {
  const active = db.prepare('SELECT id FROM ujian WHERE is_active = 1 LIMIT 1').get();
  return active ? active.id : 1;
}

// ==========================================
// 1. PROGRAM KERJA PANITIA
// ==========================================
router.get('/proker', (req, res) => {
  const ujianId = getActiveUjianId();
  const list = db.prepare('SELECT * FROM program_kerja WHERE ujian_id = ? ORDER BY urutan ASC, id ASC').all(ujianId);
  return res.json({ success: true, data: list });
});

router.post('/proker', authMiddleware, (req, res) => {
  const ujianId = getActiveUjianId();
  const { tahap, kegiatan, waktu_pelaksanaan, penanggung_jawab, anggaran, status, keterangan, urutan } = req.body;
  if (!tahap || !kegiatan || !waktu_pelaksanaan || !penanggung_jawab) {
    return res.status(400).json({ success: false, message: 'Tahap, kegiatan, waktu, dan penanggung jawab wajib diisi.' });
  }
  const result = db.prepare(`
    INSERT INTO program_kerja (ujian_id, tahap, kegiatan, waktu_pelaksanaan, penanggung_jawab, anggaran, status, keterangan, urutan)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
  `).run(
    ujianId,
    tahap,
    kegiatan,
    waktu_pelaksanaan,
    penanggung_jawab,
    anggaran || 0,
    status || 'Belum',
    keterangan || null,
    urutan || 0
  );
  const created = db.prepare('SELECT * FROM program_kerja WHERE id = ?').get(result.lastInsertRowid);
  return res.status(201).json({ success: true, message: 'Program kerja berhasil ditambahkan.', data: created });
});

router.put('/proker/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  const { tahap, kegiatan, waktu_pelaksanaan, penanggung_jawab, anggaran, status, keterangan, urutan } = req.body;
  db.prepare(`
    UPDATE program_kerja SET
      tahap = ?,
      kegiatan = ?,
      waktu_pelaksanaan = ?,
      penanggung_jawab = ?,
      anggaran = ?,
      status = ?,
      keterangan = ?,
      urutan = ?
    WHERE id = ?
  `).run(
    tahap,
    kegiatan,
    waktu_pelaksanaan,
    penanggung_jawab,
    anggaran || 0,
    status || 'Belum',
    keterangan || null,
    urutan || 0,
    id
  );
  const updated = db.prepare('SELECT * FROM program_kerja WHERE id = ?').get(id);
  return res.json({ success: true, message: 'Program kerja berhasil diperbarui.', data: updated });
});

router.delete('/proker/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  db.prepare('DELETE FROM program_kerja WHERE id = ?').run(id);
  return res.json({ success: true, message: 'Program kerja berhasil dihapus.' });
});

// ==========================================
// 2. JADWAL UJIAN TULIS
// ==========================================
router.get('/jadwal-tulis', (req, res) => {
  const ujianId = getActiveUjianId();
  const list = db.prepare(`
    SELECT jt.*, m.kode_mapel, r.kode_ruang
    FROM jadwal_tulis jt
    LEFT JOIN mata_pelajaran m ON jt.mapel_id = m.id
    LEFT JOIN ruangan r ON jt.ruang_id = r.id
    WHERE jt.ujian_id = ?
    ORDER BY jt.hari_tanggal ASC, jt.jam_mulai ASC, jt.id ASC
  `).all(ujianId);
  return res.json({ success: true, data: list });
});

router.post('/jadwal-tulis', authMiddleware, (req, res) => {
  const ujianId = getActiveUjianId();
  const { hari_tanggal, sesi, jam_mulai, jam_selesai, mapel_id, kelas_tingkat, ruang_id, pengawas_1, pengawas_2, keterangan } = req.body;
  if (!hari_tanggal || !jam_mulai || !jam_selesai || !mapel_id) {
    return res.status(400).json({ success: false, message: 'Hari/tanggal, jam mulai, jam selesai, dan mapel wajib diisi.' });
  }

  const mapel = db.prepare('SELECT nama_mapel FROM mata_pelajaran WHERE id = ?').get(mapel_id);
  const nama_mapel = mapel ? mapel.nama_mapel : 'Mata Pelajaran';

  let nama_ruang = null;
  if (ruang_id) {
    const r = db.prepare('SELECT nama_ruang FROM ruangan WHERE id = ?').get(ruang_id);
    if (r) nama_ruang = r.nama_ruang;
  }

  const result = db.prepare(`
    INSERT INTO jadwal_tulis (
      ujian_id, hari_tanggal, sesi, jam_mulai, jam_selesai, mapel_id,
      nama_mapel, kelas_tingkat, ruang_id, nama_ruang, pengawas_1, pengawas_2, keterangan
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `).run(
    ujianId,
    hari_tanggal,
    sesi || 1,
    jam_mulai,
    jam_selesai,
    mapel_id,
    nama_mapel,
    kelas_tingkat || 'Semua Kelas',
    ruang_id || null,
    nama_ruang,
    pengawas_1 || null,
    pengawas_2 || null,
    keterangan || null
  );

  const created = db.prepare('SELECT * FROM jadwal_tulis WHERE id = ?').get(result.lastInsertRowid);
  return res.status(201).json({ success: true, message: 'Jadwal ujian tulis berhasil ditambahkan.', data: created });
});

router.put('/jadwal-tulis/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  const { hari_tanggal, sesi, jam_mulai, jam_selesai, mapel_id, kelas_tingkat, ruang_id, pengawas_1, pengawas_2, keterangan } = req.body;

  const mapel = db.prepare('SELECT nama_mapel FROM mata_pelajaran WHERE id = ?').get(mapel_id);
  const nama_mapel = mapel ? mapel.nama_mapel : 'Mata Pelajaran';

  let nama_ruang = null;
  if (ruang_id) {
    const r = db.prepare('SELECT nama_ruang FROM ruangan WHERE id = ?').get(ruang_id);
    if (r) nama_ruang = r.nama_ruang;
  }

  db.prepare(`
    UPDATE jadwal_tulis SET
      hari_tanggal = ?,
      sesi = ?,
      jam_mulai = ?,
      jam_selesai = ?,
      mapel_id = ?,
      nama_mapel = ?,
      kelas_tingkat = ?,
      ruang_id = ?,
      nama_ruang = ?,
      pengawas_1 = ?,
      pengawas_2 = ?,
      keterangan = ?
    WHERE id = ?
  `).run(
    hari_tanggal,
    sesi || 1,
    jam_mulai,
    jam_selesai,
    mapel_id,
    nama_mapel,
    kelas_tingkat || 'Semua Kelas',
    ruang_id || null,
    nama_ruang,
    pengawas_1 || null,
    pengawas_2 || null,
    keterangan || null,
    id
  );

  const updated = db.prepare('SELECT * FROM jadwal_tulis WHERE id = ?').get(id);
  return res.json({ success: true, message: 'Jadwal ujian tulis berhasil diperbarui.', data: updated });
});

router.delete('/jadwal-tulis/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  db.prepare('DELETE FROM jadwal_tulis WHERE id = ?').run(id);
  return res.json({ success: true, message: 'Jadwal ujian tulis berhasil dihapus.' });
});

// ==========================================
// 3. JADWAL UJIAN PRAKTIK
// ==========================================
router.get('/jadwal-praktik', (req, res) => {
  const ujianId = getActiveUjianId();
  const list = db.prepare(`
    SELECT jp.*, m.kode_mapel
    FROM jadwal_praktik jp
    LEFT JOIN mata_pelajaran m ON jp.mapel_id = m.id
    WHERE jp.ujian_id = ?
    ORDER BY jp.hari_tanggal ASC, jp.waktu ASC, jp.id ASC
  `).all(ujianId);
  return res.json({ success: true, data: list });
});

router.post('/jadwal-praktik', authMiddleware, (req, res) => {
  const ujianId = getActiveUjianId();
  const { hari_tanggal, waktu, mapel_id, kelas_tingkat, tempat, penguji_1, penguji_2, kelompok_peserta } = req.body;
  if (!hari_tanggal || !waktu || !mapel_id) {
    return res.status(400).json({ success: false, message: 'Hari/tanggal, waktu, dan mata pelajaran praktik wajib diisi.' });
  }

  const mapel = db.prepare('SELECT nama_mapel FROM mata_pelajaran WHERE id = ?').get(mapel_id);
  const nama_mapel = mapel ? mapel.nama_mapel : 'Mata Pelajaran Praktik';

  const result = db.prepare(`
    INSERT INTO jadwal_praktik (
      ujian_id, hari_tanggal, waktu, mapel_id, nama_mapel, kelas_tingkat, tempat, penguji_1, penguji_2, kelompok_peserta
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `).run(
    ujianId,
    hari_tanggal,
    waktu,
    mapel_id,
    nama_mapel,
    kelas_tingkat || 'Semua Kelas',
    tempat || null,
    penguji_1 || null,
    penguji_2 || null,
    kelompok_peserta || null
  );

  const created = db.prepare('SELECT * FROM jadwal_praktik WHERE id = ?').get(result.lastInsertRowid);
  return res.status(201).json({ success: true, message: 'Jadwal ujian praktik berhasil ditambahkan.', data: created });
});

router.put('/jadwal-praktik/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  const { hari_tanggal, waktu, mapel_id, kelas_tingkat, tempat, penguji_1, penguji_2, kelompok_peserta } = req.body;

  const mapel = db.prepare('SELECT nama_mapel FROM mata_pelajaran WHERE id = ?').get(mapel_id);
  const nama_mapel = mapel ? mapel.nama_mapel : 'Mata Pelajaran Praktik';

  db.prepare(`
    UPDATE jadwal_praktik SET
      hari_tanggal = ?,
      waktu = ?,
      mapel_id = ?,
      nama_mapel = ?,
      kelas_tingkat = ?,
      tempat = ?,
      penguji_1 = ?,
      penguji_2 = ?,
      kelompok_peserta = ?
    WHERE id = ?
  `).run(
    hari_tanggal,
    waktu,
    mapel_id,
    nama_mapel,
    kelas_tingkat || 'Semua Kelas',
    tempat || null,
    penguji_1 || null,
    penguji_2 || null,
    kelompok_peserta || null,
    id
  );

  const updated = db.prepare('SELECT * FROM jadwal_praktik WHERE id = ?').get(id);
  return res.json({ success: true, message: 'Jadwal ujian praktik berhasil diperbarui.', data: updated });
});

router.delete('/jadwal-praktik/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  db.prepare('DELETE FROM jadwal_praktik WHERE id = ?').run(id);
  return res.json({ success: true, message: 'Jadwal ujian praktik berhasil dihapus.' });
});

// ==========================================
// 4. PERSURATAN
// ==========================================
router.get('/surat', (req, res) => {
  const ujianId = getActiveUjianId();
  const list = db.prepare('SELECT * FROM surat WHERE ujian_id = ? ORDER BY id ASC').all(ujianId);
  return res.json({ success: true, data: list });
});

router.get('/surat/:id', (req, res) => {
  const { id } = req.params;
  const surat = db.prepare('SELECT * FROM surat WHERE id = ?').get(id);
  if (!surat) {
    return res.status(404).json({ success: false, message: 'Surat tidak ditemukan.' });
  }
  return res.json({ success: true, data: surat });
});

router.post('/surat', authMiddleware, (req, res) => {
  const ujianId = getActiveUjianId();
  const {
    jenis_surat, nomor_surat, perihal, lampiran, tanggal_surat, tempat_surat,
    dasar_hukum, isi_surat, tujuan_penerima, daftar_petugas_json
  } = req.body;

  if (!jenis_surat || !nomor_surat || !perihal) {
    return res.status(400).json({ success: false, message: 'Jenis surat, nomor, dan perihal wajib diisi.' });
  }

  const result = db.prepare(`
    INSERT INTO surat (
      ujian_id, jenis_surat, nomor_surat, perihal, lampiran, tanggal_surat, tempat_surat,
      dasar_hukum, isi_surat, tujuan_penerima, daftar_petugas_json
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `).run(
    ujianId,
    jenis_surat,
    nomor_surat,
    perihal,
    lampiran || '-',
    tanggal_surat || new Date().toISOString().split('T')[0],
    tempat_surat || 'Bandung',
    dasar_hukum || null,
    isi_surat || null,
    tujuan_penerima || null,
    typeof daftar_petugas_json === 'object' ? JSON.stringify(daftar_petugas_json) : (daftar_petugas_json || '[]')
  );

  const created = db.prepare('SELECT * FROM surat WHERE id = ?').get(result.lastInsertRowid);
  return res.status(201).json({ success: true, message: 'Surat berhasil dibuat.', data: created });
});

router.put('/surat/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  const {
    jenis_surat, nomor_surat, perihal, lampiran, tanggal_surat, tempat_surat,
    dasar_hukum, isi_surat, tujuan_penerima, daftar_petugas_json
  } = req.body;

  db.prepare(`
    UPDATE surat SET
      jenis_surat = ?,
      nomor_surat = ?,
      perihal = ?,
      lampiran = ?,
      tanggal_surat = ?,
      tempat_surat = ?,
      dasar_hukum = ?,
      isi_surat = ?,
      tujuan_penerima = ?,
      daftar_petugas_json = ?
    WHERE id = ?
  `).run(
    jenis_surat,
    nomor_surat,
    perihal,
    lampiran || '-',
    tanggal_surat,
    tempat_surat,
    dasar_hukum || null,
    isi_surat || null,
    tujuan_penerima || null,
    typeof daftar_petugas_json === 'object' ? JSON.stringify(daftar_petugas_json) : (daftar_petugas_json || '[]'),
    id
  );

  const updated = db.prepare('SELECT * FROM surat WHERE id = ?').get(id);
  return res.json({ success: true, message: 'Surat berhasil diperbarui.', data: updated });
});

router.delete('/surat/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  db.prepare('DELETE FROM surat WHERE id = ?').run(id);
  return res.json({ success: true, message: 'Surat berhasil dihapus.' });
});

// ==========================================
// 5. TATA TERTIB
// ==========================================
router.get('/tatib', (req, res) => {
  const list = db.prepare('SELECT * FROM tata_tertib ORDER BY id ASC').all();
  return res.json({ success: true, data: list });
});

router.get('/tatib/:kategori', (req, res) => {
  const { kategori } = req.params;
  const item = db.prepare('SELECT * FROM tata_tertib WHERE kategori = ?').get(kategori);
  if (!item) {
    return res.status(404).json({ success: false, message: 'Tata tertib kategori tersebut tidak ditemukan.' });
  }
  return res.json({ success: true, data: item });
});

router.put('/tatib/:kategori', authMiddleware, (req, res) => {
  const { kategori } = req.params;
  const { judul, isi_poin_json } = req.body;

  db.prepare(`
    UPDATE tata_tertib SET
      judul = ?,
      isi_poin_json = ?,
      updated_at = CURRENT_TIMESTAMP
    WHERE kategori = ?
  `).run(
    judul,
    typeof isi_poin_json === 'object' ? JSON.stringify(isi_poin_json) : isi_poin_json,
    kategori
  );

  const updated = db.prepare('SELECT * FROM tata_tertib WHERE kategori = ?').get(kategori);
  return res.json({ success: true, message: 'Tata tertib berhasil diperbarui.', data: updated });
});

module.exports = router;
