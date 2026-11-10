<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class FileController
{
    public function view(Request $request): void
    {
        $this->serveFile($request, false);
    }

    public function serve(Request $request): void
    {
        $this->serveFile($request, false);
    }

    public function download(Request $request): void
    {
        $this->serveFile($request, true);
    }

    private function serveFile(Request $request, bool $asDownload): void
    {
        if (!Auth::check()) {
            http_response_code(403);
            die("Akses ditolak. Silakan login terlebih dahulu.");
        }

        $relPath = (string)$request->input('path');
        // Prevent directory traversal
        $relPath = str_replace(['../', '..\\'], '', $relPath);
        $relPath = ltrim($relPath, '/\\');

        $baseDir = dirname(__DIR__, 2) . '/storage/uploads/';
        $fullPath = realpath($baseDir . $relPath);

        if (!$fullPath || !str_starts_with($fullPath, realpath($baseDir)) || !file_exists($fullPath)) {
            http_response_code(404);
            die("Berkas tidak ditemukan.");
        }

        if ($asDownload) {
            Response::download($fullPath);
        } else {
            Response::viewFile($fullPath);
        }
    }
}
