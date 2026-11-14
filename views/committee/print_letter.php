<div style="font-size: 11pt; line-height: 1.5; font-family: 'Times New Roman', serif;">
    <!-- Meta Surat & Tanggal Surat -->
    <table style="width: 100%; margin-bottom: 20px; font-size: 11pt; border-collapse: collapse;">
        <tr>
            <td style="vertical-align: top; width: 62%;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11pt;">
                    <tr>
                        <td style="width: 80px; vertical-align: top; padding: 1px 0;">Nomor</td>
                        <td style="width: 15px; vertical-align: top; text-align: center; padding: 1px 0;">:</td>
                        <td style="vertical-align: top; padding: 1px 0;"><?= e($letter['letter_number']) ?></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 1px 0;">Lampiran</td>
                        <td style="vertical-align: top; text-align: center; padding: 1px 0;">:</td>
                        <td style="vertical-align: top; padding: 1px 0;">-</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top; padding: 1px 0;">Perihal</td>
                        <td style="vertical-align: top; text-align: center; padding: 1px 0;">:</td>
                        <td style="vertical-align: top; padding: 1px 0;"><strong><?= e($letter['subject']) ?></strong></td>
                    </tr>
                </table>
            </td>
            <td style="vertical-align: top; text-align: right; width: 38%; white-space: nowrap; font-size: 11pt; padding: 1px 0;">
                <?= e(!empty($school['signature_city']) ? $school['signature_city'] : ($school['district'] ?? ($school['regency'] ?? 'Tempat'))) ?>, <?= format_date_id($letter['letter_date'] ?? date('Y-m-d')) ?>
            </td>
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
        <div class="signature-box" style="text-align: center; min-width: 220px;">
            <div style="white-space: nowrap;"><?= e(!empty($school['signature_city']) ? $school['signature_city'] : ($school['district'] ?? ($school['regency'] ?? 'Tempat'))) ?>, <?= format_date_id($letter['letter_date'] ?? date('Y-m-d')) ?></div>
            <?= e($letter['signer_title'] ?? 'Ketua Panitia') ?>,
            <div class="signature-space"></div>
            <div class="signature-name"><?= e($letter['signer_name'] ?? ($school['committee_head'] ?? 'Panitia Ujian')) ?></div>
            <div>NIP. <?= e(!empty($letter['signer_nip']) ? $letter['signer_nip'] : '-') ?></div>
        </div>
    </div>
</div>
