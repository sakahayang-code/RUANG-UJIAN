<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\DocxGenerator;

class ReportController
{
    /**
     * Berita Acara & Kejadian Khusus (Minutes & Incidents Hub)
     */
    public function minutes(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        // 1. Incidents
        $incidents = Database::fetchAll("
            SELECT * FROM exam_incidents
            WHERE exam_event_id = ?
            ORDER BY incident_date DESC, id DESC
        ", [$event['id']]);

        // 2. Written Exam Minutes summary
        $writtenMinutes = Database::fetchAll("
            SELECT wem.*, ws.exam_date, ws.session, ws.start_time, ws.end_time,
                   s.name as subject_name, er.room_number, er.name as room_name
            FROM written_exam_minutes wem
            JOIN written_exam_schedules ws ON wem.schedule_id = ws.id
            JOIN subjects s ON ws.subject_id = s.id
            JOIN exam_rooms er ON wem.room_id = er.id
            WHERE wem.exam_event_id = ?
            ORDER BY ws.exam_date DESC, er.room_number ASC
        ", [$event['id']]);

        // 3. Practical Exam Minutes summary
        $practicalMinutes = Database::fetchAll("
            SELECT pem.*, ps.exam_date, ps.session, s.name as subject_name
            FROM practical_exam_minutes pem
            JOIN practical_exam_schedules ps ON pem.schedule_id = ps.id
            JOIN subjects s ON ps.subject_id = s.id
            WHERE pem.exam_event_id = ?
            ORDER BY ps.exam_date DESC
        ", [$event['id']]);

        View::render('reports/minutes', [
            'title' => 'Berita Acara & Catatan Kejadian Ujian',
            'event' => $event,
            'incidents' => $incidents,
            'writtenMinutes' => $writtenMinutes,
            'practicalMinutes' => $practicalMinutes
        ]);
    }

    public function storeIncident(Request $request): void
    {
        $event = active_event();
        $date = trim((string)$request->input('incident_date', date('Y-m-d')));
        $time = trim((string)$request->input('incident_time', date('H:i')));
        $category = trim((string)$request->input('category', 'tertulis'));
        $location = trim((string)$request->input('location', ''));
        $description = trim((string)$request->input('description', ''));
        $followUp = trim((string)$request->input('follow_up', ''));
        $reporter = trim((string)$request->input('reporter_name', Auth::user()['full_name'] ?? 'Petugas Panitia'));
        $status = trim((string)$request->input('status', 'Selesai'));

        if (empty($description)) {
            flash('error', 'Deskripsi kejadian khusus wajib diisi.');
            Response::back();
        }

        Database::insert('exam_incidents', [
            'exam_event_id' => $event['id'],
            'category' => $category,
            'incident_date' => $date,
            'incident_time' => $time,
            'location' => $location,
            'description' => $description,
            'follow_up' => $followUp,
            'reporter_name' => $reporter,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        flash('success', 'Catatan kejadian khusus ujian berhasil disimpan.');
        Response::redirect(url('/reports/minutes'));
    }

    public function deleteIncident(Request $request): void
    {
        $id = (int)$request->input('id');
        $event = active_event();
        Database::query("DELETE FROM exam_incidents WHERE id = ? AND exam_event_id = ?", [$id, $event['id']]);
        flash('success', 'Catatan kejadian khusus telah dihapus.');
        Response::redirect(url('/reports/minutes'));
    }

    /**
     * 18-Section Final Report (Laporan Akhir Panitia Ujian)
     */
    public function final(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $school = school_setting();
        $report = Database::fetch("SELECT * FROM exam_reports WHERE exam_event_id = ?", [$event['id']]);

        // Auto-seed default 18 sections if report doesn't exist yet
        if (!$report) {
            $defaultTitle = "LAPORAN AKHIR KEGIATAN PANITIA " . strtoupper($event['name']) . " TAHUN PELAJARAN " . $event['academic_year'];
            $defaultForeword = "Puji syukur kami panjatkan ke hadirat Tuhan Yang Maha Esa atas rahmat dan karunia-Nya, sehingga seluruh rangkaian kegiatan {$event['name']} Tahun Pelajaran {$event['academic_year']} di lingkungan {$school['name']} dapat terlaksana dengan baik, tertib, dan lancar.\n\nLaporan ini disusun sebagai bentuk pertanggungjawaban panitia pelaksana dalam menyelenggarakan seluruh proses administrasi dan teknis ujian.";
            $defaultBg = "Evaluasi hasil belajar merupakan bagian integral dari proses pendidikan yang bertujuan untuk mengetahui capaian kompetensi peserta didik. Dalam rangka menjamin akuntabilitas, ketertiban, dan kelancaran pelaksanaan ujian, dibentuk panitia penyelenggara yang bertugas merencanakan, melaksanakan, dan mengevaluasi kegiatan {$event['name']}.";
            $defaultLegal = "1. Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional.\n2. Peraturan Pemerintah Nomor 4 Tahun 2022 tentang Standar Nasional Pendidikan.\n3. Kalender Pendidikan {$school['name']} Tahun Pelajaran {$event['academic_year']}.\n4. Surat Keputusan Kepala Sekolah tentang Pembentukan Panitia {$event['name']}.";
            $defaultObjectives = "1. Terwujudnya administrasi ujian yang tertib, transparan, dan akuntabel.\n2. Terlaksananya ujian tertulis dan praktik secara tertib dan kondusif.\n3. Terjaminnya kerahasiaan naskah soal dan keamanan lembar jawaban ujian.\n4. Tersusunnya dokumen administrasi dan pelaporan resmi bagi pihak terkait.";
            $defaultClosing = "Demikian Laporan Akhir Pelaksanaan {$event['name']} ini kami susun dengan sebenar-benarnya. Terima kasih atas dedikasi seluruh panitia, pengawas, penguji, dan pihak sekolah yang telah bekerja sama menyukseskan kegiatan ini.";

            $reportId = Database::insert('exam_reports', [
                'exam_event_id' => $event['id'],
                'report_title' => $defaultTitle,
                'academic_year' => $event['academic_year'],
                'foreword' => $defaultForeword,
                'background' => $defaultBg,
                'legal_basis' => $defaultLegal,
                'objectives' => $defaultObjectives,
                'committee_summary' => "Kepanitiaan dibentuk berdasarkan SK Kepala Sekolah dan bekerja sesuai rincian pembagian tugas.",
                'preparation_summary' => "Persiapan meliputi penyusunan kisi-kisi, naskah soal, penggandaan, dan penataan ruang ujian.",
                'written_summary' => "Ujian tertulis berjalan tertib sesuai jadwal yang telah ditetapkan.",
                'practical_summary' => "Ujian praktik berjalan lancar pada pos dan laboratorium yang telah disiapkan.",
                'attendance_summary' => "Tingkat kehadiran peserta, panitia, dan pengawas tercatat lengkap dan memuaskan.",
                'distribution_summary' => "Distribusi dan pengembalian naskah soal terlaksana 100% tanpa kendala kebocoran.",
                'answer_sheet_summary' => "Lembar jawaban dihimpun, diverifikasi, dan dikemas rapi dalam amplop resmi.",
                'obstacles_solutions' => "Tantangan operasional dapat diatasi dengan cepat melalui koordinasi tim sekretariat.",
                'evaluation' => "Pelaksanaan ujian secara umum dinilai sangat baik dan memenuhi standar operasional.",
                'closing' => $defaultClosing,
                'approval_date' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $report = Database::fetch("SELECT * FROM exam_reports WHERE id = ?", [$reportId]);
        }

        // Live Aggregated Database Stats
        $stats = [
            'total_participants' => (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_participants WHERE exam_event_id = ?", [$event['id']]),
            'total_rooms' => (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_rooms WHERE exam_event_id = ?", [$event['id']]),
            'total_written_schedules' => (int)Database::fetchColumn("SELECT COUNT(*) FROM written_exam_schedules WHERE exam_event_id = ?", [$event['id']]),
            'total_practical_schedules' => (int)Database::fetchColumn("SELECT COUNT(*) FROM practical_exam_schedules WHERE exam_event_id = ?", [$event['id']]),
            'total_committees' => (int)Database::fetchColumn("SELECT COUNT(*) FROM committee_members cm JOIN committees c ON cm.committee_id = c.id WHERE c.exam_event_id = ?", [$event['id']]),
            'total_supervisors' => (int)Database::fetchColumn("SELECT COUNT(DISTINCT s.supervisor1_name_snapshot) FROM written_exam_supervisors s JOIN written_exam_schedules ws ON s.schedule_id = ws.id WHERE ws.exam_event_id = ?", [$event['id']]),
            'total_incidents' => (int)Database::fetchColumn("SELECT COUNT(*) FROM exam_incidents WHERE exam_event_id = ?", [$event['id']]),
            'total_answer_sheets' => (int)Database::fetchColumn("SELECT SUM(received_count) FROM answer_sheet_receipts WHERE exam_event_id = ?", [$event['id']]),
            'total_answer_discrepancy' => (int)Database::fetchColumn("SELECT SUM(discrepancy) FROM answer_sheet_receipts WHERE exam_event_id = ?", [$event['id']]),
            'total_batches' => (int)Database::fetchColumn("SELECT COUNT(*) FROM answer_sheet_packing_batches WHERE exam_event_id = ?", [$event['id']])
        ];

        View::render('reports/final', [
            'title' => 'Laporan Akhir Panitia Ujian (18 Bab)',
            'event' => $event,
            'school' => $school,
            'report' => $report,
            'stats' => $stats
        ]);
    }

    public function updateFinal(Request $request): void
    {
        $event = active_event();
        $fields = [
            'report_title' => trim((string)$request->input('report_title')),
            'academic_year' => trim((string)$request->input('academic_year')),
            'foreword' => trim((string)$request->input('foreword')),
            'background' => trim((string)$request->input('background')),
            'legal_basis' => trim((string)$request->input('legal_basis')),
            'objectives' => trim((string)$request->input('objectives')),
            'committee_summary' => trim((string)$request->input('committee_summary')),
            'preparation_summary' => trim((string)$request->input('preparation_summary')),
            'written_summary' => trim((string)$request->input('written_summary')),
            'practical_summary' => trim((string)$request->input('practical_summary')),
            'attendance_summary' => trim((string)$request->input('attendance_summary')),
            'distribution_summary' => trim((string)$request->input('distribution_summary')),
            'answer_sheet_summary' => trim((string)$request->input('answer_sheet_summary')),
            'obstacles_solutions' => trim((string)$request->input('obstacles_solutions')),
            'evaluation' => trim((string)$request->input('evaluation')),
            'closing' => trim((string)$request->input('closing')),
            'approval_date' => trim((string)$request->input('approval_date', date('Y-m-d'))),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        Database::update('exam_reports', $fields, 'exam_event_id = :event_id', ['event_id' => $event['id']]);
        flash('success', 'Seluruh bagian Laporan Akhir Panitia Ujian berhasil diperbarui.');
        Response::redirect(url('/reports/final'));
    }

    /**
     * Official Print View of the 18-Section Final Report
     */
    public function printFinal(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            Response::redirect(url('/events'));
        }

        $school = school_setting();
        $report = Database::fetch("SELECT * FROM exam_reports WHERE exam_event_id = ?", [$event['id']]);
        if (!$report) {
            flash('warning', 'Laporan akhir belum diinisialisasi.');
            Response::redirect(url('/reports/final'));
        }

        // Live stats & tables for report annexes
        $committees = Database::fetchAll("
            SELECT cm.*, cm.role_title as position, COALESCE(cm.custom_name, t.name) as name, COALESCE(cm.custom_nip, t.nip) as nip, cm.section_name as role
            FROM committee_members cm
            JOIN committees c ON cm.committee_id = c.id
            LEFT JOIN teachers t ON cm.teacher_id = t.id
            WHERE c.exam_event_id = ?
            ORDER BY cm.order_index ASC, cm.id ASC
        ", [$event['id']]);
        $schedules = Database::fetchAll("
            SELECT ws.*, s.name as subject_name, s.code as subject_code
            FROM written_exam_schedules ws
            JOIN subjects s ON ws.subject_id = s.id
            WHERE ws.exam_event_id = ?
            ORDER BY ws.exam_date ASC, ws.session ASC
        ", [$event['id']]);
        $incidents = Database::fetchAll("SELECT * FROM exam_incidents WHERE exam_event_id = ? ORDER BY incident_date ASC", [$event['id']]);
        $batches = Database::fetchAll("
            SELECT b.*, s.name as subject_name
            FROM answer_sheet_packing_batches b
            LEFT JOIN subjects s ON b.subject_id = s.id
            WHERE b.exam_event_id = ?
            ORDER BY b.id DESC
        ", [$event['id']]);

        // RAB if available
        $budgets = Database::fetchAll("
            SELECT b.* FROM committee_budgets b
            JOIN committee_work_programs wp ON b.work_program_id = wp.id
            WHERE wp.exam_event_id = ?
            ORDER BY b.id ASC
        ", [$event['id']]);

        View::layout('layouts/print');
        View::render('reports/print_final', [
            'title' => 'Cetak Laporan Akhir Panitia Ujian',
            'event' => $event,
            'school' => $school,
            'report' => $report,
            'committees' => $committees,
            'schedules' => $schedules,
            'incidents' => $incidents,
            'batches' => $batches,
            'budgets' => $budgets
        ]);
    }

    /**
     * Export Final Report to Word DOCX
     */
    public function exportFinalDocx(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            Response::redirect(url('/events'));
        }

        $school = school_setting();
        $report = Database::fetch("SELECT * FROM exam_reports WHERE exam_event_id = ?", [$event['id']]);
        if (!$report) {
            flash('error', 'Laporan akhir belum diinisialisasi.');
            Response::redirect(url('/reports/final'));
        }

        $doc = new DocxGenerator($report['report_title']);
        $doc->addTitle($school['name']);
        $doc->addHeading($report['report_title'], 1);
        $doc->addParagraph("Tahun Pelajaran " . $event['academic_year'] . " (Semester " . $event['semester'] . ")");
        $doc->addParagraph("");

        // Foreword
        $doc->addHeading("KATA PENGANTAR", 2);
        $doc->addParagraph($report['foreword'] ?? '');
        $doc->addParagraph("");

        // Bab I: Pendahuluan
        $doc->addHeading("BAB I: PENDAHULUAN", 2);
        $doc->addHeading("A. Latar Belakang", 3);
        $doc->addParagraph($report['background'] ?? '');
        $doc->addHeading("B. Dasar Hukum", 3);
        $doc->addParagraph($report['legal_basis'] ?? '');
        $doc->addHeading("C. Maksud dan Tujuan", 3);
        $doc->addParagraph($report['objectives'] ?? '');

        // Bab II: Kepanitiaan
        $committees = Database::fetchAll("
            SELECT cm.*, cm.role_title as position, COALESCE(cm.custom_name, t.name) as name
            FROM committee_members cm
            JOIN committees c ON cm.committee_id = c.id
            LEFT JOIN teachers t ON cm.teacher_id = t.id
            WHERE c.exam_event_id = ?
            ORDER BY cm.order_index ASC, cm.id ASC
        ", [$event['id']]);
        if (!empty($committees)) {
            $commRows = [];
            foreach ($committees as $i => $c) {
                $commRows[] = [$i + 1, $c['position'], $c['name']];
            }
            $doc->addTable(['No', 'Jabatan Panitia', 'Nama Lengkap'], $commRows);
        }

        // Bab III: Persiapan Ujian
        $doc->addHeading("BAB III: PERSIAPAN UJIAN", 2);
        $doc->addParagraph($report['preparation_summary'] ?? '');

        // Bab IV: Pelaksanaan Ujian Tertulis
        $doc->addHeading("BAB IV: PELAKSANAAN UJIAN TERTULIS", 2);
        $doc->addParagraph($report['written_summary'] ?? '');

        // Bab V: Pelaksanaan Ujian Praktik
        $doc->addHeading("BAB V: PELAKSANAAN UJIAN PRAKTIK", 2);
        $doc->addParagraph($report['practical_summary'] ?? '');

        // Bab VI: Kehadiran
        $doc->addHeading("BAB VI: REKAPITULASI KEHADIRAN", 2);
        $doc->addParagraph($report['attendance_summary'] ?? '');

        // Bab VII: Pengelolaan Naskah & Lembar Jawaban
        $doc->addHeading("BAB VII: PENGELOLAAN NASKAH & LEMBAR JAWABAN", 2);
        $doc->addParagraph($report['distribution_summary'] ?? '');
        $doc->addParagraph($report['answer_sheet_summary'] ?? '');

        // Bab VIII: Kendala & Solusi
        $doc->addHeading("BAB VIII: KENDALA DAN PEMECAHAN MASALAH", 2);
        $doc->addParagraph($report['obstacles_solutions'] ?? '');

        // Bab IX: Evaluasi
        $doc->addHeading("BAB IX: EVALUASI DAN TINDAK LANJUT", 2);
        $doc->addParagraph($report['evaluation'] ?? '');

        // Bab X: Penutup
        $doc->addHeading("BAB X: PENUTUP", 2);
        $doc->addParagraph($report['closing'] ?? '');

        $filename = 'Laporan_Akhir_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $event['name']) . '.docx';
        $doc->output($filename);
    }
}
