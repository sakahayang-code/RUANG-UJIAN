<?php

require_once __DIR__ . '/../app/Core/Helpers.php';

echo ">>> Memeriksa Database SQLite untuk kolom committee_signature_path...\n";
$schema = shell_exec('sqlite3 database/storage/ruang_ujian.sqlite "PRAGMA table_info(school_settings);"');
if (strpos($schema, 'committee_signature_path') !== false) {
    echo "  [PASS] Kolom committee_signature_path ditemukan pada tabel school_settings.\n";
} else {
    echo "  [FAIL] Kolom committee_signature_path TIDAK ditemukan pada tabel school_settings.\n";
    exit(1);
}

echo ">>> Memeriksa migrasi 008 di tabel migrations...\n";
$mig = shell_exec('sqlite3 database/storage/ruang_ujian.sqlite "SELECT migration FROM migrations WHERE migration LIKE \'%008%\';"');
if (trim($mig)) {
    echo "  [PASS] Migrasi 008 terdaftar di tabel migrations: " . trim($mig) . "\n";
} else {
    echo "  [FAIL] Migrasi 008 belum terdaftar.\n";
    exit(1);
}

echo ">>> Memeriksa folder storage/uploads/signatures...\n";
$dir = dirname(__DIR__) . '/storage/uploads/signatures';
if (is_dir($dir)) {
    echo "  [PASS] Direktori storage/uploads/signatures ada dan siap pakai.\n";
} else {
    echo "  [FAIL] Direktori storage/uploads/signatures tidak ada.\n";
    exit(1);
}

echo ">>> Memeriksa view template rendering tag tanda tangan...\n";
$viewsToCheck = [
    'views/school/index.php' => 'name="committee_signature"',
    'views/committee/index.php' => 'uploadSignatureModal',
    'views/print/participant_cards.php' => '$school[\'committee_signature_url\']',
    'views/committee/print_letter.php' => '$school[\'committee_signature_url\']',
    'views/attendance/print_committee.php' => '$school[\'committee_signature_url\']',
    'views/attendance/print_supervisors.php' => '$school[\'committee_signature_url\']',
    'views/attendance/print_examiners.php' => '$school[\'committee_signature_url\']',
    'views/written_exam/print_seating.php' => '$school[\'committee_signature_url\']',
    'views/work_program/print.php' => '$school[\'committee_signature_url\']',
    'views/reports/print_final.php' => '$school[\'committee_signature_url\']',
];

foreach ($viewsToCheck as $file => $needle) {
    $content = file_get_contents(dirname(__DIR__) . '/' . $file);
    if (strpos($content, $needle) !== false) {
        echo "  [PASS] {$file} memiliki dukungan tanda tangan digital ketua panitia.\n";
    } else {
        echo "  [FAIL] {$file} tidak memiliki string: {$needle}\n";
        exit(1);
    }
}

echo "\nSEMUA PENGUJIAN FITUR TANDA TANGAN KETUA PANITIA BERHASIL 100%!\n";
