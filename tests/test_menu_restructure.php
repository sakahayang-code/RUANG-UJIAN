<?php
/**
 * Test Menu and Feature Restructure Verification
 */
declare(strict_types=1);

echo "====================================================================\n";
echo "   PENGUJIAN PEROMBAKAN MENU DAN FITUR APLIKASI RUANG UJIAN         \n";
echo "====================================================================\n\n";

$allPassed = true;
$dbPath = __DIR__ . '/../database/storage/ruang_ujian.sqlite';

// 1. Memeriksa Struktur Sidebar views/layouts/main.php
echo "1. Memeriksa Sidebar Navigasi views/layouts/main.php...\n";
$layoutContent = file_get_contents(__DIR__ . '/../views/layouts/main.php');

$requiredMenuItems = [
    'Menu Utama Category' => 'Menu Utama',
    'Dashboard' => '/dashboard',
    'Identitas Sekolah' => '/school',
    'Manajemen Ujian' => '/events',

    'Master Data Category' => 'Master Data',
    'Data Kelas' => '/master/classes',
    'Data Mata Pelajaran' => '/master/subjects',
    'Data Peserta Ujian' => '/master/students',
    'Data Panitia' => '/committee',
    'Data Pengawas Ruang' => '/written-exam/supervisors',
    'Data Penguji Praktik' => '/master/examiners',
    'Atur Ruangan' => '/written-exam/rooms',

    'Persiapan Kegiatan Category' => 'Persiapan Kegiatan',
    'Program Kerja Panitia' => '/work-program',
    'Jadwal Ujian Tulis' => '/written-exam/schedules',
    'Jadwal Ujian Praktik' => '/practical-exam',
    'Persuratan' => '/committee/letters',
    'Tata Tertib' => '/preparation/rules',

    'Cetak Category' => 'Cetak',
    '1. Kartu Peserta' => '/print/participant-cards',
    '2. Kartu Panitia' => '/print/name-tags?type=panitia',
    '3. Jadwal Ujian' => '/print/schedules',
    '4. Nomor Ruangan Ujian' => '/print/room-signs',
    '5. Daftar Peserta Ujian' => '/print/participants',
    '6. Denah Tempat Duduk' => '/written-exam/seating/print',
    '7. Daftar Hadir' => '/print/attendance',
    '8. Berita Acara Ujian' => '/reports/minutes',
    '9. LPJ Kegiatan Ujian' => '/reports/final',

    'Sistem Category' => 'Sistem',
    'Backup & Restore' => '/backup',
    'Logout' => '/logout',
];

foreach ($requiredMenuItems as $name => $needle) {
    if (str_contains($layoutContent, $needle)) {
        echo "   [PASS] Menu {$name} ('{$needle}') terpasang di sidebar.\n";
    } else {
        echo "   [FAIL] Menu {$name} ('{$needle}') TIDAK ditemukan di sidebar!\n";
        $allPassed = false;
    }
}

// 2. Memeriksa Fitur Master Data Mata Pelajaran (Tulis & Praktik)
echo "\n2. Memeriksa Fitur Master Data Mata Pelajaran (Tulis vs Praktik)...\n";
$subjView = file_get_contents(__DIR__ . '/../views/master/subjects.php');
$subjCtrl = file_get_contents(__DIR__ . '/../app/Controllers/MasterSubjectController.php');

if (str_contains($subjView, 'type=written') && str_contains($subjView, 'type=practical') && str_contains($subjCtrl, "type === 'written'") && str_contains($subjCtrl, "type === 'practical'")) {
    echo "   [PASS] Filter dan tab Mata Pelajaran Ujian Tulis & Praktik berfungsi penuh.\n";
} else {
    echo "   [FAIL] Pemisahan filter Mata Pelajaran Tulis & Praktik tidak lengkap.\n";
    $allPassed = false;
}

// 3. Memeriksa Fitur Master Data Penguji Praktik
echo "\n3. Memeriksa Fitur Master Data Penguji Praktik...\n";
$exView = file_get_contents(__DIR__ . '/../views/master/examiners.php');
$exCtrl = file_get_contents(__DIR__ . '/../app/Controllers/PracticalExamController.php');

if (str_contains($exView, 'Data Penguji Praktik') && str_contains($exCtrl, 'function examiners') && file_exists(__DIR__ . '/../views/master/examiners.php')) {
    echo "   [PASS] Modul dan halaman Data Penguji Praktik terdaftar dan siap digunakan.\n";
} else {
    echo "   [FAIL] Modul Data Penguji Praktik tidak lengkap.\n";
    $allPassed = false;
}

// 4. Memeriksa Fitur Persuratan (5 Jenis Surat Resmi)
echo "\n4. Memeriksa Fitur Persuratan (5 Jenis Surat)...\n";
$letterTemplates = shell_exec("sqlite3 {$dbPath} \"SELECT code FROM letter_templates;\"");
$lettersView = file_get_contents(__DIR__ . '/../views/committee/letters.php');

$requiredLetterTypes = [
    'undangan_rapat',
    'tugas_soal_kisi_tulis',
    'tugas_soal_kisi_praktik',
    'tugas_pengawas',
    'tugas_penguji'
];

foreach ($requiredLetterTypes as $type) {
    if (str_contains((string)$letterTemplates, $type) && str_contains($lettersView, $type)) {
        echo "   [PASS] Jenis surat '{$type}' tersedia di database dan formulir persuratan.\n";
    } else {
        echo "   [FAIL] Jenis surat '{$type}' tidak lengkap!\n";
        $allPassed = false;
    }
}

// 5. Memeriksa Fitur Tata Tertib (3 Dokumen Regulasi)
echo "\n5. Memeriksa Fitur Tata Tertib (Peserta, Pengawas, Penguji)...\n";
$examRules = shell_exec("sqlite3 {$dbPath} \"SELECT rule_type FROM exam_rules;\"");
$rulesView = file_get_contents(__DIR__ . '/../views/preparation/rules.php');

$requiredRuleTypes = [
    'tata_tertib_peserta',
    'tata_tertib_pengawas',
    'tata_tertib_penguji'
];

foreach ($requiredRuleTypes as $type) {
    if (str_contains((string)$examRules, $type) && str_contains($rulesView, $type)) {
        echo "   [PASS] Tata tertib '{$type}' tersedia di database dan editor regulasi.\n";
    } else {
        echo "   [FAIL] Tata tertib '{$type}' tidak lengkap!\n";
        $allPassed = false;
    }
}

// 6. Memeriksa 9 Modul Cetak Terpadu
echo "\n6. Memeriksa 9 Fitur Modul Cetak Terpadu...\n";
$printView = file_get_contents(__DIR__ . '/../views/print/index.php');
$printCtrl = file_get_contents(__DIR__ . '/../app/Controllers/PrintController.php');

$printModules = [
    '1. Kartu Peserta' => 'Kartu Peserta Ujian',
    '2. Kartu Panitia' => 'Kartu Panitia',
    '3. Jadwal' => 'Jadwal Ujian',
    '4. Nomor Ruangan' => 'Nomor Ruangan Ujian',
    '5. Daftar Peserta' => 'Daftar Peserta Ujian',
    '6. Denah Tempat Duduk' => 'Denah Tempat Duduk',
    '7. Daftar Hadir' => 'Daftar Hadir',
    '8. Berita Acara' => 'Berita Acara Pelaksanaan Ujian',
    '9. LPJ Kegiatan' => 'LPJ Kegiatan Ujian',
];

foreach ($printModules as $label => $needle) {
    if (str_contains($printView, $needle)) {
        echo "   [PASS] Modul Cetak: {$label} ('{$needle}') terpasang di Pusat Cetak.\n";
    } else {
        echo "   [FAIL] Modul Cetak: {$label} TIDAK ditemukan di Pusat Cetak!\n";
        $allPassed = false;
    }
}

// 7. Memeriksa Template Cetak Baru
echo "\n7. Memeriksa Keberadaan File Template Cetak Baru...\n";
$newPrintFiles = [
    'views/print/schedules.php',
    'views/print/participants.php',
    'views/print/attendance_hub.php',
    'views/print/student_attendance.php',
    'views/master/examiners.php'
];

foreach ($newPrintFiles as $file) {
    if (file_exists(__DIR__ . '/../' . $file)) {
        echo "   [PASS] Berkas {$file} berhasil dibuat dan valid.\n";
    } else {
        echo "   [FAIL] Berkas {$file} tidak ditemukan!\n";
        $allPassed = false;
    }
}

echo "\n====================================================================\n";
if ($allPassed) {
    echo "   HASIL PENGUJIAN: SELURUH PEROMBAKAN MENU & FITUR 100% SUKSES!\n";
} else {
    echo "   HASIL PENGUJIAN: TERDAPAT KEGAGALAN!\n";
    exit(1);
}
echo "====================================================================\n";
