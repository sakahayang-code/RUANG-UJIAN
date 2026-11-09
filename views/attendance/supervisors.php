<div class="page-header">
    <div>
        <h1 class="page-title">Daftar Hadir Pengawas Ruang</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Data otomatis terintegrasi dari penugasan pengawas tanpa input nama ulang.</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('/attendance/supervisors/print?date=' . $dateFilter) ?>" target="_blank" class="btn btn-primary">
            🖨️ Cetak Presensi Terisi
        </a>
        <a href="<?= url('/attendance/supervisors/print?date=' . $dateFilter . '&blank=1') ?>" target="_blank" class="btn btn-outline">
            📝 Cetak Format Tanda Tangan Manual
        </a>
    </div>
</div>

<form method="GET" action="<?= url('/attendance/supervisors') ?>" class="filter-bar">
    <div style="min-width: 150px;">
        <label style="font-size: 11px; font-weight: 600; color: #475569; display: block;">Tanggal:</label>
        <input type="date" name="date" class="form-control" value="<?= e($dateFilter) ?>" onchange="this.form.submit()">
    </div>
    <div style="min-width: 120px;">
        <label style="font-size: 11px; font-weight: 600; color: #475569; display: block;">Sesi:</label>
        <select name="session" class="form-control" onchange="this.form.submit()">
            <option value="">Semua Sesi</option>
            <option value="1" <?= $sessionFilter === '1' ? 'selected' : '' ?>>Sesi 1</option>
            <option value="2" <?= $sessionFilter === '2' ? 'selected' : '' ?>>Sesi 2</option>
            <option value="3" <?= $sessionFilter === '3' ? 'selected' : '' ?>>Sesi 3</option>
        </select>
    </div>
    <div style="align-self: flex-end;">
        <button type="submit" class="btn btn-secondary">Filter</button>
        <?php if (!empty($dateFilter) || !empty($sessionFilter)): ?>
            <a href="<?= url('/attendance/supervisors') ?>" class="btn btn-outline">Reset</a>
        <?php endif; ?>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Tanggal & Waktu</th>
                    <th class="text-center">Sesi</th>
                    <th class="text-center">Ruang</th>
                    <th>Mata Pelajaran</th>
                    <th>Nama Pengawas Ruang</th>
                    <th>Status Presensi</th>
                    <th>Jam Hadir</th>
                    <th>Catatan</th>
                    <th style="width: 110px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($supervisors)): ?>
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada jadwal penugasan pengawas untuk kriteria filter ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($supervisors as $idx => $s): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= format_day_id($s['exam_date']) ?></strong><br>
                                <span style="font-size: 11px; color: #64748b;"><?= format_date_id($s['exam_date']) ?></span><br>
                                <span style="font-size: 11px;"><?= e($s['start_time']) ?> - <?= e($s['end_time']) ?></span>
                            </td>
                            <td class="text-center"><span class="badge badge-info">Sesi <?= e($s['session']) ?></span></td>
                            <td class="text-center">
                                <strong><?= e($s['room_number']) ?></strong><br>
                                <span style="font-size: 11px; color: #64748b;"><?= e($s['room_name']) ?></span>
                            </td>
                            <td><?= e($s['subject_name']) ?></td>
                            <td>
                                <div><strong>1. <?= e($s['sup1_name'] ?: '(Belum Ditugaskan)') ?></strong></div>
                                <?php if ($s['sup2_name']): ?>
                                    <div style="color: #475569; font-size: 12px;">2. <?= e($s['sup2_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status = $s['attendance_status'] ?: 'hadir';
                                $badge = match($status) {
                                    'hadir' => 'badge-success',
                                    'tidak_hadir' => 'badge-danger',
                                    'terlambat' => 'badge-warning',
                                    'sakit', 'izin' => 'badge-info',
                                    default => 'badge-secondary'
                                };
                                ?>
                                <span class="badge <?= $badge ?>"><?= ucfirst(e($status)) ?></span>
                            </td>
                            <td style="font-size: 12px; font-weight: bold;">
                                <?= e($s['check_in_time'] ?: '-') ?>
                            </td>
                            <td style="font-size: 11px; color: #64748b;">
                                <?= e($s['attendance_notes'] ?: '-') ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline btn-sm" onclick='openSupAttendanceModal(<?= json_encode($s) ?>)'>
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

<!-- Modal Presensi Pengawas -->
<div class="modal-overlay" id="supAttendanceModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Catat Presensi Kehadiran Pengawas</div>
            <button type="button" class="modal-close" data-modal-close="supAttendanceModal">&times;</button>
        </div>
        <form action="<?= url('/attendance/supervisors/save') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="assignment_id" id="modalSupAssignmentId">
            <div class="modal-body">
                <div style="background: #f8fafc; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 16px; font-size: 13px;">
                    <div>Ruang: <strong id="modalSupRoom"></strong></div>
                    <div>Pengawas: <strong id="modalSupName"></strong></div>
                    <div>Jadwal: <span id="modalSupSchedule"></span></div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Status Kehadiran</label>
                            <select name="status" id="modalSupStatus" class="form-control" required>
                                <option value="hadir">Hadir Tepat Waktu</option>
                                <option value="terlambat">Hadir Terlambat</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="tidak_hadir">Tidak Hadir (Alpa)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jam Hadir di Panitia</label>
                            <input type="text" name="check_in_time" id="modalSupCheckIn" class="form-control" value="07:15" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan Tambahan</label>
                    <input type="text" name="notes" id="modalSupNotes" class="form-control" placeholder="Pengganti / info tugas...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="supAttendanceModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Presensi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSupAttendanceModal(s) {
    document.getElementById('modalSupAssignmentId').value = s.supervisor_assignment_id;
    document.getElementById('modalSupRoom').textContent = s.room_number + ' (' + s.room_name + ')';
    document.getElementById('modalSupName').textContent = s.sup1_name || 'Pengawas';
    document.getElementById('modalSupSchedule').textContent = s.exam_date + ' (Sesi ' + s.session + ' - ' + s.subject_name + ')';
    document.getElementById('modalSupStatus').value = s.attendance_status || 'hadir';
    document.getElementById('modalSupCheckIn').value = s.check_in_time || '07:15';
    document.getElementById('modalSupNotes').value = s.attendance_notes || '';
    openModal('supAttendanceModal');
}
</script>
