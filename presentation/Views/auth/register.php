<div class="auth-wrapper">
    <div class="glass-panel auth-card">
        <div class="auth-header">
            <div class="logo-icon auth-logo">Q</div>
            <h2 style="font-size: 1.8rem; margin-bottom: 5px;">Daftar Akun Baru</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Sistem Layanan Loket Antrian (SiLLA)</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= url('/register') ?>" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="name" class="form-label">Nama Lengkap</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Nama Anda" required autofocus>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label for="password" class="form-label">Password (Min. 6 Karakter)</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required minlength="6">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Daftar Akun</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="<?= url('/login') ?>" class="auth-link">Masuk di sini</a>
        </div>
    </div>
</div>
