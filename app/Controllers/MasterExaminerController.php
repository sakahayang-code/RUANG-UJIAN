<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\ExcelExport;

class MasterExaminerController
{
    public function index(Request $request): void
    {
        $event = active_event();
        $search = trim((string)$request->input('q', ''));
        $subjectFilter = trim((string)$request->input('subject', ''));
        $roleFilter = trim((string)$request->input('role', ''));

        $sql = "SELECT * FROM master_examiners WHERE 1=1";
        $params = [];

        if ($event) {
            $sql .= " AND (exam_event_id = ? OR exam_event_id IS NULL)";
            $params[] = $event['id'];
        }

        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR nip LIKE ? OR subject_name LIKE ? OR school_origin LIKE ? OR location LIKE ? OR phone LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if (!empty($subjectFilter)) {
            $sql .= " AND subject_name = ?";
            $params[] = $subjectFilter;
        }

        if (!empty($roleFilter)) {
            $sql .= " AND examiner_role = ?";
            $params[] = $roleFilter;
        }

        $sql .= " ORDER BY subject_name ASC, examiner_role ASC, name ASC";
        $examiners = Database::fetchAll($sql, $params);

        // Fetch teachers for quick select
        $teachers = Database::fetchAll("SELECT id, name, nip, phone, position FROM teachers WHERE status = 'Aktif' ORDER BY name ASC");

        // Fetch subjects (especially practical ones) for dropdown
        $subjects = Database::fetchAll("SELECT DISTINCT name, code FROM subjects ORDER BY name ASC");

        // Distinct subjects & roles from master_examiners for filter tabs/options
        $subjectOptions = Database::fetchAll("SELECT DISTINCT subject_name FROM master_examiners WHERE subject_name IS NOT NULL AND subject_name != '' ORDER BY subject_name ASC");
        $roleOptions = Database::fetchAll("SELECT DISTINCT examiner_role FROM master_examiners WHERE examiner_role IS NOT NULL AND examiner_role != '' ORDER BY examiner_role ASC");

        View::render('master/examiners', [
            'title' => 'Data Penguji Praktik',
            'event' => $event,
            'examiners' => $examiners,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'subjectOptions' => array_column($subjectOptions, 'subject_name'),
            'roleOptions' => array_column($roleOptions, 'examiner_role'),
            'search' => $search,
            'subjectFilter' => $subjectFilter,
            'roleFilter' => $roleFilter,
        ]);
    }

    public function store(Request $request): void
    {
        $event = active_event();
        $name = trim((string)$request->input('name'));
        $nip = trim((string)$request->input('nip'));
        $subjectName = trim((string)$request->input('subject_name'));
        $grade = trim((string)$request->input('grade', 'Semua Tingkat'));
        $schoolOrigin = trim((string)$request->input('school_origin', 'Internal Sekolah'));
        $examinerRole = trim((string)$request->input('examiner_role', 'Penguji Utama'));
        $phone = trim((string)$request->input('phone'));
        $location = trim((string)$request->input('location'));
        $status = trim((string)$request->input('status', 'Aktif'));
        $notes = trim((string)$request->input('notes'));

        if (empty($name)) {
            flash('error', 'Nama penguji praktik wajib diisi.');
            Response::back();
        }

        if (empty($subjectName)) {
            flash('error', 'Mata pelajaran praktik yang diuji wajib diisi.');
            Response::back();
        }

        $id = Database::insert('master_examiners', [
            'exam_event_id' => $event['id'] ?? null,
            'name' => $name,
            'nip' => $nip ?: null,
            'subject_name' => $subjectName,
            'grade' => $grade ?: 'Semua Tingkat',
            'school_origin' => $schoolOrigin ?: 'Internal Sekolah',
            'examiner_role' => $examinerRole ?: 'Penguji Utama',
            'phone' => $phone ?: null,
            'location' => $location ?: null,
            'status' => $status ?: 'Aktif',
            'notes' => $notes ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        Auth::logAudit('CREATE', 'master_examiners', $id, null, ['name' => $name, 'subject' => $subjectName]);
        flash('success', "Penguji praktik \"{$name}\" ({$subjectName}) berhasil ditambahkan.");
        Response::redirect(url('/master/examiners'));
    }

    public function update(Request $request): void
    {
        $id = (int)$request->input('id');
        $examiner = Database::fetch("SELECT * FROM master_examiners WHERE id = ?", [$id]);

        if (!$examiner) {
            flash('error', 'Data penguji praktik tidak ditemukan.');
            Response::back();
        }

        $name = trim((string)$request->input('name'));
        $nip = trim((string)$request->input('nip'));
        $subjectName = trim((string)$request->input('subject_name'));
        $grade = trim((string)$request->input('grade', 'Semua Tingkat'));
        $schoolOrigin = trim((string)$request->input('school_origin', 'Internal Sekolah'));
        $examinerRole = trim((string)$request->input('examiner_role', 'Penguji Utama'));
        $phone = trim((string)$request->input('phone'));
        $location = trim((string)$request->input('location'));
        $status = trim((string)$request->input('status', 'Aktif'));
        $notes = trim((string)$request->input('notes'));

        if (empty($name)) {
            flash('error', 'Nama penguji praktik wajib diisi.');
            Response::back();
        }

        if (empty($subjectName)) {
            flash('error', 'Mata pelajaran praktik yang diuji wajib diisi.');
            Response::back();
        }

        $data = [
            'name' => $name,
            'nip' => $nip ?: null,
            'subject_name' => $subjectName,
            'grade' => $grade ?: 'Semua Tingkat',
            'school_origin' => $schoolOrigin ?: 'Internal Sekolah',
            'examiner_role' => $examinerRole ?: 'Penguji Utama',
            'phone' => $phone ?: null,
            'location' => $location ?: null,
            'status' => $status ?: 'Aktif',
            'notes' => $notes ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        Database::update('master_examiners', $data, 'id = :where_id', ['where_id' => $id]);
        Auth::logAudit('UPDATE', 'master_examiners', $id, $examiner, $data);

        flash('success', "Data penguji praktik \"{$name}\" berhasil diperbarui.");
        Response::redirect(url('/master/examiners'));
    }

    public function delete(Request $request): void
    {
        $id = (int)$request->input('id');
        $examiner = Database::fetch("SELECT * FROM master_examiners WHERE id = ?", [$id]);

        if (!$examiner) {
            flash('error', 'Data penguji praktik tidak ditemukan.');
            Response::back();
        }

        Database::delete('master_examiners', 'id = ?', [$id]);
        Auth::logAudit('DELETE', 'master_examiners', $id, $examiner, null);

        flash('success', "Data penguji praktik \"{$examiner['name']}\" berhasil dihapus.");
        Response::redirect(url('/master/examiners'));
    }

    public function deleteAll(Request $request): void
    {
        $event = active_event();
        $sql = "DELETE FROM master_examiners";
        $params = [];

        if ($event) {
            $sql .= " WHERE exam_event_id = ? OR exam_event_id IS NULL";
            $params[] = $event['id'];
        }

        Database::query($sql, $params);
        Auth::logAudit('DELETE', 'master_examiners', 0, null, ['action' => 'delete_all']);

        flash('success', 'Seluruh data penguji praktik berhasil dikosongkan.');
        Response::redirect(url('/master/examiners'));
    }

    public function export(Request $request): void
    {
        $event = active_event();
        $sql = "SELECT * FROM master_examiners";
        $params = [];

        if ($event) {
            $sql .= " WHERE exam_event_id = ? OR exam_event_id IS NULL";
            $params[] = $event['id'];
        }
        $sql .= " ORDER BY subject_name ASC, examiner_role ASC, name ASC";

        $examiners = Database::fetchAll($sql, $params);

        $headers = [
            'No',
            'Nama Guru Penguji',
            'NIP / NUPTK',
            'Mata Pelajaran Praktik',
            'Tingkat / Kelas',
            'Peran Penguji',
            'Asal Sekolah / Lembaga',
            'Lokasi / Laboratorium',
            'No. HP / WhatsApp',
            'Status',
            'Keterangan'
        ];

        $rows = [];
        foreach ($examiners as $idx => $ex) {
            $rows[] = [
                $idx + 1,
                $ex['name'],
                $ex['nip'] ?: '-',
                $ex['subject_name'],
                $ex['grade'] ?: 'Semua Tingkat',
                $ex['examiner_role'] ?: 'Penguji Utama',
                $ex['school_origin'] ?: 'Internal Sekolah',
                $ex['location'] ?: '-',
                $ex['phone'] ?: '-',
                $ex['status'] ?: 'Aktif',
                $ex['notes'] ?: '-'
            ];
        }

        $title = 'DATA PENGUJI UJIAN PRAKTIK - ' . ($event['name'] ?? 'SEKOLAH');
        ExcelExport::export('master_penguji_praktik_' . date('Ymd_His'), $title, $headers, $rows);
    }

    public function template(Request $request): void
    {
        $headers = [
            'Nama Penguji',
            'NIP',
            'Mata Pelajaran',
            'Tingkat Kelas',
            'Peran Penguji',
            'Asal Sekolah',
            'Lokasi Ujian',
            'No HP',
            'Status',
            'Keterangan'
        ];

        $sampleRows = [
            ['Dede Ali Maulana, S.Pd.', '198701022011011002', 'Ilmu Pengetahuan Alam', 'IX', 'Penguji Utama', 'SMP Negeri 1 Bojong', 'Laboratorium IPA', '081298765401', 'Aktif', 'Materi uji praktikum kalor & listrik'],
            ['Nurul Fauziah Solihah, S.Pd.', '198904082014022001', 'Ilmu Pengetahuan Alam', 'IX', 'Penguji Pendamping', 'SMP Negeri 1 Bojong', 'Laboratorium IPA', '081298765402', 'Aktif', ''],
            ['Dikdik Permana, S.Th.I.', '198106182007011003', 'Pendidikan Agama Islam', 'IX', 'Penguji Utama', 'SMP Negeri 1 Bojong', 'Musholla Sekolah', '081298765403', 'Aktif', 'Uji baca Al-Quran & Shalat'],
            ['Fiqhi Zamil, S.IP.', '199112252019031004', 'PJOK', 'IX', 'Penguji Utama', 'SMP Negeri 1 Bojong', 'Lapangan Olahraga', '081298765404', 'Aktif', 'Uji kebugaran jasmani & senam'],
            ['Asep Ridwan, ST.', '198011152005011004', 'Informatika & Prakarya', 'IX', 'Penguji Utama', 'SMP Negeri 1 Bojong', 'Laboratorium Komputer', '081298765405', 'Aktif', 'Uji olah data & desain'],
        ];

        ExcelExport::exportXlsx('template_import_penguji_praktik', 'TEMPLATE IMPORT PENGUJI PRAKTIK', $headers, $sampleRows);
    }

    public function import(Request $request): void
    {
        $event = active_event();

        if (!$request->hasFile('file')) {
            flash('error', 'Silakan pilih berkas Excel (.xlsx / .xls) atau CSV untuk diimpor.');
            Response::back();
        }

        $file = $request->file('file');
        $rows = ExcelExport::parseSpreadsheet($file['tmp_name'], $file['name'] ?? null);

        if (empty($rows)) {
            flash('error', 'Berkas Excel/CSV kosong atau format header tidak sesuai.');
            Response::back();
        }

        $success = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $name = trim($row['nama_penguji'] ?? $row['nama'] ?? $row['nama_guru'] ?? $row['penguji'] ?? '');
            if (!$name) {
                continue;
            }

            $subjectName = trim($row['mata_pelajaran'] ?? $row['mapel'] ?? $row['mata_pelajaran_praktik'] ?? $row['mapel_uji'] ?? '');
            if (!$subjectName) {
                $subjectName = 'Ujian Praktik Umum';
            }

            $nip = trim($row['nip'] ?? $row['nuptk'] ?? '');
            $grade = trim($row['tingkat_kelas'] ?? $row['tingkat'] ?? $row['kelas'] ?? 'Semua Tingkat');
            $role = trim($row['peran_penguji'] ?? $row['peran'] ?? $row['jabatan'] ?? 'Penguji Utama');
            $schoolOrigin = trim($row['asal_sekolah'] ?? $row['sekolah'] ?? $row['instansi'] ?? 'Internal Sekolah');
            $location = trim($row['lokasi_ujian'] ?? $row['lokasi'] ?? $row['tempat'] ?? $row['laboratorium'] ?? '');
            $phone = trim($row['no_hp'] ?? $row['hp'] ?? $row['telepon'] ?? $row['whatsapp'] ?? '');
            $status = trim($row['status'] ?? 'Aktif');
            $notes = trim($row['keterangan'] ?? $row['catatan'] ?? '');

            // Check if examiner already exists by name and subject in this event
            $existing = Database::fetch("
                SELECT id FROM master_examiners 
                WHERE LOWER(name) = LOWER(?) AND LOWER(subject_name) = LOWER(?) AND (exam_event_id = ? OR exam_event_id IS NULL)
            ", [$name, $subjectName, $event['id'] ?? 0]);

            if ($existing) {
                Database::update('master_examiners', [
                    'name' => $name,
                    'nip' => $nip ?: null,
                    'subject_name' => $subjectName,
                    'grade' => $grade ?: 'Semua Tingkat',
                    'examiner_role' => $role ?: 'Penguji Utama',
                    'school_origin' => $schoolOrigin ?: 'Internal Sekolah',
                    'location' => $location ?: null,
                    'phone' => $phone ?: null,
                    'status' => $status ?: 'Aktif',
                    'notes' => $notes ?: null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = :where_id', ['where_id' => $existing['id']]);
                $updated++;
            } else {
                Database::insert('master_examiners', [
                    'exam_event_id' => $event['id'] ?? null,
                    'name' => $name,
                    'nip' => $nip ?: null,
                    'subject_name' => $subjectName,
                    'grade' => $grade ?: 'Semua Tingkat',
                    'examiner_role' => $role ?: 'Penguji Utama',
                    'school_origin' => $schoolOrigin ?: 'Internal Sekolah',
                    'location' => $location ?: null,
                    'phone' => $phone ?: null,
                    'status' => $status ?: 'Aktif',
                    'notes' => $notes ?: null,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $success++;
            }
        }

        $msg = "Impor data penguji praktik selesai: {$success} penguji baru ditambahkan.";
        if ($updated > 0) {
            $msg .= " ({$updated} data penguji diperbarui).";
        }

        flash('success', $msg);
        Response::redirect(url('/master/examiners'));
    }
}
