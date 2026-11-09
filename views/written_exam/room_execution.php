<div class="page-header">
    <div>
        <a href="<?= url('/written-exam/execution') ?>" style="font-size: 13px; color: #64748b; text-decoration: none;">← Kembali ke Daftar Sesi</a>
        <h1 class="page-title" style="margin-top: 4px;">Administrasi <?= e($room['name']) ?> (<?= e($room['room_number']) ?>)</h1>
        <p class="page-subtitle"><?= e($schedule['subject_name']) ?> • <?= format_day_id($schedule['exam_date']) ?>, <?= format_date_id($schedule['exam_date']) ?> (Sesi <?= e($schedule['session']) ?> - <?= e($schedule['start_time']) ?> s.d. <?= e($schedule['end_time']) ?> WIB)</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('/written-exam/execution/print-minutes?schedule_id=' . $schedule['id'] . '&room_id=' . $room['id']) ?>" target="_blank" class="btn btn-outline">
            🖨️ Cetak Berita Acara Ruang
        </a>
    </div>
</div>

<form action="<?= url('/written-exam/execution/save-attendance') ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="schedule_id" value="<?= $schedule['id'] ?>">
    <input type="hidden" name="room_id" value="<?= $room['id'] ?>">

    <!-- Daftar Hadir Peserta Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📝 Daftar Hadir Peserta Ruang Ujian (<?= count($participants) ?> Peserta)</div>
            <div style="font-size: 12px; color: #64748b;">
                Tandai status kehadiran setiap peserta.
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">Meja</th>
                        <th>No. Peserta</th>
                        <th>Nama Siswa</th>
                        <th>Kelas Asal</th>
                        <th>Status Kehadiran</th>
                        <th>Catatan Khusus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($participants)): ?>
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 30px; color: #94a3b8;">
                                Belum ada peserta yang ditempatkan di ruang ini. Silakan atur di menu Pembagian Ruang.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($participants as $idx => $p): ?>
                            <?php $currentStatus = $p['attendance_status'] ?: 'hadir'; ?>
                            <tr>
                                <td class="text-center font-bold">#<?= $p['seat_number'] ?></td>
                                <td><code><?= e($p['participant_number']) ?></code></td>
                                <td><strong><?= e($p['student_name_snapshot']) ?></strong></td>
                                <td><span class="badge badge-secondary"><?= e($p['class_name_snapshot']) ?></span></td>
                                <td>
                                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                                        <label style="display: flex; align-items: center; gap: 4px; font-size: 12.5px; cursor: pointer; color: #15803d; font-weight: 600;">
                                            <input type="radio" name="attendances[<?= $p['id'] ?>][status]" value="hadir" <?= $currentStatus === 'hadir' ? 'checked' : '' ?>> Hadir
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 4px; font-size: 12.5px; cursor: pointer; color: #b91c1c;">
                                            <input type="radio" name="attendances[<?= $p['id'] ?>][status]" value="tidak_hadir" <?= $currentStatus === 'tidak_hadir' ? 'checked' : '' ?>> Tidak Hadir
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 4px; font-size: 12.5px; cursor: pointer; color: #b45309;">
                                            <input type="radio" name="attendances[<?= $p['id'] ?>][status]" value="izin" <?= $currentStatus === 'izin' ? 'checked' : '' ?>> Izin
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 4px; font-size: 12.5px; cursor: pointer; color: #9333ea;">
                                            <input type="radio" name="attendances[<?= $p['id'] ?>][status]" value="sakit" <?= $currentStatus === 'sakit' ? 'checked' : '' ?>> Sakit
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 4px; font-size: 12.5px; cursor: pointer; color: #4338ca;">
                                            <input type="radio" name="attendances[<?= $p['id'] ?>][status]" value="terlambat" <?= $currentStatus === 'terlambat' ? 'checked' : '' ?>> Terlambat
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 4px; font-size: 12.5px; cursor: pointer; color: #0284c7;">
                                            <input type="radio" name="attendances[<?= $p['id'] ?>][status]" value="susulan" <?= $currentStatus === 'susulan' ? 'checked' : '' ?>> Ikut Susulan
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="attendances[<?= $p['id'] ?>][notes]" class="form-control" style="font-size: 12px; padding: 4px 8px;" placeholder="Keterangan..." value="<?= e($p['attendance_notes']) ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Berita Acara & Catatan Jalannya Ujian -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📜 Catatan Berita Acara Pelaksanaan Ruang</div>
        </div>
        <div class="card-body">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Catatan Pengawas Mengenai Jalannya Ujian</label>
                <textarea name="minutes_notes" class="form-control" rows="3" placeholder="Contoh: Ujian berlangsung tertib, aman, dan terkendali. Tidak ada kendala teknis naskah soal."><?= e($minutes['notes'] ?? 'Ujian tertulis berlangsung dengan tertib, lancar, dan aman sesuai petunjuk teknis.') ?></textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary btn-lg">
                💾 Simpan Presensi & Berita Acara Ruang
            </button>
        </div>
    </div>
</form>

<!-- Catatan Kejadian Khusus Card -->
<div class="card">
    <div class="card-header">
        <div class="card-title">⚠️ Buku Catatan Kejadian Khusus Ruang</div>
        <button type="button" class="btn btn-warning btn-sm" data-modal-open="addIncidentModal">
            ➕ Catat Kejadian Khusus
        </button>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Waktu</th>
                        <th>Siswa Terkait</th>
                        <th>Uraian Kejadian Khusus</th>
                        <th>Tindak Lanjut / Solusi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($incidents)): ?>
                        <tr>
                            <td colspan="4" class="text-center" style="padding: 20px; color: #94a3b8;">
                                Tidak ada catatan kejadian khusus / insiden yang dilaporkan pada ruang ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($incidents as $inc): ?>
                            <tr>
                                <td class="font-bold"><code><?= e($inc['incident_time']) ?> WIB</code></td>
                                <td><?= e($inc['student_name_snapshot'] ?: '(Umum / Ruangan)') ?></td>
                                <td><strong><?= e($inc['incident_description']) ?></strong></td>
                                <td style="color: #059669;"><?= e($inc['follow_up'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Kejadian Khusus -->
<div class="modal-overlay" id="addIncidentModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Catat Kejadian Khusus di Ruang Ujian</div>
            <button type="button" class="modal-close" data-modal-close="addIncidentModal">&times;</button>
        </div>
        <form action="<?= url('/written-exam/execution/add-incident') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="schedule_id" value="<?= $schedule['id'] ?>">
            <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Waktu Kejadian</label>
                            <input type="text" name="incident_time" class="form-control" value="<?= date('H:i') ?>" required>
                        </div>
                    </div>
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label">Siswa Terkait (Opsional)</label>
                            <select name="participant_id" class="form-control">
                                <option value="">(Kejadian Umum / Tidak Terkait Siswa Tertentu)</option>
                                <?php foreach ($participants as $p): ?>
                                    <option value="<?= $p['id'] ?>">#<?= $p['seat_number'] ?> - <?= e($p['student_name_snapshot']) ?> (<?= e($p['class_name_snapshot']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Uraian Kejadian</label>
                    <textarea name="incident_description" class="form-control" rows="3" placeholder="Contoh: Peserta mengeluh sakit perut di tengah ujian, naskah sobek halaman 2, dll." required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label required">Tindak Lanjut / Penanganan</label>
                    <input type="text" name="follow_up" class="form-control" placeholder="Contoh: Diantar ke UKS, diganti dengan naskah cadangan..." required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addIncidentModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Catatan Kejadian</button>
            </div>
        </form>
    </div>
</div>
