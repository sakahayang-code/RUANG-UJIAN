<?php

namespace App\Core;

use PDO;
use Exception;

class Backup
{
    public static function create(?string $notes = null): string
    {
        $dbPath = Database::getDatabasePath();
        if (!file_exists($dbPath)) {
            throw new Exception("File database sumber tidak ditemukan.");
        }

        $backupDir = dirname(__DIR__, 2) . '/storage/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = date('Ymd_His') . '_' . substr(bin2hex(random_bytes(4)), 0, 6);
        $filename = "backup_ruang_ujian_{$timestamp}.sqlite";
        $targetPath = $backupDir . '/' . $filename;

        // Perform clean atomic SQLite backup using VACUUM INTO or copy while in read lock
        $pdo = Database::getConnection();
        try {
            $pdo->exec("VACUUM INTO " . $pdo->quote($targetPath));
        } catch (\Throwable $e) {
            // Fallback to safe file copy if VACUUM INTO is not supported in older sqlite versions
            copy($dbPath, $targetPath);
        }

        $fileSize = filesize($targetPath);
        $userId = Auth::id();

        Database::insert('backups', [
            'filename' => $filename,
            'file_size' => $fileSize,
            'created_by' => $userId,
            'notes' => $notes ?: 'Backup manual dibuat dari sistem',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Auth::logAudit('BACKUP_CREATE', 'backups', null, null, ['filename' => $filename, 'size' => $fileSize]);

        return $filename;
    }

    public static function restore(string $backupFilename): bool
    {
        $backupDir = dirname(__DIR__, 2) . '/storage/backups';
        $backupPath = $backupDir . '/' . basename($backupFilename);

        if (!file_exists($backupPath)) {
            throw new Exception("File backup tidak ditemukan.");
        }

        // Validate that the target file is a genuine SQLite database
        $handle = fopen($backupPath, 'rb');
        $header = fread($handle, 16);
        fclose($handle);

        if (strncmp($header, "SQLite format 3\000", 16) !== 0) {
            throw new Exception("File backup tidak valid (bukan berkas SQLite yang sah).");
        }

        // Automatic safety backup before restore
        $safetyFilename = self::create("Backup otomatis sebelum restore dari berkas {$backupFilename}");

        $dbPath = Database::getDatabasePath();

        // Close current connection
        Database::setPath($dbPath);

        // Safely replace database
        copy($backupPath, $dbPath);

        // Touch WAL/SHM removal to force clean reload
        @unlink($dbPath . '-wal');
        @unlink($dbPath . '-shm');

        // Re-record safety backup into the newly restored DB
        $safetyPath = $backupDir . '/' . $safetyFilename;
        $safetySize = file_exists($safetyPath) ? filesize($safetyPath) : 0;
        Database::insert('backups', [
            'filename' => $safetyFilename,
            'file_size' => $safetySize,
            'created_by' => Auth::id(),
            'notes' => "Backup otomatis sebelum restore dari berkas {$backupFilename}",
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Auth::logAudit('BACKUP_RESTORE', 'backups', null, null, ['restored_from' => $backupFilename]);

        return true;
    }

    public static function list(): array
    {
        $backupDir = dirname(__DIR__, 2) . '/storage/backups';
        if (!is_dir($backupDir)) {
            return [];
        }

        $records = Database::fetchAll("SELECT b.*, u.full_name as creator_name FROM backups b LEFT JOIN users u ON b.created_by = u.id ORDER BY b.id DESC");
        return $records;
    }

    public static function delete(string $filename): bool
    {
        $backupDir = dirname(__DIR__, 2) . '/storage/backups';
        $path = $backupDir . '/' . basename($filename);
        if (file_exists($path)) {
            @unlink($path);
        }
        Database::delete('backups', 'filename = ?', [$filename]);
        Auth::logAudit('BACKUP_DELETE', 'backups', null, null, ['filename' => $filename]);
        return true;
    }
}
