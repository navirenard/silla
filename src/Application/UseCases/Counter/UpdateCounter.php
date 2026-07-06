<?php

namespace App\Application\UseCases\Counter;

use App\Domain\Repositories\CounterRepositoryInterface;
use App\Application\DTOs\CounterDTO;
use Exception;

class UpdateCounter
{
    private CounterRepositoryInterface $counterRepository;

    public function __construct(CounterRepositoryInterface $counterRepository)
    {
        $this->counterRepository = $counterRepository;
    }

    /**
     * Memperbarui detail loket.
     */
    public function execute(string $id, string $name, bool $isActive, ?string $officerUid = null): CounterDTO
    {
        $counter = $this->counterRepository->findById($id);
        if (!$counter) {
            throw new Exception("Loket tidak ditemukan.");
        }

        $name = trim($name);
        if (empty($name)) {
            throw new Exception("Nama loket tidak boleh kosong.");
        }

        // Cek jika ganti nama, apakah nama baru tabrakan dengan loket lain
        if (strtolower($counter->getName()) !== strtolower($name)) {
            $existing = $this->counterRepository->findByName($name);
            if ($existing) {
                throw new Exception("Loket dengan nama '{$name}' sudah terdaftar.");
            }
        }

        $counter->setName($name);
        $counter->setIsActive($isActive);
        $counter->assignOfficer($officerUid);

        $updated = $this->counterRepository->update($counter);
        if (!$updated) {
            throw new Exception("Gagal memperbarui loket di database.");
        }

        return CounterDTO::fromEntity($counter);
    }
}
