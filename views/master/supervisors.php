<div class="page-header">
    <div>
        <h1 class="page-title">Data Pengawas Ruang</h1>
        <p class="page-subtitle"><?= e($event['name'] ?? 'Kegiatan Ujian') ?> • Manajemen manual data pengawas ruang ujian tulis, nomor kontak, dan penempatan ruang.</p>
    </div>
    <div class="page-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
        <button type="button" class="btn btn-primary" data-modal-open="addSupervisorModal">
            ➕ Tambah Pengawas
        </button>
        <button type="button" class="btn btn-outline" data-modal-open="importSupervisorModal" style="background: #ffffff;">
            📥 Impor Excel
        </button>
        <a href="<?= url('/master/supervisors/export') ?>" class="btn btn-outline" style="background: #ffffff;">
            📤 Ekspor Excel
        </a>
        <a href="<?= url('/written-exam/supervisors') ?>" class="btn btn-secondary" title="Atur jadwal penugasan sesi dan ruang">
            ⚙️ Atur Penugasan Sesi
        </a>
        <a href="<?= url('/attendance/supervisors/print') ?>" target="_blank" class="btn btn-secondary" title="Cetak presensi pengawas">
            🖨️ Cetak Presensi
        </a>
    </div>
</div>

<!-- Colorful Statistic Cards (Soft UI) -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <?php
    $totalSup = count($supervisors);
    $totalUtama = count(array_filter($supervisors, fn($s) => stripos($s['role'], 'utama') !== false || stripos($s['role'], 'pengawas ruang') !== false));
    $totalPendamping = count(array_filter($supervisors, fn($s) => stripos($s['role'], 'pendamping') !== false));
    $totalCadangan = count(array_filter($supervisors, fn($s) => stripos($s['role'], 'cadangan') !== false));
    ?>
    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Pengawas</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #4338ca;"><?= $totalSup ?></div>
            <div style="background: #eef2ff; color: #4338ca; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Orang</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Total guru & petugas pengawas</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Pengawas Utama / Ruang</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #0284c7;"><?= $totalUtama ?></div>
            <div style="background: #f0f9ff; color: #0284c7; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Bertugas</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Pengawas ruang ujian utama</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Pengawas Pendamping</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #d97706;"><?= $totalPendamping ?></div>
            <div style="background: #fffbeb; color: #d97706; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Pendamping</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Petugas pengawas ruang 2</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Pengawas Cadangan</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #059669;"><?= $totalCadangan ?></div>
            <div style="background: #ecfdf5; color: #059669; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Siaga</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Siaga di ruang panitia</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form method="GET" action="<?= url('/master/supervisors') ?>" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; flex: 1;">
            <div style="position: relative; min-width: 240px; flex: 1;">
                <input type="text" name="q" value="<?= e($search) ?>" class="form-control" placeholder="🔍 Cari nama, NIP, mapel, asal sekolah...">
            </div>

            <div style="min-width: 170px;">
                <select name="role" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Peran --</option>
                    <?php foreach ($rolesList as $r): ?>
                        <option value="<?= e($r) ?>" <?= $roleFilter === $r ? 'selected' : '' ?>><?= e($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="min-width: 170px;">
                <select name="room" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Ruang Tugas --</option>
                    <?php foreach ($roomsList as $rm): ?>
                        <option value="<?= e($rm) ?>" <?= $roomFilter === $rm ? 'selected' : '' ?>><?= e($rm) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-secondary">Filter</button>
            <?php if (!empty($search) || !empty($roleFilter) || !empty($roomFilter)): ?>
                <a href="<?= url('/master/supervisors') ?>" class="btn btn-outline">Reset</a>
            <?php endif; ?>
        </div>

        <?php if (!empty($supervisors)): ?>
            <div>
                <form action="<?= url('/master/supervisors/delete-all') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('PERINGATAN: Apakah Anda yakin ingin MENGOSONGKAN seluruh data pengawas ruang?', this)">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline btn-sm" style="color: #ef4444; border-color: #fca5a5;">
                        🗑️ Kosongkan Data
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- Data Table Card -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Nama Pengawas Ruang</th>
                    <th>Asal Sekolah / Lembaga</th>
                    <th>Mata Pelajaran</th>
                    <th class="text-center">Ruang Tugas</th>
                    <th class="text-center">Peran / Jabatan</th>
                    <th>No. HP / WhatsApp</th>
                    <th class="text-center">Status</th>
                    <th style="width: 110px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($supervisors)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 40px; color: #94a3b8;">
                            <div style="font-size: 32px; margin-bottom: 8px;">👥</div>
                            <div style="font-size: 15px; font-weight: 600; color: #475569;">Belum ada data pengawas ruang.</div>
                            <div style="font-size: 13px; color: #64748b; margin-top: 6px;">Silakan klik tombol <strong>"Tambah Pengawas"</strong> atau <strong>"Impor Excel"</strong> di atas.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($supervisors as $idx => $s): ?>
                        <tr>
                            <td class="text-center font-bold text-muted"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= e($s['name']) ?></strong>
                                <?php if (!empty($s['nip'])): ?>
                                    <div style="font-size: 11px; color: #64748b;">NIP: <?= e($s['nip']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($s['notes'])): ?>
                                    <div style="font-size: 11px; color: #94a3b8; font-style: italic;"><?= e($s['notes']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span><?= e($s['school_origin'] ?: 'Internal Sekolah') ?></span>
                            </td>
                            <td>
                                <?= e($s['subject'] ?: '-') ?>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($s['room_assignment'])): ?>
                                    <span class="badge badge-info" style="font-weight: 700;"><?= e($s['room_assignment']) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary" style="color: #94a3b8;">Belum Diatur</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php 
                                $badgeClass = 'badge-primary';
                                if (stripos($s['role'], 'pendamping') !== false) $badgeClass = 'badge-warning';
                                elseif (stripos($s['role'], 'cadangan') !== false) $badgeClass = 'badge-success';
                                elseif (stripos($s['role'], 'koordinator') !== false) $badgeClass = 'badge-danger';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= e($s['role']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($s['phone'])): ?>
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $s['phone']) ?>" target="_blank" style="color: #059669; text-decoration: none; font-weight: 600; font-size: 12px;">
                                        📱 <?= e($s['phone']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: #94a3b8;">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $s['status'] === 'Aktif' ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= e($s['status']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div style="display: flex; gap: 4px; justify-content: center;">
                                    <button type="button" class="btn btn-outline btn-sm" onclick='openEditSupervisorModal(<?= json_encode($s) ?>)' title="Edit Data">
                                        ✏️
                                    </button>
                                    <form action="<?= url('/master/supervisors/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus data pengawas <?= e($s['name']) ?>?', this)">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
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

<!-- Modal Tambah Pengawas -->
<div class="modal-overlay" id="addSupervisorModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tambah Data Pengawas Ruang</div>
            <button type="button" class="modal-close" data-modal-close="addSupervisorModal">&times;</button>
        </div>
        <form action="<?= url('/master/supervisors/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <!-- Pilihan Cepat Guru -->
                <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                    <label class="form-label" style="margin-bottom: 4px; font-size: 12px; color: #475569;">💡 Opsi Cepat: Pilih dari Data Guru Master</label>
                    <select id="quickTeacherSelect" class="form-control" onchange="applyQuickTeacher(this)">
                        <option value="">-- Ketik Manual atau Pilih Guru --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>" data-name="<?= e($t['name']) ?>" data-nip="<?= e($t['nip'] ?? '') ?>" data-phone="<?= e($t['phone'] ?? '') ?>" data-position="<?= e($t['position'] ?? '') ?>">
                                <?= e($t['name']) ?> (NIP: <?= e($t['nip'] ?: '-') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Nama Lengkap Pengawas</label>
                    <input type="text" name="name" id="addSupName" class="form-control" placeholder="Contoh: Drs. A. Nasai / Budi Santoso, M.Pd." required>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">NIP / NUPTK</label>
                        <input type="text" name="nip" id="addSupNip" class="form-control" placeholder="196803121995121001">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP / WhatsApp</label>
                        <input type="text" name="phone" id="addSupPhone" class="form-control" placeholder="08123456789">
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Asal Sekolah / Lembaga</label>
                        <input type="text" name="school_origin" class="form-control" value="Internal Sekolah" placeholder="SMP Negeri 1 Bojong">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mata Pelajaran yang Diampu</label>
                        <input type="text" name="subject" id="addSupSubject" class="form-control" placeholder="Bahasa Indonesia">
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Ruang Tugas</label>
                        <input type="text" name="room_assignment" list="roomListOptions" class="form-control" placeholder="Contoh: Ruang 01 / Cadangan">
                        <datalist id="roomListOptions">
                            <?php foreach ($rooms as $rm): ?>
                                <option value="Ruang <?= e($rm['room_number']) ?>"><?= e($rm['name']) ?></option>
                            <?php endforeach; ?>
                            <option value="Cadangan">Pengawas Cadangan</option>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Peran / Jabatan</label>
                        <select name="role" class="form-control" required>
                            <option value="Pengawas Ruang" selected>Pengawas Ruang</option>
                            <option value="Pengawas Utama">Pengawas Utama</option>
                            <option value="Pengawas Pendamping">Pengawas Pendamping</option>
                            <option value="Pengawas Cadangan">Pengawas Cadangan</option>
                            <option value="Koordinator Pengawas">Koordinator Pengawas</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="Aktif" selected>Aktif</option>
                            <option value="Cadangan">Cadangan</option>
                            <option value="Izin / Tidak Aktif">Izin / Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan / Catatan</label>
                        <input type="text" name="notes" class="form-control" placeholder="Opsional">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addSupervisorModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Pengawas</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pengawas -->
<div class="modal-overlay" id="editSupervisorModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Edit Data Pengawas Ruang</div>
            <button type="button" class="modal-close" data-modal-close="editSupervisorModal">&times;</button>
        </div>
        <form action="<?= url('/master/supervisors/update') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="editSupId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Nama Lengkap Pengawas</label>
                    <input type="text" name="name" id="editSupName" class="form-control" required>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">NIP / NUPTK</label>
                        <input type="text" name="nip" id="editSupNip" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP / WhatsApp</label>
                        <input type="text" name="phone" id="editSupPhone" class="form-control">
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Asal Sekolah / Lembaga</label>
                        <input type="text" name="school_origin" id="editSupSchool" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mata Pelajaran yang Diampu</label>
                        <input type="text" name="subject" id="editSupSubject" class="form-control">
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Ruang Tugas</label>
                        <input type="text" name="room_assignment" id="editSupRoom" list="roomListOptions" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Peran / Jabatan</label>
                        <select name="role" id="editSupRole" class="form-control" required>
                            <option value="Pengawas Ruang">Pengawas Ruang</option>
                            <option value="Pengawas Utama">Pengawas Utama</option>
                            <option value="Pengawas Pendamping">Pengawas Pendamping</option>
                            <option value="Pengawas Cadangan">Pengawas Cadangan</option>
                            <option value="Koordinator Pengawas">Koordinator Pengawas</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="editSupStatus" class="form-control">
                            <option value="Aktif">Aktif</option>
                            <option value="Cadangan">Cadangan</option>
                            <option value="Izin / Tidak Aktif">Izin / Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan / Catatan</label>
                        <input type="text" name="notes" id="editSupNotes" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editSupervisorModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Impor Excel -->
<div class="modal-overlay" id="importSupervisorModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Impor Data Pengawas Ruang dari Excel</div>
            <button type="button" class="modal-close" data-modal-close="importSupervisorModal">&times;</button>
        </div>
        <form action="<?= url('/master/supervisors/import') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                    <div style="font-weight: 600; color: #1e40af; margin-bottom: 4px;">📥 Unduh Format Template Terlebih Dahulu</div>
                    <div style="font-size: 13px; color: #3b82f6; margin-bottom: 10px;">
                        Gunakan berkas template resmi agar nama kolom sesuai secara otomatis saat diimpor.
                    </div>
                    <a href="<?= url('/master/supervisors/template') ?>" class="btn btn-outline btn-sm" style="background: #ffffff; color: #1d4ed8; border-color: #93c5fd;">
                        📄 Unduh Template Excel (.xlsx)
                    </a>
                </div>

                <div class="form-group">
                    <label class="form-label required">Pilih Berkas Excel / CSV</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <small style="color: #64748b; display: block; margin-top: 6px;">
                        Mendukung format <code>.xlsx</code>, <code>.xls</code>, atau <code>.csv</code>. Kolom yang dibaca: <em>Nama Pengawas, NIP, Asal Sekolah, Mata Pelajaran, Ruang Tugas, Peran, No HP, Status, Keterangan</em>.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="importSupervisorModal">Batal</button>
                <button type="submit" class="btn btn-primary">🚀 Mulai Impor Data</button>
            </div>
        </form>
    </div>
</div>

<script>
function applyQuickTeacher(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) return;

    document.getElementById('addSupName').value = opt.getAttribute('data-name') || '';
    document.getElementById('addSupNip').value = opt.getAttribute('data-nip') || '';
    document.getElementById('addSupPhone').value = opt.getAttribute('data-phone') || '';
    const pos = opt.getAttribute('data-position') || '';
    if (pos && pos.toLowerCase().includes('guru')) {
        document.getElementById('addSupSubject').value = pos.replace(/guru/i, '').trim();
    }
}

function openEditSupervisorModal(data) {
    document.getElementById('editSupId').value = data.id || '';
    document.getElementById('editSupName').value = data.name || '';
    document.getElementById('editSupNip').value = data.nip || '';
    document.getElementById('editSupPhone').value = data.phone || '';
    document.getElementById('editSupSchool').value = data.school_origin || 'Internal Sekolah';
    document.getElementById('editSupSubject').value = data.subject || '';
    document.getElementById('editSupRoom').value = data.room_assignment || '';
    document.getElementById('editSupRole').value = data.role || 'Pengawas Ruang';
    document.getElementById('editSupStatus').value = data.status || 'Aktif';
    document.getElementById('editSupNotes').value = data.notes || '';
    openModal('editSupervisorModal');
}
</script>
