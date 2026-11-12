<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class WrittenExamController
{
    // 1. Schedules
    public function schedules(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $schedules = Database::fetchAll("
            SELECT ws.*, s.name as subject_name, s.code as subject_code,
                   (SELECT COUNT(*) FROM written_exam_supervisors WHERE schedule_id = ws.id) as assigned_rooms_count
            FROM written_exam_schedules ws
            JOIN subjects s ON ws.subject_id = s.id
            WHERE ws.exam_event_id = ?
            ORDER BY ws.exam_date ASC, ws.session ASC
        ", [$event['id']]);

        $subjects = Database::fetchAll("SELECT * FROM subjects WHERE exam_type IN ('written', 'both') ORDER BY code ASC");

        View::render('written_exam/schedules', [
            'title' => 'Jadwal Ujian Tertulis',
            'event' => $event,
            'schedules' => $schedules,
            'subjects' => $subjects
        ]);
    }

    public function storeSchedule(Request $request): void
    {
        $event = active_event();
        $examDate = (string)$request->input('exam_date');
        $session = (string)$request->input('session', '1');
        $startTime = (string)$request->input('start_time');
        $endTime = (string)$request->input('end_time');
        $subjectId = (int)$request->input('subject_id');
        $grade = (string)$request->input('grade', 'VII');
        $notes = trim((string)$request->input('notes', ''));

        if (empty($examDate) || empty($startTime) || empty($endTime) || empty($subjectId)) {
            flash('error', 'Semua kolom jadwal wajib diisi.');
            Response::back();
        }

        $dayName = format_day_id($examDate);

        $id = Database::insert('written_exam_schedules', [
            'exam_event_id' => $event['id'],
            'exam_date' => $examDate,
            'day_name' => $dayName,
            'session' => $session,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'subject_id' => $subjectId,
            'grade' => $grade,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Automatically associate existing rooms to this schedule for supervisor assignment
        $rooms = Database::fetchAll("SELECT id FROM exam_rooms WHERE exam_event_id = ?", [$event['id']]);
        foreach ($rooms as $r) {
            Database::insert('written_exam_supervisors', [
                'schedule_id' => $id,
                'room_id' => $r['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        flash('success', 'Jadwal ujian tertulis berhasil ditambahkan.');
        Response::redirect(url('/written-exam/schedules'));
    }

    public function deleteSchedule(Request $request): void
    {
        $id = (int)$request->input('id');
        Database::delete('written_exam_schedules', 'id = ?', [$id]);
        flash('success', 'Jadwal ujian tertulis berhasil dihapus.');
        Response::redirect(url('/written-exam/schedules'));
    }

    // 2. Exam Rooms & Participant Assignment
    public function rooms(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $rooms = Database::fetchAll("
            SELECT er.*,
                   (SELECT COUNT(*) FROM exam_room_assignments WHERE exam_room_id = er.id) as assigned_count
            FROM exam_rooms er
            WHERE er.exam_event_id = ?
            ORDER BY er.room_number ASC
        ", [$event['id']]);

        // Total participants and assigned stats
        $totalParticipants = (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_participants WHERE exam_event_id = ?", [$event['id']]);
        $totalAssigned = (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_room_assignments WHERE exam_event_id = ?", [$event['id']]);
        $unassigned = max(0, $totalParticipants - $totalAssigned);
        $masterStudentCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM students WHERE deleted_at IS NULL AND (status IS NULL OR LOWER(status) = 'aktif' OR status = '')");
        $totalCapacity = (int)Database::fetchColumn("SELECT SUM(capacity) FROM exam_rooms WHERE exam_event_id = ?", [$event['id']]) ?: 0;

        View::render('written_exam/rooms', [
            'title' => 'Pengaturan Ruang & Pembagian Peserta',
            'event' => $event,
            'rooms' => $rooms,
            'totalParticipants' => $totalParticipants,
            'totalAssigned' => $totalAssigned,
            'unassigned' => $unassigned,
            'masterStudentCount' => $masterStudentCount,
            'totalCapacity' => $totalCapacity
        ]);
    }

    public function storeRoom(Request $request): void
    {
        $event = active_event();
        $roomNumber = trim((string)$request->input('room_number'));
        $name = trim((string)$request->input('name'));
        $capacity = (int)$request->input('capacity', 20);
        $rowsCount = (int)$request->input('rows_count', 5);
        $colsCount = (int)$request->input('cols_count', 4);
        $notes = trim((string)$request->input('notes', ''));

        if (empty($roomNumber) || empty($name)) {
            flash('error', 'Nomor ruang dan nama ruang wajib diisi.');
            Response::back();
        }

        $exists = Database::fetch("SELECT id FROM exam_rooms WHERE exam_event_id = ? AND room_number = ?", [$event['id'], $roomNumber]);
        if ($exists) {
            flash('error', "Ruang dengan nomor {$roomNumber} sudah ada.");
            Response::back();
        }

        $rId = Database::insert('exam_rooms', [
            'exam_event_id' => $event['id'],
            'room_number' => $roomNumber,
            'name' => $name,
            'capacity' => $capacity,
            'rows_count' => $rowsCount,
            'cols_count' => $colsCount,
            'notes' => $notes
        ]);

        // Link with existing schedules for supervisors
        $schedules = Database::fetchAll("SELECT id FROM written_exam_schedules WHERE exam_event_id = ?", [$event['id']]);
        foreach ($schedules as $sch) {
            Database::insert('written_exam_supervisors', [
                'schedule_id' => $sch['id'],
                'room_id' => $rId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        flash('success', "Ruang ujian [{$roomNumber} - {$name}] berhasil ditambahkan.");
        Response::redirect(url('/written-exam/rooms'));
    }

    public function deleteRoom(Request $request): void
    {
        $id = (int)$request->input('id');
        Database::delete('exam_rooms', 'id = ?', [$id]);
        flash('success', 'Ruang ujian berhasil dihapus.');
        Response::redirect(url('/written-exam/rooms'));
    }

    /**
     * Sinkronkan seluruh data siswa aktif dari Master Data Siswa ke daftar peserta kegiatan ujian aktif.
     */
    public function syncParticipants(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $students = Database::fetchAll("
            SELECT s.*, COALESCE(c.name, s.grade, 'Kelas') as class_name
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.deleted_at IS NULL AND (s.status IS NULL OR LOWER(s.status) = 'aktif' OR s.status = '')
            ORDER BY s.grade ASC, c.name ASC, s.name ASC
        ");

        if (empty($students)) {
            flash('warning', 'Master Data Siswa masih kosong. Silakan tambahkan atau impor data siswa terlebih dahulu di menu Siswa.');
            Response::redirect(url('/written-exam/rooms'));
        }

        $yearShort = substr(preg_replace('/[^0-9]/', '', (string)$event['academic_year']), 0, 2) ?: date('y');
        $num = (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_participants WHERE exam_event_id = ?", [$event['id']]);
        $added = 0;

        foreach ($students as $s) {
            $exists = Database::fetch("SELECT id FROM exam_participants WHERE exam_event_id = ? AND student_id = ?", [$event['id'], $s['id']]);
            if ($exists) continue;

            $num++;
            $partNumber = sprintf("%s-%s-%04d", $yearShort, $s['grade'] ?: 'U', $num);

            Database::insert('exam_participants', [
                'exam_event_id' => $event['id'],
                'student_id' => $s['id'],
                'participant_number' => $partNumber,
                'student_name_snapshot' => $s['name'],
                'nis_snapshot' => $s['nis'],
                'nisn_snapshot' => $s['nisn'],
                'class_name_snapshot' => $s['class_name'],
                'grade_snapshot' => $s['grade'] ?: 'VII',
                'gender_snapshot' => $s['gender'] ?: 'L',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $added++;
        }

        if ($added > 0) {
            flash('success', "Berhasil menyinkronkan {$added} siswa aktif menjadi peserta kegiatan ujian.");
        } else {
            flash('info', 'Semua siswa aktif sudah terdaftar sebagai peserta pada kegiatan ujian ini.');
        }

        Response::redirect(url('/written-exam/rooms'));
    }

    /**
     * Kosongkan seluruh penempatan peserta di ruangan ujian.
     */
    public function resetAssignments(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        Database::delete('exam_room_assignments', 'exam_event_id = ?', [$event['id']]);
        flash('success', 'Seluruh penempatan peserta di ruangan ujian berhasil dikosongkan.');
        Response::redirect(url('/written-exam/rooms'));
    }

    /**
     * Automatic balanced & randomized participant distribution into rooms.
     * Supports combining multiple classes/grades into a single room!
     */
    public function autoDistribute(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $mode = (string)$request->input('mode', 'mix_grades'); // 'mix_grades', 'sequential', 'random'
        $clearExisting = (bool)$request->input('clear_existing', true);

        $rooms = Database::fetchAll("SELECT * FROM exam_rooms WHERE exam_event_id = ? ORDER BY room_number ASC", [$event['id']]);
        if (empty($rooms)) {
            flash('error', 'Belum ada ruang ujian yang dibuat. Silakan tambahkan ruang ujian terlebih dahulu.');
            Response::redirect(url('/written-exam/rooms'));
        }

        // Periksa apakah kegiatan saat ini sudah memiliki peserta terdaftar
        $totalParticipantsInEvent = (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_participants WHERE exam_event_id = ?", [$event['id']]);
        if ($totalParticipantsInEvent === 0) {
            // Otomatis tarik seluruh siswa aktif dari master data siswa
            $masterStudents = Database::fetchAll("
                SELECT s.*, COALESCE(c.name, s.grade, 'Kelas') as class_name
                FROM students s
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE s.deleted_at IS NULL AND (s.status IS NULL OR LOWER(s.status) = 'aktif' OR s.status = '')
                ORDER BY s.grade ASC, c.name ASC, s.name ASC
            ");

            if (empty($masterStudents)) {
                flash('error', 'Tidak ada data peserta untuk didistribusikan. Master Data Siswa masih kosong, silakan tambahkan atau impor data siswa terlebih dahulu di menu Siswa.');
                Response::redirect(url('/written-exam/rooms'));
            }

            $yearShort = substr(preg_replace('/[^0-9]/', '', (string)$event['academic_year']), 0, 2) ?: date('y');
            $num = 0;
            foreach ($masterStudents as $s) {
                $num++;
                $partNumber = sprintf("%s-%s-%04d", $yearShort, $s['grade'] ?: 'U', $num);
                Database::insert('exam_participants', [
                    'exam_event_id' => $event['id'],
                    'student_id' => $s['id'],
                    'participant_number' => $partNumber,
                    'student_name_snapshot' => $s['name'],
                    'nis_snapshot' => $s['nis'],
                    'nisn_snapshot' => $s['nisn'],
                    'class_name_snapshot' => $s['class_name'],
                    'grade_snapshot' => $s['grade'] ?: 'VII',
                    'gender_snapshot' => $s['gender'] ?: 'L',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        if ($clearExisting) {
            Database::delete('exam_room_assignments', 'exam_event_id = ?', [$event['id']]);
        }

        // Ambil peserta yang belum ditempatkan ke dalam ruang ujian
        $participants = Database::fetchAll("
            SELECT * FROM exam_participants
            WHERE exam_event_id = ?
            AND id NOT IN (SELECT participant_id FROM exam_room_assignments WHERE exam_event_id = ?)
            ORDER BY grade_snapshot ASC, class_name_snapshot ASC, participant_number ASC
        ", [$event['id'], $event['id']]);

        if (empty($participants)) {
            flash('info', 'Semua peserta sudah memiliki penempatan ruang ujian.');
            Response::redirect(url('/written-exam/rooms'));
        }

        if ($mode === 'random') {
            shuffle($participants);
        } elseif ($mode === 'mix_grades') {
            // Kelompokkan berdasarkan tingkat
            $byGrade = [];
            foreach ($participants as $p) {
                $byGrade[$p['grade_snapshot']][] = $p;
            }

            // Jika hanya 1 tingkat, acak silang berdasarkan kelas rombel asal
            if (count($byGrade) <= 1) {
                $byClass = [];
                foreach ($participants as $p) {
                    $byClass[$p['class_name_snapshot']][] = $p;
                }
                $interleaved = [];
                $maxCount = 0;
                foreach ($byClass as $cList) {
                    $maxCount = max($maxCount, count($cList));
                }
                for ($i = 0; $i < $maxCount; $i++) {
                    foreach ($byClass as &$cList) {
                        if (isset($cList[$i])) {
                            $interleaved[] = $cList[$i];
                        }
                    }
                }
                $participants = $interleaved;
            } else {
                // Silang bergantian antar tingkat (VII, VIII, IX...)
                $interleaved = [];
                $maxCount = 0;
                foreach ($byGrade as $gList) {
                    $maxCount = max($maxCount, count($gList));
                }
                for ($i = 0; $i < $maxCount; $i++) {
                    foreach ($byGrade as $gKey => &$gList) {
                        if (isset($gList[$i])) {
                            $interleaved[] = $gList[$i];
                        }
                    }
                }
                $participants = $interleaved;
            }
        }

        // Distribusikan ke setiap ruang ujian sesuai kapasitas meja
        Database::beginTransaction();
        try {
            $pIdx = 0;
            $totalP = count($participants);

            foreach ($rooms as $room) {
                $existingAssignments = Database::fetchAll(
                    "SELECT seat_number FROM exam_room_assignments WHERE exam_room_id = ?",
                    [$room['id']]
                );
                $occupiedSeats = array_column($existingAssignments, 'seat_number');
                $occupiedMap = array_fill_keys($occupiedSeats, true);

                $cols = (int)$room['cols_count'] ?: 4;
                $capacity = (int)$room['capacity'];

                for ($seat = 1; $seat <= $capacity && $pIdx < $totalP; $seat++) {
                    if (isset($occupiedMap[$seat])) {
                        continue; // Meja sudah terisi
                    }

                    $p = $participants[$pIdx];
                    $rowNum = (int)ceil($seat / $cols);
                    $colNum = ($seat % $cols === 0) ? $cols : ($seat % $cols);

                    Database::insert('exam_room_assignments', [
                        'exam_event_id' => $event['id'],
                        'exam_room_id' => $room['id'],
                        'participant_id' => $p['id'],
                        'seat_number' => $seat,
                        'row_num' => $rowNum,
                        'col_num' => $colNum,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $pIdx++;
                }
            }
            Database::commit();

            if ($pIdx < $totalP) {
                $sisa = $totalP - $pIdx;
                flash('warning', "Berhasil mendistribusikan {$pIdx} peserta. Perhatian: {$sisa} peserta belum mendapatkan ruang karena kapasitas seluruh ruangan telah penuh. Silakan tambah ruangan baru.");
            } else {
                flash('success', "Berhasil mendistribusikan {$pIdx} peserta ke dalam " . count($rooms) . " ruang ujian secara otomatis.");
            }
        } catch (\Throwable $e) {
            Database::rollBack();
            flash('error', 'Gagal mendistribusikan peserta: ' . $e->getMessage());
        }

        Response::redirect(url('/written-exam/rooms'));
    }

    public function autoAssign(Request $request): void
    {
        $this->autoDistribute($request);
    }

    public function manualAssign(Request $request): void
    {
        $event = active_event();
        $roomId = (int)$request->input('room_id');
        $participantId = (int)$request->input('participant_id');
        $seatNumber = (int)$request->input('seat_number', 1);

        $exists = Database::fetch("SELECT id FROM exam_room_assignments WHERE exam_room_id = ? AND seat_number = ?", [$roomId, $seatNumber]);
        if ($exists) {
            flash('error', "Nomor meja {$seatNumber} pada ruangan tersebut sudah terisi.");
            Response::back();
        }

        Database::insert('exam_room_assignments', [
            'exam_event_id' => $event['id'],
            'exam_room_id' => $roomId,
            'participant_id' => $participantId,
            'seat_number' => $seatNumber,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        flash('success', 'Peserta berhasil ditempatkan pada ruang ujian.');
        Response::redirect(url('/written-exam/rooms'));
    }

    // 3. Seating Plan (Denah Tempat Duduk)
    public function seating(Request $request): void
    {
        $event = active_event();
        $roomId = (int)$request->input('room_id', 0);

        $rooms = Database::fetchAll("SELECT * FROM exam_rooms WHERE exam_event_id = ? ORDER BY room_number ASC", [$event['id']]);

        $selectedRoom = null;
        $seatsGrid = [];

        if ($roomId > 0) {
            $selectedRoom = Database::fetch("SELECT * FROM exam_rooms WHERE id = ? AND exam_event_id = ?", [$roomId, $event['id']]);
        } elseif (!empty($rooms)) {
            $selectedRoom = $rooms[0];
            $roomId = (int)$selectedRoom['id'];
        }

        if ($selectedRoom) {
            $assignments = Database::fetchAll("
                SELECT era.*, ep.participant_number, ep.student_name_snapshot, ep.class_name_snapshot, ep.grade_snapshot
                FROM exam_room_assignments era
                JOIN exam_participants ep ON era.participant_id = ep.id
                WHERE era.exam_room_id = ?
                ORDER BY era.seat_number ASC
            ", [$roomId]);

            // Index assignments by seat number
            $seatMap = [];
            foreach ($assignments as $a) {
                $seatMap[(int)$a['seat_number']] = $a;
            }

            // Build grid according to rows x cols
            $rows = (int)$selectedRoom['rows_count'] ?: 5;
            $cols = (int)$selectedRoom['cols_count'] ?: 4;
            $seatCounter = 1;

            for ($r = 1; $r <= $rows; $r++) {
                for ($c = 1; $c <= $cols; $c++) {
                    $seatData = $seatMap[$seatCounter] ?? null;
                    $seatsGrid[$r][$c] = [
                        'seat_number' => $seatCounter,
                        'participant' => $seatData
                    ];
                    $seatCounter++;
                }
            }
        }

        View::render('written_exam/seating', [
            'title' => 'Denah & Pengaturan Tempat Duduk',
            'event' => $event,
            'rooms' => $rooms,
            'selectedRoom' => $selectedRoom,
            'seatsGrid' => $seatsGrid
        ]);
    }

    public function printSeating(Request $request): void
    {
        $roomId = (int)$request->input('room_id');
        $room = Database::fetch("SELECT * FROM exam_rooms WHERE id = ?", [$roomId]);
        if (!$room) die("Ruang tidak ditemukan.");

        $school = school_setting();
        $event = Database::fetch("SELECT * FROM exam_events WHERE id = ?", [$room['exam_event_id']]);

        $assignments = Database::fetchAll("
            SELECT era.*, ep.participant_number, ep.student_name_snapshot, ep.class_name_snapshot, ep.grade_snapshot
            FROM exam_room_assignments era
            JOIN exam_participants ep ON era.participant_id = ep.id
            WHERE era.exam_room_id = ?
            ORDER BY era.seat_number ASC
        ", [$roomId]);

        $seatMap = [];
        foreach ($assignments as $a) {
            $seatMap[(int)$a['seat_number']] = $a;
        }

        $rows = (int)$room['rows_count'] ?: 5;
        $cols = (int)$room['cols_count'] ?: 4;
        $seatsGrid = [];
        $seatCounter = 1;

        for ($r = 1; $r <= $rows; $r++) {
            for ($c = 1; $c <= $cols; $c++) {
                $seatData = $seatMap[$seatCounter] ?? null;
                $seatsGrid[$r][$c] = [
                    'seat_number' => $seatCounter,
                    'participant' => $seatData
                ];
                $seatCounter++;
            }
        }

        View::render('written_exam/print_seating', [
            'title' => 'Denah Tempat Duduk ' . $room['name'],
            'room' => $room,
            'school' => $school,
            'event' => $event,
            'seatsGrid' => $seatsGrid,
            'assignments' => $assignments
        ], 'layouts/print');
    }

    public function updateLayout(Request $request): void
    {
        $roomId = (int)$request->input('room_id');
        $rows = (int)$request->input('rows_count', 5);
        $cols = (int)$request->input('cols_count', 4);

        Database::update('exam_rooms', [
            'rows_count' => $rows,
            'cols_count' => $cols,
            'capacity' => $rows * $cols
        ], 'id = :where_id', ['where_id' => $roomId]);

        flash('success', 'Tata letak dan kapasitas ruang ujian berhasil diperbarui.');
        Response::redirect(url("/written-exam/seating?room_id={$roomId}"));
    }

    // 4. Supervisors Assignment with Collision Prevention
    public function supervisors(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $assignments = Database::fetchAll("
            SELECT s.*, ws.exam_date, ws.session, ws.start_time, ws.end_time, ws.grade, sub.name as subject_name, sub.code as subject_code,
                   er.room_number, er.name as room_name,
                   t1.name as sup1_name, t1.nip as sup1_nip,
                   t2.name as sup2_name, t2.nip as sup2_nip
            FROM written_exam_supervisors s
            JOIN written_exam_schedules ws ON s.schedule_id = ws.id
            JOIN subjects sub ON ws.subject_id = sub.id
            JOIN exam_rooms er ON s.room_id = er.id
            LEFT JOIN teachers t1 ON s.supervisor1_id = t1.id
            LEFT JOIN teachers t2 ON s.supervisor2_id = t2.id
            WHERE ws.exam_event_id = ?
            ORDER BY ws.exam_date ASC, ws.session ASC, er.room_number ASC
        ", [$event['id']]);

        $teachers = Database::fetchAll("SELECT * FROM teachers WHERE status = 'Aktif' ORDER BY name ASC");

        View::render('written_exam/supervisors', [
            'title' => 'Penugasan Pengawas Ruang',
            'event' => $event,
            'assignments' => $assignments,
            'teachers' => $teachers
        ]);
    }

    public function updateSupervisor(Request $request): void
    {
        $id = (int)$request->input('id');
        $sup1Id = $request->input('supervisor1_id') ? (int)$request->input('supervisor1_id') : null;
        $sup2Id = $request->input('supervisor2_id') ? (int)$request->input('supervisor2_id') : null;

        $target = Database::fetch("
            SELECT s.*, ws.exam_date, ws.session, ws.exam_event_id
            FROM written_exam_supervisors s
            JOIN written_exam_schedules ws ON s.schedule_id = ws.id
            WHERE s.id = ?
        ", [$id]);

        if (!$target) {
            flash('error', 'Data penugasan tidak ditemukan.');
            Response::back();
        }

        // VALIDATION: Prevent collision (Pengawas tidak boleh ditugaskan di 2 ruang pada tanggal & sesi yang sama!)
        if ($sup1Id) {
            $clash1 = Database::fetch("
                SELECT s.id, er.name as room_name
                FROM written_exam_supervisors s
                JOIN written_exam_schedules ws ON s.schedule_id = ws.id
                JOIN exam_rooms er ON s.room_id = er.id
                WHERE ws.exam_event_id = ?
                AND ws.exam_date = ?
                AND ws.session = ?
                AND (s.supervisor1_id = ? OR s.supervisor2_id = ?)
                AND s.id != ?
            ", [$target['exam_event_id'], $target['exam_date'], $target['session'], $sup1Id, $sup1Id, $id]);

            if ($clash1) {
                $tName = Database::fetchColumn("SELECT name FROM teachers WHERE id = ?", [$sup1Id]);
                flash('error', "Peringatan Bentrok: {$tName} sudah ditugaskan mengawas di {$clash1['room_name']} pada tanggal dan sesi yang sama!");
                Response::back();
            }
        }

        if ($sup2Id) {
            if ($sup1Id && $sup1Id === $sup2Id) {
                flash('error', 'Pengawas utama dan pengawas pendamping tidak boleh orang yang sama.');
                Response::back();
            }

            $clash2 = Database::fetch("
                SELECT s.id, er.name as room_name
                FROM written_exam_supervisors s
                JOIN written_exam_schedules ws ON s.schedule_id = ws.id
                JOIN exam_rooms er ON s.room_id = er.id
                WHERE ws.exam_event_id = ?
                AND ws.exam_date = ?
                AND ws.session = ?
                AND (s.supervisor1_id = ? OR s.supervisor2_id = ?)
                AND s.id != ?
            ", [$target['exam_event_id'], $target['exam_date'], $target['session'], $sup2Id, $sup2Id, $id]);

            if ($clash2) {
                $tName = Database::fetchColumn("SELECT name FROM teachers WHERE id = ?", [$sup2Id]);
                flash('error', "Peringatan Bentrok: {$tName} sudah ditugaskan mengawas di {$clash2['room_name']} pada tanggal dan sesi yang sama!");
                Response::back();
            }
        }

        $sup1Name = $sup1Id ? Database::fetchColumn("SELECT name FROM teachers WHERE id = ?", [$sup1Id]) : null;
        $sup2Name = $sup2Id ? Database::fetchColumn("SELECT name FROM teachers WHERE id = ?", [$sup2Id]) : null;

        Database::update('written_exam_supervisors', [
            'supervisor1_id' => $sup1Id,
            'supervisor2_id' => $sup2Id,
            'supervisor1_name_snapshot' => $sup1Name,
            'supervisor2_name_snapshot' => $sup2Name
        ], 'id = :where_id', ['where_id' => $id]);

        flash('success', 'Penugasan pengawas ruang berhasil disimpan.');
        Response::redirect(url('/written-exam/supervisors'));
    }

    public function storeSupervisor(Request $request): void
    {
        $this->updateSupervisor($request);
    }

    public function deleteSupervisor(Request $request): void
    {
        $id = (int)$request->input('id');
        Database::update('written_exam_supervisors', [
            'supervisor1_id' => null,
            'supervisor2_id' => null,
            'supervisor1_name_snapshot' => null,
            'supervisor2_name_snapshot' => null
        ], 'id = :where_id', ['where_id' => $id]);

        flash('success', 'Penugasan pengawas ruang berhasil dikosongkan.');
        Response::redirect(url('/written-exam/supervisors'));
    }

    // 5. Execution: Attendance, Minutes (Berita Acara), Incidents
    public function execution(Request $request): void
    {
        $event = active_event();
        $user = user();
        $isSupervisorOnly = ($user['role'] === 'pengawas');

        $sql = "
            SELECT s.id as supervisor_assignment_id, s.room_id, s.schedule_id,
                   ws.exam_date, ws.session, ws.start_time, ws.end_time, ws.grade,
                   sub.name as subject_name, sub.code as subject_code,
                   er.room_number, er.name as room_name,
                   t1.name as sup1_name, t2.name as sup2_name,
                   wm.id as minutes_id, wm.present_count, wm.absent_count
            FROM written_exam_supervisors s
            JOIN written_exam_schedules ws ON s.schedule_id = ws.id
            JOIN subjects sub ON ws.subject_id = sub.id
            JOIN exam_rooms er ON s.room_id = er.id
            LEFT JOIN teachers t1 ON s.supervisor1_id = t1.id
            LEFT JOIN teachers t2 ON s.supervisor2_id = t2.id
            LEFT JOIN written_exam_minutes wm ON (wm.schedule_id = s.schedule_id AND wm.room_id = s.room_id)
            WHERE ws.exam_event_id = ?
        ";
        $params = [$event['id']];

        // If pengawas role, filter to only assigned rooms
        if ($isSupervisorOnly && !empty($user['teacher_id'])) {
            $sql .= " AND (s.supervisor1_id = ? OR s.supervisor2_id = ?)";
            $params[] = $user['teacher_id'];
            $params[] = $user['teacher_id'];
        }

        $sql .= " ORDER BY ws.exam_date ASC, ws.session ASC, er.room_number ASC";
        $sessions = Database::fetchAll($sql, $params);

        View::render('written_exam/execution', [
            'title' => 'Pelaksanaan Ujian, Presensi & Berita Acara',
            'event' => $event,
            'sessions' => $sessions
        ]);
    }

    public function manageRoomExecution(Request $request): void
    {
        $event = active_event();
        $scheduleId = (int)$request->input('schedule_id');
        $roomId = (int)$request->input('room_id');

        $schedule = Database::fetch("
            SELECT ws.*, s.name as subject_name, s.code as subject_code
            FROM written_exam_schedules ws
            JOIN subjects s ON ws.subject_id = s.id
            WHERE ws.id = ?
        ", [$scheduleId]);

        $room = Database::fetch("SELECT * FROM exam_rooms WHERE id = ?", [$roomId]);
        if (!$schedule || !$room) {
            flash('error', 'Sesi ujian atau ruang tidak ditemukan.');
            Response::redirect(url('/written-exam/execution'));
        }

        // Get participants assigned to this room
        $participants = Database::fetchAll("
            SELECT ep.*, era.seat_number, wea.status as attendance_status, wea.notes as attendance_notes
            FROM exam_room_assignments era
            JOIN exam_participants ep ON era.participant_id = ep.id
            LEFT JOIN written_exam_attendances wea ON (wea.schedule_id = ? AND wea.room_id = ? AND wea.participant_id = ep.id)
            WHERE era.exam_room_id = ?
            ORDER BY era.seat_number ASC
        ", [$scheduleId, $roomId, $roomId]);

        $minutes = Database::fetch("SELECT * FROM written_exam_minutes WHERE schedule_id = ? AND room_id = ?", [$scheduleId, $roomId]);
        $incidents = Database::fetchAll("
            SELECT wei.*, ep.student_name_snapshot, ep.participant_number
            FROM written_exam_incidents wei
            LEFT JOIN exam_participants ep ON wei.participant_id = ep.id
            WHERE wei.schedule_id = ? AND wei.room_id = ?
            ORDER BY wei.id DESC
        ", [$scheduleId, $roomId]);

        View::render('written_exam/room_execution', [
            'title' => "Administrasi Ruang {$room['name']} - {$schedule['subject_name']}",
            'event' => $event,
            'schedule' => $schedule,
            'room' => $room,
            'participants' => $participants,
            'minutes' => $minutes,
            'incidents' => $incidents
        ]);
    }

    public function saveAttendanceAndMinutes(Request $request): void
    {
        $event = active_event();
        $scheduleId = (int)$request->input('schedule_id');
        $roomId = (int)$request->input('room_id');
        $attendances = $request->input('attendances') ?? [];
        $minutesNotes = trim((string)$request->input('minutes_notes', ''));

        Database::beginTransaction();
        try {
            $present = 0;
            $absent = 0;
            $late = 0;

            foreach ($attendances as $partId => $data) {
                $status = $data['status'] ?? 'hadir';
                $notes = trim((string)($data['notes'] ?? ''));

                if ($status === 'hadir') $present++;
                elseif ($status === 'terlambat') { $present++; $late++; }
                else $absent++;

                Database::query("
                    INSERT INTO written_exam_attendances (exam_event_id, schedule_id, room_id, participant_id, status, notes, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON CONFLICT(schedule_id, room_id, participant_id) DO UPDATE SET
                    status = excluded.status, notes = excluded.notes
                ", [$event['id'], $scheduleId, $roomId, $partId, $status, $notes, date('Y-m-d H:i:s')]);
            }

            // Save or update minutes
            Database::query("
                INSERT INTO written_exam_minutes (exam_event_id, schedule_id, room_id, present_count, absent_count, late_count, notes, supervisor_signature, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT(schedule_id, room_id) DO UPDATE SET
                present_count = excluded.present_count,
                absent_count = excluded.absent_count,
                late_count = excluded.late_count,
                notes = excluded.notes,
                updated_at = excluded.updated_at
            ", [$event['id'], $scheduleId, $roomId, $present, $absent, $late, $minutesNotes, Auth::user()['full_name'] ?? 'Pengawas', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);

            Database::commit();
            flash('success', "Presensi ({$present} hadir, {$absent} tidak hadir) dan Berita Acara ruang berhasil disimpan.");
        } catch (\Throwable $e) {
            Database::rollBack();
            flash('error', 'Gagal menyimpan: ' . $e->getMessage());
        }

        Response::redirect(url("/written-exam/execution/manage?schedule_id={$scheduleId}&room_id={$roomId}"));
    }

    public function roomExecution(Request $request): void
    {
        $this->manageRoomExecution($request);
    }

    public function saveAttendance(Request $request): void
    {
        $this->saveAttendanceAndMinutes($request);
    }

    public function saveMinutes(Request $request): void
    {
        $this->saveAttendanceAndMinutes($request);
    }

    public function addIncident(Request $request): void
    {
        $event = active_event();
        $scheduleId = (int)$request->input('schedule_id');
        $roomId = (int)$request->input('room_id');
        $partId = $request->input('participant_id') ? (int)$request->input('participant_id') : null;
        $incidentTime = (string)$request->input('incident_time', date('H:i'));
        $description = trim((string)$request->input('incident_description'));
        $followUp = trim((string)$request->input('follow_up', ''));

        Database::insert('written_exam_incidents', [
            'exam_event_id' => $event['id'],
            'schedule_id' => $scheduleId,
            'room_id' => $roomId,
            'incident_time' => $incidentTime,
            'participant_id' => $partId,
            'incident_description' => $description,
            'follow_up' => $followUp,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        flash('success', 'Catatan kejadian khusus berhasil ditambahkan ke berita acara ruang.');
        Response::redirect(url("/written-exam/execution/manage?schedule_id={$scheduleId}&room_id={$roomId}"));
    }

    public function printMinutes(Request $request): void
    {
        $scheduleId = (int)$request->input('schedule_id');
        $roomId = (int)$request->input('room_id');

        $schedule = Database::fetch("
            SELECT ws.*, s.name as subject_name, s.code as subject_code
            FROM written_exam_schedules ws
            JOIN subjects s ON ws.subject_id = s.id
            WHERE ws.id = ?
        ", [$scheduleId]);

        $room = Database::fetch("SELECT * FROM exam_rooms WHERE id = ?", [$roomId]);
        $minutes = Database::fetch("SELECT * FROM written_exam_minutes WHERE schedule_id = ? AND room_id = ?", [$scheduleId, $roomId]);
        $supervisors = Database::fetch("
            SELECT s.*, t1.name as sup1_name, t1.nip as sup1_nip, t2.name as sup2_name, t2.nip as sup2_nip
            FROM written_exam_supervisors s
            LEFT JOIN teachers t1 ON s.supervisor1_id = t1.id
            LEFT JOIN teachers t2 ON s.supervisor2_id = t2.id
            WHERE s.schedule_id = ? AND s.room_id = ?
        ", [$scheduleId, $roomId]);

        $incidents = Database::fetchAll("SELECT * FROM written_exam_incidents WHERE schedule_id = ? AND room_id = ?", [$scheduleId, $roomId]);
        $school = school_setting();
        $event = Database::fetch("SELECT * FROM exam_events WHERE id = ?", [$schedule['exam_event_id']]);

        View::render('written_exam/print_minutes', [
            'title' => 'Berita Acara ' . $room['name'] . ' - ' . $schedule['subject_name'],
            'school' => $school,
            'event' => $event,
            'schedule' => $schedule,
            'room' => $room,
            'minutes' => $minutes,
            'supervisors' => $supervisors,
            'incidents' => $incidents
        ], 'layouts/print');
    }
}
