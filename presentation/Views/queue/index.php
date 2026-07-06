<!-- Halaman Control Panel Operator -->
<div class="page-container">
    <div style="margin-bottom: 30px;">
        <h1 style="font-size: 2.2rem; margin-bottom: 5px;">Operator Control Panel</h1>
        <p style="color: var(--text-secondary);">Kelola pemanggilan dan pelayanan antrian.</p>
    </div>

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

    <?php if (!$activeCounter): ?>
        <!-- Mode Pilih Loket Tugas -->
        <div class="glass-panel" style="max-width: 500px; margin: 40px auto;">
            <h2 class="glass-panel-title" style="text-align: center;">Pilih Loket Tugas</h2>
            <p style="color: var(--text-secondary); text-align: center; margin-bottom: 24px;">
                Silakan pilih loket pelayanan tempat Anda bertugas saat ini.
            </p>
            
            <form action="<?= url('/queues/select-counter') ?>" method="POST">
                <div class="form-group">
                    <label for="counter_id" class="form-label">Daftar Loket Aktif</label>
                    <select name="counter_id" id="counter_id" class="form-control" required>
                        <option value="">-- Pilih Loket --</option>
                        <?php foreach ($counters as $c): ?>
                            <?php if ($c->isActive): ?>
                                <option value="<?= htmlspecialchars($c->id) ?>"><?= htmlspecialchars($c->name) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 24px;">Aktifkan Loket</button>
            </form>
        </div>
    <?php else: ?>
        <!-- Mode Control Panel Aktif -->
        <div class="operator-layout">
            
            <!-- Kolom Kiri: Panggilan & Kontrol -->
            <div>
                <div class="current-call-card" style="margin-bottom: 30px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-light); padding-bottom: 15px; margin-bottom: 20px;">
                        <div style="text-align: left;">
                            <span class="badge badge-serving" style="margin-bottom: 5px;">Sedang Bertugas</span>
                            <h3 style="font-size: 1.3rem;"><?= htmlspecialchars($activeCounter->name) ?></h3>
                        </div>
                        <form action="<?= url('/queues/select-counter') ?>" method="POST">
                            <input type="hidden" name="counter_id" value="">
                            <button type="submit" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Ganti Loket</button>
                        </form>
                    </div>

                    <?php if ($currentQueue): ?>
                        <!-- Sedang Melayani Seseorang -->
                        <span class="current-label">Sedang Dilayani</span>
                        <div class="current-number" id="operator-current-number"><?= htmlspecialchars($currentQueue->queueNumber) ?></div>
                        <p style="color: var(--text-secondary); margin-bottom: 30px;">
                            Dipanggil pukul: <?= date('H:i:s', strtotime($currentQueue->calledAt)) ?>
                        </p>

                        <div style="display: flex; gap: 15px; justify-content: center;">
                            <form action="<?= url('/queues/complete') ?>" method="POST" style="flex: 1; max-width: 200px;">
                                <input type="hidden" name="queue_id" value="<?= htmlspecialchars($currentQueue->id) ?>">
                                <button type="submit" class="btn btn-success btn-block">Selesai Melayani</button>
                            </form>
                            <form action="<?= url('/queues/skip') ?>" method="POST" style="flex: 1; max-width: 200px;">
                                <input type="hidden" name="queue_id" value="<?= htmlspecialchars($currentQueue->id) ?>">
                                <button type="submit" class="btn btn-danger btn-block">Lewati (Lewat)</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <!-- Loket Kosong -->
                        <span class="current-label">Status Loket</span>
                        <div class="current-number" style="color: var(--text-muted); text-shadow: none; animation: none;">OFF</div>
                        <p style="color: var(--text-secondary); margin-bottom: 30px;">
                            Silakan panggil antrian berikutnya jika sudah siap.
                        </p>

                        <form action="<?= url('/queues/call') ?>" method="POST" style="max-width: 300px; margin: 0 auto;">
                            <button type="submit" class="btn btn-primary btn-block" style="font-size: 1.1rem; padding: 14px 28px;">
                                Panggil Antrian Berikutnya
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <?php if ($currentQueue): ?>
                    <!-- Tombol Panggil Berikutnya ketika Sedang Melayani -->
                    <div class="glass-panel" style="text-align: center; border-color: rgba(88, 169, 62, 0.15);">
                        <p style="color: var(--text-secondary); margin-bottom: 15px; font-size: 0.9rem;">
                            Ingin langsung memanggil antrian berikutnya? Antrian saat ini otomatis ditandai <strong>selesai</strong>.
                        </p>
                        <form action="<?= url('/queues/call') ?>" method="POST" style="max-width: 300px; margin: 0 auto;">
                            <button type="submit" class="btn btn-primary btn-block">
                                Panggil Antrian Selanjutnya
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Kolom Kanan: Daftar Antrian Menunggu -->
            <div>
                <div class="glass-panel" style="height: 100%; display: flex; flex-direction: column;">
                    <h3 class="glass-panel-title" style="margin-bottom: 15px; font-size: 1.2rem;">Daftar Tunggu (<?= count($waitingQueues) ?>)</h3>
                    
                    <div style="flex: 1; overflow-y: auto; max-height: 400px; padding-right: 5px;">
                        <?php if (empty($waitingQueues)): ?>
                            <p style="color: var(--text-muted); text-align: center; padding: 40px 0; font-size: 0.95rem;">
                                Tidak ada antrian menunggu.
                            </p>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <?php foreach ($waitingQueues as $wq): ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; background: #f8fafc; border: 1px solid var(--border-light); padding: 14px 18px; border-radius: 10px;">
                                        <div>
                                            <div style="font-weight: 700; font-size: 1.1rem; color: var(--primary-dark);"><?= htmlspecialchars($wq->queueNumber) ?></div>
                                            <span style="font-size: 0.75rem; color: var(--text-muted);"><?= date('H:i:s', strtotime($wq->createdAt)) ?></span>
                                        </div>
                                        <span class="badge badge-waiting">Menunggu</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>
