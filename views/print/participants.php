<div class="no-print" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center; justify-content: space-between; background: #ffffff; padding: 12px 18px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.08); flex-wrap: wrap;">
    <form method="GET" action="<?= url('/print/participants') ?>" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <span style="font-size: 13px; font-weight: 700; color: #334155;">Filter Peserta:</span>
        <select name="class_id" class="form-control" style="font-size: 12px; padding: 5px 10px; width: auto;" onchange="this.form.submit()">
            <option value="">-- Semua Kelas --</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $selectedClassId == $c['id'] ? 'selected' : '' ?>>
                    Kelas <?= e($c['name']) ?> (Tingkat <?= e($c['grade']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <select name="room_id" class="form-control" style="font-size: 12px; padding: 5px 10px; width: auto;" onchange="this.form.submit()">
            <option value="">-- Semua Ruang Ujian --</option>
            <?php foreach ($rooms as $r): ?>
                <option value="<?= $r['id'] ?>" <?= $selectedRoomId == $r['id'] ? 'selected' : '' ?>>
                    Ruang <?= e($r['room_number']) ?> (<?= e($r['name']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($selectedClassId) || !empty($selectedRoomId)): ?>
            <a href="<?= url('/print/participants') ?>" class="btn btn-sm btn-outline" style="font-size: 12px;">Reset</a>
        <?php endif; ?>
    </form>
    <div style="display: flex; gap: 8px;">
        <button type="button" onclick="window.print()" class="btn btn-sm btn-primary" style="font-size: 12px; font-weight: 700;">
            🖨️ Cetak Daftar Peserta (Print)
        </button>
        <a href="<?= url('/print') ?>" class="btn btn-sm btn-outline" style="font-size: 12px;">
            ← Kembali ke Pusat Cetak
        </a>
    </div>
</div>

<div class="doc-title">
    <h2>DAFTAR PESERTA UJIAN</h2>
    <p>
        <?= e($event['name']) ?> • TAHUN PELAJARAN <?= e($event['academic_year'] ?? $school['academic_year']) ?>
        <?php if (!empty($selectedClassId)): ?>
            <?php 
                $foundClass = array_filter($classes, fn($cl) => $cl['id'] == $selectedClassId);
                $cName = !empty($foundClass) ? reset($foundClass)['name'] : '';
            ?>
            <br>Kelas: <?= e($cName) ?>
        <?php endif; ?>
        <?php if (!empty($selectedRoomId)): ?>
            <?php 
                $foundRoom = array_filter($rooms, fn($rm) => $rm['id'] == $selectedRoomId);
                $rName = !empty($foundRoom) ? reset($foundRoom)['name'] : '';
                $rNum = !empty($foundRoom) ? reset($foundRoom)['room_number'] : '';
            ?>
            <br>Ruang Ujian: <?= e($rNum) ?> (<?= e($rName) ?>)
        <?php endif; ?>
    </p>
</div>

<table class="table-print" style="margin-top: 15px; margin-bottom: 30px;">
    <thead>
        <tr>
            <th style="width: 35px;">No</th>
            <th style="width: 140px;">No. Peserta</th>
            <th style="width: 100px;">NISN</th>
            <th>Nama Lengkap Peserta</th>
            <th style="width: 45px;">L/P</th>
            <th style="width: 90px;">Kelas</th>
            <th style="width: 80px;">Ruang</th>
            <th style="width: 60px;">Meja</th>
            <th style="width: 120px;">Tanda Tangan</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($participants)): ?>
            <tr>
                <td colspan="9" class="text-center" style="padding: 30px; color: #64748b;">
                    Belum ada data peserta ujian yang sesuai filter.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($participants as $idx => $p): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td class="text-center font-bold"><code><?= e($p['participant_number'] ?: '-') ?></code></td>
                    <td class="text-center"><?= e($p['nisn'] ?: '-') ?></td>
                    <td><strong><?= e($p['student_name_snapshot'] ?: $p['name'] ?? '-') ?></strong></td>
                    <td class="text-center"><?= e($p['gender'] ?: '-') ?></td>
                    <td class="text-center"><?= e($p['class_name'] ?: '-') ?></td>
                    <td class="text-center">Ruang <?= e($p['room_number'] ?: '-') ?></td>
                    <td class="text-center font-bold"><?= e($p['seat_number'] ?: '-') ?></td>
                    <td style="font-size: 8pt; vertical-align: bottom; height: 26px;">
                        <?= ($idx % 2 === 0) ? ($idx + 1) . '. .........' : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . ($idx + 1) . '. .........' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Tanda Tangan Pengesahan -->
<div class="signature-block">
    <div class="signature-box">
        Mengetahui,<br>
        Kepala <?= e($school['name']) ?>,
        <div class="signature-space"></div>
        <div class="signature-name"><?= e($school['principal_name']) ?></div>
        <div>NIP. <?= e($school['principal_nip'] ?: '-') ?></div>
    </div>

    <div class="signature-box">
        <span style="white-space: nowrap;"><?= e(!empty($school['signature_city']) ? $school['signature_city'] : ($school['district'] ?? ($school['regency'] ?? 'Tempat'))) ?>, <?= format_date_id(date('Y-m-d')) ?></span><br>
        Ketua Panitia Ujian,
        <div class="signature-space" style="display: flex; align-items: center; justify-content: center;">
            <?php if (!empty($school['committee_signature_url'])): ?>
                <img src="<?= $school['committee_signature_url'] ?>" alt="Tanda Tangan" style="max-height: 65px; max-width: 140px; object-fit: contain;">
            <?php endif; ?>
        </div>
        <div class="signature-name">
            <?= e($chair['teacher_name'] ?? $chair['custom_name'] ?? $school['committee_head'] ?? '..................................') ?>
        </div>
        <div>NIP. <?= e($chair['teacher_nip'] ?? $chair['custom_nip'] ?? '-') ?></div>
    </div>
</div>
