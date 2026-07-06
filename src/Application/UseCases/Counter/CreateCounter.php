<?php

namespace App\Application\UseCases\Counter;

use App\Domain\Entities\Counter;
use App\Domain\Repositories\CounterRepositoryInterface;
use App\Application\DTOs\CounterDTO;
use Exception;

class CreateCounter
{
    private CounterRepositoryInterface $counterRepository;

    public function __construct(CounterRepositoryInterface $counterRepository)
    {
        $this->counterRepository = $counterRepository;
    }

    /**
     * Membuat loket baru.
     */
    public function execute(string $name, bool $isActive = true): CounterDTO
    {
        $name = trim($name);
        if (empty($name)) {
            throw new Exception("Nama loket tidak boleh kosong.");
        }

        // Cek apakah nama loket sudah terdaftar
        $existing = $this->counterRepository->findByName($name);
        if ($existing) {
            throw new Exception("Loket dengan nama '{$name}' sudah terdaftar.");
        }

        $counter = new Counter(null, $name, $isActive);
        $saved = $this->counterRepository->save($counter);

        if (!$saved) {
            throw new Exception("Gagal menyimpan loket ke database.");
        }

        return CounterDTO::fromEntity($counter);
    }
}
