<?php

namespace App\Presentation\Controllers;

use Container;
use App\Application\UseCases\Queue\CreateQueue;
use App\Application\UseCases\Queue\CallNextQueue;
use App\Application\UseCases\Queue\CompleteQueue;
use App\Application\UseCases\Queue\SkipQueue;
use App\Application\UseCases\Queue\GetQueueList;
use App\Application\UseCases\Counter\GetCounters;
use App\Presentation\Middleware\AuthMiddleware;
use Exception;

class QueueController extends Controller
{
    /**
     * Halaman Control Panel Antrian Petugas
     */
    public function index(): void
    {
        AuthMiddleware::requireAuth();

        /** @var GetCounters $getCountersUseCase */
        $getCountersUseCase = Container::get(GetCounters::class);
        $counters = $getCountersUseCase->execute();

        /** @var GetQueueList $getQueuesUseCase */
        $getQueuesUseCase = Container::get(GetQueueList::class);
        $waitingQueues = $getQueuesUseCase->execute('waiting');

        // Dapatkan loket aktif petugas (dari session)
        $activeCounterId = $_SESSION['active_counter_id'] ?? null;
        $activeCounter = null;
        $currentQueue = null;

        if ($activeCounterId) {
            foreach ($counters as $c) {
                if ($c->id === $activeCounterId) {
                    $activeCounter = $c;
                    break;
                }
            }

            // Dapatkan antrian yang sedang aktif dilayani di loket ini
            if ($activeCounter && $activeCounter->currentQueueId) {
                $todayQueues = $getQueuesUseCase->execute();
                foreach ($todayQueues as $q) {
                    if ($q->id === $activeCounter->currentQueueId) {
                        $currentQueue = $q;
                        break;
                    }
                }
            }
        }

        $this->render('queue/index', [
            'title' => 'Operator Antrian - SiLLA',
            'counters' => $counters,
            'waitingQueues' => $waitingQueues,
            'activeCounter' => $activeCounter,
            'currentQueue' => $currentQueue,
            'success' => $_SESSION['queue_success'] ?? null,
            'error' => $_SESSION['queue_error'] ?? null
        ]);

        unset($_SESSION['queue_success'], $_SESSION['queue_error']);
    }

    /**
     * Set loket aktif bagi petugas yang sedang bertugas
     */
    public function selectCounter(): void
    {
        AuthMiddleware::requireAuth();
        $counterId = $_POST['counter_id'] ?? null;

        if ($counterId) {
            $_SESSION['active_counter_id'] = $counterId;
            $_SESSION['queue_success'] = "Loket berhasil diaktifkan.";
        } else {
            unset($_SESSION['active_counter_id']);
            $_SESSION['queue_success'] = "Loket dinonaktifkan.";
        }

        $this->redirect('/queues');
    }

    /**
     * Ambil Nomor Antrian Baru (Kiosk)
     * Dapat dipanggil publik (tanpa login) maupun oleh petugas
     */
    public function create(): void
    {
        try {
            /** @var CreateQueue $createQueueUseCase */
            $createQueueUseCase = Container::get(CreateQueue::class);
            $queue = $createQueueUseCase->execute();

            if (isset($_GET['ajax'])) {
                $this->json(['success' => true, 'queue' => $queue]);
            }

            $_SESSION['kiosk_success'] = $queue->queueNumber;
            $this->redirect('/kiosk');
        } catch (Exception $e) {
            if (isset($_GET['ajax'])) {
                $this->json(['success' => false, 'error' => $e->getMessage()], 400);
            }
            $_SESSION['kiosk_error'] = $e->getMessage();
            $this->redirect('/kiosk');
        }
    }

    /**
     * Panggil Antrian Berikutnya (Petugas)
     */
    public function callNext(): void
    {
        AuthMiddleware::requireAuth();
        $counterId = $_SESSION['active_counter_id'] ?? null;
        $officerUid = $_SESSION['user_uid'];

        if (!$counterId) {
            if (isset($_GET['ajax'])) {
                $this->json(['success' => false, 'error' => 'Pilih loket terlebih dahulu.'], 400);
            }
            $_SESSION['queue_error'] = "Pilih loket terlebih dahulu.";
            $this->redirect('/queues');
        }

        try {
            /** @var CallNextQueue $callNextUseCase */
            $callNextUseCase = Container::get(CallNextQueue::class);
            $queue = $callNextUseCase->execute($counterId, $officerUid);

            if (isset($_GET['ajax'])) {
                $this->json([
                    'success' => true, 
                    'called' => $queue !== null,
                    'queue' => $queue
                ]);
            }

            if ($queue) {
                $_SESSION['queue_success'] = "Memanggil antrian {$queue->queueNumber}.";
            } else {
                $_SESSION['queue_success'] = "Semua antrian telah selesai dilayani.";
            }

            $this->redirect('/queues');

        } catch (Exception $e) {
            if (isset($_GET['ajax'])) {
                $this->json(['success' => false, 'error' => $e->getMessage()], 400);
            }
            $_SESSION['queue_error'] = $e->getMessage();
            $this->redirect('/queues');
        }
    }

    /**
     * Tandai Antrian Selesai
     */
    public function complete(): void
    {
        AuthMiddleware::requireAuth();
        $queueId = $_POST['queue_id'] ?? null;

        if (!$queueId) {
            $this->redirect('/queues');
        }

        try {
            /** @var CompleteQueue $completeUseCase */
            $completeUseCase = Container::get(CompleteQueue::class);
            $completeUseCase->execute($queueId);

            if (isset($_GET['ajax'])) {
                $this->json(['success' => true]);
            }

            $_SESSION['queue_success'] = "Antrian telah ditandai selesai.";
            $this->redirect('/queues');
        } catch (Exception $e) {
            if (isset($_GET['ajax'])) {
                $this->json(['success' => false, 'error' => $e->getMessage()], 400);
            }
            $_SESSION['queue_error'] = $e->getMessage();
            $this->redirect('/queues');
        }
    }

    /**
     * Lewati Antrian
     */
    public function skip(): void
    {
        AuthMiddleware::requireAuth();
        $queueId = $_POST['queue_id'] ?? null;

        if (!$queueId) {
            $this->redirect('/queues');
        }

        try {
            /** @var SkipQueue $skipUseCase */
            $skipUseCase = Container::get(SkipQueue::class);
            $skipUseCase->execute($queueId);

            if (isset($_GET['ajax'])) {
                $this->json(['success' => true]);
            }

            $_SESSION['queue_success'] = "Antrian dilewati.";
            $this->redirect('/queues');
        } catch (Exception $e) {
            if (isset($_GET['ajax'])) {
                $this->json(['success' => false, 'error' => $e->getMessage()], 400);
            }
            $_SESSION['queue_error'] = $e->getMessage();
            $this->redirect('/queues');
        }
    }

    /**
     * Halaman Publik Display Antrian
     */
    public function display(): void
    {
        /** @var GetCounters $getCountersUseCase */
        $getCountersUseCase = Container::get(GetCounters::class);
        $counters = $getCountersUseCase->execute();

        $this->render('queue/display', [
            'title' => 'Display Monitor Antrian - SiLLA',
            'counters' => $counters
        ]);
    }

    /**
     * API data real-time untuk Display Antrian
     */
    public function displayData(): void
    {
        /** @var GetCounters $getCountersUseCase */
        $getCountersUseCase = Container::get(GetCounters::class);
        $counters = $getCountersUseCase->execute();

        /** @var GetQueueList $getQueuesUseCase */
        $getQueuesUseCase = Container::get(GetQueueList::class);
        $waitingQueues = $getQueuesUseCase->execute('waiting');

        // Formulasi data ringkas untuk polling AJAX
        $data = [
            'counters' => array_map(function($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'isActive' => $c->isActive,
                    'currentQueueNumber' => $c->currentQueueNumber ?: '-',
                    'currentQueueId' => $c->currentQueueId
                ];
            }, $counters),
            'waitingCount' => count($waitingQueues)
        ];

        $this->json($data);
    }

    /**
     * Halaman Riwayat Antrian Hari Ini
     */
    public function history(): void
    {
        AuthMiddleware::requireAuth();

        /** @var GetQueueList $getQueuesUseCase */
        $getQueuesUseCase = Container::get(GetQueueList::class);
        $queues = $getQueuesUseCase->execute();

        // Urutkan riwayat berdasarkan tanggal buat (descending/terbaru di atas)
        usort($queues, function($a, $b) {
            return strcmp($b->createdAt, $a->createdAt);
        });

        $this->render('queue/history', [
            'title' => 'Riwayat Antrian - SiLLA',
            'queues' => $queues
        ]);
    }

    /**
     * Halaman Ambil Tiket Antrian (Kiosk Mandiri)
     */
    public function kiosk(): void
    {
        $successNumber = $_SESSION['kiosk_success'] ?? null;
        $error = $_SESSION['kiosk_error'] ?? null;
        unset($_SESSION['kiosk_success'], $_SESSION['kiosk_error']);

        $this->render('queue/kiosk', [
            'title' => 'Mesin Tiket Antrian - SiLLA',
            'successNumber' => $successNumber,
            'error' => $error
        ]);
    }
}
