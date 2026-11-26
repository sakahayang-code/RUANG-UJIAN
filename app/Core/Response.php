<?php

namespace App\Core;

class Response
{
    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: " . $url);
        exit;
    }

    public static function back(string $fallback = '/'): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? $fallback;
        self::redirect($referer);
    }

    public static function download(string $filePath, ?string $filename = null): void
    {
        if (!file_exists($filePath)) {
            http_response_code(404);
            die("File tidak ditemukan.");
        }

        $filename = $filename ?: basename($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public static function viewFile(string $filePath, ?string $filename = null): void
    {
        if (!file_exists($filePath)) {
            http_response_code(404);
            die("File tidak ditemukan.");
        }

        $filename = $filename ?: basename($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . addslashes($filename) . '"');
        header('Expires: 0');
        header('Cache-Control: public, max-age=3600');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
