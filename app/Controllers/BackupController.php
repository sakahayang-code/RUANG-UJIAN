<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Backup;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;

class BackupController
{
    public function index(Request $request): void
    {
        $backups = Backup::list();

        $dbPath = Database::getDatabasePath();
        $dbSize = file_exists($dbPath) ? filesize($dbPath) : 0;
        $dbModified = file_exists($dbPath) ? date('Y-m-d H:i:s', filemtime($dbPath)) : '-';
        $tableCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'");

        View::render('backup/index', [
            'title' => 'Backup & Restore Database SQLite',
            'backups' => $backups,
            'dbPath' => $dbPath,
            'dbSize' => $dbSize,
            'dbModified' => $dbModified,
            'tableCount' => $tableCount
        ]);
    }

    public function create(Request $request): void
    {
        $notes = trim((string)$request->input('notes', 'Backup manual dibuat dari panel'));

        try {
            $filename = Backup::create($notes);
            flash('success', "Backup database berhasil dibuat: {$filename}");
        } catch (\Throwable $e) {
            flash('error', 'Gagal membuat backup database: ' . $e->getMessage());
        }

        Response::redirect(url('/backup'));
    }

    public function restore(Request $request): void
    {
        $filename = trim((string)$request->input('filename', ''));

        // Handle uploaded backup file if provided
        if ($request->hasFile('backup_file')) {
            $file = $request->file('backup_file');
            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['sqlite', 'db', 'sqlite3'])) {
                    flash('error', 'Format file tidak didukung. Harap unggah file berkas SQLite (.sqlite/.db).');
                    Response::back();
                }

                $backupDir = dirname(__DIR__, 2) . '/storage/backups';
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0755, true);
                }

                $filename = 'uploaded_' . date('Ymd_His') . '_' . basename($file['name']);
                $target = $backupDir . '/' . $filename;
                move_uploaded_file($file['tmp_name'], $target);

                Database::insert('backups', [
                    'filename' => $filename,
                    'file_size' => filesize($target),
                    'created_by' => Auth::id(),
                    'notes' => 'Diunggah manual dari komputer pengguna',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        if (empty($filename)) {
            flash('error', 'Pilih file backup yang akan di-restore.');
            Response::back();
        }

        try {
            Backup::restore($filename);
            flash('success', "Database berhasil di-restore dari arsip: {$filename}. Sistem secara otomatis juga telah membuat salinan pengaman.");
        } catch (\Throwable $e) {
            flash('error', 'Gagal me-restore database: ' . $e->getMessage());
        }

        Response::redirect(url('/backup'));
    }

    public function download(Request $request): void
    {
        $filename = basename((string)$request->input('filename', ''));
        $filePath = dirname(__DIR__, 2) . '/storage/backups/' . $filename;

        if (empty($filename) || !file_exists($filePath)) {
            flash('error', 'File backup tidak ditemukan.');
            Response::redirect(url('/backup'));
        }

        Response::download($filePath, $filename);
    }

    public function delete(Request $request): void
    {
        $filename = basename((string)$request->input('filename', ''));
        if (!empty($filename)) {
            Backup::delete($filename);
            flash('success', "File backup {$filename} telah dihapus.");
        }
        Response::redirect(url('/backup'));
    }
}
