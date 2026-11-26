<div class="page-header">
    <div>
        <h1 class="page-title">Laporan Akhir Kegiatan Panitia Ujian</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Struktur laporan resmi 18 Bab / Bagian lengkap terintegrasi dengan database kegiatan.</p>
    </div>
    <div class="page-actions">
        <a href="<?= url('/reports/final/print') ?>" target="_blank" class="btn btn-outline">
            🖨️ Cetak Laporan Resmi
        </a>
        <a href="<?= url('/reports/final/export-docx') ?>" class="btn btn-primary">
            📄 Unduh File Word (.docx)
        </a>
    </div>
</div>

<!-- Ringkasan Statistik Laporan Terintegrasi -->
<div class="stat-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px;">
    <div class="card p-3">
        <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Total Peserta</div>
        <div style="font-size: 24px; font-weight: bold; color: #1e293b; margin-top: 5px;"><?= (int)$stats['total_participants'] ?> Siswa</div>
    </div>
    <div class="card p-3">
        <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Ruang Ujian</div>
        <div style="font-size: 24px; font-weight: bold; color: #2563eb; margin-top: 5px;"><?= (int)$stats['total_rooms'] ?> Ruang</div>
    </div>
    <div class="card p-3">
        <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Panitia & Pengawas</div>
        <div style="font-size: 24px; font-weight: bold; color: #059669; margin-top: 5px;"><?= (int)$stats['total_committees'] ?> / <?= (int)$stats['total_supervisors'] ?> Orang</div>
    </div>
    <div class="card p-3">
        <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Jadwal Tertulis & Praktik</div>
        <div style="font-size: 24px; font-weight: bold; color: #7c3aed; margin-top: 5px;"><?= (int)$stats['total_written_schedules'] ?> / <?= (int)$stats['total_practical_schedules'] ?> Sesi</div>
    </div>
    <div class="card p-3">
        <div style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">LJU Diterima & Amplop</div>
        <div style="font-size: 24px; font-weight: bold; color: #0284c7; margin-top: 5px;"><?= (int)$stats['total_answer_sheets'] ?> lbr (<?= (int)$stats['total_batches'] ?> Batch)</div>
    </div>
    <div class="card p-3" style="<?= (int)$stats['total_answer_discrepancy'] !== 0 ? 'border: 2px solid #ef4444;' : '' ?>">
        <div style="font-size: 11px; font-weight: 600; color: <?= (int)$stats['total_answer_discrepancy'] !== 0 ? '#ef4444' : '#64748b' ?>; text-transform: uppercase;">Selisih / Insiden</div>
        <div style="font-size: 24px; font-weight: bold; color: <?= (int)$stats['total_answer_discrepancy'] !== 0 ? '#dc2626' : '#10b981' ?>; margin-top: 5px;">
            <?= (int)$stats['total_answer_discrepancy'] ?> / <?= (int)$stats['total_incidents'] ?>
        </div>
    </div>
</div>

<form method="POST" action="<?= url('/reports/final') ?>" class="card p-4">
    <?= csrf_field() ?>

    <div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 2px solid #e2e8f0;">
        <div>
            <h2 style="font-size: 18px; margin: 0; color: #0f172a;">Editor Dokumen Laporan Akhir</h2>
            <small class="text-muted">Setiap bab dapat disesuaikan narasi dan deskripsinya sebelum dicetak atau diekspor ke Word.</small>
        </div>
        <button type="submit" class="btn btn-success">
            💾 Simpan Seluruh Perubahan Laporan
        </button>
    </div>

    <!-- Section: Judul & Tahun Pelajaran -->
    <div class="row mb-3">
        <div class="col" style="flex: 2;">
            <label class="form-label required">Judul Dokumen Laporan Akhir</label>
            <input type="text" name="report_title" class="form-control" value="<?= e($report['report_title']) ?>" required>
        </div>
        <div class="col" style="flex: 1;">
            <label class="form-label required">Tahun Pelajaran</label>
            <input type="text" name="academic_year" class="form-control" value="<?= e($report['academic_year']) ?>" required>
        </div>
        <div class="col" style="flex: 1;">
            <label class="form-label required">Tanggal Pengesahan</label>
            <input type="date" name="approval_date" class="form-control" value="<?= e($report['approval_date'] ?? date('Y-m-d')) ?>" required>
        </div>
    </div>

    <!-- Section 1-4: Kata Pengantar & Legalitas -->
    <div class="mb-4">
        <h3 style="font-size: 15px; color: #1e3a8a; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px;">Bagian Awal: Kata Pengantar & Landasan Dokumen</h3>
        <div class="form-group mb-3">
            <label class="form-label">Kata Pengantar</label>
            <textarea name="foreword" class="form-control" rows="4"><?= e($report['foreword']) ?></textarea>
        </div>
        <div class="row mb-3">
            <div class="col" style="flex: 1;">
                <label class="form-label">Latar Belakang</label>
                <textarea name="background" class="form-control" rows="4"><?= e($report['background']) ?></textarea>
            </div>
            <div class="col" style="flex: 1;">
                <label class="form-label">Dasar Hukum</label>
                <textarea name="legal_basis" class="form-control" rows="4"><?= e($report['legal_basis']) ?></textarea>
            </div>
        </div>
        <div class="form-group mb-3">
            <label class="form-label">Maksud dan Tujuan Kegiatan</label>
            <textarea name="objectives" class="form-control" rows="3"><?= e($report['objectives']) ?></textarea>
        </div>
    </div>

    <!-- Section 5-10: Uraian Bab Pelaksanaan -->
    <div class="mb-4">
        <h3 style="font-size: 15px; color: #1e3a8a; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px;">Bab II - Bab VII: Laporan Teknis Penyelenggaraan</h3>
        <div class="row mb-3">
            <div class="col" style="flex: 1;">
                <label class="form-label">Bab II: Pengorganisasian & Kepanitiaan</label>
                <textarea name="committee_summary" class="form-control" rows="3"><?= e($report['committee_summary']) ?></textarea>
            </div>
            <div class="col" style="flex: 1;">
                <label class="form-label">Bab III: Persiapan Dokumen, Soal & Ruang</label>
                <textarea name="preparation_summary" class="form-control" rows="3"><?= e($report['preparation_summary']) ?></textarea>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col" style="flex: 1;">
                <label class="form-label">Bab IV: Pelaksanaan Ujian Tertulis & Pengawasan</label>
                <textarea name="written_summary" class="form-control" rows="3"><?= e($report['written_summary']) ?></textarea>
            </div>
            <div class="col" style="flex: 1;">
                <label class="form-label">Bab V: Pelaksanaan Ujian Praktik</label>
                <textarea name="practical_summary" class="form-control" rows="3"><?= e($report['practical_summary']) ?></textarea>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col" style="flex: 1;">
                <label class="form-label">Bab VI: Rekapitulasi Kehadiran (Peserta, Pengawas, Panitia)</label>
                <textarea name="attendance_summary" class="form-control" rows="3"><?= e($report['attendance_summary']) ?></textarea>
            </div>
            <div class="col" style="flex: 1;">
                <label class="form-label">Bab VII: Distribusi Soal & Pengemasan Lembar Jawaban</label>
                <textarea name="distribution_summary" class="form-control" rows="3"><?= e($report['distribution_summary']) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Section 11-14: Hambatan, Evaluasi & Penutup -->
    <div class="mb-4">
        <h3 style="font-size: 15px; color: #1e3a8a; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px;">Bab VIII - Bab X: Evaluasi, Solusi & Penutup</h3>
        <div class="form-group mb-3">
            <label class="form-label">Bab VIII: Kendala, Hambatan, dan Solusi Pemecahan</label>
            <textarea name="obstacles_solutions" class="form-control" rows="3"><?= e($report['obstacles_solutions']) ?></textarea>
        </div>
        <div class="form-group mb-3">
            <label class="form-label">Bab IX: Evaluasi dan Rekomendasi Kegiatan Berikutnya</label>
            <textarea name="evaluation" class="form-control" rows="3"><?= e($report['evaluation']) ?></textarea>
        </div>
        <div class="form-group mb-3">
            <label class="form-label">Bab X: Penutup</label>
            <textarea name="closing" class="form-control" rows="3"><?= e($report['closing']) ?></textarea>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid #e2e8f0;">
        <button type="submit" class="btn btn-success btn-lg">
            💾 Simpan Seluruh Perubahan Laporan
        </button>
    </div>
</form>
