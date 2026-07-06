<!-- Halaman Manajemen Loket (Admin Only) -->
<div class="page-container">
    <div style="margin-bottom: 30px;">
        <h1 style="font-size: 2.2rem; margin-bottom: 5px;">Manajemen Loket Layanan</h1>
        <p style="color: var(--text-secondary);">Kelola unit loket pelayanan antrian (Admin Only).</p>
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

    <div class="operator-layout">
        
        <!-- Kolom Kiri: Daftar Loket Terdaftar -->
        <div>
            <div class="glass-panel">
                <h2 class="glass-panel-title">Daftar Loket</h2>
                
                <?php if (empty($counters)): ?>
                    <p style="color: var(--text-secondary); text-align: center; padding: 40px 0;">Belum ada loket terdaftar.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Nama Loket</th>
                                    <th>Status Keaktifan</th>
                                    <th>Nomor Saat Ini</th>
                                    <th style="text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($counters as $c): ?>
                                    <tr>
                                        <td style="font-weight: 600; font-size: 1rem;"><?= htmlspecialchars($c->name) ?></td>
                                        <td>
                                            <?php if ($c->isActive): ?>
                                                <span class="badge" style="background: rgba(16,185,129,0.15); color: var(--success);">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge" style="background: rgba(100,116,139,0.15); color: var(--text-secondary);">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-family: var(--font-heading); font-weight: 700; color: var(--primary-dark);">
                                            <?= htmlspecialchars($c->currentQueueNumber ?: '-') ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <div style="display: inline-flex; gap: 8px;">
                                                <!-- Tombol Edit memicu pengisian form edit di kolom kanan -->
                                                <button type="button" class="btn btn-secondary" 
                                                        style="padding: 6px 12px; font-size: 0.8rem;"
                                                        onclick="loadEditForm('<?= htmlspecialchars($c->id) ?>', '<?= htmlspecialchars($c->name) ?>', <?= $c->isActive ? 'true' : 'false' ?>)">
                                                    Edit
                                                </button>
                                                
                                                <form action="<?= url('/counters/delete') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus loket ini?')" style="margin: 0;">
                                                    <input type="hidden" name="id" value="<?= htmlspecialchars($c->id) ?>">
                                                    <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.8rem; background: rgba(244,63,94,0.15); color: var(--danger); border: 1px solid rgba(244,63,94,0.2);">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Kolom Kanan: Form Tambah/Edit Loket -->
        <div>
            <!-- Form Tambah Loket (Default) -->
            <div class="glass-panel" id="create-panel">
                <h2 class="glass-panel-title">Tambah Loket Baru</h2>
                
                <form action="<?= url('/counters/create') ?>" method="POST">
                    <div class="form-group">
                        <label for="create_name" class="form-label">Nama Loket</label>
                        <input type="text" id="create_name" name="name" class="form-control" placeholder="Contoh: Loket 1, Teller A" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label class="form-checkbox">
                            <input type="checkbox" name="is_active" checked>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Aktifkan Loket Langsung</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Simpan Loket</button>
                </form>
            </div>

            <!-- Form Edit Loket (Tersembunyi secara default) -->
            <div class="glass-panel" id="edit-panel" style="display: none; border-color: var(--primary);">
                <h2 class="glass-panel-title" style="color: var(--primary);">Edit Detail Loket</h2>
                
                <form action="<?= url('/counters/update') ?>" method="POST">
                    <input type="hidden" id="edit_id" name="id" value="">
                    
                    <div class="form-group">
                        <label for="edit_name" class="form-label">Nama Loket</label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label class="form-checkbox">
                            <input type="checkbox" id="edit_is_active" name="is_active">
                            <span style="font-size: 0.9rem; color: var(--text-secondary);">Loket Aktif</span>
                        </label>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">Update Loket</button>
                        <button type="button" class="btn btn-secondary" onclick="cancelEdit()" style="flex: 1;">Batal</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    // Memindahkan data ke form edit dan menampilkannya
    function loadEditForm(id, name, isActive) {
        document.getElementById('create-panel').style.display = 'none';
        
        const editPanel = document.getElementById('edit-panel');
        editPanel.style.display = 'block';
        
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_is_active').checked = isActive;
        
        // Scroll halus ke panel form
        editPanel.scrollIntoView({ behavior: 'smooth' });
    }

    // Membatalkan edit dan mengembalikan ke form tambah
    function cancelEdit() {
        document.getElementById('edit-panel').style.display = 'none';
        document.getElementById('create-panel').style.display = 'block';
    }
</script>
