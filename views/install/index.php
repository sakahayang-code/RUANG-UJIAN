<div style="max-width: 650px; margin: 30px auto; padding: 25px; background: #fff; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;">
    <div style="text-align: center; margin-bottom: 25px;">
        <div style="font-size: 36px; margin-bottom: 8px;">🏛️</div>
        <h1 style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0;">RUANG UJIAN</h1>
        <p style="font-size: 13px; color: #64748b; margin-top: 5px;">Pemeriksa Kesiapan Lingkungan & Pemasangan Otomatis</p>
    </div>

    <?php if ($isInstalled): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <strong>✓ Aplikasi Sudah Terpasang!</strong> Database dan pengguna awal sudah aktif.
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="<?= url('/login') ?>" class="btn btn-primary btn-lg" style="width: 100%;">
                🔑 Menuju Halaman Login
            </a>
        </div>
    <?php else: ?>
        <h3 style="font-size: 14px; font-weight: 700; color: #334155; margin-bottom: 10px;">1. Persyaratan Server & Ekstensi PHP</h3>
        <div class="table-responsive mb-4">
            <table class="table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th>Komponen</th>
                        <th>Status Server</th>
                        <th class="text-center" style="width: 100px;">Kondisi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requirements as $req): ?>
                        <tr>
                            <td><strong><?= e($req['name']) ?></strong></td>
                            <td><?= e($req['current']) ?></td>
                            <td class="text-center">
                                <?php if ($req['status']): ?>
                                    <span class="badge badge-success">✓ Sesuai</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">✗ Kurang</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h3 style="font-size: 14px; font-weight: 700; color: #334155; margin-bottom: 10px;">2. Izin Tulis Direktori Penyimpanan</h3>
        <div class="table-responsive mb-4">
            <table class="table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th>Direktori</th>
                        <th class="text-center" style="width: 100px;">Izin Tulis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($directories as $name => $dir): ?>
                        <tr>
                            <td><code style="word-break: break-all; font-size: 11px;"><?= e($dir['path']) ?></code></td>
                            <td class="text-center">
                                <?php if ($dir['writable']): ?>
                                    <span class="badge badge-success">✓ Writable</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">✗ Read Only</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($allOk): ?>
            <div class="alert alert-info" style="font-size: 12px; margin-bottom: 20px;">
                Semua persyaratan server dan izin direktori terpenuhi dengan baik. Klik tombol di bawah untuk membuat seluruh tabel database dan menginisialisasi akun pengelola awal.
            </div>
            <form method="POST" action="<?= url('/install') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary btn-lg btn-block" style="width: 100%;">
                    🚀 Pasang & Inisialisasi Database Sekarang
                </button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger" style="font-size: 12px;">
                ⚠️ Beberapa persyaratan server atau izin direktori belum terpenuhi. Silakan aktifkan ekstensi yang diperlukan di <code>php.ini</code> atau ubah hak akses folder menjadi writable (chmod 755/777).
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
