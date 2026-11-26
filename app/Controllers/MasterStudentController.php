<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\ExcelExport;

class MasterStudentController
{
    public function index(Request $request): void
    {
        $classId = (string)$request->input('class_id', '');
        $grade = (string)$request->input('grade', '');
        $search = trim((string)$request->input('q', ''));

        $sql = "SELECT s.*, c.name as class_name
                FROM students s
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE s.deleted_at IS NULL";
        $params = [];

        if (!empty($classId)) {
            $sql .= " AND s.class_id = ?";
            $params[] = $classId;
        }

        if (!empty($grade)) {
            $sql .= " AND s.grade = ?";
            $params[] = $grade;
        }

        if (!empty($search)) {
            $sql .= " AND (s.name LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql .= " ORDER BY s.grade ASC, c.name ASC, s.name ASC";

        $students = Database::fetchAll($sql, $params);
        $classes = Database::fetchAll("SELECT * FROM classes ORDER BY grade ASC, name ASC");

        View::render('master/students', [
            'title' => 'Master Data Siswa',
            'students' => $students,
            'classes' => $classes,
            'selectedClass' => $classId,
            'selectedGrade' => $grade,
            'search' => $search
        ]);
    }

    public function store(Request $request): void
    {
        $name = trim((string)$request->input('name'));
        $nis = trim((string)$request->input('nis'));
        $nisn = trim((string)$request->input('nisn'));
        $gender = (string)$request->input('gender', 'L');
        $grade = (string)$request->input('grade', 'VII');
        $classId = (int)$request->input('class_id');
        $status = (string)$request->input('status', 'Aktif');

        if (empty($name) || empty($classId)) {
            flash('error', 'Nama siswa dan kelas wajib diisi.');
            Response::back();
        }

        // Validate duplicates
        if (!empty($nis)) {
            $exists = Database::fetch("SELECT id FROM students WHERE nis = ? AND deleted_at IS NULL", [$nis]);
            if ($exists) {
                flash('error', "NIS {$nis} sudah terdaftar pada siswa lain.");
                Response::back();
            }
        }

        if (!empty($nisn)) {
            $exists = Database::fetch("SELECT id FROM students WHERE nisn = ? AND deleted_at IS NULL", [$nisn]);
            if ($exists) {
                flash('error', "NISN {$nisn} sudah terdaftar pada siswa lain.");
                Response::back();
            }
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = 'std_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $targetDir = dirname(__DIR__, 2) . '/storage/uploads/students';
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            if (move_uploaded_file($file['tmp_name'], $targetDir . '/' . $newFileName)) {
                $photoPath = 'students/' . $newFileName;
            }
        }

        $id = Database::insert('students', [
            'nis' => $nis,
            'nisn' => $nisn,
            'name' => $name,
            'gender' => $gender,
            'grade' => $grade,
            'class_id' => $classId,
            'status' => $status,
            'photo_path' => $photoPath,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        self::syncClassStudentCount($classId);
        Auth::logAudit('CREATE', 'students', $id, null, ['name' => $name, 'nis' => $nis]);

        flash('success', 'Data siswa berhasil ditambahkan.');
        Response::redirect(url('/master/students'));
    }

    public function update(Request $request): void
    {
        $id = (int)$request->input('id');
        $student = Database::fetch("SELECT * FROM students WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$student) {
            flash('error', 'Data siswa tidak ditemukan.');
            Response::back();
        }

        $name = trim((string)$request->input('name'));
        $nis = trim((string)$request->input('nis'));
        $nisn = trim((string)$request->input('nisn'));
        $gender = (string)$request->input('gender', 'L');
        $grade = (string)$request->input('grade', 'VII');
        $classId = (int)$request->input('class_id');
        $status = (string)$request->input('status', 'Aktif');

        if (empty($name) || empty($classId)) {
            flash('error', 'Nama siswa dan kelas wajib diisi.');
            Response::back();
        }

        // Validate duplicates
        if (!empty($nis)) {
            $exists = Database::fetch("SELECT id FROM students WHERE nis = ? AND id != ? AND deleted_at IS NULL", [$nis, $id]);
            if ($exists) {
                flash('error', "NIS {$nis} sudah digunakan oleh siswa lain.");
                Response::back();
            }
        }

        $photoPath = $student['photo_path'];
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = 'std_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $targetDir = dirname(__DIR__, 2) . '/storage/uploads/students';
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            if (move_uploaded_file($file['tmp_name'], $targetDir . '/' . $newFileName)) {
                $photoPath = 'students/' . $newFileName;
            }
        }

        $updateData = [
            'nis' => $nis,
            'nisn' => $nisn,
            'name' => $name,
            'gender' => $gender,
            'grade' => $grade,
            'class_id' => $classId,
            'status' => $status,
            'photo_path' => $photoPath,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        Database::update('students', $updateData, 'id = :where_id', ['where_id' => $id]);
        self::syncClassStudentCount($student['class_id']);
        if ($student['class_id'] !== $classId) {
            self::syncClassStudentCount($classId);
        }

        Auth::logAudit('UPDATE', 'students', $id, $student, $updateData);
        flash('success', 'Data siswa berhasil diperbarui.');
        Response::redirect(url('/master/students'));
    }

    public function delete(Request $request): void
    {
        $id = (int)$request->input('id');
        $student = Database::fetch("SELECT * FROM students WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$student) {
            flash('error', 'Data siswa tidak ditemukan.');
            Response::back();
        }

        Database::update('students', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :where_id', ['where_id' => $id]);
        self::syncClassStudentCount($student['class_id']);
        Auth::logAudit('DELETE', 'students', $id, $student, null);

        flash('success', 'Data siswa berhasil dihapus.');
        Response::redirect(url('/master/students'));
    }

    public function deleteDummy(Request $request): void
    {
        $count = (int)Database::fetchColumn("SELECT COUNT(*) FROM students WHERE name LIKE 'Siswa %' OR nis LIKE '100%'");
        if ($count === 0) {
            flash('info', 'Tidak ada data siswa dummy yang ditemukan.');
            Response::redirect(url('/master/students'));
        }

        Database::query("DELETE FROM exam_room_assignments WHERE participant_id IN (SELECT id FROM exam_participants WHERE participant_number LIKE 'TEST-%' OR student_name_snapshot LIKE 'Siswa %')");
        Database::query("DELETE FROM exam_participants WHERE participant_number LIKE 'TEST-%' OR student_name_snapshot LIKE 'Siswa %'");
        Database::query("DELETE FROM students WHERE name LIKE 'Siswa %' OR nis LIKE '100%'");

        $allClasses = Database::fetchAll("SELECT id FROM classes");
        foreach ($allClasses as $c) {
            self::syncClassStudentCount((int)$c['id']);
        }

        Auth::logAudit('DELETE', 'students', 0, null, ['deleted_dummy_count' => $count]);
        flash('success', "Berhasil menghapus {$count} data siswa dummy.");
        Response::redirect(url('/master/students'));
    }

    public function deleteAll(Request $request): void
    {
        $count = (int)Database::fetchColumn("SELECT COUNT(*) FROM students WHERE deleted_at IS NULL");
        if ($count === 0) {
            flash('info', 'Data siswa sudah kosong.');
            Response::redirect(url('/master/students'));
        }

        Database::query("DELETE FROM exam_room_assignments");
        Database::query("DELETE FROM exam_participants");
        Database::query("DELETE FROM students");

        $allClasses = Database::fetchAll("SELECT id FROM classes");
        foreach ($allClasses as $c) {
            self::syncClassStudentCount((int)$c['id']);
        }

        Auth::logAudit('DELETE', 'students', 0, null, ['deleted_all_count' => $count]);
        flash('success', "Berhasil mengosongkan seluruh data siswa ({$count} siswa).");
        Response::redirect(url('/master/students'));
    }

    public function export(Request $request): void
    {
        $students = Database::fetchAll("
            SELECT s.nis, s.nisn, s.name, s.gender, s.grade, c.name as class_name, s.status
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.deleted_at IS NULL
            ORDER BY s.grade ASC, c.name ASC, s.name ASC
        ");

        $headers = ['NIS', 'NISN', 'Nama Siswa', 'L/P', 'Tingkat', 'Kelas', 'Status'];
        $rows = [];
        foreach ($students as $s) {
            $rows[] = [
                $s['nis'],
                $s['nisn'],
                $s['name'],
                $s['gender'],
                $s['grade'],
                $s['class_name'],
                $s['status']
            ];
        }

        ExcelExport::export('master_data_siswa_' . date('Ymd'), 'DATA MASTER SISWA', $headers, $rows);
    }

    public function template(Request $request): void
    {
        $headers = ['NIS', 'NISN', 'Nama Siswa', 'L/P', 'Tingkat', 'Kelas', 'Status'];
        $sampleRows = [
            ['1001', '0081234567', 'Ahmad Fauzi', 'L', 'VII', 'VII A', 'Aktif'],
            ['1002', '0081234568', 'Siti Aisyah', 'P', 'VII', 'VII A', 'Aktif'],
            ['1003', '0081234569', 'Budi Santoso', 'L', 'VIII', 'VIII A', 'Aktif'],
            ['1004', '0081234570', 'Dewi Lestari', 'P', 'VIII', 'VIII B', 'Aktif'],
            ['1005', '0081234571', 'Eko Prasetyo', 'L', 'IX', 'IX A', 'Aktif'],
        ];

        ExcelExport::exportXlsx('template_import_siswa', 'TEMPLATE DATA SISWA', $headers, $sampleRows);
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
            flash('error', 'Berkas Excel/CSV kosong, tidak terbaca, atau format kolom header tidak sesuai.');
            Response::back();
        }

        $success = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $name = $row['nama_siswa'] ?? $row['nama'] ?? $row['nama_lengkap'] ?? $row['name'] ?? null;
            if (!$name) {
                $skipped++;
                continue;
            }

            $nis = $row['nis'] ?? $row['nomor_induk'] ?? null;
            $nisn = $row['nisn'] ?? null;
            $rawGender = strtoupper(trim((string)($row['l_p'] ?? $row['jk'] ?? $row['jenis_kelamin'] ?? $row['gender'] ?? 'L')));
            $gender = (str_starts_with($rawGender, 'P') || $rawGender === 'PEREMPUAN') ? 'P' : 'L';
            
            $className = trim((string)($row['kelas'] ?? $row['nama_kelas'] ?? $row['rombel'] ?? 'VII A'));
            $grade = trim((string)($row['tingkat'] ?? $row['tingkat_kelas'] ?? $row['grade'] ?? ''));
            if (empty($grade)) {
                if (preg_match('/^(XII|XI|X|IX|VIII|VII|12|11|10|9|8|7)\b/i', $className, $gm)) {
                    $found = strtoupper($gm[1]);
                    $map = ['7' => 'VII', '8' => 'VIII', '9' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII'];
                    $grade = $map[$found] ?? $found;
                } else {
                    $grade = 'VII';
                }
            }

            $status = trim((string)($row['status'] ?? 'Aktif')) ?: 'Aktif';

            // Find or create class
            $class = Database::fetch("SELECT id FROM classes WHERE name = ?", [$className]);
            if (!$class) {
                $classId = Database::insert('classes', [
                    'name' => $className,
                    'grade' => $grade,
                    'student_count' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $classId = (int)$class['id'];
            }

            // Check duplicate by NIS or NISN
            if (!empty($nis)) {
                $exists = Database::fetch("SELECT id FROM students WHERE nis = ? AND deleted_at IS NULL", [$nis]);
                if ($exists) {
                    $skipped++;
                    continue;
                }
            } elseif (!empty($nisn)) {
                $exists = Database::fetch("SELECT id FROM students WHERE nisn = ? AND deleted_at IS NULL", [$nisn]);
                if ($exists) {
                    $skipped++;
                    continue;
                }
            }

            Database::insert('students', [
                'nis' => $nis,
                'nisn' => $nisn,
                'name' => $name,
                'gender' => $gender,
                'grade' => $grade,
                'class_id' => $classId,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $success++;
        }

        // Resync all class counts
        $allClasses = Database::fetchAll("SELECT id FROM classes");
        foreach ($allClasses as $c) {
            self::syncClassStudentCount((int)$c['id']);
        }

        flash('success', "Impor berhasil: {$success} siswa berhasil ditambahkan dari berkas Excel/CSV, {$skipped} dilewati (duplikat/tanpa nama).");
        Response::redirect(url('/master/students'));
    }

    public static function syncClassStudentCount(int $classId): void
    {
        $count = (int)Database::fetchColumn("SELECT COUNT(*) FROM students WHERE class_id = ? AND deleted_at IS NULL", [$classId]);
        Database::update('classes', ['student_count' => $count], 'id = :where_id', ['where_id' => $classId]);
    }
}
