<?php

namespace App\Services;

class ExcelExport
{
    public static function export(string $filename, string $title, array $headers, array $rows): void
    {
        $cleanFilename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
        if (!str_ends_with($cleanFilename, '.xls')) {
            $cleanFilename .= '.xls';
        }

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $cleanFilename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        // Output Excel compatible XML/HTML table
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        echo '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
        echo '<x:Name>' . htmlspecialchars($title) . '</x:Name>';
        echo '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
        echo '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        echo '<style>
            table { border-collapse: collapse; width: 100%; font-family: Calibri, sans-serif; font-size: 11pt; }
            th { background-color: #1e3a8a; color: #ffffff; border: 1px solid #cbd5e1; padding: 8px 12px; text-align: center; font-weight: bold; }
            td { border: 1px solid #cbd5e1; padding: 6px 10px; vertical-align: middle; }
            .num { mso-number-format: "\@"; text-align: center; }
            .text { mso-number-format: "\@"; }
            .money { mso-number-format: "\#\,\#\#0"; text-align: right; }
            .center { text-align: center; }
            .title { font-size: 14pt; font-weight: bold; text-align: center; margin-bottom: 10px; }
        </style>';
        echo '</head><body>';

        echo '<div class="title">' . htmlspecialchars($title) . '</div>';
        echo '<table border="1">';
        echo '<thead><tr>';
        foreach ($headers as $h) {
            echo '<th>' . htmlspecialchars($h) . '</th>';
        }
        echo '</tr></thead><tbody>';

        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                $cellVal = is_scalar($cell) ? (string)$cell : json_encode($cell);
                // Check if numeric text like NISN/NIS to preserve leading zeros
                if (preg_match('/^\d{5,}$/', $cellVal)) {
                    echo '<td class="num">' . htmlspecialchars($cellVal) . '</td>';
                } else {
                    echo '<td>' . htmlspecialchars($cellVal) . '</td>';
                }
            }
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</body></html>';
        exit;
    }

    public static function exportCsv(string $filename, array $headers, array $rows): void
    {
        $cleanFilename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
        if (!str_ends_with($cleanFilename, '.csv')) {
            $cleanFilename .= '.csv';
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $cleanFilename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel compatibility
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
        exit;
    }

    public static function parseCsv(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        // Strip BOM
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Detect separator (comma vs semicolon)
        $firstLine = strtok($content, "\r\n");
        $commaCount = substr_count($firstLine, ',');
        $semiCount = substr_count($firstLine, ';');
        $sep = ($semiCount > $commaCount) ? ';' : ',';

        $handle = fopen('data://text/plain;base64,' . base64_encode($content), 'r');
        $rows = [];
        $headers = null;

        while (($data = fgetcsv($handle, 10000, $sep)) !== false) {
            if ($headers === null) {
                $headers = array_map(function($h) {
                    return trim(strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $h)), '_');
                }, $data);
            } else {
                if (count($data) === count($headers)) {
                    $rows[] = array_combine($headers, array_map('trim', $data));
                }
            }
        }
        fclose($handle);

        return $rows;
    }
}
