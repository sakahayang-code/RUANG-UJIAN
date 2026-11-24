<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\ExcelExport;

class MasterSupervisorController
{
    public function index(Request $request): void
    {
        $event = active_event();
        $search = trim((string)$request->input('q', ''));
        $roleFilter = trim((string)$request->input('role', ''));
        $roomFilter = trim((string)$request->input('room', ''));

        $sql = "SELECT * FROM master_supervisors WHERE 1=1";
        $params = [];

        if ($event) {
            $sql .= " AND (exam_event_id = ? OR exam_event_id IS NULL)";
            $params[] = $event['id'];
        }

        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR nip LIKE ? OR school_origin LIKE ? OR subject LIKE ? OR phone LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if (!empty($roleFilter)) {
            $sql .= " AND role = ?";
            $params[] = $roleFilter;
        }

        if (!empty($roomFilter)) {
            $sql .= " AND room_assignment = ?";
            $params[] = $roomFilter;
        }

        $sql .= " ORDER BY room_assignment ASC, name ASC";
        $supervisors = Database::fetchAll($sql, $params);

        // Fetch teachers for quick select
        $teachers = Database::fetchAll("SELECT id, name, nip, phone, position FROM teachers WHERE status = 'Aktif' ORDER BY name ASC");
        
        // Fetch rooms for quick room select
        $rooms = Database::fetchAll("SELECT * FROM exam_rooms ORDER BY room_number ASC");

        // Unique roles and rooms for filter options
        $rolesList = Database::fetchAll("SELECT DISTINCT role FROM master_supervisors WHERE role IS NOT NULL AND role != '' ORDER BY role ASC");
        $roomsList = Database::fetchAll("SELECT DISTINCT room_assignment FROM master_supervisors WHERE room_assignment IS NOT NULL AND room_assignment != '' ORDER BY room_assignment ASC");

        View::render('master/supervisors', [
            'title' => 'Data Pengawas Ruang',
            'event' => $event,
            'supervisors' => $supervisors,
            'teachers' => $teachers,
            'rooms' => $rooms,
            'rolesList' => array_column($rolesList, 'role'),
            'roomsList' => array_column($roomsList, 'room_assignment'),
            'search' => $search,
            'roleFilter' => $roleFilter,
            'roomFilter' => $roomFilter,
        ]);
    }

    public function store(Request $request): void
    {
        $event = active_event();
        $name = trim((string)$request->input('name'));
        $nip = trim((string)$request->input('nip'));
        $schoolOrigin = trim((string)$request->input('school_origin', 'Internal Sekolah'));
        $subject = trim((string)$request->input('subject'));
        $phone = trim((string)$request->input('phone'));
        $roomAssignment = trim((string)$request->input('room_assignment'));
        $role = trim((string)$request->input('role', 'Pengawas Ruang'));
        $status = trim((string)$request->input('status', 'Aktif'));
        $notes = trim((string)$request->input('notes'));

        if (empty($name)) {
            flash('error', 'Nama pengawas ruang wajib diisi.');
            Response::back();
        }

        $id = Database::insert('master_supervisors', [
            'exam_event_id' => $event['id'] ?? null,
            'name' => $name,
            'nip' => $nip ?: null,
            'school_origin' => $schoolOrigin ?: 'Internal Sekolah',
            'subject' => $subject ?: null,
            'phone' => $phone ?: null,
            'room_assignment' => $roomAssignment ?: null,
            'role' => $role ?: 'Pengawas Ruang',
            'status' => $status ?: 'Aktif',
            'notes' => $notes ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        Auth::logAudit('CREATE', 'master_supervisors', $id, null, ['name' => $name, 'role' => $role]);
        flash('success', "Pengawas ruang \"{$name}\" berhasil ditambahkan.");
        Response::redirect(url('/master/supervisors'));
    }

    public function update(Request $request): void
    {
        $id = (int)$request->input('id');
        $supervisor = Database::fetch("SELECT * FROM master_supervisors WHERE id = ?", [$id]);

        if (!$supervisor) {
            flash('error', 'Data pengawas tidak ditemukan.');
            Response::back();
        }

        $name = trim((string)$request->input('name'));
        $nip = trim((string)$request->input('nip'));
        $schoolOrigin = trim((string)$request->input('school_origin', 'Internal Sekolah'));
        $subject = trim((string)$request->input('subject'));
        $phone = trim((string)$request->input('phone'));
        $roomAssignment = trim((string)$request->input('room_assignment'));
        $role = trim((string)$request->input('role', 'Pengawas Ruang'));
        $status = trim((string)$request->input('status', 'Aktif'));
        $notes = trim((string)$request->input('notes'));

        if (empty($name)) {
            flash('error', 'Nama pengawas ruang wajib diisi.');
            Response::back();
        }

        $data = [
            'name' => $name,
            'nip' => $nip ?: null,
            'school_origin' => $schoolOrigin ?: 'Internal Sekolah',
            'subject' => $subject ?: null,
            'phone' => $phone ?: null,
            'room_assignment' => $roomAssignment ?: null,
            'role' => $role ?: 'Pengawas Ruang',
            'status' => $status ?: 'Aktif',
            'notes' => $notes ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        Database::update('master_supervisors', $data, 'id = :where_id', ['where_id' => $id]);
        Auth::logAudit('UPDATE', 'master_supervisors', $id, $supervisor, $data);

        flash('success', "Data pengawas \"{$name}\" berhasil diperbarui.");
        Response::redirect(url('/master/supervisors'));
    }

    public function delete(Request $request): void
    {
        $id = (int)$request->input('id');
        $supervisor = Database::fetch("SELECT * FROM master_supervisors WHERE id = ?", [$id]);

        if (!$supervisor) {
            flash('error', 'Data pengawas tidak ditemukan.');
            Response::back();
        }

        Database::delete('master_supervisors', 'id = ?', [$id]);
        Auth::logAudit('DELETE', 'master_supervisors', $id, $supervisor, null);

        flash('success', "Data pengawas \"{$supervisor['name']}\" berhasil dihapus.");
        Response::redirect(url('/master/supervisors'));
    }

    public function deleteAll(Request $request): void
    {
        $event = active_event();
        $sql = "DELETE FROM master_supervisors";
        $params = [];

        if ($event) {
            $sql .= " WHERE exam_event_id = ? OR exam_event_id IS NULL";
            $params[] = $event['id'];
        }

        Database::query($sql, $params);
        Auth::logAudit('DELETE', 'master_supervisors', 0, null, ['action' => 'delete_all']);

        flash('success', 'Seluruh data pengawas ruang berhasil dikosongkan.');
        Response::redirect(url('/master/supervisors'));
    }

    public function export(Request $request): void
    {
        $event = active_event();
        $sql = "SELECT * FROM master_supervisors";
        $params = [];

        if ($event) {
            $sql .= " WHERE exam_event_id = ? OR exam_event_id IS NULL";
            $params[] = $event['id'];
        }
        $sql .= " ORDER BY room_assignment ASC, name ASC";

        $supervisors = Database::fetchAll($sql, $params);

        $headers = [
            'No',
            'Nama Pengawas',
            'NIP / NUPTK',
            'Asal Sekolah / Lembaga',
            'Mata Pelajaran',
            'Ruang Tugas',
            'Peran / Jabatan',
            'No. HP / WhatsApp',
            'Status',
            'Keterangan'
        ];

        $rows = [];
        foreach ($supervisors as $idx => $s) {
            $rows[] = [
                $idx + 1,
                $s['name'],
                $s['nip'] ?: '-',
                $s['school_origin'] ?: 'Internal Sekolah',
                $s['subject'] ?: '-',
                $s['room_assignment'] ?: '-',
                $s['role'] ?: 'Pengawas Ruang',
                $s['phone'] ?: '-',
                $s['status'] ?: 'Aktif',
                $s['notes'] ?: '-'
            ];
        }

        $title = 'DATA PENGAWAS RUANG UJIAN - ' . ($event['name'] ?? 'SEKOLAH');
        ExcelExport::export('master_pengawas_ruang_' . date('Ymd_His'), $title, $headers, $rows);
    }

    public function template(Request $request): void
    {
        $headers = [
            'Nama Pengawas',
            'NIP',
            'Asal Sekolah',
            'Mata Pelajaran',
            'Ruang Tugas',
            'Peran',
            'No HP',
            'Status',
            'Keterangan'
        ];

        $sampleRows = [
            ['Drs. A. Nasai', '196803121995121001', 'SMP Negeri 1 Bojong', 'Bahasa Indonesia', 'Ruang 01', 'Pengawas Ruang', '081234567801', 'Aktif', 'Hadir tepat waktu'],
            ['Ai Siti Nuraisyah, S.Pd.', '198405102008012003', 'SMP Negeri 1 Bojong', 'Matematika', 'Ruang 01', 'Pengawas Pendamping', '081234567802', 'Aktif', ''],
            ['Ai Tini Suhartini, S.Pd.', '198607142010012005', 'SMP Negeri 1 Bojong', 'Bahasa Inggris', 'Ruang 02', 'Pengawas Ruang', '081234567803', 'Aktif', ''],
            ['Asep Nandan Mulyana, S.Pd.', '198209202006041002', 'SMP Negeri 1 Bojong', 'IPS', 'Ruang 02', 'Pengawas Pendamping', '081234567804', 'Aktif', ''],
            ['Guntur Wibowo', '198503152009011005', 'SMP Negeri 1 Bojong', 'Seni Budaya', 'Cadangan', 'Pengawas Cadangan', '081234567807', 'Aktif', 'Standby ruang sekretariat'],
        ];

        ExcelExport::exportXlsx('template_import_pengawas_ruang', 'TEMPLATE IMPORT PENGAWAS RUANG', $headers, $sampleRows);
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
            $name = trim($row['nama_pengawas'] ?? $row['nama'] ?? $row['nama_guru'] ?? $row['pengawas'] ?? '');
            if (!$name) {
                continue;
            }

            $nip = trim($row['nip'] ?? $row['nuptk'] ?? $row['nip_nuptk'] ?? '');
            $schoolOrigin = trim($row['asal_sekolah'] ?? $row['sekolah'] ?? $row['instansi'] ?? 'Internal Sekolah');
            $subject = trim($row['mata_pelajaran'] ?? $row['mapel'] ?? $row['bidang_studi'] ?? '');
            $roomAssignment = trim($row['ruang_tugas'] ?? $row['ruang'] ?? $row['pos_tugas'] ?? '');
            $role = trim($row['peran'] ?? $row['jabatan'] ?? $row['posisi'] ?? 'Pengawas Ruang');
            $phone = trim($row['no_hp'] ?? $row['hp'] ?? $row['telepon'] ?? $row['whatsapp'] ?? '');
            $status = trim($row['status'] ?? 'Aktif');
            $notes = trim($row['keterangan'] ?? $row['catatan'] ?? '');

            // Check if supervisor already exists by name or NIP in this event
            $existing = null;
            if ($nip) {
                $existing = Database::fetch("SELECT id FROM master_supervisors WHERE nip = ? AND (exam_event_id = ? OR exam_event_id IS NULL)", [$nip, $event['id'] ?? 0]);
            }
            if (!$existing) {
                $existing = Database::fetch("SELECT id FROM master_supervisors WHERE LOWER(name) = LOWER(?) AND (exam_event_id = ? OR exam_event_id IS NULL)", [$name, $event['id'] ?? 0]);
            }

            if ($existing) {
                Database::update('master_supervisors', [
                    'name' => $name,
                    'nip' => $nip ?: null,
                    'school_origin' => $schoolOrigin ?: 'Internal Sekolah',
                    'subject' => $subject ?: null,
                    'room_assignment' => $roomAssignment ?: null,
                    'role' => $role ?: 'Pengawas Ruang',
                    'phone' => $phone ?: null,
                    'status' => $status ?: 'Aktif',
                    'notes' => $notes ?: null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = :where_id', ['where_id' => $existing['id']]);
                $updated++;
            } else {
                Database::insert('master_supervisors', [
                    'exam_event_id' => $event['id'] ?? null,
                    'name' => $name,
                    'nip' => $nip ?: null,
                    'school_origin' => $schoolOrigin ?: 'Internal Sekolah',
                    'subject' => $subject ?: null,
                    'room_assignment' => $roomAssignment ?: null,
                    'role' => $role ?: 'Pengawas Ruang',
                    'phone' => $phone ?: null,
                    'status' => $status ?: 'Aktif',
                    'notes' => $notes ?: null,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $success++;
            }
        }

        $msg = "Impor data pengawas selesai: {$success} pengawas baru ditambahkan.";
        if ($updated > 0) {
            $msg .= " ({$updated} data pengawas diperbarui).";
        }

        flash('success', $msg);
        Response::redirect(url('/master/supervisors'));
    }
}
