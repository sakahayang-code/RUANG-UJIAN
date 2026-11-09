<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\View;

class DashboardController
{
    public function index(Request $request): void
    {
        $school = school_setting();
        $event = active_event();
        $eventId = $event ? (int)$event['id'] : 0;

        // Base counts
        $studentCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM students WHERE deleted_at IS NULL AND status = 'Aktif'");
        $classCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM classes");
        $teacherCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM teachers WHERE status = 'Aktif'");
        $subjectCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM subjects");

        // Event-specific counts
        $participantCount = 0;
        $roomCount = 0;
        $committeeCount = 0;
        $supervisorCount = 0;
        $examinerCount = 0;
        $scheduleCount = 0;

        // Progress metrics
        $prepProgress = 0;
        $docProgress = 0;
        $examProgress = 0;
        $answerSheetProgress = 0;
        $reportProgress = 0;

        // Notifications
        $notifications = [];

        if ($eventId > 0) {
            $participantCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_participants WHERE exam_event_id = ?", [$eventId]);
            $roomCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_rooms WHERE exam_event_id = ?", [$eventId]);
            $committeeCount = (int)Database::fetchColumn(
                "SELECT COUNT(DISTINCT cm.id) FROM committee_members cm
                 JOIN committees c ON cm.committee_id = c.id
                 WHERE c.exam_event_id = ?",
                [$eventId]
            );
            $supervisorCount = (int)Database::fetchColumn(
                "SELECT COUNT(DISTINCT s.supervisor1_id) + COUNT(DISTINCT s.supervisor2_id)
                 FROM written_exam_supervisors s
                 JOIN written_exam_schedules ws ON s.schedule_id = ws.id
                 WHERE ws.exam_event_id = ?",
                [$eventId]
            );
            $examinerCount = (int)Database::fetchColumn(
                "SELECT COUNT(DISTINCT pa.examiner_id)
                 FROM practical_exam_assignments pa
                 JOIN practical_exam_schedules ps ON pa.practical_schedule_id = ps.id
                 WHERE ps.exam_event_id = ?",
                [$eventId]
            );
            $scheduleCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM written_exam_schedules WHERE exam_event_id = ?", [$eventId]);
            $practicalScheduleCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM practical_exam_schedules WHERE exam_event_id = ?", [$eventId]);

            // Document checklist progress
            $totalDocSubmissions = (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_document_submissions WHERE exam_event_id = ?", [$eventId]);
            $completedDocSubmissions = (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_document_submissions WHERE exam_event_id = ? AND status = 'selesai'", [$eventId]);
            $docProgress = $totalDocSubmissions > 0 ? round(($completedDocSubmissions / $totalDocSubmissions) * 100) : 0;

            // Printing progress
            $totalPrintingRecords = (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_printing_records WHERE exam_event_id = ?", [$eventId]);
            $completedPrinting = (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_printing_records WHERE exam_event_id = ? AND status IN ('Selesai Dicetak', 'Terverifikasi')", [$eventId]);
            $prepProgress = $totalPrintingRecords > 0 ? round(($completedPrinting / $totalPrintingRecords) * 100) : 0;

            // Written exam execution progress (schedules that have minutes / berita acara filled)
            $totalMinutes = (int)Database::fetchColumn("SELECT COUNT(*) FROM written_exam_minutes WHERE exam_event_id = ?", [$eventId]);
            $totalRoomSchedules = (int)Database::fetchColumn(
                "SELECT COUNT(*) FROM written_exam_supervisors s
                 JOIN written_exam_schedules ws ON s.schedule_id = ws.id
                 WHERE ws.exam_event_id = ?",
                [$eventId]
            );
            $examProgress = $totalRoomSchedules > 0 ? round(($totalMinutes / $totalRoomSchedules) * 100) : 0;

            // Answer sheets reception progress
            $totalReceipts = (int)Database::fetchColumn("SELECT COUNT(*) FROM answer_sheet_receipts WHERE exam_event_id = ?", [$eventId]);
            $completeReceipts = (int)Database::fetchColumn("SELECT COUNT(*) FROM answer_sheet_receipts WHERE exam_event_id = ? AND status = 'diterima_lengkap'", [$eventId]);
            $answerSheetProgress = $totalReceipts > 0 ? round(($completeReceipts / $totalReceipts) * 100) : 0;

            // Final report progress
            $report = Database::fetch("SELECT * FROM exam_reports WHERE exam_event_id = ?", [$eventId]);
            if ($report) {
                $reportProgress = !empty($report['approval_date']) ? 100 : 75;
            }

            // --- Real Notifications Analysis ---
            // 1. Peserta belum dapat ruang
            $unassignedParticipants = (int)Database::fetchColumn(
                "SELECT COUNT(*) FROM exam_participants ep
                 WHERE ep.exam_event_id = ?
                 AND ep.id NOT IN (SELECT participant_id FROM exam_room_assignments WHERE exam_event_id = ?)",
                [$eventId, $eventId]
            );
            if ($unassignedParticipants > 0) {
                $notifications[] = [
                    'type' => 'warning',
                    'title' => 'Peserta Belum Mendapat Ruang',
                    'message' => "Terdapat {$unassignedParticipants} peserta yang belum dialokasikan ke ruang ujian.",
                    'url' => url('/written-exam/rooms')
                ];
            }

            // 2. Ruang belum memiliki pengawas
            $unassignedSupervisors = (int)Database::fetchColumn(
                "SELECT COUNT(*) FROM written_exam_supervisors s
                 JOIN written_exam_schedules ws ON s.schedule_id = ws.id
                 WHERE ws.exam_event_id = ? AND (s.supervisor1_id IS NULL AND s.supervisor2_id IS NULL)",
                [$eventId]
            );
            if ($unassignedSupervisors > 0) {
                $notifications[] = [
                    'type' => 'danger',
                    'title' => 'Ruang Belum Memiliki Pengawas',
                    'message' => "Terdapat {$unassignedSupervisors} sesi/ruang ujian yang belum ditentukan pengawasnya.",
                    'url' => url('/written-exam/supervisors')
                ];
            }

            // 3. Dokumen naskah/kisi-kisi belum dikumpulkan
            $pendingDocs = (int)Database::fetchColumn(
                "SELECT COUNT(*) FROM exam_document_submissions WHERE exam_event_id = ? AND status IN ('belum_diserahkan', 'perlu_revisi')",
                [$eventId]
            );
            if ($pendingDocs > 0) {
                $notifications[] = [
                    'type' => 'warning',
                    'title' => 'Dokumen Naskah Belum Selesai',
                    'message' => "Ada {$pendingDocs} dokumen kisi-kisi/naskah soal yang belum diserahkan atau masih perlu revisi.",
                    'url' => url('/preparation/submissions')
                ];
            }

            // 4. Naskah belum digandakan
            $unprintedCopies = (int)Database::fetchColumn(
                "SELECT COUNT(*) FROM exam_printing_records WHERE exam_event_id = ? AND status = 'Belum Dicetak'",
                [$eventId]
            );
            if ($unprintedCopies > 0) {
                $notifications[] = [
                    'type' => 'info',
                    'title' => 'Naskah Belum Digandakan',
                    'message' => "Ada {$unprintedCopies} mata pelajaran dengan naskah soal yang belum dicetak/digandakan.",
                    'url' => url('/preparation/printing')
                ];
            }

            // 5. Naskah belum dikembalikan
            $unreturnedPapers = (int)Database::fetchColumn(
                "SELECT COUNT(*) FROM exam_paper_handover_records WHERE exam_event_id = ? AND handover_type = 'pengambilan' AND taken_or_returned_count > 0 AND id NOT IN (
                    SELECT id FROM exam_paper_handover_records WHERE exam_event_id = ? AND handover_type = 'pengembalian'
                )",
                [$eventId, $eventId]
            );
            if ($unreturnedPapers > 0) {
                $notifications[] = [
                    'type' => 'danger',
                    'title' => 'Naskah Soal Belum Dikembalikan',
                    'message' => "Terdapat naskah ujian yang telah diambil namun belum dicatat pengembaliannya ke panitia.",
                    'url' => url('/distribution/handover')
                ];
            }

            // 6. Lembar jawaban belum diterima / ada selisih
            $discrepancyReceipts = (int)Database::fetchColumn(
                "SELECT COUNT(*) FROM answer_sheet_receipts WHERE exam_event_id = ? AND (discrepancy != 0 OR status = 'ada_selisih')",
                [$eventId]
            );
            if ($discrepancyReceipts > 0) {
                $notifications[] = [
                    'type' => 'danger',
                    'title' => 'Selisih Jumlah Lembar Jawaban',
                    'message' => "Perhatian: Terdapat {$discrepancyReceipts} pencatatan lembar jawaban yang memiliki selisih fisik!",
                    'url' => url('/answer-sheets/receipts')
                ];
            }

            // 7. Laporan akhir belum diselesaikan
            if (!$report || empty($report['approval_date'])) {
                $notifications[] = [
                    'type' => 'info',
                    'title' => 'Laporan Akhir Belum Diselesaikan',
                    'message' => 'Laporan akhir kegiatan panitia 18 bab belum difinalisasi atau belum disahkan.',
                    'url' => url('/reports/final')
                ];
            }
        }

        View::render('dashboard/index', [
            'title' => 'Dashboard Utama',
            'school' => $school,
            'event' => $event,
            'studentCount' => $studentCount,
            'classCount' => $classCount,
            'teacherCount' => $teacherCount,
            'subjectCount' => $subjectCount,
            'participantCount' => $participantCount,
            'roomCount' => $roomCount,
            'committeeCount' => $committeeCount,
            'supervisorCount' => $supervisorCount,
            'examinerCount' => $examinerCount,
            'scheduleCount' => $scheduleCount,
            'prepProgress' => $prepProgress,
            'docProgress' => $docProgress,
            'examProgress' => $examProgress,
            'answerSheetProgress' => $answerSheetProgress,
            'reportProgress' => $reportProgress,
            'notifications' => $notifications
        ]);
    }
}
