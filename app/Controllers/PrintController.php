<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\QrGenerator;

class PrintController
{
    /**
     * Print Center Hub / Index
     */
    public function index(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $classes = Database::fetchAll("SELECT * FROM classes ORDER BY grade ASC, name ASC");
        $rooms = Database::fetchAll("SELECT * FROM exam_rooms WHERE exam_event_id = ? ORDER BY room_number ASC", [$event['id']]);
        $schedules = Database::fetchAll("
            SELECT ws.*, s.name as subject_name, s.code as subject_code
            FROM written_exam_schedules ws
            JOIN subjects s ON ws.subject_id = s.id
            WHERE ws.exam_event_id = ?
            ORDER BY ws.exam_date ASC, ws.session ASC
        ", [$event['id']]);

        $batches = Database::fetchAll("
            SELECT b.*, s.name as subject_name
            FROM answer_sheet_packing_batches b
            LEFT JOIN subjects s ON b.subject_id = s.id
            WHERE b.exam_event_id = ?
            ORDER BY b.id DESC
        ", [$event['id']]);

        View::render('print/index', [
            'title' => 'Pusat Cetak Perlengkapan Ujian',
            'event' => $event,
            'classes' => $classes,
            'rooms' => $rooms,
            'schedules' => $schedules,
            'batches' => $batches
        ]);
    }

    /**
     * Participant Cards (Kartu Peserta Ujian) with pure PHP QR code & Photo
     */
    public function participantCards(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            Response::redirect(url('/events'));
        }

        $classId = (int)$request->input('class_id', 0);
        $roomId = (int)$request->input('room_id', 0);

        $sql = "
            SELECT ep.*, 
                   COALESCE(NULLIF(ep.student_name_snapshot, ''), s.name, '-') as name,
                   COALESCE(NULLIF(ep.nis_snapshot, ''), s.nis, '-') as nis,
                   COALESCE(NULLIF(ep.nisn_snapshot, ''), s.nisn, '-') as nisn,
                   COALESCE(NULLIF(ep.class_name_snapshot, ''), c.name, s.grade, '-') as class_name,
                   COALESCE(NULLIF(ep.grade_snapshot, ''), s.grade, 'VII') as grade,
                   era.seat_number, er.room_number, er.name as room_name,
                   s.photo_path as photo
            FROM exam_participants ep
            LEFT JOIN students s ON ep.student_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN exam_room_assignments era ON ep.id = era.participant_id
            LEFT JOIN exam_rooms er ON era.exam_room_id = er.id
            WHERE ep.exam_event_id = ?
        ";
        $params = [$event['id']];

        if ($classId > 0) {
            $sql .= " AND s.class_id = ?";
            $params[] = $classId;
        }

        if ($roomId > 0) {
            $sql .= " AND era.exam_room_id = ?";
            $params[] = $roomId;
        }

        $sql .= " ORDER BY er.room_number ASC, era.seat_number ASC, ep.participant_number ASC";
        $participants = Database::fetchAll($sql, $params);

        // Generate QR Code for each participant & resolve photo URL
        foreach ($participants as &$p) {
            $qrText = "NO:" . ($p['participant_number'] ?? '-') .
                      "|NISN:" . ($p['nisn'] ?? '-') .
                      "|NAMA:" . ($p['name'] ?? '-') .
                      "|RUANG:" . ($p['room_number'] ?? '-');
            $p['qr_svg'] = QrGenerator::svg($qrText, 85);

            // Resolve student photo
            if (!empty($p['photo'])) {
                $photoDisk = dirname(__DIR__, 2) . '/storage/uploads/' . $p['photo'];
                if (file_exists($photoDisk) && is_file($photoDisk)) {
                    $ext = strtolower(pathinfo($photoDisk, PATHINFO_EXTENSION));
                    $mime = match($ext) {
                        'png' => 'image/png',
                        'jpg', 'jpeg' => 'image/jpeg',
                        'webp' => 'image/webp',
                        default => 'image/jpeg'
                    };
                    $p['photo_url'] = 'data:' . $mime . ';base64,' . base64_encode((string)file_get_contents($photoDisk));
                } else {
                    $p['photo_url'] = url('/file/view?path=' . urlencode($p['photo']));
                }
            } else {
                $p['photo_url'] = null;
            }
        }
        unset($p);

        $school = school_setting();

        // Penanda tangan kartu peserta: Ketua Panitia Ujian
        // 1. Prioritaskan data Ketua Panitia dari struktur panitia kegiatan aktif
        $committeeHead = Database::fetch("
            SELECT cm.*, 
                   COALESCE(NULLIF(cm.custom_name, ''), t.name) as name, 
                   COALESCE(NULLIF(cm.custom_nip, ''), t.nip) as nip
            FROM committee_members cm
            JOIN committees c ON cm.committee_id = c.id
            LEFT JOIN teachers t ON cm.teacher_id = t.id
            WHERE c.exam_event_id = ? AND (cm.role_title LIKE '%Ketua Panitia%' OR cm.role_title LIKE '%Ketua%')
            ORDER BY CASE WHEN cm.role_title = 'Ketua Panitia' THEN 1 WHEN cm.role_title LIKE '%Ketua%' THEN 2 ELSE 3 END, cm.order_index ASC, cm.id ASC
            LIMIT 1
        ", [$event['id']]);

        $committeeSignerName = !empty($committeeHead['name']) ? $committeeHead['name'] : (!empty($school['committee_head']) ? $school['committee_head'] : 'Ketua Panitia');
        $committeeSignerNip = !empty($committeeHead['nip']) ? $committeeHead['nip'] : '';

        // 2. Jika NIP masih kosong namun nama cocok dengan data guru, cari NIP guru terkait
        if (empty($committeeSignerNip) && !empty($committeeSignerName) && $committeeSignerName !== 'Ketua Panitia') {
            $t = Database::fetch("SELECT nip FROM teachers WHERE name = ? LIMIT 1", [$committeeSignerName]);
            if ($t && !empty($t['nip'])) {
                $committeeSignerNip = $t['nip'];
            }
        }

        // Use print layout
        View::layout('layouts/print');
        View::render('print/participant_cards', [
            'title' => 'Cetak Kartu Peserta Ujian',
            'event' => $event,
            'school' => $school,
            'participants' => $participants,
            'committeeSignerName' => $committeeSignerName,
            'committeeSignerNip' => $committeeSignerNip,
            'hideKop' => true,
        ]);
    }

    /**
     * Name Tags (Panitia & Pengawas)
     */
    public function nameTags(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            Response::redirect(url('/events'));
        }

        $type = (string)$request->input('type', 'panitia'); // 'panitia' or 'pengawas'
        $school = school_setting();
        $tags = [];

        if ($type === 'panitia') {
            $members = Database::fetchAll("
                SELECT cm.*, cm.role_title as position, COALESCE(cm.custom_name, t.name) as name, COALESCE(cm.custom_nip, t.nip) as nip
                FROM committee_members cm
                JOIN committees c ON cm.committee_id = c.id
                LEFT JOIN teachers t ON cm.teacher_id = t.id
                WHERE c.exam_event_id = ?
                ORDER BY cm.order_index ASC, cm.id ASC
            ", [$event['id']]);

            foreach ($members as $m) {
                $qrText = "PANITIA|EVENT:" . $event['name'] . "|NAMA:" . $m['name'] . "|JABATAN:" . $m['position'];
                $tags[] = [
                    'name' => $m['name'],
                    'nip' => $m['nip'] ?? '-',
                    'role_label' => 'PANITIA UJIAN',
                    'position' => $m['position'],
                    'qr_svg' => QrGenerator::svg($qrText, 70)
                ];
            }
        } else {
            // Pengawas Ruang
            $supervisors = Database::fetchAll("
                SELECT DISTINCT s.supervisor1_name_snapshot as supervisor_name, t.nip, t.position as teacher_pos
                FROM written_exam_supervisors s
                JOIN written_exam_schedules ws ON s.schedule_id = ws.id
                LEFT JOIN teachers t ON s.supervisor1_id = t.id
                WHERE ws.exam_event_id = ? AND s.supervisor1_name_snapshot IS NOT NULL
                ORDER BY s.supervisor1_name_snapshot ASC
            ", [$event['id']]);

            foreach ($supervisors as $s) {
                $qrText = "PENGAWAS|EVENT:" . $event['name'] . "|NAMA:" . $s['supervisor_name'];
                $tags[] = [
                    'name' => $s['supervisor_name'],
                    'nip' => $s['nip'] ?? '-',
                    'role_label' => 'PENGAWAS RUANG',
                    'position' => 'Pengawas Ruang Ujian',
                    'qr_svg' => QrGenerator::svg($qrText, 70)
                ];
            }
        }

        View::layout('layouts/print');
        View::render('print/name_tags', [
            'title' => 'Cetak Name Tag ' . ($type === 'panitia' ? 'Panitia' : 'Pengawas'),
            'event' => $event,
            'school' => $school,
            'type' => $type,
            'tags' => $tags,
            'hideKop' => true,
        ]);
    }

    /**
     * Room Signs (Label Pintu Ruang)
     */
    public function roomSigns(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            Response::redirect(url('/events'));
        }

        $roomId = (int)$request->input('room_id', 0);
        $school = school_setting();

        $sql = "SELECT * FROM exam_rooms WHERE exam_event_id = ?";
        $params = [$event['id']];
        if ($roomId > 0) {
            $sql .= " AND id = ?";
            $params[] = $roomId;
        }
        $sql .= " ORDER BY room_number ASC";
        $rooms = Database::fetchAll($sql, $params);

        foreach ($rooms as &$r) {
            // Get breakdown of classes and participant range in this room
            $participants = Database::fetchAll("
                SELECT ep.participant_number, ep.student_name_snapshot as name, ep.class_name_snapshot, ep.grade_snapshot as grade
                FROM exam_room_assignments era
                JOIN exam_participants ep ON era.participant_id = ep.id
                WHERE era.exam_room_id = ?
                ORDER BY era.seat_number ASC, ep.participant_number ASC
            ", [$r['id']]);

            $r['total_assigned'] = count($participants);
            $classesFound = [];
            $gradesFound = [];
            foreach ($participants as $p) {
                if (!empty($p['class_name_snapshot'])) {
                    $classesFound[$p['class_name_snapshot']] = true;
                }
                if (!empty($p['grade'])) {
                    $gradesFound[$p['grade']] = true;
                }
            }
            ksort($classesFound);
            ksort($gradesFound);

            $r['classes_list'] = array_keys($classesFound);
            $r['grades_list'] = array_keys($gradesFound);
            $r['first_no'] = !empty($participants) ? $participants[0]['participant_number'] : '-';
            $r['last_no'] = !empty($participants) ? $participants[count($participants) - 1]['participant_number'] : '-';
        }
        unset($r);

        View::layout('layouts/print');
        View::render('print/room_signs', [
            'title' => 'Cetak Label Pintu Ruang Ujian',
            'event' => $event,
            'school' => $school,
            'rooms' => $rooms,
            'hideKop' => true,
        ]);
    }

    /**
     * CRITICAL: Combined Question Paper Envelope Label (Label Amplop Naskah Soal)
     * Rule: 1 Ruang = 1 Amplop = 1 Label (Scenario A)
     * Even if a room contains mixed grades (VII A, VII B, VIII A, IX B) and multiple subjects.
     * Must have configurable reserve count.
     * ABSOLUTELY NO signature columns on this envelope label.
     */
    public function questionEnvelopeLabels(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            Response::redirect(url('/events'));
        }

        $dateFilter = (string)$request->input('date', '');
        $sessionFilter = (string)$request->input('session', '');
        $roomId = (int)$request->input('room_id', 0);
        $reservePerSubject = max(0, (int)$request->input('reserve', 2)); // Configurable reserve count, default 2

        $school = school_setting();

        // Find schedules for active event
        $schedSql = "
            SELECT ws.*, s.name as subject_name, s.code as subject_code
            FROM written_exam_schedules ws
            JOIN subjects s ON ws.subject_id = s.id
            WHERE ws.exam_event_id = ?
        ";
        $schedParams = [$event['id']];
        if (!empty($dateFilter)) {
            $schedSql .= " AND ws.exam_date = ?";
            $schedParams[] = $dateFilter;
        }
        if (!empty($sessionFilter)) {
            $schedSql .= " AND ws.session = ?";
            $schedParams[] = $sessionFilter;
        }
        $schedSql .= " ORDER BY ws.exam_date ASC, ws.session ASC, ws.grade ASC";
        $schedules = Database::fetchAll($schedSql, $schedParams);

        // Group schedules by exam_date and session
        $scheduleGroups = [];
        foreach ($schedules as $s) {
            $key = $s['exam_date'] . '_' . $s['session'];
            if (!isset($scheduleGroups[$key])) {
                $scheduleGroups[$key] = [
                    'exam_date' => $s['exam_date'],
                    'session' => $s['session'],
                    'start_time' => $s['start_time'],
                    'end_time' => $s['end_time'],
                    'subjects_by_grade' => []
                ];
            }
            $scheduleGroups[$key]['subjects_by_grade'][$s['grade']] = $s;
        }

        // Exam rooms
        $roomSql = "SELECT * FROM exam_rooms WHERE exam_event_id = ?";
        $roomParams = [$event['id']];
        if ($roomId > 0) {
            $roomSql .= " AND id = ?";
            $roomParams[] = $roomId;
        }
        $roomSql .= " ORDER BY room_number ASC";
        $rooms = Database::fetchAll($roomSql, $roomParams);

        $labels = [];

        // For each date+session and each room, build 1 COMBINED LABEL (1 Room = 1 Envelope = 1 Label)
        foreach ($scheduleGroups as $group) {
            foreach ($rooms as $room) {
                // Find all participants in this room, grouped by grade and class
                $assignments = Database::fetchAll("
                    SELECT ep.id, ep.participant_number, ep.student_name_snapshot as name, ep.class_name_snapshot, ep.grade_snapshot as grade
                    FROM exam_room_assignments era
                    JOIN exam_participants ep ON era.participant_id = ep.id
                    WHERE era.exam_room_id = ?
                    ORDER BY ep.grade_snapshot ASC, ep.class_name_snapshot ASC, era.seat_number ASC
                ", [$room['id']]);

                if (empty($assignments)) {
                    continue; // Skip empty room
                }

                // Group participants by grade and class
                $breakdown = [];
                $classesInRoom = [];
                $gradesInRoom = [];
                $totalMain = 0;
                $totalReserve = 0;

                // Group by grade first
                $byGrade = [];
                foreach ($assignments as $a) {
                    $g = $a['grade'] ?: 'Umum';
                    $cls = $a['class_name_snapshot'] ?: 'Umum';
                    $byGrade[$g][$cls][] = $a;
                    $classesInRoom[$cls] = true;
                    $gradesInRoom[$g] = true;
                }

                foreach ($byGrade as $grade => $classes) {
                    // Check which subject schedule applies for this grade in this date/session
                    $subjectInfo = $group['subjects_by_grade'][$grade] ?? null;
                    if (!$subjectInfo && isset($group['subjects_by_grade']['Semua'])) {
                        $subjectInfo = $group['subjects_by_grade']['Semua'];
                    }

                    $subjName = $subjectInfo ? ($subjectInfo['subject_code'] . ' - ' . $subjectInfo['subject_name']) : 'Mata Pelajaran Ujian';

                    foreach ($classes as $clsName => $students) {
                        $count = count($students);
                        $totalMain += $count;
                        $res = $reservePerSubject;
                        $totalReserve += $res;

                        $breakdown[] = [
                            'grade' => $grade,
                            'class_name' => $clsName,
                            'subject_name' => $subjName,
                            'main_count' => $count,
                            'reserve_count' => $res,
                            'total_count' => $count + $res
                        ];
                    }
                }

                $labels[] = [
                    'room_number' => $room['room_number'],
                    'room_name' => $room['name'],
                    'exam_date' => $group['exam_date'],
                    'session' => $group['session'],
                    'start_time' => $group['start_time'],
                    'end_time' => $group['end_time'],
                    'classes_list' => array_keys($classesInRoom),
                    'grades_list' => array_keys($gradesInRoom),
                    'breakdown' => $breakdown,
                    'total_main' => $totalMain,
                    'total_reserve' => $totalReserve,
                    'grand_total' => $totalMain + $totalReserve
                ];
            }
        }

        View::layout('layouts/print');
        View::render('print/question_labels', [
            'title' => 'Cetak Label Amplop Naskah Soal (1 Ruang = 1 Amplop)',
            'event' => $event,
            'school' => $school,
            'labels' => $labels,
            'reservePerSubject' => $reservePerSubject,
            'hideKop' => true,
        ]);
    }

    /**
     * Answer Sheet Envelope Labels (3 Modes)
     */
    public function answerSheetEnvelopeLabels(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            Response::redirect(url('/events'));
        }

        $batchId = (int)$request->input('batch_id', 0);
        $school = school_setting();

        $sql = "
            SELECT el.*, b.batch_code, b.packing_mode, b.class_name, b.subject_id,
                   b.total_sheets, b.envelope_count, b.packing_officer, b.packing_date,
                   b.status as batch_status, b.notes as batch_notes,
                   s.name as subject_name, s.code as subject_code
            FROM answer_sheet_envelope_labels el
            JOIN answer_sheet_packing_batches b ON el.batch_id = b.id
            LEFT JOIN subjects s ON b.subject_id = s.id
            WHERE b.exam_event_id = ?
        ";
        $params = [$event['id']];
        if ($batchId > 0) {
            $sql .= " AND el.batch_id = ?";
            $params[] = $batchId;
        }
        $sql .= " ORDER BY b.id DESC, el.envelope_number ASC";
        $labels = Database::fetchAll($sql, $params);

        // For each label, fetch item details (the constituent receipts)
        foreach ($labels as &$lbl) {
            $items = Database::fetchAll("
                SELECT asr.*, s.name as subject_name, s.code as subject_code, er.room_number
                FROM answer_sheet_packing_items pi
                JOIN answer_sheet_receipts asr ON pi.receipt_id = asr.id
                JOIN subjects s ON asr.subject_id = s.id
                JOIN exam_rooms er ON asr.room_id = er.id
                WHERE pi.batch_id = ?
                ORDER BY er.room_number ASC, asr.class_name ASC
            ", [$lbl['batch_id']]);

            $lbl['items'] = $items;
        }
        unset($lbl);

        View::layout('layouts/print');
        View::render('print/answer_sheet_labels', [
            'title' => 'Cetak Label Amplop Lembar Jawaban',
            'event' => $event,
            'school' => $school,
            'labels' => $labels,
            'hideKop' => true,
        ]);
    }
}
