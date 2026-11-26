<div class="page-header">
    <div>
        <h1 class="page-title">Tata Tertib, Juknis & Prosedur Operasional Ujian</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Dokumen regulasi resmi pelaksanaan ujian sekolah.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addRuleModal">
            ➕ Tambah Dokumen / SOP
        </button>
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
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <a href="<?= url('/preparation/rules/print?id=' . $r['id']) ?>" target="_blank" class="btn btn-outline btn-sm">
                            🖨️ Cetak Dokumen Resmi
                        </a>
                        <form action="<?= url('/preparation/rules/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus dokumen regulasi ini?', this)">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus Dokumen">🗑️</button>
                        </form>
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

<!-- Modal Tambah Dokumen / SOP Baru -->
<div class="modal-overlay" id="addRuleModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tambah Dokumen Regulasi / SOP Baru</div>
            <button type="button" class="modal-close" data-modal-close="addRuleModal">&times;</button>
        </div>
        <form action="<?= url('/preparation/rules/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Jenis / Kategori Regulasi</label>
                    <select name="rule_type" class="form-control" required>
                        <option value="tata_tertib_peserta">Tata Tertib Peserta</option>
                        <option value="tata_tertib_pengawas">Tata Tertib Pengawas Ruang</option>
                        <option value="tata_tertib_penguji">Tata Tertib Penguji Praktik</option>
                        <option value="petunjuk_teknis">Petunjuk Teknis Pelaksanaan</option>
                        <option value="prosedur_distribusi">Prosedur Distribusi Naskah Soal</option>
                        <option value="prosedur_pengumpulan">Prosedur Pengumpulan Lembar Jawaban</option>
                        <option value="prosedur_susulan">Prosedur Ujian Susulan</option>
                        <option value="dokumen_pendukung">Dokumen Pendukung Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label required">Judul Dokumen</label>
                    <input type="text" name="title" class="form-control" placeholder="Contoh: Petunjuk Teknis Ujian Praktik Laboratorium" required>
                </div>
                <div class="form-group">
                    <label class="form-label required">Isi Dokumen / Butir Aturan</label>
                    <textarea name="content" class="form-control" rows="8" placeholder="Tuliskan butir-butir aturan pelaksanaan ujian..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addRuleModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Dokumen</button>
            </div>
        </form>
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
