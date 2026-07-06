<?php

namespace App\Application\DTOs;

class QueueDTO
{
    public ?string $id;
    public string $queueNumber;
    public ?string $counterName;
    public string $status;
    public string $createdAt;
    public ?string $calledAt;
    public ?string $completedAt;
    public ?string $servedBy;
    public ?int $waitTimeMinutes;
    public ?int $serviceTimeMinutes;

    public function __construct(
        ?string $id,
        string $queueNumber,
        ?string $counterName,
        string $status,
        string $createdAt,
        ?string $calledAt,
        ?string $completedAt,
        ?string $servedBy,
        ?int $waitTimeMinutes = null,
        ?int $serviceTimeMinutes = null
    ) {
        $this->id = $id;
        $this->queueNumber = $queueNumber;
        $this->counterName = $counterName;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->calledAt = $calledAt;
        $this->completedAt = $completedAt;
        $this->servedBy = $servedBy;
        $this->waitTimeMinutes = $waitTimeMinutes;
        $this->serviceTimeMinutes = $serviceTimeMinutes;
    }

    public static function fromEntity($queue): self
    {
        return new self(
            $queue->getId(),
            $queue->getQueueNumber(),
            $queue->getCounterName(),
            $queue->getStatus(),
            $queue->getCreatedAt(),
            $queue->getCalledAt(),
            $queue->getCompletedAt(),
            $queue->getServedBy(),
            $queue->getWaitTimeMinutes(),
            $queue->getServiceTimeMinutes()
        );
    }
}
