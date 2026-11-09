<div class="page-header">
    <div>
        <h1 class="page-title">Tata Tertib, Juknis & Prosedur Operasional Ujian</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Dokumen regulasi resmi pelaksanaan ujian sekolah.</p>
    </div>
</div>

<div class="card">
    <div class="tabs-nav" style="padding: 10px 16px 0;">
        <?php foreach ($rules as $idx => $r): ?>
            <button class="tab-btn <?= $idx === 0 ? 'active' : '' ?>" onclick="switchRuleTab('rule_tab_<?= $r['id'] ?>', this)">
                <?= e($r['title']) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="card-body">
        <?php foreach ($rules as $idx => $r): ?>
            <div id="rule_tab_<?= $r['id'] ?>" class="rule-tab-content" style="<?= $idx === 0 ? '' : 'display: none;' ?>">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                    <div>
                        <h2 style="font-size: 16px; font-weight: 700; color: #1e3a8a;"><?= e($r['title']) ?></h2>
                        <span style="font-size: 11px; color: #64748b;">Kode Dokumen: <code><?= e($r['rule_type']) ?></code></span>
                    </div>
                    <div>
                        <a href="<?= url('/preparation/rules/print?id=' . $r['id']) ?>" target="_blank" class="btn btn-outline btn-sm">
                            🖨️ Cetak Dokumen Resmi
                        </a>
                    </div>
                </div>

                <form action="<?= url('/preparation/rules/update') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">

                    <div class="form-group">
                        <label class="form-label required">Judul Dokumen</label>
                        <input type="text" name="title" class="form-control" value="<?= e($r['title']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Isi Butir Aturan / SOP / Petunjuk Teknis</label>
                        <textarea name="content" class="form-control" rows="12" style="font-family: monospace; font-size: 13px;" required><?= e($r['content']) ?></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary">
                            💾 Simpan Perubahan Regulasi
                        </button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function switchRuleTab(tabId, btn) {
    document.querySelectorAll('.rule-tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(tabId).style.display = 'block';
    btn.classList.add('active');
}
</script>
