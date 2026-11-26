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

    public static function createXlsxFile(string $filePath, string $title, array $headers, array $rows): bool
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>');

        $safeSheetName = str_replace(['\\', '/', '?', '*', '[', ']', ':'], '', substr($title, 0, 31)) ?: 'Sheet1';
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="' . htmlspecialchars($safeSheetName, ENT_XML1) . '" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>');

        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><name val="Calibri"/><sz val="11"/></font>
    <font><b/><name val="Calibri"/><sz val="11"/><color rgb="FFFFFFFF"/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1E3A8A"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left style="thin"><color rgb="FFCBD5E1"/></left>
      <right style="thin"><color rgb="FFCBD5E1"/></right>
      <top style="thin"><color rgb="FFCBD5E1"/></top>
      <bottom style="thin"><color rgb="FFCBD5E1"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="3">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>
  </cellXfs>
</styleSheet>');

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $sheetXml .= '  <sheetData>' . "\n";

        // Header row
        $sheetXml .= '    <row r="1">' . "\n";
        foreach ($headers as $idx => $h) {
            $ref = self::numToCol($idx) . '1';
            $sheetXml .= '      <c r="' . $ref . '" s="1" t="inlineStr"><is><t>' . htmlspecialchars((string)$h, ENT_XML1) . '</t></is></c>' . "\n";
        }
        $sheetXml .= '    </row>' . "\n";

        // Data rows
        $rowNum = 2;
        foreach ($rows as $row) {
            $sheetXml .= '    <row r="' . $rowNum . '">' . "\n";
            $colIdx = 0;
            foreach ($row as $cell) {
                $ref = self::numToCol($colIdx) . $rowNum;
                $cellStr = (string)$cell;
                $sheetXml .= '      <c r="' . $ref . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars($cellStr, ENT_XML1) . '</t></is></c>' . "\n";
                $colIdx++;
            }
            $sheetXml .= '    </row>' . "\n";
            $rowNum++;
        }

        $sheetXml .= '  </sheetData>' . "\n";
        $sheetXml .= '</worksheet>';

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        return $zip->close();
    }

    public static function exportXlsx(string $filename, string $title, array $headers, array $rows): void
    {
        $cleanFilename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
        if (!str_ends_with($cleanFilename, '.xlsx')) {
            $cleanFilename .= '.xlsx';
        }

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');
        if (!self::createXlsxFile($tmp, $title, $headers, $rows)) {
            // Fallback to HTML-based Excel export if ZipArchive fails
            self::export($filename, $title, $headers, $rows);
            return;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $cleanFilename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        readfile($tmp);
        @unlink($tmp);
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

    /**
     * Master spreadsheet parser: automatically detects and parses .xlsx, .xls (HTML/XML table), or .csv
     */
    public static function parseSpreadsheet(string $filePath, ?string $originalName = null): array
    {
        if (!file_exists($filePath) || filesize($filePath) === 0) {
            return [];
        }

        $ext = $originalName ? strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) : '';

        // Read first 1024 bytes to inspect magic header
        $fh = fopen($filePath, 'rb');
        $magic = fread($fh, 1024);
        fclose($fh);

        // 1. Check if ZIP/XLSX (Starts with PK\x03\x04 or has .xlsx extension)
        if (str_starts_with($magic, "PK\x03\x04") || $ext === 'xlsx') {
            $rows = self::parseXlsx($filePath);
            if (!empty($rows)) {
                return $rows;
            }
        }

        // 2. Check if HTML / XML table format (.xls exported from web systems)
        if (preg_match('/<(?:html|table|workbook)\b/i', $magic) || $ext === 'xls') {
            $content = file_get_contents($filePath);
            if (preg_match('/<tr\b/i', $content)) {
                $rows = self::parseHtmlTable($content);
                if (!empty($rows)) {
                    return $rows;
                }
            }
        }

        // 3. Fallback to standard CSV parser
        return self::parseCsv($filePath);
    }

    /**
     * Pure PHP OpenXML (.xlsx) parser with zero external dependencies
     */
    public static function parseXlsx(string $filePath): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Read shared strings
        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            if (preg_match_all('/<si\b[^>]*>(.*?)<\/si>/is', $sharedXml, $siMatches)) {
                foreach ($siMatches[1] as $siContent) {
                    $str = '';
                    if (preg_match_all('/<t\b[^>]*>(.*?)<\/t>/is', $siContent, $tMatches)) {
                        foreach ($tMatches[1] as $tText) {
                            $str .= htmlspecialchars_decode($tText, ENT_QUOTES | ENT_XML1);
                        }
                    }
                    $sharedStrings[] = $str;
                }
            }
        }

        // 2. Locate first worksheet
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (preg_match('/^xl\/worksheets\/sheet\d+\.xml$/i', $name)) {
                    $sheetXml = $zip->getFromName($name);
                    break;
                }
            }
        }
        $zip->close();

        if (!$sheetXml) {
            return [];
        }

        // 3. Extract rows & cells
        $rawRows = [];
        if (preg_match_all('/<row\b[^>]*>(.*?)<\/row>/is', $sheetXml, $rowMatches)) {
            foreach ($rowMatches[1] as $rowContent) {
                $rowCells = [];
                if (preg_match_all('/<c\b([^>]*?)(?:\/>|>(.*?)<\/c>)/is', $rowContent, $cMatches, PREG_SET_ORDER)) {
                    foreach ($cMatches as $c) {
                        $attr = $c[1];
                        $body = $c[2] ?? '';

                        $cellRef = '';
                        if (preg_match('/r="([A-Z]+[0-9]+)"/i', $attr, $rMatch)) {
                            $cellRef = strtoupper($rMatch[1]);
                        }

                        $type = '';
                        if (preg_match('/t="([a-zA-Z0-9]+)"/i', $attr, $tMatch)) {
                            $type = strtolower($tMatch[1]);
                        }

                        $val = '';
                        if ($type === 's') {
                            if (preg_match('/<v>(.*?)<\/v>/is', $body, $vMatch)) {
                                $idx = (int)$vMatch[1];
                                $val = $sharedStrings[$idx] ?? '';
                            }
                        } elseif ($type === 'inlinestr') {
                            if (preg_match('/<t\b[^>]*>(.*?)<\/t>/is', $body, $tMatch)) {
                                $val = htmlspecialchars_decode($tMatch[1], ENT_QUOTES | ENT_XML1);
                            }
                        } else {
                            if (preg_match('/<v>(.*?)<\/v>/is', $body, $vMatch)) {
                                $val = htmlspecialchars_decode($vMatch[1], ENT_QUOTES | ENT_XML1);
                            }
                        }

                        if ($cellRef !== '') {
                            preg_match('/^([A-Z]+)/', $cellRef, $colMatch);
                            $colIdx = self::colLetterToIndex($colMatch[1]);
                            $rowCells[$colIdx] = trim((string)$val);
                        } else {
                            $rowCells[] = trim((string)$val);
                        }
                    }
                }

                if (!empty($rowCells)) {
                    $maxIdx = max(array_keys($rowCells));
                    $filledRow = [];
                    for ($k = 0; $k <= $maxIdx; $k++) {
                        $filledRow[$k] = $rowCells[$k] ?? '';
                    }
                    $rawRows[] = $filledRow;
                }
            }
        }

        if (empty($rawRows)) {
            return [];
        }

        // 4. Map header to rows
        $headerRow = array_shift($rawRows);
        $headers = array_map(function($h) {
            return trim(strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $h)), '_');
        }, $headerRow);

        $mapped = [];
        foreach ($rawRows as $row) {
            $item = [];
            $nonEmpty = false;
            foreach ($headers as $colIdx => $colName) {
                if (empty($colName)) continue;
                $val = $row[$colIdx] ?? '';
                if ($val !== '') $nonEmpty = true;
                $item[$colName] = $val;
            }
            if ($nonEmpty) {
                $mapped[] = $item;
            }
        }

        return $mapped;
    }

    /**
     * Parse HTML / XML table (common for .xls web exports)
     */
    public static function parseHtmlTable(string $content): array
    {
        if (!preg_match_all('/<tr\b[^>]*>(.*?)<\/tr>/is', $content, $trMatches)) {
            return [];
        }

        $rawRows = [];
        foreach ($trMatches[1] as $trContent) {
            if (preg_match_all('/<(?:td|th)\b[^>]*>(.*?)<\/(?:td|th)>/is', $trContent, $tdMatches)) {
                $row = [];
                foreach ($tdMatches[1] as $cellHtml) {
                    $text = html_entity_decode(strip_tags($cellHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $row[] = trim($text);
                }
                if (!empty($row)) {
                    $rawRows[] = $row;
                }
            }
        }

        if (empty($rawRows)) {
            return [];
        }

        // Skip any title or single-column header banners
        $headerRow = null;
        while (!empty($rawRows)) {
            $candidate = array_shift($rawRows);
            if (count($candidate) >= 3) {
                $headerRow = $candidate;
                break;
            }
        }

        if (!$headerRow) {
            return [];
        }

        $headers = array_map(function($h) {
            return trim(strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $h)), '_');
        }, $headerRow);

        $mapped = [];
        foreach ($rawRows as $row) {
            $item = [];
            $nonEmpty = false;
            foreach ($headers as $colIdx => $colName) {
                if (empty($colName)) continue;
                $val = $row[$colIdx] ?? '';
                if ($val !== '') $nonEmpty = true;
                $item[$colName] = $val;
            }
            if ($nonEmpty) {
                $mapped[] = $item;
            }
        }

        return $mapped;
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

    public static function numToCol(int $n): string
    {
        $col = '';
        while ($n >= 0) {
            $col = chr($n % 26 + 65) . $col;
            $n = intdiv($n, 26) - 1;
        }
        return $col;
    }

    public static function colLetterToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $num = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $num = $num * 26 + (ord($letters[$i]) - 64);
        }
        return $num - 1;
    }
}
