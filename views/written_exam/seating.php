<div class="page-header">
    <div>
        <h1 class="page-title">Denah Tempat Duduk Peserta</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Visualisasi denah tata letak kursi peserta per baris dan kolom.</p>
    </div>
    <div class="page-actions">
        <?php if ($selectedRoom): ?>
            <a href="<?= url('/written-exam/seating/print?room_id=' . $selectedRoom['id']) ?>" target="_blank" class="btn btn-primary">
                🖨️ Cetak Denah Ruang & Daftar Meja
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 14px 20px;">
        <form method="GET" action="<?= url('/written-exam/seating') ?>" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <label style="font-weight: 600; font-size: 13.5px;">Pilih Ruang Ujian:</label>
            <select name="room_id" class="form-control" style="width: auto; min-width: 250px;" onchange="this.form.submit()">
                <?php foreach ($rooms as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= ($selectedRoom && $selectedRoom['id'] == $r['id']) ? 'selected' : '' ?>>
                        <?= e($r['room_number']) ?> - <?= e($r['name']) ?> (<?= $r['assigned_count'] ?>/<?= $r['capacity'] ?> Kursi)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<?php if (!$selectedRoom): ?>
    <div class="card">
        <div class="card-body text-center" style="padding: 40px; color: #94a3b8;">
            Belum ada ruang ujian yang dipilih atau tersedia.
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                🏛️ <?= e($selectedRoom['room_number']) ?>: <?= e($selectedRoom['name']) ?>
                <span style="font-size: 12px; font-weight: normal; color: #64748b; margin-left: 8px;">
                    (Kapasitas: <?= $selectedRoom['capacity'] ?> | Format: <?= $selectedRoom['rows_count'] ?> Baris × <?= $selectedRoom['cols_count'] ?> Kolom)
                </span>
            </div>
        </div>
        <div class="card-body">
            <!-- Front Teacher Desk / Whiteboard Indicator -->
            <div style="background: #1e293b; color: #fff; text-align: center; padding: 10px; border-radius: var(--radius-sm); margin-bottom: 30px; font-weight: bold; letter-spacing: 2px;">
                [ DEPAN : PAPAN TULIS / MEJA PENGAWAS ]
            </div>

            <!-- Seat Grid -->
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($seatsGrid as $rowNum => $cols): ?>
                    <div style="display: grid; grid-template-columns: repeat(<?= count($cols) ?>, 1fr); gap: 16px;">
                        <?php foreach ($cols as $colNum => $seat): ?>
                            <?php $p = $seat['participant']; ?>
                            <div style="border: 2px solid <?= $p ? '#2563eb' : '#cbd5e1' ?>; background: <?= $p ? '#eff6ff' : '#f8fafc' ?>; border-radius: var(--radius-md); padding: 12px; text-align: center; min-height: 100px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm);">
                                <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: bold; color: #475569; border-bottom: 1px dashed #cbd5e1; padding-bottom: 4px; margin-bottom: 6px;">
                                    <span>Meja #<?= $seat['seat_number'] ?></span>
                                    <span>R<?= $rowNum ?>-C<?= $colNum ?></span>
                                </div>
                                <?php if ($p): ?>
                                    <div style="font-weight: 700; font-size: 12.5px; color: #1e3a8a; line-height: 1.2;">
                                        <?= e($p['student_name_snapshot']) ?>
                                    </div>
                                    <div style="font-size: 11px; color: #334155; margin-top: 4px;">
                                        <code><?= e($p['participant_number']) ?></code>
                                    </div>
                                    <div style="margin-top: 4px;">
                                        <span class="badge badge-info" style="font-size: 10px;"><?= e($p['class_name_snapshot']) ?></span>
                                    </div>
                                <?php else: ?>
                                    <div style="color: #94a3b8; font-size: 12px; margin: auto;">
                                        (Kursi Kosong)
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Door Indicator -->
            <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
                <div style="background: #f1f5f9; border: 2px dashed #94a3b8; padding: 6px 16px; border-radius: var(--radius-sm); font-size: 12px; font-weight: bold; color: #475569;">
                    🚪 PINTU MASUK
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
