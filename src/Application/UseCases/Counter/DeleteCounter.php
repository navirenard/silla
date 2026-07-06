<?php

namespace App\Application\UseCases\Counter;

use App\Domain\Repositories\CounterRepositoryInterface;
use Exception;

class DeleteCounter
{
    private CounterRepositoryInterface $counterRepository;

    public function __construct(CounterRepositoryInterface $counterRepository)
    {
        $this->counterRepository = $counterRepository;
    }

    /**
     * Menghapus loket.
     */
    public function execute(string $id): void
    {
        $counter = $this->counterRepository->findById($id);
        if (!$counter) {
            throw new Exception("Loket tidak ditemukan.");
        }

        $deleted = $this->counterRepository->delete($id);
        if (!$deleted) {
            throw new Exception("Gagal menghapus loket.");
        }
    }
}
