<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; padding: 0 !important; margin: 0 !important; }
    .page-break { page-break-after: always; }
}

.envelope-label-container {
    width: 100%;
    max-width: 780px;
    margin: 15px auto;
    border: 3px solid #000;
    border-radius: 6px;
    padding: 16px 20px;
    box-sizing: border-box;
    background: #fff;
    font-size: 11.5px;
    page-break-inside: avoid;
}

.envelope-header {
    border-bottom: 2px solid #000;
    padding-bottom: 8px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.env-logo {
    width: 50px;
    height: 50px;
    object-fit: contain;
}

.env-school-info {
    flex: 1;
    text-align: center;
}

.env-school-name {
    font-size: 14px;
    font-weight: 800;
    text-transform: uppercase;
    color: #000;
}

.env-exam-title {
    font-size: 13px;
    font-weight: 800;
    color: #000;
    margin-top: 2px;
}

.env-badge-title {
    font-size: 13px;
    font-weight: 900;
    background: #000;
    color: #fff;
    padding: 3px 10px;
    letter-spacing: 1px;
    display: inline-block;
    border-radius: 3px;
    margin-top: 4px;
}

.env-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px 15px;
    margin-bottom: 12px;
    padding: 8px 10px;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
}

.env-meta-item {
    font-size: 11.5px;
}

.env-meta-item strong {
    color: #000;
}

.env-breakdown-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    margin-bottom: 12px;
}

.env-breakdown-table th, .env-breakdown-table td {
    border: 1px solid #000;
    padding: 5px 8px;
    font-size: 11px;
}

.env-breakdown-table th {
    background: #e2e8f0;
    font-weight: 700;
    color: #000;
    text-align: center;
}

.env-total-row td {
    font-weight: 800;
    background: #f1f5f9;
}

.env-security-note {
    border: 1px dashed #475569;
    padding: 6px 10px;
    font-size: 10px;
    color: #334155;
    background: #f8fafc;
    border-radius: 4px;
    margin-top: 10px;
    text-align: justify;
}
</style>

<div class="no-print" style="background: #f8fafc; border-bottom: 1px solid #cbd5e1; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <strong>Label Amplop Naskah Soal (Ketentuan Resmi: 1 Ruang = 1 Amplop = 1 Label)</strong> • <?= count($labels) ?> Amplop
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <span style="font-size: 12px; color: #475569;">Cadangan: <strong><?= (int)$reservePerSubject ?> eks / mapel</strong></span>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Semua Label Amplop</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>
</div>

<?php if (empty($labels)): ?>
    <div style="text-align: center; padding: 50px; color: #64748b;">
        Tidak ada data jadwal dan ruang yang siap dicetak labelnya. Pastikan jadwal ujian dan alokasi ruang peserta telah diatur.
    </div>
<?php else: ?>
    <?php foreach ($labels as $idx => $lbl): ?>
        <div class="envelope-label-container <?= ($idx < count($labels) - 1) ? 'page-break' : '' ?>">
            <div class="envelope-header">
                <?php if (!empty($school['logo_url'])): ?>
                    <img src="<?= e($school['logo_url']) ?>" alt="Logo" class="env-logo">
                <?php else: ?>
                    <div style="width: 50px; height: 50px; border: 1px solid #000; display: flex; align-items: center; justify-content: center; font-weight: bold;">RU</div>
                <?php endif; ?>
                <div class="env-school-info">
                    <div class="env-school-name"><?= e($school['name']) ?></div>
                    <div class="env-exam-title">AMPLOP NASKAH SOAL • <?= strtoupper(e($event['name'])) ?></div>
                    <div style="font-size: 10.5px; color: #334155;">Tahun Pelajaran <?= e($event['academic_year']) ?> (Semester <?= e($event['semester']) ?>)</div>
                    <div><span class="env-badge-title">1 RUANG = 1 AMPLOP NASKAH SOAL</span></div>
                </div>
            </div>

            <div class="env-meta-grid">
                <div class="env-meta-item">
                    Hari / Tanggal: <strong><?= e(indo_day($lbl['exam_date'])) ?>, <?= e(indo_date($lbl['exam_date'], true)) ?></strong>
                </div>
                <div class="env-meta-item">
                    Ruang Ujian: <strong style="font-size: 14px; color: #000; background: #e2e8f0; padding: 1px 6px; border: 1px solid #000;">RUANG <?= e($lbl['room_number']) ?> (<?= e($lbl['room_name']) ?>)</strong>
                </div>
                <div class="env-meta-item">
                    Sesi / Waktu: <strong>Sesi <?= e($lbl['session']) ?> (<?= substr(e($lbl['start_time']), 0, 5) ?> - <?= substr(e($lbl['end_time']), 0, 5) ?> WIB)</strong>
                </div>
                <div class="env-meta-item">
                    Kelas Gabungan: <strong><?= implode(', ', array_map('e', $lbl['classes_list'])) ?></strong>
                </div>
            </div>

            <table class="env-breakdown-table">
                <thead>
                    <tr>
                        <th style="width: 35px;">No</th>
                        <th style="width: 70px;">Tingkat</th>
                        <th>Kelas Asal</th>
                        <th>Mata Pelajaran</th>
                        <th style="width: 80px;">Naskah Utama</th>
                        <th style="width: 75px;">Cadangan</th>
                        <th style="width: 85px;">Total Naskah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lbl['breakdown'] as $bIdx => $b): ?>
                        <tr>
                            <td class="text-center"><?= $bIdx + 1 ?></td>
                            <td class="text-center">Kelas <?= e($b['grade']) ?></td>
                            <td><strong><?= e($b['class_name']) ?></strong></td>
                            <td><?= e($b['subject_name']) ?></td>
                            <td class="text-center"><?= (int)$b['main_count'] ?> eks</td>
                            <td class="text-center"><?= (int)$b['reserve_count'] ?> eks</td>
                            <td class="text-center"><strong><?= (int)$b['total_count'] ?> eks</strong></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="env-total-row">
                        <td colspan="4" class="text-right" style="text-align: right; padding-right: 12px;">TOTAL KESELURUHAN DALAM AMPLOP INI:</td>
                        <td class="text-center"><?= (int)$lbl['total_main'] ?> eks</td>
                        <td class="text-center"><?= (int)$lbl['total_reserve'] ?> eks</td>
                        <td class="text-center" style="font-size: 12px; background: #e2e8f0;"><?= (int)$lbl['grand_total'] ?> EKS</td>
                    </tr>
                </tbody>
            </table>

            <div class="env-security-note">
                <strong>PETUNJUK PENGAWAS RUANG:</strong>
                <ol style="margin: 3px 0 0 16px; padding: 0;">
                    <li>Periksa segel amplop ini sebelum dibuka di hadapan peserta ujian.</li>
                    <li>Amplop ini hanya dibuka di dalam ruang ujian pada pukul <strong><?= substr(e($lbl['start_time']), 0, 5) ?> WIB</strong> setelah peserta siap.</li>
                    <li>Hitung kesesuaian jumlah naskah utama dan cadangan dengan daftar hadir peserta.</li>
                    <li>Lembar naskah cadangan dan sisa wajib dikembalikan utuh ke Sekretariat Panitia.</li>
                </ol>
                <div style="margin-top: 4px; font-style: italic; color: #64748b;">
                    * Catatan: Tanda terima serah terima naskah dicatat tersendiri pada Buku Berita Acara Distribusi Soal Sekretariat Panitia.
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
