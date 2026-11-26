<?php
/**
 * Checklist Persiapan & Penerimaan Dokumen Soal
 * Terpisah Khusus: Ujian Tulis & Ujian Lisan/Praktik Sesuai Standar Resmi (image.png)
 */
$grades = $matrixData['grades'];
$hasLisan = $matrixData['hasLisan'];
$hasTulis = $matrixData['hasTulis'];
$colTotals = $matrixData['columnTotals'];
$activeTab = $activeTab ?? 'written';
if ($activeTab === 'matrix') {
    $activeTab = 'written';
}

// Summary metrics
$totalTeachers = count($matrixData['teacherGroups']);
$totalRows = count($matrixData['flatRows']);
$totalSoalChecked = $totalSoalChecked ?? $colTotals['grand_soal'];
$totalKisiChecked = $totalKisiChecked ?? $colTotals['grand_kisi'];
$totalLisanChecked = $totalLisanChecked ?? $colTotals['grand_lisan'];

// Determine columns colspan
if ($hasTulis && $hasLisan) {
    $kelasColspan = count($grades) + (count($grades) * 2);
    $jumlahColspan = 3;
} elseif ($hasTulis) {
    $kelasColspan = count($grades) * 2;
    $jumlahColspan = 2;
} else {
    $kelasColspan = count($grades);
    $jumlahColspan = 1;
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Checklist Persiapan &amp; Penerimaan Dokumen Soal</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Rekapitulasi penerimaan naskah soal, kisi-kisi, dan instrumen ujian per guru, mata pelajaran, dan kelas.</p>
    </div>
    <div class="page-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
        <?php if ($activeTab === 'oral'): ?>
            <a href="<?= url('/preparation/submissions/print?type=practical') ?>" target="_blank" class="btn btn-outline" style="background: #fff;">
                🖨️ Cetak Format Ujian Lisan
            </a>
            <a href="<?= url('/preparation/submissions/export?type=practical') ?>" class="btn btn-outline" style="background: #fff;">
                📥 Unduh Excel Lisan (.xls)
            </a>
            <button type="button" class="btn btn-outline" style="background: #fff;" data-modal-open="addMatrixRowModal">
                ➕ Tambah Guru &amp; Mapel Lisan
            </button>
            <button type="button" class="btn btn-primary" data-modal-open="syncConfirmModal">
                🔄 Sinkronkan Mapel Lisan
            </button>
        <?php elseif ($activeTab === 'details'): ?>
            <a href="<?= url('/preparation/submissions/print?type=written') ?>" target="_blank" class="btn btn-outline" style="background: #fff;">
                🖨️ Cetak Rekap Tulis
            </a>
            <button type="button" class="btn btn-primary" data-modal-open="addSubmissionModal">
                ➕ Tambah Entri Dokumen
            </button>
        <?php else: ?>
            <a href="<?= url('/preparation/submissions/print?type=written') ?>" target="_blank" class="btn btn-outline" style="background: #fff;">
                🖨️ Cetak Format Ujian Tulis
            </a>
            <a href="<?= url('/preparation/submissions/export?type=written') ?>" class="btn btn-outline" style="background: #fff;">
                📥 Unduh Excel Tulis (.xls)
            </a>
            <button type="button" class="btn btn-outline" style="background: #fff;" data-modal-open="addMatrixRowModal">
                ➕ Tambah Guru &amp; Mapel Tulis
            </button>
            <button type="button" class="btn btn-primary" data-modal-open="syncConfirmModal">
                🔄 Sinkronkan Mapel Tulis
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- SaaS Metric Cards -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Guru Pengampu</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #1e293b;"><?= $totalTeachers ?></div>
            <div style="background: #eff6ff; color: #2563eb; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Guru Aktif</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;"><?= $totalRows ?> mata pelajaran terdaftar pada tabel ini</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); <?= $activeTab === 'written' ? 'border-color: #10b981;' : '' ?>">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Naskah Soal Tulis</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #059669;"><?= $totalSoalChecked ?></div>
            <div style="background: #ecfdf5; color: #059669; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Terkumpul (v)</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Total naskah soal diverifikasi</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); <?= $activeTab === 'written' ? 'border-color: #0284c7;' : '' ?>">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Kisi-Kisi Soal Tulis</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #0284c7;"><?= $totalKisiChecked ?></div>
            <div style="background: #f0f9ff; color: #0284c7; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Terkumpul (v)</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Total kisi-kisi terverifikasi</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); <?= $activeTab === 'oral' ? 'border-color: #7c3aed;' : '' ?>">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Ujian Lisan / Praktik</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #7c3aed;"><?= $totalLisanChecked ?></div>
            <div style="background: #f5f3ff; color: #7c3aed; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Terkumpul (v)</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Kisi-kisi / instrumen lisan siap</div>
    </div>
</div>

<!-- Tabs Navigation: Terpisah Khusus Ujian Tulis & Ujian Lisan -->
<div style="display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; gap: 8px; flex-wrap: wrap;">
    <a href="<?= url('/preparation/submissions?tab=written') ?>" 
       class="tab-btn <?= $activeTab === 'written' ? 'tab-btn-active' : '' ?>"
       style="padding: 10px 20px; font-weight: 700; font-size: 14px; text-decoration: none; border-bottom: 3px solid <?= $activeTab === 'written' ? '#2563eb' : 'transparent' ?>; color: <?= $activeTab === 'written' ? '#1d4ed8' : '#64748b' ?>; display: inline-flex; align-items: center; gap: 8px; background: <?= $activeTab === 'written' ? '#eff6ff' : 'transparent' ?>; border-top-left-radius: 8px; border-top-right-radius: 8px;">
        📝 Ceklis Ujian Tulis (Soal &amp; Kisi-Kisi)
    </a>
    <a href="<?= url('/preparation/submissions?tab=oral') ?>" 
       class="tab-btn <?= $activeTab === 'oral' ? 'tab-btn-active' : '' ?>"
       style="padding: 10px 20px; font-weight: 700; font-size: 14px; text-decoration: none; border-bottom: 3px solid <?= $activeTab === 'oral' ? '#7c3aed' : 'transparent' ?>; color: <?= $activeTab === 'oral' ? '#6d28d9' : '#64748b' ?>; display: inline-flex; align-items: center; gap: 8px; background: <?= $activeTab === 'oral' ? '#f5f3ff' : 'transparent' ?>; border-top-left-radius: 8px; border-top-right-radius: 8px;">
        🗣️ Ceklis Ujian Lisan / Praktik
    </a>
    <a href="<?= url('/preparation/submissions?tab=details') ?>" 
       class="tab-btn <?= $activeTab === 'details' ? 'tab-btn-active' : '' ?>"
       style="padding: 10px 20px; font-weight: 700; font-size: 14px; text-decoration: none; border-bottom: 3px solid <?= $activeTab === 'details' ? '#059669' : 'transparent' ?>; color: <?= $activeTab === 'details' ? '#047857' : '#64748b' ?>; display: inline-flex; align-items: center; gap: 8px; background: <?= $activeTab === 'details' ? '#ecfdf5' : 'transparent' ?>; border-top-left-radius: 8px; border-top-right-radius: 8px;">
        📑 Daftar Rinci &amp; Log Telaah (<?= count($submissions) ?>)
    </a>
</div>

<?php if ($activeTab === 'written' || $activeTab === 'oral'): ?>
    <!-- TAB CEKLIS MATRIKS (UJIAN TULIS ATAU UJIAN LISAN) -->

    <!-- Guidance & Context Bar -->
    <div class="card p-3 mb-2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <?php if ($activeTab === 'written'): ?>
                    <span class="badge" style="background: #eff6ff; color: #1d4ed8; font-size: 13px; font-weight: 700; padding: 6px 12px; border: 1px solid #bfdbfe;">
                        📝 Mode: Format Rekap Ujian Tulis
                    </span>
                    <span style="font-size: 13px; color: #475569;">
                        Menampilkan mata pelajaran tertulis beserta kolom penyerahan <strong>Naskah Soal</strong> dan <strong>Kisi-Kisi</strong>.
                    </span>
                <?php else: ?>
                    <span class="badge" style="background: #f5f3ff; color: #6d28d9; font-size: 13px; font-weight: 700; padding: 6px 12px; border: 1px solid #ddd6fe;">
                        🗣️ Mode: Format Rekap Ujian Lisan / Praktik
                    </span>
                    <span style="font-size: 13px; color: #475569;">
                        Menampilkan mata pelajaran dengan ujian lisan/praktik beserta kolom penyerahan <strong>Instrumen/Soal Lisan</strong>.
                    </span>
                <?php endif; ?>
            </div>

            <!-- Legend info -->
            <div style="display: flex; align-items: center; gap: 16px; font-size: 12px; color: #475569;">
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <span class="matrix-cell-control is-checked" style="width: 22px; height: 22px; font-size: 13px; pointer-events: none;"><span class="matrix-check-symbol">v</span></span>
                    Terkumpul / Selesai (v)
                </span>
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <span class="matrix-cell-control" style="width: 22px; height: 22px; pointer-events: none;"></span>
                    Belum Terisi (Klik langsung kotak untuk menandai)
                </span>
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 22px; height: 22px; background: #cbd5e1; border: 1px solid #94a3b8; border-radius: 4px;"></span>
                    Tidak Diajarkan di Kelas Ini
                </span>
            </div>
        </div>
    </div>

    <!-- Quick Fill (Isian Cepat / Batch Actions) Toolbar -->
    <div class="card p-3 mb-3" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border: 1px solid #cbd5e1; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span style="font-size: 13px; font-weight: 700; color: #0f172a; display: inline-flex; align-items: center; gap: 5px;">
                    ⚡ Pengisian Cepat:
                </span>

                <?php if ($activeTab === 'written'): ?>
                    <button type="button" class="btn btn-sm btn-outline" style="background: #ffffff; font-weight: 700; font-size: 12px; border-color: #059669; color: #059669;" onclick="runBatchCheck('check_all_soal')" title="Beri centang v untuk seluruh naskah soal ujian tulis">
                        ✓ Centang Semua Soal
                    </button>
                    <button type="button" class="btn btn-sm btn-outline" style="background: #ffffff; font-weight: 700; font-size: 12px; border-color: #0284c7; color: #0284c7;" onclick="runBatchCheck('check_all_kisi')" title="Beri centang v untuk seluruh kisi-kisi soal ujian tulis">
                        ✓ Centang Semua Kisi-Kisi
                    </button>
                    <button type="button" class="btn btn-sm btn-success" style="font-weight: 700; font-size: 12px;" onclick="runBatchCheck('check_all_written')" title="Centang seluruh naskah soal dan kisi-kisi ujian tulis">
                        ✓ Centang Semua Tulis (Lengkap)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline" style="background: #ffffff; font-weight: 700; font-size: 12px; border-color: #ef4444; color: #ef4444;" onclick="runBatchCheck('clear_all')" title="Kosongkan seluruh tanda centang ujian tulis">
                        ✕ Kosongkan Semua Ceklis Tulis
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-sm btn-primary" style="background: #7c3aed; border-color: #7c3aed; font-weight: 700; font-size: 12px;" onclick="runBatchCheck('check_all_lisan')" title="Beri centang v untuk seluruh instrumen ujian lisan">
                        ✓ Centang Semua Lisan (v)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline" style="background: #ffffff; font-weight: 700; font-size: 12px; border-color: #ef4444; color: #ef4444;" onclick="runBatchCheck('clear_all')" title="Kosongkan seluruh tanda centang ujian lisan">
                        ✕ Kosongkan Semua Ceklis Lisan
                    </button>
                <?php endif; ?>
            </div>
            
            <div style="font-size: 12px; color: #475569; font-weight: 600;">
                💡 <em>Klik langsung pada kotak cell di bawah untuk menandai (v) seketika (0ms).</em>
            </div>
        </div>
    </div>

    <!-- The Matrix Table Card -->
    <div class="card" style="border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
        <div class="table-responsive" style="max-height: 75vh; overflow: auto;">
            <table class="table-matrix-checklist" id="matrixChecklistTable">
                <thead>
                    <?php if ($activeTab === 'written'): ?>
                        <!-- UJIAN TULIS: FORMAT RESMI GAMBAR SESUAI image.png -->
                        <!-- Header Row 1 -->
                        <tr>
                            <th rowspan="3" style="width: 45px; text-align: center;">NO</th>
                            <th rowspan="3" style="width: 250px; text-align: left; padding-left: 12px;">NAMA PENDIDIK/GURU</th>
                            <th rowspan="3" style="width: 220px; text-align: left; padding-left: 12px;">MATA PELAJARAN</th>
                            <th colspan="<?= count($grades) * 2 ?>" style="text-align: center;">KELAS</th>
                            <th colspan="2" style="text-align: center;">JUMLAH</th>
                            <th rowspan="3" style="width: 50px; text-align: center;">AKSI</th>
                        </tr>
                        <!-- Header Row 2 -->
                        <tr>
                            <?php foreach ($grades as $g): ?>
                                <th colspan="2" style="text-align: center;">Kelas <?= e((string)$g) ?></th>
                            <?php endforeach; ?>
                            <th colspan="2" style="text-align: center;">TULIS</th>
                        </tr>
                        <!-- Header Row 3 -->
                        <tr>
                            <?php foreach ($grades as $g): ?>
                                <th style="width: 54px; text-align: center;">SOAL</th>
                                <th style="width: 54px; text-align: center;">KISI-KISI</th>
                            <?php endforeach; ?>
                            <th style="width: 54px; text-align: center;">SOAL</th>
                            <th style="width: 54px; text-align: center;">KISI-KISI</th>
                        </tr>
                    <?php elseif ($activeTab === 'oral'): ?>
                        <!-- UJIAN LISAN: FORMAT RINGKAS & FOKUS LISAN/PRAKTIK -->
                        <!-- Header Row 1 -->
                        <tr>
                            <th rowspan="2" style="width: 45px; text-align: center;">NO</th>
                            <th rowspan="2" style="width: 260px; text-align: left; padding-left: 12px;">NAMA PENDIDIK/GURU</th>
                            <th rowspan="2" style="width: 240px; text-align: left; padding-left: 12px;">MATA PELAJARAN</th>
                            <th colspan="<?= count($grades) ?>" style="text-align: center;">KELAS (UJIAN LISAN / PRAKTIK)</th>
                            <th rowspan="2" style="width: 80px; text-align: center;">JUMLAH<br>LISAN</th>
                            <th rowspan="2" style="width: 50px; text-align: center;">AKSI</th>
                        </tr>
                        <!-- Header Row 2 -->
                        <tr>
                            <?php foreach ($grades as $g): ?>
                                <th style="width: 75px; text-align: center;">Kelas <?= e((string)$g) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    <?php else: ?>
                        <!-- FALLBACK DENGAN KEDUA TIPE (JIKA TAB ALL) -->
                        <tr>
                            <th rowspan="3" style="width: 45px; text-align: center;">NO</th>
                            <th rowspan="3" style="width: 240px; text-align: left; padding-left: 12px;">NAMA PENDIDIK/GURU</th>
                            <th rowspan="3" style="width: 200px; text-align: left; padding-left: 12px;">MATA PELAJARAN</th>
                            <th colspan="<?= $kelasColspan ?>" style="text-align: center;">KELAS</th>
                            <th colspan="<?= $jumlahColspan ?>" style="text-align: center;">JUMLAH</th>
                            <th rowspan="3" style="width: 50px; text-align: center;">AKSI</th>
                        </tr>
                        <tr>
                            <th colspan="<?= count($grades) ?>" style="text-align: center;">LISAN</th>
                            <?php foreach ($grades as $g): ?>
                                <th colspan="2" style="text-align: center;"><?= e((string)$g) ?></th>
                            <?php endforeach; ?>
                            <th rowspan="2" style="width: 55px; text-align: center;">LISAN</th>
                            <th colspan="2" style="text-align: center;">TULIS</th>
                        </tr>
                        <tr>
                            <?php foreach ($grades as $g): ?>
                                <th style="width: 44px; text-align: center;"><?= e((string)$g) ?></th>
                            <?php endforeach; ?>
                            <?php foreach ($grades as $g): ?>
                                <th style="width: 54px; text-align: center;">SOAL</th>
                                <th style="width: 54px; text-align: center;">KISI-KISI</th>
                            <?php endforeach; ?>
                            <th style="width: 54px; text-align: center;">SOAL</th>
                            <th style="width: 54px; text-align: center;">KISI-KISI</th>
                        </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php if (empty($matrixData['flatRows'])): ?>
                        <tr>
                            <td colspan="<?= 3 + $kelasColspan + $jumlahColspan + 1 ?>" style="text-align: center; padding: 40px; color: #94a3b8;">
                                <div style="font-size: 32px; margin-bottom: 8px;">📋</div>
                                <div style="font-size: 15px; font-weight: 600; color: #475569;">
                                    Belum ada entri guru dan mata pelajaran pada checklist <?= $activeTab === 'oral' ? 'ujian lisan' : 'ujian tulis' ?>.
                                </div>
                                <div style="font-size: 13px; color: #64748b; margin-top: 6px;">
                                    Gunakan baris input di bawah atau klik tombol <strong>"Sinkronkan Mapel <?= $activeTab === 'oral' ? 'Lisan' : 'Tulis' ?>"</strong> di atas untuk memuat daftar secara otomatis.
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($matrixData['flatRows'] as $row): ?>
                            <tr id="matrix-row-<?= $row['row_no'] ?>">
                                <td class="cell-center" style="font-weight: 600; color: #64748b;"><?= $row['row_no'] ?></td>
                                
                                <?php if ($row['is_first_subject']): ?>
                                    <td rowspan="<?= $row['subject_count'] ?>" class="cell-name-group">
                                        <div style="font-weight: 700; color: #0f172a; font-size: 13px;"><?= e($row['teacher_name']) ?></div>
                                        <?php if (!empty($row['teacher_nip'])): ?>
                                            <div style="font-size: 11px; color: #64748b;">NIP: <?= e($row['teacher_nip']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>

                                <td class="cell-subject-title">
                                    <span style="font-weight: 600; color: #1e293b;"><?= e($row['subject_name']) ?></span>
                                    <?php if ($row['subject_grade'] !== 'Semua' && !empty($row['subject_grade'])): ?>
                                        <span class="badge badge-secondary" style="font-size: 10px; margin-left: 4px;">Kls <?= e($row['subject_grade']) ?></span>
                                    <?php endif; ?>
                                </td>

                                <?php if ($activeTab === 'oral'): ?>
                                    <!-- ONLY LISAN CELLS FOR ORAL TAB -->
                                    <?php foreach ($grades as $g): ?>
                                        <?php $cell = $row['lisan_cells'][$g]; ?>
                                        <?php if ($cell['shaded']): ?>
                                            <td class="cell-shaded" title="Mata pelajaran ini tidak diajarkan untuk kelas <?= e($g) ?>">&nbsp;</td>
                                        <?php else: ?>
                                            <td class="cell-matrix-interactive <?= $cell['checked'] ? 'is-checked' : '' ?>"
                                                data-teacher-id="<?= $row['teacher_id'] ?>"
                                                data-subject-id="<?= $row['subject_id'] ?>"
                                                data-grade="<?= e($g) ?>"
                                                data-doc-type="lisan"
                                                data-col-type="lisan"
                                                data-row-id="<?= $row['row_no'] ?>"
                                                title="Klik untuk menandai/mengosongkan: <?= e($row['teacher_name']) ?> - <?= e($row['subject_name']) ?> (Lisan Kelas <?= e($g) ?>)"
                                                onclick="toggleMatrixCell(this, event)">
                                                <div class="matrix-cell-control <?= $cell['checked'] ? 'is-checked' : '' ?>" tabindex="0" role="checkbox" aria-checked="<?= $cell['checked'] ? 'true' : 'false' ?>" onkeydown="handleCellKeydown(this, event)">
                                                    <span class="matrix-check-symbol"><?= $cell['checked'] ? 'v' : '' ?></span>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                    <!-- ROW TOTAL LISAN -->
                                    <td class="cell-total-box" id="row-total-lisan-<?= $row['row_no'] ?>"><?= $row['row_totals']['lisan'] ?></td>

                                <?php elseif ($activeTab === 'written'): ?>
                                    <!-- ONLY TULIS SOAL & KISI-KISI CELLS FOR WRITTEN TAB -->
                                    <?php foreach ($grades as $g): ?>
                                        <!-- SOAL CELL -->
                                        <?php $cSoal = $row['tulis_soal_cells'][$g]; ?>
                                        <?php if ($cSoal['shaded']): ?>
                                            <td class="cell-shaded" title="Mata pelajaran ini tidak diajarkan untuk kelas <?= e($g) ?>">&nbsp;</td>
                                        <?php else: ?>
                                            <td class="cell-matrix-interactive <?= $cSoal['checked'] ? 'is-checked' : '' ?>"
                                                data-teacher-id="<?= $row['teacher_id'] ?>"
                                                data-subject-id="<?= $row['subject_id'] ?>"
                                                data-grade="<?= e($g) ?>"
                                                data-doc-type="naskah_soal"
                                                data-col-type="soal"
                                                data-row-id="<?= $row['row_no'] ?>"
                                                title="Klik untuk menandai/mengosongkan: <?= e($row['teacher_name']) ?> - <?= e($row['subject_name']) ?> (Soal Kelas <?= e($g) ?>)"
                                                onclick="toggleMatrixCell(this, event)">
                                                <div class="matrix-cell-control <?= $cSoal['checked'] ? 'is-checked' : '' ?>" tabindex="0" role="checkbox" aria-checked="<?= $cSoal['checked'] ? 'true' : 'false' ?>" onkeydown="handleCellKeydown(this, event)">
                                                    <span class="matrix-check-symbol"><?= $cSoal['checked'] ? 'v' : '' ?></span>
                                                </div>
                                            </td>
                                        <?php endif; ?>

                                        <!-- KISI-KISI CELL -->
                                        <?php $cKisi = $row['tulis_kisi_cells'][$g]; ?>
                                        <?php if ($cKisi['shaded']): ?>
                                            <td class="cell-shaded" title="Mata pelajaran ini tidak diajarkan untuk kelas <?= e($g) ?>">&nbsp;</td>
                                        <?php else: ?>
                                            <td class="cell-matrix-interactive <?= $cKisi['checked'] ? 'is-checked' : '' ?>"
                                                data-teacher-id="<?= $row['teacher_id'] ?>"
                                                data-subject-id="<?= $row['subject_id'] ?>"
                                                data-grade="<?= e($g) ?>"
                                                data-doc-type="kisi_kisi"
                                                data-col-type="kisi"
                                                data-row-id="<?= $row['row_no'] ?>"
                                                title="Klik untuk menandai/mengosongkan: <?= e($row['teacher_name']) ?> - <?= e($row['subject_name']) ?> (Kisi-kisi Kelas <?= e($g) ?>)"
                                                onclick="toggleMatrixCell(this, event)">
                                                <div class="matrix-cell-control <?= $cKisi['checked'] ? 'is-checked' : '' ?>" tabindex="0" role="checkbox" aria-checked="<?= $cKisi['checked'] ? 'true' : 'false' ?>" onkeydown="handleCellKeydown(this, event)">
                                                    <span class="matrix-check-symbol"><?= $cKisi['checked'] ? 'v' : '' ?></span>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                    <!-- ROW TOTALS SOAL & KISI -->
                                    <td class="cell-total-box" id="row-total-soal-<?= $row['row_no'] ?>"><?= $row['row_totals']['soal'] ?></td>
                                    <td class="cell-total-box" id="row-total-kisi-<?= $row['row_no'] ?>"><?= $row['row_totals']['kisi'] ?></td>

                                <?php else: ?>
                                    <!-- BOTH COLUMNS -->
                                    <?php foreach ($grades as $g): ?>
                                        <?php $cell = $row['lisan_cells'][$g]; ?>
                                        <?php if ($cell['shaded']): ?>
                                            <td class="cell-shaded">&nbsp;</td>
                                        <?php else: ?>
                                            <td class="cell-matrix-interactive <?= $cell['checked'] ? 'is-checked' : '' ?>"
                                                data-teacher-id="<?= $row['teacher_id'] ?>" data-subject-id="<?= $row['subject_id'] ?>" data-grade="<?= e($g) ?>" data-doc-type="lisan" data-col-type="lisan" data-row-id="<?= $row['row_no'] ?>" onclick="toggleMatrixCell(this, event)">
                                                <div class="matrix-cell-control <?= $cell['checked'] ? 'is-checked' : '' ?>"><span class="matrix-check-symbol"><?= $cell['checked'] ? 'v' : '' ?></span></div>
                                            </td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <?php foreach ($grades as $g): ?>
                                        <?php $cSoal = $row['tulis_soal_cells'][$g]; ?>
                                        <?php if ($cSoal['shaded']): ?>
                                            <td class="cell-shaded">&nbsp;</td>
                                        <?php else: ?>
                                            <td class="cell-matrix-interactive <?= $cSoal['checked'] ? 'is-checked' : '' ?>"
                                                data-teacher-id="<?= $row['teacher_id'] ?>" data-subject-id="<?= $row['subject_id'] ?>" data-grade="<?= e($g) ?>" data-doc-type="naskah_soal" data-col-type="soal" data-row-id="<?= $row['row_no'] ?>" onclick="toggleMatrixCell(this, event)">
                                                <div class="matrix-cell-control <?= $cSoal['checked'] ? 'is-checked' : '' ?>"><span class="matrix-check-symbol"><?= $cSoal['checked'] ? 'v' : '' ?></span></div>
                                            </td>
                                        <?php endif; ?>
                                        <?php $cKisi = $row['tulis_kisi_cells'][$g]; ?>
                                        <?php if ($cKisi['shaded']): ?>
                                            <td class="cell-shaded">&nbsp;</td>
                                        <?php else: ?>
                                            <td class="cell-matrix-interactive <?= $cKisi['checked'] ? 'is-checked' : '' ?>"
                                                data-teacher-id="<?= $row['teacher_id'] ?>" data-subject-id="<?= $row['subject_id'] ?>" data-grade="<?= e($g) ?>" data-doc-type="kisi_kisi" data-col-type="kisi" data-row-id="<?= $row['row_no'] ?>" onclick="toggleMatrixCell(this, event)">
                                                <div class="matrix-cell-control <?= $cKisi['checked'] ? 'is-checked' : '' ?>"><span class="matrix-check-symbol"><?= $cKisi['checked'] ? 'v' : '' ?></span></div>
                                            </td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <td class="cell-total-box" id="row-total-lisan-<?= $row['row_no'] ?>"><?= $row['row_totals']['lisan'] ?></td>
                                    <td class="cell-total-box" id="row-total-soal-<?= $row['row_no'] ?>"><?= $row['row_totals']['soal'] ?></td>
                                    <td class="cell-total-box" id="row-total-kisi-<?= $row['row_no'] ?>"><?= $row['row_totals']['kisi'] ?></td>
                                <?php endif; ?>

                                <!-- ACTION COLUMN -->
                                <td class="cell-center">
                                    <form action="<?= url('/preparation/submissions/delete-row') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus baris guru & mapel ini dari checklist <?= $activeTab === 'oral' ? 'ujian lisan' : 'ujian tulis' ?>?', this)">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="teacher_id" value="<?= $row['teacher_id'] ?>">
                                        <input type="hidden" name="subject_id" value="<?= $row['subject_id'] ?>">
                                        <input type="hidden" name="tab" value="<?= $activeTab ?>">
                                        <button type="submit" class="btn-icon-subtle" title="Hapus Baris Ini">✕</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Inline Row Addition Directly at Bottom of Table -->
                    <tr class="inline-add-row" style="background: #f8fafc; border-top: 2px solid #cbd5e1;">
                        <td class="cell-center" style="font-weight: 800; color: #2563eb; font-size: 16px;">+</td>
                        <td colspan="2" style="padding: 10px 12px;">
                            <form action="<?= url('/preparation/submissions/add-row') ?>" method="POST" id="inlineAddRowForm" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="tab" value="<?= $activeTab ?>">
                                <span style="font-size: 12px; font-weight: 700; color: #1e293b; white-space: nowrap;">
                                    ➕ Tambah Baris <?= $activeTab === 'oral' ? 'Lisan' : 'Tulis' ?>:
                                </span>
                                <select name="teacher_id" class="form-control form-control-sm" style="min-width: 180px; font-size: 12px; background: #fff;" required>
                                    <option value="">-- Pilih Guru --</option>
                                    <?php foreach ($teachers as $t): ?>
                                        <option value="<?= $t['id'] ?>"><?= e($t['name']) ?><?= !empty($t['nip']) ? ' (' . e($t['nip']) . ')' : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="subject_id" class="form-control form-control-sm" style="min-width: 180px; font-size: 12px; background: #fff;" required>
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    <?php foreach ($subjects as $s): ?>
                                        <?php 
                                        $sExam = $s['exam_type'] ?? 'written';
                                        if ($activeTab === 'oral' && !in_array($sExam, ['practical', 'both', 'lisan'])) {
                                            continue;
                                        }
                                        if ($activeTab === 'written' && !in_array($sExam, ['written', 'both', ''])) {
                                            continue;
                                        }
                                        ?>
                                        <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['code']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary" style="font-size: 12px; padding: 5px 14px; font-weight: 600; white-space: nowrap;">
                                    Tambahkan
                                </button>
                            </form>
                        </td>
                        <td colspan="<?= $kelasColspan + $jumlahColspan + 1 ?>" style="padding: 10px 12px; color: #64748b; font-size: 12px; font-style: italic;">
                            Baris baru akan langsung ditambahkan ke tabel checklist <?= $activeTab === 'oral' ? 'ujian lisan' : 'ujian tulis' ?> ini.
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right; padding-right: 16px; font-weight: 800; font-size: 13px; color: #1e293b;">
                            JUMLAH
                        </td>
                        <?php if ($activeTab === 'oral'): ?>
                            <?php foreach ($grades as $g): ?>
                                <td class="cell-footer-total" id="col-total-lisan-<?= e($g) ?>"><?= $colTotals['lisan'][$g] ?? 0 ?></td>
                            <?php endforeach; ?>
                            <td class="cell-footer-grand" id="grand-total-lisan"><?= $colTotals['grand_lisan'] ?></td>
                        <?php elseif ($activeTab === 'written'): ?>
                            <?php foreach ($grades as $g): ?>
                                <td class="cell-footer-total" id="col-total-soal-<?= e($g) ?>"><?= $colTotals['tulis_soal'][$g] ?? 0 ?></td>
                                <td class="cell-footer-total" id="col-total-kisi-<?= e($g) ?>"><?= $colTotals['tulis_kisi'][$g] ?? 0 ?></td>
                            <?php endforeach; ?>
                            <td class="cell-footer-grand" id="grand-total-soal"><?= $colTotals['grand_soal'] ?></td>
                            <td class="cell-footer-grand" id="grand-total-kisi"><?= $colTotals['grand_kisi'] ?></td>
                        <?php else: ?>
                            <?php foreach ($grades as $g): ?>
                                <td class="cell-footer-total" id="col-total-lisan-<?= e($g) ?>"><?= $colTotals['lisan'][$g] ?? 0 ?></td>
                            <?php endforeach; ?>
                            <?php foreach ($grades as $g): ?>
                                <td class="cell-footer-total" id="col-total-soal-<?= e($g) ?>"><?= $colTotals['tulis_soal'][$g] ?? 0 ?></td>
                                <td class="cell-footer-total" id="col-total-kisi-<?= e($g) ?>"><?= $colTotals['tulis_kisi'][$g] ?? 0 ?></td>
                            <?php endforeach; ?>
                            <td class="cell-footer-grand" id="grand-total-lisan"><?= $colTotals['grand_lisan'] ?></td>
                            <td class="cell-footer-grand" id="grand-total-soal"><?= $colTotals['grand_soal'] ?></td>
                            <td class="cell-footer-grand" id="grand-total-kisi"><?= $colTotals['grand_kisi'] ?></td>
                        <?php endif; ?>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

<?php else: ?>
    <!-- TAB 3: DAFTAR RINCI & LOG TELAAH DOKUMEN -->
    <div class="card p-3 mb-3">
        <form method="GET" action="<?= url('/preparation/submissions') ?>" class="filter-bar" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="tab" value="details">
            <div style="min-width: 180px;">
                <select name="type" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Jenis Dokumen</option>
                    <option value="naskah_soal" <?= $typeFilter === 'naskah_soal' ? 'selected' : '' ?>>Naskah Soal Ujian</option>
                    <option value="kisi_kisi" <?= $typeFilter === 'kisi_kisi' ? 'selected' : '' ?>>Kisi-kisi Soal</option>
                    <option value="lisan" <?= $typeFilter === 'lisan' ? 'selected' : '' ?>>Ujian Lisan / Praktik</option>
                    <option value="kunci_jawaban" <?= $typeFilter === 'kunci_jawaban' ? 'selected' : '' ?>>Kunci Jawaban Administrasi</option>
                    <option value="dokumen_nilai" <?= $typeFilter === 'dokumen_nilai' ? 'selected' : '' ?>>Tanda Terima Dokumen Nilai</option>
                </select>
            </div>
            <div style="min-width: 160px;">
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="belum_diserahkan" <?= $statusFilter === 'belum_diserahkan' ? 'selected' : '' ?>>Belum Diserahkan</option>
                    <option value="sudah_diserahkan" <?= $statusFilter === 'sudah_diserahkan' ? 'selected' : '' ?>>Sudah Diserahkan</option>
                    <option value="sedang_diperiksa" <?= $statusFilter === 'sedang_diperiksa' ? 'selected' : '' ?>>Sedang Diperiksa</option>
                    <option value="perlu_revisi" <?= $statusFilter === 'perlu_revisi' ? 'selected' : '' ?>>Perlu Revisi</option>
                    <option value="selesai" <?= $statusFilter === 'selesai' ? 'selected' : '' ?>>Selesai / Sah</option>
                </select>
            </div>
            <?php if (!empty($typeFilter) || !empty($statusFilter)): ?>
                <a href="<?= url('/preparation/submissions?tab=details') ?>" class="btn btn-outline">Reset Filter</a>
            <?php endif; ?>

            <div style="margin-left: auto;">
                <button type="button" class="btn btn-primary" data-modal-open="addSubmissionModal">
                    ➕ Tambah Entri Dokumen
                </button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">No</th>
                        <th>Nama Guru Penyusun</th>
                        <th>Mata Pelajaran</th>
                        <th>Tingkat</th>
                        <th>Jenis Dokumen</th>
                        <th>Tanggal Serah</th>
                        <th>Status</th>
                        <th>Catatan &amp; Telaah</th>
                        <th>Petugas Penerima</th>
                        <th style="width: 90px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr>
                            <td colspan="10" class="text-center" style="padding: 30px; color: #94a3b8;">
                                Belum ada catatan checklist dokumen. Klik "Tambah Entri Dokumen" di atas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($submissions as $idx => $sub): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><strong><?= e($sub['teacher_name']) ?></strong></td>
                                <td><?= e($sub['subject_name']) ?></td>
                                <td><span class="badge badge-secondary"><?= e($sub['grade']) ?></span></td>
                                <td>
                                    <strong>
                                        <?= match($sub['doc_type']) {
                                            'kisi_kisi' => 'Kisi-kisi',
                                            'naskah_soal' => 'Naskah Soal',
                                            'lisan' => 'Ujian Lisan',
                                            'kunci_jawaban' => 'Kunci Jawaban',
                                            'dokumen_nilai' => 'Dokumen Nilai Guru',
                                            default => $sub['doc_type']
                                        } ?>
                                    </strong>
                                </td>
                                <td><?= format_date_id($sub['submission_date']) ?></td>
                                <td>
                                    <?php
                                    $badgeClass = match($sub['status']) {
                                        'selesai' => 'badge-success',
                                        'sudah_diserahkan' => 'badge-info',
                                        'sedang_diperiksa' => 'badge-warning',
                                        'perlu_revisi' => 'badge-danger',
                                        default => 'badge-secondary'
                                    };
                                    $statusLabel = match($sub['status']) {
                                        'belum_diserahkan' => 'Belum Diserahkan',
                                        'sudah_diserahkan' => 'Sudah Diserahkan',
                                        'sedang_diperiksa' => 'Sedang Diperiksa',
                                        'perlu_revisi' => 'Perlu Revisi',
                                        'selesai' => 'Selesai / Sah',
                                        default => $sub['status']
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
                                </td>
                                <td style="font-size: 12px;">
                                    <?= e($sub['notes'] ?: '-') ?>
                                    <?php if (!empty($sub['review_notes'])): ?>
                                        <div style="color: #dc2626; margin-top: 2px;">Catatan: <?= e($sub['review_notes']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 12px; color: #64748b;">
                                    <?= e($sub['reviewer_name'] ?: '-') ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline btn-sm" onclick='openUpdateStatusModal(<?= json_encode($sub) ?>)'>
                                        ✏️ Telaah
                                    </button>
                                    <form action="<?= url('/preparation/submissions/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus entri checklist ini?', this)">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus Checklist">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- MODAL 1: Tambah Guru & Mapel ke Matriks -->
<div class="modal-overlay" id="addMatrixRowModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tambah Guru &amp; Mata Pelajaran ke Ceklis (<?= $activeTab === 'oral' ? 'Ujian Lisan' : 'Ujian Tulis' ?>)</div>
            <button type="button" class="modal-close" data-modal-close="addMatrixRowModal">&times;</button>
        </div>
        <form action="<?= url('/preparation/submissions/add-row') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="tab" value="<?= $activeTab ?>">
            <div class="modal-body">
                <p style="font-size: 13px; color: #64748b; margin-top: 0;">
                    Menambahkan pasangan Guru dan Mata Pelajaran ke tabel rekapitulasi checklist <?= $activeTab === 'oral' ? 'ujian lisan/praktik' : 'ujian tulis' ?>.
                </p>
                <div class="form-group">
                    <label class="form-label required">Nama Pendidik / Guru</label>
                    <select name="teacher_id" class="form-control" required>
                        <option value="">-- Pilih Guru --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (NIP: <?= e($t['nip'] ?: '-') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label required">Mata Pelajaran</label>
                    <select name="subject_id" class="form-control" required>
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        <?php foreach ($subjects as $s): ?>
                            <?php 
                            $sExam = $s['exam_type'] ?? 'written';
                            if ($activeTab === 'oral' && !in_array($sExam, ['practical', 'both', 'lisan'])) {
                                continue;
                            }
                            if ($activeTab === 'written' && !in_array($sExam, ['written', 'both', ''])) {
                                continue;
                            }
                            ?>
                            <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (Kode: <?= e($s['code']) ?> - Kls: <?= e($s['grade']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addMatrixRowModal">Batal</button>
                <button type="submit" class="btn btn-primary">Tambahkan ke Matriks</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: Konfirmasi Sinkronkan Guru & Mapel -->
<div class="modal-overlay" id="syncConfirmModal">
    <div class="modal-container" style="max-width: 480px;">
        <div class="modal-header">
            <div class="modal-title">Sinkronkan Guru &amp; Mapel <?= $activeTab === 'oral' ? 'Ujian Lisan' : 'Ujian Tulis' ?></div>
            <button type="button" class="modal-close" data-modal-close="syncConfirmModal">&times;</button>
        </div>
        <form action="<?= url('/preparation/submissions/sync') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="tab" value="<?= $activeTab ?>">
            <div class="modal-body">
                <div style="font-size: 40px; text-align: center; margin-bottom: 12px;">🔄</div>
                <p style="font-size: 14px; text-align: center; color: #1e293b; margin: 0 0 10px 0; font-weight: 600;">
                    Apakah Anda ingin menyinkronkan daftar guru pengampu mata pelajaran <?= $activeTab === 'oral' ? 'ujian lisan/praktik' : 'ujian tulis' ?> aktif ke tabel checklist?
                </p>
                <p style="font-size: 13px; color: #64748b; text-align: center; line-height: 1.5; margin: 0;">
                    Sistem akan memastikan seluruh mata pelajaran yang relevan masuk ke dalam format rekapitulasi tanpa menghapus atau menimpa data yang telah ditandai selesai (v).
                </p>
            </div>
            <div class="modal-footer" style="justify-content: center; gap: 12px;">
                <button type="button" class="btn btn-secondary" data-modal-close="syncConfirmModal">Batal</button>
                <button type="submit" class="btn btn-primary">Ya, Sinkronkan Sekarang</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: Tambah Checklist Dokumen Tunggal (Rinci) -->
<div class="modal-overlay" id="addSubmissionModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tambah Entri Checklist Dokumen</div>
            <button type="button" class="modal-close" data-modal-close="addSubmissionModal">&times;</button>
        </div>
        <form action="<?= url('/preparation/submissions/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Nama Guru</label>
                    <select name="teacher_id" class="form-control" required>
                        <option value="">Pilih Guru</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (NIP: <?= e($t['nip'] ?: '-') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label required">Mata Pelajaran</label>
                            <select name="subject_id" class="form-control" required>
                                <option value="">Pilih Mapel</option>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= e($s['code']) ?> - <?= e($s['name']) ?> (<?= e($s['grade']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tingkat / Kelas</label>
                            <select name="grade" class="form-control" required>
                                <?php foreach ($grades as $g): ?>
                                    <option value="<?= e($g) ?>"><?= e($g) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jenis Dokumen</label>
                            <select name="doc_type" class="form-control" required>
                                <option value="naskah_soal" selected>Naskah Soal Ujian</option>
                                <option value="kisi_kisi">Kisi-kisi Soal</option>
                                <option value="lisan">Ujian Lisan / Praktik</option>
                                <option value="kunci_jawaban">Kunci Jawaban Administrasi</option>
                                <option value="dokumen_nilai">Status Penyerahan Dokumen Nilai Guru</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Status Awal</label>
                            <select name="status" class="form-control" required>
                                <option value="belum_diserahkan">Belum Diserahkan</option>
                                <option value="sudah_diserahkan">Sudah Diserahkan</option>
                                <option value="selesai">Selesai / Sah</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Tambahan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Keterangan kelengkapan dokumen..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addSubmissionModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Checklist</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 4: Update Status & Telaah -->
<div class="modal-overlay" id="updateStatusModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Telaah &amp; Perbarui Status Dokumen</div>
            <button type="button" class="modal-close" data-modal-close="updateStatusModal">&times;</button>
        </div>
        <form action="<?= url('/preparation/submissions/update-status') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="updateStatusId">
            <div class="modal-body">
                <div style="background: #f8fafc; padding: 12px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; border: 1px solid #e2e8f0;">
                    <div>Guru: <strong id="updateStatusTeacher"></strong></div>
                    <div>Dokumen: <strong id="updateStatusDoc"></strong> (<span id="updateStatusSubject"></span>)</div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Status Dokumen</label>
                    <select name="status" id="updateStatusSelect" class="form-control" required>
                        <option value="belum_diserahkan">Belum Diserahkan</option>
                        <option value="sudah_diserahkan">Sudah Diserahkan</option>
                        <option value="sedang_diperiksa">Sedang Diperiksa</option>
                        <option value="perlu_revisi">Perlu Revisi</option>
                        <option value="selesai">Selesai / Terverifikasi Sah</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Revisi / Hasil Telaah (Opsional)</label>
                    <textarea name="review_notes" id="updateReviewNotes" class="form-control" rows="3" placeholder="Tuliskan catatan jika ada hal yang perlu diperbaiki..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan Tambahan</label>
                    <input type="text" name="notes" id="updateStatusNotes" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="updateStatusModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Status Telaah</button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="matrixToast" style="position: fixed; bottom: 25px; right: 25px; background: #0f172a; color: #fff; padding: 12px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: none; z-index: 9999; align-items: center; gap: 8px; transition: opacity 0.3s ease;">
    <span id="matrixToastIcon">✓</span>
    <span id="matrixToastMsg">Status diperbarui</span>
</div>

<style>
/* Matrix Checklist Styles Matching image.png */
.table-matrix-checklist {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    font-size: 13px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
}
.table-matrix-checklist th,
.table-matrix-checklist td {
    border: 1px solid #cbd5e1;
    padding: 6px 4px;
    vertical-align: middle;
}
.table-matrix-checklist thead th {
    background-color: #f1f5f9;
    color: #1e293b;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}
.table-matrix-checklist .cell-center {
    text-align: center;
}
.table-matrix-checklist .cell-name-group {
    background-color: #ffffff;
    vertical-align: top;
    padding: 10px 12px;
    border-right: 1px solid #cbd5e1;
}
.table-matrix-checklist .cell-subject-title {
    padding-left: 10px;
    white-space: nowrap;
}
.table-matrix-checklist .cell-shaded {
    background-color: #cbd5e1 !important;
    cursor: not-allowed;
    text-align: center;
}
.table-matrix-checklist .cell-matrix-interactive {
    text-align: center;
    cursor: pointer;
    user-select: none;
    transition: background-color 0.15s ease;
    background-color: #ffffff;
}
.table-matrix-checklist .cell-matrix-interactive:hover {
    background-color: #f0fdf4 !important;
}
.table-matrix-checklist .cell-matrix-interactive.is-checked {
    background-color: #f8fafc;
}
/* Visual Interactive Box for direct filling */
.matrix-cell-control {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    margin: 0 auto;
    border: 2px dashed #94a3b8;
    border-radius: 6px;
    background-color: #f8fafc;
    cursor: pointer;
    transition: all 0.12s ease;
    outline: none;
    user-select: none;
    position: relative;
}
.matrix-cell-control:hover {
    border: 2px solid #2563eb;
    background-color: #eff6ff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
.matrix-cell-control:not(.is-checked):hover::after {
    content: '+';
    font-size: 15px;
    font-weight: 800;
    color: #2563eb;
    position: absolute;
}
.matrix-cell-control.is-checked {
    border: 2px solid #059669;
    background-color: #ecfdf5;
    color: #047857;
    box-shadow: none;
}
.matrix-cell-control.is-checked:hover {
    border-color: #dc2626;
    background-color: #fef2f2;
    color: #dc2626;
}
.matrix-cell-control:focus-visible {
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.35);
    border-color: #2563eb;
}
.matrix-check-symbol {
    font-weight: 800;
    font-size: 15px;
    line-height: 1;
    display: inline-block;
    color: #047857;
}
.matrix-cell-control.is-checked:hover .matrix-check-symbol {
    color: #dc2626;
}
.table-matrix-checklist .cell-total-box {
    text-align: center;
    font-weight: 700;
    color: #1e293b;
    background-color: #f8fafc;
}
.table-matrix-checklist tfoot td {
    background-color: #f1f5f9;
    border-top: 2px solid #94a3b8;
}
.table-matrix-checklist .cell-footer-total {
    text-align: center;
    font-weight: 800;
    color: #1e293b;
    font-size: 13px;
}
.table-matrix-checklist .cell-footer-grand {
    text-align: center;
    font-weight: 800;
    color: #1d4ed8;
    background-color: #e0f2fe;
    font-size: 13px;
}
.btn-icon-subtle {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 14px;
    padding: 2px 6px;
    border-radius: 4px;
    transition: all 0.2s ease;
}
.btn-icon-subtle:hover {
    background: #fee2e2;
    color: #ef4444;
}
</style>

<script>
/**
 * Optimistic UI Matrix Cell Toggle (0ms visual update)
 */
function toggleMatrixCell(targetEl, evt) {
    if (evt) {
        evt.stopPropagation();
    }
    const cellTd = targetEl.tagName === 'TD' ? targetEl : targetEl.closest('td');
    if (!cellTd || cellTd.classList.contains('cell-shaded')) return;

    const teacherId = cellTd.getAttribute('data-teacher-id');
    const subjectId = cellTd.getAttribute('data-subject-id');
    const grade = cellTd.getAttribute('data-grade');
    const docType = cellTd.getAttribute('data-doc-type');
    const colType = cellTd.getAttribute('data-col-type');
    const rowId = cellTd.getAttribute('data-row-id');

    const controlBox = cellTd.querySelector('.matrix-cell-control');
    const symbolSpan = cellTd.querySelector('.matrix-check-symbol');
    const rowTotalEl = document.getElementById('row-total-' + colType + '-' + rowId);
    const colTotalEl = document.getElementById('col-total-' + colType + '-' + grade);
    const grandTotalEl = document.getElementById('grand-total-' + colType);

    const prevChecked = cellTd.classList.contains('is-checked');
    const willBeChecked = !prevChecked;

    // 1. Optimistic DOM Update (Instant feedback)
    let rowTotal = parseInt(rowTotalEl ? rowTotalEl.innerText : 0) || 0;
    let colTotal = parseInt(colTotalEl ? colTotalEl.innerText : 0) || 0;
    let grandTotal = parseInt(grandTotalEl ? grandTotalEl.innerText : 0) || 0;

    if (willBeChecked) {
        cellTd.classList.add('is-checked');
        if (controlBox) {
            controlBox.classList.add('is-checked');
            controlBox.setAttribute('aria-checked', 'true');
        }
        if (symbolSpan) symbolSpan.innerText = 'v';
        if (rowTotalEl) rowTotalEl.innerText = rowTotal + 1;
        if (colTotalEl) colTotalEl.innerText = colTotal + 1;
        if (grandTotalEl) grandTotalEl.innerText = grandTotal + 1;
        showMatrixToast('✓ Ditandai selesai (v)', '#10b981');
    } else {
        cellTd.classList.remove('is-checked');
        if (controlBox) {
            controlBox.classList.remove('is-checked');
            controlBox.setAttribute('aria-checked', 'false');
        }
        if (symbolSpan) symbolSpan.innerText = '';
        if (rowTotalEl) rowTotalEl.innerText = Math.max(0, rowTotal - 1);
        if (colTotalEl) colTotalEl.innerText = Math.max(0, colTotal - 1);
        if (grandTotalEl) grandTotalEl.innerText = Math.max(0, grandTotal - 1);
        showMatrixToast('Ceklis dikosongkan', '#64748b');
    }

    // 2. Background Asynchronous Sync
    const params = new URLSearchParams();
    params.append('teacher_id', teacherId);
    params.append('subject_id', subjectId);
    params.append('grade', grade);
    params.append('doc_type', docType);
    params.append('_csrf_token', '<?= csrf_token() ?>');

    fetch('<?= url('/preparation/submissions/toggle-check') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: params.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            // Revert on server error
            revertCellState(cellTd, controlBox, symbolSpan, rowTotalEl, colTotalEl, grandTotalEl, prevChecked);
            alert(data.message || 'Gagal memperbarui status checklist.');
        }
    })
    .catch(error => {
        console.error('Toggle error:', error);
        // Revert on network error
        revertCellState(cellTd, controlBox, symbolSpan, rowTotalEl, colTotalEl, grandTotalEl, prevChecked);
        alert('Gagal menghubungi server. Perubahan dibatalkan.');
    });
}

function revertCellState(cellTd, controlBox, symbolSpan, rowTotalEl, colTotalEl, grandTotalEl, prevChecked) {
    let rowTotal = parseInt(rowTotalEl ? rowTotalEl.innerText : 0) || 0;
    let colTotal = parseInt(colTotalEl ? colTotalEl.innerText : 0) || 0;
    let grandTotal = parseInt(grandTotalEl ? grandTotalEl.innerText : 0) || 0;

    if (prevChecked) {
        cellTd.classList.add('is-checked');
        if (controlBox) {
            controlBox.classList.add('is-checked');
            controlBox.setAttribute('aria-checked', 'true');
        }
        if (symbolSpan) symbolSpan.innerText = 'v';
        if (rowTotalEl) rowTotalEl.innerText = rowTotal + 1;
        if (colTotalEl) colTotalEl.innerText = colTotal + 1;
        if (grandTotalEl) grandTotalEl.innerText = grandTotal + 1;
    } else {
        cellTd.classList.remove('is-checked');
        if (controlBox) {
            controlBox.classList.remove('is-checked');
            controlBox.setAttribute('aria-checked', 'false');
        }
        if (symbolSpan) symbolSpan.innerText = '';
        if (rowTotalEl) rowTotalEl.innerText = Math.max(0, rowTotal - 1);
        if (colTotalEl) colTotalEl.innerText = Math.max(0, colTotal - 1);
        if (grandTotalEl) grandTotalEl.innerText = Math.max(0, grandTotal - 1);
    }
}

function handleCellKeydown(controlEl, evt) {
    if (evt.key === ' ' || evt.key === 'Enter') {
        evt.preventDefault();
        toggleMatrixCell(controlEl, evt);
    }
}

function runBatchCheck(action) {
    const isOral = '<?= $activeTab ?>' === 'oral';
    const typeLabel = isOral ? 'ujian lisan' : 'ujian tulis';

    if (action === 'clear_all' && !confirm('Yakin ingin mengosongkan seluruh ceklis ' + typeLabel + '?')) {
        return;
    }
    if ((action === 'check_all_all' || action === 'check_all_written') && !confirm('Centang semua naskah dan kisi-kisi ' + typeLabel + ' sebagai selesai (v)?')) {
        return;
    }

    const params = new URLSearchParams();
    params.append('action', action);
    params.append('tab', '<?= $activeTab ?>');
    params.append('_csrf_token', '<?= csrf_token() ?>');

    showMatrixToast('Memproses isian cepat...', '#2563eb');

    fetch('<?= url('/preparation/submissions/batch-check') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: params.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showMatrixToast('✓ ' + data.message, '#10b981');
            setTimeout(() => { window.location.reload(); }, 400);
        } else {
            alert(data.message || 'Gagal memproses isian cepat.');
        }
    })
    .catch(err => {
        console.error('Batch check error:', err);
        // Fallback form submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= url('/preparation/submissions/batch-check') ?>';
        const inAction = document.createElement('input');
        inAction.type = 'hidden';
        inAction.name = 'action';
        inAction.value = action;
        form.appendChild(inAction);
        const inTab = document.createElement('input');
        inTab.type = 'hidden';
        inTab.name = 'tab';
        inTab.value = '<?= $activeTab ?>';
        form.appendChild(inTab);
        const inCsrf = document.createElement('input');
        inCsrf.type = 'hidden';
        inCsrf.name = '_csrf_token';
        inCsrf.value = '<?= csrf_token() ?>';
        form.appendChild(inCsrf);
        document.body.appendChild(form);
        form.submit();
    });
}

function showMatrixToast(msg, bg) {
    const toast = document.getElementById('matrixToast');
    const msgEl = document.getElementById('matrixToastMsg');
    if (!toast || !msgEl) return;

    msgEl.innerText = msg;
    toast.style.background = bg || '#0f172a';
    toast.style.display = 'flex';
    toast.style.opacity = '1';

    if (window._matrixToastTimeout) clearTimeout(window._matrixToastTimeout);
    window._matrixToastTimeout = setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 300);
    }, 2000);
}

function openUpdateStatusModal(sub) {
    document.getElementById('updateStatusId').value = sub.id;
    document.getElementById('updateStatusTeacher').textContent = sub.teacher_name;
    document.getElementById('updateStatusDoc').textContent = sub.doc_type;
    document.getElementById('updateStatusSubject').textContent = sub.subject_name + ' (' + sub.grade + ')';
    document.getElementById('updateStatusSelect').value = sub.status;
    document.getElementById('updateReviewNotes').value = sub.review_notes || '';
    document.getElementById('updateStatusNotes').value = sub.notes || '';
    openModal('updateStatusModal');
}
</script>
