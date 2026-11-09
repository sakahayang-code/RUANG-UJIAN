<div style="font-size: 11pt; line-height: 1.4; font-family: 'Times New Roman', serif;">
    <div class="doc-title">
        <h2>DAFTAR HADIR PANITIA PENYELENGGARA UJIAN</h2>
        <p><?= e($event['name']) ?> - TAHUN PELAJARAN <?= e($event['academic_year']) ?></p>
        <div style="font-size: 10.5pt; font-weight: bold; margin-top: 4px;">
            Hari / Tanggal: <?= format_day_id($date) ?>, <?= format_date_id($date) ?>
        </div>
    </div>

    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Nama Panitia</th>
                <th style="width: 160px;">Jabatan / Seksi</th>
                <th style="width: 90px;">Jam Datang</th>
                <th style="width: 90px;">Jam Pulang</th>
                <th style="width: 140px;">Tanda Tangan</th>
                <th style="width: 130px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $idx => $m): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td>
                        <strong><?= e($m['teacher_name'] ?: $m['custom_name']) ?></strong><br>
                        <span style="font-size: 9pt;">NIP. <?= e($m['teacher_nip'] ?: $m['custom_nip'] ?: '-') ?></span>
                    </td>
                    <td>
                        <?= e($m['role_title']) ?>
                        <?php if ($m['section_name']): ?>
                            <br><span style="font-size: 9pt;"><?= e($m['section_name']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?= $isBlank ? '.... : ....' : ($m['check_in_time'] ?: '07:00') ?>
                    </td>
                    <td class="text-center">
                        <?= $isBlank ? '.... : ....' : ($m['check_out_time'] ?: '15:30') ?>
                    </td>
                    <td style="font-size: 8.5pt; color: #444; height: 38px; vertical-align: middle;">
                        <?php if ($isBlank): ?>
                            <?= $idx + 1 ?>. ....................
                        <?php else: ?>
                            <div style="font-weight: bold; color: #059669; font-size: 9pt;">(Telah Hadir)</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $isBlank ? '' : e($m['attendance_notes'] ?: '-') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
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
            <?= e($school['signature_city']) ?>, <?= format_date_id($date) ?><br>
            Ketua Panitia Ujian,
            <div class="signature-space"></div>
            <div class="signature-name"><?= e($school['committee_head'] ?: 'Ketua Panitia') ?></div>
            <div>NIP. -</div>
        </div>
    </div>
</div>
