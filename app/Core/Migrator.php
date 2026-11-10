<?php

namespace App\Core;

class Migrator
{
    public static function migrate(): array
    {
        return self::run();
    }

    public static function run(): array
    {
        $pdo = Database::getConnection();

        // Ensure migrations table exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration VARCHAR(255) NOT NULL UNIQUE,
                applied_at DATETIME NOT NULL
            );
        ");

        $applied = Database::fetchAll("SELECT migration FROM migrations");
        $appliedList = array_column($applied, 'migration');

        $migrationsDir = dirname(__DIR__, 2) . '/database/migrations';
        $files = scandir($migrationsDir);
        sort($files);

        $executed = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || !str_ends_with($file, '.sql')) {
                continue;
            }

            if (!in_array($file, $appliedList, true)) {
                $filePath = $migrationsDir . '/' . $file;
                $sql = file_get_contents($filePath);

                Database::beginTransaction();
                try {
                    $pdo->exec($sql);
                    Database::insert('migrations', [
                        'migration' => $file,
                        'applied_at' => date('Y-m-d H:i:s')
                    ]);
                    Database::commit();
                    $executed[] = $file;
                } catch (\Throwable $e) {
                    Database::rollBack();
                    throw new \RuntimeException("Gagal menjalankan migrasi [{$file}]: " . $e->getMessage(), 0, $e);
                }
            }
        }

        return $executed;
    }

    public static function status(): array
    {
        $pdo = Database::getConnection();
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration VARCHAR(255) NOT NULL UNIQUE,
                applied_at DATETIME NOT NULL
            );
        ");

        $applied = Database::fetchAll("SELECT migration, applied_at FROM migrations");
        $appliedMap = [];
        foreach ($applied as $row) {
            $appliedMap[$row['migration']] = $row['applied_at'];
        }

        $migrationsDir = dirname(__DIR__, 2) . '/database/migrations';
        $files = scandir($migrationsDir);
        sort($files);

        $status = [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || !str_ends_with($file, '.sql')) {
                continue;
            }
            $status[] = [
                'migration' => $file,
                'applied' => isset($appliedMap[$file]),
                'applied_at' => $appliedMap[$file] ?? null
            ];
        }

        return $status;
    }
}
