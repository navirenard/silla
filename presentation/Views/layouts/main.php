<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Puskesmas Salem - Antrian & Jadwal Online') ?></title>
    
    <!-- CSS Stylesheet -->
    <link rel="stylesheet" href="<?= url('/assets/css/style.css') ?>">
</head>
<body>

    <!-- Header / Navigation Bar -->
    <header class="app-header">
        <div class="header-container">
            <a href="<?= url('/') ?>" class="logo-wrapper">
                <div class="logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home" style="width: 22px; height: 22px;">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </div>
                <div class="logo-text-group">
                    <span class="logo-title">Puskesmas Salem</span>
                    <span class="logo-subtitle">ANTRIAN & JADWAL ONLINE</span>
                </div>
            </a>
            
            <nav class="nav-links">
                <!-- Public Links with Icons -->
                <a href="<?= url('/') ?>" class="nav-item <?= $_SERVER['REQUEST_URI'] === url('/') || strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'active' : '' ?>">
                    <span>🏠</span> Beranda
                </a>
                <a href="<?= url('/kiosk') ?>" target="_blank" class="nav-item">
                    <span>➕</span> Daftar Antrian
                </a>
                <a href="<?= url('/display') ?>" target="_blank" class="nav-item">
                    <span>🔍</span> Cek Antrian
                </a>
                
                <!-- Authenticated Operator/Admin Links -->
                <?php if (\App\Presentation\Middleware\AuthMiddleware::isAuthenticated()): ?>
                    <a href="<?= url('/queues') ?>" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/queues') !== false && strpos($_SERVER['REQUEST_URI'], '/queues/history') === false ? 'active' : '' ?>">
                        <span>💻</span> Operator
                    </a>
                    <a href="<?= url('/queues/history') ?>" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/queues/history') !== false ? 'active' : '' ?>">
                        <span>📋</span> Riwayat
                    </a>
                    
                    <?php if (\App\Presentation\Middleware\AuthMiddleware::isAdmin()): ?>
                        <a href="<?= url('/counters') ?>" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/counters') !== false ? 'active' : '' ?>">
                            <span>⚙️</span> Loket
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>

            <div class="nav-auth-actions">
                <?php if (\App\Presentation\Middleware\AuthMiddleware::isAuthenticated()): ?>
                    <div class="user-badge">
                        <span class="user-dot"></span>
                        <span class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                        <span class="user-role-label"><?= htmlspecialchars(ucfirst($_SESSION['user_role'])) ?></span>
                    </div>
                    <a href="<?= url('/logout') ?>" class="btn-logout-custom">Logout</a>
                <?php else: ?>
                    <?php if (strpos($_SERVER['REQUEST_URI'], '/login') === false && strpos($_SERVER['REQUEST_URI'], '/register') === false): ?>
                        <a href="<?= url('/login') ?>" class="btn-admin-login">🔓 Admin / Operator</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="main-content-layout">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="footer-credits-custom">
        <div class="footer-container">
            <p>&copy; <?= date('Y') ?> <strong>Puskesmas Salem</strong> - Sistem Layanan Loket Antrian. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- Inject dynamic base path for JS AJAX calls -->
    <script>window.APP_BASE_PATH = '<?= (isset($_SERVER['VERCEL']) || getenv('VERCEL') === '1') ? '' : rtrim(str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']), '/') ?>';</script>
    <!-- Client-Side App Script -->
    <script src="<?= url('/assets/js/app.js') ?>"></script>
</body>
</html>
