<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\ExcelExport;

class DistributionController
{
    public function index(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $handoverType = (string)$request->input('type', 'pengambilan'); // 'pengambilan' or 'pengembalian'
        $dateFilter = (string)$request->input('date', '');
        $sessionFilter = (string)$request->input('session', '');

        $sql = "
            SELECT ep.*, s.name as subject_name, s.code as subject_code, er.room_number, er.name as room_name
            FROM exam_paper_handover_records ep
            JOIN subjects s ON ep.subject_id = s.id
            JOIN exam_rooms er ON ep.room_id = er.id
            WHERE ep.exam_event_id = ? AND ep.handover_type = ?
        ";
        $params = [$event['id'], $handoverType];

        if (!empty($dateFilter)) {
            $sql .= " AND ep.exam_date = ?";
            $params[] = $dateFilter;
        }

        if (!empty($sessionFilter)) {
            $sql .= " AND ep.session = ?";
            $params[] = $sessionFilter;
        }

        $sql .= " ORDER BY ep.exam_date ASC, ep.session ASC, er.room_number ASC, ep.id DESC";
        $records = Database::fetchAll($sql, $params);

        // Get schedules and rooms for record creation
        $schedules = Database::fetchAll("
            SELECT ws.*, s.name as subject_name, s.code as subject_code
            FROM written_exam_schedules ws
            JOIN subjects s ON ws.subject_id = s.id
            WHERE ws.exam_event_id = ?
            ORDER BY ws.exam_date ASC, ws.session ASC
        ", [$event['id']]);

        $rooms = Database::fetchAll("SELECT * FROM exam_rooms WHERE exam_event_id = ? ORDER BY room_number ASC", [$event['id']]);
        $teachers = Database::fetchAll("SELECT * FROM teachers WHERE status = 'Aktif' ORDER BY name ASC");

        View::render('distribution/index', [
            'title' => 'Distribusi & Pengembalian Naskah Soal',
            'event' => $event,
            'handoverType' => $handoverType,
            'records' => $records,
            'schedules' => $schedules,
            'rooms' => $rooms,
            'teachers' => $teachers,
            'dateFilter' => $dateFilter,
            'sessionFilter' => $sessionFilter
        ]);
    }

    public function store(Request $request): void
    {
        $event = active_event();
        $handoverType = (string)$request->input('handover_type', 'pengambilan');
        $scheduleId = (int)$request->input('schedule_id');
        $roomId = (int)$request->input('room_id');
        $takenOrReturned = (int)$request->input('taken_or_returned_count', 0);
        $handoverTime = (string)$request->input('handover_time', date('H:i'));
        $officerName = trim((string)$request->input('officer_name', Auth::user()['full_name'] ?? 'Petugas Panitia'));
        $supervisorName = trim((string)$request->input('supervisor_name'));
        $conditionStatus = (string)$request->input('condition_status', 'Baik / Lengkap');
        $notes = trim((string)$request->input('notes', ''));

        $schedule = Database::fetch("SELECT * FROM written_exam_schedules WHERE id = ?", [$scheduleId]);
        $room = Database::fetch("SELECT * FROM exam_rooms WHERE id = ?", [$roomId]);

        if (!$schedule || !$room) {
            flash('error', 'Sesi jadwal atau ruang tidak valid.');
            Response::back();
        }

        // Count assigned participants in this room who match the subject grade
        $participantCount = (int)Database::fetchColumn("
            SELECT COUNT(*) FROM exam_room_assignments era
            JOIN exam_participants ep ON era.participant_id = ep.id
            WHERE era.exam_room_id = ? AND (ep.grade_snapshot = ? OR ? = 'Semua Tingkat')
        ", [$roomId, $schedule['grade'], $schedule['grade']]);

        // Configured reserve copies for this subject/grade
        $reserveCopies = (int)Database::fetchColumn(
            "SELECT reserve_copies FROM exam_printing_records WHERE exam_event_id = ? AND subject_id = ? AND grade = ?",
            [$event['id'], $schedule['subject_id'], $schedule['grade']]
        ) ?: 2;

        $requiredCount = $participantCount + $reserveCopies;
        $discrepancy = $requiredCount - $takenOrReturned;

        Database::insert('exam_paper_handover_records', [
            'exam_event_id' => $event['id'],
            'handover_type' => $handoverType,
            'exam_date' => $schedule['exam_date'],
            'session' => $schedule['session'],
            'room_id' => $roomId,
            'subject_id' => $schedule['subject_id'],
            'grade' => $schedule['grade'],
            'participant_count' => $participantCount,
            'required_count' => $requiredCount,
            'taken_or_returned_count' => $takenOrReturned,
            'discrepancy' => $discrepancy,
            'handover_time' => $handoverTime,
            'officer_name' => $officerName,
            'supervisor_name' => $supervisorName,
            'condition_status' => $conditionStatus,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($discrepancy != 0) {
            flash('warning', "Peringatan Selisih Naskah: Kebutuhan seharusnya {$requiredCount} eks, tetapi dicatat {$takenOrReturned} eks (Selisih: {$discrepancy}).");
        } else {
            flash('success', "Pencatatan {$handoverType} naskah berhasil disimpan.");
        }

        Response::redirect(url('/distribution/handover?type=' . $handoverType));
    }

    public function print(Request $request): void
    {
        $event = active_event();
        $handoverType = (string)$request->input('type', 'pengambilan');
        $dateFilter = (string)$request->input('date', '');
        $school = school_setting();

        $sql = "
            SELECT ep.*, s.name as subject_name, s.code as subject_code, er.room_number, er.name as room_name
            FROM exam_paper_handover_records ep
            JOIN subjects s ON ep.subject_id = s.id
            JOIN exam_rooms er ON ep.room_id = er.id
            WHERE ep.exam_event_id = ? AND ep.handover_type = ?
        ";
        $params = [$event['id'], $handoverType];

        if (!empty($dateFilter)) {
            $sql .= " AND ep.exam_date = ?";
            $params[] = $dateFilter;
        }

        $sql .= " ORDER BY ep.exam_date ASC, ep.session ASC, er.room_number ASC";
        $records = Database::fetchAll($sql, $params);

        View::render('distribution/print', [
            'title' => 'Daftar ' . ucfirst($handoverType) . ' Naskah Soal',
            'event' => $event,
            'school' => $school,
            'handoverType' => $handoverType,
            'records' => $records,
            'dateFilter' => $dateFilter
        ], 'layouts/print');
    }

    public function exportExcel(Request $request): void
    {
        $event = active_event();
        $handoverType = (string)$request->input('type', 'pengambilan');
        $dateFilter = (string)$request->input('date', '');

        $sql = "
            SELECT ep.*, s.name as subject_name, er.room_number
            FROM exam_paper_handover_records ep
            JOIN subjects s ON ep.subject_id = s.id
            JOIN exam_rooms er ON ep.room_id = er.id
            WHERE ep.exam_event_id = ? AND ep.handover_type = ?
        ";
        $params = [$event['id'], $handoverType];
        if (!empty($dateFilter)) {
            $sql .= " AND ep.exam_date = ?";
            $params[] = $dateFilter;
        }
        $sql .= " ORDER BY ep.exam_date ASC, ep.session ASC, er.room_number ASC";
        $records = Database::fetchAll($sql, $params);

        $headers = ['No', 'Tanggal', 'Sesi', 'Ruang', 'Mata Pelajaran', 'Tingkat', 'Peserta', 'Kebutuhan Seharusnya', 'Jumlah Diambil/Kembali', 'Selisih', 'Jam', 'Pengawas', 'Petugas', 'Kondisi', 'Catatan'];
        $rows = [];
        foreach ($records as $idx => $r) {
            $rows[] = [
                $idx + 1,
                $r['exam_date'],
                $r['session'],
                $r['room_number'],
                $r['subject_name'],
                $r['grade'],
                $r['participant_count'],
                $r['required_count'],
                $r['taken_or_returned_count'],
                $r['discrepancy'],
                $r['handover_time'],
                $r['supervisor_name'],
                $r['officer_name'],
                $r['condition_status'],
                $r['notes'] ?: '-'
            ];
        }

        ExcelExport::export('distribusi_naskah_' . $handoverType . '_' . date('Ymd'), 'DAFTAR ' . strtoupper($handoverType) . ' NASKAH SOAL', $headers, $rows);
    }
}
