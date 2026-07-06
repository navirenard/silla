<?php

namespace App\Application\UseCases\Queue;

use App\Domain\Entities\Queue;
use App\Domain\Repositories\QueueRepositoryInterface;
use App\Domain\Services\QueueNumberGenerator;
use App\Application\DTOs\QueueDTO;
use Exception;

class CreateQueue
{
    private QueueRepositoryInterface $queueRepository;
    private string $prefix;

    public function __construct(QueueRepositoryInterface $queueRepository, string $prefix = 'A')
    {
        $this->queueRepository = $queueRepository;
        $this->prefix = $prefix;
    }

    /**
     * Membuat nomor antrian baru hari ini.
     */
    public function execute(): QueueDTO
    {
        // 1. Ambil nomor antrian terakhir hari ini
        $lastQueueNumber = $this->queueRepository->getLastQueueNumberToday();

        // 2. Generate nomor antrian berikutnya
        $generator = new QueueNumberGenerator($this->prefix);
        $nextQueueNumber = $generator->generateNext($lastQueueNumber);

        // 3. Buat entitas antrian baru
        $queue = new Queue(
            null, // ID digenerate otomatis oleh Firebase
            $nextQueueNumber,
            null, // Belum dipanggil ke loket mana pun
            Queue::STATUS_WAITING
        );

        // 4. Simpan ke database
        $saved = $this->queueRepository->save($queue);

        if (!$saved) {
            throw new Exception("Gagal menyimpan antrian baru ke database.");
        }

        return QueueDTO::fromEntity($queue);
    }
}
