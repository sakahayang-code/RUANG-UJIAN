<form action="<?= url('/login') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="username" class="form-label required">Username atau Email</label>
        <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username atau email" value="<?= e(old('username')) ?>" required autofocus>
    </div>

    <div class="form-group">
        <label for="password" class="form-label required">Kata Sandi</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan kata sandi" required>
    </div>

    <div style="margin-top: 24px;">
        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px; font-weight: 600; font-size: 14px;">
            Masuk ke Aplikasi
        </button>
    </div>
</form>
