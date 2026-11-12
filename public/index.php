<?php

/**
 * RUANG UJIAN - Sistem Administrasi Ujian Sekolah
 * Native PHP & SQLite • Compatible with XAMPP & Rumahweb Shared Hosting
 */

declare(strict_types=1);

// Error reporting configuration
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// PSR-4-like Autoloader for App\ namespace
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

// Load Core Helpers
require_once dirname(__DIR__) . '/app/Core/Helpers.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Migrator;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;

use App\Controllers\AnswerSheetController;
use App\Controllers\AttendanceController;
use App\Controllers\AuthController;
use App\Controllers\BackupController;
use App\Controllers\CommitteeController;
use App\Controllers\DashboardController;
use App\Controllers\DistributionController;
use App\Controllers\ExamEventController;
use App\Controllers\FileController;
use App\Controllers\InstallController;
use App\Controllers\MasterClassController;
use App\Controllers\MasterStudentController;
use App\Controllers\MasterSubjectController;
use App\Controllers\MasterTeacherController;
use App\Controllers\PracticalExamController;
use App\Controllers\PreparationController;
use App\Controllers\PrintController;
use App\Controllers\ReportController;
use App\Controllers\SchoolController;
use App\Controllers\UserController;
use App\Controllers\WorkProgramController;
use App\Controllers\WrittenExamController;

// Start secure session
Session::start();

// Ensure storage directories exist
$baseDir = dirname(__DIR__);
$dirs = [
    $baseDir . '/database/storage',
    $baseDir . '/storage/sessions',
    $baseDir . '/storage/backups',
    $baseDir . '/storage/uploads',
    $baseDir . '/storage/uploads/photos',
    $baseDir . '/storage/uploads/sk',
    $baseDir . '/storage/uploads/docs'
];
foreach ($dirs as $d) {
    if (!is_dir($d)) {
        @mkdir($d, 0755, true);
    }
}

// Check database connectivity / installation status
$isDatabaseReady = false;
try {
    $dbPath = Database::getDatabasePath();
    if (file_exists($dbPath)) {
        $userCount = Database::fetchColumn("SELECT COUNT(*) FROM users");
        if ($userCount !== false && (int)$userCount > 0) {
            $isDatabaseReady = true;
            try {
                Migrator::run();
            } catch (\Throwable $e) {
                // Ignore if migrations already up to date
            }
        }
    }
} catch (\Throwable $e) {
    $isDatabaseReady = false;
}

$request = new Request();
$currentPath = '/' . trim($request->getPath(), '/');

// Static Assets Direct Delivery (Ensuring PWA icons, CSS, and JS never return 404 HTML)
if (str_starts_with($currentPath, '/assets/') || str_starts_with($currentPath, '/public/assets/')) {
    $subPath = preg_replace('#^/(public/)?assets/#', '', $currentPath);
    $filePath = dirname(__DIR__) . '/public/assets/' . $subPath;
    if (file_exists($filePath) && is_file($filePath)) {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'json' => 'application/json; charset=utf-8',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
        ];
        $contentType = $mimes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $contentType);
        header('Cache-Control: public, max-age=604800');
        readfile($filePath);
        exit;
    }
}

if (in_array($currentPath, ['/apple-touch-icon.png', '/apple-touch-icon-precomposed.png', '/favicon.ico', '/favicon.png'])) {
    $fileName = ltrim($currentPath, '/');
    $filePath = dirname(__DIR__) . '/public/' . $fileName;
    if (file_exists($filePath) && is_file($filePath)) {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $contentType = $ext === 'ico' ? 'image/x-icon' : 'image/png';
        header('Content-Type: ' . $contentType);
        header('Cache-Control: public, max-age=604800');
        readfile($filePath);
        exit;
    }
}

// Auto-redirect to installer if database is not initialized yet
$isPwaStatic = in_array($currentPath, ['/manifest.json', '/sw.js', '/offline']) || str_starts_with($currentPath, '/assets');
if (!$isDatabaseReady && !str_starts_with($currentPath, '/install') && !$isPwaStatic) {
    Response::redirect(url('/install'));
}

// ==========================================
// ROUTE DEFINITIONS
// ==========================================

// PWA Assets Fallback Routing (Dynamic Manifest with current Base URL & Full App Logo Icons)
Router::get('/manifest.json', function() {
    header('Content-Type: application/manifest+json; charset=utf-8');
    header('Cache-Control: public, max-age=86400');
    $manifestPath = dirname(__DIR__) . '/public/manifest.json';
    if (file_exists($manifestPath)) {
        $data = json_decode(file_get_contents($manifestPath), true);
        if (is_array($data)) {
            $data['start_url'] = url('/dashboard');
            $data['scope'] = url('/');
            $data['icons'] = [
                [
                    'src' => asset('images/icons/icon-192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src' => asset('images/icons/icon-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src' => asset('images/icons/icon-maskable-192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => asset('images/icons/icon-maskable.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => asset('images/icons/icon.svg'),
                    'sizes' => '512x512',
                    'type' => 'image/svg+xml',
                    'purpose' => 'any'
                ]
            ];
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    readfile($manifestPath);
    exit;
});
Router::get('/sw.js', function() {
    header('Content-Type: application/javascript; charset=utf-8');
    header('Service-Worker-Allowed: /');
    readfile(dirname(__DIR__) . '/public/sw.js');
    exit;
});
Router::get('/offline', function() {
    header('Content-Type: text/html; charset=utf-8');
    readfile(dirname(__DIR__) . '/public/offline.html');
    exit;
});

// 1. Installer
Router::get('/install', [InstallController::class, 'index']);
Router::post('/install', [InstallController::class, 'install']);

// 2. Authentication
Router::get('/login', [AuthController::class, 'showLogin'], ['guest']);
Router::post('/login', [AuthController::class, 'login'], ['guest', 'csrf']);
Router::get('/logout', [AuthController::class, 'logout'], ['auth']);
Router::post('/logout', [AuthController::class, 'logout'], ['auth', 'csrf']);

// 3. Secure File Delivery (Preventing direct URL access)
Router::get('/files', [FileController::class, 'serve'], ['auth']);
Router::get('/file/view', [FileController::class, 'view'], ['auth']);
Router::get('/file/download', [FileController::class, 'download'], ['auth']);

// 4. Dashboard
Router::get('/', [DashboardController::class, 'index'], ['auth']);
Router::get('/dashboard', [DashboardController::class, 'index'], ['auth']);

// 5. School Profile & Settings
Router::get('/school', [SchoolController::class, 'index'], ['auth']);
Router::post('/school', [SchoolController::class, 'update'], ['auth', 'csrf']);
Router::post('/school/update', [SchoolController::class, 'update'], ['auth', 'csrf']);

// 6. Master Data
// Students
Router::get('/master/students', [MasterStudentController::class, 'index'], ['auth']);
Router::post('/master/students', [MasterStudentController::class, 'store'], ['auth', 'csrf']);
Router::post('/master/students/store', [MasterStudentController::class, 'store'], ['auth', 'csrf']);
Router::post('/master/students/update', [MasterStudentController::class, 'update'], ['auth', 'csrf']);
Router::post('/master/students/delete', [MasterStudentController::class, 'delete'], ['auth', 'csrf']);
Router::post('/master/students/delete-dummy', [MasterStudentController::class, 'deleteDummy'], ['auth', 'csrf']);
Router::post('/master/students/delete-all', [MasterStudentController::class, 'deleteAll'], ['auth', 'csrf']);
Router::post('/master/students/import', [MasterStudentController::class, 'import'], ['auth', 'csrf']);
Router::get('/master/students/export', [MasterStudentController::class, 'export'], ['auth']);
Router::get('/master/students/template', [MasterStudentController::class, 'template'], ['auth']);

// Classes
Router::get('/master/classes', [MasterClassController::class, 'index'], ['auth']);
Router::post('/master/classes', [MasterClassController::class, 'store'], ['auth', 'csrf']);
Router::post('/master/classes/store', [MasterClassController::class, 'store'], ['auth', 'csrf']);
Router::post('/master/classes/update', [MasterClassController::class, 'update'], ['auth', 'csrf']);
Router::post('/master/classes/delete', [MasterClassController::class, 'delete'], ['auth', 'csrf']);
Router::post('/master/classes/delete-all', [MasterClassController::class, 'deleteAll'], ['auth', 'csrf']);
Router::post('/master/classes/import', [MasterClassController::class, 'import'], ['auth', 'csrf']);
Router::get('/master/classes/export', [MasterClassController::class, 'export'], ['auth']);
Router::get('/master/classes/template', [MasterClassController::class, 'template'], ['auth']);

// Teachers
Router::get('/master/teachers', [MasterTeacherController::class, 'index'], ['auth']);
Router::post('/master/teachers', [MasterTeacherController::class, 'store'], ['auth', 'csrf']);
Router::post('/master/teachers/store', [MasterTeacherController::class, 'store'], ['auth', 'csrf']);
Router::post('/master/teachers/update', [MasterTeacherController::class, 'update'], ['auth', 'csrf']);
Router::post('/master/teachers/delete', [MasterTeacherController::class, 'delete'], ['auth', 'csrf']);
Router::post('/master/teachers/delete-all', [MasterTeacherController::class, 'deleteAll'], ['auth', 'csrf']);
Router::post('/master/teachers/import', [MasterTeacherController::class, 'import'], ['auth', 'csrf']);
Router::get('/master/teachers/export', [MasterTeacherController::class, 'export'], ['auth']);
Router::get('/master/teachers/template', [MasterTeacherController::class, 'template'], ['auth']);

// Subjects
Router::get('/master/subjects', [MasterSubjectController::class, 'index'], ['auth']);
Router::post('/master/subjects', [MasterSubjectController::class, 'store'], ['auth', 'csrf']);
Router::post('/master/subjects/store', [MasterSubjectController::class, 'store'], ['auth', 'csrf']);
Router::post('/master/subjects/update', [MasterSubjectController::class, 'update'], ['auth', 'csrf']);
Router::post('/master/subjects/delete', [MasterSubjectController::class, 'delete'], ['auth', 'csrf']);
Router::post('/master/subjects/delete-all', [MasterSubjectController::class, 'deleteAll'], ['auth', 'csrf']);
Router::post('/master/subjects/import', [MasterSubjectController::class, 'import'], ['auth', 'csrf']);
Router::get('/master/subjects/export', [MasterSubjectController::class, 'export'], ['auth']);
Router::get('/master/subjects/template', [MasterSubjectController::class, 'template'], ['auth']);
Router::get('/master/rooms', [WrittenExamController::class, 'rooms'], ['auth']);

// 7. Exam Events
Router::get('/events', [ExamEventController::class, 'index'], ['auth']);
Router::post('/events', [ExamEventController::class, 'store'], ['auth', 'csrf']);
Router::post('/events/store', [ExamEventController::class, 'store'], ['auth', 'csrf']);
Router::post('/events/update', [ExamEventController::class, 'update'], ['auth', 'csrf']);
Router::post('/events/delete', [ExamEventController::class, 'delete'], ['auth', 'csrf']);
Router::post('/events/activate', [ExamEventController::class, 'activate'], ['auth', 'csrf']);
Router::post('/events/sync-participants', [ExamEventController::class, 'syncParticipants'], ['auth', 'csrf']);

// 8. Committee Administration
Router::get('/committee', [CommitteeController::class, 'index'], ['auth']);
Router::post('/committee/store', [CommitteeController::class, 'storeMember'], ['auth', 'csrf']);
Router::post('/committee/store-member', [CommitteeController::class, 'storeMember'], ['auth', 'csrf']);
Router::post('/committee/update', [CommitteeController::class, 'updateMember'], ['auth', 'csrf']);
Router::post('/committee/delete', [CommitteeController::class, 'deleteMember'], ['auth', 'csrf']);
Router::post('/committee/delete-member', [CommitteeController::class, 'deleteMember'], ['auth', 'csrf']);
Router::post('/committee/upload-sk', [CommitteeController::class, 'uploadSk'], ['auth', 'csrf']);

// Letters (Undangan & Surat Tugas)
Router::get('/committee/letters', [CommitteeController::class, 'letters'], ['auth']);
Router::post('/committee/letters/create', [CommitteeController::class, 'storeLetter'], ['auth', 'csrf']);
Router::post('/committee/letters/store', [CommitteeController::class, 'storeLetter'], ['auth', 'csrf']);
Router::get('/committee/letters/print', [CommitteeController::class, 'printLetter'], ['auth']);
Router::get('/committee/letters/export-docx', [CommitteeController::class, 'exportDocxLetter'], ['auth']);
Router::post('/committee/letters/delete', [CommitteeController::class, 'deleteLetter'], ['auth', 'csrf']);

// 9. Work Program (20 Bagian & RAB)
Router::get('/work-program', [WorkProgramController::class, 'index'], ['auth']);
Router::post('/work-program/update', [WorkProgramController::class, 'update'], ['auth', 'csrf']);
Router::post('/work-program/activities', [WorkProgramController::class, 'storeActivity'], ['auth', 'csrf']);
Router::post('/work-program/activities/delete', [WorkProgramController::class, 'deleteActivity'], ['auth', 'csrf']);
Router::post('/work-program/budgets', [WorkProgramController::class, 'storeBudget'], ['auth', 'csrf']);
Router::post('/work-program/budgets/delete', [WorkProgramController::class, 'deleteBudget'], ['auth', 'csrf']);
Router::get('/work-program/print', [WorkProgramController::class, 'print'], ['auth']);
Router::get('/work-program/export-docx', [WorkProgramController::class, 'exportDocx'], ['auth']);

// 10. Preparation (Tata Tertib, Checklist Dokumen, Penggandaan)
Router::get('/preparation/rules', [PreparationController::class, 'rules'], ['auth']);
Router::post('/preparation/rules', [PreparationController::class, 'updateRule'], ['auth', 'csrf']);
Router::post('/preparation/rules/store', [PreparationController::class, 'storeRule'], ['auth', 'csrf']);
Router::post('/preparation/rules/update', [PreparationController::class, 'updateRule'], ['auth', 'csrf']);
Router::get('/preparation/rules/print', [PreparationController::class, 'printRule'], ['auth']);
Router::post('/preparation/rules/delete', [PreparationController::class, 'deleteRule'], ['auth', 'csrf']);

Router::get('/preparation/submissions', [PreparationController::class, 'submissions'], ['auth']);
Router::post('/preparation/submissions', [PreparationController::class, 'storeSubmission'], ['auth', 'csrf']);
Router::post('/preparation/submissions/store', [PreparationController::class, 'storeSubmission'], ['auth', 'csrf']);
Router::post('/preparation/submissions/update', [PreparationController::class, 'updateSubmissionStatus'], ['auth', 'csrf']);
Router::post('/preparation/submissions/update-status', [PreparationController::class, 'updateSubmissionStatus'], ['auth', 'csrf']);
Router::post('/preparation/submissions/delete', [PreparationController::class, 'deleteSubmission'], ['auth', 'csrf']);

Router::get('/preparation/printing', [PreparationController::class, 'printing'], ['auth']);
Router::post('/preparation/printing', [PreparationController::class, 'storePrinting'], ['auth', 'csrf']);
Router::post('/preparation/printing/store', [PreparationController::class, 'storePrinting'], ['auth', 'csrf']);
Router::post('/preparation/printing/update', [PreparationController::class, 'updatePrinting'], ['auth', 'csrf']);
Router::post('/preparation/printing/delete', [PreparationController::class, 'deletePrinting'], ['auth', 'csrf']);

// 11. Written Exam Management
Router::get('/written-exam/schedules', [WrittenExamController::class, 'schedules'], ['auth']);
Router::post('/written-exam/schedules', [WrittenExamController::class, 'storeSchedule'], ['auth', 'csrf']);
Router::post('/written-exam/schedules/store', [WrittenExamController::class, 'storeSchedule'], ['auth', 'csrf']);
Router::post('/written-exam/schedules/delete', [WrittenExamController::class, 'deleteSchedule'], ['auth', 'csrf']);

Router::get('/written-exam/rooms', [WrittenExamController::class, 'rooms'], ['auth']);
Router::post('/written-exam/rooms', [WrittenExamController::class, 'storeRoom'], ['auth', 'csrf']);
Router::post('/written-exam/rooms/store', [WrittenExamController::class, 'storeRoom'], ['auth', 'csrf']);
Router::post('/written-exam/rooms/delete', [WrittenExamController::class, 'deleteRoom'], ['auth', 'csrf']);
Router::post('/written-exam/rooms/auto-assign', [WrittenExamController::class, 'autoDistribute'], ['auth', 'csrf']);
Router::post('/written-exam/rooms/auto-distribute', [WrittenExamController::class, 'autoDistribute'], ['auth', 'csrf']);
Router::post('/written-exam/rooms/sync-participants', [WrittenExamController::class, 'syncParticipants'], ['auth', 'csrf']);
Router::post('/written-exam/rooms/reset-assignments', [WrittenExamController::class, 'resetAssignments'], ['auth', 'csrf']);
Router::post('/written-exam/rooms/manual-assign', [WrittenExamController::class, 'manualAssign'], ['auth', 'csrf']);

Router::get('/written-exam/seating', [WrittenExamController::class, 'seating'], ['auth']);
Router::post('/written-exam/seating/update-layout', [WrittenExamController::class, 'updateLayout'], ['auth', 'csrf']);
Router::get('/written-exam/seating/print', [WrittenExamController::class, 'printSeating'], ['auth']);

Router::get('/written-exam/supervisors', [WrittenExamController::class, 'supervisors'], ['auth']);
Router::post('/written-exam/supervisors', [WrittenExamController::class, 'updateSupervisor'], ['auth', 'csrf']);
Router::post('/written-exam/supervisors/update', [WrittenExamController::class, 'updateSupervisor'], ['auth', 'csrf']);
Router::post('/written-exam/supervisors/delete', [WrittenExamController::class, 'deleteSupervisor'], ['auth', 'csrf']);

Router::get('/written-exam/execution', [WrittenExamController::class, 'execution'], ['auth']);
Router::get('/written-exam/execution/room', [WrittenExamController::class, 'manageRoomExecution'], ['auth']);
Router::get('/written-exam/execution/manage', [WrittenExamController::class, 'manageRoomExecution'], ['auth']);
Router::post('/written-exam/execution/attendance', [WrittenExamController::class, 'saveAttendanceAndMinutes'], ['auth', 'csrf']);
Router::post('/written-exam/execution/save-attendance', [WrittenExamController::class, 'saveAttendanceAndMinutes'], ['auth', 'csrf']);
Router::post('/written-exam/execution/minutes', [WrittenExamController::class, 'saveAttendanceAndMinutes'], ['auth', 'csrf']);
Router::post('/written-exam/execution/add-incident', [WrittenExamController::class, 'addIncident'], ['auth', 'csrf']);
Router::get('/written-exam/execution/print-minutes', [WrittenExamController::class, 'printMinutes'], ['auth']);
Router::get('/written-exam/minutes/print', [WrittenExamController::class, 'printMinutes'], ['auth']);

// 12. Practical Exam Management
Router::get('/practical-exam', [PracticalExamController::class, 'index'], ['auth']);
Router::post('/practical-exam/schedules', [PracticalExamController::class, 'storeSchedule'], ['auth', 'csrf']);
Router::post('/practical-exam/store', [PracticalExamController::class, 'storeSchedule'], ['auth', 'csrf']);
Router::post('/practical-exam/schedules/delete', [PracticalExamController::class, 'deleteSchedule'], ['auth', 'csrf']);
Router::post('/practical-exam/assign-examiner', [PracticalExamController::class, 'assignExaminer'], ['auth', 'csrf']);
Router::post('/practical-exam/delete-examiner', [PracticalExamController::class, 'deleteAssignment'], ['auth', 'csrf']);
Router::get('/practical-exam/execution', [PracticalExamController::class, 'execution'], ['auth']);
Router::get('/practical-exam/execution/manage', [PracticalExamController::class, 'manageExecution'], ['auth']);
Router::post('/practical-exam/execution/attendance', [PracticalExamController::class, 'saveAttendance'], ['auth', 'csrf']);
Router::post('/practical-exam/execution/save-attendance', [PracticalExamController::class, 'saveAttendance'], ['auth', 'csrf']);
Router::post('/practical-exam/execution/upload-doc', [PracticalExamController::class, 'uploadDocument'], ['auth', 'csrf']);
Router::post('/practical-exam/execution/upload-document', [PracticalExamController::class, 'uploadDocument'], ['auth', 'csrf']);

// 13. Attendance Management
Router::get('/attendance/committee', [AttendanceController::class, 'committee'], ['auth']);
Router::post('/attendance/committee', [AttendanceController::class, 'saveCommitteeAttendance'], ['auth', 'csrf']);
Router::post('/attendance/committee/save', [AttendanceController::class, 'saveCommitteeAttendance'], ['auth', 'csrf']);
Router::get('/attendance/committee/print', [AttendanceController::class, 'printCommitteeAttendance'], ['auth']);
Router::get('/attendance/committee/export-excel', [AttendanceController::class, 'exportCommitteeExcel'], ['auth']);

Router::get('/attendance/supervisors', [AttendanceController::class, 'supervisors'], ['auth']);
Router::post('/attendance/supervisors', [AttendanceController::class, 'saveSupervisorAttendance'], ['auth', 'csrf']);
Router::post('/attendance/supervisors/save', [AttendanceController::class, 'saveSupervisorAttendance'], ['auth', 'csrf']);
Router::get('/attendance/supervisors/print', [AttendanceController::class, 'printSupervisorAttendance'], ['auth']);
Router::get('/attendance/supervisors/export-excel', [AttendanceController::class, 'exportSupervisorsExcel'], ['auth']);

Router::get('/attendance/examiners', [AttendanceController::class, 'examiners'], ['auth']);
Router::post('/attendance/examiners', [AttendanceController::class, 'saveExaminerAttendance'], ['auth', 'csrf']);
Router::post('/attendance/examiners/save', [AttendanceController::class, 'saveExaminerAttendance'], ['auth', 'csrf']);
Router::get('/attendance/examiners/print', [AttendanceController::class, 'printExaminersAttendance'], ['auth']);
Router::get('/attendance/examiners/export-excel', [AttendanceController::class, 'exportExaminersExcel'], ['auth']);

// 14. Distribution (Serah Terima Naskah Soal)
Router::get('/distribution', [DistributionController::class, 'index'], ['auth']);
Router::get('/distribution/handover', [DistributionController::class, 'index'], ['auth']);
Router::post('/distribution/handover', [DistributionController::class, 'store'], ['auth', 'csrf']);
Router::post('/distribution/handover/store', [DistributionController::class, 'store'], ['auth', 'csrf']);
Router::post('/distribution/handover-out', [DistributionController::class, 'store'], ['auth', 'csrf']);
Router::post('/distribution/handover-in', [DistributionController::class, 'store'], ['auth', 'csrf']);
Router::get('/distribution/print', [DistributionController::class, 'print'], ['auth']);
Router::get('/distribution/handover/print', [DistributionController::class, 'print'], ['auth']);
Router::get('/distribution/export-excel', [DistributionController::class, 'exportExcel'], ['auth']);
Router::get('/distribution/handover/export-excel', [DistributionController::class, 'exportExcel'], ['auth']);

// 15. Answer Sheets (Penerimaan, Pengemasan 3 Mode, Rekap)
Router::get('/answer-sheets/receipts', [AnswerSheetController::class, 'receipts'], ['auth']);
Router::post('/answer-sheets/receipts', [AnswerSheetController::class, 'storeReceipt'], ['auth', 'csrf']);
Router::post('/answer-sheets/receipts/store', [AnswerSheetController::class, 'storeReceipt'], ['auth', 'csrf']);
Router::get('/answer-sheets/packing', [AnswerSheetController::class, 'packing'], ['auth']);
Router::post('/answer-sheets/packing', [AnswerSheetController::class, 'createPackingBatch'], ['auth', 'csrf']);
Router::get('/answer-sheets/recap', [AnswerSheetController::class, 'handoverRecap'], ['auth']);
Router::get('/answer-sheets/handover', [AnswerSheetController::class, 'handoverRecap'], ['auth']);

// 16. Print Center
Router::get('/print', [PrintController::class, 'index'], ['auth']);
Router::get('/print/participant-cards', [PrintController::class, 'participantCards'], ['auth']);
Router::get('/print/name-tags', [PrintController::class, 'nameTags'], ['auth']);
Router::get('/print/room-signs', [PrintController::class, 'roomSigns'], ['auth']);
Router::get('/print/question-labels', [PrintController::class, 'questionEnvelopeLabels'], ['auth']);
Router::get('/print/answer-sheet-labels', [PrintController::class, 'answerSheetEnvelopeLabels'], ['auth']);

// 17. Reports & Minutes
Router::get('/reports/minutes', [ReportController::class, 'minutes'], ['auth']);
Router::post('/reports/incidents', [ReportController::class, 'storeIncident'], ['auth', 'csrf']);
Router::post('/reports/incidents/delete', [ReportController::class, 'deleteIncident'], ['auth', 'csrf']);
Router::get('/reports/final', [ReportController::class, 'final'], ['auth']);
Router::post('/reports/final', [ReportController::class, 'updateFinal'], ['auth', 'csrf']);
Router::get('/reports/final/print', [ReportController::class, 'printFinal'], ['auth']);
Router::get('/reports/final/export-docx', [ReportController::class, 'exportFinalDocx'], ['auth']);

// 18. User Management & RBAC
Router::get('/users', [UserController::class, 'index'], ['auth']);
Router::post('/users', [UserController::class, 'store'], ['auth', 'csrf']);
Router::post('/users/update', [UserController::class, 'update'], ['auth', 'csrf']);
Router::post('/users/delete', [UserController::class, 'delete'], ['auth', 'csrf']);
Router::get('/users/audit-logs', [UserController::class, 'auditLogs'], ['auth']);

// 19. Backup & Restore (SQLite)
Router::get('/backup', [BackupController::class, 'index'], ['auth']);
Router::post('/backup/create', [BackupController::class, 'create'], ['auth', 'csrf']);
Router::post('/backup/restore', [BackupController::class, 'restore'], ['auth', 'csrf']);
Router::get('/backup/download', [BackupController::class, 'download'], ['auth']);
Router::post('/backup/delete', [BackupController::class, 'delete'], ['auth', 'csrf']);

// Dispatch the incoming HTTP request
try {
    Router::dispatch($request);
} catch (\Throwable $e) {
    if ($request->isAjax()) {
        Response::json(['error' => $e->getMessage()], 500);
    } else {
        http_response_code(500);
        View::render('layouts/error', [
            'title' => 'Terjadi Kesalahan Sistem (500)',
            'message' => $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine()
        ], null);
    }
}
