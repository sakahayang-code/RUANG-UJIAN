<div class="page-header">
    <div>
        <h1 class="page-title">Checklist Persiapan & Penerimaan Dokumen</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Pemantauan kelengkapan kisi-kisi, naskah soal, kunci jawaban, dan status dokumen nilai guru.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addSubmissionModal">
            ➕ Tambah Checklist
        </button>
    </div>
</div>

<div class="alert alert-info" style="font-size: 13px;">
    <strong>ℹ️ Ketentuan Khusus Administrasi:</strong> Checklist ini mencatat status kelengkapan administrasi dokumen ujian guru (kisi-kisi, naskah soal, dan tanda terima berkas dokumen nilai). Sistem ini murni sistem administrasi dan <u>tidak membaca atau mengolah nilai akademik siswa</u>.
</div>

<!-- Filters -->
<form method="GET" action="<?= url('/preparation/submissions') ?>" class="filter-bar">
    <div style="min-width: 180px;">
        <select name="type" class="form-control" onchange="this.form.submit()">
            <option value="">Semua Jenis Dokumen</option>
            <option value="kisi_kisi" <?= $typeFilter === 'kisi_kisi' ? 'selected' : '' ?>>Kisi-kisi Soal</option>
            <option value="naskah_soal" <?= $typeFilter === 'naskah_soal' ? 'selected' : '' ?>>Naskah Soal Ujian</option>
            <option value="kunci_jawaban" <?= $typeFilter === 'kunci_jawaban' ? 'selected' : '' ?>>Kunci Jawaban Administrasi</option>
            <option value="dokumen_nilai" <?= $typeFilter === 'dokumen_nilai' ? 'selected' : '' ?>>Status Tanda Terima Dokumen Nilai Guru</option>
        </select>
    </div>
    <div style="min-width: 160px;">
        <select name="status" class="form-control" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="belum_diserahkan" <?= $statusFilter === 'belum_diserahkan' ? 'selected' : '' ?>>Belum Diserahkan</option>
            <option value="sudah_diserahkan" <?= $statusFilter === 'sudah_diserahkan' ? 'selected' : '' ?>>Sudah Diserahkan</option>
            <option value="sedang_diperiksa" <?= $statusFilter === 'sedang_diperiksa' ? 'selected' : '' ?>>Sedang Diperiksa</option>
            <option value="perlu_revisi" <?= $statusFilter === 'perlu_revisi' ? 'selected' : '' ?>>Perlu Revisi</option>
            <option value="selesai" <?= $statusFilter === 'selesai' ? 'selected' : '' ?>>Selesai / Terverifikasi</option>
        </select>
    </div>
    <?php if (!empty($typeFilter) || !empty($statusFilter)): ?>
        <a href="<?= url('/preparation/submissions') ?>" class="btn btn-outline">Reset Filter</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Nama Guru Penyusun</th>
                    <th>Mata Pelajaran</th>
                    <th>Tingkat</th>
                    <th>Jenis Dokumen</th>
                    <th>Tanggal Serah</th>
                    <th>Status</th>
                    <th>Catatan & Telaah</th>
                    <th>Petugas Penerima</th>
                    <th style="width: 90px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($submissions)): ?>
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada catatan checklist dokumen. Klik "Tambah Checklist" di atas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($submissions as $idx => $sub): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><strong><?= e($sub['teacher_name']) ?></strong></td>
                            <td><?= e($sub['subject_name']) ?></td>
                            <td><span class="badge badge-secondary"><?= e($sub['grade']) ?></span></td>
                            <td>
                                <strong>
                                    <?= match($sub['doc_type']) {
                                        'kisi_kisi' => 'Kisi-kisi',
                                        'naskah_soal' => 'Naskah Soal',
                                        'kunci_jawaban' => 'Kunci Jawaban',
                                        'dokumen_nilai' => 'Dokumen Nilai Guru',
                                        default => $sub['doc_type']
                                    } ?>
                                </strong>
                            </td>
                            <td><?= format_date_id($sub['submission_date']) ?></td>
                            <td>
                                <?php
                                $badgeClass = match($sub['status']) {
                                    'selesai' => 'badge-success',
                                    'sudah_diserahkan' => 'badge-info',
                                    'sedang_diperiksa' => 'badge-warning',
                                    'perlu_revisi' => 'badge-danger',
                                    default => 'badge-secondary'
                                };
                                $statusLabel = match($sub['status']) {
                                    'belum_diserahkan' => 'Belum Diserahkan',
                                    'sudah_diserahkan' => 'Sudah Diserahkan',
                                    'sedang_diperiksa' => 'Sedang Diperiksa',
                                    'perlu_revisi' => 'Perlu Revisi',
                                    'selesai' => 'Selesai / Sah',
                                    default => $sub['status']
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
                            </td>
                            <td style="font-size: 12px;">
                                <?= e($sub['notes'] ?: '-') ?>
                                <?php if (!empty($sub['review_notes'])): ?>
                                    <div style="color: #dc2626; margin-top: 2px;">Catatan: <?= e($sub['review_notes']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px; color: #64748b;">
                                <?= e($sub['reviewer_name'] ?: '-') ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline btn-sm" onclick='openUpdateStatusModal(<?= json_encode($sub) ?>)'>
                                    ✏️ Telaah
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Checklist -->
<div class="modal-overlay" id="addSubmissionModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tambah Checklist Dokumen Guru</div>
            <button type="button" class="modal-close" data-modal-close="addSubmissionModal">&times;</button>
        </div>
        <form action="<?= url('/preparation/submissions/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Nama Guru</label>
                    <select name="teacher_id" class="form-control" required>
                        <option value="">Pilih Guru</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (NIP: <?= e($t['nip'] ?: '-') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label required">Mata Pelajaran</label>
                            <select name="subject_id" class="form-control" required>
                                <option value="">Pilih Mapel</option>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= e($s['code']) ?> - <?= e($s['name']) ?> (<?= e($s['grade']) ?>)</option>
                                <?php endforeach; ?>
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
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jenis Dokumen</label>
                            <select name="doc_type" class="form-control" required>
                                <option value="kisi_kisi">Kisi-kisi Soal</option>
                                <option value="naskah_soal" selected>Naskah Soal Ujian</option>
                                <option value="kunci_jawaban">Kunci Jawaban Administrasi</option>
                                <option value="dokumen_nilai">Status Penyerahan Dokumen Nilai Guru</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Status Awal</label>
                            <select name="status" class="form-control" required>
                                <option value="belum_diserahkan">Belum Diserahkan</option>
                                <option value="sudah_diserahkan">Sudah Diserahkan</option>
                                <option value="selesai">Selesai / Sah</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Tambahan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Keterangan kelengkapan dokumen..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addSubmissionModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Checklist</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Update Status & Telaah -->
<div class="modal-overlay" id="updateStatusModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Telaah & Perbarui Status Dokumen</div>
            <button type="button" class="modal-close" data-modal-close="updateStatusModal">&times;</button>
        </div>
        <form action="<?= url('/preparation/submissions/update-status') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="updateStatusId">
            <div class="modal-body">
                <div style="background: #f8fafc; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 16px; font-size: 13px;">
                    <div>Guru: <strong id="updateStatusTeacher"></strong></div>
                    <div>Dokumen: <strong id="updateStatusDoc"></strong> (<span id="updateStatusSubject"></span>)</div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Status Dokumen</label>
                    <select name="status" id="updateStatusSelect" class="form-control" required>
                        <option value="belum_diserahkan">Belum Diserahkan</option>
                        <option value="sudah_diserahkan">Sudah Diserahkan</option>
                        <option value="sedang_diperiksa">Sedang Diperiksa</option>
                        <option value="perlu_revisi">Perlu Revisi</option>
                        <option value="selesai">Selesai / Terverifikasi Sah</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Revisi / Hasil Telaah (Opsional)</label>
                    <textarea name="review_notes" id="updateReviewNotes" class="form-control" rows="3" placeholder="Tuliskan catatan jika ada hal yang perlu diperbaiki..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan Tambahan</label>
                    <input type="text" name="notes" id="updateStatusNotes" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="updateStatusModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Status Telaah</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUpdateStatusModal(sub) {
    document.getElementById('updateStatusId').value = sub.id;
    document.getElementById('updateStatusTeacher').textContent = sub.teacher_name;
    document.getElementById('updateStatusDoc').textContent = sub.doc_type;
    document.getElementById('updateStatusSubject').textContent = sub.subject_name + ' (' + sub.grade + ')';
    document.getElementById('updateStatusSelect').value = sub.status;
    document.getElementById('updateReviewNotes').value = sub.review_notes || '';
    document.getElementById('updateStatusNotes').value = sub.notes || '';
    openModal('updateStatusModal');
}
</script>
