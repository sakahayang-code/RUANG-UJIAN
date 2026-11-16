<form action="<?= url('/login') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="username" class="form-label required">Username atau Email</label>
        <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username atau email" value="<?= e(old('username')) ?>" autocomplete="username" required autofocus>
    </div>

    <div class="form-group">
        <label for="password" class="form-label required">Kata Sandi</label>
        <div style="position: relative;">
            <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan kata sandi" autocomplete="current-password" required style="padding-right: 42px;">
            <button type="button" id="togglePasswordBtn" onclick="togglePasswordVisibility()" title="Tampilkan/Sembunyikan Kata Sandi" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b; font-size: 16px; padding: 4px; display: flex; align-items: center; justify-content: center;">
                👁️
            </button>
        </div>
    </div>

    <div style="margin-top: 24px;">
        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px; font-weight: 600; font-size: 14px;">
            Masuk ke Aplikasi
        </button>
    </div>
</form>

<script>
function togglePasswordVisibility() {
    const input = document.getElementById('password');
    const btn = document.getElementById('togglePasswordBtn');
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = '🙈';
    } else {
        input.type = 'password';
        btn.textContent = '👁️';
    }
}
</script>
