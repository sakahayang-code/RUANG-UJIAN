<div class="page-header">
    <div>
        <h1 class="page-title">Pengemasan (Packing) Lembar Jawaban Ujian</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Pengelompokan lembar jawaban ke dalam amplop berdasarkan 3 Mode Pengemasan resmi.</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('/answer-sheets/receipts') ?>" class="btn btn-secondary">
            📥 Penerimaan Lembar Jawaban
        </a>
        <a href="<?= url('/answer-sheets/recap') ?>" class="btn btn-outline">
            📊 Rekapitulasi Penyerahan
        </a>
        <button type="button" class="btn btn-primary" data-modal-open="createBatchModal">
            ✉️ Buat Pengemasan Baru (Packing Batch)
        </button>
    </div>
</div>

<div class="alert alert-info" style="margin-bottom: 20px;">
    <strong>ℹ️ 3 Pilihan Mode Pengemasan Lembar Jawaban:</strong>
    <ul style="margin: 5px 0 0 20px; font-size: 13px;">
        <li><strong>Mode 1 (Per Kelas Asal):</strong> 1 amplop berisi lembar jawaban dari seluruh mata pelajaran untuk 1 kelas asal tertentu (misal: Amplop Khusus Kelas VII-A).</li>
        <li><strong>Mode 2 (Per Mata Pelajaran):</strong> 1 amplop berisi lembar jawaban 1 mata pelajaran yang mencakup seluruh kelas asal (misal: Amplop Mapel Matematika Kelas VII-A s.d VII-E).</li>
        <li><strong>Mode 3 (Per Kelas & Mata Pelajaran):</strong> 1 amplop khusus untuk 1 kelas asal dan 1 mata pelajaran tertentu (pengemasan satuan/terpisah).</li>
    </ul>
</div>

<!-- Modal Buat Batch Pengemasan -->
<div class="modal" id="createBatchModal">
    <div class="modal-dialog" style="max-width: 650px;">
        <div class="modal-content">
            <form method="POST" action="<?= url('/answer-sheets/packing') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h3 class="modal-title">Buat Batch Pengemasan (Packing) Amplop</h3>
                    <button type="button" class="modal-close" data-modal-close="createBatchModal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label required">Pilih Mode Pengemasan</label>
                        <select name="packing_mode" id="packing_mode_select" class="form-control" required onchange="toggleModeInputs()">
                            <option value="per_kelas">Mode 1: Per Kelas Asal (1 Amplop berisi seluruh mapel untuk 1 kelas)</option>
                            <option value="per_mapel">Mode 2: Per Mata Pelajaran (1 Amplop berisi seluruh kelas untuk 1 mapel)</option>
                            <option value="per_kelas_mapel">Mode 3: Per Kelas & Mata Pelajaran (1 Amplop khusus 1 kelas & 1 mapel)</option>
                        </select>
                    </div>

                    <div class="form-group mb-3" id="class_input_group">
                        <label class="form-label" id="class_label">Pilih Kelas Asal</label>
                        <select name="class_name" class="form-control">
                            <option value="">-- Pilih Kelas Asal --</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= e($c['name']) ?>"><?= e($c['name']) ?> (Tingkat <?= e($c['grade']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Wajib dipilih untuk Mode 1 dan Mode 3.</small>
                    </div>

                    <div class="form-group mb-3" id="subject_input_group" style="display: none;">
                        <label class="form-label" id="subject_label">Pilih Mata Pelajaran</label>
                        <select name="subject_id" class="form-control">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            <?php foreach ($subjects as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['code']) ?> - <?= e($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Wajib dipilih untuk Mode 2 dan Mode 3.</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col" style="flex: 1;">
                            <label class="form-label required">Jumlah Amplop Fisik</label>
                            <input type="number" name="envelope_count" class="form-control" value="1" min="1" required>
                            <small class="text-muted">Berapa amplop yang disiapkan (misal 1 dari 1, atau 1 dari 2).</small>
                        </div>
                        <div class="col" style="flex: 1;">
                            <label class="form-label required">Petugas Pengemas (Packing)</label>
                            <input type="text" name="packing_officer" class="form-control" value="<?= e(auth_user()['full_name'] ?? 'Petugas Panitia') ?>" required>
                        </div>
                    </div>

                    <p style="font-size: 12px; color: #64748b; margin-top: 10px;">
                        <em>Sistem akan otomatis menghimpun lembar jawaban yang belum dikemas sesuai kriteria yang dipilih, memeriksa selisih fisik, serta menerbitkan label stiker amplop resmi.</em>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close="createBatchModal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan & Kemas ke Amplop</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleModeInputs() {
    var mode = document.getElementById('packing_mode_select').value;
    var classGroup = document.getElementById('class_input_group');
    var subjectGroup = document.getElementById('subject_input_group');

    if (mode === 'per_kelas') {
        classGroup.style.display = 'block';
        subjectGroup.style.display = 'none';
    } else if (mode === 'per_mapel') {
        classGroup.style.display = 'none';
        subjectGroup.style.display = 'block';
    } else if (mode === 'per_kelas_mapel') {
        classGroup.style.display = 'block';
        subjectGroup.style.display = 'block';
    }
}
</script>

<!-- Daftar Lembar Jawaban Belum Dikemas (Ready to Pack) -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">📦 Lembar Jawaban Siap Dikemas (<?= count($unpackedReceipts) ?> Berkas)</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Tanggal & Sesi</th>
                    <th class="text-center">Ruang</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas Asal</th>
                    <th class="text-center">Hadir</th>
                    <th class="text-center">Lembar Diterima</th>
                    <th class="text-center">Selisih</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($unpackedReceipts)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            Semua lembar jawaban yang diterima sudah selesai dikemas dalam amplop (atau belum ada penerimaan).
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($unpackedReceipts as $idx => $r): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><?= e(indo_date($r['receipt_date'], true)) ?><br><small class="text-muted">Sesi <?= e($r['session']) ?></small></td>
                            <td class="text-center"><span class="badge badge-info">Ruang <?= e($r['room_number']) ?></span></td>
                            <td><strong><?= e($r['subject_code']) ?></strong> - <?= e($r['subject_name']) ?></td>
                            <td><span class="badge badge-light"><?= e($r['class_name']) ?></span></td>
                            <td class="text-center"><?= (int)$r['present_count'] ?></td>
                            <td class="text-center"><strong><?= (int)$r['received_count'] ?></strong></td>
                            <td class="text-center">
                                <?php if ((int)$r['discrepancy'] > 0): ?>
                                    <span class="badge badge-danger">-<?= (int)$r['discrepancy'] ?> (Kurang)</span>
                                <?php elseif ((int)$r['discrepancy'] < 0): ?>
                                    <span class="badge badge-warning">+<?= abs((int)$r['discrepancy']) ?> (Lebih)</span>
                                <?php else: ?>
                                    <span class="badge badge-success">0 (Pas)</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($r['status'] === 'ada_selisih'): ?>
                                    <span class="badge badge-danger">Ada Selisih</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Sesuai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Daftar Amplop Pengemasan (Packing Batches) -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">🗂️ Riwayat Amplop Pengemasan Lembar Jawaban (<?= count($batches) ?> Batch)</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Kode Batch</th>
                    <th>Mode Pengemasan</th>
                    <th>Klasifikasi (Kelas / Mapel)</th>
                    <th class="text-center">Total Lembar</th>
                    <th class="text-center">Jumlah Amplop</th>
                    <th class="text-center">Status</th>
                    <th>Petugas Packing</th>
                    <th class="text-center" style="width: 140px;">Label Amplop</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($batches)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            Belum ada batch pengemasan amplop yang dibuat.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($batches as $idx => $b): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= e($b['batch_code']) ?></strong><br>
                                <small class="text-muted"><?= e(indo_date($b['packing_date'], true)) ?></small>
                            </td>
                            <td>
                                <?php if ($b['packing_mode'] === 'per_kelas'): ?>
                                    <span class="badge badge-primary">Mode 1: Per Kelas Asal</span>
                                <?php elseif ($b['packing_mode'] === 'per_mapel'): ?>
                                    <span class="badge badge-success">Mode 2: Per Mata Pelajaran</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Mode 3: Per Kelas & Mapel</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($b['class_name'])): ?>
                                    <div>Kelas: <strong><?= e($b['class_name']) ?></strong></div>
                                <?php endif; ?>
                                <?php if (!empty($b['subject_name'])): ?>
                                    <div>Mapel: <strong><?= e($b['subject_name']) ?></strong></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span style="font-size: 15px; font-weight: bold;"><?= (int)$b['total_sheets'] ?></span> lbr
                                <div style="font-size: 11px; color: #64748b;">(<?= (int)$b['item_count'] ?> tanda terima)</div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary"><?= (int)$b['envelope_count'] ?> Amplop</span>
                            </td>
                            <td class="text-center">
                                <?php if ($b['status'] === 'Ada Selisih'): ?>
                                    <span class="badge badge-danger">⚠️ Ada Selisih</span>
                                <?php else: ?>
                                    <span class="badge badge-success">✓ Terverifikasi</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= e($b['packing_officer']) ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('/print/answer-sheet-labels?batch_id=' . $b['id']) ?>" target="_blank" class="btn btn-sm btn-outline">
                                    🏷️ Cetak Label
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
