<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\ExcelExport;

class MasterClassController
{
    public function index(Request $request): void
    {
        $classes = Database::fetchAll("
            SELECT c.*, t.name as homeroom_teacher_name
            FROM classes c
            LEFT JOIN teachers t ON c.homeroom_teacher_id = t.id
            ORDER BY c.grade ASC, c.name ASC
        ");

        // Sync counts
        foreach ($classes as &$c) {
            $count = (int)Database::fetchColumn("SELECT COUNT(*) FROM students WHERE class_id = ? AND deleted_at IS NULL", [$c['id']]);
            $c['student_count'] = $count;
        }

        $teachers = Database::fetchAll("SELECT * FROM teachers WHERE status = 'Aktif' ORDER BY name ASC");

        View::render('master/classes', [
            'title' => 'Master Data Kelas',
            'classes' => $classes,
            'teachers' => $teachers
        ]);
    }

    public function store(Request $request): void
    {
        $name = trim((string)$request->input('name'));
        $grade = (string)$request->input('grade', 'VII');
        $teacherId = $request->input('homeroom_teacher_id') ? (int)$request->input('homeroom_teacher_id') : null;

        if (empty($name)) {
            flash('error', 'Nama kelas tidak boleh kosong.');
            Response::back();
        }

        $exists = Database::fetch("SELECT id FROM classes WHERE name = ?", [$name]);
        if ($exists) {
            flash('error', "Kelas {$name} sudah ada.");
            Response::back();
        }

        $id = Database::insert('classes', [
            'name' => $name,
            'grade' => $grade,
            'homeroom_teacher_id' => $teacherId,
            'student_count' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Auth::logAudit('CREATE', 'classes', $id, null, ['name' => $name, 'grade' => $grade]);
        flash('success', 'Data kelas berhasil ditambahkan.');
        Response::redirect(url('/master/classes'));
    }

    public function update(Request $request): void
    {
        $id = (int)$request->input('id');
        $class = Database::fetch("SELECT * FROM classes WHERE id = ?", [$id]);
        if (!$class) {
            flash('error', 'Kelas tidak ditemukan.');
            Response::back();
        }

        $name = trim((string)$request->input('name'));
        $grade = (string)$request->input('grade', 'VII');
        $teacherId = $request->input('homeroom_teacher_id') ? (int)$request->input('homeroom_teacher_id') : null;

        if (empty($name)) {
            flash('error', 'Nama kelas tidak boleh kosong.');
            Response::back();
        }

        $exists = Database::fetch("SELECT id FROM classes WHERE name = ? AND id != ?", [$name, $id]);
        if ($exists) {
            flash('error', "Kelas dengan nama {$name} sudah ada.");
            Response::back();
        }

        $data = [
            'name' => $name,
            'grade' => $grade,
            'homeroom_teacher_id' => $teacherId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        Database::update('classes', $data, 'id = :where_id', ['where_id' => $id]);
        Auth::logAudit('UPDATE', 'classes', $id, $class, $data);

        flash('success', 'Data kelas berhasil diperbarui.');
        Response::redirect(url('/master/classes'));
    }

    public function delete(Request $request): void
    {
        $id = (int)$request->input('id');
        $studentCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM students WHERE class_id = ? AND deleted_at IS NULL", [$id]);
        if ($studentCount > 0) {
            flash('error', "Kelas tidak dapat dihapus karena masih memiliki {$studentCount} siswa terdaftar.");
            Response::back();
        }

        Database::delete('classes', 'id = ?', [$id]);
        Auth::logAudit('DELETE', 'classes', $id, null, null);

        flash('success', 'Data kelas berhasil dihapus.');
        Response::redirect(url('/master/classes'));
    }

    public function deleteAll(Request $request): void
    {
        $studentCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM students WHERE deleted_at IS NULL");
        if ($studentCount > 0) {
            flash('error', "Tidak dapat mengosongkan data kelas karena masih terdapat {$studentCount} siswa terdaftar.");
            Response::back();
        }

        $count = (int)Database::fetchColumn("SELECT COUNT(*) FROM classes");
        if ($count === 0) {
            flash('info', 'Data kelas sudah kosong.');
            Response::redirect(url('/master/classes'));
        }

        Database::query("DELETE FROM classes");
        Auth::logAudit('DELETE', 'classes', 0, null, ['deleted_all_count' => $count]);
        flash('success', "Berhasil mengosongkan seluruh data kelas ({$count} kelas).");
        Response::redirect(url('/master/classes'));
    }

    public function export(Request $request): void
    {
        $classes = Database::fetchAll("
            SELECT c.name, c.grade, t.name as homeroom,
                   (SELECT COUNT(*) FROM students WHERE class_id = c.id AND deleted_at IS NULL) as student_count
            FROM classes c
            LEFT JOIN teachers t ON c.homeroom_teacher_id = t.id
            ORDER BY c.grade ASC, c.name ASC
        ");

        $headers = ['Nama Kelas', 'Tingkat', 'Wali Kelas', 'Jumlah Siswa'];
        $rows = [];
        foreach ($classes as $c) {
            $rows[] = [$c['name'], $c['grade'], $c['homeroom'] ?: '-', (int)$c['student_count']];
        }

        ExcelExport::export('master_data_kelas_' . date('Ymd'), 'DATA MASTER KELAS', $headers, $rows);
    }

    public function template(Request $request): void
    {
        $headers = ['Nama Kelas', 'Tingkat', 'Wali Kelas'];
        $sampleRows = [
            ['VII A', 'VII', 'Budi Santoso, S.Pd.'],
            ['VII B', 'VII', ''],
            ['VIII A', 'VIII', 'Siti Rahmawati, M.Pd.'],
            ['VIII B', 'VIII', ''],
            ['IX A', 'IX', ''],
        ];

        ExcelExport::exportXlsx('template_import_kelas', 'TEMPLATE DATA KELAS', $headers, $sampleRows);
    }

    public function import(Request $request): void
    {
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

        // Cache all teachers for matching homeroom teacher
        $teachers = Database::fetchAll("SELECT id, name, nip FROM teachers");
        $teacherMap = [];
        foreach ($teachers as $t) {
            $teacherMap[mb_strtolower(trim($t['name']))] = (int)$t['id'];
            if (!empty($t['nip'])) {
                $cleanNip = preg_replace('/[^0-9]/', '', (string)$t['nip']);
                if ($cleanNip) {
                    $teacherMap[$cleanNip] = (int)$t['id'];
                }
            }
        }

        $success = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $name = trim($row['nama_kelas'] ?? $row['nama'] ?? $row['kelas'] ?? $row['rombel'] ?? '');
            if (!$name) continue;

            $grade = trim($row['tingkat'] ?? $row['grade'] ?? $row['tingkat_kelas'] ?? '');
            if (!$grade) {
                // Auto-detect grade from class name (e.g. "VII A", "Kelas 7", "X IPA", "12 RPL")
                if (preg_match('/\b(VII|VIII|IX|X|XI|XII)\b/i', $name, $m)) {
                    $grade = strtoupper($m[1]);
                } elseif (preg_match('/\b(7|8|9|10|11|12)\b/', $name, $m)) {
                    $gradeMap = ['7' => 'VII', '8' => 'VIII', '9' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII'];
                    $grade = $gradeMap[$m[1]] ?? $m[1];
                } else {
                    $grade = 'VII';
                }
            }

            // Match homeroom teacher if provided
            $homeroomInput = trim($row['wali_kelas'] ?? $row['wali'] ?? $row['nama_wali'] ?? $row['guru'] ?? '');
            $teacherId = null;
            if ($homeroomInput) {
                $lowerWali = mb_strtolower($homeroomInput);
                $cleanNip = preg_replace('/[^0-9]/', '', $homeroomInput);
                if (isset($teacherMap[$lowerWali])) {
                    $teacherId = $teacherMap[$lowerWali];
                } elseif ($cleanNip && isset($teacherMap[$cleanNip])) {
                    $teacherId = $teacherMap[$cleanNip];
                } else {
                    foreach ($teachers as $t) {
                        if (stripos($t['name'], $homeroomInput) !== false || stripos($homeroomInput, $t['name']) !== false) {
                            $teacherId = (int)$t['id'];
                            break;
                        }
                    }
                }
            }

            $existing = Database::fetch("SELECT id, homeroom_teacher_id FROM classes WHERE name = ?", [$name]);
            if ($existing) {
                if ($teacherId !== null && empty($existing['homeroom_teacher_id'])) {
                    Database::update('classes', ['homeroom_teacher_id' => $teacherId, 'updated_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $existing['id']]);
                    $updated++;
                }
            } else {
                Database::insert('classes', [
                    'name' => $name,
                    'grade' => $grade,
                    'homeroom_teacher_id' => $teacherId,
                    'student_count' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $success++;
            }
        }

        $msg = "Impor data kelas selesai: {$success} kelas baru ditambahkan.";
        if ($updated > 0) {
            $msg .= " ({$updated} wali kelas diperbarui).";
        }
        flash('success', $msg);
        Response::redirect(url('/master/classes'));
    }
}
