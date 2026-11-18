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
        $subjectFilter = (int)$request->input('subject_id', 0);

        $allSubjects = Database::fetchAll("SELECT * FROM subjects ORDER BY code ASC, name ASC");

        $sql = "SELECT t.*, 
                       GROUP_CONCAT(s.id, '||') as subject_ids_str,
                       GROUP_CONCAT(s.code, '||') as subject_codes_str,
                       GROUP_CONCAT(s.name, '||') as subject_names_str
                FROM teachers t
                LEFT JOIN teacher_subjects ts ON t.id = ts.teacher_id
                LEFT JOIN subjects s ON ts.subject_id = s.id
                WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (t.name LIKE ? OR t.nip LIKE ? OR t.position LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if (!empty($subjectFilter)) {
            $sql .= " AND t.id IN (SELECT teacher_id FROM teacher_subjects WHERE subject_id = ?)";
            $params[] = $subjectFilter;
        }

        $sql .= " GROUP BY t.id ORDER BY t.name ASC";
        $teachers = Database::fetchAll($sql, $params);

        foreach ($teachers as &$t) {
            $t['subjects'] = [];
            $t['subject_ids'] = [];
            if (!empty($t['subject_ids_str'])) {
                $sIds = explode('||', (string)$t['subject_ids_str']);
                $sCodes = explode('||', (string)($t['subject_codes_str'] ?? ''));
                $sNames = explode('||', (string)($t['subject_names_str'] ?? ''));
                foreach ($sIds as $idx => $sId) {
                    $id = (int)$sId;
                    if ($id > 0) {
                        $t['subject_ids'][] = $id;
                        $t['subjects'][] = [
                            'id' => $id,
                            'code' => $sCodes[$idx] ?? '',
                            'name' => $sNames[$idx] ?? ''
                        ];
                    }
                }
            }
        }
        unset($t);

        View::render('master/teachers', [
            'title' => 'Data Guru & Tenaga Kependidikan',
            'teachers' => $teachers,
            'allSubjects' => $allSubjects,
            'search' => $search,
            'subjectFilter' => $subjectFilter
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

        // Save selected subjects
        $subjectIds = $request->input('subject_ids', []);
        if (!is_array($subjectIds)) {
            $subjectIds = $subjectIds ? [$subjectIds] : [];
        }
        foreach ($subjectIds as $sId) {
            $sId = (int)$sId;
            if ($sId > 0) {
                Database::insert('teacher_subjects', [
                    'teacher_id' => $id,
                    'subject_id' => $sId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        Auth::logAudit('CREATE', 'teachers', $id, null, ['name' => $name, 'nip' => $nip]);
        flash('success', 'Data guru/tendik beserta mata pelajaran yang diampu berhasil ditambahkan.');
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

        // Sync selected subjects
        $subjectIds = $request->input('subject_ids', []);
        if (!is_array($subjectIds)) {
            $subjectIds = $subjectIds ? [$subjectIds] : [];
        }
        Database::delete('teacher_subjects', 'teacher_id = ?', [$id]);
        foreach ($subjectIds as $sId) {
            $sId = (int)$sId;
            if ($sId > 0) {
                Database::insert('teacher_subjects', [
                    'teacher_id' => $id,
                    'subject_id' => $sId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        Auth::logAudit('UPDATE', 'teachers', $id, $teacher, $data);

        flash('success', 'Data guru/tendik dan mata pelajaran berhasil diperbarui.');
        Response::redirect(url('/master/teachers'));
    }

    public function delete(Request $request): void
    {
        $id = (int)$request->input('id');
        Database::delete('teacher_subjects', 'teacher_id = ?', [$id]);
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
        Database::query("DELETE FROM teacher_subjects");
        Database::query("DELETE FROM teachers");

        Auth::logAudit('DELETE', 'teachers', 0, null, ['deleted_all_count' => $count]);
        flash('success', "Berhasil mengosongkan seluruh data guru/tendik ({$count} data).");
        Response::redirect(url('/master/teachers'));
    }

    public function export(Request $request): void
    {
        $teachers = Database::fetchAll("
            SELECT t.name, t.nip, t.nuptk, t.position, t.phone, t.status,
                   GROUP_CONCAT(s.name, ', ') as subjects_list
            FROM teachers t
            LEFT JOIN teacher_subjects ts ON t.id = ts.teacher_id
            LEFT JOIN subjects s ON ts.subject_id = s.id
            GROUP BY t.id
            ORDER BY t.name ASC
        ");
        $headers = ['Nama Lengkap', 'NIP', 'NUPTK', 'Jabatan / Tugas', 'Mata Pelajaran yang Diampu', 'No. Telepon', 'Status'];
        $rows = [];
        foreach ($teachers as $t) {
            $rows[] = [
                $t['name'],
                $t['nip'],
                $t['nuptk'],
                $t['position'],
                $t['subjects_list'] ?: '-',
                $t['phone'],
                $t['status']
            ];
        }

        ExcelExport::export('master_guru_tendik_' . date('Ymd'), 'DATA GURU DAN TENAGA KEPENDIDIKAN', $headers, $rows);
    }

    public function template(Request $request): void
    {
        $headers = ['Nama Lengkap', 'NIP', 'NUPTK', 'Jabatan', 'Mata Pelajaran', 'No Telepon', 'Status'];
        $sampleRows = [
            ['Budi Santoso, S.Pd.', '198001012005011001', '1234567890123456', 'Guru Mata Pelajaran', 'B. INDONESIA', '081234567890', 'Aktif'],
            ['Siti Rahmawati, M.Pd.', '198502022008012002', '2345678901234567', 'Guru Mata Pelajaran', 'IPA, INFORMATIKA', '081234567891', 'Aktif'],
            ['Ahmad Hidayat, S.Kom.', '', '', 'Tenaga Administrasi / IT', '', '081234567892', 'Aktif'],
            ['Sri Wahyuni, S.Pd.', '199003032015012003', '', 'Guru Mata Pelajaran', 'B. INGGRIS', '081234567893', 'Aktif'],
        ];

        ExcelExport::exportXlsx('template_import_guru_tendik', 'TEMPLATE DATA GURU DAN TENDIK', $headers, $sampleRows);
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

        // Cache subjects map by lower-cased code and name
        $allSubjects = Database::fetchAll("SELECT id, code, name FROM subjects");
        $subjectLookup = [];
        foreach ($allSubjects as $s) {
            $subjectLookup[mb_strtolower(trim($s['code']))] = (int)$s['id'];
            $subjectLookup[mb_strtolower(trim($s['name']))] = (int)$s['id'];
        }

        $success = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $name = trim($row['nama_lengkap'] ?? $row['nama'] ?? $row['nama_guru'] ?? $row['guru'] ?? '');
            if (!$name) continue;

            $nip = isset($row['nip']) ? (string)$row['nip'] : (isset($row['nomor_induk_pegawai']) ? (string)$row['nomor_induk_pegawai'] : '');
            $nip = trim($nip);

            $nuptk = isset($row['nuptk']) ? (string)$row['nuptk'] : '';
            $nuptk = trim($nuptk);

            $position = trim($row['jabatan'] ?? $row['tugas'] ?? $row['posisi'] ?? $row['position'] ?? 'Guru Mata Pelajaran');
            $phone = isset($row['telepon']) ? (string)$row['telepon'] : (isset($row['no_telepon']) ? (string)$row['no_telepon'] : (isset($row['no_hp']) ? (string)$row['no_hp'] : (isset($row['hp']) ? (string)$row['hp'] : '')));
            $phone = trim($phone);

            $status = trim($row['status'] ?? 'Aktif');
            $status = (stripos($status, 'non') !== false) ? 'Nonaktif' : 'Aktif';

            $subjectText = trim($row['mata_pelajaran'] ?? $row['mapel'] ?? $row['mata_pelajaran_diampu'] ?? '');

            // Check if teacher exists by NIP (if available) or exact name
            $existing = null;
            if ($nip !== '') {
                $existing = Database::fetch("SELECT id FROM teachers WHERE nip = ?", [$nip]);
            }
            if (!$existing) {
                $existing = Database::fetch("SELECT id FROM teachers WHERE LOWER(name) = ?", [mb_strtolower($name)]);
            }

            $teacherId = null;
            if ($existing) {
                $teacherId = (int)$existing['id'];
                Database::update('teachers', [
                    'nip' => $nip ?: null,
                    'nuptk' => $nuptk ?: null,
                    'position' => $position,
                    'phone' => $phone ?: null,
                    'status' => $status,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = :id', ['id' => $teacherId]);
                $updated++;
            } else {
                $teacherId = Database::insert('teachers', [
                    'name' => $name,
                    'nip' => $nip ?: null,
                    'nuptk' => $nuptk ?: null,
                    'position' => $position,
                    'phone' => $phone ?: null,
                    'status' => $status,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $success++;
            }

            // Associate subjects if specified
            if (!empty($subjectText) && $teacherId) {
                $subjectTokens = preg_split('/[,;\/]+/', $subjectText);
                foreach ($subjectTokens as $token) {
                    $tokenLower = mb_strtolower(trim($token));
                    if (isset($subjectLookup[$tokenLower])) {
                        $sId = $subjectLookup[$tokenLower];
                        $exists = Database::fetchColumn(
                            "SELECT COUNT(*) FROM teacher_subjects WHERE teacher_id = ? AND subject_id = ?",
                            [$teacherId, $sId]
                        );
                        if (!$exists) {
                            Database::insert('teacher_subjects', [
                                'teacher_id' => $teacherId,
                                'subject_id' => $sId,
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }
            }
        }

        $msg = "Impor data guru/tendik selesai: {$success} guru baru ditambahkan.";
        if ($updated > 0) {
            $msg .= " ({$updated} data guru diperbarui).";
        }
        flash('success', $msg);
        Response::redirect(url('/master/teachers'));
    }
}
