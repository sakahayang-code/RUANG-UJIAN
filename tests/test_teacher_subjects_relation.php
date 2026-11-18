<?php

declare(strict_types=1);

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

require_once dirname(__DIR__) . '/app/Core/Helpers.php';

echo "====================================================================\n";
echo "   PENGUJIAN RELASI GURU & MATA PELAJARAN YANG DIAMPU               \n";
echo "====================================================================\n\n";

$allPassed = true;

// 1. Verify Table teacher_subjects in SQLite
echo "1. Memeriksa Tabel teacher_subjects di Database SQLite...\n";
$schema = shell_exec('sqlite3 database/storage/ruang_ujian.sqlite "PRAGMA table_info(teacher_subjects);"');
if (strpos($schema, 'teacher_id') !== false && strpos($schema, 'subject_id') !== false) {
    echo "   [PASS] Tabel teacher_subjects valid dengan kolom teacher_id dan subject_id.\n";
} else {
    echo "   [FAIL] Tabel teacher_subjects tidak valid.\n";
    $allPassed = false;
}

// 2. Verify Migration 010
echo "\n2. Memeriksa Status Migrasi 010...\n";
$mig = shell_exec('sqlite3 database/storage/ruang_ujian.sqlite "SELECT migration FROM migrations WHERE migration LIKE \'%010%\';"');
if (trim($mig)) {
    echo "   [PASS] Migrasi 010 terdaftar: " . trim($mig) . "\n";
} else {
    echo "   [FAIL] Migrasi 010 belum terdaftar.\n";
    $allPassed = false;
}

// 3. Verify Seeding / Existing Pairings
echo "\n3. Memeriksa Data Relasi Guru & Mapel...\n";
$tsCount = (int)shell_exec('sqlite3 database/storage/ruang_ujian.sqlite "SELECT COUNT(*) FROM teacher_subjects;"');
echo "   - Jumlah Relasi Guru-Mapel: {$tsCount}\n";
if ($tsCount >= 10) {
    echo "   [PASS] Relasi guru dan mata pelajaran berhasil terisi ({$tsCount} entri relasi).\n";
} else {
    echo "   [FAIL] Relasi guru dan mata pelajaran kurang dari 10.\n";
    $allPassed = false;
}

// 4. Verify View views/master/teachers.php
echo "\n4. Memeriksa Elemen Tampilan di views/master/teachers.php...\n";
$viewContent = file_get_contents(dirname(__DIR__) . '/views/master/teachers.php');
$viewChecks = [
    'Mata Pelajaran yang Diampu' => 'Kolom Header Tabel',
    'subject_id' => 'Dropdown Filter Mapel',
    'subject_ids[]' => 'Input Pilihan Mapel pada Modal Tambah',
    'edit-subject-cb' => 'Checkbox Pilihan Mapel pada Modal Edit',
    'editTeacher' => 'Fungsi JS Pre-fill Mapel pada Edit'
];

foreach ($viewChecks as $needle => $desc) {
    if (strpos($viewContent, $needle) !== false) {
        echo "   [PASS] Terdapat {$desc} ({$needle})\n";
    } else {
        echo "   [FAIL] TIDAK terdapat {$desc} ({$needle})\n";
        $allPassed = false;
    }
}

// 5. Verify Controller MasterTeacherController
echo "\n5. Memeriksa Logika di MasterTeacherController.php...\n";
$ctrlContent = file_get_contents(dirname(__DIR__) . '/app/Controllers/MasterTeacherController.php');
$ctrlChecks = [
    'teacher_subjects' => 'Tabel Relasi teacher_subjects',
    'subject_ids' => 'Penanganan Input subject_ids',
    'subjectFilter' => 'Fitur Filter Berdasarkan Mapel',
    'subjects_list' => 'Ekspor Kolom Mapel ke Excel'
];

foreach ($ctrlChecks as $needle => $desc) {
    if (strpos($ctrlContent, $needle) !== false) {
        echo "   [PASS] Controller mencakup {$desc}\n";
    } else {
        echo "   [FAIL] Controller TIDAK mencakup {$desc}\n";
        $allPassed = false;
    }
}

// 6. Verify Relation to PreparationController (Checklist Soal)
echo "\n6. Memeriksa Keterkaitan Relasi ke Fitur Lain (PreparationController)...\n";
$prepContent = file_get_contents(dirname(__DIR__) . '/app/Controllers/PreparationController.php');
if (strpos($prepContent, 'teacher_subjects') !== false) {
    echo "   [PASS] PreparationController (Checklist Soal) menggunakan relasi teacher_subjects sebagai sumber utama.\n";
} else {
    echo "   [FAIL] PreparationController belum menggunakan teacher_subjects.\n";
    $allPassed = false;
}

echo "\n====================================================================\n";
if ($allPassed) {
    echo "   HASIL PENGUJIAN: SELURUH FITUR RELASI GURU-MAPEL 100% SUKSES!       \n";
} else {
    echo "   HASIL PENGUJIAN: ADA PENGUJIAN YANG GAGAL!                         \n";
    exit(1);
}
echo "====================================================================\n";
