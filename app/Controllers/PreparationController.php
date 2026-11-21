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
    // 2. Document Submission Checklist (Format Rekap Matriks Sesuai Gambar & Detail Telaah)
    public function submissions(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        // Auto-sync: Ensure all active teachers and their assigned subjects from Master Guru are synchronized
        $this->autoSyncTeacherSubmissions($event);

        $matrixData = $this->buildSubmissionsMatrixData($event, $request);

        // Detailed submissions list for Log & Telaah Tab
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
        $classes = Database::fetchAll("SELECT * FROM classes ORDER BY grade ASC, name ASC");

        $unassignedTeachers = Database::fetchAll("
            SELECT * FROM teachers 
            WHERE status = 'Aktif' 
              AND id NOT IN (SELECT DISTINCT teacher_id FROM teacher_subjects)
            ORDER BY name ASC
        ");

        View::render('preparation/submissions', [
            'title' => 'Checklist Persiapan & Penerimaan Dokumen',
            'event' => $event,
            'matrixData' => $matrixData,
            'submissions' => $submissions,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'classes' => $classes,
            'typeFilter' => $typeFilter,
            'statusFilter' => $statusFilter,
            'unassignedTeachers' => $unassignedTeachers,
            'activeTab' => $request->input('tab', 'matrix')
        ]);
    }

    public function toggleCheck(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'message' => 'Kegiatan ujian tidak aktif.'], 400);
            }
            Response::redirect(url('/events'));
        }

        $teacherId = (int)$request->input('teacher_id');
        $subjectId = (int)$request->input('subject_id');
        $grade = trim((string)$request->input('grade'));
        $docType = trim((string)$request->input('doc_type'));

        // Normalize docType
        if ($docType === 'soal') {
            $docType = 'naskah_soal';
        } elseif ($docType === 'kisi') {
            $docType = 'kisi_kisi';
        } elseif ($docType === 'praktik') {
            $docType = 'lisan';
        }

        if (!$teacherId || !$subjectId || empty($grade) || empty($docType)) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'message' => 'Data tidak lengkap.'], 400);
            }
            flash('error', 'Parameter tidak lengkap.');
            Response::back();
        }

        // Check existing record
        $existing = Database::fetch(
            "SELECT * FROM exam_document_submissions WHERE exam_event_id = ? AND teacher_id = ? AND subject_id = ? AND grade = ? AND (doc_type = ? OR (doc_type IN ('lisan', 'praktik') AND ? = 'lisan'))",
            [$event['id'], $teacherId, $subjectId, $grade, $docType, $docType]
        );

        $isChecked = false;
        $newStatus = 'belum_diserahkan';

        if ($existing) {
            if (in_array($existing['status'], ['selesai', 'sudah_diserahkan'])) {
                // Toggle OFF -> uncheck (belum mengumpulkan)
                Database::update('exam_document_submissions', [
                    'status' => 'belum_diserahkan',
                    'submission_date' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = :where_id', ['where_id' => $existing['id']]);
                $isChecked = false;
                $newStatus = 'belum_diserahkan';
            } else {
                // Toggle ON -> check (sudah mengumpulkan)
                Database::update('exam_document_submissions', [
                    'status' => 'selesai',
                    'submission_date' => date('Y-m-d'),
                    'reviewer_id' => Auth::id(),
                    'reviewed_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = :where_id', ['where_id' => $existing['id']]);
                $isChecked = true;
                $newStatus = 'selesai';
            }
        } else {
            // Insert as completed (checked)
            Database::insert('exam_document_submissions', [
                'exam_event_id' => $event['id'],
                'teacher_id' => $teacherId,
                'subject_id' => $subjectId,
                'grade' => $grade,
                'doc_type' => $docType,
                'submission_date' => date('Y-m-d'),
                'status' => 'selesai',
                'reviewer_id' => Auth::id(),
                'reviewed_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $isChecked = true;
            $newStatus = 'selesai';
        }

        $docLabels = [
            'naskah_soal' => 'Naskah Soal Tulis',
            'kisi_kisi' => 'Kisi-Kisi Soal',
            'lisan' => 'Ujian Lisan/Praktik'
        ];
        $label = $docLabels[$docType] ?? 'Dokumen';
        $message = $isChecked ? "✓ {$label} berhasil ditandai sudah dikumpulkan (v)." : "{$label} ditandai belum dikumpulkan.";

        if ($request->isAjax() || $request->input('format') === 'json') {
            Response::json([
                'success' => true,
                'checked' => $isChecked,
                'status' => $newStatus,
                'doc_type' => $docType,
                'message' => $message
            ]);
        }

        flash('success', $message);
        Response::redirect(url('/preparation/submissions'));
    }

    public function batchCheck(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'message' => 'Kegiatan ujian tidak aktif.'], 400);
            }
            Response::redirect(url('/events'));
        }

        $action = trim((string)$request->input('action', ''));
        // Actions: 'check_all_soal', 'check_all_kisi', 'check_all_lisan', 'check_all_all', 'clear_all'
        
        $matrixData = $this->buildSubmissionsMatrixData($event, $request);
        $grades = $matrixData['grades'];
        $flatRows = $matrixData['flatRows'];

        $updatedCount = 0;

        if ($action === 'clear_all') {
            Database::update('exam_document_submissions', [
                'status' => 'belum_diserahkan',
                'submission_date' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'exam_event_id = :event_id', ['event_id' => $event['id']]);

            $message = 'Semua ceklis berhasil dikosongkan.';
        } else {
            foreach ($flatRows as $row) {
                $tId = (int)$row['teacher_id'];
                $sId = (int)$row['subject_id'];
                $subjGrade = trim((string)($row['subject_grade'] ?? 'Semua'));
                $subjExamType = $row['subject_exam_type'] ?? 'written';

                foreach ($grades as $g) {
                    $isApplicable = ($subjGrade === 'Semua' || $subjGrade === 'all' || $subjGrade === '' || strcasecmp($subjGrade, (string)$g) === 0);
                    if (!$isApplicable) continue;

                    $targetDocTypes = [];
                    if ($action === 'check_all_soal' || $action === 'check_all_all') {
                        $targetDocTypes[] = 'naskah_soal';
                    }
                    if ($action === 'check_all_kisi' || $action === 'check_all_all') {
                        $targetDocTypes[] = 'kisi_kisi';
                    }
                    if ($action === 'check_all_lisan' || ($action === 'check_all_all' && $matrixData['hasLisan'])) {
                        $targetDocTypes[] = 'lisan';
                    }

                    foreach ($targetDocTypes as $docType) {
                        $existing = Database::fetch(
                            "SELECT id FROM exam_document_submissions WHERE exam_event_id = ? AND teacher_id = ? AND subject_id = ? AND grade = ? AND doc_type = ?",
                            [$event['id'], $tId, $sId, $g, $docType]
                        );

                        if ($existing) {
                            Database::update('exam_document_submissions', [
                                'status' => 'selesai',
                                'submission_date' => date('Y-m-d'),
                                'reviewer_id' => Auth::id(),
                                'reviewed_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ], 'id = :id', ['id' => $existing['id']]);
                        } else {
                            Database::insert('exam_document_submissions', [
                                'exam_event_id' => $event['id'],
                                'teacher_id' => $tId,
                                'subject_id' => $sId,
                                'grade' => $g,
                                'doc_type' => $docType,
                                'submission_date' => date('Y-m-d'),
                                'status' => 'selesai',
                                'reviewer_id' => Auth::id(),
                                'reviewed_at' => date('Y-m-d H:i:s'),
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                        $updatedCount++;
                    }
                }
            }

            $actionLabels = [
                'check_all_soal' => 'Semua naskah soal berhasil diceklis (✓)',
                'check_all_kisi' => 'Semua kisi-kisi soal berhasil diceklis (✓)',
                'check_all_lisan' => 'Semua instrumen lisan berhasil diceklis (✓)',
                'check_all_all' => 'Semua checklist dokumen berhasil diceklis lengkap (✓)',
            ];
            $message = $actionLabels[$action] ?? "Checklist berhasil diperbarui ({$updatedCount} entri).";
        }

        if ($request->isAjax() || $request->input('format') === 'json') {
            Response::json([
                'success' => true,
                'message' => $message,
                'count' => $updatedCount
            ]);
        }

        flash('success', $message);
        Response::redirect(url('/preparation/submissions'));
    }

    public function syncTeachersAndSubjects(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $count = $this->autoSyncTeacherSubmissions($event);

        flash('success', "Sinkronisasi berhasil! Seluruh data guru aktif dan mata pelajaran yang diampu telah disinkronkan ke dalam format ceklis ({$count} entri checklist diperiksa/disiapkan).");
        Response::redirect(url('/preparation/submissions'));
    }

    public function autoSyncTeacherSubmissions(array $event): int
    {
        // 1. Resolve distinct grades from classes and submissions
        $submissionGrades = Database::fetchAll("
            SELECT DISTINCT grade FROM exam_document_submissions 
            WHERE exam_event_id = ? AND grade IS NOT NULL AND grade != ''
        ", [$event['id']]);

        $classGrades = Database::fetchAll("
            SELECT DISTINCT grade FROM classes 
            WHERE grade IS NOT NULL AND grade != ''
        ");

        $gradesMap = [];
        foreach ($submissionGrades as $sg) {
            if (!empty($sg['grade'])) $gradesMap[$sg['grade']] = true;
        }
        foreach ($classGrades as $cg) {
            if (!empty($cg['grade'])) $gradesMap[$cg['grade']] = true;
        }
        $gradeList = array_keys($gradesMap);
        if (empty($gradeList)) {
            $gradeList = ['1', '2'];
        } else {
            natsort($gradeList);
            $gradeList = array_values($gradeList);
        }

        // 2. Fetch known teacher-subject pairings from teacher_subjects
        $knownPairs = Database::fetchAll("
            SELECT DISTINCT ts.teacher_id, ts.subject_id, s.grade as subject_grade, s.exam_type as subject_exam_type
            FROM teacher_subjects ts
            JOIN teachers t ON ts.teacher_id = t.id
            JOIN subjects s ON ts.subject_id = s.id
            WHERE t.status = 'Aktif'
        ");

        if (empty($knownPairs)) {
            $knownPairs = Database::fetchAll("
                SELECT DISTINCT eds.teacher_id, eds.subject_id, s.grade as subject_grade, s.exam_type as subject_exam_type
                FROM exam_document_submissions eds
                JOIN teachers t ON eds.teacher_id = t.id
                JOIN subjects s ON eds.subject_id = s.id
                WHERE t.status = 'Aktif'
            ");
        }

        $count = 0;
        if (!empty($knownPairs)) {
            foreach ($knownPairs as $pair) {
                $tId = (int)$pair['teacher_id'];
                $sId = (int)$pair['subject_id'];
                $subjGrade = trim((string)($pair['subject_grade'] ?? 'Semua'));

                foreach ($gradeList as $g) {
                    $isApplicable = ($subjGrade === 'Semua' || $subjGrade === 'all' || $subjGrade === '' || strcasecmp($subjGrade, (string)$g) === 0);
                    if (!$isApplicable) continue;

                    $types = ['naskah_soal', 'kisi_kisi', 'lisan'];

                    foreach ($types as $docType) {
                        $exists = Database::fetchColumn(
                            "SELECT COUNT(*) FROM exam_document_submissions WHERE exam_event_id = ? AND teacher_id = ? AND subject_id = ? AND grade = ? AND doc_type = ?",
                            [$event['id'], $tId, $sId, $g, $docType]
                        );

                        if (!$exists) {
                            Database::insert('exam_document_submissions', [
                                'exam_event_id' => $event['id'],
                                'teacher_id' => $tId,
                                'subject_id' => $sId,
                                'grade' => $g,
                                'doc_type' => $docType,
                                'status' => 'belum_diserahkan',
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                            $count++;
                        }
                    }
                }
            }
        }

        return $count;
    }

    public function addMatrixRow(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $teacherId = (int)$request->input('teacher_id');
        $subjectId = (int)$request->input('subject_id');

        if (!$teacherId || !$subjectId) {
            flash('error', 'Pilih Guru dan Mata Pelajaran yang valid.');
            Response::back();
        }

        $subject = Database::fetch("SELECT * FROM subjects WHERE id = ?", [$subjectId]);
        if (!$subject) {
            flash('error', 'Mata pelajaran tidak ditemukan.');
            Response::back();
        }

        // Also persist to teacher_subjects pivot table so relationship is preserved across all menus
        $existsTS = Database::fetchColumn("SELECT COUNT(*) FROM teacher_subjects WHERE teacher_id = ? AND subject_id = ?", [$teacherId, $subjectId]);
        if (!$existsTS) {
            Database::insert('teacher_subjects', [
                'teacher_id' => $teacherId,
                'subject_id' => $subjectId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Get grades
        $grades = Database::fetchAll("SELECT DISTINCT grade FROM classes WHERE grade IS NOT NULL AND grade != '' ORDER BY grade ASC");
        $gradeList = array_column($grades, 'grade');
        if (empty($gradeList)) {
            $gradeList = ['1', '2'];
        }

        $subjGrade = trim((string)($subject['grade'] ?? 'Semua'));
        foreach ($gradeList as $g) {
            $isApplicable = ($subjGrade === 'Semua' || $subjGrade === 'all' || $subjGrade === '' || strcasecmp($subjGrade, (string)$g) === 0);
            if (!$isApplicable) continue;

            $types = ['naskah_soal', 'kisi_kisi', 'lisan'];

            foreach ($types as $docType) {
                $exists = Database::fetchColumn(
                    "SELECT COUNT(*) FROM exam_document_submissions WHERE exam_event_id = ? AND teacher_id = ? AND subject_id = ? AND grade = ? AND doc_type = ?",
                    [$event['id'], $teacherId, $subjectId, $g, $docType]
                );

                if (!$exists) {
                    Database::insert('exam_document_submissions', [
                        'exam_event_id' => $event['id'],
                        'teacher_id' => $teacherId,
                        'subject_id' => $subjectId,
                        'grade' => $g,
                        'doc_type' => $docType,
                        'status' => 'belum_diserahkan',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        flash('success', 'Guru dan mata pelajaran berhasil ditambahkan ke format ceklis.');
        Response::redirect(url('/preparation/submissions'));
    }

    public function deleteMatrixRow(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian aktif terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $teacherId = (int)$request->input('teacher_id');
        $subjectId = (int)$request->input('subject_id');

        if ($teacherId && $subjectId) {
            Database::delete(
                'exam_document_submissions',
                'exam_event_id = ? AND teacher_id = ? AND subject_id = ?',
                [$event['id'], $teacherId, $subjectId]
            );
            Database::delete(
                'teacher_subjects',
                'teacher_id = ? AND subject_id = ?',
                [$teacherId, $subjectId]
            );
            flash('success', 'Baris guru dan mata pelajaran berhasil dihapus dari format ceklis.');
        }

        Response::redirect(url('/preparation/submissions'));
    }

    public function printSubmissions(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            die("Pilih kegiatan ujian aktif terlebih dahulu.");
        }

        $mode = (string)$request->input('mode', 'filled');
        $school = school_setting();
        $matrixData = $this->buildSubmissionsMatrixData($event, $request);

        // Fetch committee chair
        $chair = Database::fetch("
            SELECT cm.*, t.name as teacher_name, t.nip as teacher_nip
            FROM committee_members cm
            LEFT JOIN teachers t ON cm.teacher_id = t.id
            JOIN committees c ON cm.committee_id = c.id
            WHERE c.exam_event_id = ? AND (cm.role_title LIKE '%Ketua%' OR cm.role_title = 'Ketua Panitia')
            ORDER BY cm.order_index ASC LIMIT 1
        ", [$event['id']]);

        View::render('preparation/print_submissions', [
            'title' => 'Format Ceklis Dokumen Soal & Kisi-Kisi - ' . $event['name'],
            'event' => $event,
            'school' => $school,
            'matrixData' => $matrixData,
            'chair' => $chair,
            'mode' => $mode
        ], 'layouts/print');
    }

    public function exportSubmissionsMatrix(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            die("Pilih kegiatan ujian aktif terlebih dahulu.");
        }

        $school = school_setting();
        $matrixData = $this->buildSubmissionsMatrixData($event, $request);

        $filename = 'Format_Ceklis_Dokumen_Soal_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $event['name']) . '_' . date('Ymd') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        echo '<style>
            table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 11pt; }
            th, td { border: 1px solid #000000; padding: 6px 8px; text-align: center; }
            th { background-color: #e2e8f0; font-weight: bold; }
            .shaded { background-color: #94a3b8; }
            .text-left { text-align: left; }
            .title-box { text-align: center; margin-bottom: 20px; }
            .title { font-size: 14pt; font-weight: bold; }
            .subtitle { font-size: 11pt; }
        </style></head><body>';

        echo '<div class="title-box">';
        echo '<div class="title">FORMAT CEKLIS PENERIMAAN DOKUMEN SOAL &amp; KISI-KISI</div>';
        echo '<div class="subtitle">' . htmlspecialchars($school['name'], ENT_QUOTES) . ' - ' . htmlspecialchars($event['name'], ENT_QUOTES) . '</div>';
        echo '</div>';

        $grades = $matrixData['grades'];
        $hasLisan = $matrixData['hasLisan'];
        $kelasColspan = ($hasLisan ? count($grades) : 0) + (count($grades) * 2);

        echo '<table>';
        echo '<thead>';
        // Header Row 1
        echo '<tr>';
        echo '<th rowspan="3" style="width: 40px;">NO</th>';
        echo '<th rowspan="3" style="width: 260px;">NAMA PENDIDIK/GURU</th>';
        echo '<th rowspan="3" style="width: 220px;">MATA PELAJARAN</th>';
        echo '<th colspan="' . $kelasColspan . '">KELAS</th>';
        echo '<th colspan="3">JUMLAH</th>';
        echo '</tr>';

        // Header Row 2
        echo '<tr>';
        if ($hasLisan) {
            echo '<th colspan="' . count($grades) . '">LISAN</th>';
        }
        foreach ($grades as $g) {
            echo '<th colspan="2">' . htmlspecialchars((string)$g, ENT_QUOTES) . '</th>';
        }
        echo '<th rowspan="2">LISAN</th>';
        echo '<th colspan="2">TULIS</th>';
        echo '</tr>';

        // Header Row 3
        echo '<tr>';
        if ($hasLisan) {
            foreach ($grades as $g) {
                echo '<th>' . htmlspecialchars((string)$g, ENT_QUOTES) . '</th>';
            }
        }
        foreach ($grades as $g) {
            echo '<th>SOAL</th>';
            echo '<th>KISI-KISI</th>';
        }
        echo '<th>SOAL</th>';
        echo '<th>KISI-KISI</th>';
        echo '</tr>';
        echo '</thead><tbody>';

        foreach ($matrixData['flatRows'] as $row) {
            echo '<tr>';
            echo '<td>' . $row['row_no'] . '</td>';
            if ($row['is_first_subject']) {
                echo '<td rowspan="' . $row['subject_count'] . '" class="text-left" style="font-weight: 600;">' . htmlspecialchars($row['teacher_name'], ENT_QUOTES) . '</td>';
            }
            echo '<td class="text-left">' . htmlspecialchars($row['subject_name'], ENT_QUOTES) . '</td>';

            // Lisan cells
            if ($hasLisan) {
                foreach ($grades as $g) {
                    $cell = $row['lisan_cells'][$g];
                    if ($cell['shaded']) {
                        echo '<td class="shaded">&nbsp;</td>';
                    } else {
                        echo '<td>' . ($cell['checked'] ? 'v' : '') . '</td>';
                    }
                }
            }

            // Tulis Soal & Kisi cells
            foreach ($grades as $g) {
                $cellSoal = $row['tulis_soal_cells'][$g];
                if ($cellSoal['shaded']) {
                    echo '<td class="shaded">&nbsp;</td>';
                } else {
                    echo '<td>' . ($cellSoal['checked'] ? 'v' : '') . '</td>';
                }

                $cellKisi = $row['tulis_kisi_cells'][$g];
                if ($cellKisi['shaded']) {
                    echo '<td class="shaded">&nbsp;</td>';
                } else {
                    echo '<td>' . ($cellKisi['checked'] ? 'v' : '') . '</td>';
                }
            }

            // Totals
            echo '<td>' . $row['row_totals']['lisan'] . '</td>';
            echo '<td>' . $row['row_totals']['soal'] . '</td>';
            echo '<td>' . $row['row_totals']['kisi'] . '</td>';
            echo '</tr>';
        }

        // Footer Row
        $colTotals = $matrixData['columnTotals'];
        echo '<tfoot><tr>';
        echo '<th colspan="3" style="text-align: right; padding-right: 15px;">JUMLAH</th>';
        if ($hasLisan) {
            foreach ($grades as $g) {
                echo '<th>' . ($colTotals['lisan'][$g] ?? 0) . '</th>';
            }
        }
        foreach ($grades as $g) {
            echo '<th>' . ($colTotals['tulis_soal'][$g] ?? 0) . '</th>';
            echo '<th>' . ($colTotals['tulis_kisi'][$g] ?? 0) . '</th>';
        }
        echo '<th>' . $colTotals['grand_lisan'] . '</th>';
        echo '<th>' . $colTotals['grand_soal'] . '</th>';
        echo '<th>' . $colTotals['grand_kisi'] . '</th>';
        echo '</tr></tfoot>';

        echo '</table></body></html>';
        exit;
    }

    private function buildSubmissionsMatrixData(array $event, Request $request): array
    {
        $school = school_setting();

        // 1. Resolve Distinct Grades
        $submissionGrades = Database::fetchAll("
            SELECT DISTINCT grade FROM exam_document_submissions 
            WHERE exam_event_id = ? AND grade IS NOT NULL AND grade != ''
        ", [$event['id']]);

        $classGrades = Database::fetchAll("
            SELECT DISTINCT grade FROM classes 
            WHERE grade IS NOT NULL AND grade != ''
        ");

        $gradesMap = [];
        foreach ($submissionGrades as $sg) {
            if (!empty($sg['grade'])) $gradesMap[$sg['grade']] = true;
        }
        foreach ($classGrades as $cg) {
            if (!empty($cg['grade'])) $gradesMap[$cg['grade']] = true;
        }
        $grades = array_keys($gradesMap);
        if (empty($grades)) {
            $grades = ['1', '2'];
        } else {
            natsort($grades);
            $grades = array_values($grades);
        }

        // 2. Exam Types Filtering / Inclusions
        $typeFilter = (string)$request->input('exam_type', 'all');
        $showLisan = ($typeFilter === 'all' || $typeFilter === 'practical' || $typeFilter === 'lisan');
        $showTulis = ($typeFilter === 'all' || $typeFilter === 'written' || $typeFilter === 'tulis');

        // 3. Retrieve Teacher + Subject Pairs (Union of this event submissions and active teacher-subject assignments)
        $pairs = Database::fetchAll("
            SELECT DISTINCT t.id as teacher_id, t.name as teacher_name, t.nip as teacher_nip,
                            s.id as subject_id, s.name as subject_name, s.code as subject_code,
                            s.grade as subject_grade, s.exam_type as subject_exam_type
            FROM (
                SELECT teacher_id, subject_id FROM exam_document_submissions WHERE exam_event_id = ?
                UNION
                SELECT ts.teacher_id, ts.subject_id FROM teacher_subjects ts 
                JOIN teachers t2 ON ts.teacher_id = t2.id WHERE t2.status = 'Aktif'
            ) combined
            JOIN teachers t ON combined.teacher_id = t.id
            JOIN subjects s ON combined.subject_id = s.id
            ORDER BY t.name ASC, s.code ASC, s.name ASC
        ", [$event['id']]);

        if (empty($pairs)) {
            // Check if there are teacher-subject pairs from any prior event in the school
            $previousPairs = Database::fetchAll("
                SELECT DISTINCT t.id as teacher_id, t.name as teacher_name, t.nip as teacher_nip,
                                s.id as subject_id, s.name as subject_name, s.code as subject_code,
                                s.grade as subject_grade, s.exam_type as subject_exam_type
                FROM exam_document_submissions eds
                JOIN teachers t ON eds.teacher_id = t.id
                JOIN subjects s ON eds.subject_id = s.id
                ORDER BY t.name ASC, s.code ASC, s.name ASC
            ");

            if (!empty($previousPairs)) {
                $pairs = $previousPairs;
            }
        }

        // 4. Load all Submissions into lookup map
        $allSubs = Database::fetchAll("
            SELECT * FROM exam_document_submissions 
            WHERE exam_event_id = ?
        ", [$event['id']]);

        $subMap = [];
        foreach ($allSubs as $s) {
            $key = $s['teacher_id'] . '_' . $s['subject_id'] . '_' . $s['grade'] . '_' . $s['doc_type'];
            $subMap[$key] = $s;
        }

        // 5. Group by Teacher
        $teacherGroups = [];
        foreach ($pairs as $p) {
            $tId = (int)$p['teacher_id'];
            if (!isset($teacherGroups[$tId])) {
                $teacherGroups[$tId] = [
                    'teacher_id' => $tId,
                    'teacher_name' => $p['teacher_name'],
                    'teacher_nip' => $p['teacher_nip'],
                    'subjects' => []
                ];
            }
            $teacherGroups[$tId]['subjects'][] = $p;
        }

        // 6. Build Flat Rows and Calculate Totals
        $flatRows = [];
        $rowNo = 1;

        $colTotals = [
            'lisan' => array_fill_keys($grades, 0),
            'tulis_soal' => array_fill_keys($grades, 0),
            'tulis_kisi' => array_fill_keys($grades, 0),
            'grand_lisan' => 0,
            'grand_soal' => 0,
            'grand_kisi' => 0
        ];

        foreach ($teacherGroups as $group) {
            $subjCount = count($group['subjects']);
            foreach ($group['subjects'] as $sIndex => $subj) {
                $tId = (int)$subj['teacher_id'];
                $sId = (int)$subj['subject_id'];
                $subjGrade = trim((string)($subj['subject_grade'] ?? 'Semua'));

                $rowLisan = 0;
                $rowSoal = 0;
                $rowKisi = 0;

                $lisanCells = [];
                $tulisSoalCells = [];
                $tulisKisiCells = [];

                foreach ($grades as $g) {
                    $isApplicable = ($subjGrade === 'Semua' || $subjGrade === 'all' || $subjGrade === '' || strcasecmp($subjGrade, (string)$g) === 0);

                    // Lisan
                    if ($showLisan) {
                        if (!$isApplicable) {
                            $lisanCells[$g] = [
                                'applicable' => false,
                                'shaded' => true,
                                'checked' => false,
                                'status' => 'not_applicable',
                                'submission_id' => null
                            ];
                        } else {
                            $keyLisan = "{$tId}_{$sId}_{$g}_lisan";
                            $keyPraktik = "{$tId}_{$sId}_{$g}_praktik";
                            $subRec = $subMap[$keyLisan] ?? ($subMap[$keyPraktik] ?? null);
                            $isChecked = $subRec && in_array($subRec['status'], ['selesai', 'sudah_diserahkan']);
                            if ($isChecked) {
                                $rowLisan++;
                                $colTotals['lisan'][$g]++;
                            }
                            $lisanCells[$g] = [
                                'applicable' => true,
                                'shaded' => false,
                                'checked' => $isChecked,
                                'status' => $subRec['status'] ?? 'belum_diserahkan',
                                'submission_id' => $subRec['id'] ?? null
                            ];
                        }
                    }

                    // Tulis - Soal
                    if ($showTulis) {
                        if (!$isApplicable) {
                            $tulisSoalCells[$g] = [
                                'applicable' => false,
                                'shaded' => true,
                                'checked' => false,
                                'status' => 'not_applicable',
                                'submission_id' => null
                            ];
                        } else {
                            $keySoal = "{$tId}_{$sId}_{$g}_naskah_soal";
                            $subRec = $subMap[$keySoal] ?? null;
                            $isChecked = $subRec && in_array($subRec['status'], ['selesai', 'sudah_diserahkan']);
                            if ($isChecked) {
                                $rowSoal++;
                                $colTotals['tulis_soal'][$g]++;
                            }
                            $tulisSoalCells[$g] = [
                                'applicable' => true,
                                'shaded' => false,
                                'checked' => $isChecked,
                                'status' => $subRec['status'] ?? 'belum_diserahkan',
                                'submission_id' => $subRec['id'] ?? null
                            ];
                        }

                        // Tulis - Kisi-kisi
                        if (!$isApplicable) {
                            $tulisKisiCells[$g] = [
                                'applicable' => false,
                                'shaded' => true,
                                'checked' => false,
                                'status' => 'not_applicable',
                                'submission_id' => null
                            ];
                        } else {
                            $keyKisi = "{$tId}_{$sId}_{$g}_kisi_kisi";
                            $subRec = $subMap[$keyKisi] ?? null;
                            $isChecked = $subRec && in_array($subRec['status'], ['selesai', 'sudah_diserahkan']);
                            if ($isChecked) {
                                $rowKisi++;
                                $colTotals['tulis_kisi'][$g]++;
                            }
                            $tulisKisiCells[$g] = [
                                'applicable' => true,
                                'shaded' => false,
                                'checked' => $isChecked,
                                'status' => $subRec['status'] ?? 'belum_diserahkan',
                                'submission_id' => $subRec['id'] ?? null
                            ];
                        }
                    }
                }

                $colTotals['grand_lisan'] += $rowLisan;
                $colTotals['grand_soal'] += $rowSoal;
                $colTotals['grand_kisi'] += $rowKisi;

                $flatRows[] = [
                    'row_no' => $rowNo++,
                    'teacher_id' => $tId,
                    'teacher_name' => $group['teacher_name'],
                    'teacher_nip' => $group['teacher_nip'],
                    'is_first_subject' => ($sIndex === 0),
                    'subject_count' => $subjCount,
                    'subject_id' => $sId,
                    'subject_name' => $subj['subject_name'],
                    'subject_code' => $subj['subject_code'],
                    'subject_grade' => $subjGrade,
                    'lisan_cells' => $lisanCells,
                    'tulis_soal_cells' => $tulisSoalCells,
                    'tulis_kisi_cells' => $tulisKisiCells,
                    'row_totals' => [
                        'lisan' => $rowLisan,
                        'soal' => $rowSoal,
                        'kisi' => $rowKisi
                    ]
                ];
            }
        }

        return [
            'event' => $event,
            'school' => $school,
            'grades' => $grades,
            'hasLisan' => $showLisan,
            'hasTulis' => $showTulis,
            'flatRows' => $flatRows,
            'teacherGroups' => $teacherGroups,
            'columnTotals' => $colTotals
        ];
    }

    public function storeSubmission(Request $request): void
    {
        $event = active_event();
        $teacherId = (int)$request->input('teacher_id');
        $subjectId = (int)$request->input('subject_id');
        $grade = (string)$request->input('grade', '1');
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
        Response::redirect(url('/preparation/submissions?tab=details'));
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
        Response::redirect(url('/preparation/submissions?tab=details'));
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
