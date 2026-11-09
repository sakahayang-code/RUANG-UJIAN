<?php

namespace App\Services;

use ZipArchive;

class DocxGenerator
{
    private array $elements = [];
    private string $title;

    public function __construct(string $title = 'Dokumen')
    {
        $this->title = $title;
    }

    public function addTitle(string $text): self
    {
        $this->elements[] = ['type' => 'title', 'text' => $text];
        return $this;
    }

    public function addHeading(string $text, int $level = 1): self
    {
        $this->elements[] = ['type' => 'heading', 'text' => $text, 'level' => $level];
        return $this;
    }

    public function addParagraph(string $text, array $options = []): self
    {
        $this->elements[] = ['type' => 'paragraph', 'text' => $text, 'options' => $options];
        return $this;
    }

    public function addTable(array $headers, array $rows): self
    {
        $this->elements[] = ['type' => 'table', 'headers' => $headers, 'rows' => $rows];
        return $this;
    }

    public function addPageBreak(): self
    {
        $this->elements[] = ['type' => 'page_break'];
        return $this;
    }

    public function build(): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'docx_');
        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Tidak dapat membuat file DOCX.");
        }

        // [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>' .
            '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>' .
            '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>' .
            '</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // word/_rels/document.xml.rels
        $docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '</Relationships>';
        $zip->addFromString('word/_rels/document.xml.rels', $docRels);

        // word/styles.xml
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' .
            '<w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:sz w:val="22"/></w:rPr></w:rPrDefault></w:docDefaults>' .
            '</w:styles>';
        $zip->addFromString('word/styles.xml', $styles);

        // Build word/document.xml
        $docXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' .
            '<w:body>';

        foreach ($this->elements as $el) {
            switch ($el['type']) {
                case 'title':
                    $docXml .= '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="240"/></w:pPr>' .
                        '<w:r><w:rPr><w:b/><w:sz w:val="36"/></w:rPr><w:t>' . htmlspecialchars($el['text'], ENT_XML1, 'UTF-8') . '</w:t></w:r></w:p>';
                    break;

                case 'heading':
                    $size = $el['level'] === 1 ? '28' : '24';
                    $docXml .= '<w:p><w:pPr><w:spacing w:before="240" w:after="120"/></w:pPr>' .
                        '<w:r><w:rPr><w:b/><w:sz w:val="' . $size . '"/><w:color w:val="1E293B"/></w:rPr><w:t>' . htmlspecialchars($el['text'], ENT_XML1, 'UTF-8') . '</w:t></w:r></w:p>';
                    break;

                case 'paragraph':
                    $align = $el['options']['align'] ?? 'both';
                    $bold = !empty($el['options']['bold']);
                    $italic = !empty($el['options']['italic']);
                    $docXml .= '<w:p><w:pPr><w:jc w:val="' . $align . '"/><w:spacing w:after="120" w:line="276" w:lineRule="auto"/></w:pPr>' .
                        '<w:r><w:rPr>' . ($bold ? '<w:b/>' : '') . ($italic ? '<w:i/>' : '') . '<w:sz w:val="22"/></w:rPr>' .
                        '<w:t xml:space="preserve">' . htmlspecialchars($el['text'], ENT_XML1, 'UTF-8') . '</w:t></w:r></w:p>';
                    break;

                case 'page_break':
                    $docXml .= '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
                    break;

                case 'table':
                    $docXml .= '<w:tbl>' .
                        '<w:tblPr><w:tblW w:w="5000" w:type="pct"/><w:tblBorders>' .
                        '<w:top w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>' .
                        '<w:left w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>' .
                        '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>' .
                        '<w:right w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>' .
                        '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>' .
                        '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>' .
                        '</w:tblBorders></w:tblPr>';

                    // Header row
                    if (!empty($el['headers'])) {
                        $docXml .= '<w:tr><w:trPr><w:tblHeader/></w:trPr>';
                        foreach ($el['headers'] as $header) {
                            $docXml .= '<w:tc><w:tcPr><w:shd w:val="clear" w:color="auto" w:fill="F1F5F9"/><w:tcMar><w:top w:w="120"/><w:bottom w:w="120"/><w:left w:w="140"/><w:right w:w="140"/></w:tcMar></w:tcPr>' .
                                '<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="20"/></w:rPr><w:t>' . htmlspecialchars($header, ENT_XML1, 'UTF-8') . '</w:t></w:r></w:p></w:tc>';
                        }
                        $docXml .= '</w:tr>';
                    }

                    // Body rows
                    foreach ($el['rows'] as $row) {
                        $docXml .= '<w:tr>';
                        foreach ($row as $cell) {
                            $cellStr = is_scalar($cell) ? (string)$cell : json_encode($cell);
                            $docXml .= '<w:tc><w:tcPr><w:tcMar><w:top w:w="100"/><w:bottom w:w="100"/><w:left w:w="140"/><w:right w:w="140"/></w:tcMar></w:tcPr>' .
                                '<w:p><w:r><w:sz w:val="20"/><w:t>' . htmlspecialchars($cellStr, ENT_XML1, 'UTF-8') . '</w:t></w:r></w:p></w:tc>';
                        }
                        $docXml .= '</w:tr>';
                    }

                    $docXml .= '</w:tbl><w:p/>';
                    break;
            }
        }

        // Section properties (A4 Portrait, 2.5cm margin)
        $docXml .= '<w:sectPr>' .
            '<w:pgSz w:w="11906" w:h="16838"/>' . // A4 in dxa
            '<w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>' .
            '</w:sectPr>';

        $docXml .= '</w:body></w:document>';
        $zip->addFromString('word/document.xml', $docXml);
        $zip->close();

        return $tempFile;
    }

    public function save(string $targetPath): bool
    {
        $tempFile = $this->build();
        $result = copy($tempFile, $targetPath);
        @unlink($tempFile);
        return $result;
    }

    public function output(string $filename = 'dokumen.docx'): void
    {
        $tempFile = $this->build();

        if (php_sapi_name() === 'cli' || headers_sent()) {
            @unlink($tempFile);
            return;
        }

        // Send to browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . filesize($tempFile));
        header('Cache-Control: max-age=0');
        readfile($tempFile);
        @unlink($tempFile);
        exit;
    }
}
