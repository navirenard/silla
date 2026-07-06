<div class="auth-wrapper" style="min-height: calc(100vh - 250px);">
    <div class="glass-panel kiosk-card" id="kiosk-container">
        <h1 style="font-size: 2.2rem; margin-bottom: 10px; font-family: var(--font-heading);">Mesin Antrian Mandiri</h1>
        <p style="color: var(--text-secondary); margin-bottom: 30px;">
            Silakan tekan tombol di bawah ini untuk mengambil nomor antrian Anda.
        </p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px;">
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Giant Button to Trigger Ticket Printing -->
        <button class="kiosk-btn" id="kiosk-btn-trigger">
            <span class="kiosk-btn-icon">🎟️</span>
            <span class="kiosk-btn-text" style="font-size: 1.2rem;">Ambil Antrian</span>
        </button>

        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 30px;">
            Tiket antrian akan dicetak secara otomatis. Silakan bawa tiket Anda dan tunggu panggilan di ruang tunggu.
        </p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Registrasi action untuk tombol Kiosk AJAX
        registerKioskAction();
    });
</script>
