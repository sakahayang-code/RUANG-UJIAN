<form action="<?= url('/login') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="username" class="form-label required">Username atau Email</label>
        <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username atau email" value="<?= e(old('username', 'admin')) ?>" required autofocus>
    </div>

    <div class="form-group">
        <label for="password" class="form-label required">Kata Sandi</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan kata sandi" value="password123" required>
    </div>

    <div style="margin-top: 24px;">
        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px; font-weight: 600; font-size: 14px;">
            Masuk ke Aplikasi
        </button>
    </div>
</form>

<div style="margin-top: 28px; padding-top: 18px; border-top: 1px solid #e2e8f0; font-size: 11.5px; color: #64748b;">
    <div style="font-weight: 600; margin-bottom: 6px; color: #334155;">Akun Bawaan Sistem (Password: password123):</div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px;">
        <div>• <strong>admin</strong> (Super Admin)</div>
        <div>• <strong>ketua</strong> (Ketua Panitia)</div>
        <div>• <strong>sekretaris</strong> (Sekretaris)</div>
        <div>• <strong>operator</strong> (Operator)</div>
        <div>• <strong>pengawas1</strong> (Pengawas)</div>
        <div>• <strong>penguji1</strong> (Penguji)</div>
    </div>
</div>
