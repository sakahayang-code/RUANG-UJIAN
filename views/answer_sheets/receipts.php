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
                </tr>
            </thead>
            <tbody>
                <?php if (empty($receipts)): ?>
                    <tr>
                        <td colspan="12" class="text-center" style="padding: 30px; color: #94a3b8;">
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
