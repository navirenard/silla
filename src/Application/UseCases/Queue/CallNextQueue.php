<?php

namespace App\Application\UseCases\Queue;

use App\Domain\Entities\Queue;
use App\Domain\Repositories\QueueRepositoryInterface;
use App\Domain\Repositories\CounterRepositoryInterface;
use App\Application\DTOs\QueueDTO;
use Exception;

class CallNextQueue
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
     * Memanggil antrian berikutnya untuk dilayani di loket tertentu.
     */
    public function execute(string $counterId, string $officerUid): ?QueueDTO
    {
        // 1. Dapatkan info loket
        $counter = $this->counterRepository->findById($counterId);
        if (!$counter) {
            throw new Exception("Loket tidak ditemukan.");
        }

        if (!$counter->isActive()) {
            throw new Exception("Loket ini sedang tidak aktif.");
        }

        // 2. Jika loket sedang melayani antrian lain, selesaikan antrian itu dulu
        if ($counter->getCurrentQueueId()) {
            $currentQueue = $this->queueRepository->findById($counter->getCurrentQueueId());
            if ($currentQueue && $currentQueue->isServing()) {
                $currentQueue->complete();
                $this->queueRepository->update($currentQueue);
            }
            $counter->clearCurrentServing();
        }

        // 3. Dapatkan antrian berikutnya yang sedang menunggu
        $nextQueue = $this->queueRepository->getNextWaitingQueue();
        
        if (!$nextQueue) {
            // Update loket agar tidak melayani apa-apa
            $counter->clearCurrentServing();
            $this->counterRepository->update($counter);
            return null;
        }

        // 4. Update status antrian menjadi "serving" dan pasangkan ke loket
        $nextQueue->callToCounter($counter->getName(), $officerUid);
        $this->queueRepository->update($nextQueue);

        // 5. Update status loket dengan antrian saat ini
        $counter->setCurrentServing($nextQueue->getId(), $nextQueue->getQueueNumber());
        $this->counterRepository->update($counter);

        return QueueDTO::fromEntity($nextQueue);
    }
}
