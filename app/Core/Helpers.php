<?php

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;
use App\Core\Session;

function app_base_url(): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = dirname($scriptName);
    if ($dir === '/' || $dir === '\\') {
        return '';
    }
    // If running from root index.php or public/index.php
    return rtrim($dir, '/');
}

function url(string $path = ''): string
{
    $base = app_base_url();
    $path = '/' . ltrim($path, '/');
    return $base . $path;
}

function asset(string $path): string
{
    $base = app_base_url();
    // If base already contains /public, don't duplicate
    if (str_ends_with($base, '/public')) {
        return $base . '/assets/' . ltrim($path, '/');
    }
    return $base . '/public/assets/' . ltrim($path, '/');
}

function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    return CSRF::getToken();
}

function csrf_field(): string
{
    return CSRF::field();
}

function flash(string $type, string $message): void
{
    Session::flash('flash_' . $type, $message);
}

function get_flash(string $type): ?string
{
    return Session::flash('flash_' . $type);
}

function auth(): ?array
{
    return Auth::user();
}

function auth_user(): ?array
{
    return Auth::user();
}

function user(): ?array
{
    return Auth::user();
}

function has_role($roles): bool
{
    return Auth::hasRole($roles);
}

function can(string $permission): bool
{
    return Auth::can($permission);
}

function active_event(): ?array
{
    Session::start();
    $eventId = Session::get('active_exam_event_id');
    if ($eventId) {
        $event = Database::fetch("SELECT * FROM exam_events WHERE id = ?", [$eventId]);
        if ($event) return $event;
    }
    // Default to the first active event
    $event = Database::fetch("SELECT * FROM exam_events WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    if ($event) {
        Session::set('active_exam_event_id', $event['id']);
        return $event;
    }
    // Or any latest event
    $event = Database::fetch("SELECT * FROM exam_events ORDER BY id DESC LIMIT 1");
    if ($event) {
        Session::set('active_exam_event_id', $event['id']);
        return $event;
    }
    return null;
}

function school_setting(): array
{
    static $settings = null;
    if ($settings === null) {
        $row = Database::fetch("SELECT * FROM school_settings LIMIT 1");
        $settings = $row ?: [
            'name' => 'SMP / SMA / SMK RUANG UJIAN',
            'npsn' => '12345678',
            'address' => 'Jl. Pendidikan No. 1',
            'village' => 'Sukamaju',
            'district' => 'Cibinong',
            'regency' => 'Bogor',
            'province' => 'Jawa Barat',
            'postal_code' => '16911',
            'phone' => '(021) 87654321',
            'email' => 'info@sekolah.sch.id',
            'website' => 'https://sekolah.sch.id',
            'principal_name' => 'Dr. H. Ahmad Sudrajat, M.Pd.',
            'principal_nip' => '197508152000031002',
            'academic_year' => '2026/2027',
            'committee_head' => 'Budi Santoso, S.Pd.',
            'committee_secretary' => 'Siti Aminah, S.Kom.',
            'letterhead_text' => 'PEMERINTAH DAERAH PROVINSI JAWA BARAT\nDINAS PENDIDIKAN\nCABANG DINAS PENDIDIKAN WILAYAH I',
            'letter_number_format' => '{no}/PAN-UJIAN/{bln_romawi}/{thn}',
            'date_format' => 'd F Y',
            'signature_city' => 'Bogor',
            'logo_path' => null,
            'letterhead_image_path' => null,
        ];
    }
    return $settings;
}

function format_date_id(?string $date, bool $withTime = false): string
{
    if (!$date) return '-';
    $time = strtotime($date);
    if (!$time) return $date;

    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    $d = date('j', $time);
    $m = $months[(int)date('n', $time)];
    $y = date('Y', $time);

    $formatted = "{$d} {$m} {$y}";
    if ($withTime) {
        $formatted .= ' ' . date('H:i', $time) . ' WIB';
    }
    return $formatted;
}

function format_day_id(?string $date): string
{
    if (!$date) return '';
    $time = strtotime($date);
    if (!$time) return '';

    $days = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];

    return $days[date('l', $time)] ?? date('l', $time);
}

function indo_date(?string $date, bool $withTime = false): string
{
    return format_date_id($date, $withTime);
}

function indo_day(?string $date): string
{
    return format_day_id($date);
}

function to_roman(int $number): string
{
    $map = [
        1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
        100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL',
        10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I'
    ];
    $result = '';
    foreach ($map as $val => $roman) {
        while ($number >= $val) {
            $result .= $roman;
            $number -= $val;
        }
    }
    return $result;
}

function rupiah($number): string
{
    return 'Rp ' . number_format((float)($number ?? 0), 0, ',', '.');
}

function old(string $key, $default = '')
{
    return Session::flash('_old_' . $key) ?? $default;
}

function set_old(array $data): void
{
    foreach ($data as $k => $v) {
        Session::flash('_old_' . $k, $v);
    }
}
