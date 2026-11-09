<div style="font-size: 11pt; line-height: 1.4; font-family: 'Times New Roman', serif;">
    <div class="doc-title">
        <h2>DAFTAR <?= strtoupper(e($handoverType)) ?> NASKAH SOAL UJIAN</h2>
        <p><?= e($event['name']) ?> - TAHUN PELAJARAN <?= e($event['academic_year']) ?></p>
        <?php if (!empty($dateFilter)): ?>
            <div style="font-weight: bold; margin-top: 4px;">
                Tanggal: <?= format_date_id($dateFilter) ?>
            </div>
        <?php endif; ?>
    </div>

    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th style="width: 80px;">Tgl / Jam</th>
                <th style="width: 45px;">Sesi</th>
                <th style="width: 65px;">Ruang</th>
                <th>Mata Pelajaran</th>
                <th style="width: 45px;">Tkt</th>
                <th style="width: 50px;">Peserta</th>
                <th style="width: 65px;">Kebutuhan</th>
                <th style="width: 65px;">Jumlah <?= ucfirst(e($handoverType)) ?></th>
                <th style="width: 50px;">Selisih</th>
                <th>Pengawas</th>
                <th>Petugas</th>
                <th style="width: 100px;">Paraf</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="13" class="text-center" style="padding: 20px;">Belum ada catatan <?= $handoverType ?> naskah.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($records as $idx => $r): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td class="text-center" style="font-size: 9pt;">
                            <?= e($r['exam_date']) ?><br>
                            <?= e($r['handover_time']) ?>
                        </td>
                        <td class="text-center"><?= e($r['session']) ?></td>
                        <td class="text-center"><strong><?= e($r['room_number']) ?></strong></td>
                        <td><strong><?= e($r['subject_name']) ?></strong></td>
                        <td class="text-center"><?= e($r['grade']) ?></td>
                        <td class="text-center"><?= $r['participant_count'] ?></td>
                        <td class="text-center"><?= $r['required_count'] ?></td>
                        <td class="text-center font-bold"><?= $r['taken_or_returned_count'] ?></td>
                        <td class="text-center <?= $r['discrepancy'] != 0 ? 'font-bold' : '' ?>" style="<?= $r['discrepancy'] != 0 ? 'color: red;' : '' ?>">
                            <?= $r['discrepancy'] ?>
                        </td>
                        <td style="font-size: 9.5pt;"><?= e($r['supervisor_name']) ?></td>
                        <td style="font-size: 9.5pt;"><?= e($r['officer_name']) ?></td>
                        <td style="font-size: 8pt; color: #555; vertical-align: middle; height: 35px;">
                            <?= $idx + 1 ?>. ....................
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="signature-block">
        <div class="signature-box">
            Mengetahui,<br>
            Kepala <?= e($school['name']) ?>
            <div class="signature-space"></div>
            <div class="signature-name"><?= e($school['principal_name']) ?></div>
            <div>NIP. <?= e($school['principal_nip']) ?></div>
        </div>

        <div class="signature-box">
            <?= e($school['signature_city']) ?>, <?= format_date_id($dateFilter ?: $event['start_date']) ?><br>
            Koordinator Seksi Naskah & Distribusi,
            <div class="signature-space"></div>
            <div class="signature-name"><?= e($school['committee_secretary'] ?: 'Panitia Ujian') ?></div>
            <div>NIP. -</div>
        </div>
    </div>
</div>
