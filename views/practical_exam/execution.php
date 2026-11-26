<div class="page-header">
    <div>
        <h1 class="page-title">Pelaksanaan & Presensi Ujian Praktik</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Daftar sesi pelaksanaan ujian praktik, presensi peserta, berita acara, dan dokumentasi kegiatan.</p>
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
                    <th>Lokasi & Stasiun</th>
                    <th>Waktu</th>
                    <th class="text-center">Penguji</th>
                    <th class="text-center">Kehadiran</th>
                    <th style="width: 140px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada sesi ujian praktik yang dijadwalkan.
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
                            <td><?= e($s['location']) ?> (<?= e($s['station'] ?: 'Semua Pos') ?>)</td>
                            <td class="font-bold"><?= e($s['start_time']) ?> - <?= e($s['end_time']) ?></td>
                            <td class="text-center">
                                <span class="badge badge-info"><?= $s['examiner_count'] ?> Penguji</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success"><?= $s['present_count'] ?> Hadir</span>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('/practical-exam/execution/manage?schedule_id=' . $s['id']) ?>" class="btn btn-primary btn-sm">
                                    🔬 Kelola & Presensi
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
