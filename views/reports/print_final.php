<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; padding: 0 !important; margin: 0 !important; font-size: 12pt; line-height: 1.6; }
    .page-break { page-break-after: always; }
}

.report-document {
    max-width: 850px;
    margin: 20px auto;
    padding: 30px 45px;
    background: #fff;
    box-sizing: border-box;
    font-family: 'Times New Roman', Times, serif;
    color: #000;
}

/* Cover Styling */
.cover-page {
    min-height: 900px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    text-align: center;
    padding: 50px 20px;
    box-sizing: border-box;
}

.cover-title {
    font-size: 20pt;
    font-weight: bold;
    text-transform: uppercase;
    line-height: 1.3;
}

.cover-subtitle {
    font-size: 15pt;
    font-weight: bold;
    margin-top: 15px;
    text-transform: uppercase;
}

.cover-logo {
    width: 140px;
    height: 140px;
    object-fit: contain;
    margin: 40px auto;
}

.cover-footer {
    font-size: 13pt;
    font-weight: bold;
    text-transform: uppercase;
}

/* Section & Chapter styling */
.chapter-title {
    font-size: 14pt;
    font-weight: bold;
    text-align: center;
    text-transform: uppercase;
    margin-top: 30px;
    margin-bottom: 20px;
}

.sub-chapter-title {
    font-size: 12.5pt;
    font-weight: bold;
    margin-top: 18px;
    margin-bottom: 8px;
}

.report-p {
    text-align: justify;
    text-indent: 35px;
    margin-bottom: 12px;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
    font-size: 10.5pt;
}

.report-table th, .report-table td {
    border: 1px solid #000;
    padding: 6px 8px;
}

.report-table th {
    background: #f1f5f9;
    font-weight: bold;
    text-align: center;
}

.signature-grid {
    display: flex;
    justify-content: space-between;
    margin-top: 40px;
    page-break-inside: avoid;
}

.sig-block {
    width: 45%;
    text-align: center;
}
</style>

<div class="no-print" style="background: #f8fafc; border-bottom: 1px solid #cbd5e1; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <strong>Dokumen Cetak Laporan Akhir Panitia Ujian</strong>
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Dokumen Lengkap</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>
</div>

<div class="report-document">
    <!-- 1. HALAMAN COVER -->
    <div class="cover-page page-break">
        <div>
            <div class="cover-title"><?= e($report['report_title']) ?></div>
            <div class="cover-subtitle"><?= e($event['name']) ?></div>
            <div style="font-size: 13pt; font-weight: bold; margin-top: 5px;">TAHUN PELAJARAN <?= e($event['academic_year']) ?></div>
        </div>

        <div>
            <?php if (!empty($school['logo_url'])): ?>
                <img src="<?= e($school['logo_url']) ?>" alt="Logo Sekolah" class="cover-logo">
            <?php else: ?>
                <div style="width: 120px; height: 120px; border: 2px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26pt; font-weight: bold; margin: 40px auto;">RU</div>
            <?php endif; ?>
            <div style="font-size: 12pt; font-style: italic;">Disusun Oleh:</div>
            <div style="font-size: 14pt; font-weight: bold; margin-top: 4px;">PANITIA PELAKSANA UJIAN</div>
        </div>

        <div class="cover-footer">
            <div><?= strtoupper(e($school['name'])) ?></div>
            <div style="font-size: 11pt; font-weight: normal; text-transform: none; margin-top: 3px;"><?= e($school['address']) ?>, <?= e($school['district']) ?></div>
            <div style="margin-top: 8px;">TAHUN <?= date('Y') ?></div>
        </div>
    </div>

    <!-- 2. LEMBAR PENGESAHAN -->
    <div class="page-break" style="padding-top: 40px;">
        <div class="chapter-title">LEMBAR PENGESAHAN<br>LAPORAN AKHIR KEGIATAN PANITIA UJIAN</div>
        
        <p class="report-p">
            Laporan Akhir Kegiatan Panitia <strong><?= e($event['name']) ?></strong> Tahun Pelajaran <?= e($event['academic_year']) ?> pada <?= e($school['name']) ?> telah diperiksa, disetujui, dan disahkan pada:
        </p>

        <table style="width: 100%; margin: 25px 0 35px 35px; font-size: 12pt;">
            <tr>
                <td style="width: 150px;">Hari / Tanggal</td>
                <td style="width: 15px;">:</td>
                <td><strong><?= e(indo_day($report['approval_date'] ?? date('Y-m-d'))) ?>, <?= e(indo_date($report['approval_date'] ?? date('Y-m-d'), true)) ?></strong></td>
            </tr>
            <tr>
                <td>Tempat</td>
                <td>:</td>
                <td><?= e($school['name']) ?></td>
            </tr>
        </table>

        <div class="signature-grid">
            <div class="sig-block">
                <div>Mengetahui,</div>
                <div>Kepala <?= e($school['name']) ?></div>
                <div style="height: 75px;"></div>
                <div style="font-weight: bold; text-decoration: underline;"><?= e($school['principal_name']) ?></div>
                <div>NIP. <?= e($school['principal_nip'] ?? '-') ?></div>
            </div>
            <div class="sig-block">
                <div><?= e($school['district'] ?? 'Ditetapkan') ?>, <?= e(indo_date($report['approval_date'] ?? date('Y-m-d'))) ?></div>
                <div>Ketua Panitia Ujian,</div>
                <div style="height: 75px;"></div>
                <div style="font-weight: bold; text-decoration: underline;"><?= e($school['committee_chairman'] ?? 'Ketua Panitia') ?></div>
                <div>NIP. -</div>
            </div>
        </div>
    </div>

    <!-- 3. KATA PENGANTAR -->
    <div class="page-break" style="padding-top: 30px;">
        <div class="chapter-title">KATA PENGANTAR</div>
        <div style="text-align: justify; line-height: 1.7;">
            <?= nl2br(e($report['foreword'])) ?>
        </div>
        <div style="margin-top: 40px; text-align: right; padding-right: 30px;">
            <div><?= e($school['district'] ?? 'Panitia') ?>, <?= e(indo_date(date('Y-m-d'))) ?></div>
            <div style="margin-top: 5px; font-weight: bold;">Panitia Penyelenggara Ujian</div>
        </div>
    </div>

    <!-- 4. BAB I: PENDAHULUAN -->
    <div style="margin-top: 25px;">
        <div class="chapter-title">BAB I<br>PENDAHULUAN</div>
        <div class="sub-chapter-title">A. Latar Belakang</div>
        <p class="report-p"><?= nl2br(e($report['background'])) ?></p>

        <div class="sub-chapter-title">B. Dasar Hukum</div>
        <div style="margin-left: 20px; line-height: 1.6;"><?= nl2br(e($report['legal_basis'])) ?></div>

        <div class="sub-chapter-title">C. Maksud dan Tujuan</div>
        <div style="margin-left: 20px; line-height: 1.6;"><?= nl2br(e($report['objectives'])) ?></div>
    </div>

    <!-- 5. BAB II: PENGORGANISASIAN & KEPANITIAAN -->
    <div style="margin-top: 30px;">
        <div class="chapter-title">BAB II<br>PENGORGANISASIAN & KEPANITIAAN</div>
        <p class="report-p"><?= nl2br(e($report['committee_summary'])) ?></p>

        <?php if (!empty($committees)): ?>
            <div class="sub-chapter-title">Struktur dan Susunan Panitia Pelaksana:</div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Jabatan Panitia</th>
                        <th>Nama Pejabat / Guru</th>
                        <th>Peran & Tugas Utama</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($committees as $cIdx => $c): ?>
                        <tr>
                            <td class="text-center"><?= $cIdx + 1 ?></td>
                            <td><strong><?= e($c['position']) ?></strong></td>
                            <td><?= e($c['name']) ?></td>
                            <td><?= e($c['role'] ?: 'Melaksanakan tugas kepanitiaan') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- 6. BAB III & BAB IV: PERSIAPAN & PELAKSANAAN UJIAN TERTULIS -->
    <div style="margin-top: 30px;">
        <div class="chapter-title">BAB III<br>PERSIAPAN PENYELENGGARAAN UJIAN</div>
        <p class="report-p"><?= nl2br(e($report['preparation_summary'])) ?></p>

        <div class="chapter-title" style="margin-top: 35px;">BAB IV<br>PELAKSANAAN UJIAN TERTULIS</div>
        <p class="report-p"><?= nl2br(e($report['written_summary'])) ?></p>

        <?php if (!empty($schedules)): ?>
            <div class="sub-chapter-title">Jadwal Pelaksanaan Ujian Tertulis:</div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 35px;">No</th>
                        <th>Hari / Tanggal</th>
                        <th>Sesi / Waktu</th>
                        <th>Tingkat</th>
                        <th>Mata Pelajaran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedules as $sIdx => $s): ?>
                        <tr>
                            <td class="text-center"><?= $sIdx + 1 ?></td>
                            <td><?= e(indo_day($s['exam_date'])) ?>, <?= e(indo_date($s['exam_date'], true)) ?></td>
                            <td class="text-center">Sesi <?= e($s['session']) ?> (<?= substr(e($s['start_time']), 0, 5) ?> - <?= substr(e($s['end_time']), 0, 5) ?>)</td>
                            <td class="text-center">Kelas <?= e($s['grade']) ?></td>
                            <td><strong><?= e($s['subject_code']) ?></strong> - <?= e($s['subject_name']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- 7. BAB V & BAB VI: PRAKTIK & KEHADIRAN -->
    <div style="margin-top: 30px;">
        <div class="chapter-title">BAB V<br>PELAKSANAAN UJIAN PRAKTIK</div>
        <p class="report-p"><?= nl2br(e($report['practical_summary'])) ?></p>

        <div class="chapter-title" style="margin-top: 35px;">BAB VI<br>REKAPITULASI KEHADIRAN & PENGAWASAN</div>
        <p class="report-p"><?= nl2br(e($report['attendance_summary'])) ?></p>
    </div>

    <!-- 8. BAB VII: PENGELOLAAN NASKAH & LEMBAR JAWABAN -->
    <div style="margin-top: 30px;">
        <div class="chapter-title">BAB VII<br>PENGELOLAAN NASKAH SOAL & LEMBAR JAWABAN</div>
        <p class="report-p"><?= nl2br(e($report['distribution_summary'])) ?></p>
        <p class="report-p"><?= nl2br(e($report['answer_sheet_summary'])) ?></p>

        <?php if (!empty($batches)): ?>
            <div class="sub-chapter-title">Paket Amplop Pengemasan Lembar Jawaban (3 Mode):</div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 35px;">No</th>
                        <th>Kode Batch Amplop</th>
                        <th>Mode Pengemasan</th>
                        <th>Target</th>
                        <th class="text-center">Total Lembar</th>
                        <th class="text-center">Amplop</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($batches as $bIdx => $b): ?>
                        <tr>
                            <td class="text-center"><?= $bIdx + 1 ?></td>
                            <td><?= e($b['batch_code']) ?></td>
                            <td><?= e($b['packing_mode']) ?></td>
                            <td><?= e($b['class_name'] ?: $b['subject_name'] ?: 'Semua') ?></td>
                            <td class="text-center"><strong><?= (int)$b['total_sheets'] ?></strong></td>
                            <td class="text-center"><?= (int)$b['envelope_count'] ?> Amplop</td>
                            <td class="text-center"><?= e($b['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- 9. BAB VIII: KENDALA & REKAP INSIDEN -->
    <div style="margin-top: 30px;">
        <div class="chapter-title">BAB VIII<br>KENDALA, HAMBATAN, DAN PEMECAHAN MASALAH</div>
        <p class="report-p"><?= nl2br(e($report['obstacles_solutions'])) ?></p>

        <?php if (!empty($incidents)): ?>
            <div class="sub-chapter-title">Catatan Insiden / Kejadian Khusus Selama Ujian:</div>
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 35px;">No</th>
                        <th>Tanggal & Jam</th>
                        <th>Kategori & Lokasi</th>
                        <th>Kejadian</th>
                        <th>Tindak Lanjut & Solusi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($incidents as $iIdx => $i): ?>
                        <tr>
                            <td class="text-center"><?= $iIdx + 1 ?></td>
                            <td><?= e(indo_date($i['incident_date'], true)) ?><br><small><?= e($i['incident_time']) ?></small></td>
                            <td><?= strtoupper(e($i['category'])) ?><br><small><?= e($i['location']) ?></small></td>
                            <td><?= e($i['description']) ?></td>
                            <td style="color: #15803d; font-weight: 500;"><?= e($i['follow_up'] ?: 'Tertangani') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- 10. BAB IX & BAB X: EVALUASI & PENUTUP -->
    <div style="margin-top: 30px;">
        <div class="chapter-title">BAB IX<br>EVALUASI DAN REKOMENDASI KEGIATAN BERIKUTNYA</div>
        <p class="report-p"><?= nl2br(e($report['evaluation'])) ?></p>

        <div class="chapter-title" style="margin-top: 35px;">BAB X<br>PENUTUP</div>
        <p class="report-p"><?= nl2br(e($report['closing'])) ?></p>

        <div class="signature-grid" style="margin-top: 50px;">
            <div class="sig-block">
                <div>Mengetahui,</div>
                <div>Kepala <?= e($school['name']) ?></div>
                <div style="height: 75px;"></div>
                <div style="font-weight: bold; text-decoration: underline;"><?= e($school['principal_name']) ?></div>
                <div>NIP. <?= e($school['principal_nip'] ?? '-') ?></div>
            </div>
            <div class="sig-block">
                <div><?= e($school['district'] ?? 'Ditetapkan') ?>, <?= e(indo_date($report['approval_date'] ?? date('Y-m-d'))) ?></div>
                <div>Ketua Panitia Ujian,</div>
                <div style="height: 75px;"></div>
                <div style="font-weight: bold; text-decoration: underline;"><?= e($school['committee_chairman'] ?? 'Ketua Panitia') ?></div>
                <div>NIP. -</div>
            </div>
        </div>
    </div>
</div>
