<div class="page-header">
    <div>
        <h1 class="page-title">Jadwal & Stasiun Ujian Praktik</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Pengelolaan jadwal, lokasi uji, pos/stasiun, dan penugasan guru penguji praktik.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addPracticalModal">
            🧪 Tambah Sesi Praktik
        </button>
        <a href="<?= url('/practical-exam/execution') ?>" class="btn btn-success">
            🔬 Pelaksanaan & Presensi Praktik
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
                    <th>Mata Pelajaran</th>
                    <th>Tingkat</th>
                    <th>Waktu Pelaksanaan</th>
                    <th>Lokasi & Stasiun</th>
                    <th>Metode Uji</th>
                    <th class="text-center">Penguji Ditugaskan</th>
                    <th style="width: 150px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada jadwal sesi ujian praktik yang dibuat.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($schedules as $idx => $s): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= format_day_id($s['exam_date']) ?></strong><br>
                                <span style="font-size: 12px; color: #64748b;"><?= format_date_id($s['exam_date']) ?></span>
                            </td>
                            <td><strong><?= e($s['subject_name']) ?></strong></td>
                            <td><span class="badge badge-secondary"><?= e($s['grade']) ?></span></td>
                            <td class="font-bold"><?= e($s['start_time']) ?> - <?= e($s['end_time']) ?> WIB</td>
                            <td>
                                <strong><?= e($s['location']) ?></strong><br>
                                <span style="font-size: 11px; color: #64748b;">Stasiun: <?= e($s['station'] ?: 'Semua Pos') ?></span>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= ucfirst(e($s['method'])) ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $s['examiner_count'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $s['examiner_count'] ?> Penguji
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline btn-sm" onclick="openAssignExaminerModal(<?= $s['id'] ?>, '<?= e(addslashes($s['subject_name'])) ?>', '<?= e(addslashes($s['location'])) ?>')">
                                    ➕ Penguji
                                </button>
                                <a href="<?= url('/practical-exam/execution/manage?schedule_id=' . $s['id']) ?>" class="btn btn-primary btn-sm">
                                    Presensi
                                </a>
                                <form action="<?= url('/practical-exam/schedules/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus jadwal ujian praktik ini?', this)">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus Jadwal">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Sesi Praktik -->
<div class="modal-overlay" id="addPracticalModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tambah Sesi Ujian Praktik Baru</div>
            <button type="button" class="modal-close" data-modal-close="addPracticalModal">&times;</button>
        </div>
        <form action="<?= url('/practical-exam/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label required">Mata Pelajaran Praktik</label>
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
                                <option value="IX" selected>IX</option>
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
                            <label class="form-label required">Tanggal Praktik</label>
                            <input type="date" name="exam_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jam Mulai</label>
                            <input type="text" name="start_time" class="form-control" value="08:00" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jam Selesai</label>
                            <input type="text" name="end_time" class="form-control" value="11:30" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label required">Lokasi / Tempat Uji</label>
                            <input type="text" name="location" class="form-control" placeholder="Contoh: Lab Komputer / Lab IPA / Lapangan" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Stasiun / Pos</label>
                            <input type="text" name="station" class="form-control" placeholder="Pos 1 / Stasiun A">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Metode Ujian</label>
                            <select name="method" class="form-control" required>
                                <option value="kelompok">Kelompok</option>
                                <option value="individu">Individu</option>
                                <option value="rotasi">Rotasi Stasiun</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label">Keterangan Tambahan</label>
                            <input type="text" name="notes" class="form-control" placeholder="Kebutuhan alat & bahan khusus...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addPracticalModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Jadwal Praktik</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tugaskan Penguji -->
<div class="modal-overlay" id="assignExaminerModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tugaskan Guru Penguji Praktik</div>
            <button type="button" class="modal-close" data-modal-close="assignExaminerModal">&times;</button>
        </div>
        <form action="<?= url('/practical-exam/assign-examiner') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="practical_schedule_id" id="assignScheduleId">
            <div class="modal-body">
                <div style="background: #f8fafc; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 16px; font-size: 13px;">
                    <div>Mata Pelajaran: <strong id="assignSubjectInfo"></strong></div>
                    <div>Lokasi: <strong id="assignLocationInfo"></strong></div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Pilih Guru Penguji</label>
                    <select name="examiner_id" class="form-control" required>
                        <option value="">-- Pilih Guru --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (NIP: <?= e($t['nip'] ?: '-') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Peran Penguji</label>
                    <select name="examiner_role" class="form-control" required>
                        <option value="Penguji Utama">Penguji Utama</option>
                        <option value="Penguji Pendamping">Penguji Pendamping</option>
                        <option value="Penguji Teknis / Laboran">Penguji Teknis / Laboran</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="assignExaminerModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Penugasan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAssignExaminerModal(schedId, subName, loc) {
    document.getElementById('assignScheduleId').value = schedId;
    document.getElementById('assignSubjectInfo').textContent = subName;
    document.getElementById('assignLocationInfo').textContent = loc;
    openModal('assignExaminerModal');
}
</script>
