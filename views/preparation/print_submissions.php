<?php
/**
 * Cetak Format Ceklis Penerimaan Dokumen Soal & Kisi-Kisi
 * Sesuai Standar Format Resmi (Landscape, Multi-level Header, Grey Shading, & Tanda Tangan)
 */
$grades = $matrixData['grades'];
$hasLisan = $matrixData['hasLisan'];
$kelasColspan = ($hasLisan ? count($grades) : 0) + (count($grades) * 2);
$colTotals = $matrixData['columnTotals'];
$mode = $mode ?? 'filled';
$isBlank = ($mode === 'blank');
?>

<div class="print-toolbar no-print" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center; justify-content: space-between; background: #ffffff; padding: 12px 18px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
    <div style="display: flex; gap: 8px; align-items: center;">
        <span style="font-size: 13px; font-weight: 700; color: #334155;">Mode Cetak:</span>
        <a href="<?= url('/preparation/submissions/print?mode=filled') ?>" class="btn btn-sm <?= !$isBlank ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 12px;">
            ✓ Format Terisi (Digital)
        </a>
        <a href="<?= url('/preparation/submissions/print?mode=blank') ?>" class="btn btn-sm <?= $isBlank ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 12px;">
            📝 Format Kosong (Untuk Isi Manual)
        </a>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" onclick="window.print()" class="btn btn-sm btn-primary" style="font-size: 12px; font-weight: 700;">
            🖨️ Cetak Dokumen (Print)
        </button>
        <a href="<?= url('/preparation/submissions') ?>" class="btn btn-sm btn-outline" style="font-size: 12px;">
            ← Kembali ke Checklist
        </a>
    </div>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
@page {
    size: landscape;
    margin: 8mm 10mm;
}
.print-container {
    max-width: 100% !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 auto !important;
}
.doc-title-section {
    text-align: center;
    margin-bottom: 16px;
}
.doc-title-main {
    font-size: 13pt;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #0f172a;
    margin: 0;
}
.doc-title-sub {
    font-size: 10pt;
    font-weight: 600;
    color: #334155;
    margin-top: 4px;
}
.matrix-print-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 9.5pt;
    font-family: Arial, sans-serif;
    color: #000;
}
.matrix-print-table th,
.matrix-print-table td {
    border: 1px solid #000000;
    padding: 5px 4px;
    vertical-align: middle;
}
.matrix-print-table th {
    background-color: #f1f5f9 !important;
    font-weight: 700;
    text-align: center;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
.matrix-print-table .cell-center {
    text-align: center;
}
.matrix-print-table .cell-name {
    text-align: left;
    padding-left: 8px;
    font-weight: 600;
}
.matrix-print-table .cell-subject {
    text-align: left;
    padding-left: 8px;
}
.matrix-print-table .cell-shaded {
    background-color: #cbd5e1 !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
.matrix-print-table .cell-check {
    text-align: center;
    font-weight: 700;
    font-size: 10pt;
}
.matrix-print-table tfoot td {
    background-color: #f8fafc !important;
    font-weight: 700;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

/* Signatures */
.signature-grid {
    display: table;
    width: 100%;
    margin-top: 25px;
    page-break-inside: avoid;
}
.sig-cell {
    display: table-cell;
    width: 50%;
    vertical-align: top;
}
.sig-box {
    width: 280px;
    text-align: center;
    font-size: 10pt;
}
.sig-space {
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sig-space img {
    max-height: 65px;
    max-width: 150px;
    object-fit: contain;
}
</style>

<div class="doc-title-section">
    <div class="doc-title-main">FORMAT CEKLIS PENERIMAAN DOKUMEN SOAL &amp; KISI-KISI</div>
    <div class="doc-title-sub">
        <?= e($event['name']) ?> • Tahun Pelajaran <?= e($school['academic_year'] ?? '') ?>
    </div>
</div>

<table class="matrix-print-table">
    <thead>
        <!-- Header Level 1 -->
        <tr>
            <th rowspan="3" style="width: 35px;">NO</th>
            <th rowspan="3" style="width: 240px;">NAMA PENDIDIK/GURU</th>
            <th rowspan="3" style="width: 200px;">MATA PELAJARAN</th>
            <th colspan="<?= $kelasColspan ?>">KELAS</th>
            <th colspan="3">JUMLAH</th>
        </tr>
        <!-- Header Level 2 -->
        <tr>
            <?php if ($hasLisan): ?>
                <th colspan="<?= count($grades) ?>">LISAN</th>
            <?php endif; ?>
            <?php foreach ($grades as $g): ?>
                <th colspan="2"><?= e((string)$g) ?></th>
            <?php endforeach; ?>
            <th rowspan="2" style="width: 50px;">LISAN</th>
            <th colspan="2">TULIS</th>
        </tr>
        <!-- Header Level 3 -->
        <tr>
            <?php if ($hasLisan): ?>
                <?php foreach ($grades as $g): ?>
                    <th style="width: 38px;"><?= e((string)$g) ?></th>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php foreach ($grades as $g): ?>
                <th style="width: 48px;">SOAL</th>
                <th style="width: 48px;">KISI-KISI</th>
            <?php endforeach; ?>
            <th style="width: 48px;">SOAL</th>
            <th style="width: 48px;">KISI-KISI</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($matrixData['flatRows'])): ?>
            <tr>
                <td colspan="<?= 3 + $kelasColspan + 3 ?>" class="cell-center" style="padding: 20px; color: #64748b;">
                    Belum ada data guru dan mata pelajaran.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($matrixData['flatRows'] as $row): ?>
                <tr>
                    <td class="cell-center"><?= $row['row_no'] ?></td>
                    <?php if ($row['is_first_subject']): ?>
                        <td rowspan="<?= $row['subject_count'] ?>" class="cell-name">
                            <?= e($row['teacher_name']) ?>
                        </td>
                    <?php endif; ?>
                    <td class="cell-subject"><?= e($row['subject_name']) ?></td>

                    <!-- Lisan Cells -->
                    <?php if ($hasLisan): ?>
                        <?php foreach ($grades as $g): ?>
                            <?php $cell = $row['lisan_cells'][$g]; ?>
                            <?php if ($cell['shaded']): ?>
                                <td class="cell-shaded">&nbsp;</td>
                            <?php else: ?>
                                <td class="cell-check"><?= (!$isBlank && $cell['checked']) ? 'v' : '&nbsp;' ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Tulis Soal & Kisi-Kisi Cells -->
                    <?php foreach ($grades as $g): ?>
                        <?php $cSoal = $row['tulis_soal_cells'][$g]; ?>
                        <?php if ($cSoal['shaded']): ?>
                            <td class="cell-shaded">&nbsp;</td>
                        <?php else: ?>
                            <td class="cell-check"><?= (!$isBlank && $cSoal['checked']) ? 'v' : '&nbsp;' ?></td>
                        <?php endif; ?>

                        <?php $cKisi = $row['tulis_kisi_cells'][$g]; ?>
                        <?php if ($cKisi['shaded']): ?>
                            <td class="cell-shaded">&nbsp;</td>
                        <?php else: ?>
                            <td class="cell-check"><?= (!$isBlank && $cKisi['checked']) ? 'v' : '&nbsp;' ?></td>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <!-- Row Totals -->
                    <td class="cell-center"><?= !$isBlank ? $row['row_totals']['lisan'] : '&nbsp;' ?></td>
                    <td class="cell-center"><?= !$isBlank ? $row['row_totals']['soal'] : '&nbsp;' ?></td>
                    <td class="cell-center"><?= !$isBlank ? $row['row_totals']['kisi'] : '&nbsp;' ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align: right; padding-right: 12px; font-weight: 700;">JUMLAH</td>
            <?php if ($hasLisan): ?>
                <?php foreach ($grades as $g): ?>
                    <td class="cell-center font-bold"><?= !$isBlank ? ($colTotals['lisan'][$g] ?? 0) : '&nbsp;' ?></td>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php foreach ($grades as $g): ?>
                <td class="cell-center font-bold"><?= !$isBlank ? ($colTotals['tulis_soal'][$g] ?? 0) : '&nbsp;' ?></td>
                <td class="cell-center font-bold"><?= !$isBlank ? ($colTotals['tulis_kisi'][$g] ?? 0) : '&nbsp;' ?></td>
            <?php endforeach; ?>
            <td class="cell-center font-bold"><?= !$isBlank ? $colTotals['grand_lisan'] : '&nbsp;' ?></td>
            <td class="cell-center font-bold"><?= !$isBlank ? $colTotals['grand_soal'] : '&nbsp;' ?></td>
            <td class="cell-center font-bold"><?= !$isBlank ? $colTotals['grand_kisi'] : '&nbsp;' ?></td>
        </tr>
    </tfoot>
</table>

<!-- Official Validation Signatures -->
<div class="signature-grid">
    <div class="sig-cell">
        <div class="sig-box">
            <div>Mengetahui,</div>
            <div style="font-weight: 600;">Kepala <?= e($school['name']) ?></div>
            <div class="sig-space"></div>
            <div style="font-weight: 700; text-decoration: underline;"><?= e($school['principal_name']) ?></div>
            <div style="font-size: 9pt; color: #334155;">NIP: <?= e($school['principal_nip'] ?: '-') ?></div>
        </div>
    </div>
    <div class="sig-cell" style="text-align: right;">
        <div class="sig-box" style="margin-left: auto;">
            <div><?= e($school['signature_city'] ?? $school['regency'] ?? 'Tempat') ?>, <?= format_date_id(date('Y-m-d')) ?></div>
            <div style="font-weight: 600;">Ketua Panitia Ujian</div>
            <div class="sig-space">
                <?php if (!empty($school['committee_signature_url'])): ?>
                    <img src="<?= $school['committee_signature_url'] ?>" alt="Tanda Tangan Ketua Panitia">
                <?php endif; ?>
            </div>
            <div style="font-weight: 700; text-decoration: underline;">
                <?= e($chair['teacher_name'] ?? $chair['custom_name'] ?? $school['committee_head'] ?? '..................................') ?>
            </div>
            <div style="font-size: 9pt; color: #334155;">
                NIP: <?= e($chair['teacher_nip'] ?? $chair['custom_nip'] ?? '-') ?>
            </div>
        </div>
    </div>
</div>
