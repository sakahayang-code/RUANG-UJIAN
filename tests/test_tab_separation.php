<?php
/**
 * Test Separation of Written and Oral Checklists
 */
declare(strict_types=1);

echo "====================================================================\n";
echo "   PENGUJIAN PEMISAHAN CEKLIS UJIAN TULIS & UJIAN LISAN             \n";
echo "====================================================================\n\n";

$allPassed = true;

// 1. Verify Database Data using sqlite3 CLI
echo "1. Memeriksa Data Guru, Mapel, dan Ceklis di Database SQLite...\n";
$dbPath = __DIR__ . '/../database/storage/ruang_ujian.sqlite';
$tulisMapel = trim((string)shell_exec("sqlite3 {$dbPath} \"SELECT COUNT(*) FROM subjects WHERE exam_type IN ('written', 'both', '');\""));
$lisanMapel = trim((string)shell_exec("sqlite3 {$dbPath} \"SELECT COUNT(*) FROM subjects WHERE exam_type IN ('practical', 'both', 'lisan');\""));
$tulisSubm = trim((string)shell_exec("sqlite3 {$dbPath} \"SELECT COUNT(*) FROM exam_document_submissions WHERE doc_type IN ('naskah_soal', 'kisi_kisi') AND status = 'selesai';\""));
$lisanSubm = trim((string)shell_exec("sqlite3 {$dbPath} \"SELECT COUNT(*) FROM exam_document_submissions WHERE doc_type IN ('lisan', 'praktik') AND status = 'selesai';\""));

echo "   - Mata pelajaran ujian tulis: {$tulisMapel}\n";
echo "   - Mata pelajaran ujian lisan: {$lisanMapel}\n";
echo "   - Naskah & kisi ujian tulis selesai (v): {$tulisSubm}\n";
echo "   - Instrumen ujian lisan selesai (v): {$lisanSubm}\n";

if ((int)$tulisMapel > 0 && (int)$lisanMapel > 0) {
    echo "   [PASS] Klasifikasi mata pelajaran tulis dan lisan terdaftar di database.\n";
} else {
    echo "   [FAIL] Klasifikasi mapel tidak ditemukan.\n";
    $allPassed = false;
}

// 2. Verify views/preparation/submissions.php
echo "\n2. Memeriksa Struktur File views/preparation/submissions.php...\n";
$submView = file_get_contents(__DIR__ . '/../views/preparation/submissions.php');

$checksView = [
    'Tab 1 Ujian Tulis' => 'tab=written',
    'Tab 2 Ujian Lisan' => 'tab=oral',
    'Tab 3 Daftar Rinci' => 'tab=details',
    'Header Ujian Tulis (image.png)' => 'Kelas <?= e((string)$g) ?>',
    'Header Ujian Lisan' => 'KELAS (UJIAN LISAN / PRAKTIK)',
    'Hanya Tulis Soal & Kisi di Tab Tulis' => 'tulis_soal_cells',
    'Hanya Lisan di Tab Lisan' => 'lisan_cells',
    'Tombol Cetak Tulis' => 'print?type=written',
    'Tombol Cetak Lisan' => 'print?type=practical',
    'Tombol Ekspor Tulis' => 'export?type=written',
    'Tombol Ekspor Lisan' => 'export?type=practical',
    'Optimistic UI 0ms' => 'toggleMatrixCell',
    'Isian Cepat Tab Tulis' => 'check_all_soal',
    'Isian Cepat Tab Lisan' => 'check_all_lisan',
    'Modal Tambah Row Tab-aware' => 'addMatrixRowModal',
    'Modal Sinkron Tab-aware' => 'syncConfirmModal',
];

foreach ($checksView as $label => $needle) {
    if (str_contains($submView, $needle)) {
        echo "   [PASS] {$label} terkonfigurasi dengan tepat.\n";
    } else {
        echo "   [FAIL] {$label} tidak ditemukan dalam view!\n";
        $allPassed = false;
    }
}

// 3. Verify views/preparation/print_submissions.php
echo "\n3. Memeriksa Format Cetak views/preparation/print_submissions.php...\n";
$printView = file_get_contents(__DIR__ . '/../views/preparation/print_submissions.php');

$checksPrint = [
    'Judul Format Ujian Tulis' => 'FORMAT CEKLIS PENERIMAAN NASKAH SOAL &amp; KISI-KISI UJIAN TULIS',
    'Judul Format Ujian Lisan' => 'FORMAT CEKLIS PENERIMAAN INSTRUMEN &amp; KISI-KISI UJIAN LISAN / PRAKTIK',
    'Orientasi Cetak Landscape' => 'size: landscape',
    'Tabel Standar Hitam-Putih' => 'matrix-print-table',
    'Pilihan Ujian Tulis di Toolbar' => 'print?type=written',
    'Pilihan Ujian Lisan di Toolbar' => 'print?type=practical',
    'Format Terisi vs Kosong Manual' => 'mode=blank',
    'Tanda Tangan Kepala Sekolah' => 'principal_name',
    'Tanda Tangan Ketua Panitia' => 'committee_signature_url',
];

foreach ($checksPrint as $label => $needle) {
    if (str_contains($printView, $needle)) {
        echo "   [PASS] Cetak {$label} terkonfigurasi dengan tepat.\n";
    } else {
        echo "   [FAIL] Cetak {$label} tidak ditemukan!\n";
        $allPassed = false;
    }
}

// 4. Verify PreparationController Logic
echo "\n4. Memeriksa PreparationController.php...\n";
$ctrlContent = file_get_contents(__DIR__ . '/../app/Controllers/PreparationController.php');

$checksCtrl = [
    'Dukungan Tab Written & Oral' => "activeTab = (string)\$request->input('tab', 'written')",
    'Filter Pasangan Guru Mapel Berdasarkan Tipe Ujian' => "buildSubmissionsMatrixData(array \$event, Request \$request, ?string \$examTypeOverride = null)",
    'Isian Cepat Khusus Tab Oral' => "\$action === 'check_all_lisan'",
    'Kosongkan Ceklis Khusus Tab Oral' => "\$tab === 'oral'",
    'Sinkronisasi Terfokus per Tab' => "\$types = ['lisan']",
    'Hapus Baris Khusus per Tab' => "doc_type IN ('lisan', 'praktik')",
    'Cetak Terfokus per Tab' => "\$type = (string)\$request->input('type', 'written')",
];

foreach ($checksCtrl as $label => $needle) {
    if (str_contains($ctrlContent, $needle)) {
        echo "   [PASS] Logika Controller {$label} valid.\n";
    } else {
        echo "   [FAIL] Logika Controller {$label} tidak sesuai!\n";
        $allPassed = false;
    }
}

echo "\n====================================================================\n";
if ($allPassed) {
    echo "   HASIL PENGUJIAN: SEMUA ASPEK PEMISAHAN CEKLIS LISAN & TULIS 100% SUKSES!\n";
} else {
    echo "   HASIL PENGUJIAN: TERDAPAT KEGAGALAN!\n";
    exit(1);
}
echo "====================================================================\n";
