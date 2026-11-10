<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class PreparationController
{
    // 1. Exam Rules & SOPs
    public function rules(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $rules = Database::fetchAll("SELECT * FROM exam_rules WHERE exam_event_id = ? ORDER BY id ASC", [$event['id']]);

        // Seed default rules if empty
        if (empty($rules)) {
            $defaultRules = [
                [
                    'tata_tertib_peserta',
                    'Tata Tertib Peserta Ujian',
                    "1. Peserta ujian memasuki ruangan setelah tanda masuk dibunyikan, yakni 15 (lima belas) menit sebelum ujian dimulai.\n2. Peserta dilarang membawa catatan, buku, ponsel, atau gawai elektronik ke dalam ruang ujian kecuali alat tulis yang diizinkan.\n3. Peserta wajib membawa Kartu Peserta Ujian dan menaruhnya di sudut kanan atas meja.\n4. Peserta yang terlambat hanya boleh mengikuti ujian setelah mendapat izin dari Ketua Panitia tanpa perpanjangan waktu.\n5. Selama ujian berlangsung, peserta hanya dapat meninggalkan ruangan dengan izin pengawas dan tidak boleh keluar ruangan dalam 30 menit pertama.\n6. Peserta yang melanggar tata tertib akan diberi peringatan atau dinyatakan tidak sah pekerjaannya."
                ],
                [
                    'tata_tertib_pengawas',
                    'Tata Tertib Pengawas Ruang Ujian',
                    "1. Pengawas ruang hadir di ruang panitia 30 menit sebelum ujian dimulai untuk menerima naskah soal dan administrasi ruang.\n2. Pengawas menandatangani daftar pengambilan naskah soal dan memeriksa kelengkapan isi amplop naskah.\n3. Pengawas masuk ruang 15 menit sebelum bel berbunyi, memeriksa kartu peserta dan tempat duduk.\n4. Pengawas membacakan tata tertib dan membagikan naskah soal dalam keadaan rahasia dan utuh.\n5. Pengawas dilarang merokok, membaca buku, mengoperasikan ponsel, atau memberikan jawaban soal kepada peserta.\n6. Setelah waktu selesai, pengawas mengumpulkan dan menghitung lembar jawaban, mengisi berita acara, serta menyerahkannya ke panitia."
                ],
                [
                    'tata_tertib_penguji',
                    'Tata Tertib Penguji Ujian Praktik',
                    "1. Penguji hadir di lokasi stasiun praktik 30 menit sebelum jadwal dimulai.\n2. Memeriksa kesiapan alat, bahan, dan tempat uji kecakapan praktik.\n3. Mengatur rotasi kelompok peserta sesuai pembagian stasiun.\n4. Menjaga ketertiban, keamanan, dan keselamatan kerja (K3) selama ujian praktik berlangsung.\n5. Mengisi daftar hadir penguji dan berita acara pelaksanaan praktik."
                ],
                [
                    'juknis',
                    'Petunjuk Teknis (Juknis) Pelaksanaan Ujian',
                    "Petunjuk teknis ini memuat tata cara pembagian ruang, penempatan tempat duduk bersilang antartingkat/kelas asal, prosedur pembagian amplop soal gabungan per ruang, dan penanganan kondisi darurat/kejadian khusus."
                ],
                [
                    'sop_distribusi',
                    'Standar Operasional Prosedur Distribusi Naskah Soal',
                    "1. Amplop naskah soal disusun per ruang ujian sesuai jadwal dan sesi.\n2. Satu ruang ujian mendapatkan SATU AMPLOP UTAMA terpadu berlabel resmi.\n3. Pengawas mengambil amplop di ruang panitia maksimal 20 menit sebelum sesi dimulai.\n4. Petugas panitia dan pengawas mencocokkan jumlah naskah utama dan cadangan lalu menandatangani formulir pengambilan naskah."
                ],
                [
                    'sop_pengumpulan',
                    'Standar Operasional Prosedur Pengumpulan Lembar Jawaban',
                    "1. Pengawas ruang menyerahkan lembar jawaban dan naskah sisa kepada petugas panitia penerima.\n2. Petugas penerima menghitung fisik lembar jawaban di hadapan pengawas, mencatat jumlah hadir, lembar rusak, dan lembar sisa.\n3. Petugas mencatat status tanda terima (Lengkap / Ada Selisih) dan memaraf formulir penerimaan."
                ],
                [
                    'sop_susulan',
                    'Prosedur Pelaksanaan Ujian Susulan',
                    "1. Ujian susulan hanya diperuntukkan bagi peserta yang berhalangan hadir dengan alasan sah (sakit dengan surat dokter atau izin resmi keluarga).\n2. Peserta susulan didata dalam Buku Kejadian Khusus / Peserta Susulan.\n3. Ujian susulan diselenggarakan sesuai jadwal susulan yang ditetapkan panitia."
                ],
            ];

            foreach ($defaultRules as $r) {
                Database::insert('exam_rules', [
                    'exam_event_id' => $event['id'],
                    'rule_type' => $r[0],
                    'title' => $r[1],
                    'content' => $r[2],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            $rules = Database::fetchAll("SELECT * FROM exam_rules WHERE exam_event_id = ? ORDER BY id ASC", [$event['id']]);
        }

        View::render('preparation/rules', [
            'title' => 'Tata Tertib, Juknis & Prosedur Ujian',
            'event' => $event,
            'rules' => $rules
        ]);
    }

    public function storeRule(Request $request): void
    {
        $event = active_event();
        $type = (string)$request->input('rule_type', 'dokumen_pendukung');
        $title = trim((string)$request->input('title'));
        $content = trim((string)$request->input('content'));

        if (empty($title)) {
            flash('error', 'Judul peraturan wajib diisi.');
            Response::back();
        }

        Database::insert('exam_rules', [
            'exam_event_id' => $event['id'],
            'rule_type' => $type,
            'title' => $title,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        flash('success', 'Dokumen peraturan/juknis berhasil ditambahkan.');
        Response::redirect(url('/preparation/rules'));
    }

    public function updateRule(Request $request): void
    {
        $id = (int)$request->input('id');
        $title = trim((string)$request->input('title'));
        $content = trim((string)$request->input('content'));

        Database::update('exam_rules', [
            'title' => $title,
            'content' => $content,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = :where_id', ['where_id' => $id]);

        flash('success', 'Dokumen peraturan/juknis berhasil diperbarui.');
        Response::redirect(url('/preparation/rules'));
    }

    public function deleteRule(Request $request): void
    {
        $id = (int)$request->input('id');
        Database::delete('exam_rules', 'id = ?', [$id]);
        flash('success', 'Dokumen peraturan/juknis berhasil dihapus.');
        Response::redirect(url('/preparation/rules'));
    }

    public function printRule(Request $request): void
    {
        $id = (int)$request->input('id');
        $rule = Database::fetch("SELECT * FROM exam_rules WHERE id = ?", [$id]);
        if (!$rule) die("Dokumen tidak ditemukan.");

        $school = school_setting();
        $event = Database::fetch("SELECT * FROM exam_events WHERE id = ?", [$rule['exam_event_id']]);

        View::render('preparation/print_rule', [
            'title' => $rule['title'],
            'rule' => $rule,
            'school' => $school,
            'event' => $event
        ], 'layouts/print');
    }

    // 2. Document Submission Checklist (Kisi-kisi, Soal, Kunci Jawaban, Dokumen Nilai Guru)
    public function submissions(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $typeFilter = (string)$request->input('type', '');
        $statusFilter = (string)$request->input('status', '');

        $sql = "SELECT eds.*, t.name as teacher_name, t.nip as teacher_nip, s.name as subject_name, s.code as subject_code, u.full_name as reviewer_name
                FROM exam_document_submissions eds
                JOIN teachers t ON eds.teacher_id = t.id
                JOIN subjects s ON eds.subject_id = s.id
                LEFT JOIN users u ON eds.reviewer_id = u.id
                WHERE eds.exam_event_id = ?";
        $params = [$event['id']];

        if (!empty($typeFilter)) {
            $sql .= " AND eds.doc_type = ?";
            $params[] = $typeFilter;
        }

        if (!empty($statusFilter)) {
            $sql .= " AND eds.status = ?";
            $params[] = $statusFilter;
        }

        $sql .= " ORDER BY s.code ASC, eds.grade ASC, eds.doc_type ASC";
        $submissions = Database::fetchAll($sql, $params);

        $teachers = Database::fetchAll("SELECT * FROM teachers WHERE status = 'Aktif' ORDER BY name ASC");
        $subjects = Database::fetchAll("SELECT * FROM subjects ORDER BY code ASC");

        View::render('preparation/submissions', [
            'title' => 'Checklist Persiapan & Penerimaan Dokumen',
            'event' => $event,
            'submissions' => $submissions,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'typeFilter' => $typeFilter,
            'statusFilter' => $statusFilter
        ]);
    }

    public function storeSubmission(Request $request): void
    {
        $event = active_event();
        $teacherId = (int)$request->input('teacher_id');
        $subjectId = (int)$request->input('subject_id');
        $grade = (string)$request->input('grade', 'VII');
        $docType = (string)$request->input('doc_type', 'naskah_soal');
        $submissionDate = $request->input('submission_date') ?: date('Y-m-d');
        $status = (string)$request->input('status', 'belum_diserahkan');
        $notes = trim((string)$request->input('notes', ''));

        Database::insert('exam_document_submissions', [
            'exam_event_id' => $event['id'],
            'teacher_id' => $teacherId,
            'subject_id' => $subjectId,
            'grade' => $grade,
            'doc_type' => $docType,
            'submission_date' => $status !== 'belum_diserahkan' ? $submissionDate : null,
            'status' => $status,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        flash('success', 'Entri checklist dokumen berhasil ditambahkan.');
        Response::redirect(url('/preparation/submissions'));
    }

    public function updateSubmissionStatus(Request $request): void
    {
        $id = (int)$request->input('id');
        $status = (string)$request->input('status');
        $reviewNotes = trim((string)$request->input('review_notes', ''));
        $notes = trim((string)$request->input('notes', ''));

        $data = [
            'status' => $status,
            'notes' => $notes,
            'review_notes' => $reviewNotes ?: null,
            'reviewer_id' => Auth::id(),
            'reviewed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($status !== 'belum_diserahkan' && empty($request->input('submission_date'))) {
            $data['submission_date'] = date('Y-m-d');
        }

        Database::update('exam_document_submissions', $data, 'id = :where_id', ['where_id' => $id]);
        flash('success', 'Status checklist dokumen berhasil diperbarui.');
        Response::redirect(url('/preparation/submissions'));
    }

    public function updateSubmission(Request $request): void
    {
        $this->updateSubmissionStatus($request);
    }

    public function deleteSubmission(Request $request): void
    {
        $id = (int)$request->input('id');
        Database::delete('exam_document_submissions', 'id = ?', [$id]);
        flash('success', 'Catatan checklist dokumen berhasil dihapus.');
        Response::redirect(url('/preparation/submissions'));
    }

    // 3. Question Printing Records & Replication Preparation
    public function printing(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $records = Database::fetchAll("
            SELECT epr.*, s.name as subject_name, s.code as subject_code
            FROM exam_printing_records epr
            JOIN subjects s ON epr.subject_id = s.id
            WHERE epr.exam_event_id = ?
            ORDER BY epr.grade ASC, s.code ASC
        ", [$event['id']]);

        $subjects = Database::fetchAll("SELECT * FROM subjects WHERE exam_type IN ('written', 'both') ORDER BY code ASC");

        View::render('preparation/printing', [
            'title' => 'Kesiapan Penggandaan Naskah Soal',
            'event' => $event,
            'records' => $records,
            'subjects' => $subjects
        ]);
    }

    public function storePrinting(Request $request): void
    {
        $event = active_event();
        $subjectId = (int)$request->input('subject_id');
        $grade = (string)$request->input('grade', 'VII');
        $reserveCopies = (int)$request->input('reserve_copies', 2);
        $printedCopies = (int)$request->input('printed_copies', 0);
        $picName = trim((string)$request->input('pic_name', ''));
        $printDate = $request->input('print_date') ?: date('Y-m-d');
        $notes = trim((string)$request->input('notes', ''));

        // Calculate actual participants for this grade in this event
        $partCount = (int)Database::fetchColumn(
            "SELECT COUNT(*) FROM exam_participants WHERE exam_event_id = ? AND grade_snapshot = ?",
            [$event['id'], $grade]
        );

        $mainCopies = $partCount;
        $totalCopies = $mainCopies + $reserveCopies;
        $unprinted = max(0, $totalCopies - $printedCopies);
        $status = $printedCopies >= $totalCopies ? 'Selesai Dicetak' : ($printedCopies > 0 ? 'Sedang Dicetak' : 'Belum Dicetak');

        Database::insert('exam_printing_records', [
            'exam_event_id' => $event['id'],
            'subject_id' => $subjectId,
            'grade' => $grade,
            'participant_count' => $partCount,
            'main_copies' => $mainCopies,
            'reserve_copies' => $reserveCopies,
            'total_copies' => $totalCopies,
            'printed_copies' => $printedCopies,
            'unprinted_copies' => $unprinted,
            'status' => $status,
            'pic_name' => $picName,
            'print_date' => $printDate,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        flash('success', 'Catatan kebutuhan penggandaan naskah soal berhasil disimpan.');
        Response::redirect(url('/preparation/printing'));
    }

    public function updatePrinting(Request $request): void
    {
        $id = (int)$request->input('id');
        $printedCopies = (int)$request->input('printed_copies', 0);
        $reserveCopies = (int)$request->input('reserve_copies', 2);
        $status = (string)$request->input('status');
        $picName = trim((string)$request->input('pic_name', ''));
        $printDate = (string)$request->input('print_date');
        $notes = trim((string)$request->input('notes', ''));

        $record = Database::fetch("SELECT * FROM exam_printing_records WHERE id = ?", [$id]);
        if (!$record) {
            flash('error', 'Catatan tidak ditemukan.');
            Response::back();
        }

        $total = $record['main_copies'] + $reserveCopies;
        $unprinted = max(0, $total - $printedCopies);

        Database::update('exam_printing_records', [
            'reserve_copies' => $reserveCopies,
            'total_copies' => $total,
            'printed_copies' => $printedCopies,
            'unprinted_copies' => $unprinted,
            'status' => $status,
            'pic_name' => $picName,
            'print_date' => $printDate,
            'notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = :where_id', ['where_id' => $id]);

        flash('success', 'Data penggandaan naskah berhasil diperbarui.');
        Response::redirect(url('/preparation/printing'));
    }

    public function deletePrinting(Request $request): void
    {
        $id = (int)$request->input('id');
        Database::delete('exam_printing_records', 'id = ?', [$id]);
        flash('success', 'Catatan penggandaan naskah berhasil dihapus.');
        Response::redirect(url('/preparation/printing'));
    }
}
