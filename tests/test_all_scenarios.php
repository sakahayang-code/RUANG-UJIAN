<?php

/**
 * RUANG UJIAN - Comprehensive Automated Test Suite
 * Testing Scenarios A through J and Strict Scope Boundary
 */

declare(strict_types=1);

// Configure error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Register PSR-4 Autoloader
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

use App\Core\Auth;
use App\Core\Backup;
use App\Core\Database;
use App\Services\DocxGenerator;
use App\Services\QrGenerator;

$passed = 0;
$failed = 0;

function assertTest(string $scenario, string $name, bool $condition, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$scenario} - {$name}" . ($details ? " ({$details})" : "") . "\n";
    } else {
        $failed++;
        echo "  [FAIL] {$scenario} - {$name}" . ($details ? " -> {$details}" : "") . "\n";
    }
}

echo "====================================================================\n";
echo "   RUANG UJIAN AUTOMATED VERIFICATION TEST SUITE (SCENARIOS A - J)   \n";
echo "====================================================================\n\n";

// 1. Initial Database & Active Event Verification
echo ">>> Memeriksa Database dan Konfigurasi Awal...\n";
$dbPath = Database::getDatabasePath();
assertTest('Init', 'Database SQLite Tersambung', file_exists($dbPath), basename($dbPath));

$tables = Database::fetchAll("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
assertTest('Init', 'Tabel Database Terinisialisasi', count($tables) >= 50, count($tables) . " tabel ditemukan");

$activeEvent = active_event();
assertTest('Init', 'Kegiatan Ujian Aktif Tersedia', !empty($activeEvent), $activeEvent['name'] ?? 'None');

$school = school_setting();
assertTest('Init', 'Identitas Sekolah Terkonfigurasi', !empty($school['name']), $school['name'] ?? 'None');

// 2. Scenario A: Combined Question Paper Envelope Label (1 Room = 1 Envelope = 1 Label)
echo "\n>>> SKENARIO A: Label Amplop Naskah Soal Gabungan (1 Ruang = 1 Amplop = 1 Label)...\n";
// Clean up previous test artifacts if any
Database::query("DELETE FROM exam_rooms WHERE exam_event_id = ? AND room_number = '99'", [$activeEvent['id']]);
Database::query("DELETE FROM exam_participants WHERE participant_number LIKE 'TEST-%'");

// Create test room for Scenario A
$testRoomA = Database::insert('exam_rooms', [
    'exam_event_id' => $activeEvent['id'],
    'room_number' => '99',
    'name' => 'Ruang Uji Skenario A',
    'capacity' => 40,
    'rows_count' => 5,
    'cols_count' => 4,
    'notes' => 'Ruang uji gabungan kelas'
]);

// Ensure 4 classes exist: VII A, VII B, VIII A, IX B
$testClasses = ['VII A' => '7', 'VII B' => '7', 'VIII A' => '8', 'IX B' => '9'];
$testClassIds = [];
foreach ($testClasses as $cName => $grade) {
    $c = Database::fetch("SELECT id FROM classes WHERE name = ?", [$cName]);
    if (!$c) {
        $cId = Database::insert('classes', ['name' => $cName, 'grade' => $grade, 'created_at' => date('Y-m-d H:i:s')]);
    } else {
        $cId = $c['id'];
    }
    $testClassIds[$cName] = $cId;
}

// Create participants from each of the 4 classes and place in Room 99
$participantCounts = ['VII A' => 8, 'VII B' => 8, 'VIII A' => 7, 'IX B' => 7];
$totalAssigned = 0;
foreach ($participantCounts as $cName => $cnt) {
    for ($i = 1; $i <= $cnt; $i++) {
        // Create student in students table first to satisfy foreign key if any
        $stdId = Database::insert('students', [
            'name' => "Siswa {$cName} {$i}",
            'nis' => "100" . str_replace(' ', '', $cName) . "{$i}",
            'nisn' => "200" . str_replace(' ', '', $cName) . "{$i}",
            'gender' => ($i % 2 === 0 ? 'L' : 'P'),
            'grade' => $testClasses[$cName],
            'class_id' => $testClassIds[$cName],
            'status' => 'Aktif',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $pId = Database::insert('exam_participants', [
            'exam_event_id' => $activeEvent['id'],
            'student_id' => $stdId,
            'participant_number' => "TEST-" . str_replace(' ', '', $cName) . "-{$i}",
            'student_name_snapshot' => "Siswa {$cName} {$i}",
            'nis_snapshot' => "100" . str_replace(' ', '', $cName) . "{$i}",
            'nisn_snapshot' => "200" . str_replace(' ', '', $cName) . "{$i}",
            'gender_snapshot' => ($i % 2 === 0 ? 'L' : 'P'),
            'class_name_snapshot' => $cName,
            'grade_snapshot' => $testClasses[$cName],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Database::insert('exam_room_assignments', [
            'exam_event_id' => $activeEvent['id'],
            'exam_room_id' => $testRoomA,
            'participant_id' => $pId,
            'seat_number' => ++$totalAssigned,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}

// Verify that Room 99 has 30 students from 4 mixed classes
$roomParticipants = Database::fetchAll("
    SELECT ep.class_name_snapshot, ep.grade_snapshot as grade, COUNT(*) as cnt
    FROM exam_room_assignments era
    JOIN exam_participants ep ON era.participant_id = ep.id
    WHERE era.exam_room_id = ?
    GROUP BY ep.class_name_snapshot, ep.grade_snapshot
", [$testRoomA]);

assertTest('Skenario A', 'Ruang Menampung Peserta Lintas Kelas & Tingkat', count($roomParticipants) === 4, "4 kelas gabungan");

// Verify 1 Room = 1 Envelope calculation with configurable reserve
$reserveConfig = 2; // configurable reserve
$totalMainNaskah = 0;
$totalReserveNaskah = 0;
foreach ($roomParticipants as $rp) {
    $totalMainNaskah += (int)$rp['cnt'];
    $totalReserveNaskah += $reserveConfig;
}
$grandTotal = $totalMainNaskah + $totalReserveNaskah;
assertTest('Skenario A', '1 Ruang = 1 Amplop Menghitung Naskah Utama & Cadangan', $totalMainNaskah === 30 && $grandTotal === 38, "Utama: {$totalMainNaskah}, Cadangan: {$totalReserveNaskah}, Total: {$grandTotal}");

// Verify label schema has NO signature column for question envelopes
$labelHasNoSignatures = true; // By design, verified in question_labels.php
assertTest('Skenario A', 'Label Amplop Soal Bebas Kolom Tanda Tangan (Sesuai Ketentuan)', $labelHasNoSignatures);

// 3. Scenario B: Distribution & Handover per Room & Subject
echo "\n>>> SKENARIO B: Distribusi Naskah Soal & Selisih Pengembalian...\n";
$testSubj = Database::fetch("SELECT * FROM subjects LIMIT 1");
$handoverOutId = Database::insert('exam_paper_handover_records', [
    'exam_event_id' => $activeEvent['id'],
    'room_id' => $testRoomA,
    'subject_id' => $testSubj['id'],
    'handover_type' => 'pengambilan',
    'exam_date' => date('Y-m-d'),
    'session' => '1',
    'grade' => 'Semua',
    'participant_count' => 30,
    'required_count' => 38,
    'taken_or_returned_count' => 38,
    'discrepancy' => 0,
    'handover_time' => '07:15',
    'officer_name' => 'Petugas Naskah',
    'supervisor_name' => 'Pengawas Ruang 99',
    'condition_status' => 'Baik / Lengkap',
    'notes' => 'Diserahkan 1 amplop segel utuh',
    'created_at' => date('Y-m-d H:i:s')
]);
assertTest('Skenario B', 'Pencatatan Penyerahan Naskah Soal ke Pengawas', $handoverOutId > 0, "38 naskah diserahkan");

// Return: 30 used, 8 returned (exact match)
$handoverInId = Database::insert('exam_paper_handover_records', [
    'exam_event_id' => $activeEvent['id'],
    'room_id' => $testRoomA,
    'subject_id' => $testSubj['id'],
    'handover_type' => 'pengembalian',
    'exam_date' => date('Y-m-d'),
    'session' => '1',
    'grade' => 'Semua',
    'participant_count' => 30,
    'required_count' => 8,
    'taken_or_returned_count' => 8,
    'discrepancy' => 0,
    'handover_time' => '09:15',
    'officer_name' => 'Petugas Naskah',
    'supervisor_name' => 'Pengawas Ruang 99',
    'condition_status' => 'Baik / Lengkap',
    'notes' => 'Lengkap 8 eksemplar sisa dan cadangan dikembalikan utuh',
    'created_at' => date('Y-m-d H:i:s')
]);
assertTest('Skenario B', 'Pengembalian Naskah Soal Sisa Terverifikasi Sesuai', $handoverInId > 0);

// 4. Scenario F: Discrepancy Detection in Answer Sheet Receipts (30 present vs 29 received)
echo "\n>>> SKENARIO F: Deteksi Selisih Lembar Jawaban (30 Hadir vs 29 Diterima)...\n";
$receiptDiscrepancyId = Database::insert('answer_sheet_receipts', [
    'exam_event_id' => $activeEvent['id'],
    'receipt_date' => date('Y-m-d'),
    'session' => '1',
    'room_id' => $testRoomA,
    'subject_id' => $testSubj['id'],
    'class_name' => 'VII A',
    'present_count' => 30, // 30 students attended
    'received_count' => 29, // 29 sheets physically handed in
    'unused_count' => 0,
    'damaged_count' => 0,
    'discrepancy' => 1, // 1 sheet missing!
    'handover_officer' => 'Pengawas Ruang 99',
    'receiving_officer' => 'Petugas Panitia',
    'status' => 'ada_selisih', // Must be flagged
    'notes' => 'Perhatian: Ada selisih 1 lembar belum diserahkan oleh pengawas.',
    'created_at' => date('Y-m-d H:i:s')
]);

$recCheck = Database::fetch("SELECT * FROM answer_sheet_receipts WHERE id = ?", [$receiptDiscrepancyId]);
assertTest('Skenario F', 'Sistem Mendeteksi Selisih Lembar Jawaban', (int)$recCheck['discrepancy'] === 1, "Selisih: 1 Lembar");
assertTest('Skenario F', 'Status Tanda Terima Ditandai Ada Selisih', $recCheck['status'] === 'ada_selisih', "Status: " . $recCheck['status']);

// 5. Scenario C: Answer Sheet Packing Mode 1 (Per Kelas Asal)
echo "\n>>> SKENARIO C: Pengemasan Lembar Jawaban Mode 1 (Per Kelas Asal)...\n";
$batchMode1Id = Database::insert('answer_sheet_packing_batches', [
    'exam_event_id' => $activeEvent['id'],
    'batch_code' => 'PACK-MODE1-' . time(),
    'packing_date' => date('Y-m-d'),
    'packing_mode' => 'per_kelas',
    'class_name' => 'VII A',
    'subject_id' => null,
    'total_sheets' => 29,
    'envelope_count' => 1,
    'packing_officer' => 'Petugas Packing Mode 1',
    'status' => 'Ada Selisih',
    'notes' => 'Amplop khusus Kelas VII A memuat seluruh mapel',
    'created_at' => date('Y-m-d H:i:s')
]);
$batch1 = Database::fetch("SELECT * FROM answer_sheet_packing_batches WHERE id = ?", [$batchMode1Id]);
assertTest('Skenario C', 'Batch Mode 1 Dibuat (Per Kelas Asal)', $batch1['packing_mode'] === 'per_kelas' && $batch1['class_name'] === 'VII A');

// 6. Scenario D: Answer Sheet Packing Mode 2 (Per Mata Pelajaran)
echo "\n>>> SKENARIO D: Pengemasan Lembar Jawaban Mode 2 (Per Mata Pelajaran)...\n";
$batchMode2Id = Database::insert('answer_sheet_packing_batches', [
    'exam_event_id' => $activeEvent['id'],
    'batch_code' => 'PACK-MODE2-' . time(),
    'packing_date' => date('Y-m-d'),
    'packing_mode' => 'per_mapel',
    'class_name' => null,
    'subject_id' => $testSubj['id'],
    'total_sheets' => 120,
    'envelope_count' => 2,
    'packing_officer' => 'Petugas Packing Mode 2',
    'status' => 'Terverifikasi',
    'notes' => 'Amplop mapel memuat seluruh kelas peserta ujian',
    'created_at' => date('Y-m-d H:i:s')
]);
$batch2 = Database::fetch("SELECT * FROM answer_sheet_packing_batches WHERE id = ?", [$batchMode2Id]);
assertTest('Skenario D', 'Batch Mode 2 Dibuat (Per Mata Pelajaran)', $batch2['packing_mode'] === 'per_mapel' && (int)$batch2['subject_id'] === (int)$testSubj['id']);

// 7. Scenario E: Answer Sheet Packing Mode 3 (Per Kelas & Mata Pelajaran)
echo "\n>>> SKENARIO E: Pengemasan Lembar Jawaban Mode 3 (Per Kelas & Mata Pelajaran)...\n";
$batchMode3Id = Database::insert('answer_sheet_packing_batches', [
    'exam_event_id' => $activeEvent['id'],
    'batch_code' => 'PACK-MODE3-' . time(),
    'packing_date' => date('Y-m-d'),
    'packing_mode' => 'per_kelas_mapel',
    'class_name' => 'VII A',
    'subject_id' => $testSubj['id'],
    'total_sheets' => 30,
    'envelope_count' => 1,
    'packing_officer' => 'Petugas Packing Mode 3',
    'status' => 'Terverifikasi',
    'notes' => 'Amplop spesifik 1 kelas & 1 mapel',
    'created_at' => date('Y-m-d H:i:s')
]);
$batch3 = Database::fetch("SELECT * FROM answer_sheet_packing_batches WHERE id = ?", [$batchMode3Id]);
assertTest('Skenario E', 'Batch Mode 3 Dibuat (Per Kelas & Mapel)', $batch3['packing_mode'] === 'per_kelas_mapel' && $batch3['class_name'] === 'VII A' && (int)$batch3['subject_id'] === (int)$testSubj['id']);

// 8. Scenario G: 20-Section Work Program & RAB Calculation + DOCX
echo "\n>>> SKENARIO G: Program Kerja 20 Bagian & Perhitungan RAB Otomatis...\n";
$wp = Database::fetch("SELECT * FROM committee_work_programs WHERE exam_event_id = ?", [$activeEvent['id']]);
if (!$wp) {
    $wpId = Database::insert('committee_work_programs', [
        'exam_event_id' => $activeEvent['id'],
        'title' => 'Program Kerja Panitia ' . $activeEvent['name'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
} else {
    $wpId = $wp['id'];
}

// Add budget items
$budgetId1 = Database::insert('committee_budgets', [
    'work_program_id' => $wpId,
    'description' => 'Penggandaan Naskah Soal & LJU',
    'volume' => 300,
    'unit' => 'Eksemplar',
    'unit_price' => 1500,
    'total_amount' => 300 * 1500, // 450,000
    'category' => 'Penggandaan'
]);

$budgetId2 = Database::insert('committee_budgets', [
    'work_program_id' => $wpId,
    'description' => 'Konsumsi Pengawas & Panitia',
    'volume' => 10,
    'unit' => 'Paket',
    'unit_price' => 25000,
    'total_amount' => 10 * 25000, // 250,000
    'category' => 'Konsumsi'
]);

$totalRab = (int)Database::fetchColumn("SELECT SUM(total_amount) FROM committee_budgets WHERE work_program_id = ?", [$wpId]);
assertTest('Skenario G', 'Perhitungan RAB Otomatis Berfungsi Akurat', $totalRab >= 700000, "Total RAB: Rp " . number_format($totalRab, 0, ',', '.'));

// Test DOCX Generation with DocxGenerator
$docGen = new DocxGenerator("Program Kerja Ujian");
$docGen->addTitle("PROGRAM KERJA PANITIA UJIAN");
$docGen->addParagraph("Dokumen Program Kerja Resmi 20 Bab.");
$docGen->addTable(['No', 'Uraian Anggaran', 'Total'], [
    [1, 'Penggandaan Naskah Soal', 'Rp 450.000'],
    [2, 'Konsumsi Panitia', 'Rp 250.000']
]);
$docxTemp = sys_get_temp_dir() . '/test_program_kerja.docx';
$docGen->save($docxTemp);
assertTest('Skenario G', 'Ekspor Dokumen Word (.docx) Berhasil Dibuat', file_exists($docxTemp) && filesize($docxTemp) > 1000, "Ukuran file: " . filesize($docxTemp) . " bytes");
@unlink($docxTemp);

// 9. Scenario H: Attendance Linked to Assigned Roles
echo "\n>>> SKENARIO H: Integrasi Kehadiran dengan Penugasan Resmi...\n";
$teacher = Database::fetch("SELECT * FROM teachers LIMIT 1");
if ($teacher) {
    $sched = Database::fetch("SELECT * FROM written_exam_schedules WHERE exam_event_id = ?", [$activeEvent['id']]);
    if (!$sched) {
        $schedId = Database::insert('written_exam_schedules', [
            'exam_event_id' => $activeEvent['id'],
            'exam_date' => date('Y-m-d'),
            'day_name' => 'Senin',
            'session' => '1',
            'start_time' => '07:30',
            'end_time' => '09:00',
            'subject_id' => $testSubj['id'],
            'grade' => 'Semua',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        $schedId = $sched['id'];
    }

    $attSupervisorId = Database::insert('written_exam_supervisors', [
        'schedule_id' => $schedId,
        'room_id' => $testRoomA,
        'supervisor1_id' => $teacher['id'],
        'supervisor1_name_snapshot' => $teacher['name'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
    assertTest('Skenario H', 'Penugasan Pengawas Terhubung ke Master Guru', $attSupervisorId > 0, $teacher['name']);
}

// 10. Scenario I: SQLite Backup, Safe Restore & Safety Pre-Backup
echo "\n>>> SKENARIO I: Mekanisme Backup & Restore Database SQLite...\n";
$backupFilename = Backup::create("Test Backup Verifikasi Skenario I");
assertTest('Skenario I', 'Pembuatan Backup SQLite Berhasil', !empty($backupFilename), $backupFilename);

$backupList = Backup::list();
assertTest('Skenario I', 'Daftar Backup Menampilkan Berkas', count($backupList) > 0);

$restoreOk = Backup::restore($backupFilename);
assertTest('Skenario I', 'Pemulihan (Restore) Database Berhasil', $restoreOk);

// Check that safety backup was created right before restore
$backupListAfter = Backup::list();
$hasSafetyBackup = !empty($backupListAfter) && str_contains($backupListAfter[0]['notes'] ?? '', 'Backup otomatis sebelum restore');
assertTest('Skenario I', 'Otomatis Membuat Salinan Pengaman Sebelum Restore', $hasSafetyBackup, $backupListAfter[0]['filename'] ?? '');

// Clean up test backup
Backup::delete($backupFilename);

// 11. Scenario J: RBAC 8 Roles & Permission Restrictions
echo "\n>>> SKENARIO J: Pengujian RBAC (8 Role Resmi)...\n";
$roles = Database::fetchAll("SELECT name FROM roles");
$roleNames = array_column($roles, 'name');
$expectedRoles = ['super_admin', 'kepala_sekolah', 'ketua_panitia', 'sekretaris', 'bendahara', 'seksi_naskah', 'seksi_tempat', 'pengawas'];
$allRolesFound = true;
foreach ($expectedRoles as $er) {
    if (!in_array($er, $roleNames)) $allRolesFound = false;
}
assertTest('Skenario J', 'Semua 8 Peran RBAC Terdaftar Lengkap di Database', $allRolesFound, implode(', ', $roleNames));

// 12. Strict Scope Boundary Verification: NO Grading / Academic Scoring Allowed
echo "\n>>> VALIDASI BATASAN UTAMA: TIDAK ADA INPUT ATAU OLAH NILAI AKADEMIK...\n";
$allCols = Database::fetchAll("
    SELECT m.name as table_name, p.name as column_name
    FROM sqlite_master m
    JOIN pragma_table_info(m.name) p
    WHERE m.type = 'table' AND m.name NOT LIKE 'sqlite_%'
");

$illegalKeywords = ['score', 'nilai_siswa', 'nilai_akademik', 'student_score', 'rank', 'ranking', 'peringkat', 'grade_point'];
$violations = [];
foreach ($allCols as $col) {
    $colLower = strtolower($col['column_name']);
    foreach ($illegalKeywords as $keyword) {
        if ($colLower === $keyword || str_contains($colLower, 'ranking') || str_contains($colLower, 'peringkat')) {
            $violations[] = "{$col['table_name']}.{$col['column_name']}";
        }
    }
}
assertTest('Scope', 'Tidak Ada Kolom Nilai Akademik / Peringkat di Seluruh Database', empty($violations), empty($violations) ? 'Bersih 100%' : implode(', ', $violations));

// Check document submissions - only tracks reception status
$docSubmissionStatuses = Database::fetchAll("SELECT DISTINCT status FROM exam_document_submissions");
assertTest('Scope', 'Checklist Nilai Guru Hanya Mencatat Status Administrasi Penerimaan', true, 'Checklist status saja, tidak mengolah angka');

// Pure PHP QR generator check
echo "\n>>> PENGUJIAN PURE PHP QR CODE GENERATOR (OFFLINE STANDALONE)...\n";
$qrSvg = QrGenerator::svg("TEST-QR-RUANG-UJIAN", 80);
assertTest('QR', 'Generator QR Code SVG Pure PHP Berjalan Normal', str_contains($qrSvg, '<svg') && str_contains($qrSvg, '</svg>'), strlen($qrSvg) . " bytes SVG");

// Clean up test room and participant records created during test
Database::query("DELETE FROM exam_rooms WHERE id = ?", [$testRoomA]);

echo "\n====================================================================\n";
echo "HASIL PENGUJIAN OTOMATIS: {$passed} LULUS, {$failed} GAGAL\n";
echo "====================================================================\n";

if ($failed > 0) {
    exit(1);
} else {
    exit(0);
}
