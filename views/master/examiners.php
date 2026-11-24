<div class="page-header">
    <div>
        <h1 class="page-title">Data Penguji Praktik</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Daftar penugasan guru penguji ujian praktik per mata pelajaran, sesi, dan stasiun uji.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="assignExaminerModal">
            ➕ Tugaskan Penguji Praktik
        </button>
        <a href="<?= url('/attendance/examiners/print') ?>" target="_blank" class="btn btn-outline" style="background: #fff;">
            🖨️ Cetak Daftar Presensi Penguji
        </a>
        <a href="<?= url('/practical-exam') ?>" class="btn btn-secondary">
            🧪 Kelola Jadwal Praktik
        </a>
    </div>
</div>

<!-- Statistik Ringkas Penguji Praktik -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Penugasan Penguji</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #7c3aed;"><?= count($examiners) ?></div>
            <div style="background: #f5f3ff; color: #7c3aed; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Penguji Aktif</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Total penugasan guru penguji</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Sesi / Stasiun Praktik</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #0284c7;"><?= count($schedules) ?></div>
            <div style="background: #f0f9ff; color: #0284c7; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Sesi Terjadwal</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Jadwal stasiun & pos praktik</div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Nama Guru Penguji</th>
                    <th>Mata Pelajaran</th>
                    <th>Peran Penguji</th>
                    <th>Hari & Tanggal Ujian</th>
                    <th>Waktu Pelaksanaan</th>
                    <th>Lokasi & Stasiun Uji</th>
                    <th style="width: 100px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($examiners)): ?>
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 40px; color: #94a3b8;">
                            <div style="font-size: 32px; margin-bottom: 8px;">🩺</div>
                            <div style="font-size: 15px; font-weight: 600; color: #475569;">Belum ada penugasan guru penguji praktik.</div>
                            <div style="font-size: 13px; color: #64748b; margin-top: 6px;">Klik tombol "Tugaskan Penguji Praktik" di atas untuk menambahkan penguji ke jadwal sesi.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($examiners as $idx => $ex): ?>
                        <tr>
                            <td class="text-center font-bold text-muted"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= e($ex['teacher_name']) ?></strong>
                                <?php if (!empty($ex['teacher_nip'])): ?>
                                    <div style="font-size: 11px; color: #64748b;">NIP: <?= e($ex['teacher_nip']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= e($ex['subject_name']) ?></strong> (<code><?= e($ex['subject_code']) ?></code>)
                                <span class="badge badge-secondary" style="font-size: 10px; margin-left: 4px;">Kls <?= e($ex['grade']) ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $ex['examiner_role'] === 'Penguji Utama' ? 'badge-primary' : 'badge-info' ?>">
                                    <?= e($ex['examiner_role']) ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= format_day_id($ex['exam_date']) ?></strong><br>
                                <span style="font-size: 11px; color: #64748b;"><?= format_date_id($ex['exam_date']) ?></span>
                            </td>
                            <td class="font-bold text-center">
                                <?= e($ex['start_time']) ?> - <?= e($ex['end_time']) ?> WIB
                            </td>
                            <td>
                                <strong><?= e($ex['location']) ?></strong>
                                <?php if (!empty($ex['station'])): ?>
                                    <div style="font-size: 11px; color: #64748b;">Stasiun / Pos: <?= e($ex['station']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <form action="<?= url('/practical-exam/delete-examiner') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus penugasan penguji <?= e($ex['teacher_name']) ?> ini?', this)">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $ex['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus Penugasan">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tugaskan Penguji Baru -->
<div class="modal-overlay" id="assignExaminerModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tugaskan Penguji Ujian Praktik</div>
            <button type="button" class="modal-close" data-modal-close="assignExaminerModal">&times;</button>
        </div>
        <form action="<?= url('/practical-exam/assign-examiner') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Pilih Jadwal Sesi / Mapel Praktik</label>
                    <select name="practical_schedule_id" class="form-control" required>
                        <option value="">-- Pilih Jadwal & Mata Pelajaran --</option>
                        <?php foreach ($schedules as $sch): ?>
                            <option value="<?= $sch['id'] ?>">
                                <?= e($sch['subject_name']) ?> (Kls <?= e($sch['grade']) ?>) - <?= format_date_id($sch['exam_date']) ?> [<?= e($sch['location']) ?>]
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Nama Guru Penguji</label>
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
