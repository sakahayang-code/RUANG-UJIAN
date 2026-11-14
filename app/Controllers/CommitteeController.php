<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\DocxGenerator;

class CommitteeController
{
    // 1. Structure and SK Panitia Management
    public function index(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Silakan pilih atau buat kegiatan ujian terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $committee = Database::fetch("SELECT * FROM committees WHERE exam_event_id = ?", [$event['id']]);
        if (!$committee) {
            $cId = Database::insert('committees', [
                'exam_event_id' => $event['id'],
                'title' => 'Panitia ' . $event['name'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $committee = Database::fetch("SELECT * FROM committees WHERE id = ?", [$cId]);
        }

        $members = Database::fetchAll("
            SELECT cm.*, t.name as teacher_name, t.nip as teacher_nip, t.phone as teacher_phone
            FROM committee_members cm
            LEFT JOIN teachers t ON cm.teacher_id = t.id
            WHERE cm.committee_id = ?
            ORDER BY cm.order_index ASC, cm.id ASC
        ", [$committee['id']]);

        $documents = Database::fetchAll("
            SELECT * FROM committee_documents
            WHERE exam_event_id = ?
            ORDER BY id DESC
        ", [$event['id']]);

        $teachers = Database::fetchAll("SELECT * FROM teachers WHERE status = 'Aktif' ORDER BY name ASC");

        View::render('committee/index', [
            'title' => 'Struktur Kepanitiaan & SK Panitia',
            'event' => $event,
            'committee' => $committee,
            'members' => $members,
            'documents' => $documents,
            'teachers' => $teachers
        ]);
    }

    public function storeMember(Request $request): void
    {
        $committeeId = (int)$request->input('committee_id');
        $teacherId = $request->input('teacher_id') ? (int)$request->input('teacher_id') : null;
        $customName = trim((string)$request->input('custom_name'));
        $customNip = trim((string)$request->input('custom_nip'));
        $roleTitle = (string)$request->input('role_title', 'Anggota');
        $sectionName = trim((string)$request->input('section_name', ''));
        $phone = trim((string)$request->input('phone', ''));
        $orderIndex = (int)$request->input('order_index', 10);

        if (!$teacherId && empty($customName)) {
            flash('error', 'Silakan pilih guru atau tuliskan nama anggota.');
            Response::back();
        }

        Database::insert('committee_members', [
            'committee_id' => $committeeId,
            'teacher_id' => $teacherId,
            'custom_name' => $customName ?: null,
            'custom_nip' => $customNip ?: null,
            'role_title' => $roleTitle,
            'section_name' => $sectionName ?: null,
            'phone' => $phone ?: null,
            'order_index' => $orderIndex,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        flash('success', 'Anggota panitia berhasil ditambahkan.');
        Response::redirect(url('/committee'));
    }

    public function updateMember(Request $request): void
    {
        $id = (int)$request->input('id');
        $member = Database::fetch("SELECT * FROM committee_members WHERE id = ?", [$id]);
        if (!$member) {
            flash('error', 'Data anggota panitia tidak ditemukan.');
            Response::back();
        }

        $teacherId = $request->input('teacher_id') ? (int)$request->input('teacher_id') : null;
        $roleTitle = trim((string)$request->input('role_title'));
        $sectionName = trim((string)$request->input('section_name', ''));
        $phone = trim((string)$request->input('phone', ''));
        $orderIndex = (int)$request->input('order_index', 0);

        if (empty($roleTitle)) {
            flash('error', 'Jabatan kepanitiaan wajib diisi.');
            Response::back();
        }

        $nameSnapshot = $member['name_snapshot'];
        $nipSnapshot = $member['nip_snapshot'];
        if ($teacherId) {
            $t = Database::fetch("SELECT name, nip_nuptk, phone FROM teachers WHERE id = ?", [$teacherId]);
            if ($t) {
                $nameSnapshot = $t['name'];
                $nipSnapshot = $t['nip_nuptk'];
                if (empty($phone)) $phone = $t['phone'];
            }
        } elseif ($request->input('name')) {
            $nameSnapshot = trim((string)$request->input('name'));
            $nipSnapshot = trim((string)$request->input('nip', ''));
        }

        Database::update('committee_members', [
            'teacher_id' => $teacherId,
            'name_snapshot' => $nameSnapshot,
            'nip_snapshot' => $nipSnapshot,
            'role_title' => $roleTitle,
            'section_name' => $sectionName ?: null,
            'phone' => $phone ?: null,
            'order_index' => $orderIndex
        ], 'id = :where_id', ['where_id' => $id]);

        flash('success', 'Data anggota panitia berhasil diperbarui.');
        Response::redirect(url('/committee'));
    }

    public function deleteMember(Request $request): void
    {
        $id = (int)$request->input('id');
        Database::delete('committee_members', 'id = ?', [$id]);
        flash('success', 'Anggota panitia berhasil dihapus.');
        Response::redirect(url('/committee'));
    }

    public function uploadSk(Request $request): void
    {
        $event = active_event();
        $docNumber = trim((string)$request->input('doc_number'));
        $docDate = (string)$request->input('doc_date');
        $title = trim((string)$request->input('title'));
        $description = trim((string)$request->input('description', ''));

        if (empty($title) || empty($docNumber) || !$request->hasFile('file')) {
            flash('error', 'Nomor SK, judul, dan berkas PDF wajib diisi.');
            Response::back();
        }

        $file = $request->file('file');
        $mime = mime_content_type($file['tmp_name']);
        if ($mime !== 'application/pdf') {
            flash('error', 'Berkas harus berupa dokumen format PDF.');
            Response::back();
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'sk_' . $event['id'] . '_' . time() . '.' . $ext;
        $targetDir = dirname(__DIR__, 2) . '/storage/uploads/sk_panitia';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

        if (move_uploaded_file($file['tmp_name'], $targetDir . '/' . $fileName)) {
            $relPath = 'sk_panitia/' . $fileName;

            // Check if existing SK document exists to handle versioning
            $existing = Database::fetch("SELECT * FROM committee_documents WHERE exam_event_id = ? AND doc_type = 'sk_panitia'", [$event['id']]);

            if ($existing) {
                $newVersion = ((int)$existing['version']) + 1;
                Database::insert('committee_document_versions', [
                    'document_id' => $existing['id'],
                    'version' => $existing['version'],
                    'file_path' => $existing['file_path'],
                    'uploaded_by' => Auth::id(),
                    'notes' => 'Versi lama sebelum pembaruan ke versi ' . $newVersion,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                Database::update('committee_documents', [
                    'doc_number' => $docNumber,
                    'doc_date' => $docDate,
                    'title' => $title,
                    'description' => $description,
                    'file_path' => $relPath,
                    'file_size' => $file['size'],
                    'version' => $newVersion,
                    'status' => 'Sah'
                ], 'id = :where_id', ['where_id' => $existing['id']]);
            } else {
                Database::insert('committee_documents', [
                    'exam_event_id' => $event['id'],
                    'doc_type' => 'sk_panitia',
                    'doc_number' => $docNumber,
                    'doc_date' => $docDate,
                    'title' => $title,
                    'description' => $description,
                    'file_path' => $relPath,
                    'file_size' => $file['size'],
                    'version' => 1,
                    'status' => 'Sah',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            flash('success', 'Dokumen SK Panitia berhasil diunggah dan diverifikasi.');
        } else {
            flash('error', 'Gagal mengunggah berkas SK Panitia.');
        }

        Response::redirect(url('/committee'));
    }

    // 2. Letters & Invitations Generator
    public function letters(Request $request): void
    {
        $event = active_event();
        if (!$event) {
            flash('warning', 'Pilih kegiatan ujian terlebih dahulu.');
            Response::redirect(url('/events'));
        }

        $letters = Database::fetchAll("
            SELECT * FROM committee_letters
            WHERE exam_event_id = ?
            ORDER BY id DESC
        ", [$event['id']]);

        $templates = Database::fetchAll("SELECT * FROM letter_templates ORDER BY name ASC");
        $school = school_setting();

        // Calculate auto letter number
        $monthRoman = to_roman((int)date('n'));
        $year = date('Y');
        $nextNo = sprintf("%03d", count($letters) + 1);
        $autoNumber = str_replace(
            ['{no}', '{bln_romawi}', '{bln}', '{thn}'],
            [$nextNo, $monthRoman, date('m'), $year],
            $school['letter_number_format']
        );

        View::render('committee/letters', [
            'title' => 'Surat Undangan & Surat Tugas',
            'event' => $event,
            'letters' => $letters,
            'templates' => $templates,
            'school' => $school,
            'autoNumber' => $autoNumber
        ]);
    }

    public function storeLetter(Request $request): void
    {
        $event = active_event();
        $letterType = (string)$request->input('letter_type', 'undangan_rapat');
        $letterNumber = trim((string)$request->input('letter_number'));
        $letterDate = (string)$request->input('letter_date');
        $subject = trim((string)$request->input('subject'));
        $meetingDate = $request->input('meeting_date') ?: null;
        $meetingTime = $request->input('meeting_time') ?: null;
        $meetingPlace = $request->input('meeting_place') ?: null;
        $meetingAgenda = $request->input('meeting_agenda') ?: null;
        $signerName = trim((string)$request->input('signer_name'));
        $signerNip = trim((string)$request->input('signer_nip'));
        $signerTitle = trim((string)$request->input('signer_title'));
        $targetRecipients = trim((string)$request->input('target_recipients', ''));
        $content = trim((string)$request->input('content', ''));

        if (empty($letterNumber) || empty($subject) || empty($content)) {
            flash('error', 'Nomor surat, perihal, dan isi surat wajib diisi.');
            Response::back();
        }

        $id = Database::insert('committee_letters', [
            'exam_event_id' => $event['id'],
            'letter_type' => $letterType,
            'letter_number' => $letterNumber,
            'letter_date' => $letterDate,
            'subject' => $subject,
            'meeting_date' => $meetingDate,
            'meeting_time' => $meetingTime,
            'meeting_place' => $meetingPlace,
            'meeting_agenda' => $meetingAgenda,
            'signer_name' => $signerName,
            'signer_nip' => $signerNip,
            'signer_title' => $signerTitle,
            'target_recipients' => $targetRecipients,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        flash('success', 'Surat berhasil dibuat.');
        Response::redirect(url('/committee/letters'));
    }

    public function updateLetter(Request $request): void
    {
        $id = (int)$request->input('id');
        $letter = Database::fetch("SELECT * FROM committee_letters WHERE id = ?", [$id]);
        if (!$letter) {
            flash('error', 'Surat tidak ditemukan.');
            Response::back();
        }

        $letterType = (string)$request->input('letter_type', 'undangan_rapat');
        $letterNumber = trim((string)$request->input('letter_number'));
        $letterDate = (string)$request->input('letter_date');
        $subject = trim((string)$request->input('subject'));
        $meetingDate = $request->input('meeting_date') ?: null;
        $meetingTime = $request->input('meeting_time') ?: null;
        $meetingPlace = $request->input('meeting_place') ?: null;
        $meetingAgenda = $request->input('meeting_agenda') ?: null;
        $signerName = trim((string)$request->input('signer_name'));
        $signerNip = trim((string)$request->input('signer_nip'));
        $signerTitle = trim((string)$request->input('signer_title'));
        $targetRecipients = trim((string)$request->input('target_recipients', ''));
        $content = trim((string)$request->input('content', ''));

        if (empty($letterNumber) || empty($subject) || empty($content)) {
            flash('error', 'Nomor surat, perihal, dan isi surat wajib diisi.');
            Response::back();
        }

        Database::update('committee_letters', [
            'letter_type' => $letterType,
            'letter_number' => $letterNumber,
            'letter_date' => $letterDate,
            'subject' => $subject,
            'meeting_date' => $meetingDate,
            'meeting_time' => $meetingTime,
            'meeting_place' => $meetingPlace,
            'meeting_agenda' => $meetingAgenda,
            'signer_name' => $signerName,
            'signer_nip' => $signerNip,
            'signer_title' => $signerTitle,
            'target_recipients' => $targetRecipients,
            'content' => $content,
        ], 'id = ?', [$id]);

        flash('success', 'Perubahan surat berhasil disimpan.');
        Response::redirect(url('/committee/letters'));
    }

    public function printLetter(Request $request): void
    {
        $id = (int)$request->input('id');
        $letter = Database::fetch("SELECT * FROM committee_letters WHERE id = ?", [$id]);
        if (!$letter) {
            die("Surat tidak ditemukan.");
        }

        $school = school_setting();
        $event = Database::fetch("SELECT * FROM exam_events WHERE id = ?", [$letter['exam_event_id']]);

        View::render('committee/print_letter', [
            'title' => $letter['subject'],
            'letter' => $letter,
            'school' => $school,
            'event' => $event
        ], 'layouts/print');
    }

    public function exportDocxLetter(Request $request): void
    {
        $id = (int)$request->input('id');
        $letter = Database::fetch("SELECT * FROM committee_letters WHERE id = ?", [$id]);
        if (!$letter) {
            die("Surat tidak ditemukan.");
        }

        $school = school_setting();
        $docx = new DocxGenerator($letter['subject']);

        $docx->addTitle($school['name']);
        $docx->addParagraph($school['address'] . ' - Telp: ' . $school['phone'], ['align' => 'center', 'italic' => true]);
        $docx->addParagraph(str_repeat('―', 50), ['align' => 'center']);

        $docx->addParagraph("Nomor : " . $letter['letter_number']);
        $docx->addParagraph("Lampiran : -");
        $docx->addParagraph("Perihal : " . $letter['subject'], ['bold' => true]);
        $docx->addParagraph("Tanggal : " . format_date_id($letter['letter_date']));

        $docx->addParagraph("Kepada Yth,\n" . ($letter['target_recipients'] ?: 'Bapak/Ibu Panitia / Guru'), ['bold' => true]);

        $docx->addParagraph($letter['content']);

        if (!empty($letter['meeting_date'])) {
            $docx->addParagraph("Hari/Tanggal : " . format_day_id($letter['meeting_date']) . ", " . format_date_id($letter['meeting_date']));
            $docx->addParagraph("Waktu : " . $letter['meeting_time'] . " WIB");
            $docx->addParagraph("Tempat : " . $letter['meeting_place']);
            $docx->addParagraph("Agenda : " . $letter['meeting_agenda']);
        }

        $docx->addParagraph("\n\n" . $school['signature_city'] . ", " . format_date_id($letter['letter_date']), ['align' => 'right']);
        $docx->addParagraph($letter['signer_title'] . ",\n\n\n\n" . $letter['signer_name'] . "\nNIP. " . ($letter['signer_nip'] ?: '-'), ['align' => 'right', 'bold' => true]);

        $filename = 'surat_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $letter['subject']) . '.docx';
        $docx->output($filename);
    }

    public function exportLetterDocx(Request $request): void
    {
        $this->exportDocxLetter($request);
    }

    public function deleteLetter(Request $request): void
    {
        $id = (int)$request->input('id');
        Database::delete('committee_letters', 'id = ?', [$id]);
        flash('success', 'Surat berhasil dihapus.');
        Response::redirect(url('/committee/letters'));
    }
}
