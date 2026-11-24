<div class="page-header">
    <div>
        <h1 class="page-title">Pusat Cetak Administrasi Ujian</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Modul terpadu pencetakan seluruh dokumen resmi, kartu, jadwal, label, presensi, dan laporan kegiatan ujian.</p>
    </div>
</div>

<div class="row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
    <!-- 1. Kartu Peserta Ujian -->
    <div class="card p-4" style="border-top: 3px solid #2563eb;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <div style="font-size: 32px;">🪪</div>
            <span class="badge badge-primary" style="font-size: 11px;">1. CETAK</span>
        </div>
        <h3 style="margin-top: 0; font-size: 18px;">Kartu Peserta Ujian</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Cetak kartu resmi peserta ujian lengkap dengan foto, nomor peserta, ruang, nomor meja, serta QR Code verifikasi identitas.
        </p>
        <form method="GET" action="<?= url('/print/participant-cards') ?>" target="_blank" style="margin-top: 15px;">
            <div class="form-group mb-2">
                <label style="font-size: 11px; font-weight: 600;">Filter Kelas:</label>
                <select name="class_id" class="form-control" style="font-size: 12px; padding: 6px 10px;">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (Tingkat <?= e($c['grade']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-3">
                <label style="font-size: 11px; font-weight: 600;">Filter Ruang Ujian:</label>
                <select name="room_id" class="form-control" style="font-size: 12px; padding: 6px 10px;">
                    <option value="">-- Semua Ruang --</option>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?= $r['id'] ?>">Ruang <?= e($r['room_number']) ?> (<?= e($r['name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block" style="width: 100%;">
                🖨️ Cetak Kartu Peserta
            </button>
        </form>
    </div>

    <!-- 2. Kartu Panitia -->
    <div class="card p-4" style="border-top: 3px solid #059669;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <div style="font-size: 32px;">🏷️</div>
            <span class="badge badge-success" style="font-size: 11px;">2. CETAK</span>
        </div>
        <h3 style="margin-top: 0; font-size: 18px;">Kartu Panitia</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Cetak kartu tanda pengenal (ID Card / Name Tag) resmi untuk Panitia Pelaksana dan Pengawas Ruang dengan QR Code verifikasi.
        </p>
        <form method="GET" action="<?= url('/print/name-tags') ?>" target="_blank" style="margin-top: 15px;">
            <div class="form-group mb-3">
                <label style="font-size: 11px; font-weight: 600;">Pilih Kategori:</label>
                <select name="type" class="form-control" style="font-size: 12px; padding: 6px 10px;">
                    <option value="panitia">Panitia Pelaksana Ujian</option>
                    <option value="pengawas">Pengawas Ruang Ujian</option>
                </select>
            </div>
            <div style="height: 52px;"></div>
            <button type="submit" class="btn btn-success btn-block" style="width: 100%;">
                🖨️ Cetak Kartu Panitia &amp; Pengawas
            </button>
        </form>
    </div>

    <!-- 3. Jadwal (Jadwal Ujian Tulis & Jadwal Ujian Praktik) -->
    <div class="card p-4" style="border-top: 3px solid #7c3aed;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <div style="font-size: 32px;">📅</div>
            <span class="badge" style="background: #f5f3ff; color: #7c3aed; font-size: 11px;">3. CETAK</span>
        </div>
        <h3 style="margin-top: 0; font-size: 18px;">Jadwal Ujian</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Cetak dokumen jadwal resmi ujian tertulis dan ujian praktik lengkap dengan kop sekolah, waktu, sesi, dan tanda tangan pengesahan.
        </p>
        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 25px;">
            <a href="<?= url('/print/schedules?type=written') ?>" target="_blank" class="btn btn-primary" style="text-align: center;">
                📝 Cetak Jadwal Ujian Tulis
            </a>
            <a href="<?= url('/print/schedules?type=practical') ?>" target="_blank" class="btn btn-outline" style="text-align: center; border-color: #7c3aed; color: #7c3aed;">
                🧪 Cetak Jadwal Ujian Praktik
            </a>
        </div>
    </div>

    <!-- 4. Nomor Ruangan Ujian (Room Signs) -->
    <div class="card p-4" style="border-top: 3px solid #ea580c;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <div style="font-size: 32px;">🚪</div>
            <span class="badge" style="background: #fff7ed; color: #ea580c; font-size: 11px;">4. CETAK</span>
        </div>
        <h3 style="margin-top: 0; font-size: 18px;">Nomor Ruangan Ujian</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Plang nomor ruang berukuran besar untuk ditempel pada pintu ruang ujian, lengkap dengan daftar kelas dan rentang nomor peserta.
        </p>
        <form method="GET" action="<?= url('/print/room-signs') ?>" target="_blank" style="margin-top: 15px;">
            <div class="form-group mb-3">
                <label style="font-size: 11px; font-weight: 600;">Pilih Ruang Ujian:</label>
                <select name="room_id" class="form-control" style="font-size: 12px; padding: 6px 10px;">
                    <option value="">-- Semua Ruang (Cetak Sekaligus) --</option>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?= $r['id'] ?>">Ruang <?= e($r['room_number']) ?> (<?= e($r['name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="height: 52px;"></div>
            <button type="submit" class="btn btn-primary btn-block" style="width: 100%; background: #ea580c; border-color: #ea580c;">
                🖨️ Cetak Nomor Ruang Ujian
            </button>
        </form>
    </div>

    <!-- 5. Daftar Peserta Ujian -->
    <div class="card p-4" style="border-top: 3px solid #0284c7;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <div style="font-size: 32px;">📑</div>
            <span class="badge" style="background: #f0f9ff; color: #0284c7; font-size: 11px;">5. CETAK</span>
        </div>
        <h3 style="margin-top: 0; font-size: 18px;">Daftar Peserta Ujian</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Daftar nominasi peserta resmi per kelas atau per ruang ujian dengan nomor peserta, NISN, nomor meja, dan kolom tanda tangan.
        </p>
        <form method="GET" action="<?= url('/print/participants') ?>" target="_blank" style="margin-top: 15px;">
            <div class="form-group mb-2">
                <label style="font-size: 11px; font-weight: 600;">Filter Kelas:</label>
                <select name="class_id" class="form-control" style="font-size: 12px; padding: 6px 10px;">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (Tingkat <?= e($c['grade']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-3">
                <label style="font-size: 11px; font-weight: 600;">Filter Ruang Ujian:</label>
                <select name="room_id" class="form-control" style="font-size: 12px; padding: 6px 10px;">
                    <option value="">-- Semua Ruang Ujian --</option>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?= $r['id'] ?>">Ruang <?= e($r['room_number']) ?> (<?= e($r['name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block" style="width: 100%; background: #0284c7; border-color: #0284c7;">
                🖨️ Cetak Daftar Peserta Ujian
            </button>
        </form>
    </div>

    <!-- 6. Denah Tempat Duduk -->
    <div class="card p-4" style="border-top: 3px solid #4f46e5;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <div style="font-size: 32px;">🪑</div>
            <span class="badge" style="background: #eef2ff; color: #4f46e5; font-size: 11px;">6. CETAK</span>
        </div>
        <h3 style="margin-top: 0; font-size: 18px;">Denah Tempat Duduk</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Layout tata letak meja dan denah tempat duduk bersilang per ruang ujian lengkap dengan nomor peserta dan nama siswa.
        </p>
        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 25px;">
            <a href="<?= url('/written-exam/seating/print') ?>" target="_blank" class="btn btn-primary" style="text-align: center; background: #4f46e5; border-color: #4f46e5;">
                🖨️ Cetak Denah Duduk Seluruh Ruang
            </a>
            <a href="<?= url('/written-exam/seating') ?>" class="btn btn-outline" style="text-align: center;">
                ⚙️ Atur Layout Denah Ruang
            </a>
        </div>
    </div>

    <!-- 7. Daftar Hadir (Panitia, Peserta, Pengawas, Penguji Praktik) -->
    <div class="card p-4" style="border-top: 3px solid #10b981;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <div style="font-size: 32px;">✅</div>
            <span class="badge" style="background: #ecfdf5; color: #059669; font-size: 11px;">7. CETAK</span>
        </div>
        <h3 style="margin-top: 0; font-size: 18px;">Daftar Hadir</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Format resmi presensi kehadiran untuk: <strong>1. Panitia</strong>, <strong>2. Peserta Ujian</strong>, <strong>3. Pengawas Ruang</strong>, dan <strong>4. Penguji Praktik</strong>.
        </p>
        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 25px;">
            <a href="<?= url('/print/attendance') ?>" class="btn btn-success" style="text-align: center;">
                📑 Buka Pusat Cetak Daftar Hadir
            </a>
            <a href="<?= url('/attendance/committee/print') ?>" target="_blank" class="btn btn-outline btn-sm" style="text-align: left;">
                • Cetak Presensi Panitia
            </a>
            <a href="<?= url('/attendance/supervisors/print') ?>" target="_blank" class="btn btn-outline btn-sm" style="text-align: left;">
                • Cetak Presensi Pengawas Ruang
            </a>
            <a href="<?= url('/attendance/examiners/print') ?>" target="_blank" class="btn btn-outline btn-sm" style="text-align: left;">
                • Cetak Presensi Penguji Praktik
            </a>
        </div>
    </div>

    <!-- 8. Berita Acara Pelaksanaan Ujian -->
    <div class="card p-4" style="border-top: 3px solid #d97706;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <div style="font-size: 32px;">📝</div>
            <span class="badge" style="background: #fffbeb; color: #b45309; font-size: 11px;">8. CETAK</span>
        </div>
        <h3 style="margin-top: 0; font-size: 18px;">Berita Acara Pelaksanaan Ujian</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Format berita acara resmi pelaksanaan ujian tertulis dan praktik, rekapitulasi kehadiran ruang, dan catatan kejadian khusus.
        </p>
        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 25px;">
            <a href="<?= url('/reports/minutes') ?>" class="btn btn-primary" style="text-align: center; background: #d97706; border-color: #d97706;">
                📜 Buka Rekap Berita Acara
            </a>
            <a href="<?= url('/written-exam/execution') ?>" class="btn btn-outline" style="text-align: center;">
                ✍️ Presensi &amp; Berita Acara Per Ruang
            </a>
        </div>
    </div>

    <!-- 9. LPJ Kegiatan Ujian -->
    <div class="card p-4" style="border-top: 3px solid #dc2626;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <div style="font-size: 32px;">🏆</div>
            <span class="badge" style="background: #fef2f2; color: #dc2626; font-size: 11px;">9. CETAK</span>
        </div>
        <h3 style="margin-top: 0; font-size: 18px;">LPJ Kegiatan Ujian</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Laporan Pertanggungjawaban (LPJ) Akhir Pelaksanaan Ujian struktur 18 Bab komprehensif terintegrasi dengan database kegiatan.
        </p>
        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 25px;">
            <a href="<?= url('/reports/final/print') ?>" target="_blank" class="btn btn-danger" style="text-align: center;">
                🖨️ Cetak Dokumen LPJ Resmi
            </a>
            <a href="<?= url('/reports/final/export-docx') ?>" class="btn btn-secondary" style="text-align: center;">
                📄 Unduh File Word (.docx)
            </a>
            <a href="<?= url('/reports/final') ?>" class="btn btn-outline" style="text-align: center;">
                ⚙️ Editor Bab LPJ
            </a>
        </div>
    </div>
</div>

