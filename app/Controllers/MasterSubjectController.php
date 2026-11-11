<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\ExcelExport;

class MasterSubjectController
{
    public function index(Request $request): void
    {
        $search = trim((string)$request->input('q', ''));
        $sql = "SELECT * FROM subjects WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (code LIKE ? OR name LIKE ? OR grade LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql .= " ORDER BY grade ASC, code ASC";
        $subjects = Database::fetchAll($sql, $params);

        View::render('master/subjects', [
            'title' => 'Master Data Mata Pelajaran',
            'subjects' => $subjects,
            'search' => $search
        ]);
    }

    public function store(Request $request): void
    {
        $code = strtoupper(trim((string)$request->input('code')));
        $name = trim((string)$request->input('name'));
        $grade = (string)$request->input('grade', 'Semua Tingkat');
        $examType = (string)$request->input('exam_type', 'written');

        if (empty($code) || empty($name)) {
            flash('error', 'Kode dan nama mata pelajaran wajib diisi.');
            Response::back();
        }

        $exists = Database::fetch("SELECT id FROM subjects WHERE code = ? AND grade = ?", [$code, $grade]);
        if ($exists) {
            flash('error', "Mata pelajaran dengan kode {$code} pada tingkat {$grade} sudah ada.");
            Response::back();
        }

        $id = Database::insert('subjects', [
            'code' => $code,
            'name' => $name,
            'grade' => $grade,
            'exam_type' => $examType,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Auth::logAudit('CREATE', 'subjects', $id, null, ['code' => $code, 'name' => $name]);
        flash('success', 'Mata pelajaran berhasil ditambahkan.');
        Response::redirect(url('/master/subjects'));
    }

    public function update(Request $request): void
    {
        $id = (int)$request->input('id');
        $subject = Database::fetch("SELECT * FROM subjects WHERE id = ?", [$id]);
        if (!$subject) {
            flash('error', 'Data mata pelajaran tidak ditemukan.');
            Response::back();
        }

        $code = strtoupper(trim((string)$request->input('code')));
        $name = trim((string)$request->input('name'));
        $grade = (string)$request->input('grade', 'Semua Tingkat');
        $examType = (string)$request->input('exam_type', 'written');

        if (empty($code) || empty($name)) {
            flash('error', 'Kode dan nama mata pelajaran wajib diisi.');
            Response::back();
        }

        $exists = Database::fetch("SELECT id FROM subjects WHERE code = ? AND grade = ? AND id != ?", [$code, $grade, $id]);
        if ($exists) {
            flash('error', "Mata pelajaran dengan kode {$code} pada tingkat {$grade} sudah ada.");
            Response::back();
        }

        $data = [
            'code' => $code,
            'name' => $name,
            'grade' => $grade,
            'exam_type' => $examType,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        Database::update('subjects', $data, 'id = :where_id', ['where_id' => $id]);
        Auth::logAudit('UPDATE', 'subjects', $id, $subject, $data);

        flash('success', 'Data mata pelajaran berhasil diperbarui.');
        Response::redirect(url('/master/subjects'));
    }

    public function delete(Request $request): void
    {
        $id = (int)$request->input('id');
        Database::delete('subjects', 'id = ?', [$id]);
        Auth::logAudit('DELETE', 'subjects', $id, null, null);

        flash('success', 'Data mata pelajaran berhasil dihapus.');
        Response::redirect(url('/master/subjects'));
    }

    public function deleteAll(Request $request): void
    {
        $count = (int)Database::fetchColumn("SELECT COUNT(*) FROM subjects");
        if ($count === 0) {
            flash('info', 'Data mata pelajaran sudah kosong.');
            Response::redirect(url('/master/subjects'));
        }

        Database::query("DELETE FROM written_exam_supervisors");
        Database::query("DELETE FROM written_exam_schedules");
        Database::query("DELETE FROM exam_paper_handover_records");
        Database::query("DELETE FROM answer_sheet_receipts");
        Database::query("DELETE FROM answer_sheet_packing_batches");
        Database::query("DELETE FROM subjects");

        Auth::logAudit('DELETE', 'subjects', 0, null, ['deleted_all_count' => $count]);
        flash('success', "Berhasil mengosongkan seluruh data mata pelajaran ({$count} mapel).");
        Response::redirect(url('/master/subjects'));
    }

    public function export(Request $request): void
    {
        $subjects = Database::fetchAll("SELECT code, name, grade, exam_type FROM subjects ORDER BY grade ASC, code ASC");
        $headers = ['Kode Mapel', 'Nama Mata Pelajaran', 'Tingkat', 'Jenis Ujian'];
        $rows = [];
        foreach ($subjects as $s) {
            $examLabel = match($s['exam_type']) {
                'written' => 'Tertulis',
                'practical' => 'Praktik',
                'both' => 'Tertulis & Praktik',
                default => $s['exam_type']
            };
            $rows[] = [$s['code'], $s['name'], $s['grade'], $examLabel];
        }

        ExcelExport::export('master_mata_pelajaran_' . date('Ymd'), 'DATA MASTER MATA PELAJARAN', $headers, $rows);
    }

    public function import(Request $request): void
    {
        if (!$request->hasFile('file')) {
            flash('error', 'Silakan pilih berkas CSV untuk diimpor.');
            Response::back();
        }

        $rows = ExcelExport::parseCsv($request->file('file')['tmp_name']);
        if (empty($rows)) {
            flash('error', 'Berkas CSV kosong atau format header tidak sesuai.');
            Response::back();
        }

        $success = 0;
        foreach ($rows as $row) {
            $code = strtoupper($row['kode'] ?? $row['kode_mapel'] ?? null);
            $name = $row['nama'] ?? $row['nama_mapel'] ?? null;
            if (!$code || !$name) continue;

            $grade = $row['tingkat'] ?? 'Semua Tingkat';
            $examType = strtolower($row['jenis_ujian'] ?? 'written');
            if (str_contains($examType, 'praktik') && str_contains($examType, 'tertulis')) {
                $examType = 'both';
            } elseif (str_contains($examType, 'praktik')) {
                $examType = 'practical';
            } else {
                $examType = 'written';
            }

            Database::insert('subjects', [
                'code' => $code,
                'name' => $name,
                'grade' => $grade,
                'exam_type' => $examType,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $success++;
        }

        flash('success', "Impor berhasil: {$success} mata pelajaran ditambahkan.");
        Response::redirect(url('/master/subjects'));
    }
}
