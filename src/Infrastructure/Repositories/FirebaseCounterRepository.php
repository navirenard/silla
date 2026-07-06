<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Counter;
use App\Domain\Repositories\CounterRepositoryInterface;
use App\Infrastructure\Firebase\FirebaseDBClient;

class FirebaseCounterRepository implements CounterRepositoryInterface
{
    private FirebaseDBClient $dbClient;

    public function __construct(FirebaseDBClient $dbClient)
    {
        $this->dbClient = $dbClient;
    }

    public function findAll(): array
    {
        $response = $this->dbClient->get("counters");
        
        $counters = [];
        if ($response['success'] && !empty($response['data'])) {
            foreach ($response['data'] as $id => $data) {
                $data['id'] = $id;
                $counters[] = Counter::fromArray($data);
            }

            // Urutkan loket berdasarkan nama loket
            usort($counters, function(Counter $a, Counter $b) {
                return strnatcasecmp($a->getName(), $b->getName());
            });
        }

        return $counters;
    }

    public function findById(string $id): ?Counter
    {
        $response = $this->dbClient->get("counters/{$id}");

        if ($response['success'] && !empty($response['data'])) {
            $data = $response['data'];
            $data['id'] = $id;
            return Counter::fromArray($data);
        }

        return null;
    }

    public function findByName(string $name): ?Counter
    {
        $all = $this->findAll();
        foreach ($all as $counter) {
            if (strtolower($counter->getName()) === strtolower($name)) {
                return $counter;
            }
        }
        return null;
    }

    public function save(Counter $counter): bool
    {
        if ($counter->getId()) {
            $response = $this->dbClient->set("counters/{$counter->getId()}", $counter->toArray());
            return $response['success'];
        } else {
            $response = $this->dbClient->push("counters", $counter->toArray());
            if ($response['success'] && isset($response['data']['name'])) {
                $generatedId = $response['data']['name'];
                $counter->setId($generatedId);
                
                $this->dbClient->update("counters/{$generatedId}", ['id' => $generatedId]);
                return true;
            }
        }

        return false;
    }

    public function update(Counter $counter): bool
    {
        return $this->save($counter);
    }

    public function delete(string $id): bool
    {
        $response = $this->dbClient->delete("counters/{$id}");
        return $response['success'];
    }
}
