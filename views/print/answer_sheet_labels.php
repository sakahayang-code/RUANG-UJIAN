<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; padding: 0 !important; margin: 0 !important; }
    .page-break { page-break-after: always; }
}

.as-label-container {
    width: 100%;
    max-width: 780px;
    margin: 15px auto;
    border: 3px solid #0f172a;
    border-radius: 6px;
    padding: 16px 20px;
    box-sizing: border-box;
    background: #fff;
    font-size: 11.5px;
    page-break-inside: avoid;
}

.as-header {
    border-bottom: 2px solid #0f172a;
    padding-bottom: 8px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.as-logo {
    width: 48px;
    height: 48px;
    object-fit: contain;
}

.as-school-info {
    flex: 1;
    text-align: center;
}

.as-school-name {
    font-size: 14px;
    font-weight: 800;
    text-transform: uppercase;
    color: #0f172a;
}

.as-title {
    font-size: 13px;
    font-weight: 800;
    color: #15803d;
    margin-top: 2px;
}

.as-mode-badge {
    display: inline-block;
    padding: 3px 10px;
    background: #0f172a;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    border-radius: 3px;
    margin-top: 4px;
}

.as-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px 15px;
    margin-bottom: 12px;
    padding: 8px 10px;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
}

.as-meta-item {
    font-size: 11.5px;
}

.as-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    margin-bottom: 12px;
}

.as-table th, .as-table td {
    border: 1px solid #0f172a;
    padding: 5px 8px;
    font-size: 11px;
}

.as-table th {
    background: #e2e8f0;
    font-weight: 700;
    text-align: center;
}

.as-footer-box {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: 15px;
    padding-top: 10px;
    border-top: 1px dashed #94a3b8;
}
</style>

<div class="no-print" style="background: #f8fafc; border-bottom: 1px solid #cbd5e1; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <strong>Label Amplop Lembar Jawaban (3 Mode Pengemasan)</strong> • <?= count($labels) ?> Amplop
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Label Amplop Jawaban</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>
</div>

<?php if (empty($labels)): ?>
    <div style="text-align: center; padding: 50px; color: #64748b;">
        Belum ada data amplop lembar jawaban yang dibuat melalui modul pengemasan.
    </div>
<?php else: ?>
    <?php foreach ($labels as $idx => $lbl): ?>
        <div class="as-label-container <?= ($idx < count($labels) - 1) ? 'page-break' : '' ?>">
            <div class="as-header">
                <?php if (!empty($school['logo_url'])): ?>
                    <img src="<?= e($school['logo_url']) ?>" alt="Logo" class="as-logo">
                <?php else: ?>
                    <div style="width: 48px; height: 48px; border: 1px solid #000; display: flex; align-items: center; justify-content: center; font-weight: bold;">RU</div>
                <?php endif; ?>
                <div class="as-school-info">
                    <div class="as-school-name"><?= e($school['name']) ?></div>
                    <div class="as-title">BERKAS LEMBAR JAWABAN UJIAN (LJU)</div>
                    <div style="font-size: 10.5px; color: #334155;"><?= strtoupper(e($event['name'])) ?> • TP <?= e($event['academic_year']) ?></div>
                    <div>
                        <span class="as-mode-badge">
                            <?php if ($lbl['packing_mode'] === 'per_kelas'): ?>
                                MODE 1: PENGEMASAN PER KELAS ASAL
                            <?php elseif ($lbl['packing_mode'] === 'per_mapel'): ?>
                                MODE 2: PENGEMASAN PER MATA PELAJARAN
                            <?php else: ?>
                                MODE 3: PENGEMASAN PER KELAS & MATA PELAJARAN
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="as-meta-grid">
                <div class="as-meta-item">
                    Kode Batch: <strong><?= e($lbl['batch_code']) ?></strong>
                </div>
                <div class="as-meta-item">
                    Nomor Amplop: <strong style="font-size: 13px; color: #15803d; background: #dcfce7; padding: 2px 8px; border: 1px solid #86efac; border-radius: 3px;">Amplop Ke-<?= (int)$lbl['envelope_number'] ?> dari <?= (int)$lbl['total_envelopes'] ?></strong>
                </div>
                <div class="as-meta-item">
                    <?php if (!empty($lbl['class_name'])): ?>
                        Kelas Target: <strong style="font-size: 13px;"><?= e($lbl['class_name']) ?></strong>
                    <?php else: ?>
                        Target: <strong>Seluruh Kelas Asal Terdaftar</strong>
                    <?php endif; ?>
                </div>
                <div class="as-meta-item">
                    <?php if (!empty($lbl['subject_name'])): ?>
                        Mata Pelajaran: <strong><?= e($lbl['subject_name']) ?></strong>
                    <?php else: ?>
                        Mata Pelajaran: <strong>Seluruh Mata Pelajaran</strong>
                    <?php endif; ?>
                </div>
                <div class="as-meta-item">
                    Tanggal Pengemasan: <strong><?= e(indo_date($lbl['packing_date'], true)) ?></strong>
                </div>
                <div class="as-meta-item">
                    Total Lembar Jawaban: <strong style="font-size: 13px;"><?= (int)$lbl['total_sheets'] ?> Lembar</strong>
                </div>
            </div>

            <table class="as-table">
                <thead>
                    <tr>
                        <th style="width: 35px;">No</th>
                        <th>Ruang Asal</th>
                        <th>Kelas Asal</th>
                        <th>Mata Pelajaran</th>
                        <th style="width: 70px;">Hadir</th>
                        <th style="width: 80px;">Diterima</th>
                        <th style="width: 65px;">Sisa</th>
                        <th style="width: 65px;">Rusak</th>
                        <th style="width: 75px;">Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lbl['items'])): ?>
                        <tr>
                            <td colspan="9" class="text-center py-2">Rincian lembar jawaban tertera pada dokumen tanda terima.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lbl['items'] as $iIdx => $it): ?>
                            <tr>
                                <td class="text-center"><?= $iIdx + 1 ?></td>
                                <td class="text-center">Ruang <?= e($it['room_number']) ?></td>
                                <td><strong><?= e($it['class_name']) ?></strong></td>
                                <td><?= e($it['subject_code']) ?> - <?= e($it['subject_name']) ?></td>
                                <td class="text-center"><?= (int)$it['present_count'] ?></td>
                                <td class="text-center"><strong><?= (int)$it['received_count'] ?></strong></td>
                                <td class="text-center"><?= (int)$it['unused_count'] ?></td>
                                <td class="text-center"><?= (int)$it['damaged_count'] ?></td>
                                <td class="text-center">
                                    <?php if ((int)$it['discrepancy'] !== 0): ?>
                                        <strong style="color: #dc2626;"><?= (int)$it['discrepancy'] ?></strong>
                                    <?php else: ?>
                                        0
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="as-footer-box">
                <div style="font-size: 11px;">
                    <div>Status Berkas: <strong><?= e($lbl['batch_status']) ?></strong></div>
                    <div>Catatan: <em><?= e($lbl['batch_notes'] ?: 'Dokumen rapi & tersegel') ?></em></div>
                </div>
                <div style="text-align: center; font-size: 11px;">
                    <div>Petugas Pengemas (Packing),</div>
                    <div style="height: 35px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;"><?= e($lbl['packing_officer']) ?></div>
                    <div>Panitia Pelaksana Ujian</div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
