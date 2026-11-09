<div class="page-header">
    <div>
        <a href="<?= url('/practical-exam/execution') ?>" style="font-size: 13px; color: #64748b; text-decoration: none;">← Kembali ke Daftar Sesi Praktik</a>
        <h1 class="page-title" style="margin-top: 4px;">Ujian Praktik <?= e($schedule['subject_name']) ?></h1>
        <p class="page-subtitle"><?= format_day_id($schedule['exam_date']) ?>, <?= format_date_id($schedule['exam_date']) ?> • Lokasi: <?= e($schedule['location']) ?> (<?= e($schedule['station'] ?: 'Semua Pos') ?>) • <?= e($schedule['start_time']) ?> - <?= e($schedule['end_time']) ?> WIB</p>
    </div>
</div>

<!-- Examiners Info -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <div class="card-title">👨‍🏫 Dewan Penguji Praktik Ditugaskan</div>
    </div>
    <div class="card-body">
        <?php if (empty($examiners)): ?>
            <div style="color: #94a3b8; font-size: 13px;">Belum ada penguji yang ditugaskan. Silakan tambahkan pada menu Jadwal Praktik.</div>
        <?php else: ?>
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <?php foreach ($examiners as $ex): ?>
                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: var(--radius-sm); padding: 10px 14px; min-width: 220px;">
                        <span class="badge badge-info" style="font-size: 10px;"><?= e($ex['examiner_role']) ?></span>
                        <div style="font-weight: 700; margin-top: 4px;"><?= e($ex['teacher_name']) ?></div>
                        <div style="font-size: 11px; color: #64748b;">NIP. <?= e($ex['teacher_nip'] ?: '-') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<form action="<?= url('/practical-exam/execution/save-attendance') ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="schedule_id" value="<?= $schedule['id'] ?>">

    <!-- Attendance Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📝 Presensi Peserta Ujian Praktik (<?= count($participants) ?> Peserta Tingkat <?= e($schedule['grade']) ?>)</div>
            <div style="font-size: 11px; color: #64748b;">Aplikasi mencatat status kehadiran administratif. Tidak ada input nilai akademik.</div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th>No. Peserta</th>
                        <th>Nama Peserta Didik</th>
                        <th>Kelas Asal</th>
                        <th>Status Kehadiran</th>
                        <th>Catatan Unjuk Kerja (Administrasi)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($participants)): ?>
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 30px; color: #94a3b8;">
                                Belum ada peserta terdaftar untuk tingkat ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($participants as $idx => $p): ?>
                            <?php $curr = $p['attendance_status'] ?: 'hadir'; ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><code><?= e($p['participant_number']) ?></code></td>
                                <td><strong><?= e($p['student_name_snapshot']) ?></strong></td>
                                <td><span class="badge badge-secondary"><?= e($p['class_name_snapshot']) ?></span></td>
                                <td>
                                    <div style="display: flex; gap: 12px; align-items: center;">
                                        <label style="display: flex; align-items: center; gap: 4px; font-size: 12.5px; cursor: pointer; color: #15803d; font-weight: 600;">
                                            <input type="radio" name="attendances[<?= $p['id'] ?>][status]" value="hadir" <?= $curr === 'hadir' ? 'checked' : '' ?>> Hadir
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 4px; font-size: 12.5px; cursor: pointer; color: #b91c1c;">
                                            <input type="radio" name="attendances[<?= $p['id'] ?>][status]" value="tidak_hadir" <?= $curr === 'tidak_hadir' ? 'checked' : '' ?>> Tidak Hadir
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 4px; font-size: 12.5px; cursor: pointer; color: #b45309;">
                                            <input type="radio" name="attendances[<?= $p['id'] ?>][status]" value="izin" <?= $curr === 'izin' ? 'checked' : '' ?>> Izin
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 4px; font-size: 12.5px; cursor: pointer; color: #9333ea;">
                                            <input type="radio" name="attendances[<?= $p['id'] ?>][status]" value="sakit" <?= $curr === 'sakit' ? 'checked' : '' ?>> Sakit
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="attendances[<?= $p['id'] ?>][notes]" class="form-control" style="font-size: 12px; padding: 4px 8px;" placeholder="Catatan alat / kehadiran..." value="<?= e($p['attendance_notes']) ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Minutes and Incident Notes -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📜 Berita Acara & Catatan Khusus Ujian Praktik</div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Catatan Kejadian Khusus / K3 / Kondisi Alat</label>
                <textarea name="incident_notes" class="form-control" rows="2" placeholder="Catatan keselamatan kerja atau kendala alat/bahan..."><?= e($minutes['incident_notes'] ?? '') ?></textarea>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Catatan Penguji Mengenai Kelancaran Praktik</label>
                <textarea name="examiner_notes" class="form-control" rows="2" placeholder="Pelaksanaan ujian praktik berlangsung tertib dan seluruh tahapan rotasi terselesaikan."><?= e($minutes['examiner_notes'] ?? 'Ujian praktik terselenggara dengan aman, tertib, dan lancar.') ?></textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary btn-lg">
                💾 Simpan Presensi & Berita Acara Praktik
            </button>
        </div>
    </div>
</form>

<!-- Media Documentation Upload & Gallery -->
<div class="card">
    <div class="card-header">
        <div class="card-title">📸 Dokumentasi Foto & Video Kegiatan Praktik</div>
        <button type="button" class="btn btn-outline btn-sm" data-modal-open="uploadDocModal">
            📤 Unggah Dokumentasi
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($documents)): ?>
            <div style="text-align: center; padding: 25px; color: #94a3b8; font-size: 13px;">
                Belum ada foto atau video dokumentasi yang diunggah untuk sesi praktik ini.
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
                <?php foreach ($documents as $doc): ?>
                    <div style="border: 1px solid var(--border-color); border-radius: var(--radius-sm); overflow: hidden; background: #fff; box-shadow: var(--shadow-sm);">
                        <?php if ($doc['file_type'] === 'image'): ?>
                            <img src="<?= url('/file/view?path=' . urlencode($doc['file_path'])) ?>" alt="Foto" style="width: 100%; height: 140px; object-fit: cover;">
                        <?php elseif ($doc['file_type'] === 'video'): ?>
                            <video controls style="width: 100%; height: 140px; background: #000;">
                                <source src="<?= url('/file/view?path=' . urlencode($doc['file_path'])) ?>" type="video/mp4">
                            </video>
                        <?php else: ?>
                            <div style="height: 140px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; font-size: 28px;">
                                📄
                            </div>
                        <?php endif; ?>
                        <div style="padding: 8px 10px; font-size: 11.5px;">
                            <div style="font-weight: 600; color: #1e293b;"><?= e($doc['caption'] ?: 'Dokumentasi Praktik') ?></div>
                            <div style="color: #64748b; margin-top: 2px;"><?= format_date_id($doc['created_at'], true) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Upload Dokumen -->
<div class="modal-overlay" id="uploadDocModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Unggah Foto / Video Dokumentasi Praktik</div>
            <button type="button" class="modal-close" data-modal-close="uploadDocModal">&times;</button>
        </div>
        <form action="<?= url('/practical-exam/execution/upload-document') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="schedule_id" value="<?= $schedule['id'] ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Pilih Berkas Foto atau Video</label>
                    <input type="file" name="file" class="form-control" accept="image/*,video/mp4,application/pdf" required>
                    <div class="form-text">Mendukung format JPG, PNG, MP4, atau PDF (maks 25MB).</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Keterangan / Caption Foto</label>
                    <input type="text" name="caption" class="form-control" placeholder="Contoh: Peserta sedang melakukan titrasi larutan...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="uploadDocModal">Batal</button>
                <button type="submit" class="btn btn-primary">Unggah Berkas</button>
            </div>
        </form>
    </div>
</div>
