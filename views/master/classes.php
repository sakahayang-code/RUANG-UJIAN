<div class="page-header">
    <div>
        <h1 class="page-title">Data Kelas</h1>
        <p class="page-subtitle">Daftar rombongan belajar dan wali kelas.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addClassModal">
            ➕ Tambah Kelas
        </button>
        <button type="button" class="btn btn-secondary" data-modal-open="importClassModal">
            📥 Impor Excel
        </button>
        <a href="<?= url('/master/classes/export') ?>" class="btn btn-outline">
            📤 Ekspor Excel
        </a>
        <?php if (!empty($classes)): ?>
            <form action="<?= url('/master/classes/delete-all') ?>" method="POST" style="display: inline;" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus seluruh data kelas? Tindakan ini tidak dapat dibatalkan.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline text-danger">
                    🗑️ Kosongkan Data Kelas
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Nama Kelas</th>
                    <th>Tingkat</th>
                    <th>Wali Kelas</th>
                    <th class="text-center">Jumlah Siswa</th>
                    <th style="width: 130px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($classes)): ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada data kelas. Silakan tambahkan kelas baru.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($classes as $idx => $c): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><strong><?= e($c['name']) ?></strong></td>
                            <td><span class="badge badge-secondary"><?= e($c['grade']) ?></span></td>
                            <td><?= e($c['homeroom_teacher_name'] ?: '(Belum ditentukan)') ?></td>
                            <td class="text-center">
                                <span class="badge badge-info"><?= $c['student_count'] ?> Siswa</span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline btn-sm" onclick='editClass(<?= json_encode($c) ?>)'>✏️</button>
                                <form action="<?= url('/master/classes/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus kelas <?= e($c['name']) ?>?', this)">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
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

<!-- Modal Tambah Kelas -->
<div class="modal-overlay" id="addClassModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tambah Kelas Baru</div>
            <button type="button" class="modal-close" data-modal-close="addClassModal">&times;</button>
        </div>
        <form action="<?= url('/master/classes/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Nama Rombel / Kelas</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: VII A, VIII B, IX C" required>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tingkat</label>
                            <select name="grade" class="form-control" required>
                                <option value="VII">VII</option>
                                <option value="VIII">VIII</option>
                                <option value="IX">IX</option>
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Wali Kelas</label>
                            <select name="homeroom_teacher_id" class="form-control">
                                <option value="">(Pilih Wali Kelas)</option>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= e($t['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addClassModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Kelas</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Kelas -->
<div class="modal-overlay" id="editClassModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Ubah Data Kelas</div>
            <button type="button" class="modal-close" data-modal-close="editClassModal">&times;</button>
        </div>
        <form action="<?= url('/master/classes/update') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="editClassId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Nama Rombel / Kelas</label>
                    <input type="text" name="name" id="editClassName" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tingkat</label>
                            <select name="grade" id="editClassGrade" class="form-control" required>
                                <option value="VII">VII</option>
                                <option value="VIII">VIII</option>
                                <option value="IX">IX</option>
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Wali Kelas</label>
                            <select name="homeroom_teacher_id" id="editClassTeacherId" class="form-control">
                                <option value="">(Pilih Wali Kelas)</option>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= e($t['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editClassModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Impor Data Kelas -->
<div class="modal-overlay" id="importClassModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">📥 Impor Data Kelas dari Excel / CSV</div>
            <button type="button" class="modal-close" data-modal-close="importClassModal">&times;</button>
        </div>
        <form action="<?= url('/master/classes/import') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <div style="font-weight: 700; color: #166534; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                <span>📄</span> Template Format Excel Resmi
                            </div>
                            <div style="font-size: 12px; color: #15803d; margin-top: 3px;">
                                Unduh format tabel Excel (.xlsx) untuk mempermudah penyiapan data kelas.
                            </div>
                        </div>
                        <a href="<?= url('/master/classes/template') ?>" class="btn btn-sm btn-success" style="background: #16a34a; border-color: #16a34a; color: #ffffff; font-weight: 600; text-decoration: none;">
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
                    <code>Nama Kelas, Tingkat, Wali Kelas</code><br>
                    <span style="color: #64748b; font-size: 11px;">
                        • Kolom <strong>Nama Kelas</strong> wajib diisi (contoh: <code>VII A</code>, <code>8-B</code>, <code>X RPL 1</code>).<br>
                        • Kolom <strong>Tingkat</strong> opsional (jika kosong, otomatis dideteksi dari angka/romawi pada nama kelas).<br>
                        • Kolom <strong>Wali Kelas</strong> opsional (dapat diisi nama guru atau NIP yang sudah ada di Master Guru).
                    </span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="importClassModal">Batal</button>
                <button type="submit" class="btn btn-primary">Mulai Impor</button>
            </div>
        </form>
    </div>
</div>

<script>
function editClass(c) {
    document.getElementById('editClassId').value = c.id;
    document.getElementById('editClassName').value = c.name;
    document.getElementById('editClassGrade').value = c.grade;
    document.getElementById('editClassTeacherId').value = c.homeroom_teacher_id || '';
    openModal('editClassModal');
}
</script>
