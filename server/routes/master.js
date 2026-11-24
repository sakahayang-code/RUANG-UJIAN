const express = require('express');
const router = express.Router();
const { db } = require('../database/db');
const { authMiddleware } = require('../middleware/auth');

// Helper to get active ujian ID
function getActiveUjianId() {
  const active = db.prepare('SELECT id FROM ujian WHERE is_active = 1 LIMIT 1').get();
  return active ? active.id : 1;
}

// ==========================================
// 1. DATA KELAS
// ==========================================
router.get('/kelas', (req, res) => {
  const list = db.prepare(`
    SELECT k.*, (SELECT COUNT(*) FROM peserta p WHERE p.kelas_id = k.id) as total_siswa
    FROM kelas k
    ORDER BY k.tingkat ASC, k.nama_kelas ASC
  `).all();
  return res.json({ success: true, data: list });
});

router.post('/kelas', authMiddleware, (req, res) => {
  const { tingkat, nama_kelas, jurusan, wali_kelas } = req.body;
  if (!tingkat || !nama_kelas) {
    return res.status(400).json({ success: false, message: 'Tingkat dan nama kelas wajib diisi.' });
  }
  try {
    const result = db.prepare('INSERT INTO kelas (tingkat, nama_kelas, jurusan, wali_kelas) VALUES (?, ?, ?, ?)').run(
      tingkat, nama_kelas, jurusan || null, wali_kelas || null
    );
    const created = db.prepare('SELECT * FROM kelas WHERE id = ?').get(result.lastInsertRowid);
    return res.status(201).json({ success: true, message: 'Data kelas berhasil ditambahkan.', data: created });
  } catch (err) {
    return res.status(400).json({ success: false, message: 'Nama kelas sudah terdaftar atau terjadi kesalahan.' });
  }
});

router.put('/kelas/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  const { tingkat, nama_kelas, jurusan, wali_kelas } = req.body;
  try {
    db.prepare('UPDATE kelas SET tingkat = ?, nama_kelas = ?, jurusan = ?, wali_kelas = ? WHERE id = ?').run(
      tingkat, nama_kelas, jurusan || null, wali_kelas || null, id
    );
    // Update cached nama_kelas in peserta table
    db.prepare('UPDATE peserta SET nama_kelas = ? WHERE kelas_id = ?').run(nama_kelas, id);
    const updated = db.prepare('SELECT * FROM kelas WHERE id = ?').get(id);
    return res.json({ success: true, message: 'Data kelas berhasil diperbarui.', data: updated });
  } catch (err) {
    return res.status(400).json({ success: false, message: 'Gagal memperbarui data kelas.' });
  }
});

router.delete('/kelas/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  db.prepare('DELETE FROM kelas WHERE id = ?').run(id);
  return res.json({ success: true, message: 'Data kelas berhasil dihapus.' });
});

// ==========================================
// 2. DATA MATA PELAJARAN (Tulis & Praktik)
// ==========================================
router.get('/mapel', (req, res) => {
  const { jenis } = req.query;
  let query = 'SELECT * FROM mata_pelajaran';
  const params = [];
  if (jenis) {
    query += ' WHERE jenis = ?';
    params.push(jenis);
  }
  query += ' ORDER BY jenis ASC, kode_mapel ASC';
  const list = db.prepare(query).all(...params);
  return res.json({ success: true, data: list });
});

router.post('/mapel', authMiddleware, (req, res) => {
  const { kode_mapel, nama_mapel, jenis, tingkat, durasi_menit, jumlah_soal, kriteria_penilaian } = req.body;
  if (!kode_mapel || !nama_mapel) {
    return res.status(400).json({ success: false, message: 'Kode dan nama mata pelajaran wajib diisi.' });
  }
  const result = db.prepare(`
    INSERT INTO mata_pelajaran (kode_mapel, nama_mapel, jenis, tingkat, durasi_menit, jumlah_soal, kriteria_penilaian)
    VALUES (?, ?, ?, ?, ?, ?, ?)
  `).run(
    kode_mapel,
    nama_mapel,
    jenis || 'tulis',
    tingkat || 'Semua',
    durasi_menit || 90,
    jumlah_soal || 40,
    kriteria_penilaian || null
  );
  const created = db.prepare('SELECT * FROM mata_pelajaran WHERE id = ?').get(result.lastInsertRowid);
  return res.status(201).json({ success: true, message: 'Mata pelajaran berhasil ditambahkan.', data: created });
});

router.put('/mapel/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  const { kode_mapel, nama_mapel, jenis, tingkat, durasi_menit, jumlah_soal, kriteria_penilaian } = req.body;
  db.prepare(`
    UPDATE mata_pelajaran SET
      kode_mapel = ?,
      nama_mapel = ?,
      jenis = ?,
      tingkat = ?,
      durasi_menit = ?,
      jumlah_soal = ?,
      kriteria_penilaian = ?
    WHERE id = ?
  `).run(
    kode_mapel,
    nama_mapel,
    jenis || 'tulis',
    tingkat || 'Semua',
    durasi_menit || 90,
    jumlah_soal || 40,
    kriteria_penilaian || null,
    id
  );
  const updated = db.prepare('SELECT * FROM mata_pelajaran WHERE id = ?').get(id);
  return res.json({ success: true, message: 'Mata pelajaran berhasil diperbarui.', data: updated });
});

router.delete('/mapel/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  db.prepare('DELETE FROM mata_pelajaran WHERE id = ?').run(id);
  return res.json({ success: true, message: 'Mata pelajaran berhasil dihapus.' });
});

// ==========================================
// 3. ATUR RUANGAN
// ==========================================
router.get('/ruangan', (req, res) => {
  const list = db.prepare(`
    SELECT r.*,
      (SELECT COUNT(*) FROM peserta p WHERE p.ruang_id = r.id) as terisi_peserta
    FROM ruangan r
    ORDER BY r.kode_ruang ASC
  `).all();
  return res.json({ success: true, data: list });
});

router.post('/ruangan', authMiddleware, (req, res) => {
  const { kode_ruang, nama_ruang, kapasitas, jumlah_baris, jumlah_kolom, lokasi } = req.body;
  if (!kode_ruang || !nama_ruang) {
    return res.status(400).json({ success: false, message: 'Kode dan nama ruangan wajib diisi.' });
  }
  try {
    const result = db.prepare(`
      INSERT INTO ruangan (kode_ruang, nama_ruang, kapasitas, jumlah_baris, jumlah_kolom, lokasi)
      VALUES (?, ?, ?, ?, ?, ?)
    `).run(
      kode_ruang,
      nama_ruang,
      kapasitas || 20,
      jumlah_baris || 5,
      jumlah_kolom || 4,
      lokasi || null
    );
    const created = db.prepare('SELECT * FROM ruangan WHERE id = ?').get(result.lastInsertRowid);
    return res.status(201).json({ success: true, message: 'Ruangan berhasil ditambahkan.', data: created });
  } catch (err) {
    return res.status(400).json({ success: false, message: 'Kode ruangan sudah digunakan.' });
  }
});

router.put('/ruangan/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  const { kode_ruang, nama_ruang, kapasitas, jumlah_baris, jumlah_kolom, lokasi } = req.body;
  db.prepare(`
    UPDATE ruangan SET
      kode_ruang = ?,
      nama_ruang = ?,
      kapasitas = ?,
      jumlah_baris = ?,
      jumlah_kolom = ?,
      lokasi = ?
    WHERE id = ?
  `).run(
    kode_ruang,
    nama_ruang,
    kapasitas || 20,
    jumlah_baris || 5,
    jumlah_kolom || 4,
    lokasi || null,
    id
  );
  // Update cached nama_ruang in peserta
  db.prepare('UPDATE peserta SET nama_ruang = ? WHERE ruang_id = ?').run(nama_ruang, id);
  const updated = db.prepare('SELECT * FROM ruangan WHERE id = ?').get(id);
  return res.json({ success: true, message: 'Data ruangan berhasil diperbarui.', data: updated });
});

router.delete('/ruangan/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  db.prepare('DELETE FROM ruangan WHERE id = ?').run(id);
  db.prepare('UPDATE peserta SET ruang_id = NULL, nama_ruang = NULL, no_meja = 0 WHERE ruang_id = ?').run(id);
  return res.json({ success: true, message: 'Ruangan berhasil dihapus.' });
});

// Auto Assign Peserta to Ruangan & Kursi
router.post('/ruangan/auto-assign', authMiddleware, (req, res) => {
  const { clearExisting } = req.body;
  const ujianId = getActiveUjianId();

  if (clearExisting) {
    db.prepare('UPDATE peserta SET ruang_id = NULL, nama_ruang = NULL, no_meja = 0 WHERE ujian_id = ?').run(ujianId);
  }

  const rooms = db.prepare('SELECT * FROM ruangan ORDER BY kode_ruang ASC').all();
  if (rooms.length === 0) {
    return res.status(400).json({ success: false, message: 'Belum ada ruangan ujian yang terdaftar.' });
  }

  // Get all unassigned participants (or all if cleared)
  const students = db.prepare(`
    SELECT id FROM peserta
    WHERE ujian_id = ? AND (ruang_id IS NULL OR ruang_id = 0)
    ORDER BY nama_kelas ASC, no_peserta ASC, nama ASC
  `).all(ujianId);

  let currentRoomIdx = 0;
  let currentDesk = 1;
  let assignedCount = 0;

  const updatePeserta = db.prepare(`
    UPDATE peserta SET ruang_id = ?, nama_ruang = ?, no_meja = ? WHERE id = ?
  `);

  for (const student of students) {
    if (currentRoomIdx >= rooms.length) {
      break; // All rooms full
    }

    const room = rooms[currentRoomIdx];
    updatePeserta.run(room.id, room.nama_ruang, currentDesk, student.id);
    assignedCount++;

    currentDesk++;
    if (currentDesk > room.kapasitas) {
      currentRoomIdx++;
      currentDesk = 1;
    }
  }

  return res.json({
    success: true,
    message: `Berhasil menempatkan ${assignedCount} peserta ke ${Math.min(currentRoomIdx + 1, rooms.length)} ruangan.`,
    assignedCount,
    remainingUnassigned: students.length - assignedCount
  });
});

// ==========================================
// 4. DATA PESERTA UJIAN
// ==========================================
router.get('/peserta', (req, res) => {
  const { kelas_id, ruang_id, search } = req.query;
  const ujianId = getActiveUjianId();

  let query = 'SELECT p.* FROM peserta p WHERE p.ujian_id = ?';
  const params = [ujianId];

  if (kelas_id) {
    query += ' AND p.kelas_id = ?';
    params.push(kelas_id);
  }
  if (ruang_id) {
    query += ' AND p.ruang_id = ?';
    params.push(ruang_id);
  }
  if (search) {
    query += ' AND (p.nama LIKE ? OR p.no_peserta LIKE ? OR p.nisn LIKE ?)';
    const term = `%${search}%`;
    params.push(term, term, term);
  }

  query += ' ORDER BY p.nama_kelas ASC, p.no_peserta ASC';
  const list = db.prepare(query).all(...params);
  return res.json({ success: true, data: list });
});

router.post('/peserta', authMiddleware, (req, res) => {
  const ujianId = getActiveUjianId();
  const { no_peserta, nis, nisn, nama, jenis_kelamin, kelas_id, sesi, ruang_id, no_meja, password } = req.body;

  if (!no_peserta || !nama) {
    return res.status(400).json({ success: false, message: 'Nomor peserta dan nama wajib diisi.' });
  }

  let nama_kelas = null;
  if (kelas_id) {
    const k = db.prepare('SELECT nama_kelas FROM kelas WHERE id = ?').get(kelas_id);
    if (k) nama_kelas = k.nama_kelas;
  }

  let nama_ruang = null;
  if (ruang_id) {
    const r = db.prepare('SELECT nama_ruang FROM ruangan WHERE id = ?').get(ruang_id);
    if (r) nama_ruang = r.nama_ruang;
  }

  try {
    const result = db.prepare(`
      INSERT INTO peserta (
        ujian_id, no_peserta, nis, nisn, nama, jenis_kelamin, kelas_id,
        nama_kelas, sesi, ruang_id, nama_ruang, no_meja, password
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `).run(
      ujianId,
      no_peserta,
      nis || null,
      nisn || null,
      nama,
      jenis_kelamin || 'L',
      kelas_id || null,
      nama_kelas,
      sesi || 1,
      ruang_id || null,
      nama_ruang,
      no_meja || 0,
      password || '123456'
    );
    const created = db.prepare('SELECT * FROM peserta WHERE id = ?').get(result.lastInsertRowid);
    return res.status(201).json({ success: true, message: 'Peserta ujian berhasil ditambahkan.', data: created });
  } catch (err) {
    return res.status(400).json({ success: false, message: 'Nomor peserta sudah terdaftar atau format tidak valid.' });
  }
});

router.put('/peserta/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  const { no_peserta, nis, nisn, nama, jenis_kelamin, kelas_id, sesi, ruang_id, no_meja, password } = req.body;

  let nama_kelas = null;
  if (kelas_id) {
    const k = db.prepare('SELECT nama_kelas FROM kelas WHERE id = ?').get(kelas_id);
    if (k) nama_kelas = k.nama_kelas;
  }

  let nama_ruang = null;
  if (ruang_id) {
    const r = db.prepare('SELECT nama_ruang FROM ruangan WHERE id = ?').get(ruang_id);
    if (r) nama_ruang = r.nama_ruang;
  }

  db.prepare(`
    UPDATE peserta SET
      no_peserta = ?,
      nis = ?,
      nisn = ?,
      nama = ?,
      jenis_kelamin = ?,
      kelas_id = ?,
      nama_kelas = ?,
      sesi = ?,
      ruang_id = ?,
      nama_ruang = ?,
      no_meja = ?,
      password = ?
    WHERE id = ?
  `).run(
    no_peserta,
    nis || null,
    nisn || null,
    nama,
    jenis_kelamin || 'L',
    kelas_id || null,
    nama_kelas,
    sesi || 1,
    ruang_id || null,
    nama_ruang,
    no_meja || 0,
    password || '123456',
    id
  );

  const updated = db.prepare('SELECT * FROM peserta WHERE id = ?').get(id);
  return res.json({ success: true, message: 'Data peserta berhasil diperbarui.', data: updated });
});

router.delete('/peserta/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  db.prepare('DELETE FROM peserta WHERE id = ?').run(id);
  return res.json({ success: true, message: 'Peserta berhasil dihapus.' });
});

// Batch import peserta from array (from CSV/Excel parsed on client)
router.post('/peserta/batch-import', authMiddleware, (req, res) => {
  const { data } = req.body;
  if (!Array.isArray(data) || data.length === 0) {
    return res.status(400).json({ success: false, message: 'Data peserta tidak boleh kosong.' });
  }

  const ujianId = getActiveUjianId();
  const insertPeserta = db.prepare(`
    INSERT OR REPLACE INTO peserta (
      ujian_id, no_peserta, nis, nisn, nama, jenis_kelamin, kelas_id,
      nama_kelas, sesi, ruang_id, nama_ruang, no_meja, password
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `);

  const insertMany = db.transaction((rows) => {
    let count = 0;
    for (const r of rows) {
      if (!r.no_peserta || !r.nama) continue;
      insertPeserta.run(
        ujianId,
        r.no_peserta,
        r.nis || null,
        r.nisn || null,
        r.nama,
        r.jenis_kelamin || 'L',
        r.kelas_id || null,
        r.nama_kelas || null,
        r.sesi || 1,
        r.ruang_id || null,
        r.nama_ruang || null,
        r.no_meja || 0,
        r.password || '123456'
      );
      count++;
    }
    return count;
  });

  const count = insertMany(data);
  return res.json({ success: true, message: `Berhasil mengimpor ${count} peserta ujian.` });
});

// ==========================================
// 5. DATA PANITIA
// ==========================================
router.get('/panitia', (req, res) => {
  const ujianId = getActiveUjianId();
  const list = db.prepare('SELECT * FROM panitia WHERE ujian_id = ? ORDER BY urutan ASC, id ASC').all(ujianId);
  return res.json({ success: true, data: list });
});

router.post('/panitia', authMiddleware, (req, res) => {
  const ujianId = getActiveUjianId();
  const { nama, nip, jabatan_struktural, jabatan_panitia, no_hp, tugas, urutan } = req.body;
  if (!nama || !jabatan_panitia) {
    return res.status(400).json({ success: false, message: 'Nama dan jabatan panitia wajib diisi.' });
  }
  const result = db.prepare(`
    INSERT INTO panitia (ujian_id, nama, nip, jabatan_struktural, jabatan_panitia, no_hp, tugas, urutan)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
  `).run(
    ujianId,
    nama,
    nip || null,
    jabatan_struktural || null,
    jabatan_panitia,
    no_hp || null,
    tugas || null,
    urutan || 0
  );
  const created = db.prepare('SELECT * FROM panitia WHERE id = ?').get(result.lastInsertRowid);
  return res.status(201).json({ success: true, message: 'Panitia berhasil ditambahkan.', data: created });
});

router.put('/panitia/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  const { nama, nip, jabatan_struktural, jabatan_panitia, no_hp, tugas, urutan } = req.body;
  db.prepare(`
    UPDATE panitia SET
      nama = ?,
      nip = ?,
      jabatan_struktural = ?,
      jabatan_panitia = ?,
      no_hp = ?,
      tugas = ?,
      urutan = ?
    WHERE id = ?
  `).run(
    nama,
    nip || null,
    jabatan_struktural || null,
    jabatan_panitia,
    no_hp || null,
    tugas || null,
    urutan || 0,
    id
  );
  const updated = db.prepare('SELECT * FROM panitia WHERE id = ?').get(id);
  return res.json({ success: true, message: 'Data panitia berhasil diperbarui.', data: updated });
});

router.delete('/panitia/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  db.prepare('DELETE FROM panitia WHERE id = ?').run(id);
  return res.json({ success: true, message: 'Panitia berhasil dihapus.' });
});

// ==========================================
// 6. DATA PENGAWAS RUANG
// ==========================================
router.get('/pengawas', (req, res) => {
  const list = db.prepare('SELECT * FROM pengawas ORDER BY kode_pengawas ASC, nama ASC').all();
  return res.json({ success: true, data: list });
});

router.post('/pengawas', authMiddleware, (req, res) => {
  const { kode_pengawas, nama, nip, asal_sekolah, mata_pelajaran_ampu, no_hp } = req.body;
  if (!nama) {
    return res.status(400).json({ success: false, message: 'Nama pengawas wajib diisi.' });
  }
  const result = db.prepare(`
    INSERT INTO pengawas (kode_pengawas, nama, nip, asal_sekolah, mata_pelajaran_ampu, no_hp)
    VALUES (?, ?, ?, ?, ?, ?)
  `).run(
    kode_pengawas || null,
    nama,
    nip || null,
    asal_sekolah || null,
    mata_pelajaran_ampu || null,
    no_hp || null
  );
  const created = db.prepare('SELECT * FROM pengawas WHERE id = ?').get(result.lastInsertRowid);
  return res.status(201).json({ success: true, message: 'Pengawas ruang berhasil ditambahkan.', data: created });
});

router.put('/pengawas/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  const { kode_pengawas, nama, nip, asal_sekolah, mata_pelajaran_ampu, no_hp } = req.body;
  db.prepare(`
    UPDATE pengawas SET
      kode_pengawas = ?,
      nama = ?,
      nip = ?,
      asal_sekolah = ?,
      mata_pelajaran_ampu = ?,
      no_hp = ?
    WHERE id = ?
  `).run(
    kode_pengawas || null,
    nama,
    nip || null,
    asal_sekolah || null,
    mata_pelajaran_ampu || null,
    no_hp || null,
    id
  );
  const updated = db.prepare('SELECT * FROM pengawas WHERE id = ?').get(id);
  return res.json({ success: true, message: 'Data pengawas berhasil diperbarui.', data: updated });
});

router.delete('/pengawas/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  db.prepare('DELETE FROM pengawas WHERE id = ?').run(id);
  return res.json({ success: true, message: 'Pengawas ruang berhasil dihapus.' });
});

// ==========================================
// 7. DATA PENGUJI PRAKTIK
// ==========================================
router.get('/penguji', (req, res) => {
  const list = db.prepare('SELECT * FROM penguji_praktik ORDER BY mata_pelajaran ASC, nama ASC').all();
  return res.json({ success: true, data: list });
});

router.post('/penguji', authMiddleware, (req, res) => {
  const { nama, nip, mata_pelajaran, asal_instansi, no_hp, keterangan } = req.body;
  if (!nama || !mata_pelajaran) {
    return res.status(400).json({ success: false, message: 'Nama dan mata pelajaran pengujian wajib diisi.' });
  }
  const result = db.prepare(`
    INSERT INTO penguji_praktik (nama, nip, mata_pelajaran, asal_instansi, no_hp, keterangan)
    VALUES (?, ?, ?, ?, ?, ?)
  `).run(
    nama,
    nip || null,
    mata_pelajaran,
    asal_instansi || null,
    no_hp || null,
    keterangan || null
  );
  const created = db.prepare('SELECT * FROM penguji_praktik WHERE id = ?').get(result.lastInsertRowid);
  return res.status(201).json({ success: true, message: 'Penguji praktik berhasil ditambahkan.', data: created });
});

router.put('/penguji/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  const { nama, nip, mata_pelajaran, asal_instansi, no_hp, keterangan } = req.body;
  db.prepare(`
    UPDATE penguji_praktik SET
      nama = ?,
      nip = ?,
      mata_pelajaran = ?,
      asal_instansi = ?,
      no_hp = ?,
      keterangan = ?
    WHERE id = ?
  `).run(
    nama,
    nip || null,
    mata_pelajaran,
    asal_instansi || null,
    no_hp || null,
    keterangan || null,
    id
  );
  const updated = db.prepare('SELECT * FROM penguji_praktik WHERE id = ?').get(id);
  return res.json({ success: true, message: 'Data penguji praktik berhasil diperbarui.', data: updated });
});

router.delete('/penguji/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  db.prepare('DELETE FROM penguji_praktik WHERE id = ?').run(id);
  return res.json({ success: true, message: 'Penguji praktik berhasil dihapus.' });
});

module.exports = router;
