<div class="page-header">
    <div>
        <h1 class="page-title">Pusat Cetak Perlengkapan Ujian</h1>
        <p class="page-subtitle"><?= e($event['name']) ?> • Cetak kartu peserta, name tag, label pintu ruang, label amplop naskah soal, dan label amplop lembar jawaban.</p>
    </div>
</div>

<div class="row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
    <!-- 1. Kartu Peserta Ujian -->
    <div class="card p-4">
        <div style="font-size: 32px; margin-bottom: 10px;">🪪</div>
        <h3 style="margin-top: 0; font-size: 18px;">Kartu Peserta Ujian</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Cetak kartu resmi peserta ujian lengkap dengan foto, nomor peserta, ruang, meja, serta QR Code verifikasi.
        </p>
        <form method="GET" action="<?= url('/print/participant-cards') ?>" target="_blank" style="margin-top: 15px;">
            <div class="form-group mb-2">
                <label style="font-size: 11px; font-weight: 600;">Filter Kelas:</label>
                <select name="class_id" class="form-control" style="font-size: 12px; padding: 6px 10px;">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (Tingkat <?= e($c['grade']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-3">
                <label style="font-size: 11px; font-weight: 600;">Filter Ruang Ujian:</label>
                <select name="room_id" class="form-control" style="font-size: 12px; padding: 6px 10px;">
                    <option value="">-- Semua Ruang --</option>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?= $r['id'] ?>">Ruang <?= e($r['room_number']) ?> (<?= e($r['name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block" style="width: 100%;">
                🖨️ Buka & Cetak Kartu Peserta
            </button>
        </form>
    </div>

    <!-- 2. Name Tag Panitia & Pengawas -->
    <div class="card p-4">
        <div style="font-size: 32px; margin-bottom: 10px;">🏷️</div>
        <h3 style="margin-top: 0; font-size: 18px;">Name Tag / Tanda Pengenal</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Cetak kartu tanda pengenal resmi untuk Panitia Pelaksana dan Pengawas Ruang Ujian dengan QR Code verifikasi.
        </p>
        <form method="GET" action="<?= url('/print/name-tags') ?>" target="_blank" style="margin-top: 15px;">
            <div class="form-group mb-3">
                <label style="font-size: 11px; font-weight: 600;">Pilih Kategori:</label>
                <select name="type" class="form-control" style="font-size: 12px; padding: 6px 10px;">
                    <option value="panitia">Panitia Pelaksana Ujian</option>
                    <option value="pengawas">Pengawas Ruang Ujian</option>
                </select>
            </div>
            <div style="height: 52px;"></div>
            <button type="submit" class="btn btn-primary btn-block" style="width: 100%;">
                🖨️ Buka & Cetak Name Tag
            </button>
        </form>
    </div>

    <!-- 3. Label Pintu Ruang (Room Signs) -->
    <div class="card p-4">
        <div style="font-size: 32px; margin-bottom: 10px;">🚪</div>
        <h3 style="margin-top: 0; font-size: 18px;">Label Pintu Ruang Ujian</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Plang nomor ruang berukuran besar untuk ditempel pada pintu ruang, lengkap dengan daftar kelas dan rentang nomor peserta.
        </p>
        <form method="GET" action="<?= url('/print/room-signs') ?>" target="_blank" style="margin-top: 15px;">
            <div class="form-group mb-3">
                <label style="font-size: 11px; font-weight: 600;">Pilih Ruang:</label>
                <select name="room_id" class="form-control" style="font-size: 12px; padding: 6px 10px;">
                    <option value="">-- Semua Ruang (Cetak Sekaligus) --</option>
                    <?php foreach ($rooms as $r): ?>
                        <option value="<?= $r['id'] ?>">Ruang <?= e($r['room_number']) ?> (<?= e($r['name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="height: 52px;"></div>
            <button type="submit" class="btn btn-primary btn-block" style="width: 100%;">
                🖨️ Buka & Cetak Label Pintu
            </button>
        </form>
    </div>

    <!-- 4. Label Amplop Naskah Soal (1 Ruang = 1 Amplop) -->
    <div class="card p-4" style="border-top: 3px solid #3b82f6;">
        <div style="font-size: 32px; margin-bottom: 10px;">📄</div>
        <h3 style="margin-top: 0; font-size: 18px;">Label Amplop Naskah Soal</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            <strong>Ketentuan Resmi: 1 Ruang = 1 Amplop = 1 Label</strong>. Mengakomodasi gabungan kelas/tingkat dalam satu ruang dengan cadangan yang dapat disesuaikan.
        </p>
        <form method="GET" action="<?= url('/print/question-labels') ?>" target="_blank" style="margin-top: 15px;">
            <div class="form-group mb-2">
                <label style="font-size: 11px; font-weight: 600;">Filter Tanggal Ujian:</label>
                <input type="date" name="date" class="form-control" style="font-size: 12px; padding: 6px 10px;">
            </div>
            <div class="form-group mb-2">
                <label style="font-size: 11px; font-weight: 600;">Jumlah Naskah Cadangan per Mapel:</label>
                <input type="number" name="reserve" class="form-control" value="2" min="0" style="font-size: 12px; padding: 6px 10px;">
            </div>
            <button type="submit" class="btn btn-primary btn-block" style="width: 100%;">
                🖨️ Cetak Label Amplop Soal
            </button>
        </form>
    </div>

    <!-- 5. Label Amplop Lembar Jawaban (3 Mode) -->
    <div class="card p-4" style="border-top: 3px solid #10b981;">
        <div style="font-size: 32px; margin-bottom: 10px;">✉️</div>
        <h3 style="margin-top: 0; font-size: 18px;">Label Amplop Lembar Jawaban</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Label stiker amplop lembar jawaban hasil pengemasan resmi (Mendukung <strong>Mode 1: Per Kelas</strong>, <strong>Mode 2: Per Mapel</strong>, dan <strong>Mode 3: Per Kelas & Mapel</strong>).
        </p>
        <form method="GET" action="<?= url('/print/answer-sheet-labels') ?>" target="_blank" style="margin-top: 15px;">
            <div class="form-group mb-3">
                <label style="font-size: 11px; font-weight: 600;">Pilih Batch Amplop Tertentu:</label>
                <select name="batch_id" class="form-control" style="font-size: 12px; padding: 6px 10px;">
                    <option value="">-- Semua Batch Amplop --</option>
                    <?php foreach ($batches as $b): ?>
                        <option value="<?= $b['id'] ?>"><?= e($b['batch_code']) ?> (<?= e($b['packing_mode']) ?> - <?= (int)$b['total_sheets'] ?> lbr)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="height: 52px;"></div>
            <button type="submit" class="btn btn-success btn-block" style="width: 100%;">
                🖨️ Cetak Label Amplop Jawaban
            </button>
        </form>
    </div>

    <!-- 6. Denah Tempat Duduk & Berita Acara -->
    <div class="card p-4">
        <div style="font-size: 32px; margin-bottom: 10px;">📋</div>
        <h3 style="margin-top: 0; font-size: 18px;">Dokumen Ruang & Berita Acara</h3>
        <p style="font-size: 13px; color: #64748b; min-height: 40px;">
            Akses langsung cetak denah tempat duduk meja per ruang, daftar hadir peserta, dan berita acara pelaksanaan.
        </p>
        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 25px;">
            <a href="<?= url('/written-exam/rooms') ?>" class="btn btn-outline" style="text-align: left;">
                🪑 Cetak Denah Tempat Duduk Ruang
            </a>
            <a href="<?= url('/written-exam/execution') ?>" class="btn btn-outline" style="text-align: left;">
                📝 Cetak Berita Acara & Daftar Hadir Ruang
            </a>
            <a href="<?= url('/distribution') ?>" class="btn btn-outline" style="text-align: left;">
                📦 Cetak Berita Acara Serah Terima Naskah
            </a>
        </div>
    </div>
</div>
