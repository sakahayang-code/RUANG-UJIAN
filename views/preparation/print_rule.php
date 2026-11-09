<div style="font-size: 11pt; line-height: 1.6; font-family: 'Times New Roman', serif;">
    <div class="doc-title">
        <h2><?= strtoupper(e($rule['title'])) ?></h2>
        <p><?= e($event['name']) ?> - TAHUN PELAJARAN <?= e($event['academic_year']) ?></p>
    </div>

    <div style="text-align: justify; margin: 25px 0;">
        <?= nl2br(e($rule['content'])) ?>
    </div>

    <div class="signature-block">
        <div></div>
        <div class="signature-box">
            <?= e($school['signature_city']) ?>, <?= format_date_id($event['start_date']) ?><br>
            Kepala Sekolah,
            <div class="signature-space"></div>
            <div class="signature-name"><?= e($school['principal_name']) ?></div>
            <div>NIP. <?= e($school['principal_nip']) ?></div>
        </div>
    </div>
</div>
