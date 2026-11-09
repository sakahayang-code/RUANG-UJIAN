<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class PracticalExamController
{
    public function index(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $schedules = Database::fetchAll("
            SELECT ps.*, s.name as subject_name, s.code as subject_code,
                   (SELECT COUNT(*) FROM practical_exam_assignments WHERE practical_schedule_id = ps.id) as examiner_count,
                   (SELECT COUNT(*) FROM practical_exam_groups WHERE practical_schedule_id = ps.id) as group_count,
                   (SELECT COUNT(*) FROM practical_exam_documents WHERE practical_schedule_id = ps.id) as doc_count
            FROM practical_exam_schedules ps
            JOIN subjects s ON ps.subject_id = s.id
            WHERE ps.exam_event_id = ?
            ORDER BY ps.exam_date ASC, ps.start_time ASC
        ", [$event['id']]);

        $subjects = Database::fetchAll("SELECT * FROM subjects WHERE exam_type IN ('practical', 'both') ORDER BY code ASC");
        $teachers = Database::fetchAll("SELECT * FROM teachers WHERE status = 'Aktif' ORDER BY name ASC");

        View::render('practical_exam/index', [
            'title' => 'Manajemen Ujian Praktik',
            'event' => $event,
            'schedules' => $schedules,
            'subjects' => $subjects,
            'teachers' => $teachers
        ]);
    }

    public function storeSchedule(Request $request): void
    {
        $event = active_event();
        $subjectId = (int)$request->input('subject_id');
        $grade = (string)$request->input('grade', 'IX');
        $examDate = (string)$request->input('exam_date');
        $startTime = (string)$request->input('start_time');
        $endTime = (string)$request->input('end_time');
        $location = trim((string)$request->input('location'));
        $station = trim((string)$request->input('station', ''));
        $method = (string)$request->input('method', 'kelompok');
        $notes = trim((string)$request->input('notes', ''));

        if (empty($subjectId) || empty($examDate) || empty($location)) {
            flash('error', 'Mata pelajaran, tanggal, dan lokasi wajib diisi.');
            Response::back();
        }

        $id = Database::insert('practical_exam_schedules', [
            'exam_event_id' => $event['id'],
            'subject_id' => $subjectId,
            'grade' => $grade,
            'exam_date' => $examDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'location' => $location,
            'station' => $station ?: 'Pos 1',
            'method' => $method,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Auto create 2 default groups
        Database::insert('practical_exam_groups', [
            'practical_schedule_id' => $id,
            'group_name' => 'Kelompok 1 (Sesi Pagi)',
            'order_index' => 1
        ]);
        Database::insert('practical_exam_groups', [
            'practical_schedule_id' => $id,
            'group_name' => 'Kelompok 2 (Sesi Siang)',
            'order_index' => 2
        ]);

        flash('success', 'Jadwal sesi ujian praktik berhasil dibuat.');
        Response::redirect(url('/practical-exam'));
    }

    public function assignExaminer(Request $request): void
    {
        $scheduleId = (int)$request->input('practical_schedule_id');
        $examinerId = (int)$request->input('examiner_id');
        $role = (string)$request->input('examiner_role', 'Penguji Utama');

        $exists = Database::fetch("SELECT id FROM practical_exam_assignments WHERE practical_schedule_id = ? AND examiner_id = ?", [$scheduleId, $examinerId]);
        if ($exists) {
            flash('warning', 'Penguji tersebut sudah terdaftar pada jadwal praktik ini.');
            Response::back();
        }

        $examinerName = Database::fetchColumn("SELECT name FROM teachers WHERE id = ?", [$examinerId]);

        Database::insert('practical_exam_assignments', [
            'practical_schedule_id' => $scheduleId,
            'examiner_id' => $examinerId,
            'examiner_role' => $role,
            'examiner_name_snapshot' => $examinerName,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        flash('success', 'Penguji praktik berhasil ditugaskan.');
        Response::redirect(url('/practical-exam'));
    }

    public function execution(Request $request): void
    {
        $event = active_event();
        $schedules = Database::fetchAll("
            SELECT ps.*, s.name as subject_name,
                   (SELECT COUNT(*) FROM practical_exam_assignments WHERE practical_schedule_id = ps.id) as examiner_count,
                   (SELECT COUNT(*) FROM practical_exam_attendances WHERE practical_schedule_id = ps.id AND status = 'hadir') as present_count
            FROM practical_exam_schedules ps
            JOIN subjects s ON ps.subject_id = s.id
            WHERE ps.exam_event_id = ?
            ORDER BY ps.exam_date ASC
        ", [$event['id']]);

        View::render('practical_exam/execution', [
            'title' => 'Presensi & Pelaksanaan Ujian Praktik',
            'event' => $event,
            'schedules' => $schedules
        ]);
    }

    public function manageExecution(Request $request): void
    {
        $scheduleId = (int)$request->input('schedule_id');
        $event = active_event();

        $schedule = Database::fetch("
            SELECT ps.*, s.name as subject_name, s.code as subject_code
            FROM practical_exam_schedules ps
            JOIN subjects s ON ps.subject_id = s.id
            WHERE ps.id = ?
        ", [$scheduleId]);

        if (!$schedule) {
            flash('error', 'Jadwal praktik tidak ditemukan.');
            Response::redirect(url('/practical-exam/execution'));
        }

        $examiners = Database::fetchAll("
            SELECT pa.*, t.name as teacher_name, t.nip as teacher_nip, t.phone as teacher_phone
            FROM practical_exam_assignments pa
            JOIN teachers t ON pa.examiner_id = t.id
            WHERE pa.practical_schedule_id = ?
        ", [$scheduleId]);

        // Get participants of this grade
        $participants = Database::fetchAll("
            SELECT ep.*, pea.status as attendance_status, pea.notes as attendance_notes
            FROM exam_participants ep
            LEFT JOIN practical_exam_attendances pea ON (pea.practical_schedule_id = ? AND pea.participant_id = ep.id)
            WHERE ep.exam_event_id = ? AND ep.grade_snapshot = ?
            ORDER BY ep.class_name_snapshot ASC, ep.participant_number ASC
        ", [$scheduleId, $event['id'], $schedule['grade']]);

        $documents = Database::fetchAll("
            SELECT * FROM practical_exam_documents
            WHERE practical_schedule_id = ?
            ORDER BY id DESC
        ", [$scheduleId]);

        $minutes = Database::fetch("SELECT * FROM practical_exam_minutes WHERE practical_schedule_id = ?", [$scheduleId]);

        View::render('practical_exam/manage_execution', [
            'title' => "Pelaksanaan Praktik {$schedule['subject_name']} ({$schedule['location']})",
            'event' => $event,
            'schedule' => $schedule,
            'examiners' => $examiners,
            'participants' => $participants,
            'documents' => $documents,
            'minutes' => $minutes
        ]);
    }

    public function saveAttendance(Request $request): void
    {
        $scheduleId = (int)$request->input('schedule_id');
        $attendances = $request->input('attendances') ?? [];
        $incidentNotes = trim((string)$request->input('incident_notes', ''));
        $examinerNotes = trim((string)$request->input('examiner_notes', ''));

        Database::beginTransaction();
        try {
            $present = 0;
            $absent = 0;

            foreach ($attendances as $partId => $data) {
                $status = $data['status'] ?? 'hadir';
                $notes = trim((string)($data['notes'] ?? ''));

                if ($status === 'hadir') $present++;
                else $absent++;

                Database::query("
                    INSERT INTO practical_exam_attendances (practical_schedule_id, participant_id, status, notes, created_at)
                    VALUES (?, ?, ?, ?, ?)
                    ON CONFLICT(practical_schedule_id, participant_id) DO UPDATE SET
                    status = excluded.status, notes = excluded.notes
                ", [$scheduleId, $partId, $status, $notes, date('Y-m-d H:i:s')]);
            }

            Database::query("
                INSERT INTO practical_exam_minutes (practical_schedule_id, present_count, absent_count, incident_notes, examiner_notes, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT(practical_schedule_id) DO UPDATE SET
                present_count = excluded.present_count,
                absent_count = excluded.absent_count,
                incident_notes = excluded.incident_notes,
                examiner_notes = excluded.examiner_notes,
                updated_at = excluded.updated_at
            ", [$scheduleId, $present, $absent, $incidentNotes, $examinerNotes, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);

            Database::commit();
            flash('success', "Presensi ({$present} hadir, {$absent} absen) dan Berita Acara Praktik berhasil disimpan.");
        } catch (\Throwable $e) {
            Database::rollBack();
            flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }

        Response::redirect(url('/practical-exam/execution/manage?schedule_id=' . $scheduleId));
    }

    public function uploadDocument(Request $request): void
    {
        $scheduleId = (int)$request->input('schedule_id');
        $caption = trim((string)$request->input('caption', ''));

        if (!$request->hasFile('file')) {
            flash('error', 'Silakan pilih berkas foto atau video dokumentasi.');
            Response::back();
        }

        $file = $request->file('file');
        $mime = mime_content_type($file['tmp_name']);
        $fileType = 'image';

        if (str_starts_with($mime, 'image/')) {
            $fileType = 'image';
        } elseif (str_starts_with($mime, 'video/')) {
            $fileType = 'video';
        } elseif ($mime === 'application/pdf') {
            $fileType = 'pdf';
        } else {
            flash('error', 'Hanya diperbolehkan mengunggah berkas foto, video, atau PDF.');
            Response::back();
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = 'prac_' . $scheduleId . '_' . time() . '.' . $ext;
        $targetDir = dirname(__DIR__, 2) . '/storage/uploads/documentation';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        if (move_uploaded_file($file['tmp_name'], $targetDir . '/' . $newFileName)) {
            Database::insert('practical_exam_documents', [
                'practical_schedule_id' => $scheduleId,
                'file_path' => 'documentation/' . $newFileName,
                'file_type' => $fileType,
                'file_size' => $file['size'],
                'caption' => $caption,
                'uploaded_by' => Auth::id(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            flash('success', 'Dokumentasi kegiatan ujian praktik berhasil diunggah.');
        } else {
            flash('error', 'Gagal mengunggah berkas.');
        }

        Response::redirect(url('/practical-exam/execution/manage?schedule_id=' . $scheduleId));
    }
}
