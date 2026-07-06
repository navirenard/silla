<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Counter;

interface CounterRepositoryInterface
{
    /**
     * Dapatkan semua loket
     * @return Counter[]
     */
    public function findAll(): array;

    /**
     * Cari loket berdasarkan ID
     */
    public function findById(string $id): ?Counter;

    /**
     * Cari loket berdasarkan nama
     */
    public function findByName(string $name): ?Counter;

    /**
     * Simpan loket baru
     */
    public function save(Counter $counter): bool;

    /**
     * Update loket
     */
    public function update(Counter $counter): bool;

    /**
     * Hapus loket berdasarkan ID
     */
    public function delete(string $id): bool;
}
