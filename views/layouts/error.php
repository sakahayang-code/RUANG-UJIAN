<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Pemberitahuan Sistem') ?> - RUANG UJIAN</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        body {
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-card {
            background: #fff;
            border-radius: var(--radius-lg);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
        }
        .error-code {
            font-size: 56px;
            font-weight: 900;
            color: #dc2626;
            line-height: 1;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>

<div class="error-card">
    <div class="error-code">⚠️</div>
    <h2 style="font-size: 20px; margin-bottom: 8px; color: #0f172a;"><?= e($title ?? 'Terjadi Kesalahan') ?></h2>
    <p style="color: #64748b; font-size: 14px; margin-bottom: 24px;"><?= e($message ?? 'Halaman yang Anda tuju tidak dapat diakses atau sedang mengalami gangguan.') ?></p>
    <a href="<?= url('/dashboard') ?>" class="btn btn-primary">
        Kembali ke Dashboard
    </a>
</div>

</body>
</html>
