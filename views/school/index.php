<div class="page-header">
    <div>
        <h1 class="page-title">Pengaturan Identitas Sekolah</h1>
        <p class="page-subtitle">Profil resmi sekolah yang digunakan otomatis pada kop surat, kartu, label, dan laporan ujian.</p>
    </div>
</div>

<form action="<?= url('/school/update') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="card">
        <div class="card-header">
            <div class="card-title">🏫 Informasi Dasar Sekolah</div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-col" style="flex: 2;">
                    <div class="form-group">
                        <label class="form-label required">Nama Resmi Sekolah</label>
                        <input type="text" name="name" class="form-control" value="<?= e($school['name']) ?>" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required">NPSN</label>
                        <input type="text" name="npsn" class="form-control" value="<?= e($school['npsn']) ?>" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required">Tahun Pelajaran Aktif</label>
                        <input type="text" name="academic_year" class="form-control" value="<?= e($school['academic_year']) ?>" placeholder="2026/2027" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label required">Alamat Jalan</label>
                <input type="text" name="address" class="form-control" value="<?= e($school['address']) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Desa / Kelurahan</label>
                        <input type="text" name="village" class="form-control" value="<?= e($school['village']) ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Kecamatan</label>
                        <input type="text" name="district" class="form-control" value="<?= e($school['district']) ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Kabupaten / Kota</label>
                        <input type="text" name="regency" class="form-control" value="<?= e($school['regency']) ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Provinsi</label>
                        <input type="text" name="province" class="form-control" value="<?= e($school['province']) ?>">
                    </div>
                </div>
                <div class="form-col" style="flex: 0.5;">
                    <div class="form-group">
                        <label class="form-label">Kode Pos</label>
                        <input type="text" name="postal_code" class="form-control" value="<?= e($school['postal_code']) ?>">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" name="phone" class="form-control" value="<?= e($school['phone']) ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Email Sekolah</label>
                        <input type="email" name="email" class="form-control" value="<?= e($school['email']) ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Website Resmi</label>
                        <input type="url" name="website" class="form-control" value="<?= e($school['website']) ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">📜 Logo Sekolah & Kop Surat</div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-col" style="flex: 0.8; text-align: center;">
                    <div class="form-group">
                        <label class="form-label">Logo Sekolah Saat Ini</label>
                        <div style="width: 120px; height: 120px; border: 2px dashed #cbd5e1; border-radius: 8px; margin: 0 auto; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f8fafc;">
                            <?php if (!empty($school['logo_path']) && file_exists(dirname(__DIR__, 2) . '/storage/uploads/' . $school['logo_path'])): ?>
                                <img src="<?= url('/file/view?path=' . urlencode($school['logo_path'])) ?>" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            <?php else: ?>
                                <span style="color: #94a3b8; font-size: 11px;">Belum ada logo</span>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="logo" class="form-control" style="margin-top: 10px; font-size: 11px;" accept="image/png,image/jpeg,image/webp">
                        <div class="form-text">Maksimal 5MB (PNG/JPG).</div>
                    </div>
                </div>

                <div class="form-col" style="flex: 2;">
                    <div class="form-group">
                        <label class="form-label required">Teks Kop Surat (Instansi / Yayasan di atas nama sekolah)</label>
                        <textarea name="letterhead_text" class="form-control" rows="4" placeholder="Contoh:&#10;PEMERINTAH DAERAH PROVINSI JAWA BARAT&#10;DINAS PENDIDIKAN&#10;CABANG DINAS PENDIDIKAN WILAYAH I"><?= e($school['letterhead_text']) ?></textarea>
                        <div class="form-text">Gunakan baris baru (enter) untuk memisahkan baris pada kop surat.</div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Format Penomoran Surat</label>
                                <input type="text" name="letter_number_format" class="form-control" value="<?= e($school['letter_number_format']) ?>" placeholder="{no}/PAN-UJIAN/{bln_romawi}/{thn}">
                                <div class="form-text">Variabel tersedia: {no}, {bln_romawi}, {bln}, {thn}</div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Kota Penandatanganan Dokumen</label>
                                <input type="text" name="signature_city" class="form-control" value="<?= e($school['signature_city']) ?>" placeholder="Bogor">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">✍️ Pejabat dan Tanda Tangan Resmi</div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required">Nama Kepala Sekolah</label>
                        <input type="text" name="principal_name" class="form-control" value="<?= e($school['principal_name']) ?>" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">NIP Kepala Sekolah</label>
                        <input type="text" name="principal_nip" class="form-control" value="<?= e($school['principal_nip']) ?>">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Nama Ketua Panitia</label>
                        <input type="text" name="committee_head" class="form-control" value="<?= e($school['committee_head']) ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label">Nama Sekretaris Panitia</label>
                        <input type="text" name="committee_secretary" class="form-control" value="<?= e($school['committee_secretary']) ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                💾 Simpan Perubahan Pengaturan
            </button>
        </div>
    </div>
</form>
