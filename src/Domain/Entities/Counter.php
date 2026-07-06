<?php

/**
 * Domain Entity: Counter (Loket)
 * 
 * Representasi loket/tempat pelayanan antrian.
 */

namespace App\Domain\Entities;

class Counter
{
    private ?string $id;
    private string $name;
    private bool $isActive;
    private ?string $currentQueueNumber;
    private ?string $currentQueueId;
    private ?string $assignedOfficerUid;

    public function __construct(
        ?string $id,
        string $name,
        bool $isActive = true,
        ?string $currentQueueNumber = null,
        ?string $currentQueueId = null,
        ?string $assignedOfficerUid = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->isActive = $isActive;
        $this->currentQueueNumber = $currentQueueNumber;
        $this->currentQueueId = $currentQueueId;
        $this->assignedOfficerUid = $assignedOfficerUid;
    }

    // Getters
    public function getId(): ?string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function isActive(): bool { return $this->isActive; }
    public function getCurrentQueueNumber(): ?string { return $this->currentQueueNumber; }
    public function getCurrentQueueId(): ?string { return $this->currentQueueId; }
    public function getAssignedOfficerUid(): ?string { return $this->assignedOfficerUid; }

    // Setters
    public function setId(string $id): void { $this->id = $id; }
    public function setName(string $name): void { $this->name = $name; }
    public function setIsActive(bool $isActive): void { $this->isActive = $isActive; }
    
    /**
     * Assign officer to this counter
     */
    public function assignOfficer(?string $officerUid): void
    {
        $this->assignedOfficerUid = $officerUid;
    }

    /**
     * Update current serving queue
     */
    public function setCurrentServing(?string $queueId, ?string $queueNumber): void
    {
        $this->currentQueueId = $queueId;
        $this->currentQueueNumber = $queueNumber;
    }

    /**
     * Clear current serving queue when finished or skipped
     */
    public function clearCurrentServing(): void
    {
        $this->currentQueueId = null;
        $this->currentQueueNumber = null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'isActive' => $this->isActive,
            'currentQueueNumber' => $this->currentQueueNumber,
            'currentQueueId' => $this->currentQueueId,
            'assignedOfficerUid' => $this->assignedOfficerUid,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['name'] ?? '',
            $data['isActive'] ?? true,
            $data['currentQueueNumber'] ?? null,
            $data['currentQueueId'] ?? null,
            $data['assignedOfficerUid'] ?? null
        );
    }
}
