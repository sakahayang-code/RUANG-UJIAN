<?php
$school = school_setting();
$activeEvent = active_event();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Cetak Dokumen') ?> - <?= e($school['name']) ?></title>
    <link rel="stylesheet" href="<?= asset('css/print.css') ?>">
    <style>
        .print-container {
            max-width: 960px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
        }
    </style>
</head>
<body class="print-body">

<div class="print-toolbar no-print">
    <div style="font-weight: 600; font-size: 14px; font-family: sans-serif;">
        🖨️ <?= e($title ?? 'Pratinjau Cetak Dokumen') ?> | <?= e($school['name']) ?>
    </div>
    <div style="display: flex; gap: 10px;">
        <button onclick="window.print()" class="btn btn-primary" style="background: #2563eb; color: #fff; padding: 7px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
            🖨️ Cetak / Simpan PDF
        </button>
        <button onclick="window.close(); if(!window.closed) history.back();" class="btn btn-secondary" style="background: #475569; color: #fff; padding: 7px 14px; border: none; border-radius: 4px; cursor: pointer;">
            ✕ Tutup / Kembali
        </button>
    </div>
</div>

<div class="print-container">
    <?php if (empty($hideKop)): ?>
        <?php if (!empty($school['letterhead_image_path']) && file_exists(dirname(__DIR__, 2) . '/storage/uploads/' . $school['letterhead_image_path'])): ?>
            <!-- Official Kop Surat Bergambar -->
            <div class="kop-surat-image" style="width: 100%; text-align: center; margin-bottom: 18px;">
                <img src="<?= url('/file/view?path=' . urlencode($school['letterhead_image_path'])) ?>" alt="Kop Surat <?= e($school['name']) ?>" style="width: 100%; max-height: 180px; object-fit: contain; display: block; margin: 0 auto;">
            </div>
        <?php else: ?>
            <!-- Official Kop Surat Teks & Logo -->
            <div class="kop-surat">
                <?php if (!empty($school['logo_path']) && file_exists(dirname(__DIR__, 2) . '/storage/uploads/' . $school['logo_path'])): ?>
                    <img src="<?= url('/file/view?path=' . urlencode($school['logo_path'])) ?>" alt="Logo" class="kop-logo">
                <?php else: ?>
                    <div style="width: 75px; height: 75px; border: 2px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16pt;">
                        RU
                    </div>
                <?php endif; ?>

                <div class="kop-content">
                    <div class="kop-agency"><?= nl2br(e($school['letterhead_text'] ?? 'PEMERINTAH DAERAH\nDINAS PENDIDIKAN')) ?></div>
                    <div class="kop-school"><?= e($school['name']) ?></div>
                    <div class="kop-address">
                        <?= e($school['address']) ?>, Desa/Kel. <?= e($school['village']) ?>, Kec. <?= e($school['district']) ?>, <?= e($school['regency']) ?> - <?= e($school['province']) ?> <?= e($school['postal_code']) ?><br>
                        Telp: <?= e($school['phone']) ?> | Email: <?= e($school['email']) ?> | Website: <?= e($school['website']) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Document Body -->
    <?= $content ?? '' ?>
</div>

</body>
</html>
