<?php

// Test script for admin authentication and demo account removal

$dbPath = __DIR__ . '/../database/storage/ruang_ujian.sqlite';

echo ">>> 1. Memeriksa pengguna di tabel users...\n";
$usersRaw = shell_exec("sqlite3 {$dbPath} \"SELECT id, username, full_name, role, password FROM users;\"");
$lines = array_filter(explode("\n", trim($usersRaw)));

echo "  Total pengguna saat ini: " . count($lines) . "\n";
foreach ($lines as $line) {
    $parts = explode('|', $line);
    echo "  - User: {$parts[1]} ({$parts[2]}), Role: {$parts[3]}\n";
}

// Ensure demo accounts are removed
$demoUsernames = ['ketua', 'sekretaris', 'operator', 'pengawas1', 'penguji1', 'kepsek'];
foreach ($demoUsernames as $demo) {
    $check = shell_exec("sqlite3 {$dbPath} \"SELECT count(*) FROM users WHERE username = '{$demo}';\"");
    if (trim($check) === '0') {
        echo "  [PASS] Akun demo '{$demo}' berhasil dihilangkan.\n";
    } else {
        echo "  [FAIL] Akun demo '{$demo}' masih ada!\n";
        exit(1);
    }
}

echo ">>> 2. Memeriksa kredensial akun admin...\n";
$adminData = shell_exec("sqlite3 {$dbPath} \"SELECT password FROM users WHERE username = 'admin';\"");
$adminHash = trim($adminData);

if (empty($adminHash)) {
    echo "  [FAIL] Akun admin tidak ditemukan!\n";
    exit(1);
}

$newPassword = 'Muhdir@1200723476';
$oldPassword = 'password123';

if (password_verify($newPassword, $adminHash)) {
    echo "  [PASS] Password baru '{$newPassword}' BERHASIL diverifikasi untuk user 'admin'.\n";
} else {
    echo "  [FAIL] Password baru TIDAK cocok dengan hash admin!\n";
    exit(1);
}

if (!password_verify($oldPassword, $adminHash)) {
    echo "  [PASS] Password lama '{$oldPassword}' ditolak (tidak bisa login lagi).\n";
} else {
    echo "  [FAIL] Password lama masih bisa digunakan!\n";
    exit(1);
}

echo ">>> 3. Memeriksa halaman login (views/auth/login.php)...\n";
$loginView = file_get_contents(__DIR__ . '/../views/auth/login.php');

if (strpos($loginView, 'Akun Bawaan Sistem') === false && strpos($loginView, 'password123') === false) {
    echo "  [PASS] Teks akun demo bawaan sistem & password default berhasil dihilangkan dari tampilan login.\n";
} else {
    echo "  [FAIL] Tampilan login masih memuat teks akun demo atau password default!\n";
    exit(1);
}

echo "\nSELURUH PENGUJIAN AKUN ADMIN & PENGHAPUSAN AKUN DEMO LULUS 100%!\n";
