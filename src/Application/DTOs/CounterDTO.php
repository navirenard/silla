<?php

namespace App\Application\DTOs;

class CounterDTO
{
    public ?string $id;
    public string $name;
    public bool $isActive;
    public ?string $currentQueueNumber;
    public ?string $currentQueueId;
    public ?string $assignedOfficerUid;

    public function __construct(
        ?string $id,
        string $name,
        bool $isActive,
        ?string $currentQueueNumber,
        ?string $currentQueueId,
        ?string $assignedOfficerUid
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->isActive = $isActive;
        $this->currentQueueNumber = $currentQueueNumber;
        $this->currentQueueId = $currentQueueId;
        $this->assignedOfficerUid = $assignedOfficerUid;
    }

    public static function fromEntity($counter): self
    {
        return new self(
            $counter->getId(),
            $counter->getName(),
            $counter->isActive(),
            $counter->getCurrentQueueNumber(),
            $counter->getCurrentQueueId(),
            $counter->getAssignedOfficerUid()
        );
    }
}
