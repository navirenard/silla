<!-- Halaman Riwayat Antrian (Admin & Operator) -->
<style>
    .print-only-block {
        display: none;
    }
    @media print {
        @page {
            size: landscape;
            margin: 10mm;
        }
        header, footer, .btn-print-report, .app-header, .no-print {
            display: none !important;
        }
        .print-only-block {
            display: block !important;
        }
        body {
            background-color: #ffffff !important;
            color: #000000 !important;
            padding: 0 !important;
            margin: 0 !important;
            font-family: Arial, sans-serif !important;
        }
        .page-container {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .glass-panel {
            border: none !important;
            box-shadow: none !important;
            background: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .table-responsive {
            overflow: visible !important;
            width: 100% !important;
        }
        .custom-table {
            width: 100% !important;
            table-layout: auto !important;
            border-collapse: collapse !important;
        }
        .custom-table th {
            background-color: #f1f5f9 !important;
            color: #000000 !important;
            border-bottom: 2px solid #000000 !important;
            font-size: 10px !important;
            padding: 6px 4px !important;
            text-align: center !important;
            font-weight: bold !important;
        }
        .custom-table td {
            padding: 6px 4px !important;
            border: 1px solid #cbd5e1 !important;
            font-size: 10px !important;
            color: #000000 !important;
            text-align: center !important;
            font-weight: normal !important;
            word-break: break-word !important;
        }
        .custom-table td:first-child {
            font-weight: bold !important;
        }
        /* Override inline styles inside td */
        .custom-table td * {
            font-size: 10px !important;
            color: #000000 !important;
        }
        .custom-table .badge {
            background: transparent !important;
            color: #000000 !important;
            border: 1px solid #cbd5e1 !important;
            padding: 2px 4px !important;
            font-size: 9px !important;
            border-radius: 4px !important;
            display: inline-block !important;
        }
        .print-only-block h2 {
            font-size: 1.5rem !important;
            margin-bottom: 5px !important;
        }
        .print-only-block h3 {
            font-size: 1.1rem !important;
            margin-bottom: 10px !important;
        }
        .print-only-block p {
            font-size: 0.8rem !important;
            margin-bottom: 20px !important;
        }
    }
</style>

<div class="page-container">
    <div class="page-header no-print">
        <div class="page-header-title">
            <h1>📋 Riwayat Antrian Lengkap</h1>
            <p>Daftar riwayat semua antrian hari ini: <?= date('d F Y') ?>.</p>
        </div>
        <div class="page-header-action">
            <button onclick="window.print()" class="btn btn-primary btn-print-report">
                <span>🖨️</span> Cetak Laporan Harian
            </button>
        </div>
    </div>

    <!-- Judul Laporan Khusus Print -->
    <div style="display: none; text-align: center; margin-bottom: 30px;" class="print-only-block">
        <h2 style="font-size: 1.8rem; margin-bottom: 5px; font-family: var(--font-heading);">Laporan Kunjungan & Antrian Harian</h2>
        <h3 style="font-size: 1.2rem; margin-bottom: 15px; color: var(--text-secondary);">Puskesmas Salem</h3>
        <p style="font-size: 0.9rem;">Tanggal Cetak: <?= date('d F Y H:i') ?> WIB</p>
    </div>

    <div class="glass-panel" style="border-radius: 16px; padding: 24px;">
        <?php if (empty($queues)): ?>
            <p style="color: var(--text-secondary); text-align: center; padding: 40px 0;">Belum ada antrian terdaftar hari ini.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="custom-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>No. Antrian</th>
                            <th>Kode Pendaftaran</th>
                            <th>Nama Pasien</th>
                            <th>Poliklinik</th>
                            <th>Waktu Daftar</th>
                            <th>Status</th>
                            <th>Loket / Counter</th>
                            <th>Waktu Panggil</th>
                            <th>Waktu Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($queues as $q): ?>
                            <tr>
                                <td style="font-weight: 800; color: var(--primary-dark); font-size: 1.1rem; font-family: var(--font-heading);">
                                    <?= htmlspecialchars($q['queue_number']) ?>
                                </td>
                                <td style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary);">
                                    <?= htmlspecialchars($q['id']) ?>
                                </td>
                                <td style="font-weight: 600; color: var(--text-primary);">
                                    <?= htmlspecialchars($q['nama_pasien']) ?>
                                </td>
                                <td style="font-size: 0.85rem; font-weight: 700; color: var(--primary-dark); text-transform: uppercase;">
                                    <?= htmlspecialchars($q['nama_poli']) ?>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-muted);">
                                    <?= substr($q['jam_ambil'], 0, 5) ?> WIB
                                </td>
                                <td>
                                    <span class="badge badge-<?= strtolower($q['status'] === 'Selesai' ? 'completed' : ($q['status'] === 'Dipanggil' ? 'serving' : ($q['status'] === 'Lewat' ? 'skipped' : 'waiting'))) ?>">
                                        <?= htmlspecialchars($q['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $q['counter_name'] ? htmlspecialchars($q['counter_name']) : '<span style="color: var(--text-muted);">-</span>' ?>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-secondary);">
                                    <?= $q['called_at'] ? date('H:i', strtotime($q['called_at'])) . ' WIB' : '<span style="color: var(--text-muted);">-</span>' ?>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-secondary);">
                                    <?= $q['completed_at'] ? date('H:i', strtotime($q['completed_at'])) . ' WIB' : '<span style="color: var(--text-muted);">-</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
