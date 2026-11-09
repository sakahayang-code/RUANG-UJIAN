<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class UserController
{
    /**
     * User Management Hub
     */
    public function index(Request $request): void
    {
        $users = Database::fetchAll("
            SELECT u.*, r.name as role_name, r.display_name as role_display
            FROM users u
            JOIN roles r ON u.role_id = r.id
            ORDER BY u.id ASC
        ");

        $roles = Database::fetchAll("SELECT * FROM roles ORDER BY id ASC");

        View::render('users/index', [
            'title' => 'Manajemen Pengguna & Hak Akses (8 Role)',
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function store(Request $request): void
    {
        $username = trim((string)$request->input('username'));
        $fullName = trim((string)$request->input('full_name'));
        $email = trim((string)$request->input('email'));
        $password = (string)$request->input('password');
        $roleId = (int)$request->input('role_id');
        $isActive = (int)$request->input('is_active', 1);

        if (empty($username) || empty($fullName) || empty($password) || $roleId <= 0) {
            flash('error', 'Semua kolom wajib diisi dengan benar.');
            Response::back();
        }

        // Check unique username
        $exists = Database::fetch("SELECT id FROM users WHERE username = ?", [$username]);
        if ($exists) {
            flash('error', "Username '{$username}' sudah digunakan. Silakan gunakan username lain.");
            Response::back();
        }

        $userId = Database::insert('users', [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'full_name' => $fullName,
            'email' => $email,
            'role_id' => $roleId,
            'is_active' => $isActive,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Auth::logAudit('create_user', 'users', $userId, null, [
            'username' => $username,
            'full_name' => $fullName,
            'role_id' => $roleId
        ]);

        flash('success', "Pengguna '{$fullName}' ({$username}) berhasil ditambahkan.");
        Response::redirect(url('/users'));
    }

    public function update(Request $request): void
    {
        $id = (int)$request->input('id');
        $fullName = trim((string)$request->input('full_name'));
        $email = trim((string)$request->input('email'));
        $roleId = (int)$request->input('role_id');
        $isActive = (int)$request->input('is_active', 1);
        $newPassword = (string)$request->input('password');

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            flash('error', 'Pengguna tidak ditemukan.');
            Response::redirect(url('/users'));
        }

        // Prevent deactivating own account
        if ($id === (int)Auth::id() && $isActive === 0) {
            flash('error', 'Anda tidak dapat menonaktifkan akun yang sedang digunakan.');
            Response::back();
        }

        $data = [
            'full_name' => $fullName,
            'email' => $email,
            'role_id' => $roleId,
            'is_active' => $isActive,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($newPassword)) {
            $data['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
        }

        Database::update('users', $data, 'id = :where_id', ['where_id' => $id]);

        Auth::logAudit('update_user', 'users', $id, $user, $data);

        flash('success', "Data pengguna '{$fullName}' berhasil diperbarui.");
        Response::redirect(url('/users'));
    }

    public function delete(Request $request): void
    {
        $id = (int)$request->input('id');

        if ($id === (int)Auth::id()) {
            flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            Response::back();
        }

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            flash('error', 'Pengguna tidak ditemukan.');
            Response::redirect(url('/users'));
        }

        Database::query("DELETE FROM users WHERE id = ?", [$id]);
        Auth::logAudit('delete_user', 'users', $id, $user, null);

        flash('success', "Pengguna '{$user['full_name']}' telah dihapus.");
        Response::redirect(url('/users'));
    }

    /**
     * Audit Logs & Login Logs Viewer
     */
    public function auditLogs(Request $request): void
    {
        $auditLogs = Database::fetchAll("
            SELECT al.*, u.username, u.full_name
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.id DESC
            LIMIT 100
        ");

        $loginLogs = Database::fetchAll("
            SELECT * FROM login_logs
            ORDER BY id DESC
            LIMIT 50
        ");

        View::render('users/audit_logs', [
            'title' => 'Audit Log & Riwayat Aktivitas Sistem',
            'auditLogs' => $auditLogs,
            'loginLogs' => $loginLogs
        ]);
    }
}
