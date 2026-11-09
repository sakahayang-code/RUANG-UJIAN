<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; padding: 0 !important; margin: 0 !important; }
    .page-break { page-break-after: always; }
}

.room-sign-card {
    border: 5px solid #0f172a;
    border-radius: 12px;
    padding: 30px;
    margin: 20px auto;
    max-width: 800px;
    background: #fff;
    box-sizing: border-box;
    text-align: center;
}

.sign-header {
    border-bottom: 3px double #0f172a;
    padding-bottom: 15px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
}

.sign-logo {
    width: 70px;
    height: 70px;
    object-fit: contain;
}

.sign-school {
    font-size: 20px;
    font-weight: 800;
    text-transform: uppercase;
    color: #0f172a;
}

.sign-exam {
    font-size: 16px;
    font-weight: 700;
    color: #1e3a8a;
    margin-top: 4px;
}

.sign-academic {
    font-size: 14px;
    color: #475569;
}

.sign-room-number {
    font-size: 72px;
    font-weight: 900;
    color: #0f172a;
    letter-spacing: 2px;
    margin: 15px 0 5px 0;
    line-height: 1;
}

.sign-room-name {
    font-size: 22px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 25px;
}

.sign-details-box {
    border: 2px solid #cbd5e1;
    border-radius: 8px;
    padding: 15px 20px;
    background: #f8fafc;
    margin-top: 20px;
    text-align: left;
}

.sign-details-table {
    width: 100%;
    border-collapse: collapse;
}

.sign-details-table td {
    padding: 6px 4px;
    font-size: 15px;
}

.sign-details-table .lbl {
    width: 220px;
    font-weight: 700;
    color: #334155;
}
</style>

<div class="no-print" style="background: #f8fafc; border-bottom: 1px solid #cbd5e1; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <strong>Cetak Label Pintu Ruang Ujian</strong> (<?= count($rooms) ?> Ruang)
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Plang Pintu Ruang</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>
</div>

<?php foreach ($rooms as $idx => $r): ?>
    <div class="room-sign-card <?= ($idx < count($rooms) - 1) ? 'page-break' : '' ?>">
        <div class="sign-header">
            <?php if (!empty($school['logo_url'])): ?>
                <img src="<?= e($school['logo_url']) ?>" alt="Logo" class="sign-logo">
            <?php endif; ?>
            <div>
                <div class="sign-school"><?= e($school['name']) ?></div>
                <div class="sign-exam"><?= strtoupper(e($event['name'])) ?></div>
                <div class="sign-academic">Tahun Pelajaran <?= e($event['academic_year']) ?></div>
            </div>
        </div>

        <div style="font-size: 20px; font-weight: 700; color: #64748b; letter-spacing: 3px; text-transform: uppercase;">
            RUANG UJIAN
        </div>

        <div class="sign-room-number">
            <?= e($r['room_number']) ?>
        </div>

        <div class="sign-room-name">
            (<?= e($r['name']) ?>)
        </div>

        <div class="sign-details-box">
            <table class="sign-details-table">
                <tr>
                    <td class="lbl">Kelas yang Menempati</td>
                    <td style="width: 10px;">:</td>
                    <td>
                        <?php if (!empty($r['classes_list'])): ?>
                            <strong><?= implode(', ', array_map('e', $r['classes_list'])) ?></strong>
                        <?php else: ?>
                            <em>Belum diatur</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="lbl">Tingkat</td>
                    <td>:</td>
                    <td>
                        <?php if (!empty($r['grades_list'])): ?>
                            <strong>Tingkat <?= implode(', ', array_map('e', $r['grades_list'])) ?></strong>
                        <?php else: ?>
                            <em>-</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="lbl">Rentang Nomor Peserta</td>
                    <td>:</td>
                    <td>
                        <strong><?= e($r['first_no']) ?></strong> s.d. <strong><?= e($r['last_no']) ?></strong>
                    </td>
                </tr>
                <tr>
                    <td class="lbl">Kapasitas / Jumlah Peserta</td>
                    <td>:</td>
                    <td>
                        <strong><?= (int)$r['total_assigned'] ?> Peserta</strong> (Kapasitas Maksimal: <?= (int)$r['capacity'] ?> Meja)
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin-top: 30px; font-size: 12px; color: #64748b; font-style: italic;">
            Harap tenang, sedang berlangsung kegiatan ujian. Dilarang masuk selain peserta dan pengawas ujian.
        </div>
    </div>
<?php endforeach; ?>
