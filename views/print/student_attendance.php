<div class="no-print" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center; justify-content: space-between; background: #ffffff; padding: 12px 18px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.08); flex-wrap: wrap;">
    <div style="font-size: 13px; font-weight: 700; color: #334155;">
        Ruang <?= e($room['room_number'] ?? '-') ?> • <?= e($schedule['subject_name'] ?? 'Mata Pelajaran') ?> (Sesi <?= e($schedule['session'] ?? '1') ?>)
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" onclick="window.print()" class="btn btn-sm btn-primary" style="font-size: 12px; font-weight: 700;">
            🖨️ Cetak Presensi Peserta (Print)
        </button>
        <a href="<?= url('/print/attendance') ?>" class="btn btn-sm btn-outline" style="font-size: 12px;">
            ← Kembali ke Pusat Cetak Daftar Hadir
        </a>
    </div>
</div>

<div class="doc-title">
    <h2>DAFTAR HADIR PESERTA UJIAN TERTULIS</h2>
    <p><?= e($event['name']) ?> • TAHUN PELAJARAN <?= e($event['academic_year'] ?? $school['academic_year']) ?></p>
</div>

<table style="width: 100%; margin-bottom: 15px; font-size: 10pt; line-height: 1.5;">
    <tr>
        <td style="width: 150px; font-weight: 600;">Mata Pelajaran</td>
        <td style="width: 15px;">:</td>
        <td style="width: 45%;"><strong><?= e($schedule['subject_name'] ?? '-') ?></strong> (Kode: <?= e($schedule['subject_code'] ?? '-') ?>)</td>
        <td style="width: 130px; font-weight: 600;">Hari / Tanggal</td>
        <td style="width: 15px;">:</td>
        <td><strong><?= format_day_id($schedule['exam_date'] ?? date('Y-m-d')) ?></strong>, <?= format_date_id($schedule['exam_date'] ?? date('Y-m-d')) ?></td>
    </tr>
    <tr>
        <td style="font-weight: 600;">Ruang Ujian</td>
        <td>:</td>
        <td><strong>Ruang <?= e($room['room_number'] ?? '-') ?></strong> (<?= e($room['name'] ?? '-') ?>)</td>
        <td style="font-weight: 600;">Sesi / Waktu</td>
        <td>:</td>
        <td>Sesi <?= e($schedule['session'] ?? '1') ?> (Pukul <?= e($schedule['start_time'] ?? '-') ?> s.d. <?= e($schedule['end_time'] ?? '-') ?> WIB)</td>
    </tr>
</table>

<table class="table-print" style="margin-top: 10px; margin-bottom: 25px;">
    <thead>
        <tr>
            <th style="width: 35px;">No</th>
            <th style="width: 130px;">No. Peserta</th>
            <th style="width: 90px;">NISN</th>
            <th>Nama Lengkap Peserta</th>
            <th style="width: 70px;">Kelas</th>
            <th style="width: 50px;">Meja</th>
            <th colspan="2" style="width: 170px;">Tanda Tangan</th>
            <th style="width: 90px;">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($participants)): ?>
            <tr>
                <td colspan="9" class="text-center" style="padding: 25px; color: #64748b;">
                    Belum ada data peserta yang terdaftar pada ruang ini.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($participants as $idx => $p): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td class="text-center font-bold"><code><?= e($p['participant_number'] ?: '-') ?></code></td>
                    <td class="text-center"><?= e($p['nisn'] ?: '-') ?></td>
                    <td><strong><?= e($p['student_name_snapshot'] ?: $p['name'] ?? '-') ?></strong></td>
                    <td class="text-center"><?= e($p['class_name'] ?: '-') ?></td>
                    <td class="text-center font-bold"><?= e($p['seat_number'] ?: '-') ?></td>
                    
                    <!-- Kolom Tanda Tangan Zig-zag (Ganjil / Genap) -->
                    <?php if (($idx + 1) % 2 === 1): ?>
                        <td style="width: 85px; font-size: 8.5pt; vertical-align: bottom; height: 28px;">
                            <?= ($idx + 1) ?>. ............
                        </td>
                        <td style="width: 85px;">&nbsp;</td>
                    <?php else: ?>
                        <td style="width: 85px;">&nbsp;</td>
                        <td style="width: 85px; font-size: 8.5pt; vertical-align: bottom; height: 28px;">
                            <?= ($idx + 1) ?>. ............
                        </td>
                    <?php endif; ?>

                    <td class="text-center" style="font-size: 9pt;">
                        <?php if (($p['attendance_status'] ?? '') === 'tidak_hadir'): ?>
                            <span style="color: #dc2626; font-weight: bold;">TIDAK HADIR</span>
                        <?php elseif (($p['attendance_status'] ?? '') === 'hadir'): ?>
                            <span>Hadir</span>
                        <?php else: ?>
                            &nbsp;
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Catatan & Tanda Tangan Pengawas Ruang -->
<table style="width: 100%; margin-top: 10px; font-size: 10pt;">
    <tr>
        <td style="width: 50%; vertical-align: top;">
            <div style="border: 1px solid #000; padding: 10px; min-height: 80px; width: 90%;">
                <strong>Catatan Rekapitulasi:</strong><br>
                Jumlah Peserta Seharusnya : <?= count($participants) ?> Orang<br>
                Jumlah Peserta Hadir : ............ Orang<br>
                Jumlah Peserta Tidak Hadir : ............ Orang
            </div>
        </td>
        <td style="width: 50%; vertical-align: top; text-align: right;">
            <div style="text-align: center; display: inline-block; min-width: 260px;">
                <span style="white-space: nowrap;"><?= e(!empty($school['signature_city']) ? $school['signature_city'] : ($school['district'] ?? ($school['regency'] ?? 'Tempat'))) ?>, <?= format_date_id($schedule['exam_date'] ?? date('Y-m-d')) ?></span><br>
                Pengawas Ruang Ujian,
                <div style="height: 60px;"></div>
                <div style="font-weight: 700; text-decoration: underline;">
                    <?= e($supervisors['sup1_name'] ?? '..........................................') ?>
                </div>
                <div>NIP. <?= e($supervisors['sup1_nip'] ?? '..........................................') ?></div>
            </div>
        </td>
    </tr>
</table>
