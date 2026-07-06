<?php

namespace App\Presentation\Controllers;

use Container;
use PDO;
use Exception;
use App\Presentation\Middleware\AuthMiddleware;

class QueueController extends Controller
{
    /**
     * Halaman Control Panel Antrian Petugas (Admin & Operator Only)
     */
    public function index(): void
    {
        AuthMiddleware::requireAuth();
        $db = Container::get(PDO::class);

        // Ambil semua loket
        $stmt = $db->query("SELECT * FROM counters ORDER BY name ASC");
        $counters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ambil antrian yang sedang menunggu hari ini
        $stmtWaiting = $db->prepare("
            SELECT q.*, p.nama as nama_pasien 
            FROM queues q
            JOIN pasiens p ON q.no_rm = p.no_rm
            WHERE q.tanggal = CURRENT_DATE AND q.status = 'Menunggu'
            ORDER BY q.queue_number ASC
        ");
        $stmtWaiting->execute();
        $waitingQueues = $stmtWaiting->fetchAll(PDO::FETCH_ASSOC);

        // Dapatkan loket aktif petugas (dari cookie stateless)
        $activeCounterId = $_COOKIE['silla_counter_id'] ?? null;
        $activeCounter = null;
        $currentQueue = null;

        if ($activeCounterId) {
            foreach ($counters as $c) {
                if ($c['id'] === $activeCounterId) {
                    $activeCounter = $c;
                    break;
                }
            }

            // Dapatkan antrian yang sedang aktif dilayani di loket ini
            if ($activeCounter && $activeCounter['current_queue_id']) {
                $stmtActive = $db->prepare("
                    SELECT q.*, p.nama as nama_pasien 
                    FROM queues q
                    JOIN pasiens p ON q.no_rm = p.no_rm
                    WHERE q.id = :id
                ");
                $stmtActive->execute(['id' => $activeCounter['current_queue_id']]);
                $currentQueue = $stmtActive->fetch(PDO::FETCH_ASSOC);
            }
        }

        $this->render('queue/index', [
            'title'         => 'Operator Antrian - SiLLA',
            'counters'      => $counters,
            'waitingQueues' => $waitingQueues,
            'activeCounter' => $activeCounter,
            'currentQueue'  => $currentQueue,
            'success'       => $_GET['success'] ?? null,
            'error'         => $_GET['error'] ?? null
        ]);
    }

    /**
     * Set loket aktif bagi petugas yang sedang bertugas
     */
    public function selectCounter(): void
    {
        AuthMiddleware::requireAuth();
        $counterId = $_POST['counter_id'] ?? null;

        if ($counterId) {
            setcookie('silla_counter_id', $counterId, ['expires' => time() + 86400, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
            $this->redirect('/queues?success=' . urlencode('Loket berhasil diaktifkan.'));
        } else {
            setcookie('silla_counter_id', '', ['expires' => time() - 3600, 'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
            $this->redirect('/queues?success=' . urlencode('Loket dinonaktifkan.'));
        }
    }

    /**
     * Halaman Ambil Tiket Antrian (Form Pendaftaran Pasien Baru/Lama)
     */
    public function kiosk(): void
    {
        $db = Container::get(PDO::class);

        // Ambil data Poli untuk dropdown
        $stmtPolis = $db->query("SELECT * FROM polis ORDER BY nama_poli ASC");
        $polis = $stmtPolis->fetchAll(PDO::FETCH_ASSOC);

        // Ambil data semua Dokter untuk data JSON AJAX
        $stmtDokters = $db->query("SELECT * FROM dokters ORDER BY nama_dokter ASC");
        $dokters = $stmtDokters->fetchAll(PDO::FETCH_ASSOC);

        // Ambil tiket dari DB jika ada ticket_id di URL (setelah redirect dari registerQueue)
        $successTicket = null;
        $ticketQueueId = $_GET['ticket_id'] ?? null;
        if ($ticketQueueId) {
            $stmtTicketLoad = $db->prepare("
                SELECT q.*, p.nama, d.nama_dokter, pol.nama_poli
                FROM queues q
                JOIN pasiens p ON q.no_rm = p.no_rm
                JOIN dokters d ON q.kd_dokter = d.kd_dokter
                JOIN polis pol ON q.kd_poli = pol.kd_poli
                WHERE q.id = :id
            ");
            $stmtTicketLoad->execute(['id' => $ticketQueueId]);
            $successTicket = $stmtTicketLoad->fetch(PDO::FETCH_ASSOC);
        }

        $error = $_GET['kiosk_error'] ?? null;
        if ($error) $error = htmlspecialchars(urldecode($error));

        $selectedPoli   = $_GET['kd_poli'] ?? null;
        $selectedDokter = $_GET['kd_dokter'] ?? null;

        $this->render('queue/kiosk', [
            'title'          => 'Pendaftaran Antrian - Puskesmas Salem',
            'polis'          => $polis,
            'dokters'        => $dokters,
            'selectedPoli'   => $selectedPoli,
            'selectedDokter' => $selectedDokter,
            'successTicket'  => $successTicket,
            'error'          => $error
        ]);
    }

    /**
     * Proses Pendaftaran Antrian & Simpan Pasien Baru/Lama
     */
    public function registerQueue(): void
    {
        $db = Container::get(PDO::class);

        // 1. Ambil data input diri pasien
        $nik       = trim($_POST['nik'] ?? '');
        $nama      = trim($_POST['nama'] ?? '');
        $tgl_lahir = $_POST['tgl_lahir'] ?? '';
        $jk        = $_POST['jk'] ?? '';
        $no_telp   = trim($_POST['no_telp'] ?? '');
        $no_bpjs   = trim($_POST['no_bpjs'] ?? '');
        $alamat    = trim($_POST['alamat'] ?? '');

        // 2. Ambil data kunjungan
        $tanggal_kunjungan = $_POST['tanggal_kunjungan'] ?? date('Y-m-d');
        $jenis_pembayaran  = $_POST['jenis_pembayaran'] ?? 'Umum / Bayar Mandiri';
        $kd_poli           = $_POST['kd_poli'] ?? '';
        $kd_dokter         = $_POST['kd_dokter'] ?? '';
        $keluhan           = trim($_POST['keluhan'] ?? '');

        try {
            if (strlen($nik) !== 16) {
                throw new Exception('NIK harus berjumlah 16 digit.');
            }

            // 3. Cari pasien berdasarkan NIK di DB
            $stmtPasien = $db->prepare("SELECT * FROM pasiens WHERE nik = :nik LIMIT 1");
            $stmtPasien->execute(['nik' => $nik]);
            $pasien = $stmtPasien->fetch(PDO::FETCH_ASSOC);

            if ($pasien) {
                $no_rm = $pasien['no_rm'];
                // Update data diri pasien yang mungkin berubah
                $stmtUpdatePasien = $db->prepare("
                    UPDATE pasiens 
                    SET nama = :nama, tgl_lahir = :tgl_lahir, jk = :jk, no_telp = :no_telp, no_bpjs = :no_bpjs, alamat = :alamat
                    WHERE no_rm = :no_rm
                ");
                $stmtUpdatePasien->execute([
                    'nama'      => $nama,
                    'tgl_lahir' => $tgl_lahir,
                    'jk'        => $jk,
                    'no_telp'   => $no_telp,
                    'no_bpjs'   => $no_bpjs ? $no_bpjs : null,
                    'alamat'    => $alamat,
                    'no_rm'     => $no_rm
                ]);
            } else {
                // Generate nomor rekam medis baru (RMXXXXXX)
                $no_rm = 'RM' . str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT);
                $stmtInsertPasien = $db->prepare("
                    INSERT INTO pasiens (no_rm, nik, nama, tgl_lahir, jk, no_telp, no_bpjs, alamat, created_at)
                    VALUES (:no_rm, :nik, :nama, :tgl_lahir, :jk, :no_telp, :no_bpjs, :alamat, CURRENT_TIMESTAMP)
                ");
                $stmtInsertPasien->execute([
                    'no_rm'     => $no_rm,
                    'nik'       => $nik,
                    'nama'      => $nama,
                    'tgl_lahir' => $tgl_lahir,
                    'jk'        => $jk,
                    'no_telp'   => $no_telp,
                    'no_bpjs'   => $no_bpjs ? $no_bpjs : null,
                    'alamat'    => $alamat
                ]);
            }

            // 4. Periksa Kuota Dokter di tanggal kunjungan tersebut
            $hariKunjungan = date('l', strtotime($tanggal_kunjungan));
            $dayNames = [
                'Sunday'    => 'Minggu',
                'Monday'    => 'Senin',
                'Tuesday'   => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday'  => 'Kamis',
                'Friday'    => 'Jumat',
                'Saturday'  => 'Sabtu'
            ];
            $hariIndo = $dayNames[$hariKunjungan] ?? 'Senin';

            $stmtJadwal = $db->prepare("
                SELECT kuota FROM jadwal_praktiks 
                WHERE kd_dokter = :kd_dokter AND hari = :hari LIMIT 1
            ");
            $stmtJadwal->execute(['kd_dokter' => $kd_dokter, 'hari' => $hariIndo]);
            $kuota = $stmtJadwal->fetchColumn();

            if ($kuota === false) {
                throw new Exception("Dokter yang dipilih tidak memiliki jadwal praktik pada hari {$hariIndo} ({$tanggal_kunjungan}).");
            }

            // Hitung jumlah antrian terdaftar untuk dokter & tanggal kunjungan tersebut
            $stmtCount = $db->prepare("
                SELECT COUNT(*) FROM queues 
                WHERE kd_dokter = :kd_dokter AND tanggal = :tanggal
            ");
            $stmtCount->execute(['kd_dokter' => $kd_dokter, 'tanggal' => $tanggal_kunjungan]);
            $terdaftar = (int) $stmtCount->fetchColumn();

            if ($terdaftar >= (int) $kuota) {
                throw new Exception("Kuota pendaftaran dokter tersebut sudah penuh untuk tanggal {$tanggal_kunjungan}. Silakan pilih hari atau dokter lain.");
            }

            // 5. Generate Nomor Antrian Baru sesuai Poli
            // Inisial nomor: Poli Umum (A), KIA/KB (B), Gigi (C), Lansia (D)
            $inisialPoli = 'A';
            if ($kd_poli === 'P002') $inisialPoli = 'B';
            elseif ($kd_poli === 'P003') $inisialPoli = 'C';
            elseif ($kd_poli === 'P004') $inisialPoli = 'D';

            // Hitung nomor urut antrian di poli & tanggal tersebut
            $stmtPoliCount = $db->prepare("
                SELECT COUNT(*) FROM queues 
                WHERE kd_poli = :kd_poli AND tanggal = :tanggal
            ");
            $stmtPoliCount->execute(['kd_poli' => $kd_poli, 'tanggal' => $tanggal_kunjungan]);
            $poliQueueSeq = (int) $stmtPoliCount->fetchColumn() + 1;
            
            $queueNumber = $inisialPoli . str_pad((string) $poliQueueSeq, 3, '0', STR_PAD_LEFT); // Contoh: A001
            
            // Generate ID tiket unik (Kode Penelusuran Status) -> format: A001-260706
            $tglSuffix = date('dmy', strtotime($tanggal_kunjungan));
            $ticketId = $queueNumber . '-' . $tglSuffix;

            // 6. Simpan antrian ke database
            $stmtInsertQueue = $db->prepare("
                INSERT INTO queues (id, queue_number, no_rm, kd_poli, kd_dokter, tanggal, jenis_pembayaran, keluhan, jam_ambil, status)
                VALUES (:id, :queue_number, :no_rm, :kd_poli, :kd_dokter, :tanggal, :jenis_pembayaran, :keluhan, CURRENT_TIME, 'Menunggu')
            ");
            $stmtInsertQueue->execute([
                'id'               => $ticketId,
                'queue_number'     => $queueNumber,
                'no_rm'            => $no_rm,
                'kd_poli'          => $kd_poli,
                'kd_dokter'        => $kd_dokter,
                'tanggal'          => $tanggal_kunjungan,
                'jenis_pembayaran' => $jenis_pembayaran,
                'keluhan'          => $keluhan ? $keluhan : null
            ]);

            // Ambil data lengkap untuk dicetak di tiket
            $stmtTicket = $db->prepare("
                SELECT q.*, p.nama, d.nama_dokter, pol.nama_poli
                FROM queues q
                JOIN pasiens p ON q.no_rm = p.no_rm
                JOIN dokters d ON q.kd_dokter = d.kd_dokter
                JOIN polis pol ON q.kd_poli = pol.kd_poli
                WHERE q.id = :id
            ");
            $stmtTicket->execute(['id' => $ticketId]);
            $ticketData = $stmtTicket->fetch(PDO::FETCH_ASSOC);

            // Redirect dengan ticket_id agar bisa di-fetch ulang (stateless)
            $this->redirect('/kiosk?ticket_id=' . urlencode($ticketId));

        } catch (Exception $e) {
            $this->redirect('/kiosk?kiosk_error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Halaman Cek Status Antrian (Public)
     */
    public function showCheckStatus(): void
    {
        $db = Container::get(PDO::class);
        $searchCode = $_GET['code'] ?? null;
        
        $queue = null;
        $searched = false;

        if ($searchCode) {
            $searched = true;
            $searchCode = strtoupper(trim($searchCode));
            
            try {
                $stmt = $db->prepare("
                    SELECT q.*, p.nama as nama_pasien, d.nama_dokter, pol.nama_poli
                    FROM queues q
                    JOIN pasiens p ON q.no_rm = p.no_rm
                    JOIN dokters d ON q.kd_dokter = d.kd_dokter
                    JOIN polis pol ON q.kd_poli = pol.kd_poli
                    WHERE q.id = :id LIMIT 1
                ");
                $stmt->execute(['id' => $searchCode]);
                $queue = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $queue = null;
            }
        }

        $this->render('queue/check', [
            'title'      => 'Cek Status Antrian - Puskesmas Salem',
            'searchCode' => $searchCode,
            'searched'   => $searched,
            'queue'      => $queue
        ]);
    }

    /**
     * Halaman Jadwal Dokter Lengkap (Public)
     */
    public function showSchedule(): void
    {
        $db = Container::get(PDO::class);

        // Ambil semua jadwal dokter
        $jadwalPerHari = [];
        try {
            $stmt = $db->query("
                SELECT jp.*, d.nama_dokter, p.nama_poli, p.kd_poli
                FROM jadwal_praktiks jp
                JOIN dokters d ON jp.kd_dokter = d.kd_dokter
                JOIN polis p ON d.kd_poli = p.kd_poli
                ORDER BY 
                    CASE jp.hari 
                        WHEN 'Senin'  THEN 1 
                        WHEN 'Selasa' THEN 2 
                        WHEN 'Rabu'   THEN 3 
                        WHEN 'Kamis'  THEN 4 
                        WHEN 'Jumat'  THEN 5 
                        WHEN 'Sabtu'  THEN 6 
                        ELSE 7 
                    END, jp.jam_mulai ASC
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $jadwalPerHari[$row['hari']][] = $row;
            }
        } catch (Exception $e) {
            // Fallback
        }

        $this->render('queue/schedule', [
            'title'         => 'Jadwal Dokter - Puskesmas Salem',
            'jadwalPerHari' => $jadwalPerHari
        ]);
    }

    /**
     * Panggil Antrian Berikutnya (Petugas)
     */
    public function callNext(): void
    {
        AuthMiddleware::requireAuth();
        $db = Container::get(PDO::class);

        $counterId = $_COOKIE['silla_counter_id'] ?? null;
        $user = AuthMiddleware::getUser();
        $officerUid = $user['uid'] ?? 'unknown';

        if (!$counterId) {
            $this->redirect('/queues?error=' . urlencode('Pilih loket terlebih dahulu.'));
        }

        try {
            // Ambil nama counter
            $stmtCounter = $db->prepare("SELECT * FROM counters WHERE id = :id");
            $stmtCounter->execute(['id' => $counterId]);
            $counter = $stmtCounter->fetch(PDO::FETCH_ASSOC);

            if (!$counter) {
                throw new Exception('Loket tidak valid.');
            }

            // Dapatkan antrian 'Menunggu' terawal hari ini
            $stmtNext = $db->prepare("
                SELECT * FROM queues 
                WHERE tanggal = CURRENT_DATE AND status = 'Menunggu'
                ORDER BY queue_number ASC LIMIT 1
            ");
            $stmtNext->execute();
            $nextQueue = $stmtNext->fetch(PDO::FETCH_ASSOC);

            if ($nextQueue) {
                // Update antrian menjadi Dipanggil (serving)
                $stmtUpdateQueue = $db->prepare("
                    UPDATE queues 
                    SET status = 'Dipanggil', counter_name = :counter_name, called_at = CURRENT_TIMESTAMP, served_by = :served_by
                    WHERE id = :id
                ");
                $stmtUpdateQueue->execute([
                    'counter_name' => $counter['name'],
                    'served_by'    => $officerUid,
                    'id'           => $nextQueue['id']
                ]);

                // Update status loket
                $stmtUpdateCounter = $db->prepare("
                    UPDATE counters 
                    SET current_queue_number = :q_number, current_queue_id = :q_id
                    WHERE id = :id
                ");
                $stmtUpdateCounter->execute([
                    'q_number' => $nextQueue['queue_number'],
                    'q_id'     => $nextQueue['id'],
                    'id'       => $counterId
                ]);

                $this->redirect('/queues?success=' . urlencode("Memanggil antrian {$nextQueue['queue_number']}."));
            } else {
                $this->redirect('/queues?success=' . urlencode("Semua antrian telah selesai dilayani."));
            }

        } catch (Exception $e) {
            $this->redirect('/queues?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Tandai Antrian Selesai (Petugas)
     */
    public function complete(): void
    {
        AuthMiddleware::requireAuth();
        $db = Container::get(PDO::class);

        $queueId = $_POST['queue_id'] ?? null;
        $counterId = $_COOKIE['silla_counter_id'] ?? null;

        if (!$queueId) {
            $this->redirect('/queues');
        }

        try {
            // Update antrian jadi Selesai
            $stmtUpdateQueue = $db->prepare("
                UPDATE queues 
                SET status = 'Selesai', completed_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            $stmtUpdateQueue->execute(['id' => $queueId]);

            // Clear antrian aktif di loket
            if ($counterId) {
                $stmtUpdateCounter = $db->prepare("
                    UPDATE counters 
                    SET current_queue_number = NULL, current_queue_id = NULL
                    WHERE id = :id
                ");
                $stmtUpdateCounter->execute(['id' => $counterId]);
            }

            $this->redirect('/queues?success=' . urlencode("Antrian telah ditandai selesai."));
        } catch (Exception $e) {
            $this->redirect('/queues?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Lewati Antrian
     */
    public function skip(): void
    {
        AuthMiddleware::requireAuth();
        $db = Container::get(PDO::class);

        $queueId = $_POST['queue_id'] ?? null;
        $counterId = $_COOKIE['silla_counter_id'] ?? null;

        if (!$queueId) {
            $this->redirect('/queues');
        }

        try {
            // Update antrian jadi Lewat
            $stmtUpdateQueue = $db->prepare("
                UPDATE queues 
                SET status = 'Lewat', completed_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            $stmtUpdateQueue->execute(['id' => $queueId]);

            // Clear antrian aktif di loket
            if ($counterId) {
                $stmtUpdateCounter = $db->prepare("
                    UPDATE counters 
                    SET current_queue_number = NULL, current_queue_id = NULL
                    WHERE id = :id
                ");
                $stmtUpdateCounter->execute(['id' => $counterId]);
            }

            $this->redirect('/queues?success=' . urlencode("Antrian dilewati."));
        } catch (Exception $e) {
            $this->redirect('/queues?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Halaman Publik Display Antrian
     */
    public function display(): void
    {
        $db = Container::get(PDO::class);

        $stmt = $db->query("SELECT * FROM counters ORDER BY name ASC");
        $counters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('queue/display', [
            'title'    => 'Display Monitor Antrian - Puskesmas Salem',
            'counters' => $counters
        ]);
    }

    /**
     * API data real-time untuk Display Antrian
     */
    public function displayData(): void
    {
        $db = Container::get(PDO::class);

        $stmt = $db->query("SELECT * FROM counters ORDER BY name ASC");
        $counters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtWaiting = $db->prepare("
            SELECT COUNT(*) FROM queues 
            WHERE tanggal = CURRENT_DATE AND status = 'Menunggu'
        ");
        $stmtWaiting->execute();
        $waitingCount = (int) $stmtWaiting->fetchColumn();

        $data = [
            'counters' => array_map(function($c) {
                return [
                    'id'                 => $c['id'],
                    'name'               => $c['name'],
                    'isActive'           => (bool) $c['is_active'],
                    'currentQueueNumber' => $c['current_queue_number'] ?: '-',
                    'currentQueueId'     => $c['current_queue_id']
                ];
            }, $counters),
            'waitingCount' => $waitingCount
        ];

        $this->json($data);
    }

    /**
     * Halaman Riwayat Antrian Hari Ini
     */
    public function history(): void
    {
        AuthMiddleware::requireAuth();
        $db = Container::get(PDO::class);

        $stmt = $db->prepare("
            SELECT q.*, p.nama as nama_pasien, pol.nama_poli, d.nama_dokter
            FROM queues q
            JOIN pasiens p ON q.no_rm = p.no_rm
            JOIN polis pol ON q.kd_poli = pol.kd_poli
            JOIN dokters d ON q.kd_dokter = d.kd_dokter
            WHERE q.tanggal = CURRENT_DATE
            ORDER BY q.jam_ambil DESC
        ");
        $stmt->execute();
        $queues = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('queue/history', [
            'title'  => 'Riwayat Antrian - SiLLA',
            'queues' => $queues
        ]);
    }
}
