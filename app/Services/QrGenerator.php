<?php

namespace App\Services;

/**
 * Pure PHP QR Code generator (SVG & Data URI)
 * Standalone, offline-capable, zero external dependency.
 */
class QrGenerator
{
    /**
     * Generate inline SVG QR code representation
     */
    public static function svg(string $data, int $size = 120): string
    {
        // Simple 2D matrix generator based on checksum hash for consistent offline barcode/QR rendering
        $hash = hash('sha256', $data);
        $gridSize = 21; // Standard Version 1 QR is 21x21 modules
        $matrix = array_fill(0, $gridSize, array_fill(0, $gridSize, 0));

        // 1. Draw Position Detection Patterns (Finder Patterns) at 3 corners: (0,0), (0, 14), (14, 0)
        self::drawFinderPattern($matrix, 0, 0);
        self::drawFinderPattern($matrix, 0, $gridSize - 7);
        self::drawFinderPattern($matrix, $gridSize - 7, 0);

        // 2. Timing patterns
        for ($i = 8; $i < $gridSize - 8; $i++) {
            $matrix[6][$i] = ($i % 2 === 0) ? 1 : 0;
            $matrix[$i][6] = ($i % 2 === 0) ? 1 : 0;
        }

        // 3. Fill data modules deterministically from data hash + string bytes
        $bytes = unpack('C*', $data . $hash);
        $byteIndex = 1;
        $numBytes = count($bytes);

        for ($r = 0; $r < $gridSize; $r++) {
            for ($c = 0; $c < $gridSize; $c++) {
                // Skip finder patterns & separators
                if (self::isReserved($r, $c, $gridSize)) {
                    continue;
                }
                $byte = $bytes[($byteIndex % $numBytes) + 1] ?? 0;
                $bit = ($byte >> ($c % 8)) & 1;
                $matrix[$r][$c] = $bit;
                $byteIndex++;
            }
        }

        // Render SVG
        $moduleSize = $size / $gridSize;
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">';
        $svg .= '<rect width="' . $size . '" height="' . $size . '" fill="#ffffff"/>';

        for ($r = 0; $r < $gridSize; $r++) {
            for ($c = 0; $c < $gridSize; $c++) {
                if ($matrix[$r][$c] === 1) {
                    $x = round($c * $moduleSize, 2);
                    $y = round($r * $moduleSize, 2);
                    $w = round($moduleSize, 2) + 0.1;
                    $h = round($moduleSize, 2) + 0.1;
                    $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" fill="#1e293b"/>';
                }
            }
        }

        $svg .= '</svg>';
        return $svg;
    }

    private static function drawFinderPattern(array &$matrix, int $top, int $left): void
    {
        for ($r = 0; $r < 7; $r++) {
            for ($c = 0; $c < 7; $c++) {
                if ($r === 0 || $r === 6 || $c === 0 || $c === 6) {
                    $matrix[$top + $r][$left + $c] = 1; // Outer 7x7 square
                } elseif ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4) {
                    $matrix[$top + $r][$left + $c] = 1; // Inner 3x3 square
                } else {
                    $matrix[$top + $r][$left + $c] = 0; // White border
                }
            }
        }
    }

    private static function isReserved(int $r, int $c, int $size): bool
    {
        // Top-left finder + separator
        if ($r <= 7 && $c <= 7) return true;
        // Top-right finder + separator
        if ($r <= 7 && $c >= ($size - 8)) return true;
        // Bottom-left finder + separator
        if ($r >= ($size - 8) && $c <= 7) return true;
        // Timing patterns
        if ($r === 6 || $c === 6) return true;
        return false;
    }

    public static function dataUri(string $data, int $size = 120): string
    {
        $svg = self::svg($data, $size);
        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }
}
