<?php

namespace App\Application\UseCases\Queue;

use App\Domain\Repositories\QueueRepositoryInterface;
use App\Domain\Repositories\CounterRepositoryInterface;
use App\Application\DTOs\QueueDTO;
use Exception;

class CompleteQueue
{
    private QueueRepositoryInterface $queueRepository;
    private CounterRepositoryInterface $counterRepository;

    public function __construct(
        QueueRepositoryInterface $queueRepository,
        CounterRepositoryInterface $counterRepository
    ) {
        $this->queueRepository = $queueRepository;
        $this->counterRepository = $counterRepository;
    }

    /**
     * Menandai antrian tertentu selesai dilayani.
     */
    public function execute(string $queueId): QueueDTO
    {
        $queue = $this->queueRepository->findById($queueId);
        if (!$queue) {
            throw new Exception("Antrian tidak ditemukan.");
        }

        // 1. Update status antrian menjadi selesai
        $queue->complete();
        $this->queueRepository->update($queue);

        // 2. Cari loket yang sedang melayani antrian ini dan bersihkan statusnya
        $counters = $this->counterRepository->findAll();
        foreach ($counters as $counter) {
            if ($counter->getCurrentQueueId() === $queueId) {
                $counter->clearCurrentServing();
                $this->counterRepository->update($counter);
                break;
            }
        }

        return QueueDTO::fromEntity($queue);
    }
}
