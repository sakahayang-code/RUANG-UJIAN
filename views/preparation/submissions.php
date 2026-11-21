<?php
/**
 * Checklist Persiapan & Penerimaan Dokumen Soal
 * Format Matriks Sesuai Standar Resmi (image.png) & Log Telaah Dokumen
 */
$grades = $matrixData['grades'];
$hasLisan = $matrixData['hasLisan'];
$hasTulis = $matrixData['hasTulis'];
$kelasColspan = ($hasLisan ? count($grades) : 0) + (count($grades) * 2);
$colTotals = $matrixData['columnTotals'];
$activeTab = $activeTab ?? 'matrix';

// Summary metrics
$totalTeachers = count($matrixData['teacherGroups']);
$totalRows = count($matrixData['flatRows']);
$totalSoalChecked = $colTotals['grand_soal'];
$totalKisiChecked = $colTotals['grand_kisi'];
$totalLisanChecked = $colTotals['grand_lisan'];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Checklist Persiapan &amp; Penerimaan Dokumen Soal</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Rekapitulasi penerimaan naskah soal, kisi-kisi, dan ujian lisan/praktik per guru, mata pelajaran, dan kelas.</p>
    </div>
    <div class="page-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="<?= url('/preparation/submissions/print') ?>" target="_blank" class="btn btn-outline" style="background: #fff;">
            🖨️ Cetak Format Rekap Soal
        </a>
        <a href="<?= url('/preparation/submissions/export') ?>" class="btn btn-outline" style="background: #fff;">
            📥 Unduh Excel (.xls)
        </a>
        <button type="button" class="btn btn-outline" style="background: #fff;" data-modal-open="addMatrixRowModal">
            ➕ Tambah Guru &amp; Mapel
        </button>
        <button type="button" class="btn btn-primary" data-modal-open="syncConfirmModal">
            🔄 Sinkronkan Semua
        </button>
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
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;"><?= $totalRows ?> mata pelajaran terdaftar</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Naskah Soal Tulis</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #059669;"><?= $totalSoalChecked ?></div>
            <div style="background: #ecfdf5; color: #059669; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Terkumpul</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Total naskah soal diverifikasi</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Kisi-Kisi Soal</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #0284c7;"><?= $totalKisiChecked ?></div>
            <div style="background: #f0f9ff; color: #0284c7; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Terkumpul</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Total kisi-kisi terverifikasi</div>
    </div>

    <?php if ($hasLisan): ?>
    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Ujian Lisan / Praktik</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #7c3aed;"><?= $totalLisanChecked ?></div>
            <div style="background: #f5f3ff; color: #7c3aed; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Terkumpul</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Kisi-kisi/instrumen lisan siap</div>
    </div>
    <?php endif; ?>
</div>

<!-- Tabs Navigation -->
<div style="display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; gap: 8px;">
    <a href="<?= url('/preparation/submissions?tab=matrix') ?>" 
       class="tab-btn <?= $activeTab === 'matrix' ? 'tab-btn-active' : '' ?>"
       style="padding: 10px 18px; font-weight: 700; font-size: 14px; text-decoration: none; border-bottom: 3px solid <?= $activeTab === 'matrix' ? '#3b82f6' : 'transparent' ?>; color: <?= $activeTab === 'matrix' ? '#1d4ed8' : '#64748b' ?>; display: inline-flex; align-items: center; gap: 6px;">
        📋 Format Rekap Matriks (Format Gambar Resmi)
    </a>
    <a href="<?= url('/preparation/submissions?tab=details') ?>" 
       class="tab-btn <?= $activeTab === 'details' ? 'tab-btn-active' : '' ?>"
       style="padding: 10px 18px; font-weight: 700; font-size: 14px; text-decoration: none; border-bottom: 3px solid <?= $activeTab === 'details' ? '#3b82f6' : 'transparent' ?>; color: <?= $activeTab === 'details' ? '#1d4ed8' : '#64748b' ?>; display: inline-flex; align-items: center; gap: 6px;">
        📑 Daftar Rinci &amp; Log Telaah (<?= count($submissions) ?>)
    </a>
</div>

<?php if ($activeTab === 'matrix'): ?>
    <!-- TAB 1: FORMAT REKAP MATRIKS SESUAI GAMBAR RESMI -->

    <!-- Filter & Guidance Bar -->
    <div class="card p-3 mb-2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <span style="font-size: 13px; font-weight: 600; color: #334155;">Filter Jenis Ujian:</span>
                <a href="<?= url('/preparation/submissions?tab=matrix&exam_type=all') ?>" class="btn btn-sm <?= ($matrixData['hasLisan'] && $matrixData['hasTulis']) ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 12px;">
                    Semua (Lisan &amp; Tulis)
                </a>
                <a href="<?= url('/preparation/submissions?tab=matrix&exam_type=written') ?>" class="btn btn-sm <?= (!$matrixData['hasLisan'] && $matrixData['hasTulis']) ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 12px;">
                    Hanya Ujian Tulis
                </a>
                <a href="<?= url('/preparation/submissions?tab=matrix&exam_type=practical') ?>" class="btn btn-sm <?= ($matrixData['hasLisan'] && !$matrixData['hasTulis']) ? 'btn-primary' : 'btn-outline' ?>" style="font-size: 12px;">
                    Hanya Ujian Lisan
                </a>
            </div>

            <!-- Legend info -->
            <div style="display: flex; align-items: center; gap: 16px; font-size: 12px; color: #475569;">
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <span class="matrix-cell-control is-checked" style="width: 20px; height: 20px; font-size: 12px; pointer-events: none;"><span class="matrix-check-symbol">v</span></span>
                    Terkumpul / Selesai (v)
                </span>
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <span class="matrix-cell-control" style="width: 20px; height: 20px; pointer-events: none;"></span>
                    Belum Terisi (Klik kotak untuk mengisi)
                </span>
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 20px; height: 20px; background: #cbd5e1; border: 1px solid #94a3b8; border-radius: 4px;"></span>
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
                <button type="button" class="btn btn-sm btn-outline" style="background: #ffffff; font-weight: 700; font-size: 12px; border-color: #059669; color: #059669;" onclick="runBatchCheck('check_all_soal')" title="Beri centang v untuk seluruh naskah soal yang ada">
                    ✓ Centang Semua Soal
                </button>
                <button type="button" class="btn btn-sm btn-outline" style="background: #ffffff; font-weight: 700; font-size: 12px; border-color: #0284c7; color: #0284c7;" onclick="runBatchCheck('check_all_kisi')" title="Beri centang v untuk seluruh kisi-kisi soal yang ada">
                    ✓ Centang Semua Kisi-Kisi
                </button>
                <?php if ($hasLisan): ?>
                <button type="button" class="btn btn-sm btn-outline" style="background: #ffffff; font-weight: 700; font-size: 12px; border-color: #7c3aed; color: #7c3aed;" onclick="runBatchCheck('check_all_lisan')" title="Beri centang v untuk seluruh ujian lisan/praktik">
                    ✓ Centang Semua Lisan
                </button>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-success" style="font-weight: 700; font-size: 12px;" onclick="runBatchCheck('check_all_all')" title="Centang seluruh naskah soal dan kisi-kisi">
                    ✓ Centang Semua (Lengkap)
                </button>
                <button type="button" class="btn btn-sm btn-outline" style="background: #ffffff; font-weight: 700; font-size: 12px; border-color: #ef4444; color: #ef4444;" onclick="runBatchCheck('clear_all')" title="Kosongkan seluruh tanda centang pada tabel">
                    ✕ Kosongkan Semua Ceklis
                </button>
            </div>
            
            <div style="font-size: 12px; color: #475569; font-weight: 600;">
                💡 <em>Klik langsung pada kotak cell di bawah untuk mengisi tanda ceklis (v).</em>
            </div>
        </div>
    </div>

    <!-- The Matrix Table Card -->
    <div class="card" style="border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
        <div class="table-responsive" style="max-height: 75vh; overflow: auto;">
            <table class="table-matrix-checklist" id="matrixChecklistTable">
                <thead>
                    <!-- Header Row 1 -->
                    <tr>
                        <th rowspan="3" style="width: 45px; text-align: center;">NO</th>
                        <th rowspan="3" style="width: 250px; text-align: left; padding-left: 12px;">NAMA PENDIDIK/GURU</th>
                        <th rowspan="3" style="width: 210px; text-align: left; padding-left: 12px;">MATA PELAJARAN</th>
                        <th colspan="<?= $kelasColspan ?>" style="text-align: center;">KELAS</th>
                        <th colspan="3" style="text-align: center;">JUMLAH</th>
                        <th rowspan="3" style="width: 50px; text-align: center;">AKSI</th>
                    </tr>
                    <!-- Header Row 2 -->
                    <tr>
                        <?php if ($hasLisan): ?>
                            <th colspan="<?= count($grades) ?>" style="text-align: center;">LISAN</th>
                        <?php endif; ?>
                        <?php foreach ($grades as $g): ?>
                            <th colspan="2" style="text-align: center;"><?= e((string)$g) ?></th>
                        <?php endforeach; ?>
                        <th rowspan="2" style="width: 55px; text-align: center;">LISAN</th>
                        <th colspan="2" style="text-align: center;">TULIS</th>
                    </tr>
                    <!-- Header Row 3 -->
                    <tr>
                        <?php if ($hasLisan): ?>
                            <?php foreach ($grades as $g): ?>
                                <th style="width: 44px; text-align: center;"><?= e((string)$g) ?></th>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php foreach ($grades as $g): ?>
                            <th style="width: 54px; text-align: center;">SOAL</th>
                            <th style="width: 54px; text-align: center;">KISI-KISI</th>
                        <?php endforeach; ?>
                        <th style="width: 54px; text-align: center;">SOAL</th>
                        <th style="width: 54px; text-align: center;">KISI-KISI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($matrixData['flatRows'])): ?>
                        <tr>
                            <td colspan="<?= 3 + $kelasColspan + 3 + 1 ?>" style="text-align: center; padding: 40px; color: #94a3b8;">
                                <div style="font-size: 32px; margin-bottom: 8px;">📋</div>
                                <div style="font-size: 15px; font-weight: 600; color: #475569;">Belum ada entri guru dan mata pelajaran pada checklist kegiatan ini.</div>
                                <div style="font-size: 13px; color: #64748b; margin-top: 6px;">Gunakan baris input di bawah atau tombol "Tambah Guru &amp; Mapel" di atas untuk memuat matriks.</div>
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

                                <!-- LISAN CELLS -->
                                <?php if ($hasLisan): ?>
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
                                                title="Klik untuk mengisi/mengosongkan: <?= e($row['teacher_name']) ?> - <?= e($row['subject_name']) ?> (Lisan Kelas <?= e($g) ?>)"
                                                onclick="toggleMatrixCell(this, event)">
                                                <div class="matrix-cell-control <?= $cell['checked'] ? 'is-checked' : '' ?>" tabindex="0" role="checkbox" aria-checked="<?= $cell['checked'] ? 'true' : 'false' ?>" onkeydown="handleCellKeydown(this, event)">
                                                    <span class="matrix-check-symbol"><?= $cell['checked'] ? 'v' : '' ?></span>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <!-- TULIS SOAL & KISI-KISI CELLS -->
                                <?php foreach ($grades as $g): ?>
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
                                            title="Klik untuk mengisi/mengosongkan: <?= e($row['teacher_name']) ?> - <?= e($row['subject_name']) ?> (Soal Kelas <?= e($g) ?>)"
                                            onclick="toggleMatrixCell(this, event)">
                                            <div class="matrix-cell-control <?= $cSoal['checked'] ? 'is-checked' : '' ?>" tabindex="0" role="checkbox" aria-checked="<?= $cSoal['checked'] ? 'true' : 'false' ?>" onkeydown="handleCellKeydown(this, event)">
                                                <span class="matrix-check-symbol"><?= $cSoal['checked'] ? 'v' : '' ?></span>
                                            </div>
                                        </td>
                                    <?php endif; ?>

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
                                            title="Klik untuk mengisi/mengosongkan: <?= e($row['teacher_name']) ?> - <?= e($row['subject_name']) ?> (Kisi-kisi Kelas <?= e($g) ?>)"
                                            onclick="toggleMatrixCell(this, event)">
                                            <div class="matrix-cell-control <?= $cKisi['checked'] ? 'is-checked' : '' ?>" tabindex="0" role="checkbox" aria-checked="<?= $cKisi['checked'] ? 'true' : 'false' ?>" onkeydown="handleCellKeydown(this, event)">
                                                <span class="matrix-check-symbol"><?= $cKisi['checked'] ? 'v' : '' ?></span>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <!-- ROW TOTALS -->
                                <td class="cell-total-box" id="row-total-lisan-<?= $row['row_no'] ?>"><?= $row['row_totals']['lisan'] ?></td>
                                <td class="cell-total-box" id="row-total-soal-<?= $row['row_no'] ?>"><?= $row['row_totals']['soal'] ?></td>
                                <td class="cell-total-box" id="row-total-kisi-<?= $row['row_no'] ?>"><?= $row['row_totals']['kisi'] ?></td>

                                <!-- ACTION COLUMN -->
                                <td class="cell-center">
                                    <form action="<?= url('/preparation/submissions/delete-row') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus baris guru & mapel ini dari matriks checklist?', this)">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="teacher_id" value="<?= $row['teacher_id'] ?>">
                                        <input type="hidden" name="subject_id" value="<?= $row['subject_id'] ?>">
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
                                <span style="font-size: 12px; font-weight: 700; color: #1e293b; white-space: nowrap;">➕ Tambah Baris:</span>
                                <select name="teacher_id" class="form-control form-control-sm" style="min-width: 180px; font-size: 12px; background: #fff;" required>
                                    <option value="">-- Pilih Guru --</option>
                                    <?php foreach ($teachers as $t): ?>
                                        <option value="<?= $t['id'] ?>"><?= e($t['name']) ?><?= !empty($t['nip']) ? ' (' . e($t['nip']) . ')' : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="subject_id" class="form-control form-control-sm" style="min-width: 180px; font-size: 12px; background: #fff;" required>
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    <?php foreach ($subjects as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['code']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary" style="font-size: 12px; padding: 5px 14px; font-weight: 600; white-space: nowrap;">
                                    Tambahkan
                                </button>
                            </form>
                        </td>
                        <td colspan="<?= $kelasColspan + 3 + 1 ?>" style="padding: 10px 12px; color: #64748b; font-size: 12px; font-style: italic;">
                            Baris baru akan langsung muncul pada tabel dan siap diisi ceklisnya.
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right; padding-right: 16px; font-weight: 800; font-size: 13px; color: #1e293b;">
                            JUMLAH
                        </td>
                        <?php if ($hasLisan): ?>
                            <?php foreach ($grades as $g): ?>
                                <td class="cell-footer-total" id="col-total-lisan-<?= e($g) ?>"><?= $colTotals['lisan'][$g] ?? 0 ?></td>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php foreach ($grades as $g): ?>
                            <td class="cell-footer-total" id="col-total-soal-<?= e($g) ?>"><?= $colTotals['tulis_soal'][$g] ?? 0 ?></td>
                            <td class="cell-footer-total" id="col-total-kisi-<?= e($g) ?>"><?= $colTotals['tulis_kisi'][$g] ?? 0 ?></td>
                        <?php endforeach; ?>
                        <td class="cell-footer-grand" id="grand-total-lisan"><?= $colTotals['grand_lisan'] ?></td>
                        <td class="cell-footer-grand" id="grand-total-soal"><?= $colTotals['grand_soal'] ?></td>
                        <td class="cell-footer-grand" id="grand-total-kisi"><?= $colTotals['grand_kisi'] ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

<?php else: ?>
    <!-- TAB 2: DAFTAR RINCI & LOG TELAAH DOKUMEN -->
    <div class="card p-3 mb-3">
        <form method="GET" action="<?= url('/preparation/submissions') ?>" class="filter-bar" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="tab" value="details">
            <div style="min-width: 180px;">
                <select name="type" class="form-control" onchange="this.form.submit()">
                    <option value="">Semua Jenis Dokumen</option>
                    <option value="kisi_kisi" <?= $typeFilter === 'kisi_kisi' ? 'selected' : '' ?>>Kisi-kisi Soal</option>
                    <option value="naskah_soal" <?= $typeFilter === 'naskah_soal' ? 'selected' : '' ?>>Naskah Soal Ujian</option>
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
            <div class="modal-title">Tambah Guru &amp; Mata Pelajaran ke Ceklis</div>
            <button type="button" class="modal-close" data-modal-close="addMatrixRowModal">&times;</button>
        </div>
        <form action="<?= url('/preparation/submissions/add-row') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <p style="font-size: 13px; color: #64748b; margin-top: 0;">
                    Menambahkan pasangan Guru dan Mata Pelajaran ke tabel rekapitulasi checklist naskah soal.
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

<!-- MODAL 2: Konfirmasi Sinkronkan Semua Guru & Mapel -->
<div class="modal-overlay" id="syncConfirmModal">
    <div class="modal-container" style="max-width: 480px;">
        <div class="modal-header">
            <div class="modal-title">Sinkronkan Semua Guru &amp; Mapel</div>
            <button type="button" class="modal-close" data-modal-close="syncConfirmModal">&times;</button>
        </div>
        <form action="<?= url('/preparation/submissions/sync') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div style="font-size: 40px; text-align: center; margin-bottom: 12px;">🔄</div>
                <p style="font-size: 14px; text-align: center; color: #1e293b; margin: 0 0 10px 0; font-weight: 600;">
                    Apakah Anda ingin menyinkronkan seluruh daftar guru dan mata pelajaran aktif ke tabel checklist?
                </p>
                <p style="font-size: 13px; color: #64748b; text-align: center; line-height: 1.5; margin: 0;">
                    Sistem akan otomatis memastikan seluruh guru pengampu dan mata pelajaran yang relevan masuk ke dalam format rekapitulasi naskah soal tanpa menimpa data yang telah ditandai selesai.
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
.table-matrix-checklist .cell-matrix-interactive.is-loading {
    opacity: 0.4;
    pointer-events: none;
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
    transition: all 0.15s ease;
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
function toggleMatrixCell(targetEl, evt) {
    if (evt) {
        evt.stopPropagation();
    }
    const cellTd = targetEl.tagName === 'TD' ? targetEl : targetEl.closest('td');
    if (!cellTd || cellTd.classList.contains('cell-shaded') || cellTd.classList.contains('is-loading')) return;

    const teacherId = cellTd.getAttribute('data-teacher-id');
    const subjectId = cellTd.getAttribute('data-subject-id');
    const grade = cellTd.getAttribute('data-grade');
    const docType = cellTd.getAttribute('data-doc-type');
    const colType = cellTd.getAttribute('data-col-type');
    const rowId = cellTd.getAttribute('data-row-id');

    cellTd.classList.add('is-loading');

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
        cellTd.classList.remove('is-loading');
        if (data.success) {
            const controlBox = cellTd.querySelector('.matrix-cell-control');
            const symbolSpan = cellTd.querySelector('.matrix-check-symbol');
            const rowTotalEl = document.getElementById('row-total-' + colType + '-' + rowId);
            const colTotalEl = document.getElementById('col-total-' + colType + '-' + grade);
            const grandTotalEl = document.getElementById('grand-total-' + colType);

            let rowTotal = parseInt(rowTotalEl ? rowTotalEl.innerText : 0) || 0;
            let colTotal = parseInt(colTotalEl ? colTotalEl.innerText : 0) || 0;
            let grandTotal = parseInt(grandTotalEl ? grandTotalEl.innerText : 0) || 0;

            if (data.checked) {
                cellTd.classList.add('is-checked');
                if (controlBox) {
                    controlBox.classList.add('is-checked');
                    controlBox.setAttribute('aria-checked', 'true');
                }
                if (symbolSpan) {
                    symbolSpan.innerText = 'v';
                }
                if (rowTotalEl) rowTotalEl.innerText = rowTotal + 1;
                if (colTotalEl) colTotalEl.innerText = colTotal + 1;
                if (grandTotalEl) grandTotalEl.innerText = grandTotal + 1;
                showMatrixToast('✓ ' + (data.message || 'Ceklis berhasil ditandai selesai.'), '#10b981');
            } else {
                cellTd.classList.remove('is-checked');
                if (controlBox) {
                    controlBox.classList.remove('is-checked');
                    controlBox.setAttribute('aria-checked', 'false');
                }
                if (symbolSpan) {
                    symbolSpan.innerText = '';
                }
                if (rowTotalEl) rowTotalEl.innerText = Math.max(0, rowTotal - 1);
                if (colTotalEl) colTotalEl.innerText = Math.max(0, colTotal - 1);
                if (grandTotalEl) grandTotalEl.innerText = Math.max(0, grandTotal - 1);
                showMatrixToast('Ceklis naskah dikosongkan', '#64748b');
            }
        } else {
            alert(data.message || 'Gagal memperbarui status checklist.');
        }
    })
    .catch(error => {
        cellTd.classList.remove('is-loading');
        console.error('Toggle error:', error);
        alert('Gagal menghubungi server.');
    });
}

function handleCellKeydown(controlEl, evt) {
    if (evt.key === ' ' || evt.key === 'Enter') {
        evt.preventDefault();
        toggleMatrixCell(controlEl, evt);
    }
}

function runBatchCheck(action) {
    if (action === 'clear_all' && !confirm('Yakin ingin mengosongkan seluruh ceklis pada format rekap dokumen ini?')) {
        return;
    }
    if (action === 'check_all_all' && !confirm('Centang semua naskah soal dan kisi-kisi sebagai selesai (v)?')) {
        return;
    }

    const params = new URLSearchParams();
    params.append('action', action);
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
            setTimeout(() => { window.location.reload(); }, 500);
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
