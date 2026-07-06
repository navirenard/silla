<?php

namespace App\Application\UseCases\Queue;

use App\Domain\Repositories\QueueRepositoryInterface;
use App\Application\DTOs\QueueDTO;

class GetQueueList
{
    private QueueRepositoryInterface $queueRepository;

    public function __construct(QueueRepositoryInterface $queueRepository)
    {
        $this->queueRepository = $queueRepository;
    }

    /**
     * Mengambil daftar antrian hari ini, opsional difilter berdasarkan status.
     * @return QueueDTO[]
     */
    public function execute(?string $status = null): array
    {
        if ($status !== null) {
            $queues = $this->queueRepository->getTodayQueuesByStatus($status);
        } else {
            $queues = $this->queueRepository->getTodayQueues();
        }

        return array_map(function($queue) {
            return QueueDTO::fromEntity($queue);
        }, $queues);
    }
}
