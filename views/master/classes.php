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
            📥 Impor CSV
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

<!-- Modal Impor CSV Kelas -->
<div class="modal-overlay" id="importClassModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Impor Data Kelas (CSV)</div>
            <button type="button" class="modal-close" data-modal-close="importClassModal">&times;</button>
        </div>
        <form action="<?= url('/master/classes/import') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="alert alert-info">
                    Format file CSV harus memiliki header: <code>nama,tingkat</code> (contoh baris: <code>VII-A,VII</code>).
                </div>
                <div class="form-group">
                    <label class="form-label required">Pilih Berkas CSV</label>
                    <input type="file" name="file" class="form-control" accept=".csv" required>
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
