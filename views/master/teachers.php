<div class="page-header">
    <div>
        <h1 class="page-title">Data Guru & Tenaga Kependidikan</h1>
        <p class="page-subtitle">Daftar pendidik dan tenaga kependidikan untuk penugasan panitia, pengawas, dan penguji.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addTeacherModal">
            ➕ Tambah Guru / Tendik
        </button>
        <button type="button" class="btn btn-secondary" data-modal-open="importTeacherModal">
            📥 Impor Excel
        </button>
        <a href="<?= url('/master/teachers/export') ?>" class="btn btn-outline">
            📤 Ekspor Excel
        </a>
        <?php if (!empty($teachers)): ?>
            <form action="<?= url('/master/teachers/delete-all') ?>" method="POST" style="display: inline;" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus seluruh data guru dan tendik? Tindakan ini tidak dapat dibatalkan.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline text-danger">
                    🗑️ Kosongkan Data Guru
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<form method="GET" action="<?= url('/master/teachers') ?>" class="filter-bar">
    <div class="filter-input">
        <input type="text" name="q" class="form-control" placeholder="Cari nama, NIP, atau jabatan..." value="<?= e($search) ?>">
    </div>
    <button type="submit" class="btn btn-secondary">Cari</button>
    <?php if (!empty($search)): ?>
        <a href="<?= url('/master/teachers') ?>" class="btn btn-outline">Reset</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Nama Lengkap</th>
                    <th>NIP</th>
                    <th>NUPTK</th>
                    <th>Jabatan / Tugas</th>
                    <th>No. Telepon</th>
                    <th>Status</th>
                    <th style="width: 120px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($teachers)): ?>
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada data guru atau tenaga kependidikan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($teachers as $idx => $t): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><strong><?= e($t['name']) ?></strong></td>
                            <td><?= e($t['nip'] ?: '-') ?></td>
                            <td><?= e($t['nuptk'] ?: '-') ?></td>
                            <td><span class="badge badge-secondary"><?= e($t['position']) ?></span></td>
                            <td><?= e($t['phone'] ?: '-') ?></td>
                            <td>
                                <span class="badge <?= $t['status'] === 'Aktif' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= e($t['status']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline btn-sm" onclick='editTeacher(<?= json_encode($t) ?>)'>✏️</button>
                                <form action="<?= url('/master/teachers/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus <?= e($t['name']) ?>?', this)">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Guru -->
<div class="modal-overlay" id="addTeacherModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tambah Guru / Tendik Baru</div>
            <button type="button" class="modal-close" data-modal-close="addTeacherModal">&times;</button>
        </div>
        <form action="<?= url('/master/teachers/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Nama Lengkap & Gelar</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso, S.Pd., M.M." required>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip" class="form-control" placeholder="18 digit NIP jika PNS">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">NUPTK</label>
                            <input type="text" name="nuptk" class="form-control" placeholder="16 digit NUPTK">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Jabatan / Tugas</label>
                            <input type="text" name="position" class="form-control" value="Guru Mata Pelajaran" placeholder="Guru Matematika / Staf TU">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">No. WhatsApp / HP</label>
                            <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addTeacherModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Guru -->
<div class="modal-overlay" id="editTeacherModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Ubah Data Guru / Tendik</div>
            <button type="button" class="modal-close" data-modal-close="editTeacherModal">&times;</button>
        </div>
        <form action="<?= url('/master/teachers/update') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="editTeacherId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Nama Lengkap & Gelar</label>
                    <input type="text" name="name" id="editTeacherName" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip" id="editTeacherNip" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">NUPTK</label>
                            <input type="text" name="nuptk" id="editTeacherNuptk" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Jabatan / Tugas</label>
                            <input type="text" name="position" id="editTeacherPosition" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">No. WhatsApp / HP</label>
                            <input type="text" name="phone" id="editTeacherPhone" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" id="editTeacherStatus" class="form-control">
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editTeacherModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Impor Data Guru & Tendik -->
<div class="modal-overlay" id="importTeacherModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">📥 Impor Data Guru & Tendik dari Excel / CSV</div>
            <button type="button" class="modal-close" data-modal-close="importTeacherModal">&times;</button>
        </div>
        <form action="<?= url('/master/teachers/import') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <div style="font-weight: 700; color: #166534; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                <span>📄</span> Template Format Excel Resmi
                            </div>
                            <div style="font-size: 12px; color: #15803d; margin-top: 3px;">
                                Unduh format tabel Excel (.xlsx) untuk mempermudah penyiapan data guru & tendik.
                            </div>
                        </div>
                        <a href="<?= url('/master/teachers/template') ?>" class="btn btn-sm btn-success" style="background: #16a34a; border-color: #16a34a; color: #ffffff; font-weight: 600; text-decoration: none;">
                            📥 Unduh Template Excel
                        </a>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Pilih Berkas Excel (.xlsx / .xls) atau CSV</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv" required>
                    <div class="form-text">Mendukung berkas Microsoft Excel (.xlsx, .xls) dan CSV (Maksimal 10MB).</div>
                </div>

                <div class="alert alert-info" style="font-size: 12px; margin-top: 14px; line-height: 1.6;">
                    <strong>Petunjuk Format Kolom:</strong><br>
                    Baris pertama dokumen harus berupa judul kolom:<br>
                    <code>Nama Lengkap, NIP, NUPTK, Jabatan, No Telepon, Status</code><br>
                    <span style="color: #64748b; font-size: 11px;">
                        • Kolom <strong>Nama Lengkap</strong> wajib diisi.<br>
                        • Kolom NIP, NUPTK, dan No Telepon tidak akan terpotong angka nol di depannya.<br>
                        • Kolom <strong>Jabatan</strong> contoh: <code>Guru Mata Pelajaran</code>, <code>Wali Kelas</code>, <code>Tenaga Administrasi</code>.<br>
                        • Kolom <strong>Status</strong>: <code>Aktif</code> atau <code>Nonaktif</code> (default: Aktif).<br>
                        • Jika data guru dengan NIP atau Nama yang sama sudah ada, sistem akan memperbarui datanya secara otomatis.
                    </span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="importTeacherModal">Batal</button>
                <button type="submit" class="btn btn-primary">Mulai Impor</button>
            </div>
        </form>
    </div>
</div>

<script>
function editTeacher(t) {
    document.getElementById('editTeacherId').value = t.id;
    document.getElementById('editTeacherName').value = t.name;
    document.getElementById('editTeacherNip').value = t.nip || '';
    document.getElementById('editTeacherNuptk').value = t.nuptk || '';
    document.getElementById('editTeacherPosition').value = t.position || '';
    document.getElementById('editTeacherPhone').value = t.phone || '';
    document.getElementById('editTeacherStatus').value = t.status || 'Aktif';
    openModal('editTeacherModal');
}
</script>
