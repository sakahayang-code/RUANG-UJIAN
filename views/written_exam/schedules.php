<div class="page-header">
    <div>
        <h1 class="page-title">Jadwal Ujian Tertulis</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Pengaturan tanggal, hari, sesi, dan jam pelaksanaan ujian tertulis.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addScheduleModal">
            🗓️ Tambah Jadwal Sesi
        </button>
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
                    <th class="text-center">Waktu Mulai - Selesai</th>
                    <th>Mata Pelajaran</th>
                    <th>Tingkat</th>
                    <th>Keterangan</th>
                    <th style="width: 140px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada jadwal sesi ujian tertulis. Klik "Tambah Jadwal Sesi" di atas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($schedules as $idx => $s): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= e($s['day_name']) ?></strong><br>
                                <span style="font-size: 12px; color: #64748b;"><?= format_date_id($s['exam_date']) ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">Sesi <?= e($s['session']) ?></span>
                            </td>
                            <td class="text-center font-bold">
                                <?= e($s['start_time']) ?> - <?= e($s['end_time']) ?> WIB
                            </td>
                            <td>
                                <strong><?= e($s['subject_name']) ?></strong> (<code><?= e($s['subject_code']) ?></code>)
                            </td>
                            <td><span class="badge badge-secondary"><?= e($s['grade']) ?></span></td>
                            <td style="font-size: 12px;"><?= e($s['notes'] ?: '-') ?></td>
                            <td class="text-center">
                                <div style="display: inline-flex; gap: 4px; align-items: center;">
                                    <button type="button"
                                        class="btn btn-secondary btn-sm"
                                        data-schedule="<?= e(json_encode([
                                            'id' => $s['id'],
                                            'exam_date' => $s['exam_date'] ?? '',
                                            'session' => $s['session'] ?? '1',
                                            'start_time' => $s['start_time'] ?? '',
                                            'end_time' => $s['end_time'] ?? '',
                                            'subject_id' => $s['subject_id'] ?? '',
                                            'grade' => $s['grade'] ?? 'VII',
                                            'notes' => $s['notes'] ?? '',
                                        ], JSON_HEX_APOS | JSON_HEX_QUOT)) ?>"
                                        onclick="openEditScheduleModal(this)">
                                        ✏️ Edit
                                    </button>
                                    <form action="<?= url('/written-exam/schedules/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus jadwal sesi ini?', this)">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
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

<!-- Modal Tambah Jadwal -->
<div class="modal-overlay" id="addScheduleModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tambah Jadwal Sesi Ujian Tertulis</div>
            <button type="button" class="modal-close" data-modal-close="addScheduleModal">&times;</button>
        </div>
        <form action="<?= url('/written-exam/schedules/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tanggal Ujian</label>
                            <input type="date" name="exam_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Sesi Ke-</label>
                            <select name="session" class="form-control" required>
                                <option value="1">Sesi 1</option>
                                <option value="2">Sesi 2</option>
                                <option value="3">Sesi 3</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jam Mulai</label>
                            <input type="text" name="start_time" class="form-control" placeholder="07:30" value="07:30" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jam Selesai</label>
                            <input type="text" name="end_time" class="form-control" placeholder="09:30" value="09:30" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label required">Mata Pelajaran</label>
                            <select name="subject_id" class="form-control" required>
                                <option value="">Pilih Mata Pelajaran</option>
                                <?php foreach ($subjects as $sub): ?>
                                    <option value="<?= $sub['id'] ?>"><?= e($sub['code']) ?> - <?= e($sub['name']) ?> (<?= e($sub['grade']) ?>)</option>
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
                                <option value="Semua Tingkat">Semua Tingkat</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan Tambahan</label>
                    <input type="text" name="notes" class="form-control" placeholder="Catatan khusus sesi...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addScheduleModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Jadwal -->
<div class="modal-overlay" id="editScheduleModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">✏️ Edit Jadwal Sesi Ujian Tertulis</div>
            <button type="button" class="modal-close" data-modal-close="editScheduleModal">&times;</button>
        </div>
        <form action="<?= url('/written-exam/schedules/update') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit_sched_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tanggal Ujian</label>
                            <input type="date" name="exam_date" id="edit_sched_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Sesi Ke-</label>
                            <select name="session" id="edit_sched_session" class="form-control" required>
                                <option value="1">Sesi 1</option>
                                <option value="2">Sesi 2</option>
                                <option value="3">Sesi 3</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jam Mulai</label>
                            <input type="text" name="start_time" id="edit_sched_start" class="form-control" placeholder="07:30" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jam Selesai</label>
                            <input type="text" name="end_time" id="edit_sched_end" class="form-control" placeholder="09:30" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label required">Mata Pelajaran</label>
                            <select name="subject_id" id="edit_sched_subject_id" class="form-control" required>
                                <option value="">Pilih Mata Pelajaran</option>
                                <?php foreach ($subjects as $sub): ?>
                                    <option value="<?= $sub['id'] ?>"><?= e($sub['code']) ?> - <?= e($sub['name']) ?> (<?= e($sub['grade']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tingkat</label>
                            <select name="grade" id="edit_sched_grade" class="form-control" required>
                                <option value="VII">VII</option>
                                <option value="VIII">VIII</option>
                                <option value="IX">IX</option>
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                                <option value="Semua Tingkat">Semua Tingkat</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan Tambahan</label>
                    <input type="text" name="notes" id="edit_sched_notes" class="form-control" placeholder="Catatan khusus sesi...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editScheduleModal">Batal</button>
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditScheduleModal(btn) {
    try {
        const d = JSON.parse(btn.getAttribute('data-schedule'));
        document.getElementById('edit_sched_id').value = d.id || '';
        document.getElementById('edit_sched_date').value = d.exam_date || '';
        document.getElementById('edit_sched_session').value = d.session || '1';
        document.getElementById('edit_sched_start').value = d.start_time || '';
        document.getElementById('edit_sched_end').value = d.end_time || '';
        document.getElementById('edit_sched_subject_id').value = d.subject_id || '';
        document.getElementById('edit_sched_grade').value = d.grade || 'VII';
        document.getElementById('edit_sched_notes').value = d.notes || '';
        openModal('editScheduleModal');
    } catch (e) {
        console.error('Gagal membuka modal edit jadwal:', e);
    }
}
</script>
