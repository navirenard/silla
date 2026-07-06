<div style="max-width: 1200px; margin: 40px auto; padding: 0 24px;">
    
    <!-- Header Monitor -->
    <div style="text-align: center; margin-bottom: 40px;">
        <span class="system-status" style="background-color: #d1fae5; color: #065f46; border-color: #a7f3d0;">
            <span class="status-dot" style="background: #10b981;"></span> Layanan Live Monitor
        </span>
        <h1 class="display-title" style="color: var(--bg-header); font-size: 2.5rem; margin-top: 10px; font-weight: 800;">MONITOR ANTRIAN UTAMA</h1>
        <div class="display-subtitle" id="display-time-label" style="font-weight: 600; color: var(--text-secondary); font-size: 1.1rem; margin-top: 5px;">
            <?= date('l, d F Y') ?>
        </div>
    </div>

    <!-- Info Rangkuman Sampingan -->
    <div style="display: flex; justify-content: center; gap: 40px; margin-bottom: 45px;">
        <div style="text-align: center; background: #ffffff; border: 1px solid var(--border-light); padding: 18px 40px; border-radius: 16px; min-width: 250px; box-shadow: var(--shadow-md);">
            <div style="font-size: 0.85rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 8px;">Sisa Antrian Menunggu</div>
            <div id="display-waiting-count" style="font-size: 2.8rem; font-family: var(--font-heading); font-weight: 800; color: var(--warning); line-height: 1;">-</div>
        </div>
    </div>

    <!-- Active Counters Grid -->
    <div class="display-grid">
        <?php foreach ($counters as $c): ?>
            <?php if ($c->isActive): ?>
                <div class="display-card" id="counter-card-<?= htmlspecialchars($c->id) ?>" style="border: 1px solid var(--border-light); box-shadow: var(--shadow-md); border-top: 5px solid var(--bg-header);">
                    <div class="display-counter-name" style="color: var(--bg-header); font-weight: 800;"><?= htmlspecialchars($c->name) ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase; font-weight: 700; letter-spacing: 0.08em; margin-top: 15px;">Nomor Antrian</div>
                    <div class="display-number" id="counter-num-<?= htmlspecialchars($c->id) ?>" style="font-size: 5.2rem; font-weight: 900; margin: 10px 0; color: var(--primary-dark);">
                        <?= htmlspecialchars($c->currentQueueNumber ?: '-') ?>
                    </div>
                    <div style="color: var(--text-muted); font-size: 0.85rem; border-top: 1px solid #f1f5f9; padding-top: 15px; font-weight: 600;">
                        Silakan Menuju ke Loket
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="glass-panel" style="text-align: center; border-color: #cbd5e1; padding: 20px; background: #ffffff; box-shadow: var(--shadow-sm);">
        <p style="color: var(--text-secondary); font-size: 0.95rem; font-weight: 500;">
            🔊 <strong>Sistem Suara Pemanggilan Aktif</strong>. Pastikan volume speaker monitor Anda menyala dan browser Anda diizinkan memutar suara.
        </p>
    </div>
</div>

<script>
    // Inisialisasi Monitor Display saat halaman dimuat
    document.addEventListener('DOMContentLoaded', () => {
        // Trigger speech synthesis loading
        if ('speechSynthesis' in window) {
            window.speechSynthesis.getVoices();
        }

        // Jalankan monitoring polling
        const monitor = new QueueDisplayMonitor();
        monitor.start();

        // Update Jam & Menit di Monitor secara live
        setInterval(() => {
            const timeLabel = document.getElementById('display-time-label');
            if (timeLabel) {
                const now = new Date();
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
                timeLabel.textContent = now.toLocaleDateString('id-ID', options);
            }
        }, 1000);
    });
</script>
