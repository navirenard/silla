<!-- Hero Section Design matching the user request -->
<div class="hero-section">
    <div class="hero-container-inner">
        <div class="system-status">
            <span class="status-dot"></span> Sistem Online Aktif
        </div>
        <h1 class="hero-title">Antrian <em>Puskesmas</em><br>Lebih Mudah</h1>
        <p class="hero-desc">
            Daftar antrian dan pantau pelayanan loket puskesmas tanpa harus mengantri lama di lokasi. Hemat waktu, layanan lebih nyaman dan efisien.
        </p>
        <div class="hero-actions">
            <a href="<?= url('/kiosk') ?>" target="_blank" class="btn-hero-primary">
                <span>➕</span> Daftar Antrian Sekarang
            </a>
            <a href="<?= url('/display') ?>" target="_blank" class="btn-hero-secondary">
                <span>🔍</span> Cek Status Antrian
            </a>
        </div>
    </div>
</div>

<!-- Stats Counter Bar -->
<div class="stats-bar">
    <div class="stats-bar-container">
        <div class="stat-item">
            <span class="stat-num"><?= $stats['total'] ?></span>
            <span class="stat-label">Antrian Hari Ini</span>
        </div>
        <div class="stat-item">
            <span class="stat-num"><?= $stats['waiting'] ?></span>
            <span class="stat-label">Menunggu Dipanggil</span>
        </div>
        <div class="stat-item">
            <span class="stat-num"><?= $stats['serving'] + $activeCountersCount ?></span>
            <span class="stat-label">Loket Pelayanan Aktif</span>
        </div>
    </div>
</div>

<!-- Detailed Metrics & Activity List -->
<div class="page-container">

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Mini Metrics Cards -->
    <div class="metrics-grid-mini">
        <div class="metric-card-mini">
            <div class="metric-card-icon">⚡</div>
            <div class="metric-card-info">
                <span class="metric-card-label">Sedang Dilayani</span>
                <span class="metric-card-val"><?= $stats['serving'] ?> Antrian</span>
            </div>
        </div>
        <div class="metric-card-mini">
            <div class="metric-card-icon">✅</div>
            <div class="metric-card-info">
                <span class="metric-card-label">Selesai Dilayani</span>
                <span class="metric-card-val"><?= $stats['completed'] ?> Antrian</span>
            </div>
        </div>
        <div class="metric-card-mini">
            <div class="metric-card-icon">❌</div>
            <div class="metric-card-info">
                <span class="metric-card-label">Dilewati (Skipped)</span>
                <span class="metric-card-val"><?= $stats['skipped'] ?> Antrian</span>
            </div>
        </div>
        <div class="metric-card-mini">
            <div class="metric-card-icon">⏱️</div>
            <div class="metric-card-info">
                <span class="metric-card-label">Rata-Rata Tunggu</span>
                <span class="metric-card-val"><?= $stats['avg_wait'] ?> Menit</span>
            </div>
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="glass-panel">
        <h2 class="glass-panel-title">Antrian Terakhir Hari Ini</h2>
        
        <?php if (empty($queues)): ?>
            <p style="color: var(--text-secondary); text-align: center; padding: 30px 0;">Belum ada aktivitas antrian hari ini.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nomor Antrian</th>
                            <th>Waktu Ambil</th>
                            <th>Status</th>
                            <th>Loket / Operator</th>
                            <th>Waktu Panggil</th>
                            <th>Waktu Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($queues) as $q): ?>
                            <tr>
                                <td style="font-weight: 800; font-size: 1.1rem; color: var(--primary-dark);"><?= htmlspecialchars($q->queueNumber) ?></td>
                                <td><?= date('H:i:s', strtotime($q->createdAt)) ?></td>
                                <td>
                                    <span class="badge badge-<?= $q->status ?>">
                                        <?= htmlspecialchars($q->status) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($q->counterName): ?>
                                        <span style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($q->counterName) ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $q->calledAt ? date('H:i:s', strtotime($q->calledAt)) : '<span style="color: var(--text-muted);">-</span>' ?>
                                </td>
                                <td>
                                    <?= $q->completedAt ? date('H:i:s', strtotime($q->completedAt)) : '<span style="color: var(--text-muted);">-</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
