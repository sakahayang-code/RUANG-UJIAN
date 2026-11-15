<div class="page-header">
    <div>
        <h1 class="page-title">Penerimaan & Verifikasi Lembar Jawaban</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Pencatatan fisik lembar jawaban yang diterima dari pengawas ruang setelah ujian selesai.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addReceiptModal">
            📥 Catat Penerimaan Lembar Jawaban
        </button>
        <a href="<?= url('/answer-sheets/packing') ?>" class="btn btn-success">
            ✉️ Lanjut ke Pengemasan (Packing)
        </a>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="<?= url('/answer-sheets/receipts') ?>" class="filter-bar">
    <div style="min-width: 150px;">
        <label style="font-size: 11px; font-weight: 600; color: #475569; display: block;">Status Penerimaan:</label>
        <select name="status" class="form-control" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="diterima_lengkap" <?= $statusFilter === 'diterima_lengkap' ? 'selected' : '' ?>>Diterima Lengkap (Sesuai)</option>
            <option value="ada_selisih" <?= $statusFilter === 'ada_selisih' ? 'selected' : '' ?>>Ada Selisih Fisik</option>
            <option value="belum_diterima" <?= $statusFilter === 'belum_diterima' ? 'selected' : '' ?>>Belum Diterima</option>
        </select>
    </div>
    <div style="min-width: 150px;">
        <label style="font-size: 11px; font-weight: 600; color: #475569; display: block;">Tanggal:</label>
        <input type="date" name="date" class="form-control" value="<?= e($dateFilter) ?>" onchange="this.form.submit()">
    </div>
    <div style="align-self: flex-end;">
        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if (!empty($statusFilter) || !empty($dateFilter)): ?>
            <a href="<?= url('/answer-sheets/receipts') ?>" class="btn btn-outline">Reset</a>
        <?php endif; ?>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Tanggal & Sesi</th>
                    <th class="text-center">Ruang Asal</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas Asal</th>
                    <th class="text-center">Peserta Hadir</th>
                    <th class="text-center">Lembar Diterima</th>
                    <th class="text-center">Sisa / Tidak Terpakai</th>
                    <th class="text-center">Rusak</th>
                    <th class="text-center">Selisih</th>
                    <th class="text-center">Status</th>
                    <th>Petugas Penerima</th>
                    <th style="width: 130px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($receipts)): ?>
                    <tr>
                        <td colspan="13" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada catatan penerimaan lembar jawaban. Klik tombol di atas untuk mencatat.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($receipts as $idx => $r): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= format_day_id($r['receipt_date']) ?></strong><br>
                                <span style="font-size: 11px; color: #64748b;"><?= format_date_id($r['receipt_date']) ?> (Sesi <?= e($r['session']) ?>)</span>
                            </td>
                            <td class="text-center">
                                <strong><?= e($r['room_number']) ?></strong>
                            </td>
                            <td><strong><?= e($r['subject_name']) ?></strong></td>
                            <td><span class="badge badge-secondary"><?= e($r['class_name']) ?></span></td>
                            <td class="text-center font-bold"><?= $r['present_count'] ?> Siswa</td>
                            <td class="text-center font-bold" style="color: #059669; font-size: 13px;"><?= $r['received_count'] ?> Lbr</td>
                            <td class="text-center"><?= $r['unused_count'] ?></td>
                            <td class="text-center" style="color: <?= $r['damaged_count'] > 0 ? '#dc2626' : '#64748b' ?>;"><?= $r['damaged_count'] ?></td>
                            <td class="text-center">
                                <?php if ($r['discrepancy'] != 0): ?>
                                    <span class="badge badge-danger">Kurang <?= $r['discrepancy'] ?></span>
                                <?php else: ?>
                                    <span class="badge badge-success">0 (Cocok)</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $r['status'] === 'diterima_lengkap' ? 'badge-success' : ($r['status'] === 'ada_selisih' ? 'badge-danger' : 'badge-warning') ?>">
                                    <?= ucwords(str_replace('_', ' ', $r['status'])) ?>
                                </span>
                            </td>
                            <td style="font-size: 11px;">
                                <strong><?= e($r['receiving_officer']) ?></strong>
                                <?php if (!empty($r['handover_officer'])): ?>
                                    <div style="color: #64748b;">Dari: <?= e($r['handover_officer']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div style="display: inline-flex; gap: 4px; align-items: center;">
                                    <button type="button"
                                        class="btn btn-secondary btn-sm"
                                        data-receipt="<?= e(json_encode([
                                            'id' => $r['id'],
                                            'room_id' => $r['room_id'] ?? '',
                                            'class_name' => $r['class_name'] ?? '',
                                            'present_count' => $r['present_count'] ?? 0,
                                            'received_count' => $r['received_count'] ?? 0,
                                            'unused_count' => $r['unused_count'] ?? 0,
                                            'damaged_count' => $r['damaged_count'] ?? 0,
                                            'handover_officer' => $r['handover_officer'] ?? '',
                                            'receiving_officer' => $r['receiving_officer'] ?? '',
                                            'notes' => $r['notes'] ?? '',
                                        ], JSON_HEX_APOS | JSON_HEX_QUOT)) ?>"
                                        onclick="openEditReceiptModal(this)">
                                        ✏️ Edit
                                    </button>
                                    <form action="<?= url('/answer-sheets/receipts/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus catatan penerimaan lembar jawaban ini?', this)">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Catat Penerimaan -->
<div class="modal-overlay" id="addReceiptModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tanda Terima Lembar Jawaban Ujian</div>
            <button type="button" class="modal-close" data-modal-close="addReceiptModal">&times;</button>
        </div>
        <form action="<?= url('/answer-sheets/receipts/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label required">Jadwal Sesi & Mapel</label>
                            <select name="schedule_id" class="form-control" required>
                                <option value="">Pilih Jadwal</option>
                                <?php foreach ($schedules as $sch): ?>
                                    <option value="<?= $sch['id'] ?>">
                                        <?= $sch['exam_date'] ?> (Sesi <?= $sch['session'] ?>) - <?= e($sch['subject_name']) ?> (<?= e($sch['grade']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Ruang Asal</label>
                            <select name="room_id" class="form-control" required>
                                <option value="">Pilih Ruang</option>
                                <?php foreach ($rooms as $rm): ?>
                                    <option value="<?= $rm['id'] ?>"><?= e($rm['room_number']) ?> - <?= e($rm['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Kelas Asal Siswa</label>
                            <select name="class_name" class="form-control" required>
                                <option value="">Pilih Kelas Asal</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= e($c['name']) ?>"><?= e($c['name']) ?> (<?= e($c['grade']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jumlah Fisik Diterima (Lembar)</label>
                            <input type="number" name="received_count" class="form-control" value="20" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Lembar Tidak Terpakai (Sisa)</label>
                            <input type="number" name="unused_count" class="form-control" value="2" min="0">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Lembar Cacat / Rusak</label>
                            <input type="number" name="damaged_count" class="form-control" value="0" min="0">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Pengawas Penyerah</label>
                            <input type="text" name="handover_officer" class="form-control" placeholder="Nama guru pengawas...">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Catatan Tambahan</label>
                            <input type="text" name="notes" class="form-control" placeholder="Keterangan fisik...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addReceiptModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Penerimaan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Penerimaan Lembar Jawaban -->
<div class="modal-overlay" id="editReceiptModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">✏️ Edit Tanda Terima Lembar Jawaban</div>
            <button type="button" class="modal-close" data-modal-close="editReceiptModal">&times;</button>
        </div>
        <form action="<?= url('/answer-sheets/receipts/update') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit_rec_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Ruang Asal</label>
                            <select name="room_id" id="edit_rec_room_id" class="form-control" required>
                                <?php foreach ($rooms as $rm): ?>
                                    <option value="<?= $rm['id'] ?>"><?= e($rm['room_number']) ?> - <?= e($rm['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Kelas Asal Siswa</label>
                            <select name="class_name" id="edit_rec_class_name" class="form-control" required>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= e($c['name']) ?>"><?= e($c['name']) ?> (<?= e($c['grade']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Peserta Hadir (Siswa)</label>
                            <input type="number" name="present_count" id="edit_rec_present" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Lembar Diterima</label>
                            <input type="number" name="received_count" id="edit_rec_received" class="form-control" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Lembar Tidak Terpakai</label>
                            <input type="number" name="unused_count" id="edit_rec_unused" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Lembar Rusak</label>
                            <input type="number" name="damaged_count" id="edit_rec_damaged" class="form-control" min="0">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Pengawas Penyerah</label>
                            <input type="text" name="handover_officer" id="edit_rec_handover" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Petugas Penerima</label>
                            <input type="text" name="receiving_officer" id="edit_rec_receiving" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Tambahan</label>
                    <input type="text" name="notes" id="edit_rec_notes" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editReceiptModal">Batal</button>
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditReceiptModal(btn) {
    try {
        const d = JSON.parse(btn.getAttribute('data-receipt'));
        document.getElementById('edit_rec_id').value = d.id || '';
        document.getElementById('edit_rec_room_id').value = d.room_id || '';
        document.getElementById('edit_rec_class_name').value = d.class_name || '';
        document.getElementById('edit_rec_present').value = d.present_count ?? 0;
        document.getElementById('edit_rec_received').value = d.received_count ?? 0;
        document.getElementById('edit_rec_unused').value = d.unused_count ?? 0;
        document.getElementById('edit_rec_damaged').value = d.damaged_count ?? 0;
        document.getElementById('edit_rec_handover').value = d.handover_officer || '';
        document.getElementById('edit_rec_receiving').value = d.receiving_officer || '';
        document.getElementById('edit_rec_notes').value = d.notes || '';
        openModal('editReceiptModal');
    } catch (e) {
        console.error('Gagal membuka modal edit penerimaan lembar jawaban:', e);
    }
}
</script>
