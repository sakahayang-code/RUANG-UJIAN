<div class="page-header">
    <div>
        <h1 class="page-title">Data Siswa</h1>
        <p class="page-subtitle">Master data peserta didik untuk seluruh tingkatan kelas.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addStudentModal">
            ➕ Tambah Siswa
        </button>
        <button type="button" class="btn btn-secondary" data-modal-open="importStudentModal">
            📥 Impor CSV
        </button>
        <a href="<?= url('/master/students/export') ?>" class="btn btn-outline">
            📤 Ekspor Excel
        </a>
        <?php if (!empty($students)): ?>
            <form action="<?= url('/master/students/delete-all') ?>" method="POST" style="display: inline;" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus seluruh data siswa? Tindakan ini akan mengosongkan master data siswa.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline text-danger">
                    🗑️ Kosongkan Data Siswa
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Filter Bar -->
<form method="GET" action="<?= url('/master/students') ?>" class="filter-bar">
    <div class="filter-input">
        <input type="text" name="q" class="form-control" placeholder="Cari nama, NIS, atau NISN..." value="<?= e($search) ?>">
    </div>
    <div style="min-width: 140px;">
        <select name="grade" class="form-control" onchange="this.form.submit()">
            <option value="">Semua Tingkat</option>
            <option value="VII" <?= $selectedGrade === 'VII' ? 'selected' : '' ?>>Tingkat VII</option>
            <option value="VIII" <?= $selectedGrade === 'VIII' ? 'selected' : '' ?>>Tingkat VIII</option>
            <option value="IX" <?= $selectedGrade === 'IX' ? 'selected' : '' ?>>Tingkat IX</option>
            <option value="X" <?= $selectedGrade === 'X' ? 'selected' : '' ?>>Tingkat X</option>
            <option value="XI" <?= $selectedGrade === 'XI' ? 'selected' : '' ?>>Tingkat XI</option>
            <option value="XII" <?= $selectedGrade === 'XII' ? 'selected' : '' ?>>Tingkat XII</option>
        </select>
    </div>
    <div style="min-width: 160px;">
        <select name="class_id" class="form-control" onchange="this.form.submit()">
            <option value="">Semua Kelas</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?= $c['id'] ?>" <?= (string)$selectedClass === (string)$c['id'] ? 'selected' : '' ?>>
                    <?= e($c['name']) ?> (<?= e($c['grade']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-secondary">Filter</button>
    <?php if (!empty($search) || !empty($selectedClass) || !empty($selectedGrade)): ?>
        <a href="<?= url('/master/students') ?>" class="btn btn-outline" title="Reset filter">Reset</a>
    <?php endif; ?>
</form>

<!-- Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table" id="studentsTable">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Foto</th>
                    <th>NIS</th>
                    <th>NISN</th>
                    <th>Nama Lengkap</th>
                    <th class="text-center">L/P</th>
                    <th>Tingkat</th>
                    <th>Kelas</th>
                    <th>Status</th>
                    <th style="width: 120px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada data siswa. Silakan tambahkan atau impor dari CSV.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $idx => $s): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td style="width: 45px;">
                                <?php if (!empty($s['photo_path']) && file_exists(dirname(__DIR__, 2) . '/storage/uploads/' . $s['photo_path'])): ?>
                                    <img src="<?= url('/file/view?path=' . urlencode($s['photo_path'])) ?>" alt="Foto" style="width: 32px; height: 38px; object-fit: cover; border-radius: 3px;">
                                <?php else: ?>
                                    <div style="width: 32px; height: 38px; background: #e2e8f0; border-radius: 3px; display: flex; align-items: center; justify-content: center; font-size: 11px; color: #64748b;">
                                        <?= $s['gender'] ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= e($s['nis'] ?: '-') ?></strong></td>
                            <td><?= e($s['nisn'] ?: '-') ?></td>
                            <td><strong><?= e($s['name']) ?></strong></td>
                            <td class="text-center"><?= e($s['gender']) ?></td>
                            <td><span class="badge badge-secondary"><?= e($s['grade']) ?></span></td>
                            <td><?= e($s['class_name'] ?: '-') ?></td>
                            <td>
                                <span class="badge <?= $s['status'] === 'Aktif' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= e($s['status']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline btn-sm" onclick='editStudent(<?= json_encode($s) ?>)'>
                                    ✏️
                                </button>
                                <form action="<?= url('/master/students/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus siswa <?= e($s['name']) ?>?', this)">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
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

<!-- Modal Tambah Siswa -->
<div class="modal-overlay" id="addStudentModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tambah Siswa Baru</div>
            <button type="button" class="modal-close" data-modal-close="addStudentModal">&times;</button>
        </div>
        <form action="<?= url('/master/students/store') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Nama Lengkap Siswa</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Muhammad Rizky Pratama" required>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">NIS</label>
                            <input type="text" name="nis" class="form-control" placeholder="Nomor Induk Siswa">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">NISN</label>
                            <input type="text" name="nisn" class="form-control" placeholder="Nomor Induk Siswa Nasional">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jenis Kelamin</label>
                            <select name="gender" class="form-control" required>
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                    </div>
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
                            <label class="form-label required">Kelas Asal</label>
                            <select name="class_id" class="form-control" required>
                                <option value="">Pilih Kelas</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['grade']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Foto Siswa</label>
                            <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png">
                            <div class="form-text">Format JPG/PNG, maks 2MB (opsional).</div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Status Siswa</label>
                            <select name="status" class="form-control">
                                <option value="Aktif">Aktif</option>
                                <option value="Mutasi">Mutasi</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addStudentModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Siswa</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Siswa -->
<div class="modal-overlay" id="editStudentModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Ubah Data Siswa</div>
            <button type="button" class="modal-close" data-modal-close="editStudentModal">&times;</button>
        </div>
        <form action="<?= url('/master/students/update') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="editStudentId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Nama Lengkap Siswa</label>
                    <input type="text" name="name" id="editStudentName" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">NIS</label>
                            <input type="text" name="nis" id="editStudentNis" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">NISN</label>
                            <input type="text" name="nisn" id="editStudentNisn" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jenis Kelamin</label>
                            <select name="gender" id="editStudentGender" class="form-control" required>
                                <option value="L">Laki-laki (L)</option>
                                <option value="P">Perempuan (P)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tingkat</label>
                            <select name="grade" id="editStudentGrade" class="form-control" required>
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
                            <label class="form-label required">Kelas Asal</label>
                            <select name="class_id" id="editStudentClassId" class="form-control" required>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['grade']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Ganti Foto Siswa</label>
                            <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Status Siswa</label>
                            <select name="status" id="editStudentStatus" class="form-control">
                                <option value="Aktif">Aktif</option>
                                <option value="Mutasi">Mutasi</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editStudentModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Impor CSV -->
<div class="modal-overlay" id="importStudentModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Impor Data Siswa dari CSV</div>
            <button type="button" class="modal-close" data-modal-close="importStudentModal">&times;</button>
        </div>
        <form action="<?= url('/master/students/import') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Pilih Berkas CSV</label>
                    <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
                </div>
                <div class="alert alert-info" style="font-size: 12px; margin-top: 12px;">
                    <strong>Format Kolom Header CSV:</strong><br>
                    <code>nis, nisn, nama, jk, tingkat, kelas</code><br>
                    Contoh baris data:<br>
                    <code>1001, 0081234567, Ahmad Fauzi, L, VII, VII A</code><br>
                    <code>1002, 0081234568, Siti Aisyah, P, VII, VII A</code>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="importStudentModal">Batal</button>
                <button type="submit" class="btn btn-primary">Mulai Impor</button>
            </div>
        </form>
    </div>
</div>

<script>
function editStudent(student) {
    document.getElementById('editStudentId').value = student.id;
    document.getElementById('editStudentName').value = student.name;
    document.getElementById('editStudentNis').value = student.nis || '';
    document.getElementById('editStudentNisn').value = student.nisn || '';
    document.getElementById('editStudentGender').value = student.gender;
    document.getElementById('editStudentGrade').value = student.grade;
    document.getElementById('editStudentClassId').value = student.class_id;
    document.getElementById('editStudentStatus').value = student.status;
    openModal('editStudentModal');
}
</script>
