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
            📥 Impor Excel
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

<!-- Tabs Kategori Mata Pelajaran: Semua, Ujian Tulis, Ujian Praktik -->
<div style="display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; gap: 8px; flex-wrap: wrap;">
    <a href="<?= url('/master/subjects?type=all') ?>" 
       style="padding: 10px 18px; font-weight: 700; font-size: 13.5px; text-decoration: none; border-bottom: 3px solid <?= ($type ?? 'all') === 'all' ? '#2563eb' : 'transparent' ?>; color: <?= ($type ?? 'all') === 'all' ? '#1d4ed8' : '#64748b' ?>; display: inline-flex; align-items: center; gap: 6px; background: <?= ($type ?? 'all') === 'all' ? '#eff6ff' : 'transparent' ?>; border-top-left-radius: 8px; border-top-right-radius: 8px;">
        📚 Semua Mata Pelajaran (<?= $totalAll ?? count($subjects) ?>)
    </a>
    <a href="<?= url('/master/subjects?type=written') ?>" 
       style="padding: 10px 18px; font-weight: 700; font-size: 13.5px; text-decoration: none; border-bottom: 3px solid <?= ($type ?? 'all') === 'written' ? '#059669' : 'transparent' ?>; color: <?= ($type ?? 'all') === 'written' ? '#047857' : '#64748b' ?>; display: inline-flex; align-items: center; gap: 6px; background: <?= ($type ?? 'all') === 'written' ? '#ecfdf5' : 'transparent' ?>; border-top-left-radius: 8px; border-top-right-radius: 8px;">
        📝 Mata Pelajaran Ujian Tulis (<?= $totalWritten ?? 0 ?>)
    </a>
    <a href="<?= url('/master/subjects?type=practical') ?>" 
       style="padding: 10px 18px; font-weight: 700; font-size: 13.5px; text-decoration: none; border-bottom: 3px solid <?= ($type ?? 'all') === 'practical' ? '#7c3aed' : 'transparent' ?>; color: <?= ($type ?? 'all') === 'practical' ? '#6d28d9' : '#64748b' ?>; display: inline-flex; align-items: center; gap: 6px; background: <?= ($type ?? 'all') === 'practical' ? '#f5f3ff' : 'transparent' ?>; border-top-left-radius: 8px; border-top-right-radius: 8px;">
        🧪 Mata Pelajaran Ujian Praktik (<?= $totalPractical ?? 0 ?>)
    </a>
</div>

<form method="GET" action="<?= url('/master/subjects') ?>" class="filter-bar">
    <input type="hidden" name="type" value="<?= e($type ?? 'all') ?>">
    <div class="filter-input">
        <input type="text" name="q" class="form-control" placeholder="Cari kode atau nama mapel..." value="<?= e($search) ?>">
    </div>
    <button type="submit" class="btn btn-secondary">Cari</button>
    <?php if (!empty($search)): ?>
        <a href="<?= url('/master/subjects?type=' . e($type ?? 'all')) ?>" class="btn btn-outline">Reset</a>
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

<!-- Modal Impor Data Mata Pelajaran -->
<div class="modal-overlay" id="importSubjectModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">📥 Impor Data Mata Pelajaran dari Excel / CSV</div>
            <button type="button" class="modal-close" data-modal-close="importSubjectModal">&times;</button>
        </div>
        <form action="<?= url('/master/subjects/import') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <div style="font-weight: 700; color: #166534; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                <span>📄</span> Template Format Excel Resmi
                            </div>
                            <div style="font-size: 12px; color: #15803d; margin-top: 3px;">
                                Unduh format tabel Excel (.xlsx) untuk mempermudah penyiapan data mata pelajaran.
                            </div>
                        </div>
                        <a href="<?= url('/master/subjects/template') ?>" class="btn btn-sm btn-success" style="background: #16a34a; border-color: #16a34a; color: #ffffff; font-weight: 600; text-decoration: none;">
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
                    <code>Kode Mapel, Nama Mata Pelajaran, Tingkat, Jenis Ujian</code><br>
                    <span style="color: #64748b; font-size: 11px;">
                        • Kolom <strong>Nama Mata Pelajaran</strong> wajib diisi.<br>
                        • Kolom <strong>Kode Mapel</strong> (contoh: <code>BIN</code>, <code>MAT</code>, <code>IPA</code>). Jika kosong, otomatis dibuatkan kode.<br>
                        • Kolom <strong>Tingkat</strong>: <code>Semua Tingkat</code>, <code>VII</code>, <code>VIII</code>, <code>IX</code>, dsb (default: Semua Tingkat).<br>
                        • Kolom <strong>Jenis Ujian</strong>: <code>Tertulis</code>, <code>Praktik</code>, atau <code>Tertulis & Praktik</code>.<br>
                        • Jika kode mapel sudah ada, data mata pelajaran akan diperbarui secara otomatis.
                    </span>
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
