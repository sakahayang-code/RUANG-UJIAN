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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= e($title ?? 'Aplikasi Administrasi Ujian Sekolah') ?> - RUANG UJIAN</title>
    
    <!-- PWA Android Configuration -->
    <link rel="manifest" href="<?= url('/manifest.json') ?>">
    <meta name="theme-color" content="#1e3a8a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="RUANG UJIAN">
    <meta name="application-name" content="RUANG UJIAN">
    <meta name="format-detection" content="telephone=no">
    
    <!-- App Icons -->
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/icons/icon.svg') ?>">
    <link rel="alternate icon" type="image/png" href="<?= asset('images/icons/icon-192.png') ?>">
    <link rel="apple-touch-icon" href="<?= asset('images/icons/icon-192.png') ?>">
    
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
            <!-- Android PWA Install Banner -->
            <div class="android-install-banner" id="androidInstallBanner" style="display: none;">
                <div class="install-banner-left">
                    <img src="<?= asset('images/icons/icon-192.png') ?>" alt="Icon RUANG UJIAN" class="install-banner-icon">
                    <div class="install-banner-text">
                        <div class="install-banner-title">Pasang Aplikasi RUANG UJIAN</div>
                        <div class="install-banner-desc">Akses cepat & tampilan native APK di layar HP</div>
                    </div>
                </div>
                <div class="install-banner-right">
                    <button type="button" class="btn-pwa-install" id="btnInstallPwa">Pasang</button>
                    <button type="button" class="btn-pwa-dismiss" id="btnDismissPwa" title="Tutup">✕</button>
                </div>
            </div>

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

<!-- Android Native Bottom Navigation Bar (Material 3 Style) -->
<nav class="android-bottom-nav" id="androidBottomNav" aria-label="Navigasi Bawah Android">
    <a href="<?= url('/dashboard') ?>" class="android-nav-item <?= (str_contains($currentUrl, '/dashboard') || $currentUrl === '/') ? 'active' : '' ?>">
        <div class="nav-indicator">
            <span class="nav-icon">📊</span>
        </div>
        <span class="nav-label">Beranda</span>
    </a>

    <a href="<?= url('/written-exam/schedules') ?>" class="android-nav-item <?= (str_contains($currentUrl, '/written-exam/schedules') || str_contains($currentUrl, '/written-exam/rooms') || str_contains($currentUrl, '/written-exam/seating')) ? 'active' : '' ?>">
        <div class="nav-indicator">
            <span class="nav-icon">🗓️</span>
        </div>
        <span class="nav-label">Jadwal</span>
    </a>

    <a href="<?= url('/written-exam/execution') ?>" class="android-nav-item <?= (str_contains($currentUrl, '/written-exam/execution') || str_contains($currentUrl, '/attendance')) ? 'active' : '' ?>">
        <div class="nav-indicator">
            <span class="nav-icon">✍️</span>
        </div>
        <span class="nav-label">Presensi</span>
    </a>

    <a href="<?= url('/distribution/handover') ?>" class="android-nav-item <?= (str_contains($currentUrl, '/distribution') || str_contains($currentUrl, '/answer-sheets')) ? 'active' : '' ?>">
        <div class="nav-indicator">
            <span class="nav-icon">📦</span>
        </div>
        <span class="nav-label">Naskah</span>
    </a>

    <button type="button" class="android-nav-item nav-btn-more" id="androidMenuToggle" aria-label="Buka Semua Menu">
        <div class="nav-indicator">
            <span class="nav-icon">☰</span>
        </div>
        <span class="nav-label">Menu</span>
    </button>
</nav>

<!-- Android Native Modal Bottom Sheet Drawer for All Modules -->
<div class="android-sheet-backdrop" id="androidSheetBackdrop">
    <div class="android-bottom-sheet" id="androidBottomSheet">
        <div class="sheet-drag-handle"></div>
        
        <div class="sheet-header">
            <div class="sheet-user-card">
                <div class="user-avatar-sheet"><?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)) ?></div>
                <div class="sheet-user-meta">
                    <div class="sheet-user-name"><?= e($currentUser['full_name'] ?? 'Pengguna') ?></div>
                    <div class="sheet-user-role"><?= e($currentUser['role_display'] ?? $currentUser['role'] ?? 'User') ?></div>
                </div>
            </div>
            <button type="button" class="sheet-close-btn" id="androidSheetClose">✕</button>
        </div>

        <div class="sheet-active-event-pill">
            <span class="event-pill-icon">📌</span>
            <span class="event-pill-text"><?= e($activeEvent['name'] ?? 'Tidak ada kegiatan aktif') ?> (TP <?= e($activeEvent['academic_year'] ?? '-') ?>)</span>
        </div>

        <div class="sheet-body">
            <div class="sheet-category-title">Semua Menu Administrasi</div>
            <div class="sheet-grid">
                <a href="<?= url('/dashboard') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-blue">📊</div>
                    <span class="tile-text">Dashboard</span>
                </a>
                
                <?php if (has_role(['super_admin', 'admin'])): ?>
                <a href="<?= url('/school') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-indigo">🏫</div>
                    <span class="tile-text">Sekolah</span>
                </a>
                <?php endif; ?>

                <?php if (has_role(['super_admin', 'admin', 'operator'])): ?>
                <a href="<?= url('/master/students') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-teal">👨‍🎓</div>
                    <span class="tile-text">Siswa</span>
                </a>
                <a href="<?= url('/master/classes') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-cyan">🏛️</div>
                    <span class="tile-text">Kelas</span>
                </a>
                <a href="<?= url('/master/teachers') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-sky">👨‍🏫</div>
                    <span class="tile-text">Guru</span>
                </a>
                <a href="<?= url('/master/subjects') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-amber">📚</div>
                    <span class="tile-text">Mapel</span>
                </a>
                <?php endif; ?>

                <?php if (has_role(['super_admin', 'admin', 'ketua_panitia', 'sekretaris', 'operator'])): ?>
                <a href="<?= url('/events') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-purple">📅</div>
                    <span class="tile-text">Kegiatan</span>
                </a>
                <a href="<?= url('/committee') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-indigo">👥</div>
                    <span class="tile-text">Panitia</span>
                </a>
                <a href="<?= url('/committee/letters') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-emerald">✉️</div>
                    <span class="tile-text">Surat</span>
                </a>
                <a href="<?= url('/work-program') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-rose">📋</div>
                    <span class="tile-text">Program & RAB</span>
                </a>
                <a href="<?= url('/preparation/rules') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-amber">📜</div>
                    <span class="tile-text">Tata Tertib</span>
                </a>
                <a href="<?= url('/preparation/submissions') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-lime">📝</div>
                    <span class="tile-text">Checklist Soal</span>
                </a>
                <a href="<?= url('/preparation/printing') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-slate">🖨️</div>
                    <span class="tile-text">Penggandaan</span>
                </a>
                <?php endif; ?>

                <a href="<?= url('/written-exam/schedules') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-blue">🗓️</div>
                    <span class="tile-text">Jadwal Tertulis</span>
                </a>
                <?php if (!has_role('pengawas')): ?>
                <a href="<?= url('/written-exam/rooms') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-teal">🚪</div>
                    <span class="tile-text">Ruang Ujian</span>
                </a>
                <a href="<?= url('/written-exam/seating') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-cyan">🪑</div>
                    <span class="tile-text">Denah Kursi</span>
                </a>
                <a href="<?= url('/written-exam/supervisors') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-amber">👀</div>
                    <span class="tile-text">Pengawas</span>
                </a>
                <?php endif; ?>
                <a href="<?= url('/written-exam/execution') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-emerald">✍️</div>
                    <span class="tile-text">Presensi Ruang</span>
                </a>

                <?php if (has_role(['super_admin', 'admin', 'ketua_panitia', 'sekretaris', 'operator', 'penguji'])): ?>
                <a href="<?= url('/practical-exam') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-purple">🧪</div>
                    <span class="tile-text">Ujian Praktik</span>
                </a>
                <a href="<?= url('/practical-exam/execution') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-indigo">🔬</div>
                    <span class="tile-text">Presensi Praktik</span>
                </a>
                <?php endif; ?>

                <?php if (has_role(['super_admin', 'admin', 'ketua_panitia', 'sekretaris', 'operator'])): ?>
                <a href="<?= url('/attendance/committee') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-teal">✅</div>
                    <span class="tile-text">Hadir Panitia</span>
                </a>
                <a href="<?= url('/attendance/supervisors') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-sky">📋</div>
                    <span class="tile-text">Hadir Pengawas</span>
                </a>
                <a href="<?= url('/attendance/examiners') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-lime">🩺</div>
                    <span class="tile-text">Hadir Penguji</span>
                </a>
                <a href="<?= url('/distribution/handover') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-amber">📦</div>
                    <span class="tile-text">Distribusi Soal</span>
                </a>
                <a href="<?= url('/answer-sheets/receipts') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-blue">📥</div>
                    <span class="tile-text">Penerimaan LJ</span>
                </a>
                <a href="<?= url('/answer-sheets/packing') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-rose">✉️</div>
                    <span class="tile-text">Packing 3 Mode</span>
                </a>
                <a href="<?= url('/answer-sheets/handover') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-indigo">📑</div>
                    <span class="tile-text">Rekap LJ</span>
                </a>
                <?php endif; ?>

                <a href="<?= url('/print') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-emerald">🖨️</div>
                    <span class="tile-text">Pusat Cetak</span>
                </a>
                <a href="<?= url('/reports/minutes') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-cyan">📜</div>
                    <span class="tile-text">Berita Acara</span>
                </a>
                <a href="<?= url('/reports/final') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-amber">🏆</div>
                    <span class="tile-text">Laporan 18 Bab</span>
                </a>

                <?php if (has_role(['super_admin', 'admin'])): ?>
                <a href="<?= url('/users') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-purple">👤</div>
                    <span class="tile-text">Pengguna</span>
                </a>
                <a href="<?= url('/backup') ?>" class="sheet-tile">
                    <div class="tile-icon-bg bg-teal">💾</div>
                    <span class="tile-text">Backup SQLite</span>
                </a>
                <?php endif; ?>

                <a href="<?= url('/logout') ?>" class="sheet-tile text-danger">
                    <div class="tile-icon-bg bg-danger">🚪</div>
                    <span class="tile-text">Keluar</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
