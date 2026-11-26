<div class="page-header">
    <div>
        <h1 class="page-title">Pelaksanaan Ujian, Presensi & Berita Acara</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Pengisian daftar hadir peserta, catatan kejadian khusus, dan berita acara ruang ujian.</p>
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
                    <th>Pengawas Ruang</th>
                    <th class="text-center">Presensi Peserta</th>
                    <th class="text-center">Status Berita Acara</th>
                    <th style="width: 170px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sessions)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada jadwal sesi atau ruang yang dapat dikelola.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sessions as $idx => $s): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= format_day_id($s['exam_date']) ?></strong><br>
                                <span style="font-size: 12px; color: #64748b;"><?= format_date_id($s['exam_date']) ?></span>
                            </td>
                            <td class="text-center"><span class="badge badge-info">Sesi <?= e($s['session']) ?></span></td>
                            <td class="text-center">
                                <strong><?= e($s['room_number']) ?></strong><br>
                                <span style="font-size: 11px; color: #64748b;"><?= e($s['room_name']) ?></span>
                            </td>
                            <td>
                                <strong><?= e($s['subject_name']) ?></strong> (<code><?= e($s['subject_code']) ?></code>)
                            </td>
                            <td style="font-size: 12px;">
                                1. <?= e($s['sup1_name'] ?: '(Belum ditentukan)') ?><br>
                                <?php if ($s['sup2_name']): ?>
                                    2. <?= e($s['sup2_name']) ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($s['minutes_id']): ?>
                                    <span class="badge badge-success"><?= $s['present_count'] ?> Hadir</span>
                                    <?php if ($s['absent_count'] > 0): ?>
                                        <span class="badge badge-danger"><?= $s['absent_count'] ?> Absen</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Belum Diisi</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($s['minutes_id']): ?>
                                    <span class="badge badge-success">✓ Lengkap</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Belum Dibuat</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('/written-exam/execution/manage?schedule_id=' . $s['schedule_id'] . '&room_id=' . $s['room_id']) ?>" class="btn btn-primary btn-sm">
                                    ✍️ Isi Presensi & BAP
                                </a>
                                <?php if ($s['minutes_id']): ?>
                                    <a href="<?= url('/written-exam/execution/print-minutes?schedule_id=' . $s['schedule_id'] . '&room_id=' . $s['room_id']) ?>" target="_blank" class="btn btn-outline btn-sm" title="Cetak Berita Acara">
                                        🖨️
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
