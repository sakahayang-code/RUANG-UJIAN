const express = require('express');
const router = express.Router();
const { db } = require('../database/db');
const { authMiddleware } = require('../middleware/auth');

function getActiveUjian() {
  let active = db.prepare('SELECT * FROM ujian WHERE is_active = 1 LIMIT 1').get();
  if (!active) {
    active = db.prepare('SELECT * FROM ujian ORDER BY id DESC LIMIT 1').get();
  }
  return active;
}

function getSekolah() {
  return db.prepare('SELECT * FROM identitas_sekolah WHERE id = 1').get();
}

// 1. DATA KARTU PESERTA
router.get('/kartu-peserta', (req, res) => {
  const { kelas_id, ruang_id, id } = req.query;
  const ujian = getActiveUjian();
  const sekolah = getSekolah();

  let query = 'SELECT * FROM peserta WHERE ujian_id = ?';
  const params = [ujian.id];

  if (id) {
    query += ' AND id = ?';
    params.push(id);
  } else {
    if (kelas_id) {
      query += ' AND kelas_id = ?';
      params.push(kelas_id);
    }
    if (ruang_id) {
      query += ' AND ruang_id = ?';
      params.push(ruang_id);
    }
  }

  query += ' ORDER BY nama_kelas ASC, no_meja ASC, no_peserta ASC';
  const pesertaList = db.prepare(query).all(...params);

  return res.json({
    success: true,
    sekolah,
    ujian,
    data: pesertaList
  });
});

// 2. DATA KARTU PANITIA
router.get('/kartu-panitia', (req, res) => {
  const ujian = getActiveUjian();
  const sekolah = getSekolah();
  const panitiaList = db.prepare('SELECT * FROM panitia WHERE ujian_id = ? ORDER BY urutan ASC, id ASC').all(ujian.id);

  return res.json({
    success: true,
    sekolah,
    ujian,
    data: panitiaList
  });
});

// 3. DATA JADWAL CETAK (Tulis & Praktik)
router.get('/jadwal', (req, res) => {
  const ujian = getActiveUjian();
  const sekolah = getSekolah();
  const tulis = db.prepare('SELECT * FROM jadwal_tulis WHERE ujian_id = ? ORDER BY hari_tanggal ASC, jam_mulai ASC').all(ujian.id);
  const praktik = db.prepare('SELECT * FROM jadwal_praktik WHERE ujian_id = ? ORDER BY hari_tanggal ASC, waktu ASC').all(ujian.id);

  return res.json({
    success: true,
    sekolah,
    ujian,
    jadwal_tulis: tulis,
    jadwal_praktik: praktik
  });
});

// 4. DATA NOMOR RUANGAN UJIAN
router.get('/nomor-ruang', (req, res) => {
  const ujian = getActiveUjian();
  const sekolah = getSekolah();
  const ruangan = db.prepare('SELECT * FROM ruangan ORDER BY kode_ruang ASC').all();

  const data = ruangan.map(r => {
    const participants = db.prepare(`
      SELECT no_peserta, nama, nama_kelas, no_meja
      FROM peserta
      WHERE ujian_id = ? AND ruang_id = ?
      ORDER BY no_meja ASC, no_peserta ASC
    `).all(ujian.id, r.id);

    const firstNo = participants.length > 0 ? participants[0].no_peserta : '-';
    const lastNo = participants.length > 0 ? participants[participants.length - 1].no_peserta : '-';

    return {
      ...r,
      total_peserta: participants.length,
      rentang_nomor: participants.length > 0 ? `${firstNo} s.d. ${lastNo}` : 'Belum ada peserta',
      peserta: participants
    };
  });

  return res.json({
    success: true,
    sekolah,
    ujian,
    data
  });
});

// 5. DATA DAFTAR PESERTA UJIAN (DNT)
router.get('/daftar-peserta', (req, res) => {
  const { filter_by, filter_id } = req.query; // filter_by: 'ruang' or 'kelas'
  const ujian = getActiveUjian();
  const sekolah = getSekolah();

  let query = 'SELECT * FROM peserta WHERE ujian_id = ?';
  const params = [ujian.id];

  if (filter_by === 'ruang' && filter_id) {
    query += ' AND ruang_id = ?';
    params.push(filter_id);
  } else if (filter_by === 'kelas' && filter_id) {
    query += ' AND kelas_id = ?';
    params.push(filter_id);
  }

  query += ' ORDER BY nama_kelas ASC, no_meja ASC, no_peserta ASC';
  const data = db.prepare(query).all(...params);

  return res.json({
    success: true,
    sekolah,
    ujian,
    data
  });
});

// 6. DATA DENAH TEMPAT DUDUK
router.get('/denah-duduk', (req, res) => {
  const { ruang_id } = req.query;
  const ujian = getActiveUjian();
  const sekolah = getSekolah();

  let roomQuery = 'SELECT * FROM ruangan';
  const roomParams = [];
  if (ruang_id) {
    roomQuery += ' WHERE id = ?';
    roomParams.push(ruang_id);
  }
  roomQuery += ' ORDER BY kode_ruang ASC';
  const rooms = db.prepare(roomQuery).all(...roomParams);

  const data = rooms.map(room => {
    const participants = db.prepare(`
      SELECT * FROM peserta
      WHERE ujian_id = ? AND ruang_id = ?
      ORDER BY no_meja ASC
    `).all(ujian.id, room.id);

    // Build grid
    const totalSlots = room.jumlah_baris * room.jumlah_kolom;
    const grid = [];
    const participantMap = {};
    participants.forEach(p => {
      participantMap[p.no_meja] = p;
    });

    let seatNo = 1;
    for (let r = 0; r < room.jumlah_baris; r++) {
      const row = [];
      for (let c = 0; c < room.jumlah_kolom; c++) {
        row.push({
          seatNo,
          peserta: participantMap[seatNo] || null
        });
        seatNo++;
      }
      grid.push(row);
    }

    return {
      ruang: room,
      total_peserta: participants.length,
      grid
    };
  });

  return res.json({
    success: true,
    sekolah,
    ujian,
    data
  });
});

// 7. DAFTAR HADIR
// 7a. Daftar Hadir Panitia
router.get('/daftar-hadir/panitia', (req, res) => {
  const ujian = getActiveUjian();
  const sekolah = getSekolah();
  const panitia = db.prepare('SELECT * FROM panitia WHERE ujian_id = ? ORDER BY urutan ASC, id ASC').all(ujian.id);

  return res.json({
    success: true,
    sekolah,
    ujian,
    data: panitia
  });
});

// 7b. Daftar Hadir Peserta Ujian
router.get('/daftar-hadir/peserta', (req, res) => {
  const { ruang_id, mapel_id, sesi } = req.query;
  const ujian = getActiveUjian();
  const sekolah = getSekolah();

  let mapel = null;
  if (mapel_id) {
    mapel = db.prepare('SELECT * FROM mata_pelajaran WHERE id = ?').get(mapel_id);
  }

  let ruang = null;
  if (ruang_id) {
    ruang = db.prepare('SELECT * FROM ruangan WHERE id = ?').get(ruang_id);
  }

  let query = 'SELECT * FROM peserta WHERE ujian_id = ?';
  const params = [ujian.id];
  if (ruang_id) {
    query += ' AND ruang_id = ?';
    params.push(ruang_id);
  }
  if (sesi) {
    query += ' AND sesi = ?';
    params.push(sesi);
  }
  query += ' ORDER BY no_meja ASC, no_peserta ASC';
  const pesertaList = db.prepare(query).all(...params);

  return res.json({
    success: true,
    sekolah,
    ujian,
    ruang,
    mapel,
    sesi: sesi || 1,
    data: pesertaList
  });
});

// 7c. Daftar Hadir Pengawas Ruang
router.get('/daftar-hadir/pengawas', (req, res) => {
  const ujian = getActiveUjian();
  const sekolah = getSekolah();
  const pengawas = db.prepare('SELECT * FROM pengawas ORDER BY kode_pengawas ASC, nama ASC').all();
  const jadwal = db.prepare('SELECT * FROM jadwal_tulis WHERE ujian_id = ? ORDER BY hari_tanggal ASC, jam_mulai ASC').all(ujian.id);

  return res.json({
    success: true,
    sekolah,
    ujian,
    pengawas,
    jadwal
  });
});

// 7d. Daftar Hadir Penguji Praktik
router.get('/daftar-hadir/penguji', (req, res) => {
  const ujian = getActiveUjian();
  const sekolah = getSekolah();
  const penguji = db.prepare('SELECT * FROM penguji_praktik ORDER BY mata_pelajaran ASC, nama ASC').all();
  const jadwal = db.prepare('SELECT * FROM jadwal_praktik WHERE ujian_id = ? ORDER BY hari_tanggal ASC, waktu ASC').all(ujian.id);

  return res.json({
    success: true,
    sekolah,
    ujian,
    penguji,
    jadwal
  });
});

// 8. BERITA ACARA PELAKSANAAN UJIAN
router.get('/berita-acara', (req, res) => {
  const ujian = getActiveUjian();
  const sekolah = getSekolah();
  const list = db.prepare('SELECT * FROM berita_acara WHERE ujian_id = ? ORDER BY tanggal DESC, id DESC').all(ujian.id);

  return res.json({
    success: true,
    sekolah,
    ujian,
    data: list
  });
});

router.post('/berita-acara', authMiddleware, (req, res) => {
  const ujian = getActiveUjian();
  const {
    tanggal, ruang_id, sesi, mapel_id,
    jumlah_seharusnya, jumlah_hadir, jumlah_tidak_hadir, nomor_tidak_hadir,
    catatan_kejadian, pengawas_1_nama, pengawas_1_nip, pengawas_2_nama, pengawas_2_nip
  } = req.body;

  let nama_ruang = null;
  if (ruang_id) {
    const r = db.prepare('SELECT nama_ruang FROM ruangan WHERE id = ?').get(ruang_id);
    if (r) nama_ruang = r.nama_ruang;
  }

  let nama_mapel = null;
  if (mapel_id) {
    const m = db.prepare('SELECT nama_mapel FROM mata_pelajaran WHERE id = ?').get(mapel_id);
    if (m) nama_mapel = m.nama_mapel;
  }

  const result = db.prepare(`
    INSERT INTO berita_acara (
      ujian_id, tanggal, ruang_id, nama_ruang, sesi, mapel_id, nama_mapel,
      jumlah_seharusnya, jumlah_hadir, jumlah_tidak_hadir, nomor_tidak_hadir,
      catatan_kejadian, pengawas_1_nama, pengawas_1_nip, pengawas_2_nama, pengawas_2_nip
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `).run(
    ujian.id,
    tanggal || new Date().toISOString().split('T')[0],
    ruang_id || null,
    nama_ruang,
    sesi || 1,
    mapel_id || null,
    nama_mapel,
    jumlah_seharusnya || 0,
    jumlah_hadir || 0,
    jumlah_tidak_hadir || 0,
    nomor_tidak_hadir || '-',
    catatan_kejadian || 'Pelaksanaan ujian berjalan tertib dan lancar.',
    pengawas_1_nama || null,
    pengawas_1_nip || null,
    pengawas_2_nama || null,
    pengawas_2_nip || null
  );

  const created = db.prepare('SELECT * FROM berita_acara WHERE id = ?').get(result.lastInsertRowid);
  return res.status(201).json({ success: true, message: 'Berita acara berhasil disimpan.', data: created });
});

router.put('/berita-acara/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  const {
    tanggal, ruang_id, sesi, mapel_id,
    jumlah_seharusnya, jumlah_hadir, jumlah_tidak_hadir, nomor_tidak_hadir,
    catatan_kejadian, pengawas_1_nama, pengawas_1_nip, pengawas_2_nama, pengawas_2_nip
  } = req.body;

  let nama_ruang = null;
  if (ruang_id) {
    const r = db.prepare('SELECT nama_ruang FROM ruangan WHERE id = ?').get(ruang_id);
    if (r) nama_ruang = r.nama_ruang;
  }

  let nama_mapel = null;
  if (mapel_id) {
    const m = db.prepare('SELECT nama_mapel FROM mata_pelajaran WHERE id = ?').get(mapel_id);
    if (m) nama_mapel = m.nama_mapel;
  }

  db.prepare(`
    UPDATE berita_acara SET
      tanggal = ?,
      ruang_id = ?,
      nama_ruang = ?,
      sesi = ?,
      mapel_id = ?,
      nama_mapel = ?,
      jumlah_seharusnya = ?,
      jumlah_hadir = ?,
      jumlah_tidak_hadir = ?,
      nomor_tidak_hadir = ?,
      catatan_kejadian = ?,
      pengawas_1_nama = ?,
      pengawas_1_nip = ?,
      pengawas_2_nama = ?,
      pengawas_2_nip = ?
    WHERE id = ?
  `).run(
    tanggal,
    ruang_id || null,
    nama_ruang,
    sesi || 1,
    mapel_id || null,
    nama_mapel,
    jumlah_seharusnya || 0,
    jumlah_hadir || 0,
    jumlah_tidak_hadir || 0,
    nomor_tidak_hadir || '-',
    catatan_kejadian,
    pengawas_1_nama || null,
    pengawas_1_nip || null,
    pengawas_2_nama || null,
    pengawas_2_nip || null,
    id
  );

  const updated = db.prepare('SELECT * FROM berita_acara WHERE id = ?').get(id);
  return res.json({ success: true, message: 'Berita acara berhasil diperbarui.', data: updated });
});

router.delete('/berita-acara/:id', authMiddleware, (req, res) => {
  const { id } = req.params;
  db.prepare('DELETE FROM berita_acara WHERE id = ?').run(id);
  return res.json({ success: true, message: 'Berita acara berhasil dihapus.' });
});

// 9. LPJ KEGIATAN UJIAN
router.get('/lpj', (req, res) => {
  const ujian = getActiveUjian();
  const sekolah = getSekolah();
  let lpj = db.prepare('SELECT * FROM lpj WHERE ujian_id = ?').get(ujian.id);

  if (!lpj) {
    // Return sample draft if not yet initialized for this exam
    lpj = {
      ujian_id: ujian.id,
      nomor_sk: `421.3/SK-${String(ujian.id).padStart(3, '0')}/SMAN1-MB/${new Date().getFullYear()}`,
      tentang_sk: `Pembentukan Panitia ${ujian.nama_ujian}`,
      latar_belakang: 'Dalam rangka memenuhi standar nasional pendidikan dan mengevaluasi hasil belajar peserta didik...',
      dasar_kegiatan: '1. UU No. 20 Tahun 2003 tentang Sisdiknas\n2. Permendikbudristek Standar Penilaian',
      tujuan_kegiatan: '1. Mengukur capaian kompetensi akademik\n2. Bahan evaluasi kurikulum sekolah',
      hasil_evaluasi: 'Pelaksanaan ujian berjalan lancar dengan tingkat kehadiran 99%.',
      hambatan_solusi: 'Kendala teknis tertangani dengan sigap oleh tim panitia.',
      kesimpulan: 'Kegiatan ujian terselenggara dengan sukses dan akuntabel.',
      saran: 'Peningkatan sarana prasarana penunjang ujian pada periode berikutnya.',
      rincian_keuangan_json: JSON.stringify({
        pemasukan: [{ sumber: 'BOS Reguler', jumlah: 8000000 }],
        pengeluaran: [{ uraian: 'Penggandaan Naskah & Administrasi', jumlah: 3000000 }, { uraian: 'Konsumsi & Honor', jumlah: 5000000 }]
      })
    };
  }

  // Supporting stats for LPJ
  const totalPeserta = db.prepare('SELECT COUNT(*) as c FROM peserta WHERE ujian_id = ?').get(ujian.id).c;
  const totalPanitia = db.prepare('SELECT COUNT(*) as c FROM panitia WHERE ujian_id = ?').get(ujian.id).c;
  const totalPengawas = db.prepare('SELECT COUNT(*) as c FROM pengawas').get().c;
  const totalRuang = db.prepare('SELECT COUNT(*) as c FROM ruangan').get().c;
  const proker = db.prepare('SELECT * FROM program_kerja WHERE ujian_id = ? ORDER BY urutan ASC').all(ujian.id);

  return res.json({
    success: true,
    sekolah,
    ujian,
    lpj,
    summary: {
      totalPeserta,
      totalPanitia,
      totalPengawas,
      totalRuang,
      proker
    }
  });
});

router.put('/lpj', authMiddleware, (req, res) => {
  const ujian = getActiveUjian();
  const {
    nomor_sk, tentang_sk, latar_belakang, dasar_kegiatan, tujuan_kegiatan,
    hasil_evaluasi, hambatan_solusi, kesimpulan, saran, rincian_keuangan_json
  } = req.body;

  const existing = db.prepare('SELECT id FROM lpj WHERE ujian_id = ?').get(ujian.id);

  if (existing) {
    db.prepare(`
      UPDATE lpj SET
        nomor_sk = ?,
        tentang_sk = ?,
        latar_belakang = ?,
        dasar_kegiatan = ?,
        tujuan_kegiatan = ?,
        hasil_evaluasi = ?,
        hambatan_solusi = ?,
        kesimpulan = ?,
        saran = ?,
        rincian_keuangan_json = ?,
        updated_at = CURRENT_TIMESTAMP
      WHERE ujian_id = ?
    `).run(
      nomor_sk,
      tentang_sk,
      latar_belakang,
      dasar_kegiatan,
      tujuan_kegiatan,
      hasil_evaluasi,
      hambatan_solusi,
      kesimpulan,
      saran,
      typeof rincian_keuangan_json === 'object' ? JSON.stringify(rincian_keuangan_json) : rincian_keuangan_json,
      ujian.id
    );
  } else {
    db.prepare(`
      INSERT INTO lpj (
        ujian_id, nomor_sk, tentang_sk, latar_belakang, dasar_kegiatan, tujuan_kegiatan,
        hasil_evaluasi, hambatan_solusi, kesimpulan, saran, rincian_keuangan_json
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `).run(
      ujian.id,
      nomor_sk,
      tentang_sk,
      latar_belakang,
      dasar_kegiatan,
      tujuan_kegiatan,
      hasil_evaluasi,
      hambatan_solusi,
      kesimpulan,
      saran,
      typeof rincian_keuangan_json === 'object' ? JSON.stringify(rincian_keuangan_json) : rincian_keuangan_json
    );
  }

  const updated = db.prepare('SELECT * FROM lpj WHERE ujian_id = ?').get(ujian.id);
  return res.json({ success: true, message: 'LPJ Kegiatan Ujian berhasil disimpan.', data: updated });
});

module.exports = router;
