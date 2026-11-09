<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; padding: 0 !important; margin: 0 !important; }
}

.cards-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    padding: 10px;
}

.participant-card {
    border: 2px solid #1e293b;
    border-radius: 6px;
    padding: 10px 12px;
    background: #fff;
    page-break-inside: avoid;
    font-size: 11px;
    position: relative;
    box-sizing: border-box;
}

.card-header-inner {
    display: flex;
    align-items: center;
    border-bottom: 2px solid #0f172a;
    padding-bottom: 6px;
    margin-bottom: 8px;
    gap: 10px;
}

.card-logo {
    width: 42px;
    height: 42px;
    object-fit: contain;
}

.card-header-text {
    flex: 1;
    text-align: center;
}

.card-school-name {
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    color: #0f172a;
    line-height: 1.2;
}

.card-exam-title {
    font-size: 11px;
    font-weight: 700;
    color: #1e3a8a;
    margin-top: 2px;
}

.card-academic-year {
    font-size: 10px;
    color: #475569;
}

.card-body-inner {
    display: flex;
    gap: 10px;
}

.card-photo-box {
    width: 65px;
    height: 85px;
    border: 1px dashed #94a3b8;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    color: #64748b;
    text-align: center;
    padding: 2px;
    flex-shrink: 0;
}

.card-table {
    flex: 1;
    border-collapse: collapse;
}

.card-table td {
    padding: 2px 0;
    vertical-align: top;
    font-size: 10.5px;
}

.card-table .label {
    width: 80px;
    font-weight: 600;
    color: #334155;
}

.card-footer-inner {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: 8px;
    padding-top: 6px;
    border-top: 1px dotted #cbd5e1;
}

.qr-box {
    width: 60px;
    height: 60px;
}

.signature-box {
    text-align: center;
    font-size: 9.5px;
}
</style>

<div class="no-print" style="background: #f8fafc; border-bottom: 1px solid #cbd5e1; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <strong>Cetak Kartu Peserta Ujian</strong> (<?= count($participants) ?> Peserta)
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Kartu Sekarang</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>
</div>

<div class="cards-container">
    <?php if (empty($participants)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748b;">
            Tidak ada data peserta ujian yang sesuai filter.
        </div>
    <?php else: ?>
        <?php foreach ($participants as $p): ?>
            <div class="participant-card">
                <div class="card-header-inner">
                    <?php if (!empty($school['logo_url'])): ?>
                        <img src="<?= e($school['logo_url']) ?>" alt="Logo" class="card-logo">
                    <?php else: ?>
                        <div style="width: 42px; height: 42px; background: #e2e8f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;">RU</div>
                    <?php endif; ?>
                    <div class="card-header-text">
                        <div class="card-school-name"><?= e($school['name']) ?></div>
                        <div class="card-exam-title">KARTU PESERTA <?= strtoupper(e($event['name'])) ?></div>
                        <div class="card-academic-year">Tahun Pelajaran <?= e($event['academic_year']) ?> (Semester <?= e($event['semester']) ?>)</div>
                    </div>
                </div>

                <div class="card-body-inner">
                    <div class="card-photo-box">
                        <?php if (!empty($p['photo'])): ?>
                            <img src="<?= e($p['photo']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <span>FOTO<br>3 x 4</span>
                        <?php endif; ?>
                    </div>
                    <table class="card-table">
                        <tr>
                            <td class="label">Nomor Peserta</td>
                            <td style="width: 8px;">:</td>
                            <td><strong style="color: #0f172a; font-size: 11.5px;"><?= e($p['participant_number']) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="label">Nama Lengkap</td>
                            <td>:</td>
                            <td><strong><?= e($p['name']) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="label">NIS / NISN</td>
                            <td>:</td>
                            <td><?= e($p['nis']) ?> / <?= e($p['nisn']) ?></td>
                        </tr>
                        <tr>
                            <td class="label">Kelas Asal</td>
                            <td>:</td>
                            <td><?= e($p['class_name_snapshot'] ?: $p['class_name']) ?></td>
                        </tr>
                        <tr>
                            <td class="label">Ruang / Meja</td>
                            <td>:</td>
                            <td><span style="border: 1px solid #000; padding: 1px 5px; font-weight: bold;">Ruang <?= e($p['room_number'] ?? '-') ?></span> &nbsp; No Meja: <strong><?= e($p['seat_number'] ?? '-') ?></strong></td>
                        </tr>
                    </table>
                </div>

                <div class="card-footer-inner">
                    <div class="qr-box">
                        <?= $p['qr_svg'] ?>
                    </div>
                    <div class="signature-box">
                        <div><?= e($school['district'] ?? 'Tempat') ?>, <?= e(indo_date(date('Y-m-d'))) ?></div>
                        <div style="font-weight: 600;">Kepala Sekolah / Ketua Panitia,</div>
                        <div style="height: 35px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;"><?= e($school['principal_name']) ?></div>
                        <div>NIP. <?= e($school['principal_nip'] ?? '-') ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
