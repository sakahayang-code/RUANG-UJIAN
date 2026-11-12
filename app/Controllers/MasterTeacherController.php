<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\ExcelExport;

class MasterTeacherController
{
    public function index(Request $request): void
    {
        $search = trim((string)$request->input('q', ''));
        $sql = "SELECT * FROM teachers WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR nip LIKE ? OR position LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql .= " ORDER BY name ASC";
        $teachers = Database::fetchAll($sql, $params);

        View::render('master/teachers', [
            'title' => 'Data Guru & Tenaga Kependidikan',
            'teachers' => $teachers,
            'search' => $search
        ]);
    }

    public function store(Request $request): void
    {
        $name = trim((string)$request->input('name'));
        $nip = trim((string)$request->input('nip'));
        $nuptk = trim((string)$request->input('nuptk'));
        $position = trim((string)$request->input('position', 'Guru Mata Pelajaran'));
        $phone = trim((string)$request->input('phone'));
        $status = (string)$request->input('status', 'Aktif');

        if (empty($name)) {
            flash('error', 'Nama guru/tendik wajib diisi.');
            Response::back();
        }

        $id = Database::insert('teachers', [
            'name' => $name,
            'nip' => $nip ?: null,
            'nuptk' => $nuptk ?: null,
            'position' => $position,
            'phone' => $phone ?: null,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Auth::logAudit('CREATE', 'teachers', $id, null, ['name' => $name, 'nip' => $nip]);
        flash('success', 'Data guru/tendik berhasil ditambahkan.');
        Response::redirect(url('/master/teachers'));
    }

    public function update(Request $request): void
    {
        $id = (int)$request->input('id');
        $teacher = Database::fetch("SELECT * FROM teachers WHERE id = ?", [$id]);
        if (!$teacher) {
            flash('error', 'Data tidak ditemukan.');
            Response::back();
        }

        $name = trim((string)$request->input('name'));
        $nip = trim((string)$request->input('nip'));
        $nuptk = trim((string)$request->input('nuptk'));
        $position = trim((string)$request->input('position', 'Guru Mata Pelajaran'));
        $phone = trim((string)$request->input('phone'));
        $status = (string)$request->input('status', 'Aktif');

        if (empty($name)) {
            flash('error', 'Nama guru/tendik wajib diisi.');
            Response::back();
        }

        $data = [
            'name' => $name,
            'nip' => $nip ?: null,
            'nuptk' => $nuptk ?: null,
            'position' => $position,
            'phone' => $phone ?: null,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        Database::update('teachers', $data, 'id = :where_id', ['where_id' => $id]);
        Auth::logAudit('UPDATE', 'teachers', $id, $teacher, $data);

        flash('success', 'Data guru/tendik berhasil diperbarui.');
        Response::redirect(url('/master/teachers'));
    }

    public function delete(Request $request): void
    {
        $id = (int)$request->input('id');
        Database::delete('teachers', 'id = ?', [$id]);
        Auth::logAudit('DELETE', 'teachers', $id, null, null);

        flash('success', 'Data guru/tendik berhasil dihapus.');
        Response::redirect(url('/master/teachers'));
    }

    public function deleteAll(Request $request): void
    {
        $count = (int)Database::fetchColumn("SELECT COUNT(*) FROM teachers");
        if ($count === 0) {
            flash('info', 'Data guru sudah kosong.');
            Response::redirect(url('/master/teachers'));
        }

        Database::query("UPDATE classes SET homeroom_teacher_id = NULL");
        Database::query("UPDATE committee_members SET teacher_id = NULL");
        Database::query("DELETE FROM written_exam_supervisors");
        Database::query("DELETE FROM teachers");

        Auth::logAudit('DELETE', 'teachers', 0, null, ['deleted_all_count' => $count]);
        flash('success', "Berhasil mengosongkan seluruh data guru/tendik ({$count} data).");
        Response::redirect(url('/master/teachers'));
    }

    public function export(Request $request): void
    {
        $teachers = Database::fetchAll("SELECT name, nip, nuptk, position, phone, status FROM teachers ORDER BY name ASC");
        $headers = ['Nama Lengkap', 'NIP', 'NUPTK', 'Jabatan / Tugas', 'No. Telepon', 'Status'];
        $rows = [];
        foreach ($teachers as $t) {
            $rows[] = [$t['name'], $t['nip'], $t['nuptk'], $t['position'], $t['phone'], $t['status']];
        }

        ExcelExport::export('master_guru_tendik_' . date('Ymd'), 'DATA GURU DAN TENAGA KEPENDIDIKAN', $headers, $rows);
    }

    public function import(Request $request): void
    {
        if (!$request->hasFile('file')) {
            flash('error', 'Silakan pilih berkas CSV untuk diimpor.');
            Response::back();
        }

        $file = $request->file('file');
        $rows = ExcelExport::parseSpreadsheet($file['tmp_name'], $file['name'] ?? null);
        if (empty($rows)) {
            flash('error', 'Berkas Excel/CSV kosong atau format header tidak sesuai.');
            Response::back();
        }

        $success = 0;
        foreach ($rows as $row) {
            $name = $row['nama'] ?? $row['nama_lengkap'] ?? null;
            if (!$name) continue;

            $nip = $row['nip'] ?? null;
            $nuptk = $row['nuptk'] ?? null;
            $position = $row['jabatan'] ?? 'Guru Mata Pelajaran';
            $phone = $row['telepon'] ?? $row['no_telepon'] ?? null;
            $status = $row['status'] ?? 'Aktif';

            Database::insert('teachers', [
                'name' => $name,
                'nip' => $nip,
                'nuptk' => $nuptk,
                'position' => $position,
                'phone' => $phone,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $success++;
        }

        flash('success', "Impor berhasil: {$success} data guru/tendik ditambahkan.");
        Response::redirect(url('/master/teachers'));
    }
}
