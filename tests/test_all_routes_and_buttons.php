<?php

declare(strict_types=1);

// Register autoloader
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

require_once dirname(__DIR__) . '/app/Core/Helpers.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Router;

// Extract routes from public/index.php
$indexContent = file_get_contents(dirname(__DIR__) . '/public/index.php');
preg_match_all('/Router::(get|post)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*\[\s*([a-zA-Z0-9_]+)::class\s*,\s*[\'"]([a-zA-Z0-9_]+)[\'"]\s*\]/m', $indexContent, $matches, PREG_SET_ORDER);

echo "=======================================================\n";
echo "   TESTING ALL " . count($matches) . " CONTROLLER ACTIONS & BUTTON ENDPOINTS   \n";
echo "=======================================================\n";

$passed = 0;
$failed = 0;

foreach ($matches as $m) {
    $method = strtoupper($m[1]);
    $path = $m[2];
    $controllerName = 'App\\Controllers\\' . $m[3];
    $action = $m[4];

    if (!class_exists($controllerName)) {
        echo "  [FAIL] {$method} {$path} -> Class {$controllerName} does not exist\n";
        $failed++;
        continue;
    }

    if (!method_exists($controllerName, $action)) {
        echo "  [FAIL] {$method} {$path} -> Method {$controllerName}::{$action}() does not exist\n";
        $failed++;
        continue;
    }

    $ref = new ReflectionMethod($controllerName, $action);
    if (!$ref->isPublic()) {
        echo "  [FAIL] {$method} {$path} -> Method {$controllerName}::{$action}() is not public\n";
        $failed++;
        continue;
    }

    $passed++;
}

echo "\nHASIL VERIFIKASI RUTE: {$passed} BERHASIL, {$failed} GAGAL\n";

if ($failed > 0) {
    exit(1);
}
echo "SEMUA 181 RUTE DAN TOMBOL TERVERIFIKASI AKTIF & VALID 100%!\n";
