<div class="page-header">
    <div>
        <h1 class="page-title">Distribusi & Pengembalian Naskah Soal</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Pencatatan penyerahan naskah soal ke pengawas dan pengembalian sisa naskah ke panitia.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addHandoverModal">
            📦 Catat <?= $handoverType === 'pengambilan' ? 'Pengambilan' : 'Pengembalian' ?> Naskah
        </button>
        <a href="<?= url('/distribution/handover/print?type=' . $handoverType . '&date=' . $dateFilter) ?>" target="_blank" class="btn btn-outline">
            🖨️ Cetak Berkas
        </a>
        <a href="<?= url('/distribution/handover/export-excel?type=' . $handoverType . '&date=' . $dateFilter) ?>" class="btn btn-secondary">
            📤 Ekspor Excel
        </a>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="card" style="margin-bottom: 20px;">
    <div class="tabs-nav" style="padding: 10px 16px 0; margin-bottom: 0;">
        <a href="<?= url('/distribution/handover?type=pengambilan') ?>" class="tab-btn <?= $handoverType === 'pengambilan' ? 'active' : '' ?>">
            📤 Daftar Pengambilan Naskah (Sebelum Ujian)
        </a>
        <a href="<?= url('/distribution/handover?type=pengembalian') ?>" class="tab-btn <?= $handoverType === 'pengembalian' ? 'active' : '' ?>">
            📥 Daftar Pengembalian Naskah (Setelah Ujian)
        </a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Tanggal & Waktu</th>
                    <th class="text-center">Sesi</th>
                    <th class="text-center">Ruang</th>
                    <th>Mata Pelajaran</th>
                    <th class="text-center">Peserta</th>
                    <th class="text-center">Kebutuhan</th>
                    <th class="text-center">Jumlah <?= $handoverType === 'pengambilan' ? 'Diambil' : 'Kembali' ?></th>
                    <th class="text-center">Selisih</th>
                    <th>Pengawas</th>
                    <th>Petugas Panitia</th>
                    <th>Kondisi & Catatan</th>
                    <th style="width: 130px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="13" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada catatan <?= $handoverType ?> naskah soal.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($records as $idx => $r): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= format_day_id($r['exam_date']) ?></strong><br>
                                <span style="font-size: 11px; color: #64748b;"><?= format_date_id($r['exam_date']) ?></span><br>
                                <span style="font-size: 11px; font-weight: bold;"><?= e($r['handover_time']) ?> WIB</span>
                            </td>
                            <td class="text-center"><span class="badge badge-info">Sesi <?= e($r['session']) ?></span></td>
                            <td class="text-center"><strong><?= e($r['room_number']) ?></strong></td>
                            <td><strong><?= e($r['subject_name']) ?></strong> (<?= e($r['grade']) ?>)</td>
                            <td class="text-center"><?= $r['participant_count'] ?></td>
                            <td class="text-center font-bold" style="color: #1e3a8a;"><?= $r['required_count'] ?> eks</td>
                            <td class="text-center font-bold" style="font-size: 13px; color: #059669;"><?= $r['taken_or_returned_count'] ?> eks</td>
                            <td class="text-center">
                                <?php if ($r['discrepancy'] != 0): ?>
                                    <span class="badge badge-danger">Selisih <?= $r['discrepancy'] ?></span>
                                <?php else: ?>
                                    <span class="badge badge-success">Sesuai (0)</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px; font-weight: 600;"><?= e($r['supervisor_name']) ?></td>
                            <td style="font-size: 12px;"><?= e($r['officer_name']) ?></td>
                            <td style="font-size: 11px;">
                                <span class="badge badge-secondary"><?= e($r['condition_status']) ?></span>
                                <?php if (!empty($r['notes'])): ?>
                                    <div style="color: #64748b; margin-top: 2px;"><?= e($r['notes']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div style="display: inline-flex; gap: 4px; align-items: center;">
                                    <button type="button"
                                        class="btn btn-secondary btn-sm"
                                        data-handover="<?= e(json_encode([
                                            'id' => $r['id'],
                                            'handover_type' => $r['handover_type'] ?? $handoverType,
                                            'room_id' => $r['room_id'] ?? '',
                                            'taken_or_returned_count' => $r['taken_or_returned_count'] ?? 0,
                                            'handover_time' => $r['handover_time'] ?? '',
                                            'supervisor_name' => $r['supervisor_name'] ?? '',
                                            'officer_name' => $r['officer_name'] ?? '',
                                            'condition_status' => $r['condition_status'] ?? 'Baik / Lengkap',
                                            'notes' => $r['notes'] ?? '',
                                        ], JSON_HEX_APOS | JSON_HEX_QUOT)) ?>"
                                        onclick="openEditHandoverModal(this)">
                                        ✏️ Edit
                                    </button>
                                    <form action="<?= url('/distribution/handover/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus catatan distribusi naskah ini?', this)">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
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

<!-- Modal Catat Distribusi Naskah -->
<div class="modal-overlay" id="addHandoverModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Catat <?= $handoverType === 'pengambilan' ? 'Pengambilan' : 'Pengembalian' ?> Naskah Soal</div>
            <button type="button" class="modal-close" data-modal-close="addHandoverModal">&times;</button>
        </div>
        <form action="<?= url('/distribution/handover/store') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="handover_type" value="<?= e($handoverType) ?>">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label required">Pilih Jadwal Sesi & Mapel</label>
                            <select name="schedule_id" class="form-control" required>
                                <option value="">Pilih Jadwal</option>
                                <?php foreach ($schedules as $sch): ?>
                                    <option value="<?= $sch['id'] ?>">
                                        <?= $sch['exam_date'] ?> (Sesi <?= $sch['session'] ?>) - <?= e($sch['subject_name']) ?> (<?= e($sch['grade']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Pilih Ruang Ujian</label>
                            <select name="room_id" class="form-control" required>
                                <option value="">Pilih Ruang</option>
                                <?php foreach ($rooms as $rm): ?>
                                    <option value="<?= $rm['id'] ?>"><?= e($rm['room_number']) ?> - <?= e($rm['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jumlah Fisik Naskah <?= $handoverType === 'pengambilan' ? 'Diambil' : 'Dikembalikan' ?></label>
                            <input type="number" name="taken_or_returned_count" class="form-control" value="22" min="0" required>
                            <div class="form-text">Sistem otomatis menghitung selisih dengan kebutuhan resmi.</div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jam Pelaksanaan</label>
                            <input type="text" name="handover_time" class="form-control" value="<?= date('H:i') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Nama Pengawas yang <?= $handoverType === 'pengambilan' ? 'Menerima' : 'Menyerahkan' ?></label>
                            <input type="text" name="supervisor_name" class="form-control" placeholder="Nama guru pengawas..." required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Kondisi Fisik Naskah</label>
                            <select name="condition_status" class="form-control" required>
                                <option value="Baik / Lengkap">Baik / Lengkap</option>
                                <option value="Amplop Rusak / Terbuka">Amplop Rusak / Terbuka</option>
                                <option value="Terdapat Halaman Cacat">Terdapat Halaman Cacat</option>
                                <option value="Kurang dari Kebutuhan">Kurang dari Kebutuhan</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan / Tindak Lanjut Selisih (Wajib jika ada selisih)</label>
                    <input type="text" name="notes" class="form-control" placeholder="Alasan jika terjadi selisih naskah...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addHandoverModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Pencatatan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Distribusi Naskah -->
<div class="modal-overlay" id="editHandoverModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">✏️ Edit Pencatatan Distribusi Naskah</div>
            <button type="button" class="modal-close" data-modal-close="editHandoverModal">&times;</button>
        </div>
        <form action="<?= url('/distribution/handover/update') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="edit_ho_id">
            <input type="hidden" name="handover_type" id="edit_ho_type" value="<?= e($handoverType) ?>">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Ruang Ujian</label>
                            <select name="room_id" id="edit_ho_room_id" class="form-control" required>
                                <?php foreach ($rooms as $rm): ?>
                                    <option value="<?= $rm['id'] ?>"><?= e($rm['room_number']) ?> - <?= e($rm['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jumlah Fisik Naskah</label>
                            <input type="number" name="taken_or_returned_count" id="edit_ho_count" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jam Pelaksanaan</label>
                            <input type="text" name="handover_time" id="edit_ho_time" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Nama Pengawas</label>
                            <input type="text" name="supervisor_name" id="edit_ho_supervisor" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Petugas Panitia</label>
                            <input type="text" name="officer_name" id="edit_ho_officer" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Kondisi Fisik Naskah</label>
                            <select name="condition_status" id="edit_ho_condition" class="form-control" required>
                                <option value="Baik / Lengkap">Baik / Lengkap</option>
                                <option value="Amplop Rusak / Terbuka">Amplop Rusak / Terbuka</option>
                                <option value="Terdapat Halaman Cacat">Terdapat Halaman Cacat</option>
                                <option value="Kurang dari Kebutuhan">Kurang dari Kebutuhan</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Catatan / Tindak Lanjut</label>
                            <input type="text" name="notes" id="edit_ho_notes" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editHandoverModal">Batal</button>
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditHandoverModal(btn) {
    try {
        const d = JSON.parse(btn.getAttribute('data-handover'));
        document.getElementById('edit_ho_id').value = d.id || '';
        document.getElementById('edit_ho_type').value = d.handover_type || 'pengambilan';
        document.getElementById('edit_ho_room_id').value = d.room_id || '';
        document.getElementById('edit_ho_count').value = d.taken_or_returned_count ?? 0;
        document.getElementById('edit_ho_time').value = d.handover_time || '';
        document.getElementById('edit_ho_supervisor').value = d.supervisor_name || '';
        document.getElementById('edit_ho_officer').value = d.officer_name || '';
        document.getElementById('edit_ho_condition').value = d.condition_status || 'Baik / Lengkap';
        document.getElementById('edit_ho_notes').value = d.notes || '';
        openModal('editHandoverModal');
    } catch (e) {
        console.error('Gagal membuka modal edit distribusi:', e);
    }
}
</script>
