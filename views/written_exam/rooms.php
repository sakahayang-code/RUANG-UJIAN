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
        <?php if (!empty($masterStudentCount) && $masterStudentCount > 0): ?>
            <form action="<?= url('/written-exam/rooms/sync-participants') ?>" method="POST" style="display: inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-secondary" title="Tarik atau sinkronkan seluruh siswa aktif dari Master Data Siswa">
                    🔄 Tarik Siswa
                </button>
            </form>
        <?php endif; ?>
        <?php if (!empty($totalAssigned) && $totalAssigned > 0): ?>
            <form action="<?= url('/written-exam/rooms/reset-assignments') ?>" method="POST" style="display: inline;" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin mengosongkan seluruh penempatan peserta di ruangan ujian? Tindakan ini dapat dibagikan ulang kapan saja.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline text-danger" title="Kosongkan penempatan ruang">
                    🗑️ Reset Penempatan
                </button>
            </form>
        <?php endif; ?>
        <a href="<?= url('/written-exam/seating') ?>" class="btn btn-outline">
            🪑 Lihat Denah Tempat Duduk
        </a>
    </div>
</div>

<?php if ($totalParticipants === 0): ?>
    <?php if (!empty($masterStudentCount) && $masterStudentCount > 0): ?>
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 26px;">👥</span>
                <div>
                    <div style="font-weight: 700; color: #1e40af; font-size: 14px;">Belum Ada Peserta Terdaftar di Kegiatan Ini</div>
                    <div style="font-size: 12px; color: #2563eb; margin-top: 2px;">
                        Ditemukan <strong><?= number_format($masterStudentCount) ?> siswa aktif</strong> di Master Data Siswa. Klik tombol <strong>Tarik Siswa Sekarang</strong> atau langsung tekan <strong>Distribusikan Peserta Otomatis</strong> untuk menempatkan mereka ke dalam ruang ujian.
                    </div>
                </div>
            </div>
            <form action="<?= url('/written-exam/rooms/sync-participants') ?>" method="POST" style="margin: 0;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary btn-sm" style="font-weight: 600;">
                    📥 Tarik <?= number_format($masterStudentCount) ?> Siswa Sekarang
                </button>
            </form>
        </div>
    <?php else: ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 26px;">⚠️</span>
                <div>
                    <div style="font-weight: 700; color: #991b1b; font-size: 14px;">Master Data Siswa Masih Kosong</div>
                    <div style="font-size: 12px; color: #dc2626; margin-top: 2px;">
                        Peserta tidak dapat didistribusikan karena belum ada data siswa yang diimpor atau ditambahkan ke sistem.
                    </div>
                </div>
            </div>
            <a href="<?= url('/master/students') ?>" class="btn btn-primary btn-sm" style="font-weight: 600;">
                📥 Buka Master Data Siswa
            </a>
        </div>
    <?php endif; ?>
<?php elseif ($unassigned > 0 && !empty($totalCapacity) && $totalCapacity < ($unassigned + $totalAssigned)): ?>
    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <span style="font-size: 22px;">⚠️</span>
        <div style="font-size: 12px; color: #92400e;">
            <strong>Perhatian Kapasitas:</strong> Total kapasitas kursi ruang ujian saat ini (<strong><?= number_format($totalCapacity) ?> kursi</strong>) lebih sedikit dibandingkan total peserta (<strong><?= number_format($totalParticipants) ?> siswa</strong>). Tambahkan ruang baru atau perbesar kapasitas agar seluruh siswa tertampung.
        </div>
    </div>
<?php endif; ?>

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
                                <?php $assignedCount = (int)($r['assigned_count'] ?? 0); ?>
                                <span class="badge badge-info" style="font-size: 13px;"><?= $assignedCount ?> Siswa</span>
                            </td>
                            <td class="text-center">
                                <?php if ($assignedCount >= $r['capacity']): ?>
                                    <span class="badge badge-success">Penuh (<?= $assignedCount ?>/<?= $r['capacity'] ?>)</span>
                                <?php elseif ($assignedCount > 0): ?>
                                    <span class="badge badge-warning">Terisi Sebagian (<?= $assignedCount ?>/<?= $r['capacity'] ?>)</span>
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
                <?php if ($totalParticipants === 0 && !empty($masterStudentCount) && $masterStudentCount > 0): ?>
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; font-size: 13px; color: #166534; line-height: 1.5;">
                        💡 <strong>Sinkronisasi Otomatis:</strong> Kegiatan ini belum memiliki peserta terdaftar. Saat Anda menekan <strong>Mulai Distribusi Peserta</strong>, sistem akan <strong>otomatis menarik <?= number_format($masterStudentCount) ?> siswa aktif</strong> dari Master Data Siswa dan langsung menempatkannya ke dalam ruang ujian.
                    </div>
                <?php elseif ($totalParticipants === 0 && (empty($masterStudentCount) || $masterStudentCount === 0)): ?>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; font-size: 13px; color: #991b1b; line-height: 1.5;">
                        ⚠️ <strong>Data Siswa Kosong:</strong> Belum ada data siswa di Master Data Siswa. Silakan impor atau tambahkan siswa terlebih dahulu di menu Siswa sebelum menjalankan distribusi ruangan.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" style="font-size: 13px;">
                        Sistem dapat mendistribusikan peserta secara seimbang ke seluruh ruang yang ada. Satu ruang dapat menampung campuran dari berbagai kelas dan tingkatan.
                    </div>
                <?php endif; ?>

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
                <?php if ($totalParticipants === 0 && (empty($masterStudentCount) || $masterStudentCount === 0)): ?>
                    <a href="<?= url('/master/students') ?>" class="btn btn-primary">Buka Master Data Siswa</a>
                <?php else: ?>
                    <button type="submit" class="btn btn-success">Mulai Distribusi Peserta</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
