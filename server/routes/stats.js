const express = require('express');
const router = express.Router();
const { db } = require('../database/db');

router.get('/dashboard', (req, res) => {
  let activeUjian = db.prepare('SELECT * FROM ujian WHERE is_active = 1 LIMIT 1').get();
  if (!activeUjian) {
    activeUjian = db.prepare('SELECT * FROM ujian ORDER BY id DESC LIMIT 1').get();
  }
  const ujianId = activeUjian ? activeUjian.id : 1;

  const sekolah = db.prepare('SELECT nama_sekolah, npsn, kepala_sekolah, tahun_ajaran, semester, logo_url FROM identitas_sekolah WHERE id = 1').get();

  // Metrics
  const totalPeserta = db.prepare('SELECT COUNT(*) as c FROM peserta WHERE ujian_id = ?').get(ujianId).c;
  const pesertaLaki = db.prepare("SELECT COUNT(*) as c FROM peserta WHERE ujian_id = ? AND jenis_kelamin = 'L'").get(ujianId).c;
  const pesertaPerempuan = db.prepare("SELECT COUNT(*) as c FROM peserta WHERE ujian_id = ? AND jenis_kelamin = 'P'").get(ujianId).c;
  const pesertaBelumRuang = db.prepare("SELECT COUNT(*) as c FROM peserta WHERE ujian_id = ? AND (ruang_id IS NULL OR ruang_id = 0)").get(ujianId).c;

  const totalKelas = db.prepare('SELECT COUNT(*) as c FROM kelas').get().c;
  const totalMapelTulis = db.prepare("SELECT COUNT(*) as c FROM mata_pelajaran WHERE jenis = 'tulis'").get().c;
  const totalMapelPraktik = db.prepare("SELECT COUNT(*) as c FROM mata_pelajaran WHERE jenis = 'praktik'").get().c;

  const totalRuang = db.prepare('SELECT COUNT(*) as c FROM ruangan').get().c;
  const kapasitasTotal = db.prepare('SELECT SUM(kapasitas) as s FROM ruangan').get().s || 0;

  const totalPanitia = db.prepare('SELECT COUNT(*) as c FROM panitia WHERE ujian_id = ?').get(ujianId).c;
  const totalPengawas = db.prepare('SELECT COUNT(*) as c FROM pengawas').get().c;
  const totalPenguji = db.prepare('SELECT COUNT(*) as c FROM penguji_praktik').get().c;

  // Proker Stats
  const prokerTotal = db.prepare('SELECT COUNT(*) as c FROM program_kerja WHERE ujian_id = ?').get(ujianId).c;
  const prokerSelesai = db.prepare("SELECT COUNT(*) as c FROM program_kerja WHERE ujian_id = ? AND status = 'Selesai'").get(ujianId).c;
  const prokerSedang = db.prepare("SELECT COUNT(*) as c FROM program_kerja WHERE ujian_id = ? AND status = 'Sedang'").get(ujianId).c;
  const prokerBelum = db.prepare("SELECT COUNT(*) as c FROM program_kerja WHERE ujian_id = ? AND status = 'Belum'").get(ujianId).c;
  const totalAnggaran = db.prepare('SELECT SUM(anggaran) as s FROM program_kerja WHERE ujian_id = ?').get(ujianId).s || 0;

  // Recent / Upcoming Schedules
  const jadwalTulis = db.prepare(`
    SELECT jt.*, m.kode_mapel
    FROM jadwal_tulis jt
    LEFT JOIN mata_pelajaran m ON jt.mapel_id = m.id
    WHERE jt.ujian_id = ?
    ORDER BY jt.hari_tanggal ASC, jt.jam_mulai ASC
    LIMIT 6
  `).all(ujianId);

  const jadwalPraktik = db.prepare(`
    SELECT jp.*, m.kode_mapel
    FROM jadwal_praktik jp
    LEFT JOIN mata_pelajaran m ON jp.mapel_id = m.id
    WHERE jp.ujian_id = ?
    ORDER BY jp.hari_tanggal ASC, jp.waktu ASC
    LIMIT 5
  `).all(ujianId);

  // Ruangan details
  const ruanganList = db.prepare(`
    SELECT r.*,
      (SELECT COUNT(*) FROM peserta p WHERE p.ruang_id = r.id AND p.ujian_id = ?) as terisi
    FROM ruangan r
    ORDER BY r.kode_ruang ASC
  `).all(ujianId);

  return res.json({
    success: true,
    sekolah,
    ujian: activeUjian,
    metrics: {
      totalPeserta,
      pesertaLaki,
      pesertaPerempuan,
      pesertaBelumRuang,
      totalKelas,
      totalMapelTulis,
      totalMapelPraktik,
      totalRuang,
      kapasitasTotal,
      totalPanitia,
      totalPengawas,
      totalPenguji,
      proker: {
        total: prokerTotal,
        selesai: prokerSelesai,
        sedang: prokerSedang,
        belum: prokerBelum,
        persentase: prokerTotal > 0 ? Math.round((prokerSelesai / prokerTotal) * 100) : 0,
        totalAnggaran
      }
    },
    jadwalTulis,
    jadwalPraktik,
    ruanganList
  });
});

module.exports = router;
