<?php

namespace App\Presentation\Controllers;

use Container;
use App\Application\UseCases\Queue\GetQueueList;
use App\Presentation\Middleware\AuthMiddleware;

class DashboardController extends Controller
{
    /**
     * Halaman Dashboard Utama Petugas/Admin
     */
    public function index(): void
    {
        AuthMiddleware::requireAuth();

        /** @var GetQueueList $getQueuesUseCase */
        $getQueuesUseCase = Container::get(GetQueueList::class);
        $queues = $getQueuesUseCase->execute();

        // Hitung statistik antrian hari ini
        $stats = [
            'total' => count($queues),
            'waiting' => 0,
            'serving' => 0,
            'completed' => 0,
            'skipped' => 0,
            'avg_wait' => 0,
            'avg_service' => 0
        ];

        $waitTimes = [];
        $serviceTimes = [];

        foreach ($queues as $q) {
            switch ($q->status) {
                case 'waiting':
                    $stats['waiting']++;
                    break;
                case 'serving':
                    $stats['serving']++;
                    if ($q->waitTimeMinutes !== null) {
                        $waitTimes[] = $q->waitTimeMinutes;
                    }
                    break;
                case 'completed':
                    $stats['completed']++;
                    if ($q->waitTimeMinutes !== null) {
                        $waitTimes[] = $q->waitTimeMinutes;
                    }
                    if ($q->serviceTimeMinutes !== null) {
                        $serviceTimes[] = $q->serviceTimeMinutes;
                    }
                    break;
                case 'skipped':
                    $stats['skipped']++;
                    if ($q->waitTimeMinutes !== null) {
                        $waitTimes[] = $q->waitTimeMinutes;
                    }
                    break;
            }
        }

        // Rata-rata waktu tunggu (menit)
        if (!empty($waitTimes)) {
            $stats['avg_wait'] = (int) round(array_sum($waitTimes) / count($waitTimes));
        }

        // Rata-rata waktu pelayanan (menit)
        if (!empty($serviceTimes)) {
            $stats['avg_service'] = (int) round(array_sum($serviceTimes) / count($serviceTimes));
        }

        // Hitung loket aktif
        $activeCountersCount = 0;
        try {
            /** @var \App\Application\UseCases\Counter\GetCounters $getCountersUseCase */
            $getCountersUseCase = Container::get(\App\Application\UseCases\Counter\GetCounters::class);
            $counters = $getCountersUseCase->execute();
            foreach ($counters as $c) {
                if ($c->isActive) {
                    $activeCountersCount++;
                }
            }
        } catch (\Throwable $e) {
            $activeCountersCount = 0;
        }

        $success = $_SESSION['auth_success'] ?? null;
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['auth_success'], $_SESSION['error']);

        $this->render('dashboard/index', [
            'title' => 'Dashboard - SiLLA',
            'stats' => $stats,
            'activeCountersCount' => $activeCountersCount,
            'queues' => array_slice($queues, -5), // 5 antrian terakhir hari ini
            'success' => $success,
            'error' => $error
        ]);
    }
}
