<div class="page-header">
    <div>
        <h1 class="page-title">Data Mata Pelajaran</h1>
        <p class="page-subtitle">Daftar mata pelajaran yang diujikan secara tertulis maupun praktik.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addSubjectModal">
            ➕ Tambah Mapel
        </button>
        <button type="button" class="btn btn-secondary" data-modal-open="importSubjectModal">
            📥 Impor CSV
        </button>
        <a href="<?= url('/master/subjects/export') ?>" class="btn btn-outline">
            📤 Ekspor Excel
        </a>
        <?php if (!empty($subjects)): ?>
            <form action="<?= url('/master/subjects/delete-all') ?>" method="POST" style="display: inline;" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus seluruh data mata pelajaran? Tindakan ini tidak dapat dibatalkan.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline text-danger">
                    🗑️ Kosongkan Data Mapel
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<form method="GET" action="<?= url('/master/subjects') ?>" class="filter-bar">
    <div class="filter-input">
        <input type="text" name="q" class="form-control" placeholder="Cari kode atau nama mapel..." value="<?= e($search) ?>">
    </div>
    <button type="submit" class="btn btn-secondary">Cari</button>
    <?php if (!empty($search)): ?>
        <a href="<?= url('/master/subjects') ?>" class="btn btn-outline">Reset</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Kode Mapel</th>
                    <th>Nama Mata Pelajaran</th>
                    <th>Tingkat</th>
                    <th>Jenis Ujian</th>
                    <th style="width: 120px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subjects)): ?>
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada mata pelajaran. Silakan tambahkan atau impor.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subjects as $idx => $s): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><code><?= e($s['code']) ?></code></td>
                            <td><strong><?= e($s['name']) ?></strong></td>
                            <td><span class="badge badge-secondary"><?= e($s['grade']) ?></span></td>
                            <td>
                                <?php if ($s['exam_type'] === 'written'): ?>
                                    <span class="badge badge-info">Tertulis</span>
                                <?php elseif ($s['exam_type'] === 'practical'): ?>
                                    <span class="badge badge-warning">Praktik</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Tertulis & Praktik</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline btn-sm" onclick='editSubject(<?= json_encode($s) ?>)'>✏️</button>
                                <form action="<?= url('/master/subjects/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus mapel <?= e($s['name']) ?>?', this)">
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

<!-- Modal Tambah Mapel -->
<div class="modal-overlay" id="addSubjectModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tambah Mata Pelajaran Baru</div>
            <button type="button" class="modal-close" data-modal-close="addSubjectModal">&times;</button>
        </div>
        <form action="<?= url('/master/subjects/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col" style="flex: 0.8;">
                        <div class="form-group">
                            <label class="form-label required">Kode Mapel</label>
                            <input type="text" name="code" class="form-control" placeholder="Contoh: MAT, IPA" required style="text-transform: uppercase;">
                        </div>
                    </div>
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label required">Nama Mata Pelajaran</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Matematika" required>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tingkat yang Mengikuti</label>
                            <select name="grade" class="form-control" required>
                                <option value="Semua Tingkat">Semua Tingkat</option>
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
                            <label class="form-label required">Jenis Ujian</label>
                            <select name="exam_type" class="form-control" required>
                                <option value="written">Tertulis Saja</option>
                                <option value="practical">Praktik Saja</option>
                                <option value="both">Tertulis & Praktik</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addSubjectModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Mapel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Mapel -->
<div class="modal-overlay" id="editSubjectModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Ubah Mata Pelajaran</div>
            <button type="button" class="modal-close" data-modal-close="editSubjectModal">&times;</button>
        </div>
        <form action="<?= url('/master/subjects/update') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="editSubjectId">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col" style="flex: 0.8;">
                        <div class="form-group">
                            <label class="form-label required">Kode Mapel</label>
                            <input type="text" name="code" id="editSubjectCode" class="form-control" required style="text-transform: uppercase;">
                        </div>
                    </div>
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label required">Nama Mata Pelajaran</label>
                            <input type="text" name="name" id="editSubjectName" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tingkat yang Mengikuti</label>
                            <select name="grade" id="editSubjectGrade" class="form-control" required>
                                <option value="Semua Tingkat">Semua Tingkat</option>
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
                            <label class="form-label required">Jenis Ujian</label>
                            <select name="exam_type" id="editSubjectExamType" class="form-control" required>
                                <option value="written">Tertulis Saja</option>
                                <option value="practical">Praktik Saja</option>
                                <option value="both">Tertulis & Praktik</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editSubjectModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Impor CSV -->
<div class="modal-overlay" id="importSubjectModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Impor Mata Pelajaran dari CSV</div>
            <button type="button" class="modal-close" data-modal-close="importSubjectModal">&times;</button>
        </div>
        <form action="<?= url('/master/subjects/import') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Pilih Berkas CSV</label>
                    <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
                </div>
                <div class="alert alert-info" style="font-size: 12px; margin-top: 12px;">
                    <strong>Header Kolom CSV yang Diharapkan:</strong><br>
                    <code>kode, nama, tingkat, jenis_ujian</code><br>
                    Contoh jenis_ujian: <code>written</code>, <code>practical</code>, atau <code>both</code>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="importSubjectModal">Batal</button>
                <button type="submit" class="btn btn-primary">Mulai Impor</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSubject(s) {
    document.getElementById('editSubjectId').value = s.id;
    document.getElementById('editSubjectCode').value = s.code;
    document.getElementById('editSubjectName').value = s.name;
    document.getElementById('editSubjectGrade').value = s.grade;
    document.getElementById('editSubjectExamType').value = s.exam_type;
    openModal('editSubjectModal');
}
</script>
