<div class="page-header">
    <div>
        <h1 class="page-title">Surat Undangan Rapat & Surat Tugas</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Generator persuratan resmi kepanitiaan ujian.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" data-modal-open="createLetterModal">
            ✉️ Buat Surat Baru
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;" class="text-center">No</th>
                    <th>Nomor Surat</th>
                    <th>Jenis Surat</th>
                    <th>Perihal</th>
                    <th>Tanggal Surat</th>
                    <th>Agenda / Rincian</th>
                    <th>Penandatangan</th>
                    <th style="width: 180px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($letters)): ?>
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 30px; color: #94a3b8;">
                            Belum ada surat yang dibuat. Klik tombol "Buat Surat Baru" di atas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($letters as $idx => $l): ?>
                        <tr>
                            <td class="text-center"><?= $idx + 1 ?></td>
                            <td><code><?= e($l['letter_number']) ?></code></td>
                            <td>
                                <span class="badge <?= str_contains($l['letter_type'], 'undangan') ? 'badge-primary' : 'badge-info' ?>">
                                    <?= ucwords(str_replace('_', ' ', $l['letter_type'])) ?>
                                </span>
                            </td>
                            <td><strong><?= e($l['subject']) ?></strong></td>
                            <td><?= format_date_id($l['letter_date']) ?></td>
                            <td style="font-size: 12px; color: #475569;">
                                <?php if (!empty($l['meeting_date'])): ?>
                                    Rapat: <?= format_day_id($l['meeting_date']) ?>, <?= format_date_id($l['meeting_date']) ?><br>
                                    Tempat: <?= e($l['meeting_place']) ?>
                                <?php else: ?>
                                    <?= e(substr($l['content'], 0, 70)) ?>...
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px;">
                                <strong><?= e($l['signer_name']) ?></strong><br>
                                <span style="color: #64748b;"><?= e($l['signer_title']) ?></span>
                            </td>
                            <td class="text-center">
                                <a href="<?= url('/committee/letters/print?id=' . $l['id']) ?>" target="_blank" class="btn btn-outline btn-sm" title="Cetak / Pratinjau">
                                    🖨️
                                </a>
                                <a href="<?= url('/committee/letters/export-docx?id=' . $l['id']) ?>" class="btn btn-secondary btn-sm" title="Ekspor ke Microsoft Word (.docx)">
                                    📄 Word
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Buat Surat Baru -->
<div class="modal-overlay" id="createLetterModal">
    <div class="modal-container modal-lg">
        <div class="modal-header">
            <div class="modal-title">Buat Surat Resmi Panitia Ujian</div>
            <button type="button" class="modal-close" data-modal-close="createLetterModal">&times;</button>
        </div>
        <form action="<?= url('/committee/letters/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jenis Surat</label>
                            <select name="letter_type" id="letterTypeSelect" class="form-control" onchange="applyLetterTemplate(this)" required>
                                <option value="undangan_rapat">Undangan Rapat Panitia</option>
                                <option value="tugas_kisi_kisi">Surat Tugas Penyusun Kisi-kisi</option>
                                <option value="tugas_soal">Surat Tugas Penyusun Naskah Soal</option>
                                <option value="tugas_pengawas">Surat Tugas Pengawas Ruang</option>
                                <option value="tugas_penguji">Surat Tugas Penguji Ujian Praktik</option>
                                <option value="tugas_panitia">Surat Tugas Kepanitiaan Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Nomor Surat (Otomatis)</label>
                            <input type="text" name="letter_number" class="form-control" value="<?= e($autoNumber) ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Tanggal Surat</label>
                            <input type="date" name="letter_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Perihal / Hal Surat</label>
                    <input type="text" name="subject" id="letterSubject" class="form-control" value="Undangan Rapat Koordinasi Panitia <?= e($event['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Tujuan Penerima Surat (Yth.)</label>
                    <input type="text" name="target_recipients" class="form-control" placeholder="Bapak/Ibu Panitia Ujian / Dewan Guru" value="Bapak/Ibu Anggota Panitia Ujian">
                </div>

                <!-- Bagian Khusus Rapat -->
                <div id="meetingDetailsBox" style="background: #f8fafc; padding: 14px; border: 1px solid #e2e8f0; border-radius: var(--radius-sm); margin-bottom: 16px;">
                    <div style="font-weight: 600; font-size: 13px; margin-bottom: 8px; color: #1e3a8a;">📅 Rincian Pelaksanaan Rapat (Khusus Undangan)</div>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Tanggal Rapat</label>
                                <input type="date" name="meeting_date" class="form-control" value="<?= date('Y-m-d', strtotime('+2 days')) ?>">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Waktu Rapat</label>
                                <input type="text" name="meeting_time" class="form-control" value="09.00 - 11.30">
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Tempat Rapat</label>
                                <input type="text" name="meeting_place" class="form-control" value="Ruang Guru / Laboratorium Komputer">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Agenda Rapat</label>
                        <input type="text" name="meeting_agenda" class="form-control" value="Koordinasi Kesiapan Naskah, Ruang, dan Jadwal Ujian">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Isi Dokumen / Surat (Dapat Diedit)</label>
                    <textarea name="content" id="letterContent" class="form-control" rows="5" required>Dengan hormat,
Sehubungan dengan persiapan pelaksanaan kegiatan <?= e($event['name']) ?> Tahun Pelajaran <?= e($event['academic_year']) ?>, kami mengundang Bapak/Ibu untuk hadir dalam rapat koordinasi yang akan dilaksanakan sesuai rincian waktu dan tempat di atas.

Mengingat pentingnya agenda tersebut, kami mengharapkan kehadiran Bapak/Ibu tepat pada waktunya. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.</textarea>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Nama Penandatangan</label>
                            <input type="text" name="signer_name" class="form-control" value="<?= e($school['principal_name']) ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">NIP Penandatangan</label>
                            <input type="text" name="signer_nip" class="form-control" value="<?= e($school['principal_nip']) ?>">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required">Jabatan Penandatangan</label>
                            <input type="text" name="signer_title" class="form-control" value="Kepala Sekolah" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close="createLetterModal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan & Buat Surat</button>
            </div>
        </form>
    </div>
</div>

<script>
const templates = <?= json_encode($templates) ?>;
const eventName = <?= json_encode($event['name']) ?>;
const academicYear = <?= json_encode($event['academic_year']) ?>;

function applyLetterTemplate(select) {
    const val = select.value;
    const meetingBox = document.getElementById('meetingDetailsBox');
    const subjectInput = document.getElementById('letterSubject');
    const contentArea = document.getElementById('letterContent');

    if (val === 'undangan_rapat') {
        meetingBox.style.display = 'block';
        subjectInput.value = 'Undangan Rapat Koordinasi Panitia ' + eventName;
        contentArea.value = `Dengan hormat,\nSehubungan dengan persiapan pelaksanaan kegiatan ${eventName} Tahun Pelajaran ${academicYear}, kami mengundang Bapak/Ibu untuk hadir dalam rapat koordinasi yang akan dilaksanakan sesuai jadwal di atas.\n\nMengingat pentingnya agenda tersebut, kami mengharapkan kehadiran Bapak/Ibu tepat pada waktunya. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.`;
    } else {
        meetingBox.style.display = 'none';
        const typeTitle = select.options[select.selectedIndex].text;
        subjectInput.value = typeTitle + ' ' + eventName;

        let found = templates.find(t => t.code === val);
        if (found) {
            let body = found.template_body
                .replace(/{nama_ujian}/g, eventName)
                .replace(/{tahun_pelajaran}/g, academicYear);
            contentArea.value = body;
        } else {
            contentArea.value = `Kepala Sekolah dengan ini memberikan tugas kepada nama-nama terlampir untuk melaksanakan tugas pada kegiatan ${eventName} Tahun Pelajaran ${academicYear} dengan penuh tanggung jawab.`;
        }
    }
}
</script>
