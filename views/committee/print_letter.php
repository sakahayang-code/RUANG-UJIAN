<div style="font-size: 11pt; line-height: 1.5; font-family: 'Times New Roman', serif;">
    <!-- Meta Surat -->
    <table style="width: 100%; margin-bottom: 20px; font-size: 11pt;">
        <tr>
            <td style="width: 100px;">Nomor</td>
            <td style="width: 15px;">:</td>
            <td><?= e($letter['letter_number']) ?></td>
            <td style="text-align: right;"><?= e($school['signature_city']) ?>, <?= format_date_id($letter['letter_date']) ?></td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td>-</td>
            <td></td>
        </tr>
        <tr>
            <td>Perihal</td>
            <td>:</td>
            <td><strong><?= e($letter['subject']) ?></strong></td>
            <td></td>
        </tr>
    </table>

    <!-- Kepada Yth -->
    <div style="margin-bottom: 20px;">
        Kepada Yth.<br>
        <strong><?= nl2br(e($letter['target_recipients'] ?: 'Bapak/Ibu Panitia / Dewan Guru')) ?></strong><br>
        di Tempat
    </div>

    <!-- Isi Surat -->
    <div style="text-align: justify; margin-bottom: 16px;">
        <?= nl2br(e($letter['content'])) ?>
    </div>

    <!-- Rincian Acara Rapat jika ada -->
    <?php if (!empty($letter['meeting_date'])): ?>
        <table style="width: 90%; margin: 15px auto 20px; font-size: 11pt;">
            <tr>
                <td style="width: 140px; padding: 3px 0;">Hari / Tanggal</td>
                <td style="width: 15px;">:</td>
                <td><strong><?= format_day_id($letter['meeting_date']) ?>, <?= format_date_id($letter['meeting_date']) ?></strong></td>
            </tr>
            <tr>
                <td style="padding: 3px 0;">Waktu</td>
                <td>:</td>
                <td>Pukul <?= e($letter['meeting_time']) ?> WIB</td>
            </tr>
            <tr>
                <td style="padding: 3px 0;">Tempat</td>
                <td>:</td>
                <td><?= e($letter['meeting_place']) ?></td>
            </tr>
            <tr>
                <td style="padding: 3px 0; vertical-align: top;">Agenda / Acara</td>
                <td style="vertical-align: top;">:</td>
                <td><strong><?= e($letter['meeting_agenda']) ?></strong></td>
            </tr>
        </table>

        <div style="text-align: justify; margin-bottom: 30px;">
            Demikian surat undangan ini kami sampaikan. Atas kehadiran dan kerjasamanya, kami mengucapkan terima kasih.
        </div>
    <?php endif; ?>

    <!-- Tanda Tangan -->
    <div class="signature-block">
        <div></div>
        <div class="signature-box">
            <?= e($school['signature_city'] ?? 'Bogor') ?>, <?= format_date_id($letter['letter_date'] ?? date('Y-m-d')) ?><br>
            <?= e($letter['signer_title'] ?? 'Ketua Panitia') ?>,
            <div class="signature-space"></div>
            <div class="signature-name"><?= e($letter['signer_name'] ?? ($school['committee_head'] ?? 'Panitia Ujian')) ?></div>
            <div>NIP. <?= e(!empty($letter['signer_nip']) ? $letter['signer_nip'] : '-') ?></div>
        </div>
    </div>
</div>
