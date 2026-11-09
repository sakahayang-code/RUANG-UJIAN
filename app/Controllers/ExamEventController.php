<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

class ExamEventController
{
    public function index(Request $request): void
    {
        $events = Database::fetchAll("
            SELECT e.*,
                   (SELECT COUNT(*) FROM exam_participants WHERE exam_event_id = e.id) as participant_count,
                   (SELECT COUNT(*) FROM exam_rooms WHERE exam_event_id = e.id) as room_count,
                   (SELECT COUNT(*) FROM written_exam_schedules WHERE exam_event_id = e.id) as schedule_count
            FROM exam_events e
            ORDER BY e.is_active DESC, e.start_date DESC, e.id DESC
        ");

        $examTypes = Database::fetchAll("SELECT * FROM exam_types ORDER BY name ASC");
        $academicYears = Database::fetchAll("SELECT * FROM academic_years ORDER BY name DESC, semester ASC");
        $classes = Database::fetchAll("SELECT * FROM classes ORDER BY grade ASC, name ASC");

        View::render('events/index', [
            'title' => 'Manajemen Kegiatan Ujian',
            'events' => $events,
            'examTypes' => $examTypes,
            'academicYears' => $academicYears,
            'classes' => $classes
        ]);
    }

    public function store(Request $request): void
    {
        $name = trim((string)$request->input('name'));
        $academicYear = trim((string)$request->input('academic_year'));
        $semester = (string)$request->input('semester', 'Ganjil');
        $examType = (string)$request->input('exam_type', 'Penilaian Akhir Semester');
        $startDate = (string)$request->input('start_date');
        $endDate = (string)$request->input('end_date');
        $implementationType = (string)$request->input('implementation_type', 'Kertas / Manual');
        $notes = trim((string)$request->input('notes', ''));

        if (empty($name) || empty($academicYear) || empty($startDate) || empty($endDate)) {
            flash('error', 'Semua kolom wajib harus diisi.');
            Response::back();
        }

        // Check if there are existing events; if none, make this active
        $hasActive = (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_events WHERE is_active = 1");
        $isActive = ($hasActive === 0) ? 1 : 0;

        $id = Database::insert('exam_events', [
            'name' => $name,
            'academic_year' => $academicYear,
            'semester' => $semester,
            'exam_type' => $examType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'implementation_type' => $implementationType,
            'status' => 'draft',
            'is_active' => $isActive,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Auto create empty committee container
        Database::insert('committees', [
            'exam_event_id' => $id,
            'title' => 'Panitia Penyelenggara ' . $name,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($isActive) {
            Session::set('active_exam_event_id', $id);
        }

        Auth::logAudit('CREATE', 'exam_events', $id, null, ['name' => $name, 'academic_year' => $academicYear]);
        flash('success', "Kegiatan ujian [{$name}] berhasil dibuat.");
        Response::redirect(url('/events'));
    }

    public function update(Request $request): void
    {
        $id = (int)$request->input('id');
        $event = Database::fetch("SELECT * FROM exam_events WHERE id = ?", [$id]);
        if (!$event) {
            flash('error', 'Kegiatan ujian tidak ditemukan.');
            Response::back();
        }

        $name = trim((string)$request->input('name'));
        $academicYear = trim((string)$request->input('academic_year'));
        $semester = (string)$request->input('semester', 'Ganjil');
        $examType = (string)$request->input('exam_type');
        $startDate = (string)$request->input('start_date');
        $endDate = (string)$request->input('end_date');
        $implementationType = (string)$request->input('implementation_type');
        $status = (string)$request->input('status', 'draft');
        $notes = trim((string)$request->input('notes', ''));

        $data = [
            'name' => $name,
            'academic_year' => $academicYear,
            'semester' => $semester,
            'exam_type' => $examType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'implementation_type' => $implementationType,
            'status' => $status,
            'notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        Database::update('exam_events', $data, 'id = :where_id', ['where_id' => $id]);
        Auth::logAudit('UPDATE', 'exam_events', $id, $event, $data);

        flash('success', 'Data kegiatan ujian berhasil diperbarui.');
        Response::redirect(url('/events'));
    }

    public function activate(Request $request): void
    {
        $id = (int)$request->input('id');
        Database::query("UPDATE exam_events SET is_active = 0");
        Database::query("UPDATE exam_events SET is_active = 1 WHERE id = ?", [$id]);
        Session::set('active_exam_event_id', $id);

        flash('success', 'Kegiatan ujian berhasil diaktifkan sebagai kegiatan utama.');
        Response::redirect(url('/events'));
    }

    public function switchEvent(Request $request): void
    {
        $id = (int)$request->input('id');
        $event = Database::fetch("SELECT id FROM exam_events WHERE id = ?", [$id]);
        if ($event) {
            Session::set('active_exam_event_id', (int)$event['id']);
        }
        Response::back();
    }

    public function syncParticipants(Request $request): void
    {
        $eventId = (int)$request->input('event_id');
        $grades = $request->input('grades') ?? []; // e.g. ['VII', 'VIII', 'IX']

        $event = Database::fetch("SELECT * FROM exam_events WHERE id = ?", [$eventId]);
        if (!$event) {
            flash('error', 'Kegiatan tidak ditemukan.');
            Response::back();
        }

        $sql = "SELECT s.*, c.name as class_name
                FROM students s
                JOIN classes c ON s.class_id = c.id
                WHERE s.deleted_at IS NULL AND s.status = 'Aktif'";
        $params = [];

        if (!empty($grades)) {
            $placeholders = implode(',', array_fill(0, count($grades), '?'));
            $sql .= " AND s.grade IN ({$placeholders})";
            $params = $grades;
        }

        $sql .= " ORDER BY s.grade ASC, c.name ASC, s.name ASC";
        $students = Database::fetchAll($sql, $params);

        if (empty($students)) {
            flash('warning', 'Tidak ada siswa yang sesuai kriteria tingkat untuk disinkronkan.');
            Response::back();
        }

        $yearShort = substr(preg_replace('/[^0-9]/', '', $event['academic_year']), 0, 2) ?: '26';
        $count = 0;
        $num = (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_participants WHERE exam_event_id = ?", [$eventId]);

        foreach ($students as $s) {
            $existing = Database::fetch("SELECT id FROM exam_participants WHERE exam_event_id = ? AND student_id = ?", [$eventId, $s['id']]);
            if ($existing) continue;

            $num++;
            $partNumber = sprintf("%s-%s-%04d", $yearShort, $s['grade'], $num);

            Database::insert('exam_participants', [
                'exam_event_id' => $eventId,
                'student_id' => $s['id'],
                'participant_number' => $partNumber,
                'student_name_snapshot' => $s['name'],
                'nis_snapshot' => $s['nis'],
                'nisn_snapshot' => $s['nisn'],
                'class_name_snapshot' => $s['class_name'],
                'grade_snapshot' => $s['grade'],
                'gender_snapshot' => $s['gender'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $count++;
        }

        flash('success', "Berhasil menyinkronkan {$count} peserta baru ke dalam kegiatan [{$event['name']}].");
        Response::redirect(url('/events'));
    }

    public function delete(Request $request): void
    {
        $id = (int)$request->input('id');
        $event = Database::fetch("SELECT * FROM exam_events WHERE id = ?", [$id]);
        if (!$event) {
            flash('error', 'Kegiatan tidak ditemukan.');
            Response::back();
        }

        Database::delete('exam_events', 'id = ?', [$id]);
        Auth::logAudit('DELETE', 'exam_events', $id, $event, null);

        // Switch active event if active was deleted
        if (Session::get('active_exam_event_id') == $id) {
            Session::remove('active_exam_event_id');
        }

        flash('success', 'Kegiatan ujian berhasil dihapus.');
        Response::redirect(url('/events'));
    }
}
