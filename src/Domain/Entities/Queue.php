<?php

/**
 * Domain Entity: Queue (Antrian)
 * 
 * Representasi satu nomor antrian dalam sistem.
 */

namespace App\Domain\Entities;

class Queue
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_SERVING = 'serving';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_SKIPPED = 'skipped';

    private ?string $id;
    private string $queueNumber;
    private ?string $counterName;
    private string $status;
    private string $createdAt;
    private ?string $calledAt;
    private ?string $completedAt;
    private ?string $servedBy;

    public function __construct(
        ?string $id,
        string $queueNumber,
        ?string $counterName = null,
        string $status = self::STATUS_WAITING,
        ?string $createdAt = null,
        ?string $calledAt = null,
        ?string $completedAt = null,
        ?string $servedBy = null
    ) {
        $this->id = $id;
        $this->queueNumber = $queueNumber;
        $this->counterName = $counterName;
        $this->status = $status;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->calledAt = $calledAt;
        $this->completedAt = $completedAt;
        $this->servedBy = $servedBy;
    }

    // Getters
    public function getId(): ?string { return $this->id; }
    public function getQueueNumber(): string { return $this->queueNumber; }
    public function getCounterName(): ?string { return $this->counterName; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getCalledAt(): ?string { return $this->calledAt; }
    public function getCompletedAt(): ?string { return $this->completedAt; }
    public function getServedBy(): ?string { return $this->servedBy; }

    // Setters
    public function setId(string $id): void { $this->id = $id; }

    /**
     * Panggil antrian ini ke loket tertentu
     */
    public function callToCounter(string $counterName, string $servedBy): void
    {
        $this->status = self::STATUS_SERVING;
        $this->counterName = $counterName;
        $this->calledAt = date('Y-m-d H:i:s');
        $this->servedBy = $servedBy;
    }

    /**
     * Tandai antrian selesai
     */
    public function complete(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completedAt = date('Y-m-d H:i:s');
    }

    /**
     * Skip antrian ini
     */
    public function skip(): void
    {
        $this->status = self::STATUS_SKIPPED;
        $this->completedAt = date('Y-m-d H:i:s');
    }

    public function isWaiting(): bool { return $this->status === self::STATUS_WAITING; }
    public function isServing(): bool { return $this->status === self::STATUS_SERVING; }
    public function isCompleted(): bool { return $this->status === self::STATUS_COMPLETED; }
    public function isSkipped(): bool { return $this->status === self::STATUS_SKIPPED; }

    /**
     * Hitung waktu tunggu dalam menit
     */
    public function getWaitTimeMinutes(): ?int
    {
        if (!$this->calledAt) return null;
        $created = strtotime($this->createdAt);
        $called = strtotime($this->calledAt);
        return (int) round(($called - $created) / 60);
    }

    /**
     * Hitung waktu layanan dalam menit
     */
    public function getServiceTimeMinutes(): ?int
    {
        if (!$this->calledAt || !$this->completedAt) return null;
        $called = strtotime($this->calledAt);
        $completed = strtotime($this->completedAt);
        return (int) round(($completed - $called) / 60);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'queueNumber' => $this->queueNumber,
            'counterName' => $this->counterName,
            'status' => $this->status,
            'createdAt' => $this->createdAt,
            'calledAt' => $this->calledAt,
            'completedAt' => $this->completedAt,
            'servedBy' => $this->servedBy,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['queueNumber'] ?? '',
            $data['counterName'] ?? null,
            $data['status'] ?? self::STATUS_WAITING,
            $data['createdAt'] ?? null,
            $data['calledAt'] ?? null,
            $data['completedAt'] ?? null,
            $data['servedBy'] ?? null
        );
    }
}
