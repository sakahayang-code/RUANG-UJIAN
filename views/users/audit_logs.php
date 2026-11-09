<div class="page-header">
    <div>
        <h1 class="page-title">Audit Log & Riwayat Aktivitas Keamanan</h1>
        <p class="page-subtitle">Pencatatan seluruh aktivitas penting pengguna, modifikasi data, serta riwayat upaya login ke sistem.</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('/users') ?>" class="btn btn-secondary">
            ⬅️ Kembali ke Manajemen Pengguna
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">📜 Audit Log Aktivitas Penting (100 Terakhir)</h3>
    </div>
    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table" style="font-size: 12px;">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Aksi</th>
                    <th>Tabel Data</th>
                    <th>ID Data</th>
                    <th>Detail Perubahan</th>
                    <th>Alamat IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($auditLogs)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat audit log tersimpan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($auditLogs as $idx => $al): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><?= e(indo_date($al['created_at'], true)) ?></td>
                            <td>
                                <strong><?= e($al['full_name'] ?: $al['username'] ?: 'Sistem') ?></strong>
                            </td>
                            <td><span class="badge badge-info"><?= e($al['action']) ?></span></td>
                            <td><code><?= e($al['table_name'] ?: '-') ?></code></td>
                            <td><?= e($al['record_id'] ?: '-') ?></td>
                            <td style="max-width: 250px; overflow-x: auto;">
                                <?php if (!empty($al['new_values'])): ?>
                                    <small><?= e(substr($al['new_values'], 0, 120)) ?><?= strlen($al['new_values']) > 120 ? '...' : '' ?></small>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><code><?= e($al['ip_address'] ?: '127.0.0.1') ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">🔐 Riwayat Upaya Login (50 Terakhir)</h3>
    </div>
    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
        <table class="table" style="font-size: 12px;">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Waktu Login</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Alamat IP</th>
                    <th>Perangkat / Browser</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($loginLogs)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat login tercatat.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($loginLogs as $idx => $ll): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><?= e(indo_date($ll['login_at'], true)) ?></td>
                            <td><strong><?= e($ll['username']) ?></strong></td>
                            <td>
                                <?php if ($ll['status'] === 'success'): ?>
                                    <span class="badge badge-success">✓ Berhasil</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">✗ Gagal</span>
                                <?php endif; ?>
                            </td>
                            <td><code><?= e($ll['ip_address']) ?></code></td>
                            <td style="max-width: 300px; font-size: 11px; color: #64748b;">
                                <?= e(substr($ll['user_agent'], 0, 80)) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
