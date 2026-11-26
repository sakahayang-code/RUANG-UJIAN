<div class="page-header">
    <div>
        <h1 class="page-title">Daftar Hadir Penguji Praktik</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Terintegrasi dari penugasan guru penguji ujian praktik per stasiun dan jadwal.</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('/attendance/examiners/print') ?>" target="_blank" class="btn btn-primary">
            🖨️ Cetak Presensi Penguji
        </a>
        <a href="<?= url('/attendance/examiners/export-excel') ?>" class="btn btn-secondary">
            📤 Ekspor Excel
        </a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Hari & Tanggal</th>
                    <th>Waktu</th>
                    <th>Mata Pelajaran</th>
                    <th>Lokasi & Stasiun</th>
                    <th>Nama Penguji</th>
                    <th>Peran</th>
                    <th>Status Presensi</th>
                    <th>Jam Hadir</th>
                    <th style="width: 100px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada penugasan guru penguji praktik.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $idx => $r): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= format_day_id($r['exam_date']) ?></strong><br>
                                <span style="font-size: 11px; color: #64748b;"><?= format_date_id($r['exam_date']) ?></span>
                            </td>
                            <td class="font-bold"><?= e($r['start_time']) ?> - <?= e($r['end_time']) ?></td>
                            <td><strong><?= e($r['subject_name']) ?></strong></td>
                            <td><?= e($r['location']) ?> (<?= e($r['station'] ?: 'Semua') ?>)</td>
                            <td>
                                <strong><?= e($r['examiner_name']) ?></strong><br>
                                <span style="font-size: 11px; color: #64748b;">NIP. <?= e($r['examiner_nip'] ?: '-') ?></span>
                            </td>
                            <td><span class="badge badge-info"><?= e($r['examiner_role']) ?></span></td>
                            <td>
                                <span class="badge <?= ($r['attendance_status'] ?? 'hadir') === 'hadir' ? 'badge-success' : 'badge-warning' ?>">
                                    <?= ucfirst(e($r['attendance_status'] ?? 'Hadir')) ?>
                                </span>
                            </td>
                            <td style="font-weight: bold;"><?= e($r['check_in_time'] ?: '07:30') ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline btn-sm" onclick='openExaminerAttModal(<?= json_encode($r) ?>)'>
                                    ✏️ Presensi
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Presensi Penguji -->
<div class="modal-overlay" id="examinerAttModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Presensi Penguji Ujian Praktik</div>
            <button type="button" class="modal-close" data-modal-close="examinerAttModal">&times;</button>
        </div>
        <form action="<?= url('/attendance/examiners/save') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="assignment_id" id="modalExAssignmentId">
            <div class="modal-body">
                <div style="background: #f8fafc; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 16px; font-size: 13px;">
                    <div>Penguji: <strong id="modalExName"></strong></div>
                    <div>Mata Pelajaran: <strong id="modalExSubject"></strong> (<span id="modalExLocation"></span>)</div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Status Kehadiran</label>
                            <select name="status" id="modalExStatus" class="form-control" required>
                                <option value="hadir">Hadir Tepat Waktu</option>
                                <option value="terlambat">Hadir Terlambat</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jam Hadir</label>
                            <input type="text" name="check_in_time" id="modalExCheckIn" class="form-control" value="07:30" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Tambahan</label>
                    <input type="text" name="notes" id="modalExNotes" class="form-control" placeholder="Keterangan pengujian...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="examinerAttModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Presensi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openExaminerAttModal(r) {
    document.getElementById('modalExAssignmentId').value = r.id;
    document.getElementById('modalExName').textContent = r.examiner_name;
    document.getElementById('modalExSubject').textContent = r.subject_name;
    document.getElementById('modalExLocation').textContent = r.location;
    document.getElementById('modalExStatus').value = r.attendance_status || 'hadir';
    document.getElementById('modalExCheckIn').value = r.check_in_time || '07:30';
    document.getElementById('modalExNotes').value = r.attendance_notes || '';
    openModal('examinerAttModal');
}
</script>
