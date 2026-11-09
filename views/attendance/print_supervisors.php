<div style="font-size: 11pt; line-height: 1.4; font-family: 'Times New Roman', serif;">
    <div class="doc-title">
        <h2>DAFTAR HADIR PENGAWAS RUANG UJIAN</h2>
        <p><?= e($event['name']) ?> - TAHUN PELAJARAN <?= e($event['academic_year']) ?></p>
        <?php if (!empty($dateFilter)): ?>
            <div style="font-weight: bold; margin-top: 4px;">
                Hari / Tanggal: <?= format_day_id($dateFilter) ?>, <?= format_date_id($dateFilter) ?>
            </div>
        <?php endif; ?>
    </div>

    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Hari / Tanggal</th>
                <th style="width: 50px;">Sesi</th>
                <th style="width: 70px;">Ruang</th>
                <th>Mata Pelajaran</th>
                <th>Nama Pengawas Ruang</th>
                <th style="width: 140px;">Tanda Tangan</th>
                <th style="width: 110px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">Belum ada penugasan pengawas ruang.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($records as $idx => $r): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td>
                            <?= format_day_id($r['exam_date']) ?><br>
                            <span style="font-size: 9pt;"><?= format_date_id($r['exam_date']) ?></span>
                        </td>
                        <td class="text-center">Sesi <?= e($r['session']) ?></td>
                        <td class="text-center"><strong><?= e($r['room_number']) ?></strong></td>
                        <td><strong><?= e($r['subject_name']) ?></strong></td>
                        <td>
                            <strong>1. <?= e($r['sup1_name'] ?: '................................') ?></strong>
                            <?php if ($r['sup2_name']): ?>
                                <br>2. <?= e($r['sup2_name']) ?>
                            <?php endif; ?>
                        </td>
                        <td style="height: 38px; vertical-align: middle;">
                            <?php if ($isBlank): ?>
                                <?= $idx + 1 ?>. ....................
                            <?php else: ?>
                                <div style="font-weight: bold; color: #059669; font-size: 9pt;">(Telah Hadir - <?= e($r['check_in_time'] ?: '07:15') ?>)</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $isBlank ? '' : e($r['attendance_notes'] ?: '-') ?>
                        </td>
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
            <div class="signature-name"><?= e($school['principal_name']) ?></div>
            <div>NIP. <?= e($school['principal_nip']) ?></div>
        </div>

        <div class="signature-box">
            <?= e($school['signature_city']) ?>, <?= format_date_id($dateFilter ?: $event['start_date']) ?><br>
            Ketua Panitia Ujian,
            <div class="signature-space"></div>
            <div class="signature-name"><?= e($school['committee_head'] ?: 'Ketua Panitia') ?></div>
            <div>NIP. -</div>
        </div>
    </div>
</div>
