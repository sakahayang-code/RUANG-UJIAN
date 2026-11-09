<!-- COVER PAGE -->
<div style="min-height: 95vh; display: flex; flex-direction: column; justify-content: space-between; text-align: center; padding: 40px 20px; page-break-after: always;">
    <div>
        <div style="font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
            <?= e($school['letterhead_text'] ? explode("\n", $school['letterhead_text'])[0] : 'PEMERINTAH DAERAH') ?>
        </div>
        <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase;">
            DINAS PENDIDIKAN
        </div>
        <div style="font-size: 18pt; font-weight: bold; text-transform: uppercase; margin-top: 5px; color: #1e3a8a;">
            <?= e($school['name']) ?>
        </div>
        <div style="font-size: 10pt; font-style: italic; color: #475569; margin-top: 3px;">
            <?= e($school['address']) ?> - Telp: <?= e($school['phone']) ?>
        </div>
    </div>

    <div style="margin: 60px 0;">
        <div style="width: 140px; height: 140px; margin: 0 auto 30px; display: flex; align-items: center; justify-content: center; border: 3px double #1e3a8a; border-radius: 50%;">
            <?php if (!empty($school['logo_path']) && file_exists(dirname(__DIR__, 2) . '/storage/uploads/' . $school['logo_path'])): ?>
                <img src="<?= url('/file/view?path=' . urlencode($school['logo_path'])) ?>" alt="Logo" style="max-width: 110px; max-height: 110px; object-fit: contain;">
            <?php else: ?>
                <span style="font-size: 32pt; font-weight: 900; color: #1e3a8a;">RU</span>
            <?php endif; ?>
        </div>

        <h1 style="font-size: 18pt; font-weight: bold; text-transform: uppercase; line-height: 1.4; margin-bottom: 15px;">
            <?= nl2br(e($program['title'])) ?>
        </h1>
        <div style="font-size: 13pt; font-weight: bold; letter-spacing: 0.5px;">
            TAHUN PELAJARAN <?= e($program['academic_year']) ?>
        </div>
        <div style="font-size: 11pt; margin-top: 8px; color: #334155;">
            Tema: "<em><?= e($program['theme']) ?></em>"
        </div>
    </div>

    <div>
        <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase;">
            PANITIA PENYELENGGARA UJIAN SEKOLAH<br>
            <?= e($school['name']) ?><br>
            <?= e($school['signature_city']) ?>, <?= date('Y') ?>
        </div>
    </div>
</div>

<!-- KATA PENGANTAR -->
<div style="page-break-after: always; padding-top: 20px;">
    <div class="doc-title">
        <h2>KATA PENGANTAR</h2>
    </div>
    <div style="text-align: justify; font-size: 11pt; line-height: 1.6; margin-bottom: 20px;">
        <?= nl2br(e($program['foreword'])) ?>
    </div>

    <div class="signature-block">
        <div></div>
        <div class="signature-box">
            <?= e($school['signature_city']) ?>, <?= format_date_id($program['approval_date']) ?><br>
            Ketua Panitia Ujian,
            <div class="signature-space"></div>
            <div class="signature-name"><?= e($school['committee_head'] ?: 'Ketua Panitia') ?></div>
            <div>NIP. -</div>
        </div>
    </div>
</div>

<!-- DAFTAR ISI -->
<div style="page-break-after: always; padding-top: 20px;">
    <div class="doc-title">
        <h2>DAFTAR ISI</h2>
    </div>
    <table style="width: 100%; font-size: 11pt; line-height: 2;">
        <tr><td><strong>KATA PENGANTAR</strong></td><td style="text-align: right;">i</td></tr>
        <tr><td><strong>DAFTAR ISI</strong></td><td style="text-align: right;">ii</td></tr>
        <tr><td><strong>BAB I. PENDAHULUAN</strong></td><td style="text-align: right;">1</td></tr>
        <tr><td style="padding-left: 20px;">A. Latar Belakang</td><td style="text-align: right;">1</td></tr>
        <tr><td style="padding-left: 20px;">B. Dasar Hukum</td><td style="text-align: right;">1</td></tr>
        <tr><td style="padding-left: 20px;">C. Maksud dan Tujuan</td><td style="text-align: right;">2</td></tr>
        <tr><td style="padding-left: 20px;">D. Nama dan Tema Kegiatan</td><td style="text-align: right;">2</td></tr>
        <tr><td style="padding-left: 20px;">E. Waktu dan Tempat Pelaksanaan</td><td style="text-align: right;">2</td></tr>
        <tr><td style="padding-left: 20px;">F. Sasaran dan Peserta Ujian</td><td style="text-align: right;">2</td></tr>
        <tr><td><strong>BAB II. STRUKTUR KEPANITIAAN & URAIAN TUGAS</strong></td><td style="text-align: right;">3</td></tr>
        <tr><td><strong>BAB III. RENCANA KERJA DAN TAHAPAN PERSIAPAN</strong></td><td style="text-align: right;">4</td></tr>
        <tr><td><strong>BAB IV. JADWAL UJIAN TERTULIS & PRAKTIK</strong></td><td style="text-align: right;">5</td></tr>
        <tr><td><strong>BAB V. RENCANA ADMINISTRASI, PENGAWASAN & PELAPORAN</strong></td><td style="text-align: right;">6</td></tr>
        <tr><td><strong>BAB VI. RENCANA ANGGARAN BIAYA (RAB)</strong></td><td style="text-align: right;">7</td></tr>
        <tr><td><strong>BAB VII. PENUTUP & LEMBAR PENGESAHAN</strong></td><td style="text-align: right;">8</td></tr>
    </table>
</div>

<!-- BAB I. PENDAHULUAN -->
<div style="page-break-after: always; padding-top: 20px;">
    <div style="font-size: 13pt; font-weight: bold; margin-bottom: 12px; border-bottom: 2px solid #000; padding-bottom: 4px;">
        BAB I. PENDAHULUAN
    </div>

    <div style="margin-bottom: 14px;">
        <strong>A. Latar Belakang</strong>
        <div style="text-align: justify; margin-top: 4px;"><?= nl2br(e($program['background'])) ?></div>
    </div>

    <div style="margin-bottom: 14px;">
        <strong>B. Dasar Hukum</strong>
        <div style="text-align: justify; margin-top: 4px;"><?= nl2br(e($program['legal_basis'])) ?></div>
    </div>

    <div style="margin-bottom: 14px;">
        <strong>C. Maksud dan Tujuan</strong>
        <div style="text-align: justify; margin-top: 4px;"><?= nl2br(e($program['objectives'])) ?></div>
    </div>

    <div style="margin-bottom: 14px;">
        <strong>D. Nama dan Tema Kegiatan</strong>
        <div style="margin-top: 4px;">
            1. Nama Kegiatan: <strong><?= e($event['name']) ?></strong><br>
            2. Tema Kegiatan: <em>"<?= e($program['theme']) ?>"</em>
        </div>
    </div>

    <div style="margin-bottom: 14px;">
        <strong>E. Waktu dan Tempat Pelaksanaan</strong>
        <div style="margin-top: 4px;">
            1. Waktu: <?= format_date_id($event['start_date']) ?> s.d. <?= format_date_id($event['end_date']) ?><br>
            2. Tempat: Kampus <?= e($school['name']) ?>
        </div>
    </div>

    <div style="margin-bottom: 14px;">
        <strong>F. Sasaran dan Peserta</strong>
        <div style="margin-top: 4px;"><?= e($program['target_description']) ?></div>
    </div>
</div>

<!-- BAB II. STRUKTUR KEPANITIAAN -->
<div style="page-break-after: always; padding-top: 20px;">
    <div style="font-size: 13pt; font-weight: bold; margin-bottom: 12px; border-bottom: 2px solid #000; padding-bottom: 4px;">
        BAB II. STRUKTUR KEPANITIAAN & URAIAN TUGAS
    </div>

    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Jabatan Panitia</th>
                <th>Nama Personel</th>
                <th>NIP</th>
                <th>Seksi / Bidang Tugas</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($committeeMembers as $idx => $cm): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td><strong><?= e($cm['role_title']) ?></strong></td>
                    <td><?= e($cm['teacher_name'] ?: $cm['custom_name']) ?></td>
                    <td><?= e($cm['teacher_nip'] ?: $cm['custom_nip'] ?: '-') ?></td>
                    <td><?= e($cm['section_name'] ?: '-') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- BAB III. RENCANA KERJA TAHAPAN PERSIAPAN -->
<div style="page-break-after: always; padding-top: 20px;">
    <div style="font-size: 13pt; font-weight: bold; margin-bottom: 12px; border-bottom: 2px solid #000; padding-bottom: 4px;">
        BAB III. RENCANA KERJA DAN TAHAPAN PERSIAPAN
    </div>

    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Tahapan / Uraian Kegiatan</th>
                <th style="width: 110px;">Tanggal Mulai</th>
                <th style="width: 110px;">Tanggal Selesai</th>
                <th style="width: 140px;">Penanggung Jawab</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($activities as $idx => $act): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td><strong><?= e($act['activity_name']) ?></strong></td>
                    <td class="text-center"><?= format_date_id($act['start_date']) ?></td>
                    <td class="text-center"><?= format_date_id($act['end_date']) ?></td>
                    <td><?= e($act['pic_name']) ?></td>
                    <td><?= e($act['notes']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- BAB IV. JADWAL UJIAN -->
<div style="page-break-after: always; padding-top: 20px;">
    <div style="font-size: 13pt; font-weight: bold; margin-bottom: 12px; border-bottom: 2px solid #000; padding-bottom: 4px;">
        BAB IV. JADWAL UJIAN TERTULIS DAN PRAKTIK
    </div>

    <div style="font-weight: bold; margin: 10px 0 6px;">A. Jadwal Ujian Tertulis</div>
    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Hari / Tanggal</th>
                <th>Sesi</th>
                <th>Waktu</th>
                <th>Mata Pelajaran</th>
                <th>Tingkat</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($writtenSchedules as $idx => $ws): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td><?= format_day_id($ws['exam_date']) ?>, <?= format_date_id($ws['exam_date']) ?></td>
                    <td class="text-center">Sesi <?= e($ws['session']) ?></td>
                    <td class="text-center"><?= e($ws['start_time']) ?> - <?= e($ws['end_time']) ?> WIB</td>
                    <td><strong><?= e($ws['subject_name']) ?></strong></td>
                    <td class="text-center"><?= e($ws['grade']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="font-weight: bold; margin: 20px 0 6px;">B. Jadwal Ujian Praktik</div>
    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Hari / Tanggal</th>
                <th>Waktu</th>
                <th>Mata Pelajaran</th>
                <th>Tingkat</th>
                <th>Lokasi / Stasiun</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($practicalSchedules as $idx => $ps): ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td><?= format_day_id($ps['exam_date']) ?>, <?= format_date_id($ps['exam_date']) ?></td>
                    <td class="text-center"><?= e($ps['start_time']) ?> - <?= e($ps['end_time']) ?> WIB</td>
                    <td><strong><?= e($ps['subject_name']) ?></strong></td>
                    <td class="text-center"><?= e($ps['grade']) ?></td>
                    <td><?= e($ps['location']) ?> (<?= e($ps['station'] ?: 'Semua') ?>)</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- BAB V. RENCANA ADMINISTRASI, PENGAWASAN, PELAPORAN -->
<div style="page-break-after: always; padding-top: 20px;">
    <div style="font-size: 13pt; font-weight: bold; margin-bottom: 12px; border-bottom: 2px solid #000; padding-bottom: 4px;">
        BAB V. RENCANA ADMINISTRASI, PENGAWASAN & PELAPORAN
    </div>

    <div style="margin-bottom: 14px;">
        <strong>A. Rencana Administrasi dan Perlengkapan</strong>
        <div style="text-align: justify; margin-top: 4px;"><?= nl2br(e($program['facilities_plan'])) ?></div>
    </div>

    <div style="margin-bottom: 14px;">
        <strong>B. Rencana Pengawasan dan Pengujian</strong>
        <div style="text-align: justify; margin-top: 4px;"><?= nl2br(e($program['supervision_plan'])) ?></div>
    </div>

    <div style="margin-bottom: 14px;">
        <strong>C. Rencana Dokumentasi dan Pelaporan</strong>
        <div style="text-align: justify; margin-top: 4px;"><?= nl2br(e($program['documentation_plan'])) ?></div>
    </div>
</div>

<!-- BAB VI. RENCANA ANGGARAN BIAYA (RAB) -->
<div style="page-break-after: always; padding-top: 20px;">
    <div style="font-size: 13pt; font-weight: bold; margin-bottom: 12px; border-bottom: 2px solid #000; padding-bottom: 4px;">
        BAB VI. RENCANA ANGGARAN BIAYA (RAB)
    </div>

    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th>Uraian Kebutuhan Anggaran</th>
                <th style="width: 80px;">Volume</th>
                <th style="width: 80px;">Satuan</th>
                <th style="width: 130px;">Harga Satuan (Rp)</th>
                <th style="width: 140px;">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grandTotal = 0;
            foreach ($budgets as $idx => $b):
                $grandTotal += (float)$b['total_amount'];
            ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td><?= e($b['description']) ?></td>
                    <td class="text-center"><?= $b['volume'] ?></td>
                    <td class="text-center"><?= e($b['unit']) ?></td>
                    <td class="text-right"><?= rupiah($b['unit_price']) ?></td>
                    <td class="text-right"><?= rupiah($b['total_amount']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f1f5f9;">
                <td colspan="5" class="text-right">TOTAL KESELURUHAN RENCANA ANGGARAN:</td>
                <td class="text-right"><?= rupiah($grandTotal) ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- BAB VII. PENUTUP & PENGESAHAN -->
<div style="padding-top: 20px;">
    <div style="font-size: 13pt; font-weight: bold; margin-bottom: 12px; border-bottom: 2px solid #000; padding-bottom: 4px;">
        BAB VII. PENUTUP DAN LEMBAR PENGESAHAN
    </div>

    <div style="text-align: justify; line-height: 1.6; margin-bottom: 30px;">
        <?= nl2br(e($program['closing_notes'])) ?>
    </div>

    <div style="text-align: center; margin: 40px 0 20px;">
        <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase;">
            LEMBAR PENGESAHAN PROGRAM KERJA
        </div>
        <div style="font-size: 11pt;">
            Disahkan di: <?= e($school['signature_city']) ?> • Tanggal: <?= format_date_id($program['approval_date']) ?>
        </div>
    </div>

    <div class="signature-block">
        <div class="signature-box">
            Mengetahui,<br>
            Kepala <?= e($school['name']) ?>
            <div class="signature-space"></div>
            <div class="signature-name"><?= e($school['principal_name']) ?></div>
            <div>NIP. <?= e($school['principal_nip']) ?></div>
        </div>

        <div class="signature-box">
            Ketua Panitia Ujian,<br>
            <?= e($event['name']) ?>
            <div class="signature-space"></div>
            <div class="signature-name"><?= e($school['committee_head'] ?: 'Ketua Panitia') ?></div>
            <div>NIP. -</div>
        </div>
    </div>
</div>
