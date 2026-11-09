<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard Administrasi Ujian</h1>
        <p class="page-subtitle">
            <?= e($school['name']) ?> • Tahun Pelajaran <?= e($school['academic_year']) ?>
        </p>
    </div>
    <div class="page-actions">
        <a href="<?= url('/print') ?>" class="btn btn-primary">
            🖨️ Cetak Perlengkapan
        </a>
        <a href="<?= url('/work-program') ?>" class="btn btn-secondary">
            📋 Program Kerja & RAB
        </a>
    </div>
</div>

<!-- Event Info Banner -->
<div class="card" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); color: #ffffff; border: none; margin-bottom: 24px;">
    <div class="card-body" style="padding: 24px;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
            <div>
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.85; font-weight: 600;">Kegiatan Ujian Aktif</div>
                <h2 style="font-size: 22px; font-weight: 800; margin: 4px 0; color: #fff;">
                    <?= $event ? e($event['name']) : 'Belum Ada Kegiatan Ujian Aktif' ?>
                </h2>
                <div style="font-size: 13px; opacity: 0.9; margin-top: 4px;">
                    <?php if ($event): ?>
                        Jenis: <strong><?= e($event['exam_type']) ?></strong> •
                        Periode: <strong><?= format_date_id($event['start_date']) ?> s.d. <?= format_date_id($event['end_date']) ?></strong> •
                        Status: <span class="badge badge-success" style="vertical-align: middle;"><?= strtoupper(e($event['status'])) ?></span>
                    <?php else: ?>
                        Silakan buat atau aktifkan kegiatan ujian melalui menu <strong>Kegiatan Ujian</strong>.
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <a href="<?= url('/events') ?>" class="btn btn-secondary" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.3);">
                    ⚙️ Kelola Kegiatan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Notifications Center -->
<?php if (!empty($notifications)): ?>
<div class="card" style="border-left: 4px solid #f59e0b; margin-bottom: 24px;">
    <div class="card-header" style="background: #fffbeb;">
        <div class="card-title" style="color: #92400e; display: flex; align-items: center; gap: 8px;">
            <span>🔔</span> <strong>Pusat Notifikasi & Kendali Administrasi</strong>
        </div>
        <span class="badge badge-warning"><?= count($notifications) ?> Perhatian</span>
    </div>
    <div class="card-body" style="padding: 12px 20px;">
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($notifications as $n): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; background: #fff; border: 1px solid #fed7aa; border-radius: var(--radius-sm);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 16px;"><?= $n['type'] === 'danger' ? '🔴' : ($n['type'] === 'warning' ? '🟠' : '🔵') ?></span>
                        <div>
                            <div style="font-weight: 600; font-size: 13px; color: #1e293b;"><?= e($n['title']) ?></div>
                            <div style="font-size: 12px; color: #64748b;"><?= e($n['message']) ?></div>
                        </div>
                    </div>
                    <?php if (!empty($n['url'])): ?>
                        <a href="<?= $n['url'] ?>" class="btn btn-outline btn-sm" style="font-size: 11px;">
                            Tindak Lanjut →
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">👨‍🎓</div>
        <div class="stat-info">
            <div class="stat-label">Peserta Ujian</div>
            <div class="stat-value"><?= number_format($participantCount) ?></div>
            <div class="stat-subtext">Dari <?= number_format($studentCount) ?> siswa terdaftar</div>
        </div>
    </div>

    <div class="stat-card indigo">
        <div class="stat-icon indigo">🏛️</div>
        <div class="stat-info">
            <div class="stat-label">Jumlah Kelas</div>
            <div class="stat-value"><?= number_format($classCount) ?></div>
            <div class="stat-subtext">Tingkat VII, VIII, IX</div>
        </div>
    </div>

    <div class="stat-card success">
        <div class="stat-icon success">📚</div>
        <div class="stat-info">
            <div class="stat-label">Mata Pelajaran</div>
            <div class="stat-value"><?= number_format($subjectCount) ?></div>
            <div class="stat-subtext">Tertulis & Praktik</div>
        </div>
    </div>

    <div class="stat-card purple">
        <div class="stat-icon purple">🚪</div>
        <div class="stat-info">
            <div class="stat-label">Ruang Ujian</div>
            <div class="stat-value"><?= number_format($roomCount) ?></div>
            <div class="stat-subtext">Ruang tertulis aktif</div>
        </div>
    </div>

    <div class="stat-card primary">
        <div class="stat-icon primary">👥</div>
        <div class="stat-info">
            <div class="stat-label">Panitia Ujian</div>
            <div class="stat-value"><?= number_format($committeeCount) ?></div>
            <div class="stat-subtext">Struktur kepanitiaan</div>
        </div>
    </div>

    <div class="stat-card warning">
        <div class="stat-icon warning">👀</div>
        <div class="stat-info">
            <div class="stat-label">Pengawas Ruang</div>
            <div class="stat-value"><?= number_format($supervisorCount) ?></div>
            <div class="stat-subtext">Petugas pengawas ruang</div>
        </div>
    </div>

    <div class="stat-card success">
        <div class="stat-icon success">🩺</div>
        <div class="stat-info">
            <div class="stat-label">Penguji Praktik</div>
            <div class="stat-value"><?= number_format($examinerCount) ?></div>
            <div class="stat-subtext">Penugasan ujian praktik</div>
        </div>
    </div>

    <div class="stat-card indigo">
        <div class="stat-icon indigo">🗓️</div>
        <div class="stat-info">
            <div class="stat-label">Jadwal Sesi</div>
            <div class="stat-value"><?= number_format($scheduleCount) ?></div>
            <div class="stat-subtext">Sesi ujian tertulis</div>
        </div>
    </div>
</div>

<!-- Progress Metrics -->
<div class="card">
    <div class="card-header">
        <div class="card-title">📈 Progres Tahapan Penyelenggaraan Ujian</div>
        <span class="badge badge-info">Pemantauan Terpadu</span>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                    <span>1. Administrasi Dokumen Soal & Kisi-kisi</span>
                    <span><?= $docProgress ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $docProgress ?>%;"></div>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Penerimaan kisi-kisi, naskah soal, dan dokumen guru.</div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                    <span>2. Penggandaan Naskah Soal</span>
                    <span><?= $prepProgress ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $prepProgress ?>%; background: #059669;"></div>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Pencetakan naskah utama dan cadangan per mapel.</div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                    <span>3. Pelaksanaan Ujian (Berita Acara & Presensi)</span>
                    <span><?= $examProgress ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $examProgress ?>%; background: #2563eb;"></div>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Pengisian berita acara dan presensi per ruang/sesi.</div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                    <span>4. Penerimaan & Packing Lembar Jawaban</span>
                    <span><?= $answerSheetProgress ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $answerSheetProgress ?>%; background: #7c3aed;"></div>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Verifikasi fisik, pencocokan jumlah hadir, dan packing 3 mode.</div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
                    <span>5. Laporan Akhir Kegiatan Panitia</span>
                    <span><?= $reportProgress ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $reportProgress ?>%; background: #d97706;"></div>
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Penyusunan dan pengesahan laporan 18 bab terpadu.</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Shortcuts -->
<div class="card">
    <div class="card-header">
        <div class="card-title">⚡ Akses Cepat Modul Utama</div>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
            <a href="<?= url('/print/participant-cards') ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 12px;">
                <span>🪪</span> <span>Cetak Kartu Peserta</span>
            </a>
            <a href="<?= url('/print/question-labels') ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 12px;">
                <span>🏷️</span> <span>Label Naskah per Ruang</span>
            </a>
            <a href="<?= url('/answer-sheets/packing') ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 12px;">
                <span>✉️</span> <span>Packing Lembar Jawaban</span>
            </a>
            <a href="<?= url('/attendance/supervisors') ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 12px;">
                <span>📋</span> <span>Presensi Pengawas Ruang</span>
            </a>
            <a href="<?= url('/distribution/handover') ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 12px;">
                <span>📦</span> <span>Distribusi Naskah Soal</span>
            </a>
            <a href="<?= url('/reports/final') ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 12px;">
                <span>🏆</span> <span>Laporan Akhir 18 Bab</span>
            </a>
        </div>
    </div>
</div>
