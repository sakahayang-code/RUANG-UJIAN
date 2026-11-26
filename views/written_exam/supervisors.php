<div class="page-header">
    <div>
        <h1 class="page-title">Penugasan Pengawas Ruang</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Penjadwalan pengawas utama dan pendamping per sesi dan ruang (dilengkapi deteksi anti-bentrok).</p>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Hari & Tanggal</th>
                    <th class="text-center">Sesi</th>
                    <th class="text-center">Ruang</th>
                    <th>Mata Pelajaran</th>
                    <th>Waktu</th>
                    <th>Pengawas Utama</th>
                    <th>Pengawas Pendamping</th>
                    <th style="width: 100px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assignments)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada jadwal sesi atau ruang ujian yang dibuat. Pastikan sudah menambahkan jadwal dan ruang.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assignments as $idx => $a): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= format_day_id($a['exam_date']) ?></strong><br>
                                <span style="font-size: 12px; color: #64748b;"><?= format_date_id($a['exam_date']) ?></span>
                            </td>
                            <td class="text-center"><span class="badge badge-info">Sesi <?= e($a['session']) ?></span></td>
                            <td class="text-center">
                                <strong><?= e($a['room_number']) ?></strong><br>
                                <span style="font-size: 11px; color: #64748b;"><?= e($a['room_name']) ?></span>
                            </td>
                            <td>
                                <strong><?= e($a['subject_name']) ?></strong> (<code><?= e($a['subject_code']) ?></code>)
                            </td>
                            <td class="text-center" style="font-size: 12px;"><?= e($a['start_time']) ?> - <?= e($a['end_time']) ?></td>
                            <td>
                                <?php if ($a['sup1_name']): ?>
                                    <strong style="color: #1e3a8a;">1. <?= e($a['sup1_name']) ?></strong>
                                <?php else: ?>
                                    <span class="badge badge-danger">Belum Ditentukan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($a['sup2_name']): ?>
                                    <span style="color: #334155;">2. <?= e($a['sup2_name']) ?></span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 12px;">(Opsional / Kosong)</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline btn-sm" onclick='openEditSupervisorModal(<?= json_encode($a) ?>)'>
                                    ✏️ Tugaskan
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Penugasan Pengawas -->
<div class="modal-overlay" id="editSupervisorModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Penugasan Pengawas Ruang Ujian</div>
            <button type="button" class="modal-close" data-modal-close="editSupervisorModal">&times;</button>
        </div>
        <form action="<?= url('/written-exam/supervisors/update') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="editSupAssignmentId">
            <div class="modal-body">
                <div style="background: #f8fafc; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 16px; font-size: 13px;">
                    <div>Jadwal: <strong id="editSupScheduleInfo"></strong></div>
                    <div>Ruang: <strong id="editSupRoomInfo"></strong></div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Pengawas Utama (Pengawas 1)</label>
                    <select name="supervisor1_id" id="editSup1Select" class="form-control" required>
                        <option value="">-- Pilih Guru Pengawas 1 --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (NIP: <?= e($t['nip'] ?: '-') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Pengawas Pendamping (Pengawas 2 - Opsional)</label>
                    <select name="supervisor2_id" id="editSup2Select" class="form-control">
                        <option value="">-- Tidak Ada / Kosong --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (NIP: <?= e($t['nip'] ?: '-') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="alert alert-info" style="font-size: 12px; margin-bottom: 0;">
                    🛡️ <strong>Perlindungan Bentrok Jadwal:</strong> Sistem akan secara otomatis memverifikasi bahwa pengawas yang dipilih tidak sedang bertugas di ruang lain pada tanggal dan jam yang sama.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editSupervisorModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Penugasan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditSupervisorModal(a) {
    document.getElementById('editSupAssignmentId').value = a.id;
    document.getElementById('editSupScheduleInfo').textContent = a.exam_date + ' (Sesi ' + a.session + ' - ' + a.subject_name + ')';
    document.getElementById('editSupRoomInfo').textContent = a.room_number + ' (' + a.room_name + ')';
    document.getElementById('editSup1Select').value = a.supervisor1_id || '';
    document.getElementById('editSup2Select').value = a.supervisor2_id || '';
    openModal('editSupervisorModal');
}
</script>
