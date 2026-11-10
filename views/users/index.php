<div class="page-header">
    <div>
        <h1 class="page-title">Manajemen Pengguna & Hak Akses (8 Role)</h1>
        <p class="page-subtitle">Kelola akun pengguna, penugasan hak akses peran (RBAC), serta status aktif pengguna aplikasi.</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('/users/audit-logs') ?>" class="btn btn-secondary">
            📋 Lihat Audit Log Sistem
        </a>
        <button type="button" class="btn btn-primary" data-modal-open="addUserModal">
            👤 Tambah Pengguna Baru
        </button>
    </div>
</div>

<!-- Penjelasan 8 Hak Akses Peran (RBAC) -->
<div class="card mb-4" style="background: #f8fafc; border-left: 4px solid #3b82f6;">
    <div class="card-body p-3">
        <h4 style="margin: 0 0 8px 0; font-size: 14px; color: #1e3a8a;">🛡️ Matriks 8 Peran Resmi Sistem RUANG UJIAN:</h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 10px; font-size: 12px; color: #334155;">
            <div><strong>1. Super Admin:</strong> Akses penuh seluruh sistem, database, backup & restore.</div>
            <div><strong>2. Kepala Sekolah:</strong> Pengawasan menyeluruh, persetujuan program, pengesahan laporan.</div>
            <div><strong>3. Ketua Panitia:</strong> Manajemen kegiatan ujian, kepanitiaan, RAB, evaluasi pelaksanaan.</div>
            <div><strong>4. Sekretaris:</strong> Surat dinas, SK panitia, administrasi berkas, laporan akhir 18 bab.</div>
            <div><strong>5. Bendahara:</strong> Rencana Anggaran Biaya (RAB) dan realisasi anggaran ujian.</div>
            <div><strong>6. Seksi Naskah:</strong> Checklist kisi-kisi/soal, penggandaan, serah terima & packing LJU.</div>
            <div><strong>7. Seksi Tempat:</strong> Manajemen ruang, kapasitas, pengacakan denah kursi, label pintu.</div>
            <div><strong>8. Pengawas:</strong> Presensi kehadiran ruang, catatan insiden, pengembalian berkas.</div>
        </div>
    </div>
</div>

<!-- Modal Tambah Pengguna -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal-container" style="max-width: 550px;">
        <form method="POST" action="<?= url('/users') ?>">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h3 class="modal-title">Tambah Pengguna Baru</h3>
                <button type="button" class="modal-close" data-modal-close="addUserModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="form-label required">Nama Lengkap</label>
                    <input type="text" name="full_name" class="form-control" required placeholder="Nama lengkap beserta gelar jika ada">
                </div>
                <div class="row mb-3">
                    <div class="col" style="flex: 1;">
                        <label class="form-label required">Username</label>
                        <input type="text" name="username" class="form-control" required placeholder="huruf kecil tanpa spasi">
                    </div>
                    <div class="col" style="flex: 1;">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="user@sekolah.sch.id">
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label required">Password Akun</label>
                    <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter (dihash dengan Bcrypt)">
                </div>
                <div class="row mb-3">
                    <div class="col" style="flex: 1;">
                        <label class="form-label required">Peran / Hak Akses (Role)</label>
                        <select name="role_id" class="form-control" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= e($r['display_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col" style="flex: 1;">
                        <label class="form-label required">Status Akun</label>
                        <select name="is_active" class="form-control" required>
                            <option value="1">Aktif (Dapat Login)</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addUserModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Daftar Pengguna -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Pengguna Aplikasi (<?= count($users) ?> Akun)</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Nama Pengguna</th>
                    <th>Username</th>
                    <th>Peran (Role)</th>
                    <th>Email</th>
                    <th class="text-center">Status</th>
                    <th>Login Terakhir</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $idx => $u): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td><strong><?= e($u['full_name']) ?></strong></td>
                        <td><code><?= e($u['username']) ?></code></td>
                        <td>
                            <span class="badge badge-primary"><?= e($u['role_display']) ?></span>
                        </td>
                        <td><?= e($u['email'] ?: '-') ?></td>
                        <td class="text-center">
                            <?php if ($u['is_active']): ?>
                                <span class="badge badge-success">✓ Aktif</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td><small class="text-muted"><?= $u['last_login_at'] ? e(indo_date($u['last_login_at'], true)) : 'Belum pernah' ?></small></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline" data-modal-open="editUserModal_<?= $u['id'] ?>">Edit</button>
                            <?php if ($u['id'] !== (int)auth_user()['id']): ?>
                                <form method="POST" action="<?= url('/users/delete') ?>" style="display: inline-block;" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                     <button type="submit" class="btn btn-sm btn-outline text-danger">Hapus</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modals Edit Pengguna (Ditempatkan di luar tabel untuk stabilitas DOM HTML5) -->
<?php foreach ($users as $u): ?>
<div class="modal-overlay" id="editUserModal_<?= $u['id'] ?>">
    <div class="modal-container" style="max-width: 550px;">
        <form method="POST" action="<?= url('/users/update') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $u['id'] ?>">
            <div class="modal-header">
                <h3 class="modal-title">Edit Akun: <?= e($u['username']) ?></h3>
                <button type="button" class="modal-close" data-modal-close="editUserModal_<?= $u['id'] ?>">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="form-label required">Nama Lengkap</label>
                    <input type="text" name="full_name" class="form-control" value="<?= e($u['full_name']) ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($u['email']) ?>">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Ubah Password (Kosongkan jika tidak ingin diubah)</label>
                    <input type="password" name="password" class="form-control" placeholder="Isi password baru untuk reset">
                </div>
                <div class="row mb-3">
                    <div class="col" style="flex: 1;">
                        <label class="form-label required">Peran / Hak Akses</label>
                        <select name="role_id" class="form-control" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= $r['id'] == $u['role_id'] ? 'selected' : '' ?>><?= e($r['display_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col" style="flex: 1;">
                        <label class="form-label required">Status Akun</label>
                        <select name="is_active" class="form-control" required>
                            <option value="1" <?= $u['is_active'] ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= !$u['is_active'] ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editUserModal_<?= $u['id'] ?>">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>
