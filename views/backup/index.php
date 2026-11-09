<div class="page-header">
    <div>
        <h1 class="page-title">Backup & Restore Database SQLite</h1>
        <p class="page-subtitle">Pembuatan salinan cadangan instan (snapshot atomic) dan pemulihan data aplikasi dengan validasi keabsahan SQLite.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="createBackupModal">
            💾 Buat Backup Database Sekarang
        </button>
        <button type="button" class="btn btn-outline" data-modal-open="uploadRestoreModal">
            📤 Unggah & Restore File Backup
        </button>
    </div>
</div>

<!-- Kartu Informasi Database Aktif -->
<div class="stat-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 25px;">
    <div class="card p-3">
        <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Ukuran Database Aktif</div>
        <div style="font-size: 24px; font-weight: bold; color: #1e293b; margin-top: 5px;">
            <?= number_format($dbSize / 1024, 1) ?> KB
        </div>
        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">(<?= number_format($dbSize / (1024 * 1024), 2) ?> MB)</div>
    </div>
    <div class="card p-3">
        <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Jumlah Tabel Sistem</div>
        <div style="font-size: 24px; font-weight: bold; color: #2563eb; margin-top: 5px;">
            <?= (int)$tableCount ?> Tabel
        </div>
        <div style="font-size: 12px; color: #2563eb; margin-top: 2px;">Terstruktur & Termigrasi</div>
    </div>
    <div class="card p-3">
        <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Modifikasi Terakhir</div>
        <div style="font-size: 16px; font-weight: bold; color: #0f172a; margin-top: 8px;">
            <?= $dbModified !== '-' ? e(indo_date($dbModified, true)) : '-' ?>
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">WAL Mode Aktif</div>
    </div>
    <div class="card p-3">
        <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Keamanan Pemulihan</div>
        <div style="font-size: 14px; font-weight: bold; color: #059669; margin-top: 8px;">
            ✓ Auto-Safety Backup
        </div>
        <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Otomatis dicadangkan sebelum di-restore</div>
    </div>
</div>

<!-- Modal Buat Backup Manual -->
<div class="modal" id="createBackupModal">
    <div class="modal-dialog" style="max-width: 500px;">
        <div class="modal-content">
            <form method="POST" action="<?= url('/backup/create') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h3 class="modal-title">Buat Backup Database Baru</h3>
                    <button type="button" class="modal-close" data-modal-close="createBackupModal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Catatan / Keterangan Backup</label>
                        <input type="text" name="notes" class="form-control" placeholder="Misal: Sebelum impor siswa baru / Akhir semester" value="Backup manual sistem">
                        <small class="text-muted">Membantu mengidentifikasi kondisi data pada waktu backup dibuat.</small>
                    </div>
                    <p style="font-size: 12px; color: #475569;">
                        Sistem akan menjalankan perintah pencadangan atomik SQLite ke folder penyimpanan lokal yang aman.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close="createBackupModal">Batal</button>
                    <button type="submit" class="btn btn-primary">Mulai Proses Backup</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Unggah & Restore File Backup -->
<div class="modal" id="uploadRestoreModal">
    <div class="modal-dialog" style="max-width: 550px;">
        <div class="modal-content">
            <form method="POST" action="<?= url('/backup/restore') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h3 class="modal-title">Unggah & Restore File Backup</h3>
                    <button type="button" class="modal-close" data-modal-close="uploadRestoreModal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" style="font-size: 12px; margin-bottom: 15px;">
                        ⚠️ <strong>PENTING:</strong> Restore akan menimpa data yang saat ini aktif dengan isi berkas backup. Sistem akan otomatis membuat salinan pengaman terlebih dahulu.
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label required">Pilih Berkas Backup (.sqlite / .db)</label>
                        <input type="file" name="backup_file" class="form-control" accept=".sqlite,.db,.sqlite3" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close="uploadRestoreModal">Batal</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('PERINGATAN: Apakah Anda yakin ingin memulihkan database dari file ini?');">
                        Unggah & Pulihkan (Restore)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tabel Daftar Berkas Backup -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Arsip Berkas Cadangan (<?= count($backups) ?> File Tersedia)</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Nama Berkas Backup</th>
                    <th class="text-center">Ukuran File</th>
                    <th>Catatan Keterangan</th>
                    <th>Dibuat Oleh</th>
                    <th>Waktu Pembuatan</th>
                    <th class="text-center" style="width: 220px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($backups)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Belum ada riwayat berkas cadangan database. Silakan klik tombol "Buat Backup Database Sekarang" di atas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($backups as $idx => $b): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td>
                                <code><strong><?= e($b['filename']) ?></strong></code>
                            </td>
                            <td class="text-center">
                                <?= number_format($b['file_size'] / 1024, 1) ?> KB
                            </td>
                            <td style="font-size: 12px;"><?= e($b['notes'] ?: '-') ?></td>
                            <td><?= e($b['creator_name'] ?: 'Sistem') ?></td>
                            <td><?= e(indo_date($b['created_at'], true)) ?></td>
                            <td class="text-center">
                                <a href="<?= url('/backup/download?filename=' . urlencode($b['filename'])) ?>" class="btn btn-xs btn-primary" title="Unduh ke komputer">
                                    📥 Unduh
                                </a>

                                <form method="POST" action="<?= url('/backup/restore') ?>" style="display: inline-block;" onsubmit="return confirm('PERHATIAN: Apakah Anda yakin ingin mengembalikan seluruh data dari berkas <?= e($b['filename']) ?>? Sistem akan membuat salinan pengaman otomatis.');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="filename" value="<?= e($b['filename']) ?>">
                                    <button type="submit" class="btn btn-xs btn-warning">🔄 Restore</button>
                                </form>

                                <form method="POST" action="<?= url('/backup/delete') ?>" style="display: inline-block;" onsubmit="return confirm('Hapus file backup <?= e($b['filename']) ?> dari server?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="filename" value="<?= e($b['filename']) ?>">
                                    <button type="submit" class="btn btn-xs btn-outline text-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
