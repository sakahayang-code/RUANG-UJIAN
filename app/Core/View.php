<?php

namespace App\Core;

class View
{
    private static array $sections = [];
    private static ?string $currentSection = null;
    private static ?string $activeLayout = 'layouts/main';

    public static function layout(?string $layout): void
    {
        self::$activeLayout = $layout;
    }

    public static function render(string $view, array $data = [], ?string $layout = 'USE_DEFAULT'): void
    {
        if ($layout === 'USE_DEFAULT') {
            $layout = self::$activeLayout;
        }

        // Reset active layout back to default for subsequent renders
        self::$activeLayout = 'layouts/main';

        $viewFile = dirname(__DIR__, 2) . '/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            die("View [{$view}] tidak ditemukan di {$viewFile}");
        }

        extract($data);

        if ($layout === null) {
            require $viewFile;
            return;
        }

        // Buffer the view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render inside layout
        $layoutFile = dirname(__DIR__, 2) . '/views/' . str_replace('.', '/', $layout) . '.php';
        if (!file_exists($layoutFile)) {
            echo $content;
            return;
        }

        require $layoutFile;
    }

    public static function startSection(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }

    public static function endSection(): void
    {
        if (self::$currentSection !== null) {
            self::$sections[self::$currentSection] = ob_get_clean();
            self::$currentSection = null;
        }
    }

    public static function yieldSection(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }
}
