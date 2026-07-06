<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Queue;
use App\Domain\Repositories\QueueRepositoryInterface;
use App\Infrastructure\Firebase\FirebaseDBClient;

class FirebaseQueueRepository implements QueueRepositoryInterface
{
    private FirebaseDBClient $dbClient;

    public function __construct(FirebaseDBClient $dbClient)
    {
        $this->dbClient = $dbClient;
    }

    private function getTodayPath(): string
    {
        $today = date('Y-m-d');
        return "queues/{$today}";
    }

    public function findById(string $id): ?Queue
    {
        $todayPath = $this->getTodayPath();
        $response = $this->dbClient->get("{$todayPath}/{$id}");

        if ($response['success'] && !empty($response['data'])) {
            return Queue::fromArray($response['data']);
        }

        return null;
    }

    public function getTodayQueues(): array
    {
        $todayPath = $this->getTodayPath();
        $response = $this->dbClient->get($todayPath);

        $queues = [];
        if ($response['success'] && !empty($response['data'])) {
            foreach ($response['data'] as $id => $data) {
                // Pastikan ID ada di dalam data
                $data['id'] = $id;
                $queues[] = Queue::fromArray($data);
            }

            // Urutkan berdasarkan waktu dibuat (ascending)
            usort($queues, function(Queue $a, Queue $b) {
                return strcmp($a->getCreatedAt(), $b->getCreatedAt());
            });
        }

        return $queues;
    }

    public function getTodayQueuesByStatus(string $status): array
    {
        $all = $this->getTodayQueues();
        return array_values(array_filter($all, function(Queue $q) use ($status) {
            return $q->getStatus() === $status;
        }));
    }

    public function getNextWaitingQueue(): ?Queue
    {
        $waiting = $this->getTodayQueuesByStatus(Queue::STATUS_WAITING);
        return !empty($waiting) ? $waiting[0] : null;
    }

    public function save(Queue $queue): bool
    {
        $todayPath = $this->getTodayPath();
        
        if ($queue->getId()) {
            $response = $this->dbClient->set("{$todayPath}/{$queue->getId()}", $queue->toArray());
            return $response['success'];
        } else {
            // Push ke Firebase untuk generate key otomatis
            $response = $this->dbClient->push($todayPath, $queue->toArray());
            if ($response['success'] && isset($response['data']['name'])) {
                $generatedId = $response['data']['name'];
                $queue->setId($generatedId);
                
                // Update kembali data dengan menyertakan ID
                $this->dbClient->update("{$todayPath}/{$generatedId}", ['id' => $generatedId]);
                return true;
            }
        }

        return false;
    }

    public function update(Queue $queue): bool
    {
        if (!$queue->getId()) {
            return false;
        }

        $todayPath = $this->getTodayPath();
        $response = $this->dbClient->set("{$todayPath}/{$queue->getId()}", $queue->toArray());
        return $response['success'];
    }

    public function getLastQueueNumberToday(): ?string
    {
        $todayQueues = $this->getTodayQueues();
        if (empty($todayQueues)) {
            return null;
        }

        // Cari nomor antrian terbesar/terakhir
        $lastQueue = end($todayQueues);
        return $lastQueue->getQueueNumber();
    }
}
