<div class="page-header">
    <div>
        <h1 class="page-title">Rekapitulasi Penyerahan & Pengemasan Lembar Jawaban</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Rekonsiliasi total peserta hadir, naskah diterima, sisa, rusak, dan selisih lembar jawaban.</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('/answer-sheets/receipts') ?>" class="btn btn-secondary">
            📥 Penerimaan
        </a>
        <a href="<?= url('/answer-sheets/packing') ?>" class="btn btn-primary">
            ✉️ Pengemasan Amplop
        </a>
        <button type="button" class="btn btn-outline" onclick="window.print()">
            🖨️ Cetak Rekapitulasi
        </button>
    </div>
</div>

<!-- Statistik Ringkasan -->
<div class="stat-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
    <div class="card p-3">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase;">Total Peserta Hadir</div>
        <div style="font-size: 26px; font-weight: bold; color: #1e293b; margin-top: 5px;">
            <?= number_format((int)($stats['total_present'] ?? 0)) ?>
        </div>
        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Berdasarkan absensi ruang</div>
    </div>
    <div class="card p-3">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase;">Lembar Diterima</div>
        <div style="font-size: 26px; font-weight: bold; color: #10b981; margin-top: 5px;">
            <?= number_format((int)($stats['total_received'] ?? 0)) ?>
        </div>
        <div style="font-size: 12px; color: #10b981; margin-top: 2px;">Fisik lembar masuk</div>
    </div>
    <div class="card p-3">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase;">Lembar Dikemas (Packed)</div>
        <div style="font-size: 26px; font-weight: bold; color: #3b82f6; margin-top: 5px;">
            <?= number_format((int)$totalPacked) ?>
        </div>
        <div style="font-size: 12px; color: #3b82f6; margin-top: 2px;">Tersimpan dalam amplop</div>
    </div>
    <div class="card p-3">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase;">Sisa & Rusak</div>
        <div style="font-size: 26px; font-weight: bold; color: #64748b; margin-top: 5px;">
            <?= number_format((int)($stats['total_unused'] ?? 0)) ?> / <?= number_format((int)($stats['total_damaged'] ?? 0)) ?>
        </div>
        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Sisa / Lembar rusak</div>
    </div>
    <div class="card p-3" style="<?= (int)($stats['total_discrepancy'] ?? 0) !== 0 ? 'border: 2px solid #ef4444; background: #fff5f5;' : '' ?>">
        <div style="font-size: 12px; font-weight: 600; color: <?= (int)($stats['total_discrepancy'] ?? 0) !== 0 ? '#ef4444' : '#64748b' ?>; text-transform: uppercase;">Total Selisih</div>
        <div style="font-size: 26px; font-weight: bold; color: <?= (int)($stats['total_discrepancy'] ?? 0) !== 0 ? '#dc2626' : '#10b981' ?>; margin-top: 5px;">
            <?= (int)($stats['total_discrepancy'] ?? 0) ?>
        </div>
        <div style="font-size: 12px; color: <?= (int)($stats['total_discrepancy'] ?? 0) !== 0 ? '#dc2626' : '#10b981' ?>; margin-top: 2px;">
            <?= (int)($stats['total_discrepancy'] ?? 0) === 0 ? '✓ Tepat / Tidak Ada Selisih' : '⚠️ Ada Selisih Fisik!' ?>
        </div>
    </div>
</div>

<!-- Tabel Rekap Batch Pengemasan -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Paket Amplop Lembar Jawaban</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Kode Batch Amplop</th>
                    <th>Tanggal Packing</th>
                    <th>Mode</th>
                    <th>Klasifikasi Target</th>
                    <th class="text-center">Jumlah Amplop</th>
                    <th class="text-center">Total Lembar</th>
                    <th class="text-center">Status</th>
                    <th>Petugas</th>
                    <th class="text-center">Label Amplop</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($batches)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            Belum ada paket amplop yang dibuat.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($batches as $idx => $b): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><strong><?= e($b['batch_code']) ?></strong></td>
                            <td><?= e(indo_date($b['packing_date'], true)) ?></td>
                            <td>
                                <?php if ($b['packing_mode'] === 'per_kelas'): ?>
                                    <span class="badge badge-primary">Mode 1: Per Kelas</span>
                                <?php elseif ($b['packing_mode'] === 'per_mapel'): ?>
                                    <span class="badge badge-success">Mode 2: Per Mapel</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Mode 3: Per Kelas & Mapel</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= !empty($b['class_name']) ? 'Kelas ' . e($b['class_name']) : '' ?>
                                <?= !empty($b['subject_name']) ? (!empty($b['class_name']) ? ' • ' : '') . e($b['subject_name']) : '' ?>
                            </td>
                            <td class="text-center"><span class="badge badge-secondary"><?= (int)$b['envelope_count'] ?></span></td>
                            <td class="text-center"><strong><?= (int)$b['total_sheets'] ?></strong></td>
                            <td class="text-center">
                                <?php if ($b['status'] === 'Ada Selisih'): ?>
                                    <span class="badge badge-danger">⚠️ Ada Selisih</span>
                                <?php else: ?>
                                    <span class="badge badge-success">✓ Lengkap</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($b['packing_officer']) ?></td>
                            <td class="text-center">
                                <a href="<?= url('/print/answer-sheet-labels?batch_id=' . $b['id']) ?>" target="_blank" class="btn btn-sm btn-outline">
                                    🏷️ Label
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
