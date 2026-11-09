<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= e($title ?? 'Masuk') ?> - RUANG UJIAN</title>
    
    <!-- PWA Android Configuration -->
    <link rel="manifest" href="<?= url('/manifest.json') ?>">
    <meta name="theme-color" content="#1e3a8a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="RUANG UJIAN">
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/icons/icon.svg') ?>">
    <link rel="alternate icon" type="image/png" href="<?= asset('images/icons/icon-192.png') ?>">
    <link rel="apple-touch-icon" href="<?= asset('images/icons/icon-192.png') ?>">

    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .auth-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 440px;
            padding: 36px 32px;
        }
        .auth-brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .auth-brand .logo-box {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #2563eb, #1e3a8a);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 26px;
            font-weight: 800;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.4);
            margin-bottom: 12px;
        }
        .auth-brand h1 {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .auth-brand p {
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-brand">
        <div class="logo-box">RU</div>
        <h1>RUANG UJIAN</h1>
        <p>Sistem Administrasi Ujian Sekolah Terpadu</p>
    </div>

    <?php if ($msg = get_flash('error')): ?>
        <div class="alert alert-error">
            <span class="alert-icon">✕</span>
            <div><?= e($msg) ?></div>
        </div>
    <?php endif; ?>

    <?php if ($msg = get_flash('success')): ?>
        <div class="alert alert-success">
            <span class="alert-icon">✓</span>
            <div><?= e($msg) ?></div>
        </div>
    <?php endif; ?>

    <?= $content ?? '' ?>
</div>

</body>
</html>
