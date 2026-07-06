<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Queue;
use App\Domain\Repositories\QueueRepositoryInterface;
use PDO;

class MySQLQueueRepository implements QueueRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function mapRowToEntity(array $row): Queue
    {
        return new Queue(
            $row['id'],
            $row['queue_number'],
            $row['counter_name'],
            $row['status'],
            $row['created_at'],
            $row['called_at'],
            $row['completed_at'],
            $row['served_by']
        );
    }

    public function findById(string $id): ?Queue
    {
        $stmt = $this->db->prepare("SELECT * FROM queues WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToEntity($row) : null;
    }

    public function getTodayQueues(): array
    {
        // CAST(created_at AS DATE) = CURRENT_DATE kompatibel dengan MySQL & PostgreSQL
        $stmt = $this->db->prepare("
            SELECT * FROM queues 
            WHERE CAST(created_at AS DATE) = CURRENT_DATE 
            ORDER BY created_at ASC
        ");
        $stmt->execute();
        
        $queues = [];
        while ($row = $stmt->fetch()) {
            $queues[] = $this->mapRowToEntity($row);
        }

        return $queues;
    }

    public function getTodayQueuesByStatus(string $status): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM queues 
            WHERE CAST(created_at AS DATE) = CURRENT_DATE AND status = :status 
            ORDER BY created_at ASC
        ");
        $stmt->execute(['status' => $status]);

        $queues = [];
        while ($row = $stmt->fetch()) {
            $queues[] = $this->mapRowToEntity($row);
        }

        return $queues;
    }

    public function getNextWaitingQueue(): ?Queue
    {
        $stmt = $this->db->prepare("
            SELECT * FROM queues 
            WHERE CAST(created_at AS DATE) = CURRENT_DATE AND status = :status 
            ORDER BY created_at ASC 
            LIMIT 1
        ");
        $stmt->execute(['status' => Queue::STATUS_WAITING]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToEntity($row) : null;
    }

    public function save(Queue $queue): bool
    {
        if (!$queue->getId()) {
            $generatedId = uniqid('q_', true);
            $queue->setId($generatedId);
        }

        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            $stmt = $this->db->prepare("
                INSERT INTO queues (id, queue_number, counter_name, status, created_at, called_at, completed_at, served_by)
                VALUES (:id, :queue_number, :counter_name, :status, :created_at, :called_at, :completed_at, :served_by)
                ON CONFLICT (id) DO UPDATE SET
                    counter_name = EXCLUDED.counter_name,
                    status = EXCLUDED.status,
                    called_at = EXCLUDED.called_at,
                    completed_at = EXCLUDED.completed_at,
                    served_by = EXCLUDED.served_by
            ");
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO queues (id, queue_number, counter_name, status, created_at, called_at, completed_at, served_by)
                VALUES (:id, :queue_number, :counter_name, :status, :created_at, :called_at, :completed_at, :served_by)
                ON DUPLICATE KEY UPDATE
                    counter_name = VALUES(counter_name),
                    status = VALUES(status),
                    called_at = VALUES(called_at),
                    completed_at = VALUES(completed_at),
                    served_by = VALUES(served_by)
            ");
        }

        return $stmt->execute([
            'id'           => $queue->getId(),
            'queue_number' => $queue->getQueueNumber(),
            'counter_name' => $queue->getCounterName(),
            'status'       => $queue->getStatus(),
            'created_at'   => $queue->getCreatedAt(),
            'called_at'    => $queue->getCalledAt(),
            'completed_at' => $queue->getCompletedAt(),
            'served_by'    => $queue->getServedBy()
        ]);
    }

    public function update(Queue $queue): bool
    {
        return $this->save($queue);
    }

    public function getLastQueueNumberToday(): ?string
    {
        $stmt = $this->db->prepare("
            SELECT queue_number FROM queues 
            WHERE CAST(created_at AS DATE) = CURRENT_DATE 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ? $row['queue_number'] : null;
    }
}
