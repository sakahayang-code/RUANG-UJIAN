<div class="page-header">
    <div>
        <h1 class="page-title">Struktur Kepanitiaan & Dokumen SK</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> (TP <?= e($event['academic_year']) ?> - <?= e($event['semester']) ?>)</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-outline" data-modal-open="uploadSignatureModal" title="Unggah Tanda Tangan Digital Ketua Panitia">
            ✍️ Tanda Tangan Ketua Panitia
        </button>
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
                    <th style="width: 140px;" class="text-center">Aksi</th>
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
                                <div style="display: inline-flex; gap: 4px; align-items: center;">
                                    <button type="button"
                                        class="btn btn-secondary btn-sm"
                                        data-member="<?= e(json_encode([
                                            'id' => $m['id'],
                                            'role_title' => $m['role_title'] ?? 'Anggota',
                                            'section_name' => $m['section_name'] ?? '',
                                            'teacher_id' => $m['teacher_id'] ?? '',
                                            'custom_name' => $m['custom_name'] ?? '',
                                            'custom_nip' => $m['custom_nip'] ?? '',
                                            'phone' => $m['phone'] ?? '',
                                            'order_index' => $m['order_index'] ?? 10,
                                        ], JSON_HEX_APOS | JSON_HEX_QUOT)) ?>"
                                        onclick="openEditMemberModal(this)">
                                        ✏️ Edit
                                    </button>
                                    <form action="<?= url('/committee/delete-member') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus anggota ini dari kepanitiaan?', this)">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">🗑️</button>
                                    </form>
                                </div>
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

<!-- Modal Edit Anggota Panitia -->
<div class="modal-overlay" id="editMemberModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">✏️ Edit Anggota Panitia Ujian</div>
            <button type="button" class="modal-close" data-modal-close="editMemberModal">&times;</button>
        </div>
        <form action="<?= url('/committee/update-member') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit_member_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jabatan Kepanitiaan</label>
                            <select name="role_title" id="edit_member_role_title" class="form-control" required>
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
                            <input type="text" name="section_name" id="edit_member_section_name" class="form-control" placeholder="Contoh: Seksi Penggandaan, Seksi Distribusi">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Pilih dari Master Data Guru</label>
                    <select name="teacher_id" class="form-control" id="edit_member_teacher_id" onchange="toggleEditCustomMemberInput(this)">
                        <option value="">-- Atau Ketik Nama Manual di Bawah --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (NIP: <?= e($t['nip'] ?: '-') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="editCustomMemberBox">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Nama Anggota (Manual)</label>
                                <input type="text" name="custom_name" id="edit_member_custom_name" class="form-control" placeholder="Nama lengkap & gelar">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">NIP (Manual)</label>
                                <input type="text" name="custom_nip" id="edit_member_custom_nip" class="form-control" placeholder="NIP jika ada">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">No. Telepon / WhatsApp</label>
                            <input type="text" name="phone" id="edit_member_phone" class="form-control" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Urutan Tampilan</label>
                            <input type="number" name="order_index" id="edit_member_order_index" class="form-control" value="10">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editMemberModal">Batal</button>
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
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

<!-- Upload Signature Modal -->
<div class="modal" id="uploadSignatureModal">
    <div class="modal-dialog">
        <form action="<?= url('/committee/upload-signature') ?>" method="POST" enctype="multipart/form-data" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h3 class="modal-title">✍️ Tanda Tangan Digital Ketua Panitia</h3>
                <button type="button" class="modal-close" data-modal-close="uploadSignatureModal">&times;</button>
            </div>
            <div class="modal-body">
                <?php 
                    $schoolData = $school ?? school_setting();
                    $hasSig = !empty($schoolData['committee_signature_path']) && file_exists(dirname(__DIR__, 2) . '/storage/uploads/' . $schoolData['committee_signature_path']); 
                ?>
                <div style="margin-bottom: 16px;">
                    <label class="form-label">Nama Ketua Panitia Saat Ini</label>
                    <input type="text" class="form-control" value="<?= e($schoolData['committee_head'] ?: 'Belum diatur di Pengaturan Sekolah') ?>" readonly style="background: #f1f5f9; color: #334155;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label class="form-label">Pratinjau Tanda Tangan</label>
                    <div style="height: 100px; border: 2px dashed #cbd5e1; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: #ffffff; background-image: linear-gradient(45deg, #f1f5f9 25%, transparent 25%), linear-gradient(-45deg, #f1f5f9 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f1f5f9 75%), linear-gradient(-45deg, transparent 75%, #f1f5f9 75%); background-size: 16px 16px; background-position: 0 0, 0 8px, 8px -8px, -8px 0px; overflow: hidden;">
                        <?php if ($hasSig): ?>
                            <img src="<?= url('/file/view?path=' . urlencode($schoolData['committee_signature_path'])) ?>" alt="Tanda Tangan Ketua Panitia" style="max-height: 90px; max-width: 90%; object-fit: contain;">
                        <?php else: ?>
                            <span style="color: #94a3b8; font-size: 13px;">Belum ada berkas tanda tangan yang diunggah</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Pilih Berkas Tanda Tangan</label>
                    <input type="file" name="committee_signature" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                    <div class="form-text" style="line-height: 1.4; margin-top: 6px;">
                        Disarankan berkas <strong>PNG transparan</strong> (atau JPG/WEBP/SVG, maks 5MB). Tanda tangan ini otomatis disematkan pada Kartu Peserta Ujian, Surat Undangan/Tugas, Daftar Hadir, dan Dokumen Panitia.
                    </div>
                </div>

                <?php if ($hasSig): ?>
                    <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                        <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: #dc2626; cursor: pointer; font-weight: 500;">
                            <input type="checkbox" name="remove_committee_signature" value="1">
                            Hapus tanda tangan digital ketua panitia saat ini
                        </label>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="uploadSignatureModal">Tutup</button>
                <button type="submit" class="btn btn-primary">Simpan Tanda Tangan</button>
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

function toggleEditCustomMemberInput(select) {
    const box = document.getElementById('editCustomMemberBox');
    if (select.value) {
        box.style.display = 'none';
    } else {
        box.style.display = 'block';
    }
}

function openEditMemberModal(btn) {
    try {
        const d = JSON.parse(btn.getAttribute('data-member'));
        document.getElementById('edit_member_id').value = d.id || '';
        document.getElementById('edit_member_role_title').value = d.role_title || 'Anggota';
        document.getElementById('edit_member_section_name').value = d.section_name || '';
        const teacherSelect = document.getElementById('edit_member_teacher_id');
        teacherSelect.value = d.teacher_id || '';
        document.getElementById('edit_member_custom_name').value = d.custom_name || '';
        document.getElementById('edit_member_custom_nip').value = d.custom_nip || '';
        document.getElementById('edit_member_phone').value = d.phone || '';
        document.getElementById('edit_member_order_index').value = d.order_index ?? 10;
        toggleEditCustomMemberInput(teacherSelect);
        openModal('editMemberModal');
    } catch (e) {
        console.error('Gagal membuka modal edit anggota:', e);
    }
}
</script>
