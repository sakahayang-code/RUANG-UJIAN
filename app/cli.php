<?php

require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Core/Migrator.php';
require_once __DIR__ . '/../database/seeders/initial_seeder.php';

use App\Core\Migrator;
use Database\Seeders\InitialSeeder;

echo "=== RUANG UJIAN MIGRATION RUNNER ===\n";

try {
    $executed = Migrator::run();
    if (empty($executed)) {
        echo "Database up-to-date. Tidak ada migrasi baru.\n";
    } else {
        echo "Berhasil menjalankan " . count($executed) . " migrasi:\n";
        foreach ($executed as $mig) {
            echo " - {$mig}\n";
        }
    }

    echo "Menjalankan seeder awal...\n";
    InitialSeeder::run();
    echo "Seeder berhasil dijalankan.\n";
    echo "SUKSES!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
