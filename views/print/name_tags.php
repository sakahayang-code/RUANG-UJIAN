<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; padding: 0 !important; margin: 0 !important; }
}

.tags-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    padding: 15px;
}

.name-tag {
    border: 2px solid #0f172a;
    border-radius: 8px;
    padding: 15px;
    background: #fff;
    page-break-inside: avoid;
    text-align: center;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 320px;
}

.tag-header {
    border-bottom: 2px solid #0f172a;
    padding-bottom: 8px;
}

.tag-logo {
    width: 48px;
    height: 48px;
    object-fit: contain;
    margin-bottom: 5px;
}

.tag-school {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    color: #0f172a;
}

.tag-event {
    font-size: 10px;
    color: #475569;
}

.tag-role-banner {
    background: #0f172a;
    color: #fff;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 1px;
    padding: 4px 0;
    border-radius: 4px;
    margin: 10px 0;
}

.tag-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.tag-name {
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
}

.tag-position {
    font-size: 11px;
    color: #2563eb;
    font-weight: 600;
    margin-bottom: 2px;
}

.tag-nip {
    font-size: 10px;
    color: #64748b;
}

.tag-footer {
    display: flex;
    justify-content: center;
    align-items: center;
    border-top: 1px dashed #cbd5e1;
    padding-top: 8px;
}
</style>

<div class="no-print" style="background: #f8fafc; border-bottom: 1px solid #cbd5e1; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <strong>Cetak Name Tag <?= $type === 'panitia' ? 'Panitia' : 'Pengawas' ?></strong> (<?= count($tags) ?> Kartu)
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Name Tag</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>
</div>

<div class="tags-container">
    <?php if (empty($tags)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748b;">
            Belum ada data <?= $type === 'panitia' ? 'panitia' : 'pengawas' ?> yang dapat dicetak.
        </div>
    <?php else: ?>
        <?php foreach ($tags as $t): ?>
            <div class="name-tag">
                <div class="tag-header">
                    <?php if (!empty($school['logo_url'])): ?>
                        <img src="<?= e($school['logo_url']) ?>" alt="Logo" class="tag-logo">
                    <?php endif; ?>
                    <div class="tag-school"><?= e($school['name']) ?></div>
                    <div class="tag-event"><?= e($event['name']) ?> • TP <?= e($event['academic_year']) ?></div>
                </div>

                <div class="tag-role-banner">
                    <?= e($t['role_label']) ?>
                </div>

                <div class="tag-body">
                    <div class="tag-name"><?= e($t['name']) ?></div>
                    <div class="tag-position"><?= e($t['position']) ?></div>
                    <div class="tag-nip">NIP/ID: <?= e($t['nip']) ?></div>
                </div>

                <div class="tag-footer">
                    <div style="width: 60px; height: 60px;">
                        <?= $t['qr_svg'] ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
