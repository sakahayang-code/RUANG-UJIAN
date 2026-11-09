<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Migrator;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class InstallController
{
    public function index(Request $request): void
    {
        $requirements = [
            'php_version' => [
                'name' => 'PHP Version >= 8.0',
                'current' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '8.0.0', '>=')
            ],
            'pdo_sqlite' => [
                'name' => 'Ekstensi PDO SQLite (pdo_sqlite)',
                'current' => extension_loaded('pdo_sqlite') ? 'Aktif' : 'Tidak Aktif',
                'status' => extension_loaded('pdo_sqlite')
            ],
            'sqlite3' => [
                'name' => 'Ekstensi SQLite3',
                'current' => extension_loaded('sqlite3') ? 'Aktif' : 'Tidak Aktif',
                'status' => extension_loaded('sqlite3')
            ],
            'gd' => [
                'name' => 'Ekstensi GD (Pengolahan Logo / Gambar)',
                'current' => extension_loaded('gd') ? 'Aktif' : 'Tidak Aktif',
                'status' => extension_loaded('gd')
            ],
            'zip' => [
                'name' => 'Ekstensi Zip (Ekspor Word DOCX)',
                'current' => extension_loaded('zip') ? 'Aktif' : 'Tidak Aktif',
                'status' => extension_loaded('zip')
            ]
        ];

        $root = dirname(__DIR__, 2);
        $directories = [
            'database_dir' => [
                'path' => $root . '/database/storage',
                'writable' => is_writable($root . '/database/storage') || is_writable($root . '/database')
            ],
            'storage_dir' => [
                'path' => $root . '/storage',
                'writable' => is_writable($root . '/storage')
            ],
            'backup_dir' => [
                'path' => $root . '/storage/backups',
                'writable' => is_writable($root . '/storage/backups') || is_writable($root . '/storage')
            ],
            'upload_dir' => [
                'path' => $root . '/storage/uploads',
                'writable' => is_writable($root . '/storage/uploads') || is_writable($root . '/storage')
            ]
        ];

        $allOk = true;
        foreach ($requirements as $req) {
            if (!$req['status']) $allOk = false;
        }
        foreach ($directories as $dir) {
            if (!$dir['writable']) $allOk = false;
        }

        $isInstalled = false;
        try {
            $userCount = Database::fetchColumn("SELECT COUNT(*) FROM users");
            if ($userCount !== false && (int)$userCount > 0) {
                $isInstalled = true;
            }
        } catch (\Throwable $e) {
            $isInstalled = false;
        }

        View::layout('layouts/auth');
        View::render('install/index', [
            'title' => 'Pemasangan Aplikasi RUANG UJIAN',
            'requirements' => $requirements,
            'directories' => $directories,
            'allOk' => $allOk,
            'isInstalled' => $isInstalled
        ]);
    }

    public function install(Request $request): void
    {
        try {
            // 1. Run migrations
            Migrator::migrate();

            // 2. Run initial seeder if users table is empty
            $count = (int)Database::fetchColumn("SELECT COUNT(*) FROM users");
            if ($count === 0) {
                require dirname(__DIR__, 2) . '/database/seeders/initial_seeder.php';
            }

            flash('success', 'Aplikasi RUANG UJIAN berhasil dipasang! Silakan login dengan akun default: username: admin / password: password123');
            Response::redirect(url('/login'));
        } catch (\Throwable $e) {
            flash('error', 'Gagal memasang aplikasi: ' . $e->getMessage());
            Response::redirect(url('/install'));
        }
    }
}
