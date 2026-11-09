<div class="page-header">
    <div>
        <h1 class="page-title">Persiapan Penggandaan Naskah Soal</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Perhitungan kebutuhan naskah utama, cadangan terkonfigurasi, dan status cetak.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="addPrintingModal">
            ➕ Hitung & Catat Kebutuhan Cetak
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Mata Pelajaran</th>
                    <th>Tingkat</th>
                    <th class="text-center">Peserta</th>
                    <th class="text-center">Naskah Utama</th>
                    <th class="text-center">Cadangan</th>
                    <th class="text-center">Total Kebutuhan</th>
                    <th class="text-center">Sudah Dicetak</th>
                    <th class="text-center">Belum Dicetak</th>
                    <th class="text-center">Status</th>
                    <th>Penanggung Jawab</th>
                    <th style="width: 100px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="12" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada catatan penggandaan naskah soal. Klik tombol di atas untuk memulai pencatatan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php
                    $totalMain = 0;
                    $totalReserve = 0;
                    $totalNeeded = 0;
                    $totalPrinted = 0;
                    $totalUnprinted = 0;
                    foreach ($records as $idx => $r):
                        $totalMain += (int)$r['main_copies'];
                        $totalReserve += (int)$r['reserve_copies'];
                        $totalNeeded += (int)$r['total_copies'];
                        $totalPrinted += (int)$r['printed_copies'];
                        $totalUnprinted += (int)$r['unprinted_copies'];
                    ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><strong><?= e($r['subject_name']) ?></strong> (<code><?= e($r['subject_code']) ?></code>)</td>
                            <td><span class="badge badge-secondary"><?= e($r['grade']) ?></span></td>
                            <td class="text-center"><?= $r['participant_count'] ?></td>
                            <td class="text-center"><?= $r['main_copies'] ?></td>
                            <td class="text-center font-bold" style="color: #2563eb;">+<?= $r['reserve_copies'] ?></td>
                            <td class="text-center font-bold" style="color: #1e3a8a;"><?= $r['total_copies'] ?></td>
                            <td class="text-center" style="color: #059669; font-weight: bold;"><?= $r['printed_copies'] ?></td>
                            <td class="text-center" style="color: #dc2626;"><?= $r['unprinted_copies'] ?></td>
                            <td class="text-center">
                                <span class="badge <?= $r['status'] === 'Selesai Dicetak' || $r['status'] === 'Terverifikasi' ? 'badge-success' : ($r['status'] === 'Sedang Dicetak' ? 'badge-warning' : 'badge-danger') ?>">
                                    <?= e($r['status']) ?>
                                </span>
                            </td>
                            <td style="font-size: 12px;"><?= e($r['pic_name'] ?: '-') ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline btn-sm" onclick='openUpdatePrintingModal(<?= json_encode($r) ?>)'>
                                    ✏️ Update
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($records)): ?>
                <tfoot>
                    <tr style="background: #f8fafc; font-weight: bold;">
                        <td colspan="4" class="text-right">TOTAL KESELURUHAN:</td>
                        <td class="text-center"><?= $totalMain ?></td>
                        <td class="text-center"><?= $totalReserve ?></td>
                        <td class="text-center" style="color: #1e3a8a; font-size: 14px;"><?= $totalNeeded ?></td>
                        <td class="text-center" style="color: #059669;"><?= $totalPrinted ?></td>
                        <td class="text-center" style="color: #dc2626;"><?= $totalUnprinted ?></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Modal Tambah Catatan Penggandaan -->
<div class="modal-overlay" id="addPrintingModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Hitung & Catat Kebutuhan Penggandaan Naskah</div>
            <button type="button" class="modal-close" data-modal-close="addPrintingModal">&times;</button>
        </div>
        <form action="<?= url('/preparation/printing/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col" style="flex: 2;">
                        <div class="form-group">
                            <label class="form-label required">Mata Pelajaran</label>
                            <select name="subject_id" class="form-control" required>
                                <option value="">Pilih Mata Pelajaran</option>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= e($s['code']) ?> - <?= e($s['name']) ?> (<?= e($s['grade']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tingkat</label>
                            <select name="grade" class="form-control" required>
                                <option value="VII">VII</option>
                                <option value="VIII">VIII</option>
                                <option value="IX">IX</option>
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jumlah Cadangan (Konfigurasi)</label>
                            <input type="number" name="reserve_copies" class="form-control" value="2" min="0" required>
                            <div class="form-text">Dapat disesuaikan (default: 2 eks/ruang atau per mapel).</div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Sudah Selesai Dicetak (Eks)</label>
                            <input type="number" name="printed_copies" class="form-control" value="0" min="0">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Penanggung Jawab Penggandaan</label>
                            <input type="text" name="pic_name" class="form-control" placeholder="Nama petugas seksi penggandaan...">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Tanggal Pelaksanaan Penggandaan</label>
                            <input type="date" name="print_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan Tambahan</label>
                    <input type="text" name="notes" class="form-control" placeholder="Tempat cetak / rekanan...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="addPrintingModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Kebutuhan Cetak</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Update Status Penggandaan -->
<div class="modal-overlay" id="updatePrintingModal">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">Perbarui Kesiapan Cetak Naskah</div>
            <button type="button" class="modal-close" data-modal-close="updatePrintingModal">&times;</button>
        </div>
        <form action="<?= url('/preparation/printing/update') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="editPrintId">
            <div class="modal-body">
                <div style="background: #f8fafc; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 16px; font-size: 13px;">
                    <div>Mata Pelajaran: <strong id="editPrintSubject"></strong></div>
                    <div>Peserta Terdaftar: <strong id="editPrintParticipants"></strong> Siswa</div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jumlah Naskah Cadangan</label>
                            <input type="number" name="reserve_copies" id="editPrintReserve" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jumlah Sudah Dicetak (Eks)</label>
                            <input type="number" name="printed_copies" id="editPrintPrinted" class="form-control" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Status Penggandaan</label>
                            <select name="status" id="editPrintStatus" class="form-control" required>
                                <option value="Belum Dicetak">Belum Dicetak</option>
                                <option value="Sedang Dicetak">Sedang Dicetak</option>
                                <option value="Selesai Dicetak">Selesai Dicetak</option>
                                <option value="Terverifikasi">Terverifikasi Siap Didistribusikan</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Penanggung Jawab</label>
                            <input type="text" name="pic_name" id="editPrintPic" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Tanggal Penggandaan</label>
                            <input type="date" name="print_date" id="editPrintDate" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Catatan Tambahan</label>
                            <input type="text" name="notes" id="editPrintNotes" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="updatePrintingModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Progres</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUpdatePrintingModal(r) {
    document.getElementById('editPrintId').value = r.id;
    document.getElementById('editPrintSubject').textContent = r.subject_name + ' (Tingkat ' + r.grade + ')';
    document.getElementById('editPrintParticipants').textContent = r.participant_count;
    document.getElementById('editPrintReserve').value = r.reserve_copies;
    document.getElementById('editPrintPrinted').value = r.printed_copies;
    document.getElementById('editPrintStatus').value = r.status;
    document.getElementById('editPrintPic').value = r.pic_name || '';
    document.getElementById('editPrintDate').value = r.print_date || '';
    document.getElementById('editPrintNotes').value = r.notes || '';
    openModal('updatePrintingModal');
}
</script>
