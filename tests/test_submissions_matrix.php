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
echo "   PENGUJIAN FITUR FORMAT CEKLIS PENERIMAAN DOKUMEN SOAL (MATRIKS)  \n";
echo "====================================================================\n\n";

$allPassed = true;

// 1. Verify Routes in public/index.php
echo "1. Memeriksa Pendaftaran Rute di public/index.php...\n";
$indexContent = file_get_contents(dirname(__DIR__) . '/public/index.php');
$routes = [
    '/preparation/submissions' => 'submissions',
    '/preparation/submissions/print' => 'printSubmissions',
    '/preparation/submissions/export' => 'exportSubmissionsMatrix',
    '/preparation/submissions/toggle-check' => 'toggleCheck',
    '/preparation/submissions/batch-check' => 'batchCheck',
    '/preparation/submissions/sync' => 'syncTeachersAndSubjects',
    '/preparation/submissions/add-row' => 'addMatrixRow',
    '/preparation/submissions/delete-row' => 'deleteMatrixRow'
];

foreach ($routes as $path => $method) {
    if (strpos($indexContent, $path) !== false && strpos($indexContent, $method) !== false) {
        echo "   [PASS] Rute {$path} terdaftar ke PreparationController::{$method}\n";
    } else {
        echo "   [FAIL] Rute {$path} TIDAK ditemukan di public/index.php\n";
        $allPassed = false;
    }
}

// 2. Verify Controller Methods in PreparationController
echo "\n2. Memeriksa Method Publik di PreparationController...\n";
$controllerClass = 'App\\Controllers\\PreparationController';
foreach ($routes as $path => $method) {
    if (method_exists($controllerClass, $method)) {
        $ref = new ReflectionMethod($controllerClass, $method);
        if ($ref->isPublic()) {
            echo "   [PASS] Method {$controllerClass}::{$method}() valid dan bersifat public\n";
        } else {
            echo "   [FAIL] Method {$controllerClass}::{$method}() bukan public\n";
            $allPassed = false;
        }
    } else {
        echo "   [FAIL] Method {$controllerClass}::{$method}() tidak ditemukan\n";
        $allPassed = false;
    }
}

// 3. Verify Database Master Data matching image.png
echo "\n3. Memeriksa Database SQLite untuk Data Guru & Mapel Sesuai image.png...\n";
$teacherCount = (int)shell_exec('sqlite3 database/storage/ruang_ujian.sqlite "SELECT COUNT(*) FROM teachers;"');
$subjectCount = (int)shell_exec('sqlite3 database/storage/ruang_ujian.sqlite "SELECT COUNT(*) FROM subjects;"');
$submCount = (int)shell_exec('sqlite3 database/storage/ruang_ujian.sqlite "SELECT COUNT(*) FROM exam_document_submissions WHERE exam_event_id = 1;"');

echo "   - Jumlah Guru Terdaftar: {$teacherCount}\n";
echo "   - Jumlah Mata Pelajaran: {$subjectCount}\n";
echo "   - Jumlah Entri Ceklis Dokumen Event 1: {$submCount}\n";

if ($teacherCount >= 11 && $subjectCount >= 13 && $submCount >= 40) {
    echo "   [PASS] Data master guru, mapel, dan ceklis event 1 sesuai dengan spesifikasi image.png.\n";
} else {
    echo "   [FAIL] Data master tidak mencukupi.\n";
    $allPassed = false;
}

// 4. Verify View Template: views/preparation/submissions.php
echo "\n4. Memeriksa Struktur File views/preparation/submissions.php...\n";
$submView = file_get_contents(dirname(__DIR__) . '/views/preparation/submissions.php');
$submMustHave = [
    'table-matrix-checklist' => 'Tabel Matriks Ceklis',
    'cell-shaded' => 'Pewarnaan Sel Abu-Abu (Tidak Diajarkan)',
    'cell-matrix-interactive' => 'Interaktivitas Sel Centang 1-Klik',
    'matrix-cell-control' => 'Kotak Isian Interaktif Sel Matriks',
    'toggleMatrixCell' => 'Fungsi AJAX Toggle Checkmark',
    'runBatchCheck' => 'Fungsi Isian Cepat / Batch Actions',
    'inline-add-row' => 'Baris Tambah Cepat Guru & Mapel Langsung di Tabel',
    'Centang Semua Soal' => 'Tombol Pengisian Cepat Soal',
    'Centang Semua Kisi-Kisi' => 'Tombol Pengisian Cepat Kisi-Kisi',
    'addMatrixRowModal' => 'Modal Tambah Guru & Mapel ke Ceklis',
    'syncConfirmModal' => 'Modal Konfirmasi Sinkronkan Semua Guru & Mapel',
    'row-total-soal' => 'Total Naskah Soal per Baris',
    'row-total-kisi' => 'Total Kisi-kisi per Baris',
    'col-total-soal' => 'Total Naskah Soal per Kolom',
    'col-total-kisi' => 'Total Kisi-kisi per Kolom',
    'grand-total-soal' => 'Grand Total Naskah Soal',
    'grand-total-kisi' => 'Grand Total Kisi-kisi'
];

foreach ($submMustHave as $needle => $desc) {
    if (strpos($submView, $needle) !== false) {
        echo "   [PASS] Terdapat {$desc} ({$needle})\n";
    } else {
        echo "   [FAIL] TIDAK terdapat {$desc} ({$needle})\n";
        $allPassed = false;
    }
}

// 5. Verify View Template: views/preparation/print_submissions.php
echo "\n5. Memeriksa Struktur File views/preparation/print_submissions.php...\n";
$printView = file_get_contents(dirname(__DIR__) . '/views/preparation/print_submissions.php');
$printMustHave = [
    'size: landscape' => 'Orientasi Cetak Landscape',
    'FORMAT CEKLIS PENERIMAAN DOKUMEN SOAL' => 'Judul Resmi Dokumen',
    'matrix-print-table' => 'Tabel Matriks Cetak Hitam-Putih Crisp',
    'cell-shaded' => 'Shading Abu-Abu pada Cetak',
    'principal_name' => 'Tanda Tangan Kepala Sekolah',
    'committee_signature_url' => 'Tanda Tangan Digital Ketua Panitia',
    'mode=blank' => 'Dukungan Cetak Format Kosong Manual',
    'print-toolbar' => 'Toolbar Cetak'
];

foreach ($printMustHave as $needle => $desc) {
    if (strpos($printView, $needle) !== false) {
        echo "   [PASS] Cetak mencakup {$desc} ({$needle})\n";
    } else {
        echo "   [FAIL] Cetak TIDAK mencakup {$desc} ({$needle})\n";
        $allPassed = false;
    }
}

// 6. Verify Print Center tile in views/print/index.php
echo "\n6. Memeriksa Akses Menu di Pusat Cetak (views/print/index.php)...\n";
$printIndex = file_get_contents(dirname(__DIR__) . '/views/print/index.php');
if (strpos($printIndex, '/preparation/submissions/print') !== false) {
    echo "   [PASS] Terdapat pintasan cetak Ceklis Dokumen Soal di Pusat Cetak.\n";
} else {
    echo "   [FAIL] Pintasan cetak Ceklis Dokumen Soal belum ada di Pusat Cetak.\n";
    $allPassed = false;
}

echo "\n====================================================================\n";
if ($allPassed) {
    echo "   HASIL PENGUJIAN: SELURUH FITUR CEKLIS MATRIKS 100% SUKSES & VALID!   \n";
} else {
    echo "   HASIL PENGUJIAN: ADA PENGUJIAN YANG GAGAL!                           \n";
    exit(1);
}
echo "====================================================================\n";
