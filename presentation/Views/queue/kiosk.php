<!-- Halaman Form Pendaftaran Antrian -->
<div class="auth-wrapper" style="min-height: calc(100vh - 300px); padding: 40px 16px;">
    <div style="width: 100%; max-width: 600px; display: flex; flex-direction: column; gap: 24px;">

        <?php if (!empty($successTicket)): ?>
            <!-- Print specific styles to force exactly 1 page and clean ticket layout -->
            <style>
                @media print {
                    /* Restrict viewport to exactly 1 page and hide overflow */
                    html, body {
                        height: 100% !important;
                        overflow: hidden !important;
                        background-color: #ffffff !important;
                        color: #000000 !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        font-family: Arial, sans-serif !important;
                    }
                    @page {
                        size: portrait;
                        margin: 0;
                    }
                    /* Hide non-ticket layout elements */
                    header, footer, .app-header, .footer-credits-custom, .no-print {
                        display: none !important;
                    }
                    .auth-wrapper {
                        background: none !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        min-height: auto !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        height: 100vh !important;
                    }
                    /* Ticket box optimization */
                    .glass-panel {
                        border: none !important;
                        box-shadow: none !important;
                        background: #ffffff !important;
                        padding: 0 !important;
                        margin: 0 auto !important;
                        width: 100% !important;
                        max-width: 500px !important; /* Limit width on page */
                    }
                    .ticket-header {
                        background-color: #f8fafc !important;
                        border-bottom: 2px dashed #000000 !important;
                        padding: 16px !important;
                        color: #000000 !important;
                    }
                    .ticket-header h2, .ticket-header span {
                        color: #000000 !important;
                    }
                    .ticket-header div {
                        background: #ffffff !important;
                        color: #000000 !important;
                        border: 1px solid #cbd5e1 !important;
                        box-shadow: none !important;
                    }
                    .ticket-body {
                        padding: 20px 15px !important;
                    }
                    .ticket-code {
                        background-color: transparent !important;
                        border: 1.5px dashed #000000 !important;
                        padding: 8px 16px !important;
                        margin-bottom: 16px !important;
                    }
                    .ticket-details {
                        background-color: transparent !important;
                        border: 1px solid #cbd5e1 !important;
                        padding: 12px 16px !important;
                        margin-bottom: 16px !important;
                        gap: 10px !important;
                        font-size: 0.85rem !important;
                    }
                    .ticket-footer-note {
                        background-color: transparent !important;
                        border-top: 1.5px dashed #cbd5e1 !important;
                        padding: 12px !important;
                        color: #000000 !important;
                    }
                }
            </style>

            <!-- Tampilan Tiket Setelah Sukses Mendaftar -->
            <div class="glass-panel" style="padding: 0; overflow: hidden; border-radius: 20px; box-shadow: var(--shadow-lg); text-align: center; animation: ticketSlideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1); border: 2px solid var(--primary-glow);">
                <div class="ticket-header" style="background-color: var(--bg-header); color: #ffffff; padding: 24px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div style="width: 48px; height: 48px; background: #ffffff; color: var(--bg-header); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">🏥</div>
                    <h2 style="color: #ffffff; margin: 0; font-family: var(--font-heading); font-size: 1.4rem;">Puskesmas Salem</h2>
                    <span style="font-size: 0.8rem; opacity: 0.8; letter-spacing: 0.05em; font-weight: 700; text-transform: uppercase;">Bukti Pendaftaran Antrian</span>
                </div>
                
                <div class="ticket-body" style="padding: 30px var(--container-px); background-color: #ffffff;">
                    <span style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; display: block; margin-bottom: 6px;">Nomor Antrian</span>
                    <strong style="font-size: 4rem; color: var(--primary-dark); font-family: var(--font-heading); display: block; line-height: 1.1; margin-bottom: 16px;"><?= htmlspecialchars($successTicket['queue_number']) ?></strong>
                    
                    <div class="ticket-code" style="display: inline-block; background-color: #f0fdf4; border: 1.5px dashed var(--primary); padding: 12px 24px; border-radius: 10px; margin-bottom: 24px;">
                        <span style="font-size: 0.78rem; color: var(--text-secondary); display: block; margin-bottom: 2px; font-weight: 600;">KODE PENELUSURAN STATUS (CEK STATUS)</span>
                        <strong style="font-size: 1.3rem; color: var(--primary-dark); font-family: var(--font-heading); letter-spacing: 0.05em;"><?= htmlspecialchars($successTicket['id']) ?></strong>
                    </div>

                    <div class="ticket-details" style="display: flex; flex-direction: column; gap: 14px; text-align: left; background-color: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid var(--border-light); font-size: 0.9rem; margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-secondary);">No. Rekam Medis:</span> <strong><?= htmlspecialchars($successTicket['no_rm']) ?></strong></div>
                        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-secondary);">Nama Pasien:</span> <strong><?= htmlspecialchars($successTicket['nama']) ?></strong></div>
                        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-secondary);">Poli Tujuan:</span> <strong style="text-transform: uppercase; color: var(--primary-dark);"><?= htmlspecialchars($successTicket['nama_poli']) ?></strong></div>
                        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-secondary);">Dokter:</span> <strong><?= htmlspecialchars($successTicket['nama_dokter']) ?></strong></div>
                        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-secondary);">Tanggal Periksa:</span> <strong><?= date('d F Y', strtotime($successTicket['tanggal'])) ?></strong></div>
                        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-secondary);">Jenis Pembayaran:</span> <strong><?= htmlspecialchars($successTicket['jenis_pembayaran']) ?></strong></div>
                    </div>

                    <div style="display: flex; gap: 12px; justify-content: center;" class="no-print">
                        <button onclick="window.print()" class="btn" style="padding: 12px 24px; font-weight: 700; background-color: #e2e8f0; color: var(--text-primary); border: none; border-radius: 8px; cursor: pointer; transition: var(--transition-smooth); display: flex; align-items: center; gap: 8px;" onmouseover="this.style.backgroundColor='#cbd5e1'" onmouseout="this.style.backgroundColor='#e2e8f0'">
                            <span>🖨️</span> Cetak Tiket
                        </button>
                        <a href="<?= url('/') ?>" class="btn-hero-primary" style="padding: 12px 24px; font-size: 0.9rem; border-radius: 8px; text-decoration: none;">
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
                
                <div class="ticket-footer-note" style="background-color: #f8fafc; border-top: 1px solid var(--border-light); padding: 16px; font-size: 0.8rem; color: var(--text-muted);">
                    Simpan Kode Penelusuran di atas untuk memantau antrian secara real-time dari handphone Anda.
                </div>
            </div>
        <?php else: ?>
            <!-- Tampilan Formulir Pendaftaran Antrian -->
            <div class="glass-panel" style="padding: 0; overflow: hidden; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.06);">
                <!-- Card Header -->
                <div style="background-color: var(--bg-header); color: #ffffff; padding: 20px 24px;">
                    <h2 style="color: #ffffff; margin: 0 0 4px 0; font-family: var(--font-heading); font-size: 1.35rem; display: flex; align-items: center; gap: 8px;">
                        <span>📋</span> Formulir Pendaftaran Antrian
                    </h2>
                    <p style="color: rgba(255,255,255,0.85); margin: 0; font-size: 0.85rem;">Isi data di bawah ini untuk mendapatkan nomor antrian.</p>
                </div>
                
                <!-- Card Body -->
                <div style="padding: 30px 24px;">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" style="margin-bottom: 20px; font-size: 0.88rem;">
                            <span><strong>Pendaftaran Gagal:</strong> <?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="<?= url('/kiosk') ?>" method="POST" autocomplete="off">
                        <!-- Data Diri Pasien -->
                        <h3 style="font-size: 1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-bottom: 18px; color: var(--primary-dark); font-family: var(--font-heading); display: flex; align-items: center; gap: 6px;">
                            <span>👤</span> Data Diri Pasien
                        </h3>

                        <div class="form-group">
                            <label for="nik" class="form-label">NIK (16 digit KTP)</label>
                            <input type="text" id="nik" name="nik" class="form-control" placeholder="16 digit NIK KTP" required maxlength="16" minlength="16" pattern="[0-9]{16}">
                        </div>

                        <div class="form-group">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama" class="form-control" placeholder="Nama sesuai KTP" required>
                        </div>

                        <div class="grid-2" style="gap: 16px; margin-bottom: 0;">
                            <div class="form-group">
                                <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" id="tgl_lahir" name="tgl_lahir" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="jk" class="form-label">Jenis Kelamin</label>
                                <select id="jk" name="jk" class="form-control" required style="height: 48px; background: #fafafa; border: 1.5px solid var(--border-light); border-radius: 10px;">
                                    <option value="">-- Pilih --</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid-2" style="gap: 16px; margin-bottom: 0;">
                            <div class="form-group">
                                <label for="no_telp" class="form-label">No. Telepon</label>
                                <input type="text" id="no_telp" name="no_telp" class="form-control" placeholder="Contoh: 08123456789" required>
                            </div>
                            <div class="form-group">
                                <label for="no_bpjs" class="form-label">No. BPJS (Opsional)</label>
                                <input type="text" id="no_bpjs" name="no_bpjs" class="form-control" placeholder="Kosongkan jika bukan BPJS">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea id="alamat" name="alamat" class="form-control" placeholder="Alamat lengkap tempat tinggal" required style="min-height: 80px; resize: vertical; padding: 12px 16px;"></textarea>
                        </div>

                        <!-- Data Kunjungan -->
                        <h3 style="font-size: 1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-top: 28px; margin-bottom: 18px; color: var(--primary-dark); font-family: var(--font-heading); display: flex; align-items: center; gap: 6px;">
                            <span>🏥</span> Data Kunjungan
                        </h3>

                        <div class="grid-2" style="gap: 16px; margin-bottom: 0;">
                            <div class="form-group">
                                <label for="tanggal_kunjungan" class="form-label">Tanggal Kunjungan</label>
                                <input type="date" id="tanggal_kunjungan" name="tanggal_kunjungan" class="form-control" required value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="form-group">
                                <label for="jenis_pembayaran" class="form-label">Jenis Pembayaran</label>
                                <select id="jenis_pembayaran" name="jenis_pembayaran" class="form-control" required style="height: 48px; background: #fafafa; border: 1.5px solid var(--border-light); border-radius: 10px;">
                                    <option value="Umum / Bayar Mandiri">Umum / Bayar Mandiri</option>
                                    <option value="BPJS Kesehatan">BPJS Kesehatan</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid-2" style="gap: 16px; margin-bottom: 0;">
                            <div class="form-group">
                                <label for="kd_poli" class="form-label">Pilih Poli</label>
                                <select id="kd_poli" name="kd_poli" class="form-control" required style="height: 48px; background: #fafafa; border: 1.5px solid var(--border-light); border-radius: 10px;">
                                    <option value="">-- Pilih Poli --</option>
                                    <?php foreach ($polis as $p): ?>
                                        <option value="<?= $p['kd_poli'] ?>" <?= ($selectedPoli ?? '') === $p['kd_poli'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nama_poli']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="kd_dokter" class="form-label">Pilih Dokter</label>
                                <select id="kd_dokter" name="kd_dokter" class="form-control" required style="height: 48px; background: #fafafa; border: 1.5px solid var(--border-light); border-radius: 10px;">
                                    <option value="">-- Pilih Dokter --</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="keluhan" class="form-label">Keluhan</label>
                            <textarea id="keluhan" name="keluhan" class="form-control" placeholder="Ceritakan keluhan Anda (opsional)" style="min-height: 80px; resize: vertical; padding: 12px 16px;"></textarea>
                        </div>

                        <div style="display: flex; gap: 12px; margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 24px;">
                            <a href="<?= url('/') ?>" class="btn" style="flex: 1; padding: 14px; background-color: #ffffff; color: var(--text-primary); border: 1.5px solid var(--border-light); border-radius: 10px; font-size: 1rem; font-weight: 700; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: var(--transition-smooth);" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='#ffffff'">
                                &larr; Kembali
                            </a>
                            <button type="submit" class="btn-primary" style="flex: 1; padding: 14px; background: var(--primary); color: #ffffff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 700; font-family: var(--font-heading); cursor: pointer; transition: var(--transition-smooth); display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <span>✓</span> Daftar Antrian
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Dynamic Poli-Dokter Select AJAX handler -->
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const poliSelect = document.getElementById('kd_poli');
                    const dokterSelect = document.getElementById('kd_dokter');
                    
                    const dokters = <?= json_encode($dokters) ?>;
                    const selectedDokter = "<?= $selectedDokter ?? '' ?>";
                    
                    function updateDokters() {
                        const poliVal = poliSelect.value;
                        dokterSelect.innerHTML = '<option value="">-- Pilih Dokter --</option>';
                        
                        if (poliVal) {
                            const filtered = dokters.filter(d => d.kd_poli === poliVal);
                            filtered.forEach(d => {
                                const opt = document.createElement('option');
                                opt.value = d.kd_dokter;
                                opt.textContent = d.nama_dokter;
                                if (d.kd_dokter === selectedDokter) {
                                    opt.selected = true;
                                }
                                dokterSelect.appendChild(opt);
                            });
                        }
                    }
                    
                    poliSelect.addEventListener('change', updateDokters);
                    
                    // Trigger initial populate if poli is preselected
                    if (poliSelect.value) {
                        updateDokters();
                    }
                });
            </script>
        <?php endif; ?>

    </div>
</div>
