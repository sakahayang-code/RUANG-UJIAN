<div class="page-header">
    <div>
        <h1 class="page-title">Manajemen Kegiatan Ujian</h1>
        <p class="page-subtitle">Kelola berbagai kegiatan ujian per tahun pelajaran (PTS, PAS, PAT, US, dsb.) secara terisolasi dan terdokumentasi.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addEventModal">
            ➕ Tambah Kegiatan Ujian
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Nama Kegiatan Ujian</th>
                    <th>Tahun Pelajaran</th>
                    <th>Semester</th>
                    <th>Periode Tanggal</th>
                    <th class="text-center">Peserta</th>
                    <th class="text-center">Ruang</th>
                    <th class="text-center">Status</th>
                    <th style="width: 200px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($events)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada kegiatan ujian yang dibuat. Silakan tambahkan kegiatan baru.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($events as $idx => $ev): ?>
                        <tr style="<?= $ev['is_active'] ? 'background-color: #f0fdf4;' : '' ?>">
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td>
                                <strong><?= e($ev['name']) ?></strong>
                                <?php if ($ev['is_active']): ?>
                                    <span class="badge badge-success" style="margin-left: 6px;">Aktif Saat Ini</span>
                                <?php endif; ?>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                    Jenis: <?= e($ev['exam_type']) ?> • <?= e($ev['implementation_type']) ?>
                                </div>
                            </td>
                            <td><?= e($ev['academic_year']) ?></td>
                            <td><?= e($ev['semester']) ?></td>
                            <td style="font-size: 12px;">
                                <?= format_date_id($ev['start_date']) ?> s.d.<br>
                                <?= format_date_id($ev['end_date']) ?>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info"><?= number_format($ev['participant_count']) ?> Siswa</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary"><?= $ev['room_count'] ?> Ruang</span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $ev['status'] === 'aktif' || $ev['status'] === 'berjalan' ? 'badge-success' : ($ev['status'] === 'selesai' ? 'badge-info' : 'badge-warning') ?>">
                                    <?= strtoupper(e($ev['status'])) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
                                    <?php if (!$ev['is_active']): ?>
                                        <form action="<?= url('/events/activate') ?>" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $ev['id'] ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" title="Jadikan Kegiatan Aktif">⭐</button>
                                        </form>
                                    <?php endif; ?>

                                    <button type="button" class="btn btn-secondary btn-sm" title="Sinkronkan Peserta Siswa" onclick="openSyncModal(<?= $ev['id'] ?>, '<?= e(addslashes($ev['name'])) ?>')">
                                        👥
                                    </button>

                                    <button type="button" class="btn btn-outline btn-sm" title="Ubah" onclick='editEvent(<?= json_encode($ev) ?>)'>
                                        ✏️
                                    </button>

                                    <form action="<?= url('/events/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirmAction('Hapus kegiatan <?= e(addslashes($ev['name'])) ?> dan seluruh datanya?', this)">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $ev['id'] ?>">
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

<!-- Modal Tambah Kegiatan -->
<div class="modal-overlay" id="addEventModal">
    <div class="modal-container modal-lg">
        <div class="modal-header">
            <div class="modal-title">Buat Kegiatan Ujian Baru</div>
            <button type="button" class="modal-close" data-modal-close="addEventModal">&times;</button>
        </div>
        <form action="<?= url('/events/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label required">Nama Kegiatan Ujian</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Penilaian Akhir Semester Ganjil 2026/2027" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jenis Ujian</label>
                            <select name="exam_type" class="form-control" required>
                                <?php foreach ($examTypes as $et): ?>
                                    <option value="<?= e($et['name']) ?>"><?= e($et['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tahun Pelajaran</label>
                            <input type="text" name="academic_year" class="form-control" placeholder="2026/2027" value="2026/2027" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Semester</label>
                            <select name="semester" class="form-control" required>
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Metode Pelaksanaan</label>
                            <select name="implementation_type" class="form-control" required>
                                <option value="Kertas / Manual">Kertas / Manual</option>
                                <option value="Berbasis Komputer (CBT)">Berbasis Komputer (CBT)</option>
                                <option value="Campuran / Hybrid">Campuran / Hybrid</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tanggal Selesai</label>
                            <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan / Deskripsi Tambahan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Keterangan singkat kegiatan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addEventModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan & Buat Kegiatan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Kegiatan -->
<div class="modal-overlay" id="editEventModal">
    <div class="modal-container modal-lg">
        <div class="modal-header">
            <div class="modal-title">Ubah Data Kegiatan Ujian</div>
            <button type="button" class="modal-close" data-modal-close="editEventModal">&times;</button>
        </div>
        <form action="<?= url('/events/update') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="editEventId">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label required">Nama Kegiatan Ujian</label>
                            <input type="text" name="name" id="editEventName" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jenis Ujian</label>
                            <select name="exam_type" id="editEventExamType" class="form-control" required>
                                <?php foreach ($examTypes as $et): ?>
                                    <option value="<?= e($et['name']) ?>"><?= e($et['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tahun Pelajaran</label>
                            <input type="text" name="academic_year" id="editEventYear" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Semester</label>
                            <select name="semester" id="editEventSemester" class="form-control" required>
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Status Kegiatan</label>
                            <select name="status" id="editEventStatus" class="form-control" required>
                                <option value="draft">Draft</option>
                                <option value="aktif">Aktif</option>
                                <option value="berjalan">Sedang Berjalan</option>
                                <option value="selesai">Selesai</option>
                                <option value="diarsipkan">Diarsipkan</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="editEventStartDate" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tanggal Selesai</label>
                            <input type="date" name="end_date" id="editEventEndDate" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Metode Pelaksanaan</label>
                            <input type="text" name="implementation_type" id="editEventImplType" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Tambahan</label>
                    <textarea name="notes" id="editEventNotes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="editEventModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sinkronkan Peserta -->
<div class="modal-overlay" id="syncParticipantModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Sinkronisasi Peserta Ujian</div>
            <button type="button" class="modal-close" data-modal-close="syncParticipantModal">&times;</button>
        </div>
        <form action="<?= url('/events/sync-participants') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="event_id" id="syncEventId">
            <div class="modal-body">
                <p style="font-size: 13px; color: #475569; margin-bottom: 16px;">
                    Sistem akan mengambil data siswa aktif dari Master Data dan menyimpannya sebagai <strong>snapshot peserta ujian</strong> (nama, NIS, NISN, kelas, dan tingkat). Data ini terkunci secara permanen dan tidak akan rusak jika master data diubah di masa depan.
                </p>
                <div class="form-group">
                    <label class="form-label required">Pilih Tingkat Siswa yang Mengikuti:</label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 8px;">
                        <label style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                            <input type="checkbox" name="grades[]" value="VII" checked> Tingkat VII
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                            <input type="checkbox" name="grades[]" value="VIII" checked> Tingkat VIII
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                            <input type="checkbox" name="grades[]" value="IX" checked> Tingkat IX
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                            <input type="checkbox" name="grades[]" value="X"> Tingkat X
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                            <input type="checkbox" name="grades[]" value="XI"> Tingkat XI
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                            <input type="checkbox" name="grades[]" value="XII"> Tingkat XII
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="syncParticipantModal">Batal</button>
                <button type="submit" class="btn btn-primary">Sinkronkan Sekarang</button>
            </div>
        </form>
    </div>
</div>

<script>
function editEvent(ev) {
    document.getElementById('editEventId').value = ev.id;
    document.getElementById('editEventName').value = ev.name;
    document.getElementById('editEventExamType').value = ev.exam_type;
    document.getElementById('editEventYear').value = ev.academic_year;
    document.getElementById('editEventSemester').value = ev.semester;
    document.getElementById('editEventStatus').value = ev.status;
    document.getElementById('editEventStartDate').value = ev.start_date;
    document.getElementById('editEventEndDate').value = ev.end_date;
    document.getElementById('editEventImplType').value = ev.implementation_type;
    document.getElementById('editEventNotes').value = ev.notes || '';
    openModal('editEventModal');
}

function openSyncModal(eventId, eventName) {
    document.getElementById('syncEventId').value = eventId;
    openModal('syncParticipantModal');
}
</script>
