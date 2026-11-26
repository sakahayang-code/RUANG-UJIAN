<div class="no-print" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center; justify-content: space-between; background: #ffffff; padding: 12px 18px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.08); flex-wrap: wrap;">
    <div style="display: flex; gap: 8px; align-items: center;">
        <span style="font-size: 13px; font-weight: 700; color: #334155;">Pilihan Jadwal:</span>
        <a href="<?= url('/print/schedules?type=written') ?>" class="btn btn-sm <?= $type !== 'practical' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 12px;">
            📝 Jadwal Ujian Tulis
        </a>
        <a href="<?= url('/print/schedules?type=practical') ?>" class="btn btn-sm <?= $type === 'practical' ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 12px;">
            🧪 Jadwal Ujian Praktik
        </a>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" onclick="window.print()" class="btn btn-sm btn-primary" style="font-size: 12px; font-weight: 700;">
            🖨️ Cetak Jadwal (Print)
        </button>
        <a href="<?= url('/print') ?>" class="btn btn-sm btn-outline" style="font-size: 12px;">
            ← Kembali ke Pusat Cetak
        </a>
    </div>
</div>

<div class="doc-title">
    <h2><?= $type === 'practical' ? 'JADWAL PELAKSANAAN UJIAN PRAKTIK' : 'JADWAL PELAKSANAAN UJIAN TERTULIS' ?></h2>
    <p><?= e($event['name']) ?> • TAHUN PELAJARAN <?= e($event['academic_year'] ?? $school['academic_year']) ?></p>
</div>

<?php if ($type === 'practical'): ?>
    <!-- Jadwal Ujian Praktik Table -->
    <table class="table-print" style="margin-top: 15px; margin-bottom: 30px;">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 140px;">Hari &amp; Tanggal</th>
                <th style="width: 110px;">Waktu</th>
                <th>Mata Pelajaran</th>
                <th style="width: 80px;">Tingkat</th>
                <th>Tempat / Laboratorium</th>
                <th>Stasiun / Pos</th>
                <th>Penguji Ditugaskan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($practicalSchedules)): ?>
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #64748b;">
                        Belum ada jadwal sesi ujian praktik yang dibuat.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($practicalSchedules as $idx => $ps): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td>
                            <strong><?= format_day_id($ps['exam_date']) ?></strong><br>
                            <span style="font-size: 9pt; color: #334155;"><?= format_date_id($ps['exam_date']) ?></span>
                        </td>
                        <td class="text-center font-bold">
                            <?= e($ps['start_time']) ?> - <?= e($ps['end_time']) ?> WIB
                        </td>
                        <td>
                            <strong><?= e($ps['subject_name']) ?></strong> (<code><?= e($ps['subject_code']) ?></code>)
                        </td>
                        <td class="text-center">Kelas <?= e($ps['grade']) ?></td>
                        <td><?= e($ps['location']) ?></td>
                        <td class="text-center"><?= e($ps['station'] ?: 'Pos 1') ?></td>
                        <td style="font-size: 9pt;"><?= e($ps['examiners_list'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
<?php else: ?>
    <!-- Jadwal Ujian Tulis Table -->
    <table class="table-print" style="margin-top: 15px; margin-bottom: 30px;">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 150px;">Hari &amp; Tanggal</th>
                <th style="width: 70px;">Sesi</th>
                <th style="width: 130px;">Waktu (WIB)</th>
                <th>Mata Pelajaran</th>
                <th style="width: 90px;">Tingkat</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($writtenSchedules)): ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #64748b;">
                        Belum ada jadwal sesi ujian tertulis yang dibuat.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($writtenSchedules as $idx => $ws): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td>
                            <strong><?= format_day_id($ws['exam_date']) ?></strong><br>
                            <span style="font-size: 9pt; color: #334155;"><?= format_date_id($ws['exam_date']) ?></span>
                        </td>
                        <td class="text-center">Sesi <?= e($ws['session']) ?></td>
                        <td class="text-center font-bold">
                            <?= e($ws['start_time']) ?> - <?= e($ws['end_time']) ?> WIB
                        </td>
                        <td>
                            <strong><?= e($ws['subject_name']) ?></strong> (<code><?= e($ws['subject_code']) ?></code>)
                        </td>
                        <td class="text-center">Kelas <?= e($ws['grade']) ?></td>
                        <td><?= e($ws['notes'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>

<!-- Tanda Tangan Pengesahan -->
<div class="signature-block">
    <div class="signature-box">
        Mengetahui,<br>
        Kepala <?= e($school['name']) ?>,
        <div class="signature-space"></div>
        <div class="signature-name"><?= e($school['principal_name']) ?></div>
        <div>NIP. <?= e($school['principal_nip'] ?: '-') ?></div>
    </div>

    <div class="signature-box">
        <span style="white-space: nowrap;"><?= e(!empty($school['signature_city']) ? $school['signature_city'] : ($school['district'] ?? ($school['regency'] ?? 'Tempat'))) ?>, <?= format_date_id(date('Y-m-d')) ?></span><br>
        Ketua Panitia Ujian,
        <div class="signature-space" style="display: flex; align-items: center; justify-content: center;">
            <?php if (!empty($school['committee_signature_url'])): ?>
                <img src="<?= $school['committee_signature_url'] ?>" alt="Tanda Tangan" style="max-height: 65px; max-width: 140px; object-fit: contain;">
            <?php endif; ?>
        </div>
        <div class="signature-name">
            <?= e($chair['teacher_name'] ?? $chair['custom_name'] ?? $school['committee_head'] ?? '..................................') ?>
        </div>
        <div>NIP. <?= e($chair['teacher_nip'] ?? $chair['custom_nip'] ?? '-') ?></div>
    </div>
</div>
