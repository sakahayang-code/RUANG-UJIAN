<div class="page-header">
    <div>
        <h1 class="page-title">Atur Ruangan Ujian & Pembagian Peserta</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Pengelolaan kapasitas ruang dan sistem alokasi peserta (campuran multi-tingkat/kelas).</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addRoomModal">
            🚪 Tambah Ruang Ujian
        </button>
        <button type="button" class="btn btn-success" data-modal-open="distributeModal">
            🔀 Distribusikan Peserta Otomatis
        </button>
        <a href="<?= url('/written-exam/seating') ?>" class="btn btn-outline">
            🪑 Lihat Denah Tempat Duduk
        </a>
    </div>
</div>

<!-- Stats Bar -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-icon primary">👥</div>
        <div class="stat-info">
            <div class="stat-label">Total Peserta</div>
            <div class="stat-value"><?= number_format($totalParticipants) ?></div>
        </div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon success">✓</div>
        <div class="stat-info">
            <div class="stat-label">Sudah Dapat Ruang</div>
            <div class="stat-value"><?= number_format($totalAssigned) ?></div>
        </div>
    </div>
    <div class="stat-card <?= $unassigned > 0 ? 'warning' : 'primary' ?>">
        <div class="stat-icon <?= $unassigned > 0 ? 'warning' : 'primary' ?>">⌛</div>
        <div class="stat-info">
            <div class="stat-label">Belum Dapat Ruang</div>
            <div class="stat-value"><?= number_format($unassigned) ?></div>
        </div>
    </div>
    <div class="stat-card indigo">
        <div class="stat-icon indigo">🚪</div>
        <div class="stat-info">
            <div class="stat-label">Jumlah Ruang</div>
            <div class="stat-value"><?= count($rooms) ?></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>No. Ruang</th>
                    <th>Nama Ruang</th>
                    <th class="text-center">Kapasitas</th>
                    <th class="text-center">Denah Meja</th>
                    <th class="text-center">Peserta Terisi</th>
                    <th class="text-center">Status Keterisian</th>
                    <th>Keterangan</th>
                    <th style="width: 140px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rooms)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada ruang ujian yang dibuat. Klik "Tambah Ruang Ujian" di atas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rooms as $idx => $r): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><code><?= e($r['room_number']) ?></code></td>
                            <td><strong><?= e($r['name']) ?></strong></td>
                            <td class="text-center font-bold"><?= $r['capacity'] ?> Kursi</td>
                            <td class="text-center"><?= $r['rows_count'] ?> Baris × <?= $r['cols_count'] ?> Kolom</td>
                            <td class="text-center">
                                <span class="badge badge-info" style="font-size: 13px;"><?= $r['assigned_count'] ?> Siswa</span>
                            </td>
                            <td class="text-center">
                                <?php if ($r['assigned_count'] >= $r['capacity']): ?>
                                    <span class="badge badge-success">Penuh (<?= $r['assigned_count'] ?>/<?= $r['capacity'] ?>)</span>
                                <?php elseif ($r['assigned_count'] > 0): ?>
                                    <span class="badge badge-warning">Terisi Sebagian (<?= $r['assigned_count'] ?>/<?= $r['capacity'] ?>)</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Kosong (0/<?= $r['capacity'] ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px;"><?= e($r['notes'] ?: '-') ?></td>
                            <td class="text-center">
                                <a href="<?= url('/written-exam/seating?room_id=' . $r['id']) ?>" class="btn btn-outline btn-sm" title="Lihat Denah">
                                    🪑 Denah
                                </a>
                                <form action="<?= url('/written-exam/rooms/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus ruang <?= e($r['name']) ?>?', this)">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
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

<!-- Modal Tambah Ruang -->
<div class="modal-overlay" id="addRoomModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Tambah Ruang Ujian Baru</div>
            <button type="button" class="modal-close" data-modal-close="addRoomModal">&times;</button>
        </div>
        <form action="<?= url('/written-exam/rooms/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Nomor Ruang (Kode)</label>
                            <input type="text" name="room_number" class="form-control" placeholder="Contoh: R-01, Ruang 01" required>
                        </div>
                    </div>
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label required">Nama Ruang / Lokasi</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Ruang Kelas VII A / Lab Bahasa" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Kapasitas Kursi</label>
                            <input type="number" name="capacity" class="form-control" value="20" min="1" max="100" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jumlah Baris Meja</label>
                            <input type="number" name="rows_count" class="form-control" value="5" min="1" max="20" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jumlah Kolom Meja</label>
                            <input type="number" name="cols_count" class="form-control" value="4" min="1" max="20" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan Tambahan</label>
                    <input type="text" name="notes" class="form-control" placeholder="Lantai 1, Sayap Timur...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addRoomModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Ruang</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Distribusi Otomatis -->
<div class="modal-overlay" id="distributeModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Distribusi Peserta ke Ruang Ujian</div>
            <button type="button" class="modal-close" data-modal-close="distributeModal">&times;</button>
        </div>
        <form action="<?= url('/written-exam/rooms/auto-distribute') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="alert alert-info" style="font-size: 13px;">
                    Sistem dapat mendistribusikan peserta secara seimbang ke seluruh ruang yang ada. Satu ruang dapat menampung campuran dari berbagai kelas dan tingkatan.
                </div>

                <div class="form-group">
                    <label class="form-label required">Metode Pembagian Peserta</label>
                    <select name="mode" class="form-control" required>
                        <option value="mix_grades">Campur & Silang Antar Tingkat / Rombel (Disarankan untuk Mencegah Mencontek)</option>
                        <option value="random">Acak Sepenuhnya (Randomize)</option>
                        <option value="sequential">Berurutan per Tingkat dan Kelas Asal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer;">
                        <input type="checkbox" name="clear_existing" value="1" checked>
                        <span>Hapus dan set ulang penempatan ruang sebelumnya</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="distributeModal">Batal</button>
                <button type="submit" class="btn btn-success">Mulai Distribusi Peserta</button>
            </div>
        </form>
    </div>
</div>
