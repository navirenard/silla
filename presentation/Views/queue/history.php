<!-- Halaman Riwayat Antrian (Admin & Operator) -->
<div class="page-container">
    <div style="margin-bottom: 30px;">
        <h1 style="font-size: 2.2rem; margin-bottom: 5px;">Riwayat Antrian Lengkap</h1>
        <p style="color: var(--text-secondary);">Daftar riwayat semua antrian hari ini: <?= date('d F Y') ?>.</p>
    </div>

    <div class="glass-panel">
        <?php if (empty($queues)): ?>
            <p style="color: var(--text-secondary); text-align: center; padding: 40px 0;">Belum ada antrian terdaftar hari ini.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nomor Antrian</th>
                            <th>Waktu Dibuat</th>
                            <th>Status</th>
                            <th>Waktu Dipanggil</th>
                            <th>Waktu Selesai</th>
                            <th>Loket / Counter</th>
                            <th>Waktu Tunggu</th>
                            <th>Durasi Layanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($queues as $q): ?>
                            <tr>
                                <td style="font-weight: 800; color: var(--primary-dark); font-size: 1.1rem;">
                                    <?= htmlspecialchars($q->queueNumber) ?>
                                </td>
                                <td><?= date('H:i:s', strtotime($q->createdAt)) ?></td>
                                <td>
                                    <span class="badge badge-<?= $q->status ?>">
                                        <?= htmlspecialchars($q->status) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $q->calledAt ? date('H:i:s', strtotime($q->calledAt)) : '<span style="color: var(--text-muted);">-</span>' ?>
                                </td>
                                <td>
                                    <?= $q->completedAt ? date('H:i:s', strtotime($q->completedAt)) : '<span style="color: var(--text-muted);">-</span>' ?>
                                </td>
                                <td>
                                    <?= $q->counterName ? htmlspecialchars($q->counterName) : '<span style="color: var(--text-muted);">-</span>' ?>
                                </td>
                                <td>
                                    <?php if ($q->waitTimeMinutes !== null): ?>
                                        <span><?= $q->waitTimeMinutes ?> mnt</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($q->serviceTimeMinutes !== null): ?>
                                        <span><?= $q->serviceTimeMinutes ?> mnt</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
