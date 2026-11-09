<div class="page-header">
    <div>
        <h1 class="page-title">Berita Acara & Catatan Kejadian Khusus</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Rekapitulasi berita acara ujian tertulis, ujian praktik, dan pencatatan insiden/kejadian khusus selama ujian.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addIncidentModal">
            🚨 Catat Kejadian Khusus / Insiden
        </button>
    </div>
</div>

<!-- Modal Catat Insiden -->
<div class="modal" id="addIncidentModal">
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-content">
            <form method="POST" action="<?= url('/reports/incidents') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h3 class="modal-title">Pencatatan Kejadian Khusus / Insiden Ujian</h3>
                    <button type="button" class="modal-close" data-modal-close="addIncidentModal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col" style="flex: 1;">
                            <label class="form-label required">Tanggal</label>
                            <input type="date" name="incident_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col" style="flex: 1;">
                            <label class="form-label required">Waktu (Jam)</label>
                            <input type="text" name="incident_time" class="form-control" value="<?= date('H:i') ?> WIB" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col" style="flex: 1;">
                            <label class="form-label required">Kategori</label>
                            <select name="category" class="form-control" required>
                                <option value="tertulis">Ujian Tertulis</option>
                                <option value="praktik">Ujian Praktik</option>
                                <option value="naskah">Naskah Soal & Lembar Jawaban</option>
                                <option value="kepanitiaan">Kepanitiaan / Sarana</option>
                                <option value="kesehatan">Kesehatan Peserta</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col" style="flex: 1;">
                            <label class="form-label">Lokasi / Ruang</label>
                            <input type="text" name="location" class="form-control" placeholder="Misal: Ruang 03 / Lab IPA">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label required">Uraian Kejadian</label>
                        <textarea name="description" class="form-control" rows="3" required placeholder="Jelaskan secara rinci kronologi kejadian..."></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Tindak Lanjut / Solusi yang Diambil</label>
                        <textarea name="follow_up" class="form-control" rows="2" placeholder="Tindakan yang dilakukan oleh pengawas / panitia..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col" style="flex: 1;">
                            <label class="form-label">Nama Pelapor</label>
                            <input type="text" name="reporter_name" class="form-control" value="<?= e(auth_user()['full_name'] ?? 'Petugas Panitia') ?>">
                        </div>
                        <div class="col" style="flex: 1;">
                            <label class="form-label">Status Penyelesaian</label>
                            <select name="status" class="form-control">
                                <option value="Selesai">Selesai (Tertangani)</option>
                                <option value="Dalam Proses">Dalam Proses</option>
                                <option value="Perlu Perhatian">Perlu Perhatian Khusus</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close="addIncidentModal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Catatan Kejadian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 1. Buku Catatan Kejadian Khusus / Insiden -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">📖 Buku Catatan Kejadian Khusus / Insiden Pelaksanaan (<?= count($incidents) ?> Catatan)</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Waktu & Tanggal</th>
                    <th>Kategori & Lokasi</th>
                    <th>Uraian Kejadian</th>
                    <th>Tindak Lanjut</th>
                    <th>Pelapor</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 80px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($incidents)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            Tidak ada catatan kejadian khusus / insiden yang dilaporkan. Seluruh pelaksanaan ujian berjalan tertib.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($incidents as $idx => $inc): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= e(indo_date($inc['incident_date'], true)) ?></strong><br>
                                <small class="text-muted"><?= e($inc['incident_time']) ?></small>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= strtoupper(e($inc['category'])) ?></span><br>
                                <small>📍 <?= e($inc['location'] ?: '-') ?></small>
                            </td>
                            <td style="max-width: 280px; font-size: 13px;">
                                <?= nl2br(e($inc['description'])) ?>
                            </td>
                            <td style="max-width: 240px; font-size: 13px; color: #15803d;">
                                <?= nl2br(e($inc['follow_up'] ?: '-')) ?>
                            </td>
                            <td><?= e($inc['reporter_name']) ?></td>
                            <td class="text-center">
                                <?php if ($inc['status'] === 'Selesai'): ?>
                                    <span class="badge badge-success">✓ Selesai</span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?= e($inc['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <form method="POST" action="<?= url('/reports/incidents/delete') ?>" onsubmit="return confirm('Hapus catatan kejadian ini?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $inc['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline text-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 2. Hub Berita Acara Ujian Tertulis & Praktik -->
<div class="row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
    <div class="card p-3">
        <div class="card-header d-flex justify-content-between align-items-center" style="border: none; padding: 0 0 10px 0;">
            <h3 class="card-title" style="font-size: 16px;">📝 Berita Acara Ujian Tertulis (<?= count($writtenMinutes) ?> Dokumen)</h3>
            <a href="<?= url('/written-exam/execution') ?>" class="btn btn-sm btn-outline">Lihat Pelaksanaan</a>
        </div>
        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
            <table class="table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Ruang</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Cetak</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($writtenMinutes)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">Belum ada berita acara tertulis yang dibuat.</td></tr>
                    <?php else: ?>
                        <?php foreach ($writtenMinutes as $wm): ?>
                            <tr>
                                <td><?= e(indo_date($wm['exam_date'], true)) ?></td>
                                <td>Ruang <?= e($wm['room_number']) ?></td>
                                <td><?= e($wm['subject_name']) ?></td>
                                <td class="text-center"><strong><?= (int)$wm['present_count'] ?></strong></td>
                                <td class="text-center">
                                    <a href="<?= url('/written-exam/minutes/print?id=' . $wm['id']) ?>" target="_blank" class="btn btn-xs btn-outline">🖨️</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-3">
        <div class="card-header d-flex justify-content-between align-items-center" style="border: none; padding: 0 0 10px 0;">
            <h3 class="card-title" style="font-size: 16px;">🛠️ Berita Acara Ujian Praktik (<?= count($practicalMinutes) ?> Dokumen)</h3>
            <a href="<?= url('/practical-exam') ?>" class="btn btn-sm btn-outline">Lihat Ujian Praktik</a>
        </div>
        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
            <table class="table" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Kesesuaian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($practicalMinutes)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">Belum ada berita acara praktik yang dibuat.</td></tr>
                    <?php else: ?>
                        <?php foreach ($practicalMinutes as $pm): ?>
                            <tr>
                                <td><?= e(indo_date($pm['exam_date'], true)) ?></td>
                                <td><?= e($pm['subject_name']) ?></td>
                                <td class="text-center"><strong><?= (int)$pm['present_count'] ?></strong></td>
                                <td class="text-center"><span class="badge badge-success">✓ Lengkap</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
