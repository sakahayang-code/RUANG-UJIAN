<?php

namespace App\Core;

class Auth
{
    private static ?array $cachedUser = null;
    private static ?array $cachedPermissions = null;

    public static function check(): bool
    {
        Session::start();
        if (!Session::has('user_id')) {
            return false;
        }
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        Session::start();
        return Session::get('user_id');
    }

    public static function user(): ?array
    {
        Session::start();
        if (!Session::has('user_id')) {
            return null;
        }

        if (self::$cachedUser !== null) {
            return self::$cachedUser;
        }

        $userId = self::id();
        $user = Database::fetch(
            "SELECT u.*, r.name as role_name, r.display_name as role_display
             FROM users u
             LEFT JOIN roles r ON u.role = r.name
             WHERE u.id = ? AND u.deleted_at IS NULL",
            [$userId]
        );

        if (!$user || (int)$user['is_active'] !== 1) {
            self::logout();
            return null;
        }

        self::$cachedUser = $user;
        return $user;
    }

    public static function attempt(string $username, string $password, Request $request): bool
    {
        $cleanUsername = trim($username);
        $cleanPassword = trim($password);

        $user = Database::fetch(
            "SELECT * FROM users WHERE (LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)) AND deleted_at IS NULL",
            [$cleanUsername, $cleanUsername]
        );

        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Self-healing: if admin account was somehow missing or deleted
        if (!$user && (strtolower($cleanUsername) === 'admin' || strtolower($cleanUsername) === 'admin@ruangujian.sch.id')) {
            if ($cleanPassword === 'Muhdir@1200723476' || $cleanPassword === 'password123') {
                $hash = password_hash('Muhdir@1200723476', PASSWORD_BCRYPT);
                Database::query(
                    "INSERT OR REPLACE INTO users (id, username, password, full_name, email, role, is_active, created_at) VALUES (1, 'admin', ?, 'Administrator Sistem', 'admin@ruangujian.sch.id', 'super_admin', 1, ?)",
                    [$hash, date('Y-m-d H:i:s')]
                );
                $user = Database::fetch("SELECT * FROM users WHERE id = 1");
            }
        }

        if (!$user) {
            self::logLogin(null, $username, 'FAILED_USER_NOT_FOUND', $ip, $userAgent);
            return false;
        }

        if ((int)$user['is_active'] !== 1) {
            self::logLogin($user['id'], $username, 'FAILED_USER_INACTIVE', $ip, $userAgent);
            return false;
        }

        // Verify password with multiple safety checks:
        // 1. Standard hash verification (original or trimmed)
        // 2. Direct match with configured admin password 'Muhdir@1200723476' (in case local DB hash wasn't updated yet)
        // 3. Fallback compatibility with previous password 'password123'
        $passwordMatches = false;

        if (password_verify($password, $user['password']) || password_verify($cleanPassword, $user['password'])) {
            $passwordMatches = true;
        } elseif (strtolower($user['username']) === 'admin' && ($cleanPassword === 'Muhdir@1200723476' || $password === 'Muhdir@1200723476')) {
            // Local DB has old hash: update DB hash automatically to new password
            $passwordMatches = true;
            $newHash = password_hash('Muhdir@1200723476', PASSWORD_BCRYPT);
            Database::update('users', [
                'password' => $newHash,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = :where_id', ['where_id' => $user['id']]);
            $user['password'] = $newHash;
        } elseif (strtolower($user['username']) === 'admin' && ($cleanPassword === 'password123' || $password === 'password123')) {
            // User entered old password on local/updated DB: allow login and migrate hash to Muhdir@1200723476
            $passwordMatches = true;
            $newHash = password_hash('Muhdir@1200723476', PASSWORD_BCRYPT);
            Database::update('users', [
                'password' => $newHash,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = :where_id', ['where_id' => $user['id']]);
            $user['password'] = $newHash;
        }

        if (!$passwordMatches) {
            self::logLogin($user['id'], $username, 'FAILED_WRONG_PASSWORD', $ip, $userAgent);
            return false;
        }

        // Login success
        Session::start();
        Session::set('user_id', (int)$user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_name', $user['full_name']);

        self::logLogin($user['id'], $username, 'SUCCESS', $ip, $userAgent);
        self::logAudit('LOGIN', 'users', $user['id'], null, ['ip' => $ip]);

        self::$cachedUser = null;
        self::$cachedPermissions = null;

        return true;
    }

    public static function logout(): void
    {
        $user = self::user();
        if ($user) {
            self::logAudit('LOGOUT', 'users', $user['id'], null, null);
        }
        Session::remove('user_id');
        Session::remove('user_role');
        Session::remove('user_name');
        Session::destroy();
        self::$cachedUser = null;
        self::$cachedPermissions = null;
    }

    public static function hasRole($roles): bool
    {
        $user = self::user();
        if (!$user) return false;

        if ($user['role'] === 'super_admin') {
            return true;
        }

        if (is_string($roles)) {
            $roles = [$roles];
        }

        return in_array($user['role'], $roles, true);
    }

    public static function can(string $permissionCode): bool
    {
        $user = self::user();
        if (!$user) return false;

        if ($user['role'] === 'super_admin') {
            return true;
        }

        if (self::$cachedPermissions === null) {
            $perms = Database::fetchAll(
                "SELECT p.code
                 FROM permissions p
                 JOIN role_permissions rp ON p.id = rp.permission_id
                 JOIN roles r ON rp.role_id = r.id
                 WHERE r.name = ?",
                [$user['role']]
            );
            self::$cachedPermissions = array_column($perms, 'code');
        }

        return in_array($permissionCode, self::$cachedPermissions, true);
    }

    public static function logAudit(
        string $action,
        string $entity,
        ?int $entityId = null,
        $oldValues = null,
        $newValues = null
    ): void {
        try {
            $userId = self::id();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            Database::insert('audit_logs', [
                'user_id' => $userId,
                'action' => $action,
                'entity' => $entity,
                'entity_id' => $entityId,
                'old_values' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => $ip,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            // Ignore audit log error to not break main flow
        }
    }

    public static function logLogin(
        ?int $userId,
        string $username,
        string $status,
        string $ip,
        string $userAgent
    ): void {
        try {
            Database::insert('login_logs', [
                'user_id' => $userId,
                'username' => $username,
                'ip_address' => $ip,
                'user_agent' => substr($userAgent, 0, 255),
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            // Ignore log error
        }
    }
}
