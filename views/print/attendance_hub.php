<div class="page-header">
    <div>
        <h1 class="page-title">Pusat Cetak Daftar Hadir</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Cetak formulir dan rekapitulasi presensi kehadiran resmi seluruh unsur kegiatan ujian.</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('/print') ?>" class="btn btn-outline">
            ← Kembali ke Pusat Cetak
        </a>
    </div>
</div>

<div class="row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
    <!-- 1. Daftar Hadir Panitia -->
    <div class="card p-4" style="border-top: 3px solid #2563eb;">
        <div style="font-size: 32px; margin-bottom: 10px;">👥</div>
        <h3 style="margin-top: 0; font-size: 18px;">1. Daftar Hadir Panitia</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Format rekapitulasi presensi kehadiran panitia pelaksana ujian per tanggal kegiatan lengkap dengan jabatan dan kolom tanda tangan.
        </p>
        <div style="margin-top: 20px;">
            <a href="<?= url('/attendance/committee/print') ?>" target="_blank" class="btn btn-primary btn-block" style="width: 100%; text-align: center;">
                🖨️ Cetak Presensi Panitia
            </a>
            <a href="<?= url('/attendance/committee') ?>" class="btn btn-outline btn-block" style="width: 100%; text-align: center; margin-top: 8px;">
                ⚙️ Kelola Kehadiran Panitia
            </a>
        </div>
    </div>

    <!-- 2. Daftar Hadir Peserta Ujian -->
    <div class="card p-4" style="border-top: 3px solid #059669;">
        <div style="font-size: 32px; margin-bottom: 10px;">👨‍🎓</div>
        <h3 style="margin-top: 0; font-size: 18px;">2. Daftar Hadir Peserta Ujian</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Daftar hadir peserta per ruang dan sesi ujian tertulis dengan kolom paraf ganjil-genap untuk pengawasan ruang.
        </p>
        <form method="GET" action="<?= url('/print/student-attendance') ?>" target="_blank" style="margin-top: 15px;">
            <div class="form-group mb-2">
                <label style="font-size: 11px; font-weight: 600;">Pilih Ruang Ujian:</label>
                <select name="room_id" class="form-control" style="font-size: 12px; padding: 6px 10px;" required>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?= $r['id'] ?>">Ruang <?= e($r['room_number']) ?> (<?= e($r['name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-3">
                <label style="font-size: 11px; font-weight: 600;">Pilih Sesi / Mata Pelajaran:</label>
                <select name="schedule_id" class="form-control" style="font-size: 12px; padding: 6px 10px;" required>
                    <?php foreach ($schedules as $s): ?>
                        <option value="<?= $s['id'] ?>">
                            <?= format_date_id($s['exam_date']) ?> - Sesi <?= e($s['session']) ?> (<?= e($s['subject_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-success btn-block" style="width: 100%;">
                🖨️ Cetak Presensi Peserta Ruang
            </button>
        </form>
    </div>

    <!-- 3. Daftar Hadir Pengawas Ruang -->
    <div class="card p-4" style="border-top: 3px solid #0284c7;">
        <div style="font-size: 32px; margin-bottom: 10px;">👀</div>
        <h3 style="margin-top: 0; font-size: 18px;">3. Daftar Hadir Pengawas Ruang</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Daftar hadir pengawas ruang utama dan pendamping per sesi dan ruang ujian, lengkap dengan jam hadir dan paraf.
        </p>
        <div style="margin-top: 20px;">
            <a href="<?= url('/attendance/supervisors/print') ?>" target="_blank" class="btn btn-primary btn-block" style="width: 100%; text-align: center;">
                🖨️ Cetak Presensi Pengawas
            </a>
            <a href="<?= url('/attendance/supervisors') ?>" class="btn btn-outline btn-block" style="width: 100%; text-align: center; margin-top: 8px;">
                ⚙️ Kelola Kehadiran Pengawas
            </a>
        </div>
    </div>

    <!-- 4. Daftar Hadir Penguji Praktik -->
    <div class="card p-4" style="border-top: 3px solid #7c3aed;">
        <div style="font-size: 32px; margin-bottom: 10px;">🩺</div>
        <h3 style="margin-top: 0; font-size: 18px;">4. Daftar Hadir Penguji Praktik</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Presensi kehadiran guru penguji ujian praktik per stasiun, laboratorium, dan tanggal pengujian kecakapan.
        </p>
        <div style="margin-top: 20px;">
            <a href="<?= url('/attendance/examiners/print') ?>" target="_blank" class="btn btn-primary btn-block" style="width: 100%; text-align: center; background: #7c3aed; border-color: #7c3aed;">
                🖨️ Cetak Presensi Penguji
            </a>
            <a href="<?= url('/attendance/examiners') ?>" class="btn btn-outline btn-block" style="width: 100%; text-align: center; margin-top: 8px;">
                ⚙️ Kelola Kehadiran Penguji
            </a>
        </div>
    </div>
</div>
