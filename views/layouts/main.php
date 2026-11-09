<?php
$currentUser = user();
$activeEvent = active_event();
$school = school_setting();
$currentUrl = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$allEvents = \App\Core\Database::fetchAll("SELECT * FROM exam_events ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Aplikasi Administrasi Ujian Sekolah') ?> - RUANG UJIAN</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/print.css') ?>" media="print">
    <script>
        window.APP_BASE_URL = '<?= app_base_url() ?>';
    </script>
</head>
<body>
<div class="app-wrapper">
    <!-- Sidebar Overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar Navigation -->
    <aside class="app-sidebar" id="appSidebar">
        <div class="sidebar-header">
            <div class="brand-icon">RU</div>
            <div>
                <div class="brand-title">RUANG UJIAN</div>
                <div class="brand-sub">Sistem Administrasi</div>
            </div>
        </div>

        <nav class="sidebar-menu">
            <div class="menu-category">Utama</div>
            <a href="<?= url('/dashboard') ?>" class="menu-item <?= str_contains($currentUrl, '/dashboard') ? 'active' : '' ?>">
                <span class="menu-icon">📊</span> Dashboard
            </a>

            <?php if (has_role(['super_admin', 'admin'])): ?>
            <a href="<?= url('/school') ?>" class="menu-item <?= str_contains($currentUrl, '/school') ? 'active' : '' ?>">
                <span class="menu-icon">🏫</span> Identitas Sekolah
            </a>
            <?php endif; ?>

            <?php if (has_role(['super_admin', 'admin', 'operator'])): ?>
            <div class="menu-category">Master Data</div>
            <a href="<?= url('/master/students') ?>" class="menu-item <?= str_contains($currentUrl, '/master/students') ? 'active' : '' ?>">
                <span class="menu-icon">👨‍🎓</span> Data Siswa
            </a>
            <a href="<?= url('/master/classes') ?>" class="menu-item <?= str_contains($currentUrl, '/master/classes') ? 'active' : '' ?>">
                <span class="menu-icon">🏛️</span> Data Kelas
            </a>
            <a href="<?= url('/master/teachers') ?>" class="menu-item <?= str_contains($currentUrl, '/master/teachers') ? 'active' : '' ?>">
                <span class="menu-icon">👨‍🏫</span> Data Guru & Tendik
            </a>
            <a href="<?= url('/master/subjects') ?>" class="menu-item <?= str_contains($currentUrl, '/master/subjects') ? 'active' : '' ?>">
                <span class="menu-icon">📚</span> Data Mata Pelajaran
            </a>
            <?php endif; ?>

            <?php if (has_role(['super_admin', 'admin', 'ketua_panitia', 'sekretaris', 'operator'])): ?>
            <div class="menu-category">Kegiatan & Kepanitiaan</div>
            <a href="<?= url('/events') ?>" class="menu-item <?= str_contains($currentUrl, '/events') ? 'active' : '' ?>">
                <span class="menu-icon">📅</span> Manajemen Kegiatan
            </a>
            <a href="<?= url('/committee') ?>" class="menu-item <?= (str_contains($currentUrl, '/committee') && !str_contains($currentUrl, '/work-program')) ? 'active' : '' ?>">
                <span class="menu-icon">👥</span> Struktur & Dokumen SK
            </a>
            <a href="<?= url('/committee/letters') ?>" class="menu-item <?= str_contains($currentUrl, '/committee/letters') ? 'active' : '' ?>">
                <span class="menu-icon">✉️</span> Undangan & Surat Tugas
            </a>
            <a href="<?= url('/work-program') ?>" class="menu-item <?= str_contains($currentUrl, '/work-program') ? 'active' : '' ?>">
                <span class="menu-icon">📋</span> Program Kerja & RAB
            </a>
            <?php endif; ?>

            <?php if (has_role(['super_admin', 'admin', 'ketua_panitia', 'sekretaris', 'operator'])): ?>
            <div class="menu-category">Persiapan Naskah & Dokumen</div>
            <a href="<?= url('/preparation/rules') ?>" class="menu-item <?= str_contains($currentUrl, '/preparation/rules') ? 'active' : '' ?>">
                <span class="menu-icon">📜</span> Tata Tertib & Juknis
            </a>
            <a href="<?= url('/preparation/submissions') ?>" class="menu-item <?= str_contains($currentUrl, '/preparation/submissions') ? 'active' : '' ?>">
                <span class="menu-icon">📝</span> Checklist Dokumen Soal
            </a>
            <a href="<?= url('/preparation/printing') ?>" class="menu-item <?= str_contains($currentUrl, '/preparation/printing') ? 'active' : '' ?>">
                <span class="menu-icon">🖨️</span> Penggandaan Naskah
            </a>
            <?php endif; ?>

            <?php if (has_role(['super_admin', 'admin', 'ketua_panitia', 'sekretaris', 'operator', 'pengawas'])): ?>
            <div class="menu-category">Ujian Tertulis</div>
            <a href="<?= url('/written-exam/schedules') ?>" class="menu-item <?= str_contains($currentUrl, '/written-exam/schedules') ? 'active' : '' ?>">
                <span class="menu-icon">🗓️</span> Jadwal Ujian Tertulis
            </a>
            <?php if (!has_role('pengawas')): ?>
            <a href="<?= url('/written-exam/rooms') ?>" class="menu-item <?= str_contains($currentUrl, '/written-exam/rooms') ? 'active' : '' ?>">
                <span class="menu-icon">🚪</span> Ruang & Penempatan
            </a>
            <a href="<?= url('/written-exam/seating') ?>" class="menu-item <?= str_contains($currentUrl, '/written-exam/seating') ? 'active' : '' ?>">
                <span class="menu-icon">🪑</span> Denah Tempat Duduk
            </a>
            <a href="<?= url('/written-exam/supervisors') ?>" class="menu-item <?= str_contains($currentUrl, '/written-exam/supervisors') ? 'active' : '' ?>">
                <span class="menu-icon">👀</span> Penugasan Pengawas
            </a>
            <?php endif; ?>
            <a href="<?= url('/written-exam/execution') ?>" class="menu-item <?= str_contains($currentUrl, '/written-exam/execution') ? 'active' : '' ?>">
                <span class="menu-icon">✍️</span> Presensi & Berita Acara
            </a>
            <?php endif; ?>

            <?php if (has_role(['super_admin', 'admin', 'ketua_panitia', 'sekretaris', 'operator', 'penguji'])): ?>
            <div class="menu-category">Ujian Praktik</div>
            <a href="<?= url('/practical-exam') ?>" class="menu-item <?= str_contains($currentUrl, '/practical-exam') ? 'active' : '' ?>">
                <span class="menu-icon">🧪</span> Jadwal & Stasiun Praktik
            </a>
            <a href="<?= url('/practical-exam/execution') ?>" class="menu-item <?= str_contains($currentUrl, '/practical-exam/execution') ? 'active' : '' ?>">
                <span class="menu-icon">🔬</span> Presensi & Pelaksanaan
            </a>
            <?php endif; ?>

            <?php if (has_role(['super_admin', 'admin', 'ketua_panitia', 'sekretaris', 'operator'])): ?>
            <div class="menu-category">Kehadiran & Distribusi</div>
            <a href="<?= url('/attendance/committee') ?>" class="menu-item <?= str_contains($currentUrl, '/attendance/committee') ? 'active' : '' ?>">
                <span class="menu-icon">✅</span> Kehadiran Panitia
            </a>
            <a href="<?= url('/attendance/supervisors') ?>" class="menu-item <?= str_contains($currentUrl, '/attendance/supervisors') ? 'active' : '' ?>">
                <span class="menu-icon">📋</span> Kehadiran Pengawas
            </a>
            <a href="<?= url('/attendance/examiners') ?>" class="menu-item <?= str_contains($currentUrl, '/attendance/examiners') ? 'active' : '' ?>">
                <span class="menu-icon">🩺</span> Kehadiran Penguji Praktik
            </a>
            <a href="<?= url('/distribution/handover') ?>" class="menu-item <?= str_contains($currentUrl, '/distribution/handover') ? 'active' : '' ?>">
                <span class="menu-icon">📦</span> Distribusi Naskah Soal
            </a>
            <?php endif; ?>

            <?php if (has_role(['super_admin', 'admin', 'ketua_panitia', 'sekretaris', 'operator'])): ?>
            <div class="menu-category">Lembar Jawaban & Packing</div>
            <a href="<?= url('/answer-sheets/receipts') ?>" class="menu-item <?= str_contains($currentUrl, '/answer-sheets/receipts') ? 'active' : '' ?>">
                <span class="menu-icon">📥</span> Penerimaan Lembar Jawaban
            </a>
            <a href="<?= url('/answer-sheets/packing') ?>" class="menu-item <?= str_contains($currentUrl, '/answer-sheets/packing') ? 'active' : '' ?>">
                <span class="menu-icon">✉️</span> Packing & Label 3 Mode
            </a>
            <a href="<?= url('/answer-sheets/handover') ?>" class="menu-item <?= str_contains($currentUrl, '/answer-sheets/handover') ? 'active' : '' ?>">
                <span class="menu-icon">📑</span> Rekap Penyerahan LJ
            </a>
            <?php endif; ?>

            <div class="menu-category">Cetak & Laporan</div>
            <a href="<?= url('/print') ?>" class="menu-item <?= str_contains($currentUrl, '/print') ? 'active' : '' ?>">
                <span class="menu-icon">🖨️</span> Pusat Cetak Perlengkapan
            </a>
            <a href="<?= url('/reports/minutes') ?>" class="menu-item <?= str_contains($currentUrl, '/reports/minutes') ? 'active' : '' ?>">
                <span class="menu-icon">📜</span> Berita Acara & Dokumen
            </a>
            <a href="<?= url('/reports/final') ?>" class="menu-item <?= str_contains($currentUrl, '/reports/final') ? 'active' : '' ?>">
                <span class="menu-icon">🏆</span> Laporan Akhir 18 Bab
            </a>

            <?php if (has_role(['super_admin', 'admin'])): ?>
            <div class="menu-category">Sistem & Pengguna</div>
            <a href="<?= url('/users') ?>" class="menu-item <?= str_contains($currentUrl, '/users') ? 'active' : '' ?>">
                <span class="menu-icon">👤</span> Manajemen Pengguna
            </a>
            <a href="<?= url('/backup') ?>" class="menu-item <?= str_contains($currentUrl, '/backup') ? 'active' : '' ?>">
                <span class="menu-icon">💾</span> Backup & Restore SQLite
            </a>
            <?php endif; ?>

            <div style="height: 40px;"></div>
        </nav>
    </aside>

    <!-- Main Container -->
    <div class="app-main">
        <!-- Topbar -->
        <header class="app-topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Buka/Tutup Menu">
                    ☰
                </button>
                <div class="event-switcher">
                    <span>📌 Kegiatan Aktif:</span>
                    <select id="activeEventSelector" title="Pilih Kegiatan Ujian">
                        <?php if (empty($allEvents)): ?>
                            <option value="">(Belum ada kegiatan)</option>
                        <?php else: ?>
                            <?php foreach ($allEvents as $ev): ?>
                                <option value="<?= $ev['id'] ?>" <?= ($activeEvent && $activeEvent['id'] == $ev['id']) ? 'selected' : '' ?>>
                                    <?= e($ev['name']) ?> (TP <?= e($ev['academic_year']) ?> - <?= e($ev['semester']) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="topbar-right">
                <div class="user-badge">
                    <div class="user-avatar"><?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)) ?></div>
                    <div class="user-info">
                        <div class="user-name"><?= e($currentUser['full_name'] ?? 'Pengguna') ?></div>
                        <div class="user-role"><?= e($currentUser['role_display'] ?? $currentUser['role'] ?? 'User') ?></div>
                    </div>
                </div>

                <a href="<?= url('/logout') ?>" class="btn btn-outline btn-sm" title="Keluar">
                    🚪 Keluar
                </a>
            </div>
        </header>

        <!-- Page Body -->
        <main class="page-container">
            <!-- Flash Messages -->
            <?php if ($msg = get_flash('success')): ?>
                <div class="alert alert-success">
                    <span class="alert-icon">✓</span>
                    <div><?= e($msg) ?></div>
                </div>
            <?php endif; ?>

            <?php if ($msg = get_flash('error')): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">✕</span>
                    <div><?= e($msg) ?></div>
                </div>
            <?php endif; ?>

            <?php if ($msg = get_flash('warning')): ?>
                <div class="alert alert-warning">
                    <span class="alert-icon">⚠</span>
                    <div><?= e($msg) ?></div>
                </div>
            <?php endif; ?>

            <?php if ($msg = get_flash('info')): ?>
                <div class="alert alert-info">
                    <span class="alert-icon">ℹ</span>
                    <div><?= e($msg) ?></div>
                </div>
            <?php endif; ?>

            <!-- View Content Injection -->
            <?= $content ?? '' ?>
        </main>
    </div>
</div>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
