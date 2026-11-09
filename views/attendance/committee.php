<div class="page-header">
    <div>
        <h1 class="page-title">Daftar Hadir Panitia Ujian</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Terintegrasi otomatis dengan struktur kepanitiaan ujian.</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('/attendance/committee/print?date=' . $selectedDate) ?>" target="_blank" class="btn btn-primary">
            🖨️ Cetak Presensi Terisi
        </a>
        <a href="<?= url('/attendance/committee/print?date=' . $selectedDate . '&blank=1') ?>" target="_blank" class="btn btn-outline">
            📝 Cetak Format Tanda Tangan Manual
        </a>
        <a href="<?= url('/attendance/committee/export-excel?date=' . $selectedDate) ?>" class="btn btn-secondary">
            📤 Ekspor Excel
        </a>
    </div>
</div>

<form method="GET" action="<?= url('/attendance/committee') ?>" class="filter-bar">
    <label style="font-weight: 600; font-size: 13px;">Pilih Tanggal Kegiatan:</label>
    <input type="date" name="date" class="form-control" style="width: auto;" value="<?= e($selectedDate) ?>" onchange="this.form.submit()">
    <button type="submit" class="btn btn-secondary">Tampilkan</button>
</form>

<form action="<?= url('/attendance/committee/save') ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="date" value="<?= e($selectedDate) ?>">

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                📅 Presensi Panitia Tanggal: <?= format_day_id($selectedDate) ?>, <?= format_date_id($selectedDate) ?>
            </div>
            <span class="badge badge-info"><?= count($members) ?> Personel Panitia</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th>Nama Panitia</th>
                        <th>Jabatan dalam Panitia</th>
                        <th style="width: 120px;">Jam Datang</th>
                        <th style="width: 120px;">Jam Pulang</th>
                        <th>Status Kehadiran</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($members)): ?>
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 30px; color: #94a3b8;">
                                Belum ada struktur panitia yang ditambahkan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($members as $idx => $m): ?>
                            <?php $curr = $m['attendance_status'] ?: 'hadir'; ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td>
                                    <strong><?= e($m['teacher_name'] ?: $m['custom_name']) ?></strong><br>
                                    <span style="font-size: 11px; color: #64748b;">NIP. <?= e($m['teacher_nip'] ?: $m['custom_nip'] ?: '-') ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?= e($m['role_title']) ?></span>
                                    <?php if ($m['section_name']): ?>
                                        <div style="font-size: 11px; color: #475569; margin-top: 2px;"><?= e($m['section_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="text" name="attendances[<?= $m['id'] ?>][check_in]" class="form-control" style="font-size: 12px;" value="<?= e($m['check_in_time'] ?: '07:00') ?>">
                                </td>
                                <td>
                                    <input type="text" name="attendances[<?= $m['id'] ?>][check_out]" class="form-control" style="font-size: 12px;" value="<?= e($m['check_out_time'] ?: '15:30') ?>">
                                </td>
                                <td>
                                    <select name="attendances[<?= $m['id'] ?>][status]" class="form-control" style="font-size: 12px;">
                                        <option value="hadir" <?= $curr === 'hadir' ? 'selected' : '' ?>>Hadir</option>
                                        <option value="tidak_hadir" <?= $curr === 'tidak_hadir' ? 'selected' : '' ?>>Tidak Hadir</option>
                                        <option value="izin" <?= $curr === 'izin' ? 'selected' : '' ?>>Izin</option>
                                        <option value="sakit" <?= $curr === 'sakit' ? 'selected' : '' ?>>Sakit</option>
                                        <option value="terlambat" <?= $curr === 'terlambat' ? 'selected' : '' ?>>Terlambat</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="attendances[<?= $m['id'] ?>][notes]" class="form-control" style="font-size: 12px;" placeholder="Catatan tugas..." value="<?= e($m['attendance_notes']) ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                💾 Simpan Presensi Panitia Tanggal Ini
            </button>
        </div>
    </div>
</form>
