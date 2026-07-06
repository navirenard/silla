<!-- Halaman Manajemen Master Data (Admin Only) -->
<div class="page-container">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-title">
            <h1>🗂️ Manajemen Master Data</h1>
            <p>Kelola data master Poliklinik, Dokter, Jadwal Praktik, dan Pasien.</p>
        </div>
        <div class="page-header-action">
            <a href="<?= url('/queues') ?>" class="btn btn-secondary">&larr; Control Panel</a>
        </div>
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

    <!-- Tab Bar -->
    <div class="tab-bar">
        <a href="?tab=poli"   class="tab-item <?= $activeTab === 'poli'   ? 'active' : '' ?>">🏢 Poliklinik</a>
        <a href="?tab=dokter" class="tab-item <?= $activeTab === 'dokter' ? 'active' : '' ?>">🩺 Dokter</a>
        <a href="?tab=jadwal" class="tab-item <?= $activeTab === 'jadwal' ? 'active' : '' ?>">🗓️ Jadwal Praktik</a>
        <a href="?tab=pasien" class="tab-item <?= $activeTab === 'pasien' ? 'active' : '' ?>">👥 Pasien (Rekam Medis)</a>
    </div>

    <!-- TAB 1: POLIKLINIK -->
    <?php if ($activeTab === 'poli'): ?>
        <div class="master-layout">
            <div class="glass-panel" style="border-radius: 16px;">
                <h3 class="glass-panel-title" style="font-family: var(--font-heading);">Daftar Poliklinik</h3>
                <div class="table-responsive">
                    <table class="custom-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Kode Poli</th>
                                <th>Nama Poliklinik</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($polis as $p): ?>
                                <tr>
                                    <td class="fw-bold text-primary-dark"><?= htmlspecialchars($p['kd_poli']) ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($p['nama_poli']) ?></td>
                                    <td style="text-align: right;">
                                        <div class="table-actions">
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="loadPoliEdit('<?= htmlspecialchars($p['kd_poli']) ?>', '<?= htmlspecialchars($p['nama_poli']) ?>')">Edit</button>
                                            <form action="<?= url('/admin/master/poli/delete') ?>" method="POST" onsubmit="return confirm('Hapus poliklinik ini? Semua dokter di poli ini juga akan terhapus.')" style="margin:0;">
                                                <input type="hidden" name="kd_poli" value="<?= htmlspecialchars($p['kd_poli']) ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div>
                <!-- Add Form -->
                <div class="glass-panel" id="poli-create-panel" style="border-radius: 16px;">
                    <h3 class="glass-panel-title" style="font-family: var(--font-heading);">Tambah Poli Baru</h3>
                    <form action="<?= url('/admin/master/poli/create') ?>" method="POST">
                        <div class="form-group">
                            <label for="kd_poli_add" class="form-label">Kode Poli</label>
                            <input type="text" id="kd_poli_add" name="kd_poli" class="form-control" placeholder="Contoh: P005" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="nama_poli_add" class="form-label">Nama Poliklinik</label>
                            <input type="text" id="nama_poli_add" name="nama_poli" class="form-control" placeholder="Contoh: Poli Mata" required>
                        </div>
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 12px;">Simpan Poli</button>
                    </form>
                </div>
                <!-- Edit Form -->
                <div class="glass-panel" id="poli-edit-panel" style="display: none; border-radius: 16px; border-color: var(--primary);">
                    <h3 class="glass-panel-title" style="color: var(--primary); font-family: var(--font-heading);">Edit Poliklinik</h3>
                    <form action="<?= url('/admin/master/poli/update') ?>" method="POST">
                        <input type="hidden" id="kd_poli_edit" name="kd_poli">
                        <div class="form-group">
                            <label class="form-label">Kode Poli (Tidak Dapat Diubah)</label>
                            <input type="text" id="kd_poli_edit_display" class="form-control" disabled>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="nama_poli_edit" class="form-label">Nama Poliklinik</label>
                            <input type="text" id="nama_poli_edit" name="nama_poli" class="form-control" required>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button type="submit" class="btn-primary" style="flex: 1; padding: 12px;">Update</button>
                            <button type="button" class="btn" style="flex: 1; padding: 12px; background-color: #f1f5f9;" onclick="cancelPoliEdit()">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            function loadPoliEdit(kd, nama) {
                document.getElementById('poli-create-panel').style.display = 'none';
                document.getElementById('poli-edit-panel').style.display = 'block';
                document.getElementById('kd_poli_edit').value = kd;
                document.getElementById('kd_poli_edit_display').value = kd;
                document.getElementById('nama_poli_edit').value = nama;
            }
            function cancelPoliEdit() {
                document.getElementById('poli-edit-panel').style.display = 'none';
                document.getElementById('poli-create-panel').style.display = 'block';
            }
        </script>
    <?php endif; ?>

    <!-- TAB 2: DOKTER -->
    <?php if ($activeTab === 'dokter'): ?>
        <div class="master-layout">
            <div class="glass-panel" style="border-radius: 16px;">
                <h3 class="glass-panel-title" style="font-family: var(--font-heading);">Daftar Dokter</h3>
                <div class="table-responsive">
                    <table class="custom-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Kode Dokter</th>
                                <th>Nama Dokter</th>
                                <th>Poliklinik</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dokters as $d): ?>
                                <tr>
                                    <td class="fw-bold text-primary-dark"><?= htmlspecialchars($d['kd_dokter']) ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($d['nama_dokter']) ?></td>
                                    <td><span class="badge-poli"><?= htmlspecialchars($d['nama_poli']) ?></span></td>
                                    <td style="text-align: right;">
                                        <div class="table-actions">
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="loadDokterEdit('<?= htmlspecialchars($d['kd_dokter']) ?>', '<?= htmlspecialchars($d['nama_dokter']) ?>', '<?= htmlspecialchars($d['kd_poli']) ?>')">Edit</button>
                                            <form action="<?= url('/admin/master/dokter/delete') ?>" method="POST" onsubmit="return confirm('Hapus dokter ini? Semua jadwal prakteknya juga akan terhapus.')" style="margin:0;">
                                                <input type="hidden" name="kd_dokter" value="<?= htmlspecialchars($d['kd_dokter']) ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div>
                <!-- Add Form -->
                <div class="glass-panel" id="dokter-create-panel" style="border-radius: 16px;">
                    <h3 class="glass-panel-title" style="font-family: var(--font-heading);">Tambah Dokter Baru</h3>
                    <form action="<?= url('/admin/master/dokter/create') ?>" method="POST">
                        <div class="form-group">
                            <label for="kd_dokter_add" class="form-label">Kode Dokter</label>
                            <input type="text" id="kd_dokter_add" name="kd_dokter" class="form-control" placeholder="Contoh: D005" required>
                        </div>
                        <div class="form-group">
                            <label for="nama_dokter_add" class="form-label">Nama Dokter</label>
                            <input type="text" id="nama_dokter_add" name="nama_dokter" class="form-control" placeholder="Contoh: dr. Setiawan" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="kd_poli_dokter_add" class="form-label">Poliklinik Tugas</label>
                            <select id="kd_poli_dokter_add" name="kd_poli" class="form-control" required style="height: 48px; border-radius: 10px;">
                                <option value="">-- Pilih Poli --</option>
                                <?php foreach ($polis as $p): ?>
                                    <option value="<?= htmlspecialchars($p['kd_poli']) ?>"><?= htmlspecialchars($p['nama_poli']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 12px;">Simpan Dokter</button>
                    </form>
                </div>
                <!-- Edit Form -->
                <div class="glass-panel" id="dokter-edit-panel" style="display: none; border-radius: 16px; border-color: var(--primary);">
                    <h3 class="glass-panel-title" style="color: var(--primary); font-family: var(--font-heading);">Edit Dokter</h3>
                    <form action="<?= url('/admin/master/dokter/update') ?>" method="POST">
                        <input type="hidden" id="kd_dokter_edit" name="kd_dokter">
                        <div class="form-group">
                            <label class="form-label">Kode Dokter (Tidak Dapat Diubah)</label>
                            <input type="text" id="kd_dokter_edit_display" class="form-control" disabled>
                        </div>
                        <div class="form-group">
                            <label for="nama_dokter_edit" class="form-label">Nama Dokter</label>
                            <input type="text" id="nama_dokter_edit" name="nama_dokter" class="form-control" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="kd_poli_dokter_edit" class="form-label">Poliklinik Tugas</label>
                            <select id="kd_poli_dokter_edit" name="kd_poli" class="form-control" required style="height: 48px; border-radius: 10px;">
                                <?php foreach ($polis as $p): ?>
                                    <option value="<?= htmlspecialchars($p['kd_poli']) ?>"><?= htmlspecialchars($p['nama_poli']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button type="submit" class="btn-primary" style="flex: 1; padding: 12px;">Update</button>
                            <button type="button" class="btn" style="flex: 1; padding: 12px; background-color: #f1f5f9;" onclick="cancelDokterEdit()">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            function loadDokterEdit(kd, nama, poli) {
                document.getElementById('dokter-create-panel').style.display = 'none';
                document.getElementById('dokter-edit-panel').style.display = 'block';
                document.getElementById('kd_dokter_edit').value = kd;
                document.getElementById('kd_dokter_edit_display').value = kd;
                document.getElementById('nama_dokter_edit').value = nama;
                document.getElementById('kd_poli_dokter_edit').value = poli;
            }
            function cancelDokterEdit() {
                document.getElementById('dokter-edit-panel').style.display = 'none';
                document.getElementById('dokter-create-panel').style.display = 'block';
            }
        </script>
    <?php endif; ?>

    <!-- TAB 3: JADWAL PRAKTIK -->
    <?php if ($activeTab === 'jadwal'): ?>
        <div class="master-layout">
            <div class="glass-panel" style="border-radius: 16px;">
                <h3 class="glass-panel-title" style="font-family: var(--font-heading);">Daftar Jadwal Praktik Dokter</h3>
                <div class="table-responsive">
                    <table class="custom-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Dokter</th>
                                <th>Poli</th>
                                <th>Jam Praktik</th>
                                <th>Kuota</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jadwals as $j): ?>
                                <tr>
                                    <td class="fw-bold text-primary-dark"><?= htmlspecialchars($j['hari']) ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($j['nama_dokter']) ?></td>
                                    <td><span class="badge-poli"><?= htmlspecialchars($j['nama_poli']) ?></span></td>
                                    <td class="text-muted-sm"><?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?></td>
                                    <td class="fw-bold"><?= $j['kuota'] ?></td>
                                    <td style="text-align: right;">
                                        <div class="table-actions">
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="loadJadwalEdit(<?= $j['id'] ?>, '<?= htmlspecialchars($j['kd_dokter']) ?>', '<?= htmlspecialchars($j['hari']) ?>', '<?= substr($j['jam_mulai'], 0, 5) ?>', '<?= substr($j['jam_selesai'], 0, 5) ?>', <?= $j['kuota'] ?>)">Edit</button>
                                            <form action="<?= url('/admin/master/jadwal/delete') ?>" method="POST" onsubmit="return confirm('Hapus jadwal praktik ini?')" style="margin:0;">
                                                <input type="hidden" name="id" value="<?= $j['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div>
                <!-- Add Form -->
                <div class="glass-panel" id="jadwal-create-panel" style="border-radius: 16px;">
                    <h3 class="glass-panel-title" style="font-family: var(--font-heading);">Tambah Jadwal Baru</h3>
                    <form action="<?= url('/admin/master/jadwal/create') ?>" method="POST">
                        <div class="form-group">
                            <label for="kd_dokter_jadwal_add" class="form-label">Pilih Dokter</label>
                            <select id="kd_dokter_jadwal_add" name="kd_dokter" class="form-control" required style="height: 48px; border-radius: 10px;">
                                <option value="">-- Pilih Dokter --</option>
                                <?php foreach ($dokters as $d): ?>
                                    <option value="<?= htmlspecialchars($d['kd_dokter']) ?>"><?= htmlspecialchars($d['nama_dokter']) ?> (<?= htmlspecialchars($d['nama_poli']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="hari_add" class="form-label">Hari Praktik</label>
                            <select id="hari_add" name="hari" class="form-control" required style="height: 48px; border-radius: 10px;">
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                            </select>
                        </div>
                        <div class="grid-2" style="gap: 12px; margin-bottom: 0;">
                            <div class="form-group">
                                <label for="jam_mulai_add" class="form-label">Jam Mulai</label>
                                <input type="time" id="jam_mulai_add" name="jam_mulai" class="form-control" value="08:00" required>
                            </div>
                            <div class="form-group">
                                <label for="jam_selesai_add" class="form-label">Jam Selesai</label>
                                <input type="time" id="jam_selesai_add" name="jam_selesai" class="form-control" value="12:00" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="kuota_add" class="form-label">Kuota Harian Pasien</label>
                            <input type="number" id="kuota_add" name="kuota" class="form-control" value="20" required min="1">
                        </div>
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 12px;">Simpan Jadwal</button>
                    </form>
                </div>
                <!-- Edit Form -->
                <div class="glass-panel" id="jadwal-edit-panel" style="display: none; border-radius: 16px; border-color: var(--primary);">
                    <h3 class="glass-panel-title" style="color: var(--primary); font-family: var(--font-heading);">Edit Jadwal Praktik</h3>
                    <form action="<?= url('/admin/master/jadwal/update') ?>" method="POST">
                        <input type="hidden" id="jadwal_id_edit" name="id">
                        <div class="form-group">
                            <label for="kd_dokter_jadwal_edit" class="form-label">Pilih Dokter</label>
                            <select id="kd_dokter_jadwal_edit" name="kd_dokter" class="form-control" required style="height: 48px; border-radius: 10px;">
                                <?php foreach ($dokters as $d): ?>
                                    <option value="<?= htmlspecialchars($d['kd_dokter']) ?>"><?= htmlspecialchars($d['nama_dokter']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="hari_edit" class="form-label">Hari Praktik</label>
                            <select id="hari_edit" name="hari" class="form-control" required style="height: 48px; border-radius: 10px;">
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                            </select>
                        </div>
                        <div class="grid-2" style="gap: 12px; margin-bottom: 0;">
                            <div class="form-group">
                                <label for="jam_mulai_edit" class="form-label">Jam Mulai</label>
                                <input type="time" id="jam_mulai_edit" name="jam_mulai" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="jam_selesai_edit" class="form-label">Jam Selesai</label>
                                <input type="time" id="jam_selesai_edit" name="jam_selesai" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="kuota_edit" class="form-label">Kuota Harian Pasien</label>
                            <input type="number" id="kuota_edit" name="kuota" class="form-control" required min="1">
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button type="submit" class="btn-primary" style="flex: 1; padding: 12px;">Update</button>
                            <button type="button" class="btn" style="flex: 1; padding: 12px; background-color: #f1f5f9;" onclick="cancelJadwalEdit()">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            function loadJadwalEdit(id, kd_dokter, hari, mulai, selesai, kuota) {
                document.getElementById('jadwal-create-panel').style.display = 'none';
                document.getElementById('jadwal-edit-panel').style.display = 'block';
                document.getElementById('jadwal_id_edit').value = id;
                document.getElementById('kd_dokter_jadwal_edit').value = kd_dokter;
                document.getElementById('hari_edit').value = hari;
                document.getElementById('jam_mulai_edit').value = mulai;
                document.getElementById('jam_selesai_edit').value = selesai;
                document.getElementById('kuota_edit').value = kuota;
            }
            function cancelJadwalEdit() {
                document.getElementById('jadwal-edit-panel').style.display = 'none';
                document.getElementById('jadwal-create-panel').style.display = 'block';
            }
        </script>
    <?php endif; ?>

    <!-- TAB 4: PASIEN -->
    <?php if ($activeTab === 'pasien'): ?>
        <div class="master-layout">
            <div class="glass-panel" style="border-radius: 16px;">
                <h3 class="glass-panel-title" style="font-family: var(--font-heading);">Rekam Medis Pasien Terdaftar</h3>
                <div class="table-responsive">
                    <table class="custom-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No. RM</th>
                                <th>NIK</th>
                                <th>Nama Pasien</th>
                                <th>Tgl Lahir</th>
                                <th>J. Kelamin</th>
                                <th>No. Telepon</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pasiens as $pas): ?>
                                <tr>
                                    <td class="fw-bold text-primary-dark"><?= htmlspecialchars($pas['no_rm']) ?></td>
                                    <td class="text-muted-sm"><?= htmlspecialchars($pas['nik']) ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($pas['nama']) ?></td>
                                    <td class="text-muted-sm"><?= date('d-m-Y', strtotime($pas['tgl_lahir'])) ?></td>
                                    <td><?= htmlspecialchars($pas['jk']) ?></td>
                                    <td class="text-muted-sm"><?= htmlspecialchars($pas['no_telp']) ?></td>
                                    <td style="text-align: right;">
                                        <div class="table-actions">
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="loadPasienEdit('<?= htmlspecialchars($pas['no_rm']) ?>', '<?= htmlspecialchars($pas['nik']) ?>', '<?= htmlspecialchars($pas['nama']) ?>', '<?= htmlspecialchars($pas['tgl_lahir']) ?>', '<?= htmlspecialchars($pas['jk']) ?>', '<?= htmlspecialchars($pas['no_telp']) ?>', '<?= htmlspecialchars($pas['no_bpjs']) ?>', '<?= htmlspecialchars(json_encode($pas['alamat'])) ?>')">Edit</button>
                                            <form action="<?= url('/admin/master/pasien/delete') ?>" method="POST" onsubmit="return confirm('Hapus data pasien ini? Seluruh riwayat antrian pasien ini juga akan terhapus.')" style="margin:0;">
                                                <input type="hidden" name="no_rm" value="<?= htmlspecialchars($pas['no_rm']) ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div>
                <!-- Add Form -->
                <div class="glass-panel" id="pasien-create-panel" style="border-radius: 16px;">
                    <h3 class="glass-panel-title" style="font-family: var(--font-heading);">Tambah Pasien Baru</h3>
                    <form action="<?= url('/admin/master/pasien/create') ?>" method="POST" autocomplete="off">
                        <div class="form-group">
                            <label for="nik_pas_add" class="form-label">NIK (16 digit KTP)</label>
                            <input type="text" id="nik_pas_add" name="nik" class="form-control" placeholder="16 digit NIK KTP" required maxlength="16" minlength="16" pattern="[0-9]{16}">
                        </div>
                        <div class="form-group">
                            <label for="nama_pas_add" class="form-label">Nama Lengkap</label>
                            <input type="text" id="nama_pas_add" name="nama" class="form-control" placeholder="Nama sesuai KTP" required>
                        </div>
                        <div class="grid-2" style="gap: 12px; margin-bottom: 0;">
                            <div class="form-group">
                                <label for="tgl_lahir_pas_add" class="form-label">Tanggal Lahir</label>
                                <input type="date" id="tgl_lahir_pas_add" name="tgl_lahir" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="jk_pas_add" class="form-label">Jenis Kelamin</label>
                                <select id="jk_pas_add" name="jk" class="form-control" required style="height: 48px; border-radius: 10px;">
                                    <option value="">-- Pilih --</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="no_telp_pas_add" class="form-label">No. Telepon</label>
                            <input type="text" id="no_telp_pas_add" name="no_telp" class="form-control" placeholder="Contoh: 08123456789" required>
                        </div>
                        <div class="form-group">
                            <label for="no_bpjs_pas_add" class="form-label">No. BPJS (Opsional)</label>
                            <input type="text" id="no_bpjs_pas_add" name="no_bpjs" class="form-control" placeholder="Kosongkan jika bukan BPJS">
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="alamat_pas_add" class="form-label">Alamat</label>
                            <textarea id="alamat_pas_add" name="alamat" class="form-control" placeholder="Alamat lengkap tempat tinggal" style="min-height: 80px;" required></textarea>
                        </div>
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 12px;">Simpan Pasien</button>
                    </form>
                </div>
                <!-- Edit Form -->
                <div class="glass-panel" id="pasien-edit-panel" style="display: none; border-radius: 16px; border-color: var(--primary);">
                    <h3 class="glass-panel-title" style="color: var(--primary); font-family: var(--font-heading);">Edit Pasien</h3>
                    <form action="<?= url('/admin/master/pasien/update') ?>" method="POST">
                        <input type="hidden" id="no_rm_edit" name="no_rm">
                        <div class="form-group">
                            <label class="form-label">No. Rekam Medis (RM)</label>
                            <input type="text" id="no_rm_edit_display" class="form-control" disabled>
                        </div>
                        <div class="form-group">
                            <label for="nik_pas_edit" class="form-label">NIK (16 digit KTP)</label>
                            <input type="text" id="nik_pas_edit" name="nik" class="form-control" placeholder="16 digit NIK KTP" required maxlength="16" minlength="16" pattern="[0-9]{16}">
                        </div>
                        <div class="form-group">
                            <label for="nama_pas_edit" class="form-label">Nama Lengkap</label>
                            <input type="text" id="nama_pas_edit" name="nama" class="form-control" placeholder="Nama sesuai KTP" required>
                        </div>
                        <div class="grid-2" style="gap: 12px; margin-bottom: 0;">
                            <div class="form-group">
                                <label for="tgl_lahir_pas_edit" class="form-label">Tanggal Lahir</label>
                                <input type="date" id="tgl_lahir_pas_edit" name="tgl_lahir" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="jk_pas_edit" class="form-label">Jenis Kelamin</label>
                                <select id="jk_pas_edit" name="jk" class="form-control" required style="height: 48px; border-radius: 10px;">
                                    <option value="">-- Pilih --</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="no_telp_pas_edit" class="form-label">No. Telepon</label>
                            <input type="text" id="no_telp_pas_edit" name="no_telp" class="form-control" placeholder="Contoh: 08123456789" required>
                        </div>
                        <div class="form-group">
                            <label for="no_bpjs_pas_edit" class="form-label">No. BPJS (Opsional)</label>
                            <input type="text" id="no_bpjs_pas_edit" name="no_bpjs" class="form-control" placeholder="Kosongkan jika bukan BPJS">
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="alamat_pas_edit" class="form-label">Alamat</label>
                            <textarea id="alamat_pas_edit" name="alamat" class="form-control" placeholder="Alamat lengkap tempat tinggal" style="min-height: 80px;" required></textarea>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button type="submit" class="btn-primary" style="flex: 1; padding: 12px;">Update</button>
                            <button type="button" class="btn" style="flex: 1; padding: 12px; background-color: #f1f5f9;" onclick="cancelPasienEdit()">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            function loadPasienEdit(rm, nik, nama, tgl, jk, telp, bpjs, alamatJson) {
                document.getElementById('pasien-create-panel').style.display = 'none';
                document.getElementById('pasien-edit-panel').style.display = 'block';
                document.getElementById('no_rm_edit').value = rm;
                document.getElementById('no_rm_edit_display').value = rm;
                document.getElementById('nik_pas_edit').value = nik;
                document.getElementById('nama_pas_edit').value = nama;
                document.getElementById('tgl_lahir_pas_edit').value = tgl;
                document.getElementById('jk_pas_edit').value = jk;
                document.getElementById('no_telp_pas_edit').value = telp;
                document.getElementById('no_bpjs_pas_edit').value = bpjs === 'null' || !bpjs ? '' : bpjs;
                
                // Parse double-encoded address
                let parsedAddr = "";
                try {
                    parsedAddr = JSON.parse(alamatJson);
                } catch(e) {
                    parsedAddr = alamatJson;
                }
                document.getElementById('alamat_pas_edit').value = parsedAddr;
            }
            function cancelPasienEdit() {
                document.getElementById('pasien-edit-panel').style.display = 'none';
                document.getElementById('pasien-create-panel').style.display = 'block';
            }
        </script>
    <?php endif; ?>
</div>
