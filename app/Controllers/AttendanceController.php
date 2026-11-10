<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\ExcelExport;

class AttendanceController
{
    // 1. Committee Attendance
    public function committee(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $dateFilter = (string)$request->input('date', date('Y-m-d'));

        $members = Database::fetchAll("
            SELECT cm.*, t.name as teacher_name, t.nip as teacher_nip,
                   ca.id as attendance_id, ca.check_in_time, ca.check_out_time, ca.status as attendance_status, ca.notes as attendance_notes, ca.digital_signed
            FROM committee_members cm
            JOIN committees c ON cm.committee_id = c.id
            LEFT JOIN teachers t ON cm.teacher_id = t.id
            LEFT JOIN committee_attendances ca ON (ca.committee_member_id = cm.id AND ca.attendance_date = ?)
            WHERE c.exam_event_id = ?
            ORDER BY cm.order_index ASC, cm.id ASC
        ", [$dateFilter, $event['id']]);

        View::render('attendance/committee', [
            'title' => 'Daftar Hadir Panitia Ujian',
            'event' => $event,
            'members' => $members,
            'selectedDate' => $dateFilter
        ]);
    }

    public function saveCommitteeAttendance(Request $request): void
    {
        $event = active_event();
        $date = (string)$request->input('date', date('Y-m-d'));
        $attendances = $request->input('attendances') ?? [];

        Database::beginTransaction();
        try {
            foreach ($attendances as $cmId => $data) {
                $status = $data['status'] ?? 'hadir';
                $checkIn = !empty($data['check_in']) ? $data['check_in'] : '07:00';
                $checkOut = !empty($data['check_out']) ? $data['check_out'] : '15:30';
                $notes = trim((string)($data['notes'] ?? ''));

                Database::query("
                    INSERT INTO committee_attendances (exam_event_id, attendance_date, committee_member_id, check_in_time, check_out_time, status, notes, digital_signed, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)
                    ON CONFLICT(exam_event_id, attendance_date, committee_member_id) DO UPDATE SET
                    check_in_time = excluded.check_in_time,
                    check_out_time = excluded.check_out_time,
                    status = excluded.status,
                    notes = excluded.notes,
                    digital_signed = 1
                ", [$event['id'], $date, $cmId, $checkIn, $checkOut, $status, $notes, date('Y-m-d H:i:s')]);
            }
            Database::commit();
            flash('success', 'Presensi kehadiran panitia berhasil disimpan.');
        } catch (\Throwable $e) {
            Database::rollBack();
            flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }

        Response::redirect(url('/attendance/committee?date=' . $date));
    }

    public function printCommitteeAttendance(Request $request): void
    {
        $event = active_event();
        $date = (string)$request->input('date', date('Y-m-d'));
        $isBlank = (bool)$request->input('blank', false);
        $school = school_setting();

        $members = Database::fetchAll("
            SELECT cm.*, t.name as teacher_name, t.nip as teacher_nip,
                   ca.check_in_time, ca.check_out_time, ca.status as attendance_status, ca.notes as attendance_notes
            FROM committee_members cm
            JOIN committees c ON cm.committee_id = c.id
            LEFT JOIN teachers t ON cm.teacher_id = t.id
            LEFT JOIN committee_attendances ca ON (ca.committee_member_id = cm.id AND ca.attendance_date = ?)
            WHERE c.exam_event_id = ?
            ORDER BY cm.order_index ASC
        ", [$date, $event['id']]);

        View::render('attendance/print_committee', [
            'title' => 'Daftar Hadir Panitia ' . format_date_id($date),
            'event' => $event,
            'school' => $school,
            'members' => $members,
            'date' => $date,
            'isBlank' => $isBlank
        ], 'layouts/print');
    }

    public function saveCommittee(Request $request): void
    {
        $this->saveCommitteeAttendance($request);
    }

    public function printCommittee(Request $request): void
    {
        $this->printCommitteeAttendance($request);
    }

    public function exportCommitteeExcel(Request $request): void
    {
        $event = active_event();
        $date = (string)$request->input('date', date('Y-m-d'));

        $members = Database::fetchAll("
            SELECT cm.*, t.name as teacher_name, t.nip as teacher_nip,
                   ca.check_in_time, ca.check_out_time, ca.status as attendance_status, ca.notes as attendance_notes
            FROM committee_members cm
            JOIN committees c ON cm.committee_id = c.id
            LEFT JOIN teachers t ON cm.teacher_id = t.id
            LEFT JOIN committee_attendances ca ON (ca.committee_member_id = cm.id AND ca.attendance_date = ?)
            WHERE c.exam_event_id = ?
            ORDER BY cm.order_index ASC
        ", [$date, $event['id']]);

        $headers = ['No', 'Nama Panitia', 'NIP', 'Jabatan / Seksi', 'Jam Datang', 'Jam Pulang', 'Status Kehadiran', 'Keterangan'];
        $rows = [];
        foreach ($members as $idx => $m) {
            $rows[] = [
                $idx + 1,
                $m['teacher_name'] ?: $m['custom_name'],
                $m['teacher_nip'] ?: $m['custom_nip'] ?: '-',
                $m['role_title'] . ($m['section_name'] ? ' (' . $m['section_name'] . ')' : ''),
                $m['check_in_time'] ?: '-',
                $m['check_out_time'] ?: '-',
                ucfirst($m['attendance_status'] ?: 'Hadir'),
                $m['attendance_notes'] ?: '-'
            ];
        }

        ExcelExport::export('kehadiran_panitia_' . str_replace('-', '', $date), 'DAFTAR HADIR PANITIA - ' . format_date_id($date), $headers, $rows);
    }

    // 2. Supervisors Attendance (Pulled directly from supervisor assignment)
    public function supervisors(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $dateFilter = (string)$request->input('date', '');
        $sessionFilter = (string)$request->input('session', '');

        $sql = "
            SELECT s.id as supervisor_assignment_id, s.room_id, s.schedule_id,
                   ws.exam_date, ws.session, ws.start_time, ws.end_time, ws.grade,
                   sub.name as subject_name, er.room_number, er.name as room_name,
                   t1.name as sup1_name, t1.nip as sup1_nip,
                   t2.name as sup2_name, t2.nip as sup2_nip,
                   sa.id as attendance_id, sa.status as attendance_status, sa.check_in_time, sa.check_out_time, sa.notes as attendance_notes, sa.digital_signed
            FROM written_exam_supervisors s
            JOIN written_exam_schedules ws ON s.schedule_id = ws.id
            JOIN subjects sub ON ws.subject_id = sub.id
            JOIN exam_rooms er ON s.room_id = er.id
            LEFT JOIN teachers t1 ON s.supervisor1_id = t1.id
            LEFT JOIN teachers t2 ON s.supervisor2_id = t2.id
            LEFT JOIN supervisor_attendances sa ON (sa.written_supervisor_id = s.id AND sa.attendance_date = ws.exam_date AND sa.session = ws.session)
            WHERE ws.exam_event_id = ?
        ";
        $params = [$event['id']];

        if (!empty($dateFilter)) {
            $sql .= " AND ws.exam_date = ?";
            $params[] = $dateFilter;
        }

        if (!empty($sessionFilter)) {
            $sql .= " AND ws.session = ?";
            $params[] = $sessionFilter;
        }

        $sql .= " ORDER BY ws.exam_date ASC, ws.session ASC, er.room_number ASC";
        $supervisors = Database::fetchAll($sql, $params);

        View::render('attendance/supervisors', [
            'title' => 'Daftar Hadir Pengawas Ruang',
            'event' => $event,
            'supervisors' => $supervisors,
            'dateFilter' => $dateFilter,
            'sessionFilter' => $sessionFilter
        ]);
    }

    public function saveSupervisorAttendance(Request $request): void
    {
        $event = active_event();
        $assignmentId = (int)$request->input('assignment_id');
        $status = (string)$request->input('status', 'hadir');
        $checkIn = (string)$request->input('check_in_time', '07:15');
        $checkOut = (string)$request->input('check_out_time', '09:45');
        $notes = trim((string)$request->input('notes', ''));

        $assignment = Database::fetch("
            SELECT s.*, ws.exam_date, ws.session
            FROM written_exam_supervisors s
            JOIN written_exam_schedules ws ON s.schedule_id = ws.id
            WHERE s.id = ?
        ", [$assignmentId]);

        if (!$assignment) {
            flash('error', 'Penugasan tidak ditemukan.');
            Response::back();
        }

        Database::query("
            INSERT INTO supervisor_attendances (exam_event_id, written_supervisor_id, attendance_date, session, status, check_in_time, check_out_time, digital_signed, notes, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?)
            ON CONFLICT(exam_event_id, written_supervisor_id, attendance_date, session) DO UPDATE SET
            status = excluded.status,
            check_in_time = excluded.check_in_time,
            check_out_time = excluded.check_out_time,
            digital_signed = 1,
            notes = excluded.notes
        ", [$event['id'], $assignmentId, $assignment['exam_date'], $assignment['session'], $status, $checkIn, $checkOut, $notes, date('Y-m-d H:i:s')]);

        flash('success', 'Presensi pengawas berhasil dicatat.');
        Response::back();
    }

    public function printSupervisorAttendance(Request $request): void
    {
        $event = active_event();
        $dateFilter = (string)$request->input('date', '');
        $isBlank = (bool)$request->input('blank', false);
        $school = school_setting();

        $sql = "
            SELECT s.*, ws.exam_date, ws.session, ws.start_time, ws.end_time,
                   sub.name as subject_name, er.room_number, er.name as room_name,
                   t1.name as sup1_name, t1.nip as sup1_nip,
                   t2.name as sup2_name, t2.nip as sup2_nip,
                   sa.status as attendance_status, sa.check_in_time, sa.check_out_time, sa.notes as attendance_notes
            FROM written_exam_supervisors s
            JOIN written_exam_schedules ws ON s.schedule_id = ws.id
            JOIN subjects sub ON ws.subject_id = sub.id
            JOIN exam_rooms er ON s.room_id = er.id
            LEFT JOIN teachers t1 ON s.supervisor1_id = t1.id
            LEFT JOIN teachers t2 ON s.supervisor2_id = t2.id
            LEFT JOIN supervisor_attendances sa ON (sa.written_supervisor_id = s.id AND sa.attendance_date = ws.exam_date AND sa.session = ws.session)
            WHERE ws.exam_event_id = ?
        ";
        $params = [$event['id']];
        if (!empty($dateFilter)) {
            $sql .= " AND ws.exam_date = ?";
            $params[] = $dateFilter;
        }
        $sql .= " ORDER BY ws.exam_date ASC, ws.session ASC, er.room_number ASC";
        $records = Database::fetchAll($sql, $params);

        View::render('attendance/print_supervisors', [
            'title' => 'Daftar Hadir Pengawas Ruang',
            'event' => $event,
            'school' => $school,
            'records' => $records,
            'dateFilter' => $dateFilter,
            'isBlank' => $isBlank
        ], 'layouts/print');
    }

    public function saveSupervisors(Request $request): void
    {
        $this->saveSupervisorAttendance($request);
    }

    public function printSupervisors(Request $request): void
    {
        $this->printSupervisorAttendance($request);
    }

    public function exportSupervisorsExcel(Request $request): void
    {
        $event = active_event();
        $dateFilter = (string)$request->input('date', '');

        $sql = "
            SELECT ws.exam_date, ws.session, ws.start_time, ws.end_time,
                   er.room_number, er.name as room_name,
                   s.name as subject_name,
                   t1.name as sup1_name,
                   t2.name as sup2_name,
                   sa.status as attendance_status, sa.check_in_time, sa.notes as attendance_notes
            FROM written_exam_supervisors wes
            JOIN written_exam_schedules ws ON wes.schedule_id = ws.id
            JOIN exam_rooms er ON wes.room_id = er.id
            JOIN subjects s ON ws.subject_id = s.id
            LEFT JOIN teachers t1 ON wes.supervisor1_id = t1.id
            LEFT JOIN teachers t2 ON wes.supervisor2_id = t2.id
            LEFT JOIN supervisor_attendances sa ON (sa.written_supervisor_id = wes.id AND sa.attendance_date = ws.exam_date AND sa.session = ws.session)
            WHERE ws.exam_event_id = ?
        ";
        $params = [$event['id']];
        if (!empty($dateFilter)) {
            $sql .= " AND ws.exam_date = ?";
            $params[] = $dateFilter;
        }
        $sql .= " ORDER BY ws.exam_date ASC, ws.session ASC, er.room_number ASC";
        $records = Database::fetchAll($sql, $params);

        $headers = ['No', 'Tanggal', 'Sesi', 'Ruang', 'Mata Pelajaran', 'Pengawas 1', 'Pengawas 2', 'Status Kehadiran', 'Jam Hadir', 'Catatan'];
        $rows = [];
        foreach ($records as $idx => $r) {
            $rows[] = [
                $idx + 1,
                $r['exam_date'],
                'Sesi ' . $r['session'],
                $r['room_number'] . ' - ' . $r['room_name'],
                $r['subject_name'],
                $r['sup1_name'] ?: '-',
                $r['sup2_name'] ?: '-',
                ucfirst($r['attendance_status'] ?: 'Hadir'),
                $r['check_in_time'] ?: '-',
                $r['attendance_notes'] ?: '-'
            ];
        }

        ExcelExport::export('kehadiran_pengawas_' . ($dateFilter ?: date('Ymd')), 'DAFTAR HADIR PENGAWAS RUANG', $headers, $rows);
    }

    // 3. Practical Examiners Attendance
    public function examiners(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $records = Database::fetchAll("
            SELECT pa.*, ps.exam_date, ps.start_time, ps.end_time, ps.location, ps.station,
                   sub.name as subject_name, t.name as examiner_name, t.nip as examiner_nip,
                   pea.id as attendance_id, pea.status as attendance_status, pea.check_in_time, pea.notes as attendance_notes
            FROM practical_exam_assignments pa
            JOIN practical_exam_schedules ps ON pa.practical_schedule_id = ps.id
            JOIN subjects sub ON ps.subject_id = sub.id
            JOIN teachers t ON pa.examiner_id = t.id
            LEFT JOIN practical_examiner_attendances pea ON (pea.practical_assignment_id = pa.id AND pea.attendance_date = ps.exam_date)
            WHERE ps.exam_event_id = ?
            ORDER BY ps.exam_date ASC, ps.start_time ASC
        ", [$event['id']]);

        View::render('attendance/examiners', [
            'title' => 'Daftar Hadir Penguji Praktik',
            'event' => $event,
            'records' => $records
        ]);
    }

    public function saveExaminerAttendance(Request $request): void
    {
        $event = active_event();
        $assignmentId = (int)$request->input('assignment_id');
        $status = (string)$request->input('status', 'hadir');
        $checkIn = (string)$request->input('check_in_time', '07:30');
        $notes = trim((string)$request->input('notes', ''));

        $assignment = Database::fetch("
            SELECT pa.*, ps.exam_date
            FROM practical_exam_assignments pa
            JOIN practical_exam_schedules ps ON pa.practical_schedule_id = ps.id
            WHERE pa.id = ?
        ", [$assignmentId]);

        if (!$assignment) {
            flash('error', 'Penugasan tidak ditemukan.');
            Response::back();
        }

        Database::query("
            INSERT INTO practical_examiner_attendances (exam_event_id, practical_assignment_id, attendance_date, session, status, check_in_time, digital_signed, notes, created_at)
            VALUES (?, ?, ?, '1', ?, ?, 1, ?, ?)
            ON CONFLICT(exam_event_id, practical_assignment_id, attendance_date, session) DO UPDATE SET
            status = excluded.status,
            check_in_time = excluded.check_in_time,
            notes = excluded.notes,
            digital_signed = 1
        ", [$event['id'], $assignmentId, $assignment['exam_date'], $status, $checkIn, $notes, date('Y-m-d H:i:s')]);

        flash('success', 'Presensi penguji praktik berhasil disimpan.');
        Response::back();
    }

    public function saveExaminers(Request $request): void
    {
        $this->saveExaminerAttendance($request);
    }

    public function printExaminersAttendance(Request $request): void
    {
        $event = active_event();
        $school = school_setting();
        $records = Database::fetchAll("
            SELECT pa.*, ps.exam_date, ps.start_time, ps.end_time, ps.location, ps.station, s.name as subject_name,
                   t.name as examiner_name, t.nip as examiner_nip,
                   pea.status as attendance_status, pea.check_in_time, pea.notes as attendance_notes
            FROM practical_exam_assignments pa
            JOIN practical_exam_schedules ps ON pa.practical_schedule_id = ps.id
            JOIN subjects s ON ps.subject_id = s.id
            JOIN teachers t ON pa.examiner_id = t.id
            LEFT JOIN practical_examiner_attendances pea ON (pea.practical_assignment_id = pa.id AND pea.attendance_date = ps.exam_date)
            WHERE ps.exam_event_id = ?
            ORDER BY ps.exam_date ASC, ps.start_time ASC
        ", [$event['id']]);

        View::render('attendance/print_examiners', [
            'title' => 'Daftar Hadir Penguji Ujian Praktik',
            'event' => $event,
            'school' => $school,
            'records' => $records
        ], 'layouts/print');
    }

    public function printExaminers(Request $request): void
    {
        $this->printExaminersAttendance($request);
    }

    public function exportExaminersExcel(Request $request): void
    {
        $event = active_event();
        $records = Database::fetchAll("
            SELECT ps.exam_date, ps.start_time, ps.end_time, ps.location, ps.station, s.name as subject_name,
                   t.name as examiner_name, t.nip as examiner_nip, pa.examiner_role,
                   pea.status as attendance_status, pea.check_in_time, pea.notes as attendance_notes
            FROM practical_exam_assignments pa
            JOIN practical_exam_schedules ps ON pa.practical_schedule_id = ps.id
            JOIN subjects s ON ps.subject_id = s.id
            JOIN teachers t ON pa.examiner_id = t.id
            LEFT JOIN practical_examiner_attendances pea ON (pea.practical_assignment_id = pa.id AND pea.attendance_date = ps.exam_date)
            WHERE ps.exam_event_id = ?
            ORDER BY ps.exam_date ASC, ps.start_time ASC
        ", [$event['id']]);

        $headers = ['No', 'Tanggal', 'Waktu', 'Mata Pelajaran', 'Lokasi/Pos', 'Nama Penguji', 'NIP', 'Peran', 'Status', 'Jam Hadir', 'Catatan'];
        $rows = [];
        foreach ($records as $idx => $r) {
            $rows[] = [
                $idx + 1,
                $r['exam_date'],
                $r['start_time'] . ' - ' . $r['end_time'],
                $r['subject_name'],
                $r['location'] . ($r['station'] ? ' (' . $r['station'] . ')' : ''),
                $r['examiner_name'],
                $r['examiner_nip'] ?: '-',
                $r['examiner_role'],
                ucfirst($r['attendance_status'] ?: 'Hadir'),
                $r['check_in_time'] ?: '-',
                $r['attendance_notes'] ?: '-'
            ];
        }

        ExcelExport::export('kehadiran_penguji_' . date('Ymd'), 'DAFTAR HADIR PENGUJI PRAKTIK', $headers, $rows);
    }
}
