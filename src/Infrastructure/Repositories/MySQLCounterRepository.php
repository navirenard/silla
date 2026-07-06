<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Counter;
use App\Domain\Repositories\CounterRepositoryInterface;
use PDO;

class MySQLCounterRepository implements CounterRepositoryInterface
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function mapRowToEntity(array $row): Counter
    {
        return new Counter(
            $row['id'],
            $row['name'],
            (bool) $row['is_active'],
            $row['current_queue_number'],
            $row['current_queue_id'],
            $row['assigned_officer_uid']
        );
    }

    public function findAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM counters ORDER BY name ASC");
        $stmt->execute();

        $counters = [];
        while ($row = $stmt->fetch()) {
            $counters[] = $this->mapRowToEntity($row);
        }

        return $counters;
    }

    public function findById(string $id): ?Counter
    {
        $stmt = $this->db->prepare("SELECT * FROM counters WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToEntity($row) : null;
    }

    public function findByName(string $name): ?Counter
    {
        $stmt = $this->db->prepare("SELECT * FROM counters WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch();

        return $row ? $this->mapRowToEntity($row) : null;
    }

    public function save(Counter $counter): bool
    {
        if (!$counter->getId()) {
            $generatedId = uniqid('c_', true);
            $counter->setId($generatedId);
        }

        $driver = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            $stmt = $this->db->prepare("
                INSERT INTO counters (id, name, is_active, current_queue_number, current_queue_id, assigned_officer_uid)
                VALUES (:id, :name, :is_active, :current_queue_number, :current_queue_id, :assigned_officer_uid)
                ON CONFLICT (id) DO UPDATE SET
                    name = EXCLUDED.name,
                    is_active = EXCLUDED.is_active,
                    current_queue_number = EXCLUDED.current_queue_number,
                    current_queue_id = EXCLUDED.current_queue_id,
                    assigned_officer_uid = EXCLUDED.assigned_officer_uid
            ");
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO counters (id, name, is_active, current_queue_number, current_queue_id, assigned_officer_uid)
                VALUES (:id, :name, :is_active, :current_queue_number, :current_queue_id, :assigned_officer_uid)
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    is_active = VALUES(is_active),
                    current_queue_number = VALUES(current_queue_number),
                    current_queue_id = VALUES(current_queue_id),
                    assigned_officer_uid = VALUES(assigned_officer_uid)
            ");
        }

        return $stmt->execute([
            'id' => $counter->getId(),
            'name' => $counter->getName(),
            'is_active' => $counter->isActive() ? 1 : 0,
            'current_queue_number' => $counter->getCurrentQueueNumber(),
            'current_queue_id' => $counter->getCurrentQueueId(),
            'assigned_officer_uid' => $counter->getAssignedOfficerUid()
        ]);
    }

    public function update(Counter $counter): bool
    {
        return $this->save($counter);
    }

    public function delete(string $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM counters WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
