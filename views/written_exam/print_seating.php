<div style="font-size: 11pt; line-height: 1.4; font-family: 'Times New Roman', serif;">
    <div class="doc-title">
        <h2>DENAH TEMPAT DUDUK PESERTA UJIAN</h2>
        <p><?= e($event['name']) ?> - TAHUN PELAJARAN <?= e($event['academic_year']) ?></p>
        <div style="font-weight: bold; font-size: 12pt; margin-top: 4px;">
            <?= e($room['room_number']) ?> (<?= e($room['name']) ?>) - Kapasitas: <?= $room['capacity'] ?> Kursi
        </div>
    </div>

    <!-- Whiteboard Banner -->
    <div style="border: 2px solid #000; text-align: center; padding: 6px; font-weight: bold; margin-bottom: 25px; text-transform: uppercase;">
        [ PAPAN TULIS / MEJA PENGAWAS ]
    </div>

    <!-- Seating Grid -->
    <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 30px;">
        <?php foreach ($seatsGrid as $rowNum => $cols): ?>
            <div style="display: grid; grid-template-columns: repeat(<?= count($cols) ?>, 1fr); gap: 10px;">
                <?php foreach ($cols as $colNum => $seat): ?>
                    <?php $p = $seat['participant']; ?>
                    <div style="border: 1.5px solid #000; padding: 8px; text-align: center; min-height: 85px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div style="font-size: 9pt; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px;">
                            Meja <?= $seat['seat_number'] ?>
                        </div>
                        <?php if ($p): ?>
                            <div style="font-weight: bold; font-size: 9pt; margin: 3px 0;">
                                <?= e($p['student_name_snapshot']) ?>
                            </div>
                            <div style="font-size: 8pt;">
                                <?= e($p['participant_number']) ?><br>
                                Kelas: <?= e($p['class_name_snapshot']) ?>
                            </div>
                        <?php else: ?>
                            <div style="font-size: 8pt; font-style: italic; color: #666; margin: auto;">
                                (Kosong)
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Table of assigned participants -->
    <div style="font-weight: bold; font-size: 11pt; margin-bottom: 6px; page-break-before: always;">
        DAFTAR PESERTA <?= strtoupper(e($room['room_number'])) ?> (<?= count($assignments) ?> PESERTA)
    </div>
    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 50px;">Meja</th>
                <th>No. Peserta</th>
                <th>Nama Peserta Didik</th>
                <th>NIS</th>
                <th>Kelas Asal</th>
                <th>Tanda Tangan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assignments as $idx => $a): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td class="text-center font-bold">#<?= $a['seat_number'] ?></td>
                    <td><code><?= e($a['participant_number'] ?? '-') ?></code></td>
                    <td><strong><?= e($a['student_name_snapshot'] ?? '-') ?></strong></td>
                    <td class="text-center"><?= e(!empty($a['nis_snapshot']) ? $a['nis_snapshot'] : (!empty($a['nis']) ? $a['nis'] : '-')) ?></td>
                    <td class="text-center"><?= e($a['class_name_snapshot'] ?? '-') ?></td>
                    <td style="width: 120px; font-size: 8pt; color: #666;">
                        <?= $idx + 1 ?>. ....................
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="signature-block">
        <div></div>
        <div class="signature-box">
            <?= e($school['signature_city']) ?>, <?= format_date_id($event['start_date']) ?><br>
            Ketua Panitia Ujian,
            <div class="signature-space"></div>
            <div class="signature-name"><?= e($school['committee_head'] ?: 'Ketua Panitia') ?></div>
            <div>NIP. -</div>
        </div>
    </div>
</div>
