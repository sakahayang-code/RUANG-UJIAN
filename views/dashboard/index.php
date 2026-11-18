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
<div class="card hero-banner" style="background: linear-gradient(135deg, #090d16 0%, #1e1b4b 45%, #312e81 80%, #4338ca 100%); color: #ffffff; border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; box-shadow: 0 12px 30px -5px rgba(15, 23, 42, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.15); margin-bottom: 24px; position: relative; overflow: hidden;">
    <div style="position: absolute; right: -50px; top: -50px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(168, 85, 247, 0.3) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
    <div style="position: absolute; left: 20%; bottom: -60px; width: 180px; height: 180px; background: radial-gradient(circle, rgba(6, 182, 212, 0.18) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
    <div class="card-body" style="padding: 26px 28px; position: relative; z-index: 2;">
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 18px;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1.2px; font-weight: 800; color: #a5b4fc;">Kegiatan Ujian Aktif</span>
                    <?php if ($event): ?>
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 2px 8px; border-radius: 9999px; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); font-size: 11px; font-weight: 700; color: #6ee7b7;">
                            <span class="pulse-indicator" style="width: 6px; height: 6px;"></span>
                            <?= strtoupper(e($event['status'])) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <h2 style="font-size: 24px; font-weight: 800; margin: 4px 0 8px 0; color: #fff; letter-spacing: -0.4px;">
                    <?= $event ? e($event['name']) : 'Belum Ada Kegiatan Ujian Aktif' ?>
                </h2>
                <div style="font-size: 13px; opacity: 0.95; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                    <?php if ($event): ?>
                        <span>Jenis: <strong><?= e($event['exam_type']) ?></strong></span>
                        <span style="opacity: 0.6;">•</span>
                        <span>Periode: <strong><?= format_date_id($event['start_date']) ?> s.d. <?= format_date_id($event['end_date']) ?></strong></span>
                    <?php else: ?>
                        Silakan buat atau aktifkan kegiatan ujian melalui menu <strong>Kegiatan Ujian</strong>.
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <a href="<?= url('/events') ?>" class="btn btn-secondary" style="background: rgba(255,255,255,0.14); color: #fff; border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); font-weight: 700; padding: 10px 18px; border-radius: 12px; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);">
                    ⚙️ Kelola Kegiatan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Notifications Center -->
<?php if (!empty($notifications)): ?>
<div class="card" style="border-left: 5px solid #f59e0b; margin-bottom: 24px; border-radius: 16px; box-shadow: 0 4px 18px rgba(245, 158, 11, 0.08);">
    <div class="card-header" style="background: linear-gradient(90deg, #fffbeb 0%, #ffffff 100%);">
        <div class="card-title" style="color: #92400e; display: flex; align-items: center; gap: 8px; font-weight: 700;">
            <span>🔔</span> <span>Pusat Notifikasi & Kendali Administrasi</span>
        </div>
        <span class="badge badge-warning"><?= count($notifications) ?> Perhatian</span>
    </div>
    <div class="card-body" style="padding: 14px 20px;">
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($notifications as $n): ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #fff; border: 1px solid #fed7aa; border-radius: 12px; transition: transform 0.15s ease;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 18px;"><?= $n['type'] === 'danger' ? '🔴' : ($n['type'] === 'warning' ? '🟠' : '🔵') ?></span>
                        <div>
                            <div style="font-weight: 700; font-size: 13px; color: #1e293b;"><?= e($n['title']) ?></div>
                            <div style="font-size: 12px; color: #64748b; margin-top: 1px;"><?= e($n['message']) ?></div>
                        </div>
                    </div>
                    <?php if (!empty($n['url'])): ?>
                        <a href="<?= $n['url'] ?>" class="btn btn-outline btn-sm" style="font-size: 11.5px; border-radius: 8px;">
                            Tindak Lanjut →
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modern SaaS Colorful Statistic Cards -->
<div class="stats-grid">
    <!-- 1. Peserta Ujian (Primary / Indigo) -->
    <a href="<?= url('/master/students') ?>" class="stat-card-link">
        <div class="stat-card primary">
            <div class="stat-card-top">
                <div class="stat-icon primary">
                    <span>👨‍🎓</span>
                </div>
                <span class="stat-badge primary">Terdaftar</span>
            </div>
            <div class="stat-value"><?= number_format($participantCount) ?></div>
            <div class="stat-label">Peserta Ujian</div>
            <div class="stat-footer">
                <span class="stat-footer-icon">👥</span>
                <span>Dari <?= number_format($studentCount) ?> total siswa aktif</span>
            </div>
        </div>
    </a>

    <!-- 2. Jumlah Kelas (Violet / Purple) -->
    <a href="<?= url('/master/classes') ?>" class="stat-card-link">
        <div class="stat-card indigo">
            <div class="stat-card-top">
                <div class="stat-icon indigo">
                    <span>🏛️</span>
                </div>
                <span class="stat-badge indigo">3 Tingkat</span>
            </div>
            <div class="stat-value"><?= number_format($classCount) ?></div>
            <div class="stat-label">Jumlah Rombel</div>
            <div class="stat-footer">
                <span class="stat-footer-icon">🏫</span>
                <span>Kelas VII, VIII, dan IX</span>
            </div>
        </div>
    </a>

    <!-- 3. Mata Pelajaran (Teal / Emerald) -->
    <a href="<?= url('/master/subjects') ?>" class="stat-card-link">
        <div class="stat-card teal">
            <div class="stat-card-top">
                <div class="stat-icon teal">
                    <span>📚</span>
                </div>
                <span class="stat-badge teal">Kurikulum</span>
            </div>
            <div class="stat-value"><?= number_format($subjectCount) ?></div>
            <div class="stat-label">Mata Pelajaran</div>
            <div class="stat-footer">
                <span class="stat-footer-icon">📖</span>
                <span>Ujian Tertulis & Praktik</span>
            </div>
        </div>
    </a>

    <!-- 4. Ruang Ujian (Cyan / Sky) -->
    <a href="<?= url('/written-exam/rooms') ?>" class="stat-card-link">
        <div class="stat-card cyan">
            <div class="stat-card-top">
                <div class="stat-icon cyan">
                    <span>🚪</span>
                </div>
                <span class="stat-badge cyan">Kapasitas</span>
            </div>
            <div class="stat-value"><?= number_format($roomCount) ?></div>
            <div class="stat-label">Ruang Ujian</div>
            <div class="stat-footer">
                <span class="stat-footer-icon">🪑</span>
                <span>Atur denah & kursi →</span>
            </div>
        </div>
    </a>

    <!-- 5. Panitia Ujian (Pink / Rose) -->
    <a href="<?= url('/committee') ?>" class="stat-card-link">
        <div class="stat-card pink">
            <div class="stat-card-top">
                <div class="stat-icon pink">
                    <span>👥</span>
                </div>
                <span class="stat-badge pink">SK Panitia</span>
            </div>
            <div class="stat-value"><?= number_format($committeeCount) ?></div>
            <div class="stat-label">Panitia Ujian</div>
            <div class="stat-footer">
                <span class="stat-footer-icon">📋</span>
                <span>Struktur SK & Administrasi</span>
            </div>
        </div>
    </a>

    <!-- 6. Pengawas Ruang (Amber / Warning) -->
    <a href="<?= url('/written-exam/supervisors') ?>" class="stat-card-link">
        <div class="stat-card warning">
            <div class="stat-card-top">
                <div class="stat-icon warning">
                    <span>👀</span>
                </div>
                <span class="stat-badge warning">Bertugas</span>
            </div>
            <div class="stat-value"><?= number_format($supervisorCount) ?></div>
            <div class="stat-label">Pengawas Ruang</div>
            <div class="stat-footer">
                <span class="stat-footer-icon">📝</span>
                <span>Penugasan & Jadwal Ruang</span>
            </div>
        </div>
    </a>

    <!-- 7. Penguji Praktik (Ruby / Crimson) -->
    <a href="<?= url('/practical-exam') ?>" class="stat-card-link">
        <div class="stat-card rose">
            <div class="stat-card-top">
                <div class="stat-icon rose">
                    <span>🩺</span>
                </div>
                <span class="stat-badge rose">Praktik</span>
            </div>
            <div class="stat-value"><?= number_format($examinerCount) ?></div>
            <div class="stat-label">Penguji Praktik</div>
            <div class="stat-footer">
                <span class="stat-footer-icon">🔬</span>
                <span>Stasiun & Penilaian Praktik</span>
            </div>
        </div>
    </a>

    <!-- 8. Jadwal Sesi (Blue / Cobalt) -->
    <a href="<?= url('/written-exam/schedules') ?>" class="stat-card-link">
        <div class="stat-card blue">
            <div class="stat-card-top">
                <div class="stat-icon blue">
                    <span>🗓️</span>
                </div>
                <span class="stat-badge blue">Sesi Ujian</span>
            </div>
            <div class="stat-value"><?= number_format($scheduleCount) ?></div>
            <div class="stat-label">Jadwal Sesi</div>
            <div class="stat-footer">
                <span class="stat-footer-icon">⏱️</span>
                <span>Agenda tertulis & sesi</span>
            </div>
        </div>
    </a>
</div>

<!-- Progress Metrics -->
<div class="card" style="box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04), inset 0 1px 0 rgba(255, 255, 255, 0.9);">
    <div class="card-header" style="background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);">
        <div class="card-title" style="display: flex; align-items: center; gap: 8px;">
            <span>📈</span> <span>Progres Tahapan Penyelenggaraan Ujian</span>
        </div>
        <span class="badge badge-info" style="border-radius: 9999px; font-weight: 700; padding: 4px 10px;">Pemantauan Terpadu</span>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 600; margin-bottom: 8px;">
                    <span style="color: #1e293b;">1. Dokumen Soal & Kisi-kisi</span>
                    <span class="badge" style="background: rgba(79, 70, 229, 0.1); color: #4338ca; font-weight: 800; border-radius: 9999px; padding: 2px 8px;"><?= $docProgress ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $docProgress ?>%;"></div>
                </div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 6px;">Penerimaan kisi-kisi, naskah soal, dan dokumen guru.</div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 600; margin-bottom: 8px;">
                    <span style="color: #1e293b;">2. Penggandaan Naskah Soal</span>
                    <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #065f46; font-weight: 800; border-radius: 9999px; padding: 2px 8px;"><?= $prepProgress ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $prepProgress ?>%; background: linear-gradient(90deg, #059669 0%, #10b981 100%);"></div>
                </div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 6px;">Pencetakan naskah utama dan cadangan per mapel.</div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 600; margin-bottom: 8px;">
                    <span style="color: #1e293b;">3. Pelaksanaan (Berita Acara & Hadir)</span>
                    <span class="badge" style="background: rgba(37, 99, 235, 0.1); color: #1d4ed8; font-weight: 800; border-radius: 9999px; padding: 2px 8px;"><?= $examProgress ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $examProgress ?>%; background: linear-gradient(90deg, #1d4ed8 0%, #3b82f6 100%);"></div>
                </div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 6px;">Pengisian berita acara dan presensi per ruang/sesi.</div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 600; margin-bottom: 8px;">
                    <span style="color: #1e293b;">4. Packing Lembar Jawaban (3 Mode)</span>
                    <span class="badge" style="background: rgba(124, 58, 237, 0.1); color: #6d28d9; font-weight: 800; border-radius: 9999px; padding: 2px 8px;"><?= $answerSheetProgress ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $answerSheetProgress ?>%; background: linear-gradient(90deg, #6d28d9 0%, #8b5cf6 100%);"></div>
                </div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 6px;">Verifikasi fisik, pencocokan presensi, dan packing 3 mode.</div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 600; margin-bottom: 8px;">
                    <span style="color: #1e293b;">5. Laporan Akhir Kegiatan Panitia</span>
                    <span class="badge" style="background: rgba(217, 119, 6, 0.1); color: #92400e; font-weight: 800; border-radius: 9999px; padding: 2px 8px;"><?= $reportProgress ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $reportProgress ?>%; background: linear-gradient(90deg, #d97706 0%, #f59e0b 100%);"></div>
                </div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 6px;">Penyusunan dan pengesahan laporan 18 bab terpadu.</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Shortcuts -->
<div class="card" style="box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04), inset 0 1px 0 rgba(255, 255, 255, 0.9);">
    <div class="card-header" style="background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);">
        <div class="card-title" style="display: flex; align-items: center; gap: 8px;">
            <span>⚡</span> <span>Akses Cepat Modul Utama</span>
        </div>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px;">
            <a href="<?= url('/print/participant-cards') ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 12px 14px; border-radius: 12px; gap: 10px; font-weight: 600; border-color: #e2e8f0; background: #ffffff;">
                <span style="font-size: 18px;">🪪</span> <span>Cetak Kartu Peserta</span>
            </a>
            <a href="<?= url('/print/question-labels') ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 12px 14px; border-radius: 12px; gap: 10px; font-weight: 600; border-color: #e2e8f0; background: #ffffff;">
                <span style="font-size: 18px;">🏷️</span> <span>Label Naskah per Ruang</span>
            </a>
            <a href="<?= url('/answer-sheets/packing') ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 12px 14px; border-radius: 12px; gap: 10px; font-weight: 600; border-color: #e2e8f0; background: #ffffff;">
                <span style="font-size: 18px;">✉️</span> <span>Packing Lembar Jawaban</span>
            </a>
            <a href="<?= url('/attendance/supervisors') ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 12px 14px; border-radius: 12px; gap: 10px; font-weight: 600; border-color: #e2e8f0; background: #ffffff;">
                <span style="font-size: 18px;">📋</span> <span>Presensi Pengawas Ruang</span>
            </a>
            <a href="<?= url('/distribution/handover') ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 12px 14px; border-radius: 12px; gap: 10px; font-weight: 600; border-color: #e2e8f0; background: #ffffff;">
                <span style="font-size: 18px;">📦</span> <span>Distribusi Naskah Soal</span>
            </a>
            <a href="<?= url('/reports/final') ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 12px 14px; border-radius: 12px; gap: 10px; font-weight: 600; border-color: #e2e8f0; background: #ffffff;">
                <span style="font-size: 18px;">🏆</span> <span>Laporan Akhir 18 Bab</span>
            </a>
        </div>
    </div>
</div>
