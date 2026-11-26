<div class="page-header">
    <div>
        <h1 class="page-title">Data Penguji Praktik</h1>
        <p class="page-subtitle"><?= e($event['name'] ?? 'Kegiatan Ujian') ?> • Manajemen manual data guru penguji ujian praktik per mata pelajaran, tingkat kelas, dan lokasi uji.</p>
    </div>
    <div class="page-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
        <button type="button" class="btn btn-primary" data-modal-open="addExaminerModal">
            ➕ Tambah Penguji
        </button>
        <button type="button" class="btn btn-outline" data-modal-open="importExaminerModal" style="background: #ffffff;">
            📥 Impor Excel
        </button>
        <a href="<?= url('/master/examiners/export') ?>" class="btn btn-outline" style="background: #ffffff;">
            📤 Ekspor Excel
        </a>
        <a href="<?= url('/attendance/examiners/print') ?>" target="_blank" class="btn btn-secondary" title="Cetak presensi penguji praktik">
            🖨️ Cetak Presensi
        </a>
        <a href="<?= url('/practical-exam') ?>" class="btn btn-secondary" title="Kelola jadwal rotasi dan pos praktik">
            🧪 Jadwal Praktik
        </a>
    </div>
</div>

<!-- Colorful Statistic Cards (Soft UI) -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <?php
    $totalEx = count($examiners);
    $uniqueSubjects = count(array_unique(array_filter(array_column($examiners, 'subject_name'))));
    $totalUtama = count(array_filter($examiners, fn($e) => stripos($e['examiner_role'], 'utama') !== false));
    $totalPendamping = count(array_filter($examiners, fn($e) => stripos($e['examiner_role'], 'pendamping') !== false || stripos($e['examiner_role'], 'eksternal') !== false));
    ?>
    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Penguji Praktik</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #7c3aed;"><?= $totalEx ?></div>
            <div style="background: #f5f3ff; color: #7c3aed; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Orang</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Total guru penguji praktik terdata</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Mata Pelajaran Diuji</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #0284c7;"><?= $uniqueSubjects ?></div>
            <div style="background: #f0f9ff; color: #0284c7; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Mapel</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Bidang studi yang diujikan</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Penguji Utama</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #059669;"><?= $totalUtama ?></div>
            <div style="background: #ecfdf5; color: #059669; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Utama</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Penguji materi utama / koordinator</div>
    </div>

    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 18px 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Penguji Pendamping / Eksternal</div>
        <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: 8px;">
            <div style="font-size: 26px; font-weight: 800; color: #d97706;"><?= $totalPendamping ?></div>
            <div style="background: #fffbeb; color: #d97706; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">Pendamping</div>
        </div>
        <div style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Penguji pendamping / mitra luar</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="padding: 16px 20px; margin-bottom: 20px;">
    <form method="GET" action="<?= url('/master/examiners') ?>" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; flex: 1;">
            <div style="position: relative; min-width: 240px; flex: 1;">
                <input type="text" name="q" value="<?= e($search) ?>" class="form-control" placeholder="🔍 Cari nama penguji, NIP, mapel, lokasi uji...">
            </div>

            <div style="min-width: 180px;">
                <select name="subject" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Mata Pelajaran --</option>
                    <?php foreach ($subjectOptions as $sb): ?>
                        <option value="<?= e($sb) ?>" <?= $subjectFilter === $sb ? 'selected' : '' ?>><?= e($sb) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="min-width: 170px;">
                <select name="role" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Peran Penguji --</option>
                    <?php foreach ($roleOptions as $ro): ?>
                        <option value="<?= e($ro) ?>" <?= $roleFilter === $ro ? 'selected' : '' ?>><?= e($ro) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-secondary">Filter</button>
            <?php if (!empty($search) || !empty($subjectFilter) || !empty($roleFilter)): ?>
                <a href="<?= url('/master/examiners') ?>" class="btn btn-outline">Reset</a>
            <?php endif; ?>
        </div>

        <?php if (!empty($examiners)): ?>
            <div>
                <form action="<?= url('/master/examiners/delete-all') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('PERINGATAN: Apakah Anda yakin ingin MENGOSONGKAN seluruh data penguji praktik?', this)">
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
                    <th>Nama Guru Penguji</th>
                    <th>Mata Pelajaran Praktik</th>
                    <th class="text-center">Tingkat Kelas</th>
                    <th class="text-center">Peran Penguji</th>
                    <th>Asal Sekolah / Lembaga</th>
                    <th>Lokasi / Laboratorium</th>
                    <th>No. HP / WhatsApp</th>
                    <th class="text-center">Status</th>
                    <th style="width: 110px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($examiners)): ?>
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 40px; color: #94a3b8;">
                            <div style="font-size: 32px; margin-bottom: 8px;">🩺</div>
                            <div style="font-size: 15px; font-weight: 600; color: #475569;">Belum ada data penguji praktik.</div>
                            <div style="font-size: 13px; color: #64748b; margin-top: 6px;">Silakan klik tombol <strong>"Tambah Penguji"</strong> atau <strong>"Impor Excel"</strong> di atas.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($examiners as $idx => $ex): ?>
                        <tr>
                            <td class="text-center font-bold text-muted"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= e($ex['name']) ?></strong>
                                <?php if (!empty($ex['nip'])): ?>
                                    <div style="font-size: 11px; color: #64748b;">NIP: <?= e($ex['nip']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($ex['notes'])): ?>
                                    <div style="font-size: 11px; color: #94a3b8; font-style: italic;"><?= e($ex['notes']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= e($ex['subject_name']) ?></strong>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary" style="font-weight: 700;"><?= e($ex['grade'] ?: 'Semua') ?></span>
                            </td>
                            <td class="text-center">
                                <?php 
                                $badgeClass = 'badge-primary';
                                if (stripos($ex['examiner_role'], 'pendamping') !== false) $badgeClass = 'badge-info';
                                elseif (stripos($ex['examiner_role'], 'eksternal') !== false) $badgeClass = 'badge-warning';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= e($ex['examiner_role']) ?></span>
                            </td>
                            <td>
                                <span><?= e($ex['school_origin'] ?: 'Internal Sekolah') ?></span>
                            </td>
                            <td>
                                <?php if (!empty($ex['location'])): ?>
                                    <span style="font-weight: 600; color: #334155;"><?= e($ex['location']) ?></span>
                                <?php else: ?>
                                    <span style="color: #94a3b8;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($ex['phone'])): ?>
                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $ex['phone']) ?>" target="_blank" style="color: #059669; text-decoration: none; font-weight: 600; font-size: 12px;">
                                        📱 <?= e($ex['phone']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: #94a3b8;">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $ex['status'] === 'Aktif' ? 'badge-success' : 'badge-secondary' ?>">
                                    <?= e($ex['status']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div style="display: flex; gap: 4px; justify-content: center;">
                                    <button type="button" class="btn btn-outline btn-sm" onclick='openEditExaminerModal(<?= json_encode($ex) ?>)' title="Edit Data">
                                        ✏️
                                    </button>
                                    <form action="<?= url('/master/examiners/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus penguji <?= e($ex['name']) ?> untuk mata pelajaran <?= e($ex['subject_name']) ?>?', this)">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $ex['id'] ?>">
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

<!-- Modal Tambah Penguji Praktik -->
<div class="modal-overlay" id="addExaminerModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tambah Data Penguji Praktik</div>
            <button type="button" class="modal-close" data-modal-close="addExaminerModal">&times;</button>
        </div>
        <form action="<?= url('/master/examiners/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <!-- Pilihan Cepat Guru -->
                <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                    <label class="form-label" style="margin-bottom: 4px; font-size: 12px; color: #475569;">💡 Opsi Cepat: Pilih dari Data Guru Master</label>
                    <select id="quickTeacherSelectEx" class="form-control" onchange="applyQuickTeacherEx(this)">
                        <option value="">-- Ketik Manual atau Pilih Guru --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>" data-name="<?= e($t['name']) ?>" data-nip="<?= e($t['nip'] ?? '') ?>" data-phone="<?= e($t['phone'] ?? '') ?>">
                                <?= e($t['name']) ?> (NIP: <?= e($t['nip'] ?: '-') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Nama Lengkap Penguji</label>
                    <input type="text" name="name" id="addExName" class="form-control" placeholder="Contoh: Dede Ali Maulana, S.Pd." required>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">NIP / NUPTK</label>
                        <input type="text" name="nip" id="addExNip" class="form-control" placeholder="198701022011011002">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP / WhatsApp</label>
                        <input type="text" name="phone" id="addExPhone" class="form-control" placeholder="081298765432">
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label required">Mata Pelajaran Praktik</label>
                        <input type="text" name="subject_name" list="subjectPracticalOptions" class="form-control" placeholder="Contoh: IPA / PAI / PJOK" required>
                        <datalist id="subjectPracticalOptions">
                            <?php foreach ($subjects as $sb): ?>
                                <option value="<?= e($sb['name']) ?>"><?= e($sb['code']) ?></option>
                            <?php endforeach; ?>
                            <option value="Ilmu Pengetahuan Alam">IPA</option>
                            <option value="Pendidikan Agama Islam">PAI</option>
                            <option value="PJOK">Pendidikan Jasmani Olahraga Kesehatan</option>
                            <option value="Bahasa Inggris">Bahasa Inggris (Speaking & Listening)</option>
                            <option value="Informatika & Prakarya">Informatika / Prakarya</option>
                            <option value="Seni Budaya">Seni Budaya (Musik / Tari / Rupa)</option>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tingkat Kelas</label>
                        <select name="grade" class="form-control">
                            <option value="Semua Tingkat" selected>Semua Tingkat (VII, VIII, IX)</option>
                            <option value="VII">Kelas VII</option>
                            <option value="VIII">Kelas VIII</option>
                            <option value="IX">Kelas IX</option>
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label required">Peran Penguji</label>
                        <select name="examiner_role" class="form-control" required>
                            <option value="Penguji Utama" selected>Penguji Utama</option>
                            <option value="Penguji Pendamping">Penguji Pendamping</option>
                            <option value="Penguji Eksternal">Penguji Eksternal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asal Sekolah / Lembaga</label>
                        <input type="text" name="school_origin" class="form-control" value="Internal Sekolah" placeholder="SMP Negeri 1 Bojong">
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Lokasi / Laboratorium Uji</label>
                        <input type="text" name="location" class="form-control" placeholder="Contoh: Lab IPA / Musholla / Lapangan">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="Aktif" selected>Aktif</option>
                            <option value="Cadangan">Cadangan</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan / Materi Uji</label>
                    <input type="text" name="notes" class="form-control" placeholder="Contoh: Ujian praktik kelistrikan / baca Al-Quran">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addExaminerModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Penguji Praktik</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Penguji Praktik -->
<div class="modal-overlay" id="editExaminerModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Edit Data Penguji Praktik</div>
            <button type="button" class="modal-close" data-modal-close="editExaminerModal">&times;</button>
        </div>
        <form action="<?= url('/master/examiners/update') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="editExId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Nama Lengkap Penguji</label>
                    <input type="text" name="name" id="editExName" class="form-control" required>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">NIP / NUPTK</label>
                        <input type="text" name="nip" id="editExNip" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. HP / WhatsApp</label>
                        <input type="text" name="phone" id="editExPhone" class="form-control">
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label required">Mata Pelajaran Praktik</label>
                        <input type="text" name="subject_name" id="editExSubject" list="subjectPracticalOptions" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tingkat Kelas</label>
                        <select name="grade" id="editExGrade" class="form-control">
                            <option value="Semua Tingkat">Semua Tingkat (VII, VIII, IX)</option>
                            <option value="VII">Kelas VII</option>
                            <option value="VIII">Kelas VIII</option>
                            <option value="IX">Kelas IX</option>
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label required">Peran Penguji</label>
                        <select name="examiner_role" id="editExRole" class="form-control" required>
                            <option value="Penguji Utama">Penguji Utama</option>
                            <option value="Penguji Pendamping">Penguji Pendamping</option>
                            <option value="Penguji Eksternal">Penguji Eksternal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asal Sekolah / Lembaga</label>
                        <input type="text" name="school_origin" id="editExSchool" class="form-control">
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Lokasi / Laboratorium Uji</label>
                        <input type="text" name="location" id="editExLocation" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="editExStatus" class="form-control">
                            <option value="Aktif">Aktif</option>
                            <option value="Cadangan">Cadangan</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan / Catatan</label>
                    <input type="text" name="notes" id="editExNotes" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editExaminerModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Impor Excel -->
<div class="modal-overlay" id="importExaminerModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Impor Data Penguji Praktik dari Excel</div>
            <button type="button" class="modal-close" data-modal-close="importExaminerModal">&times;</button>
        </div>
        <form action="<?= url('/master/examiners/import') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px; margin-bottom: 16px;">
                    <div style="font-weight: 600; color: #1e40af; margin-bottom: 4px;">📥 Unduh Format Template Terlebih Dahulu</div>
                    <div style="font-size: 13px; color: #3b82f6; margin-bottom: 10px;">
                        Gunakan berkas template resmi agar nama kolom terbaca secara akurat.
                    </div>
                    <a href="<?= url('/master/examiners/template') ?>" class="btn btn-outline btn-sm" style="background: #ffffff; color: #1d4ed8; border-color: #93c5fd;">
                        📄 Unduh Template Excel (.xlsx)
                    </a>
                </div>

                <div class="form-group">
                    <label class="form-label required">Pilih Berkas Excel / CSV</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <small style="color: #64748b; display: block; margin-top: 6px;">
                        Mendukung format <code>.xlsx</code>, <code>.xls</code>, atau <code>.csv</code>. Kolom yang dibaca: <em>Nama Penguji, NIP, Mata Pelajaran, Tingkat Kelas, Peran Penguji, Asal Sekolah, Lokasi Ujian, No HP, Status, Keterangan</em>.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="importExaminerModal">Batal</button>
                <button type="submit" class="btn btn-primary">🚀 Mulai Impor Data</button>
            </div>
        </form>
    </div>
</div>

<script>
function applyQuickTeacherEx(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) return;

    document.getElementById('addExName').value = opt.getAttribute('data-name') || '';
    document.getElementById('addExNip').value = opt.getAttribute('data-nip') || '';
    document.getElementById('addExPhone').value = opt.getAttribute('data-phone') || '';
}

function openEditExaminerModal(data) {
    document.getElementById('editExId').value = data.id || '';
    document.getElementById('editExName').value = data.name || '';
    document.getElementById('editExNip').value = data.nip || '';
    document.getElementById('editExPhone').value = data.phone || '';
    document.getElementById('editExSubject').value = data.subject_name || '';
    document.getElementById('editExGrade').value = data.grade || 'Semua Tingkat';
    document.getElementById('editExRole').value = data.examiner_role || 'Penguji Utama';
    document.getElementById('editExSchool').value = data.school_origin || 'Internal Sekolah';
    document.getElementById('editExLocation').value = data.location || '';
    document.getElementById('editExStatus').value = data.status || 'Aktif';
    document.getElementById('editExNotes').value = data.notes || '';
    openModal('editExaminerModal');
}
</script>
