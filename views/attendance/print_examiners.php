<div style="font-size: 11pt; line-height: 1.4; font-family: 'Times New Roman', serif;">
    <div class="doc-title">
        <h2>DAFTAR HADIR DEWAN PENGUJI UJIAN PRAKTIK</h2>
        <p><?= e($event['name']) ?> - TAHUN PELAJARAN <?= e($event['academic_year']) ?></p>
    </div>

    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th style="width: 100px;">Hari & Tanggal</th>
                <th style="width: 85px;">Waktu</th>
                <th>Mata Pelajaran</th>
                <th>Lokasi & Stasiun</th>
                <th>Nama Penguji</th>
                <th style="width: 110px;">Peran</th>
                <th style="width: 100px;">Tanda Tangan</th>
                <th style="width: 80px;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">Belum ada penugasan penguji praktik.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($records as $idx => $r): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td>
                            <?= format_day_id($r['exam_date']) ?><br>
                            <span style="font-size: 9pt;"><?= format_date_id($r['exam_date']) ?></span>
                        </td>
                        <td class="text-center"><?= e($r['start_time']) ?> - <?= e($r['end_time']) ?></td>
                        <td><strong><?= e($r['subject_name']) ?></strong></td>
                        <td><?= e($r['location']) ?> (<?= e($r['station'] ?: 'Semua') ?>)</td>
                        <td>
                            <strong><?= e($r['examiner_name']) ?></strong><br>
                            <span style="font-size: 8.5pt;">NIP. <?= e($r['examiner_nip'] ?: '-') ?></span>
                        </td>
                        <td><?= e($r['examiner_role']) ?></td>
                        <td style="font-size: 8.5pt; color: #444; height: 36px; vertical-align: middle;">
                            <?php if (($r['attendance_status'] ?? '') === 'hadir'): ?>
                                <div style="font-weight: bold; color: #059669;">(Hadir: <?= e($r['check_in_time'] ?: '07:30') ?>)</div>
                            <?php else: ?>
                                <?= $idx + 1 ?>. ....................
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= ucfirst(e($r['attendance_status'] ?: 'Hadir')) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="signature-block">
        <div class="signature-box">
            Mengetahui,<br>
            Kepala <?= e($school['name']) ?>
            <div class="signature-space"></div>
            <strong><u><?= e($school['principal_name']) ?></u></strong><br>
            NIP. <?= e($school['principal_nip'] ?: '-') ?>
        </div>
        <div class="signature-box">
            <?= e($school['district'] ?? 'Ditetapkan') ?>, <?= format_date_id(date('Y-m-d')) ?><br>
            Ketua Panitia Ujian
            <div class="signature-space"></div>
            <strong><u><?= e($school['committee_head'] ?: 'Ketua Panitia') ?></u></strong><br>
            NIP. -
        </div>
    </div>
</div>
