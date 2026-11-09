<div style="font-size: 11pt; line-height: 1.5; font-family: 'Times New Roman', serif;">
    <div class="doc-title">
        <h2>BERITA ACARA PELAKSANAAN UJIAN TERTULIS</h2>
        <p><?= e($event['name']) ?> TAHUN PELAJARAN <?= e($event['academic_year']) ?></p>
    </div>

    <div style="text-align: justify; margin-bottom: 16px;">
        Pada hari ini <strong><?= format_day_id($schedule['exam_date']) ?></strong>, tanggal <strong><?= format_date_id($schedule['exam_date']) ?></strong>, telah diselenggarakan kegiatan <strong><?= e($event['name']) ?></strong> di <strong><?= e($school['name']) ?></strong> dengan rincian pelaksanaan sebagai berikut:
    </div>

    <!-- Data Pelaksanaan Ruang -->
    <table style="width: 100%; margin-bottom: 20px; font-size: 11pt;">
        <tr>
            <td style="width: 220px; padding: 3px 0;">1. Mata Pelajaran</td>
            <td style="width: 15px;">:</td>
            <td><strong><?= e($schedule['subject_name']) ?></strong> (Kode: <?= e($schedule['subject_code']) ?>)</td>
        </tr>
        <tr>
            <td style="padding: 3px 0;">2. Ruang Ujian</td>
            <td>:</td>
            <td><strong><?= e($room['room_number']) ?></strong> (<?= e($room['name']) ?>)</td>
        </tr>
        <tr>
            <td style="padding: 3px 0;">3. Sesi / Waktu Pelaksanaan</td>
            <td>:</td>
            <td>Sesi <?= e($schedule['session']) ?> (Pukul <?= e($schedule['start_time']) ?> s.d. <?= e($schedule['end_time']) ?> WIB)</td>
        </tr>
        <tr>
            <td style="padding: 3px 0;">4. Jumlah Peserta Seharusnya</td>
            <td>:</td>
            <td><strong><?= (int)($minutes['present_count'] ?? 0) + (int)($minutes['absent_count'] ?? 0) ?> Orang</strong></td>
        </tr>
        <tr>
            <td style="padding: 3px 0;">5. Jumlah Peserta Hadir</td>
            <td>:</td>
            <td><strong><?= (int)($minutes['present_count'] ?? 0) ?> Orang</strong></td>
        </tr>
        <tr>
            <td style="padding: 3px 0;">6. Jumlah Peserta Tidak Hadir</td>
            <td>:</td>
            <td><strong><?= (int)($minutes['absent_count'] ?? 0) ?> Orang</strong></td>
        </tr>
        <tr>
            <td style="padding: 3px 0; vertical-align: top;">7. Catatan Pelaksanaan Pengawas</td>
            <td style="vertical-align: top;">:</td>
            <td style="font-style: italic;"><?= nl2br(e($minutes['notes'] ?: 'Ujian berjalan tertib, aman, dan terkendali.')) ?></td>
        </tr>
    </table>

    <!-- Kejadian Khusus -->
    <div style="font-weight: bold; margin-bottom: 6px;">8. Catatan Kejadian Khusus Selama Ujian Berlangsung:</div>
    <?php if (empty($incidents)): ?>
        <div style="font-style: italic; color: #555; margin-left: 20px; margin-bottom: 20px;">
            - Tidak ada kejadian khusus atau pelanggaran tata tertib selama ujian berlangsung.
        </div>
    <?php else: ?>
        <table class="table-print" style="margin-bottom: 25px;">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th style="width: 80px;">Waktu</th>
                    <th>Uraian Kejadian Khusus</th>
                    <th>Tindak Lanjut / Penyelesaian</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incidents as $idx => $inc): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td class="text-center"><?= e($inc['incident_time']) ?> WIB</td>
                        <td><?= e($inc['incident_description']) ?></td>
                        <td><?= e($inc['follow_up'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div style="text-align: justify; margin-bottom: 30px;">
        Demikian Berita Acara ini dibuat dengan sebenarnya dengan penuh rasa tanggung jawab untuk dapat dipergunakan sebagaimana mestinya.
    </div>

    <!-- Tanda Tangan Pengawas Ruang -->
    <div class="signature-block">
        <div class="signature-box">
            Pengawas Ruang 2,
            <div class="signature-space"></div>
            <div class="signature-name"><?= e($supervisors['sup2_name'] ?: '..........................................') ?></div>
            <div>NIP. <?= e($supervisors['sup2_nip'] ?: '..........................................') ?></div>
        </div>

        <div class="signature-box">
            <?= e($school['signature_city']) ?>, <?= format_date_id($schedule['exam_date']) ?><br>
            Pengawas Ruang 1 (Utama),
            <div class="signature-space"></div>
            <div class="signature-name"><?= e($supervisors['sup1_name'] ?: '..........................................') ?></div>
            <div>NIP. <?= e($supervisors['sup1_nip'] ?: '..........................................') ?></div>
        </div>
    </div>
</div>
