<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; padding: 0 !important; margin: 0 !important; }
}

.cards-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    padding: 10px;
}

.participant-card {
    border: 2px solid #1e293b;
    border-radius: 6px;
    padding: 10px 12px;
    background: #fff;
    page-break-inside: avoid;
    font-size: 11px;
    position: relative;
    box-sizing: border-box;
}

.card-header-inner {
    display: flex;
    align-items: center;
    border-bottom: 2px solid #0f172a;
    padding-bottom: 6px;
    margin-bottom: 8px;
    gap: 10px;
}

.card-logo {
    width: 42px;
    height: 42px;
    object-fit: contain;
}

.card-header-text {
    flex: 1;
    text-align: center;
}

.card-school-name {
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    color: #0f172a;
    line-height: 1.2;
}

.card-exam-title {
    font-size: 11px;
    font-weight: 700;
    color: #1e3a8a;
    margin-top: 2px;
}

.card-academic-year {
    font-size: 10px;
    color: #475569;
}

.card-body-inner {
    display: flex;
    gap: 10px;
}

.card-photo-box {
    width: 65px;
    height: 85px;
    border: 1px dashed #94a3b8;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    color: #64748b;
    text-align: center;
    padding: 2px;
    flex-shrink: 0;
}

.card-table {
    flex: 1;
    border-collapse: collapse;
}

.card-table td {
    padding: 2px 0;
    vertical-align: top;
    font-size: 10.5px;
}

.card-table .label {
    width: 80px;
    font-weight: 600;
    color: #334155;
}

.card-footer-inner {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: 8px;
    padding-top: 6px;
    border-top: 1px dotted #cbd5e1;
}

.qr-box {
    width: 60px;
    height: 60px;
}

.signature-box {
    text-align: center;
    font-size: 9.5px;
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
?>

<div class="cards-container">
    <?php if (empty($participants)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748b;">
            Tidak ada data peserta ujian yang sesuai filter.
        </div>
    <?php else: ?>
        <?php foreach ($participants as $p): ?>
            <div class="participant-card">
                <div class="card-header-inner">
                    <?php if (!empty($schoolLogo)): ?>
                        <img src="<?= e($schoolLogo) ?>" alt="Logo <?= e($school['name']) ?>" class="card-logo">
                    <?php else: ?>
                        <!-- Logo Resmi Sekolah (Emblem Pendidikan) jika belum unggah logo khusus -->
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
                </div>

                <div class="card-body-inner">
                    <div class="card-photo-box">
                        <?php 
                        $photoSrc = !empty($p['photo_url']) ? $p['photo_url'] : (!empty($p['photo']) ? (str_starts_with($p['photo'], 'data:') || str_starts_with($p['photo'], 'http') || str_starts_with($p['photo'], '/') ? $p['photo'] : url('/file/view?path=' . urlencode($p['photo']))) : null); 
                        ?>
                        <?php if (!empty($photoSrc)): ?>
                            <img src="<?= e($photoSrc) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <span>FOTO<br>3 x 4</span>
                        <?php endif; ?>
                    </div>
                    <table class="card-table">
                        <tr>
                            <td class="label">Nomor Peserta</td>
                            <td style="width: 8px;">:</td>
                            <td><strong style="color: #0f172a; font-size: 11.5px;"><?= e($p['participant_number']) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="label">Nama Lengkap</td>
                            <td>:</td>
                            <td><strong><?= e($p['name']) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="label">NIS / NISN</td>
                            <td>:</td>
                            <td><?= e($p['nis']) ?> / <?= e($p['nisn']) ?></td>
                        </tr>
                        <tr>
                            <td class="label">Kelas Asal</td>
                            <td>:</td>
                            <td><?= e($p['class_name_snapshot'] ?: $p['class_name']) ?></td>
                        </tr>
                        <tr>
                            <td class="label">Ruang / Meja</td>
                            <td>:</td>
                            <td>
                                <?php
                                $rNum = $p['room_number'] ?? '-';
                                $roomText = ($rNum !== '-' && !preg_match('/^ruang/i', trim((string)$rNum))) ? 'Ruang ' . $rNum : $rNum;
                                ?>
                                <span style="border: 1px solid #000; padding: 1px 5px; font-weight: bold;"><?= e($roomText) ?></span> &nbsp; No Meja: <strong><?= e($p['seat_number'] ?? '-') ?></strong>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card-footer-inner">
                    <div class="qr-box">
                        <?= $p['qr_svg'] ?>
                    </div>
                    <div class="signature-box">
                        <div><?= e(!empty($school['signature_city']) ? $school['signature_city'] : ($school['district'] ?? 'Tempat')) ?>, <?= e(indo_date(date('Y-m-d'))) ?></div>
                        <div style="font-weight: 600;">Ketua Panitia,</div>
                        <div style="height: 35px;"></div>
                        <div style="font-weight: bold; text-decoration: underline;"><?= e($committeeSignerName ?: 'Ketua Panitia') ?></div>
                        <div>NIP. <?= !empty($committeeSignerNip) ? e($committeeSignerNip) : '-' ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
