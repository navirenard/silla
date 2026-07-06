<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Queue;

interface QueueRepositoryInterface
{
    /**
     * Cari antrian berdasarkan ID
     */
    public function findById(string $id): ?Queue;

    /**
     * Dapatkan antrian aktif hari ini
     * @return Queue[]
     */
    public function getTodayQueues(): array;

    /**
     * Dapatkan antrian dengan status tertentu hari ini
     * @return Queue[]
     */
    public function getTodayQueuesByStatus(string $status): array;

    /**
     * Dapatkan antrian berikutnya yang sedang menunggu (waiting) hari ini
     */
    public function getNextWaitingQueue(): ?Queue;

    /**
     * Simpan/buat antrian baru
     */
    public function save(Queue $queue): bool;

    /**
     * Update antrian yang sudah ada
     */
    public function update(Queue $queue): bool;

    /**
     * Dapatkan nomor antrian terakhir untuk hari ini guna keperluan generator nomor
     */
    public function getLastQueueNumberToday(): ?string;
}
