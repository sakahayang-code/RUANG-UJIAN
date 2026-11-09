<div class="page-header">
    <div>
        <h1 class="page-title">Struktur Kepanitiaan & Dokumen SK</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> (TP <?= e($event['academic_year']) ?> - <?= e($event['semester']) ?>)</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addMemberModal">
            ➕ Tambah Anggota Panitia
        </button>
        <button type="button" class="btn btn-secondary" data-modal-open="uploadSkModal">
            📄 Unggah Berkas SK Panitia
        </button>
    </div>
</div>

<!-- SK Panitia Overview Card -->
<div class="card" style="border-left: 4px solid #2563eb;">
    <div class="card-header">
        <div class="card-title">📜 Surat Keputusan (SK) Kepanitiaan Ujian</div>
    </div>
    <div class="card-body">
        <?php
        $skDoc = null;
        foreach ($documents as $doc) {
            if ($doc['doc_type'] === 'sk_panitia') {
                $skDoc = $doc;
                break;
            }
        }
        ?>
        <?php if ($skDoc): ?>
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #1e3a8a; margin-bottom: 4px;">
                        <?= e($skDoc['title']) ?>
                    </h3>
                    <div style="font-size: 13px; color: #475569;">
                        Nomor SK: <strong><?= e($skDoc['doc_number']) ?></strong> •
                        Tanggal SK: <strong><?= format_date_id($skDoc['doc_date']) ?></strong> •
                        Versi: <span class="badge badge-info">v<?= $skDoc['version'] ?></span> •
                        Status: <span class="badge badge-success"><?= e($skDoc['status']) ?></span>
                    </div>
                    <?php if (!empty($skDoc['description'])): ?>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;"><?= e($skDoc['description']) ?></div>
                    <?php endif; ?>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="<?= url('/file/view?path=' . urlencode($skDoc['file_path'])) ?>" target="_blank" class="btn btn-primary btn-sm">
                        👁️ Pratinjau Dokumen PDF
                    </a>
                    <a href="<?= url('/file/download?path=' . urlencode($skDoc['file_path'])) ?>" class="btn btn-outline btn-sm">
                        ⬇️ Unduh Berkas
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="color: #64748b; font-size: 13px;">
                    Belum ada dokumen SK Panitia resmi berformat PDF yang diunggah untuk kegiatan ujian ini.
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-modal-open="uploadSkModal">
                    Unggah SK Sekarang
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Committee Structure Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title">👥 Susunan Struktur Panitia Ujian</div>
        <span class="badge badge-info"><?= count($members) ?> Personel</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Jabatan dalam Panitia</th>
                    <th>Seksi / Bidang</th>
                    <th>Nama Pejabat / Guru</th>
                    <th>NIP</th>
                    <th>No. Kontak / WA</th>
                    <th style="width: 100px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada struktur anggota panitia yang ditambahkan. Silakan tambahkan anggota panitia.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($members as $idx => $m): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><strong><?= e($m['role_title']) ?></strong></td>
                            <td><?= e($m['section_name'] ?: '-') ?></td>
                            <td><strong><?= e($m['teacher_name'] ?: $m['custom_name']) ?></strong></td>
                            <td><?= e($m['teacher_nip'] ?: $m['custom_nip'] ?: '-') ?></td>
                            <td><?= e($m['phone'] ?: $m['teacher_phone'] ?: '-') ?></td>
                            <td class="text-center">
                                <form action="<?= url('/committee/delete-member') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus anggota ini dari kepanitiaan?', this)">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Anggota Panitia -->
<div class="modal-overlay" id="addMemberModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tambah Anggota Panitia Ujian</div>
            <button type="button" class="modal-close" data-modal-close="addMemberModal">&times;</button>
        </div>
        <form action="<?= url('/committee/store-member') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="committee_id" value="<?= $committee['id'] ?>">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jabatan Kepanitiaan</label>
                            <select name="role_title" class="form-control" required>
                                <option value="Penanggung Jawab">Penanggung Jawab</option>
                                <option value="Pelindung">Pelindung</option>
                                <option value="Ketua Panitia">Ketua Panitia</option>
                                <option value="Wakil Ketua">Wakil Ketua</option>
                                <option value="Sekretaris">Sekretaris</option>
                                <option value="Bendahara">Bendahara</option>
                                <option value="Koordinator Seksi">Koordinator Seksi</option>
                                <option value="Anggota Seksi">Anggota Seksi</option>
                                <option value="Anggota">Anggota</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Nama Seksi / Bidang (Opsional)</label>
                            <input type="text" name="section_name" class="form-control" placeholder="Contoh: Seksi Penggandaan, Seksi Distribusi">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Pilih dari Master Data Guru</label>
                    <select name="teacher_id" class="form-control" id="memberTeacherSelect" onchange="toggleCustomMemberInput(this)">
                        <option value="">-- Atau Ketik Nama Manual di Bawah --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (NIP: <?= e($t['nip'] ?: '-') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="customMemberBox">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Nama Anggota (Manual)</label>
                                <input type="text" name="custom_name" class="form-control" placeholder="Nama lengkap & gelar">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">NIP (Manual)</label>
                                <input type="text" name="custom_nip" class="form-control" placeholder="NIP jika ada">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">No. Telepon / WhatsApp</label>
                            <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Urutan Tampilan</label>
                            <input type="number" name="order_index" class="form-control" value="10">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addMemberModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Anggota</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Unggah SK Panitia -->
<div class="modal-overlay" id="uploadSkModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Unggah Dokumen SK Panitia Ujian (PDF)</div>
            <button type="button" class="modal-close" data-modal-close="uploadSkModal">&times;</button>
        </div>
        <form action="<?= url('/committee/upload-sk') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Judul Dokumen SK</label>
                    <input type="text" name="title" class="form-control" value="Surat Keputusan Pembentukan Panitia <?= e($event['name']) ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Nomor SK</label>
                            <input type="text" name="doc_number" class="form-control" placeholder="421.3/045/SMPN1/2026" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tanggal Penetapan SK</label>
                            <input type="date" name="doc_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label required">Pilih Berkas PDF</label>
                    <input type="file" name="file" class="form-control" accept="application/pdf" required>
                    <div class="form-text">Hanya format PDF resmi, maks 20MB.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi / Perihal</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Keterangan singkat penetapan SK..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="uploadSkModal">Batal</button>
                <button type="submit" class="btn btn-primary">Unggah & Simpan SK</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleCustomMemberInput(select) {
    const box = document.getElementById('customMemberBox');
    if (select.value) {
        box.style.display = 'none';
    } else {
        box.style.display = 'block';
    }
}
</script>
