<div class="page-header">
    <div>
        <h1 class="page-title">Program Kerja & Rencana Anggaran Biaya (RAB)</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Dokumen perencanaan resmi 20 bab sesuai standar administrasi sekolah.</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('/work-program/print') ?>" target="_blank" class="btn btn-outline">
            🖨️ Cetak Dokumen Resmi
        </a>
        <a href="<?= url('/work-program/export-docx') ?>" class="btn btn-secondary">
            📄 Ekspor Word (.docx)
        </a>
    </div>
</div>

<form action="<?= url('/work-program/update') ?>" method="POST" id="workProgramForm">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $program['id'] ?>">

    <!-- 1 & 2. Cover & Judul -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">1. Cover & Judul Dokumen</div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-col" style="flex: 2;">
                    <div class="form-group">
                        <label class="form-label required">Judul Utama Dokumen</label>
                        <textarea name="title" class="form-control" rows="2" required><?= e($program['title']) ?></textarea>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required">Tahun Pelajaran</label>
                        <input type="text" name="academic_year" class="form-control" value="<?= e($program['academic_year']) ?>" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required">Tanggal Pengesahan</label>
                        <input type="date" name="approval_date" class="form-control" value="<?= e($program['approval_date'] ?: date('Y-m-d')) ?>" required>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Kata Pengantar -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">2. Kata Pengantar Panitia</div>
        </div>
        <div class="card-body">
            <div class="form-group" style="margin-bottom: 0;">
                <textarea name="foreword" class="form-control" rows="5" required><?= e($program['foreword']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- 3 to 9. Pendahuluan, Maksud & Tujuan, Sasaran -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">3 - 9. Latar Belakang, Dasar Hukum, Maksud, Tujuan, Tema & Sasaran</div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label required">4. Latar Belakang</label>
                <textarea name="background" class="form-control" rows="4" required><?= e($program['background']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label required">5. Dasar Hukum</label>
                <textarea name="legal_basis" class="form-control" rows="4" required><?= e($program['legal_basis']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label required">6. Maksud dan Tujuan</label>
                <textarea name="objectives" class="form-control" rows="3" required><?= e($program['objectives']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required">7. Tema Kegiatan</label>
                        <input type="text" name="theme" class="form-control" value="<?= e($program['theme']) ?>" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required">9. Sasaran / Peserta Ujian</label>
                        <input type="text" name="target_description" class="form-control" value="<?= e($program['target_description']) ?>" required>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 10 & 11. Struktur Kepanitiaan Terintegrasi -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">10 & 11. Struktur Kepanitiaan & Uraian Tugas (Terintegrasi)</div>
            <a href="<?= url('/committee') ?>" target="_blank" class="btn btn-outline btn-sm">Kelola Struktur ↗</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Jabatan Panitia</th>
                            <th>Nama Pejabat / Guru</th>
                            <th>NIP</th>
                            <th>Seksi / Bidang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($committeeMembers)): ?>
                            <tr><td colspan="5" class="text-center" style="color: #94a3b8;">Belum ada personel dalam struktur panitia.</td></tr>
                        <?php else: ?>
                            <?php foreach ($committeeMembers as $idx => $cm): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><strong><?= e($cm['role_title']) ?></strong></td>
                                    <td><?= e($cm['teacher_name'] ?: $cm['custom_name']) ?></td>
                                    <td><?= e($cm['teacher_nip'] ?: $cm['custom_nip'] ?: '-') ?></td>
                                    <td><?= e($cm['section_name'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 12. Rencana Kerja dan Tahapan Persiapan (Dynamic Table) -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">12. Rencana Kerja & Tahapan Persiapan</div>
            <button type="button" class="btn btn-primary btn-sm" onclick="addWorkProgramActivityRow()">
                ➕ Tambah Baris Kegiatan
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" id="workProgramActivitiesTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th>Uraian Kegiatan</th>
                            <th style="width: 140px;">Mulai</th>
                            <th style="width: 140px;">Selesai</th>
                            <th style="width: 180px;">Penanggung Jawab</th>
                            <th>Keterangan</th>
                            <th style="width: 80px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="workProgramActivitiesTableBody">
                        <?php foreach ($activities as $idx => $act): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><input type="text" name="activities[<?= $idx + 1 ?>][name]" class="form-control" value="<?= e($act['activity_name']) ?>" required></td>
                                <td><input type="date" name="activities[<?= $idx + 1 ?>][start_date]" class="form-control" value="<?= e($act['start_date']) ?>"></td>
                                <td><input type="date" name="activities[<?= $idx + 1 ?>][end_date]" class="form-control" value="<?= e($act['end_date']) ?>"></td>
                                <td><input type="text" name="activities[<?= $idx + 1 ?>][pic]" class="form-control" value="<?= e($act['pic_name']) ?>"></td>
                                <td><input type="text" name="activities[<?= $idx + 1 ?>][notes]" class="form-control" value="<?= e($act['notes']) ?>"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); renumberRows('workProgramActivitiesTableBody');">Hapus</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 13 & 14. Ringkasan Jadwal Ujian Terintegrasi -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">13 & 14. Jadwal Ujian Tertulis & Praktik (Terintegrasi)</div>
        </div>
        <div class="card-body">
            <div style="font-weight: 600; font-size: 13px; color: #1e3a8a; margin-bottom: 8px;">Jadwal Ujian Tertulis (<?= count($writtenSchedules) ?> Sesi)</div>
            <div class="table-responsive" style="margin-bottom: 20px;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hari / Tanggal</th>
                            <th>Sesi</th>
                            <th>Waktu</th>
                            <th>Mata Pelajaran</th>
                            <th>Tingkat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($writtenSchedules)): ?>
                            <tr><td colspan="5" class="text-center" style="color: #94a3b8;">Jadwal tertulis belum dibuat.</td></tr>
                        <?php else: ?>
                            <?php foreach ($writtenSchedules as $ws): ?>
                                <tr>
                                    <td><?= format_day_id($ws['exam_date']) ?>, <?= format_date_id($ws['exam_date']) ?></td>
                                    <td>Sesi <?= e($ws['session']) ?></td>
                                    <td><?= e($ws['start_time']) ?> - <?= e($ws['end_time']) ?> WIB</td>
                                    <td><strong><?= e($ws['subject_name']) ?></strong></td>
                                    <td><?= e($ws['grade']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div style="font-weight: 600; font-size: 13px; color: #1e3a8a; margin-bottom: 8px;">Jadwal Ujian Praktik (<?= count($practicalSchedules) ?> Sesi)</div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hari / Tanggal</th>
                            <th>Waktu</th>
                            <th>Mata Pelajaran</th>
                            <th>Tingkat</th>
                            <th>Lokasi & Stasiun</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($practicalSchedules)): ?>
                            <tr><td colspan="5" class="text-center" style="color: #94a3b8;">Jadwal praktik belum dibuat.</td></tr>
                        <?php else: ?>
                            <?php foreach ($practicalSchedules as $ps): ?>
                                <tr>
                                    <td><?= format_day_id($ps['exam_date']) ?>, <?= format_date_id($ps['exam_date']) ?></td>
                                    <td><?= e($ps['start_time']) ?> - <?= e($ps['end_time']) ?> WIB</td>
                                    <td><strong><?= e($ps['subject_name']) ?></strong></td>
                                    <td><?= e($ps['grade']) ?></td>
                                    <td><?= e($ps['location']) ?> (<?= e($ps['station'] ?: 'Semua Pos') ?>)</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 15, 16, 17. Rencana Administrasi, Pengawasan, & Pelaporan -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">15 - 17. Rencana Perlengkapan, Pengawasan, dan Pelaporan</div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label required">15. Rencana Administrasi dan Perlengkapan</label>
                <textarea name="facilities_plan" class="form-control" rows="3" required><?= e($program['facilities_plan']) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label required">16. Rencana Pengawasan dan Pengujian</label>
                <textarea name="supervision_plan" class="form-control" rows="3" required><?= e($program['supervision_plan']) ?></textarea>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label required">17. Rencana Dokumentasi dan Pelaporan</label>
                <textarea name="documentation_plan" class="form-control" rows="3" required><?= e($program['documentation_plan']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- 18. Rencana Anggaran Biaya (RAB) - Dynamic Table with Auto Calc -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">18. Rencana Anggaran Biaya (RAB) Penyelenggaraan Ujian</div>
            <button type="button" class="btn btn-primary btn-sm" onclick="addWorkProgramBudgetRow()">
                ➕ Tambah Baris Anggaran
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" id="workProgramBudgetTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th>Uraian Kebutuhan Anggaran</th>
                            <th style="width: 100px;">Volume</th>
                            <th style="width: 110px;">Satuan</th>
                            <th style="width: 150px;">Harga Satuan (Rp)</th>
                            <th style="width: 160px;" class="text-right">Jumlah Total (Rp)</th>
                            <th style="width: 80px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="workProgramBudgetTableBody">
                        <?php
                        $grandTotal = 0;
                        foreach ($budgets as $idx => $b):
                            $grandTotal += (float)$b['total_amount'];
                        ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><input type="text" name="budgets[<?= $idx + 1 ?>][description]" class="form-control" value="<?= e($b['description']) ?>" required></td>
                                <td><input type="number" step="0.1" name="budgets[<?= $idx + 1 ?>][volume]" class="form-control rab-volume" value="<?= $b['volume'] ?>" min="0" required oninput="calculateRabRow(this)"></td>
                                <td><input type="text" name="budgets[<?= $idx + 1 ?>][unit]" class="form-control" value="<?= e($b['unit']) ?>" required></td>
                                <td><input type="number" step="100" name="budgets[<?= $idx + 1 ?>][unit_price]" class="form-control rab-price" value="<?= $b['unit_price'] ?>" min="0" required oninput="calculateRabRow(this)"></td>
                                <td class="text-right"><span class="rab-subtotal font-bold"><?= rupiah($b['total_amount']) ?></span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); renumberRows('workProgramBudgetTableBody'); calculateRabGrandTotal();">Hapus</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: #f1f5f9; font-weight: bold; font-size: 14px;">
                            <td colspan="5" class="text-right">TOTAL KESELURUHAN RENCANA ANGGARAN (RAB):</td>
                            <td class="text-right" style="color: #1e3a8a;">
                                <span id="rabGrandTotalDisplay"><?= rupiah($grandTotal) ?></span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- 19 & 20. Penutup & Pengesahan -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">19 & 20. Penutup dan Halaman Pengesahan</div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label required">19. Catatan Penutup</label>
                <textarea name="closing_notes" class="form-control" rows="3" required><?= e($program['closing_notes']) ?></textarea>
            </div>
            <div class="alert alert-info" style="font-size: 12.5px; margin-bottom: 0;">
                <strong>Halaman Pengesahan (Bab 20):</strong> Otomatis ditandatangani oleh Ketua Panitia (<?= e($school['committee_head'] ?: '-') ?>) dan Kepala Sekolah (<?= e($school['principal_name']) ?> - NIP: <?= e($school['principal_nip']) ?>) sesuai tanggal pengesahan saat dicetak atau diekspor.
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary btn-lg">
                💾 Simpan Seluruh Program Kerja & RAB
            </button>
        </div>
    </div>
</form>
