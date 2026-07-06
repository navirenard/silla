<div class="auth-wrapper">
    <div class="glass-panel auth-card">
        <div class="auth-header">
            <div class="logo-icon auth-logo">Q</div>
            <h2 style="font-size: 1.8rem; margin-bottom: 5px;">Masuk ke SiLLA</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Sistem Layanan Loket Antrian</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= url('/login') ?>" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" required autofocus>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Masuk Sekarang</button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="<?= url('/register') ?>" class="auth-link">Daftar di sini</a>
        </div>
    </div>
</div>
