<style>
/* Modern Font & Reset */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

@media print {
    .no-print { display: none !important; }
    body {
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
    }
    .print-container {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .cards-container {
        max-width: 100% !important;
        padding: 0 !important;
        gap: 6mm 5mm !important;
        grid-template-columns: repeat(2, 1fr) !important;
    }
    .participant-card {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        border: 1.5px solid #0f172a !important;
        box-shadow: none !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}

.cards-container {
    max-width: 820px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    padding: 12px 10px;
    box-sizing: border-box;
}

.participant-card {
    border: 1.5px solid #1e293b;
    border-radius: 8px;
    padding: 11px 13px;
    background: #ffffff;
    page-break-inside: avoid;
    break-inside: avoid;
    font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    font-size: 11px;
    color: #0f172a;
    position: relative;
    box-sizing: border-box;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* Header (Kop Kartu Peserta) */
.card-header-inner {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 7px;
    margin-bottom: 9px;
    border-bottom: 2px solid #0f172a;
    position: relative;
}

.card-header-inner::after {
    content: "";
    position: absolute;
    bottom: -3px;
    left: 0;
    right: 0;
    height: 0.75px;
    background: #0f172a;
}

.card-logo {
    width: 48px;
    height: 48px;
    object-fit: contain;
    flex-shrink: 0;
}

.card-header-spacer {
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    visibility: hidden;
}

.card-header-text {
    flex: 1;
    text-align: center;
    padding: 0 2px;
}

.card-school-name {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    color: #0f172a;
    line-height: 1.25;
    letter-spacing: 0.3px;
}

.card-exam-title {
    font-size: 11.5px;
    font-weight: 800;
    text-transform: uppercase;
    color: #1e3a8a;
    line-height: 1.25;
    margin-top: 2px;
    letter-spacing: 0.4px;
}

.card-academic-year {
    font-size: 9.5px;
    font-weight: 600;
    color: #475569;
    margin-top: 2px;
    line-height: 1.2;
}

/* Card Body (Photo & Table) */
.card-body-inner {
    display: flex;
    gap: 12px;
    align-items: stretch;
    margin-bottom: 8px;
}

.card-photo-box {
    width: 68px;
    height: 90px;
    border: 1.5px dashed #94a3b8;
    border-radius: 4px;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #64748b;
    text-align: center;
    padding: 2px;
    flex-shrink: 0;
    overflow: hidden;
    box-sizing: border-box;
}

.card-photo-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 3px;
    display: block;
}

.card-photo-text {
    font-size: 8.5px;
    font-weight: 700;
    color: #94a3b8;
    line-height: 1.3;
    letter-spacing: 0.5px;
    margin-top: 3px;
}

.card-table {
    flex: 1;
    border-collapse: collapse;
}

.card-table td {
    padding: 2px 0;
    vertical-align: middle;
    font-size: 10.5px;
    line-height: 1.3;
}

.card-table .label {
    width: 84px;
    font-weight: 600;
    color: #475569;
    white-space: nowrap;
}

.card-table .separator {
    width: 10px;
    text-align: center;
    color: #64748b;
    font-weight: 700;
}

.val-number {
    font-family: 'Consolas', 'Courier New', monospace;
    font-size: 11.5px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: 0.5px;
}

.val-name {
    font-size: 11px;
    font-weight: 800;
    color: #0f172a;
    text-transform: uppercase;
}

.badge-room {
    display: inline-block;
    background: #f8fafc;
    border: 1.5px solid #0f172a;
    padding: 1px 6px;
    border-radius: 3px;
    font-weight: 700;
    font-size: 10px;
    color: #0f172a;
}

.badge-seat {
    display: inline-block;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    padding: 1px 6px;
    border-radius: 3px;
    font-size: 10px;
    color: #334155;
    margin-left: 4px;
}

/* Card Footer (QR Code & Signature) */
.card-footer-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 8px;
    margin-top: auto;
    border-top: 1px dashed #cbd5e1;
}

.qr-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
}

.qr-box {
    width: 58px;
    height: 58px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 2px;
    background: #ffffff;
    box-sizing: border-box;
    display: flex;
    align-items: center;
    justify-content: center;
}

.qr-box svg {
    width: 100% !important;
    height: 100% !important;
    display: block;
}

.qr-caption {
    font-size: 7.5px;
    font-weight: 600;
    color: #64748b;
    letter-spacing: 0.4px;
    text-transform: uppercase;
}

.signature-box {
    text-align: center;
    font-size: 10px;
    color: #1e293b;
    min-width: 170px;
}

.sig-date {
    font-size: 9.5px;
    color: #475569;
    margin-bottom: 2px;
}

.sig-title {
    font-size: 10px;
    font-weight: 700;
    color: #0f172a;
}

.sig-space {
    height: 28px;
}

.sig-name {
    font-size: 10.5px;
    font-weight: 800;
    text-decoration: underline;
    color: #0f172a;
    line-height: 1.2;
}

.sig-nip {
    font-size: 9px;
    color: #475569;
    margin-top: 2px;
}
</style>

<?php
// Resolve school logo (from logo_url or logo_path)
$schoolLogo = $school['logo_url'] ?? '';
if (empty($schoolLogo) && !empty($school['logo_path'])) {
    $fullLogoPath = dirname(__DIR__, 2) . '/storage/uploads/' . $school['logo_path'];
    if (file_exists($fullLogoPath) && is_file($fullLogoPath)) {
        $ext = strtolower(pathinfo($fullLogoPath, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            default => 'image/png'
        };
        $schoolLogo = 'data:' . $mime . ';base64,' . base64_encode((string)file_get_contents($fullLogoPath));
    } else {
        $schoolLogo = url('/file/view?path=' . urlencode($school['logo_path']));
    }
}

// Ketua Panitia fallback
$committeeSignerName = $committeeSignerName ?? (!empty($school['committee_head']) ? $school['committee_head'] : 'Ketua Panitia');
$committeeSignerNip = $committeeSignerNip ?? '';
$cityName = !empty($school['signature_city']) ? $school['signature_city'] : ($school['district'] ?? 'Tempat');
?>

<div class="no-print" style="max-width: 820px; margin: 10px auto 0; padding: 10px 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; font-size: 12px; color: #1e40af; display: flex; justify-content: space-between; align-items: center; box-sizing: border-box;">
    <div>
        <strong>Kartu Peserta Ujian</strong> &bull; Total: <strong><?= count($participants) ?> Peserta</strong> &bull; Format 2 Kolom (A4/F4)
    </div>
    <div style="font-size: 11px; color: #3b82f6;">
        💡 <em>Aktifkan opsi "Background graphics" saat mencetak kartu</em>
    </div>
</div>

<div class="cards-container">
    <?php if (empty($participants)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748b; background: #fff; border-radius: 8px; border: 1px dashed #cbd5e1;">
            Tidak ada data peserta ujian yang sesuai filter.
        </div>
    <?php else: ?>
        <?php foreach ($participants as $p): ?>
            <div class="participant-card">
                <!-- Header / Kop Kartu -->
                <div class="card-header-inner">
                    <?php if (!empty($schoolLogo)): ?>
                        <img src="<?= e($schoolLogo) ?>" alt="Logo <?= e($school['name']) ?>" class="card-logo">
                    <?php else: ?>
                        <!-- Logo Resmi Sekolah (Emblem Pendidikan) -->
                        <svg class="card-logo" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="24" cy="24" r="22" fill="#f8fafc" stroke="#1e3a8a" stroke-width="2"/>
                            <circle cx="24" cy="24" r="18" fill="none" stroke="#d97706" stroke-width="1" stroke-dasharray="2 2"/>
                            <!-- Sayap & Toga Akademik -->
                            <path d="M24 13L35 18L24 23L13 18L24 13Z" fill="#1e3a8a"/>
                            <path d="M17 20.5V26C17 28.5 20.1 30.5 24 30.5C27.9 30.5 31 28.5 31 26V20.5" stroke="#1e3a8a" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M33 19V27" stroke="#d97706" stroke-width="1.5" stroke-linecap="round"/>
                            <circle cx="33" cy="28" r="1.5" fill="#d97706"/>
                            <!-- Kitab / Buku Terbuka -->
                            <path d="M18 35C20.5 33.5 22.5 33.8 24 34.5C25.5 33.8 27.5 33.5 30 35V32C27.5 30.8 25.5 31.2 24 32C22.5 31.2 20.5 30.8 18 32V35Z" fill="#d97706" opacity="0.9"/>
                        </svg>
                    <?php endif; ?>

                    <div class="card-header-text">
                        <div class="card-school-name"><?= e($school['name']) ?></div>
                        <div class="card-exam-title">KARTU PESERTA <?= strtoupper(e($event['name'])) ?></div>
                        <div class="card-academic-year">Tahun Pelajaran <?= e($event['academic_year']) ?> (Semester <?= e($event['semester']) ?>)</div>
                    </div>

                    <!-- Spacer kanan penyeimbang agar teks kop berada tepat di tengah -->
                    <div class="card-header-spacer"></div>
                </div>

                <!-- Identitas Peserta & Foto -->
                <div class="card-body-inner">
                    <div class="card-photo-box">
                        <?php 
                        $photoSrc = !empty($p['photo_url']) ? $p['photo_url'] : (!empty($p['photo']) ? (str_starts_with($p['photo'], 'data:') || str_starts_with($p['photo'], 'http') || str_starts_with($p['photo'], '/') ? $p['photo'] : url('/file/view?path=' . urlencode($p['photo']))) : null); 
                        ?>
                        <?php if (!empty($photoSrc)): ?>
                            <img src="<?= e($photoSrc) ?>" alt="Foto <?= e($p['name']) ?>">
                        <?php else: ?>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <span class="card-photo-text">FOTO<br>3 &times; 4</span>
                        <?php endif; ?>
                    </div>

                    <table class="card-table">
                        <tr>
                            <td class="label">Nomor Peserta</td>
                            <td class="separator">:</td>
                            <td><span class="val-number"><?= e($p['participant_number']) ?></span></td>
                        </tr>
                        <tr>
                            <td class="label">Nama Lengkap</td>
                            <td class="separator">:</td>
                            <td><strong class="val-name"><?= e($p['name']) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="label">NIS / NISN</td>
                            <td class="separator">:</td>
                            <td><?= e($p['nis']) ?> / <?= e($p['nisn']) ?></td>
                        </tr>
                        <tr>
                            <td class="label">Kelas</td>
                            <td class="separator">:</td>
                            <td><strong><?= e($p['class_name_snapshot'] ?: $p['class_name']) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="label">Ruang / Meja</td>
                            <td class="separator">:</td>
                            <td>
                                <?php
                                $rNum = $p['room_number'] ?? '-';
                                $roomText = ($rNum !== '-' && !preg_match('/^ruang/i', trim((string)$rNum))) ? 'Ruang ' . $rNum : $rNum;
                                ?>
                                <span class="badge-room"><?= e($roomText) ?></span>
                                <span class="badge-seat">No. Meja: <strong><?= e($p['seat_number'] ?? '-') ?></strong></span>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Footer: QR Code & Tanda Tangan Ketua Panitia -->
                <div class="card-footer-inner">
                    <div class="qr-section">
                        <div class="qr-box">
                            <?= $p['qr_svg'] ?>
                        </div>
                        <span class="qr-caption">Verifikasi QR</span>
                    </div>

                    <div class="signature-box">
                        <div class="sig-date"><?= e($cityName) ?>, <?= e(indo_date(date('Y-m-d'))) ?></div>
                        <div class="sig-title">Ketua Panitia,</div>
                        <div class="sig-space"></div>
                        <div class="sig-name"><?= e($committeeSignerName ?: 'Ketua Panitia') ?></div>
                        <div class="sig-nip">NIP. <?= !empty($committeeSignerNip) && $committeeSignerNip !== '-' ? e($committeeSignerNip) : '-' ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
