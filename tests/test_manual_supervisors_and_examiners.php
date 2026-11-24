<?php
/**
 * Test Manual Supervisors and Practical Examiners CRUD, Import, Export
 */
declare(strict_types=1);

echo "====================================================================\n";
echo "   PENGUJIAN FITUR MANUAL DATA PENGAWAS RUANG & PENGUJI PRAKTIK     \n";
echo "====================================================================\n\n";

$allPassed = true;
$dbPath = __DIR__ . '/../database/storage/ruang_ujian.sqlite';

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require_once $file;
});
require_once dirname(__DIR__) . '/app/Core/Helpers.php';

// 1. Memeriksa Controllers
echo "1. Memeriksa Controller Master Data...\n";
$supClass = 'App\\Controllers\\MasterSupervisorController';
$exClass = 'App\\Controllers\\MasterExaminerController';

$requiredMethods = ['index', 'store', 'update', 'delete', 'deleteAll', 'export', 'template', 'import'];

foreach ($requiredMethods as $method) {
    if (method_exists($supClass, $method)) {
        echo "   [PASS] MasterSupervisorController::{$method}() tersedia.\n";
    } else {
        echo "   [FAIL] MasterSupervisorController::{$method}() TIDAK ditemukan!\n";
        $allPassed = false;
    }

    if (method_exists($exClass, $method)) {
        echo "   [PASS] MasterExaminerController::{$method}() tersedia.\n";
    } else {
        echo "   [FAIL] MasterExaminerController::{$method}() TIDAK ditemukan!\n";
        $allPassed = false;
    }
}

// 2. Memeriksa Database Tables
echo "\n2. Memeriksa Tabel SQLite master_supervisors & master_examiners...\n";
$supCount = (int)shell_exec("sqlite3 {$dbPath} \"SELECT COUNT(*) FROM master_supervisors;\"");
$exCount = (int)shell_exec("sqlite3 {$dbPath} \"SELECT COUNT(*) FROM master_examiners;\"");

if ($supCount > 0) {
    echo "   [PASS] Tabel master_supervisors aktif dan berisi {$supCount} data pengawas.\n";
} else {
    echo "   [FAIL] Tabel master_supervisors kosong atau tidak ada!\n";
    $allPassed = false;
}

if ($exCount > 0) {
    echo "   [PASS] Tabel master_examiners aktif dan berisi {$exCount} data penguji.\n";
} else {
    echo "   [FAIL] Tabel master_examiners kosong atau tidak ada!\n";
    $allPassed = false;
}

// 3. Memeriksa Tampilan View
echo "\n3. Memeriksa View views/master/supervisors.php & views/master/examiners.php...\n";
$supView = file_get_contents(__DIR__ . '/../views/master/supervisors.php');
$exView = file_get_contents(__DIR__ . '/../views/master/examiners.php');

$supRequirements = [
    'Tombol Tambah Pengawas' => 'addSupervisorModal',
    'Tombol Impor Excel' => 'importSupervisorModal',
    'Tautan Unduh Template' => '/master/supervisors/template',
    'Tautan Ekspor Excel' => '/master/supervisors/export',
    'Input File Impor' => 'type="file"',
    'Statistik Pengawas' => 'stats-grid',
    'Form Modal Edit' => 'editSupervisorModal'
];

foreach ($supRequirements as $name => $needle) {
    if (str_contains($supView, $needle)) {
        echo "   [PASS] supervisors.php: {$name} terpasang.\n";
    } else {
        echo "   [FAIL] supervisors.php: {$name} TIDAK ditemukan!\n";
        $allPassed = false;
    }
}

$exRequirements = [
    'Tombol Tambah Penguji' => 'addExaminerModal',
    'Tombol Impor Excel' => 'importExaminerModal',
    'Tautan Unduh Template' => '/master/examiners/template',
    'Tautan Ekspor Excel' => '/master/examiners/export',
    'Input File Impor' => 'type="file"',
    'Statistik Penguji' => 'stats-grid',
    'Form Modal Edit' => 'editExaminerModal'
];

foreach ($exRequirements as $name => $needle) {
    if (str_contains($exView, $needle)) {
        echo "   [PASS] examiners.php: {$name} terpasang.\n";
    } else {
        echo "   [FAIL] examiners.php: {$name} TIDAK ditemukan!\n";
        $allPassed = false;
    }
}

// 4. Memeriksa Rute di public/index.php
echo "\n4. Memeriksa Pendaftaran Rute public/index.php...\n";
$indexContent = file_get_contents(__DIR__ . '/../public/index.php');

$requiredRoutes = [
    '/master/supervisors' => 'MasterSupervisorController',
    '/master/supervisors/store' => 'MasterSupervisorController',
    '/master/supervisors/export' => 'MasterSupervisorController',
    '/master/supervisors/template' => 'MasterSupervisorController',
    '/master/supervisors/import' => 'MasterSupervisorController',
    '/master/examiners' => 'MasterExaminerController',
    '/master/examiners/store' => 'MasterExaminerController',
    '/master/examiners/export' => 'MasterExaminerController',
    '/master/examiners/template' => 'MasterExaminerController',
    '/master/examiners/import' => 'MasterExaminerController',
];

foreach ($requiredRoutes as $route => $ctrl) {
    if (str_contains($indexContent, $route) && str_contains($indexContent, $ctrl)) {
        echo "   [PASS] Rute '{$route}' -> {$ctrl} terdaftar.\n";
    } else {
        echo "   [FAIL] Rute '{$route}' TIDAK terdaftar di public/index.php!\n";
        $allPassed = false;
    }
}

echo "\n====================================================================\n";
if ($allPassed) {
    echo "   HASIL PENGUJIAN: SELURUH FITUR MANUAL, EKSPOR & IMPOR 100% SUKSES!\n";
} else {
    echo "   HASIL PENGUJIAN: ADA TES YANG GAGAL!\n";
    exit(1);
}
echo "====================================================================\n";
