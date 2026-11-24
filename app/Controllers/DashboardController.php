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
            $manualSupCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM master_supervisors WHERE exam_event_id = ? OR exam_event_id IS NULL", [$eventId]);
            $supervisorCount = $manualSupCount > 0 ? $manualSupCount : (int)Database::fetchColumn(
                "SELECT COUNT(DISTINCT s.supervisor1_id) + COUNT(DISTINCT s.supervisor2_id)
                 FROM written_exam_supervisors s
                 JOIN written_exam_schedules ws ON s.schedule_id = ws.id
                 WHERE ws.exam_event_id = ?",
                [$eventId]
            );

            $manualExCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM master_examiners WHERE exam_event_id = ? OR exam_event_id IS NULL", [$eventId]);
            $examinerCount = $manualExCount > 0 ? $manualExCount : (int)Database::fetchColumn(
                "SELECT COUNT(DISTINCT pa.examiner_id)
                 FROM practical_exam_assignments pa
                 JOIN practical_exam_schedules ps ON pa.practical_schedule_id = ps.id
                 WHERE ps.exam_event_id = ?",
                [$eventId]
            );
            $scheduleCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM written_exam_schedules WHERE exam_event_id = ?", [$eventId]);
            $practicalScheduleCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM practical_exam_schedules WHERE exam_event_id = ?", [$eventId]);

            // 1. Progres Master Data (Kelas, Mapel, Siswa, Panitia, Pengawas, Penguji, Ruangan)
            $masterItems = [
                $classCount > 0,
                $subjectCount > 0,
                $participantCount > 0,
                $committeeCount > 0,
                $supervisorCount > 0,
                $examinerCount > 0,
                $roomCount > 0
            ];
            $masterProgress = round((count(array_filter($masterItems)) / count($masterItems)) * 100);

            // 2. Progres Persiapan Kegiatan (Program Kerja, Jadwal Tulis, Jadwal Praktik, Persuratan, Tata Tertib)
            $workProgramCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM committee_work_programs WHERE exam_event_id = ?", [$eventId]);
            $lettersCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM committee_letters WHERE exam_event_id = ?", [$eventId]);
            $rulesCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_rules WHERE exam_event_id = ? OR exam_event_id IS NULL", [$eventId]);

            $prepItems = [
                $workProgramCount > 0,
                $scheduleCount > 0,
                $practicalScheduleCount > 0,
                $lettersCount > 0,
                $rulesCount >= 3
            ];
            $prepProgress = round((count(array_filter($prepItems)) / count($prepItems)) * 100);

            // 3. Progres Berita Acara Pelaksanaan Ujian
            $totalMinutes = (int)Database::fetchColumn("SELECT COUNT(*) FROM written_exam_minutes WHERE exam_event_id = ?", [$eventId]);
            $examProgress = $scheduleCount > 0 ? round(($totalMinutes / max(1, $scheduleCount)) * 100) : 0;
            if ($examProgress > 100) $examProgress = 100;

            // 4. Progres LPJ Kegiatan Ujian
            $report = Database::fetch("SELECT * FROM exam_reports WHERE exam_event_id = ?", [$eventId]);
            $reportProgress = 25;
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

            // 2. Data Pengawas Ruang belum diatur
            if ($supervisorCount === 0) {
                $notifications[] = [
                    'type' => 'danger',
                    'title' => 'Data Pengawas Ruang Belum Diisi',
                    'message' => 'Belum ada data pengawas ruang ujian yang ditambahkan ke sistem.',
                    'url' => url('/master/supervisors')
                ];
            }

            // 3. Jadwal Ujian Tulis belum dibuat
            if ($scheduleCount === 0) {
                $notifications[] = [
                    'type' => 'warning',
                    'title' => 'Jadwal Ujian Tulis Belum Dibuat',
                    'message' => 'Silakan susun jadwal ujian tulis mata pelajaran per tanggal dan sesi.',
                    'url' => url('/written-exam/schedules')
                ];
            }

            // 4. Data Penguji Praktik belum diisi
            if ($examinerCount === 0) {
                $notifications[] = [
                    'type' => 'info',
                    'title' => 'Data Penguji Praktik Belum Diisi',
                    'message' => 'Belum ada guru penguji praktik yang didaftarkan untuk mata pelajaran praktik.',
                    'url' => url('/master/examiners')
                ];
            }

            // 5. LPJ Kegiatan Belum Disahkan
            if (!$report || empty($report['approval_date'])) {
                $notifications[] = [
                    'type' => 'info',
                    'title' => 'LPJ Kegiatan Ujian Belum Disahkan',
                    'message' => 'Laporan Pertanggungjawaban (LPJ) kegiatan ujian panitia belum disahkan.',
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
            'masterProgress' => $masterProgress ?? 100,
            'prepProgress' => $prepProgress ?? 100,
            'examProgress' => $examProgress ?? 0,
            'reportProgress' => $reportProgress ?? 25,
            'notifications' => $notifications
        ]);
    }
}
