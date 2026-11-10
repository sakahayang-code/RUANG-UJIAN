<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= e($title ?? 'Masuk') ?> - RUANG UJIAN</title>
    
    <!-- PWA Android Configuration -->
    <link rel="manifest" href="<?= url('/manifest.json') ?>">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="RUANG UJIAN">
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/icons/icon.svg') ?>">
    <link rel="alternate icon" type="image/png" href="<?= asset('images/icons/icon-192.png') ?>">
    <link rel="apple-touch-icon" href="<?= asset('images/icons/icon-192.png') ?>">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: 
                radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.35) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(236, 72, 153, 0.3) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.15) 0%, transparent 60%),
                linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 22px;
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 
                0 25px 50px -12px rgba(15, 23, 42, 0.45),
                0 0 0 1px rgba(99, 102, 241, 0.15);
            width: 100%;
            max-width: 440px;
            padding: 38px 32px;
            position: relative;
            z-index: 10;
        }
        .auth-brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .auth-brand .logo-box {
            width: 72px;
            height: 72px;
            background: transparent;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 28px -3px rgba(79, 70, 229, 0.45);
            margin-bottom: 14px;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .auth-brand .logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 20px;
        }
        .auth-brand .logo-box:hover {
            transform: scale(1.08) rotate(3deg);
        }
        .auth-brand h1 {
            font-size: 23px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
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
        <div class="logo-box">
            <img src="<?= asset('images/icons/icon.svg') ?>" alt="Logo RUANG UJIAN">
        </div>
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
