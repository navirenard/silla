<?php

namespace App\Application\UseCases\Counter;

use App\Domain\Repositories\CounterRepositoryInterface;
use App\Application\DTOs\CounterDTO;

class GetCounters
{
    private CounterRepositoryInterface $counterRepository;

    public function __construct(CounterRepositoryInterface $counterRepository)
    {
        $this->counterRepository = $counterRepository;
    }

    /**
     * Dapatkan semua daftar loket.
     * @return CounterDTO[]
     */
    public function execute(): array
    {
        $counters = $this->counterRepository->findAll();
        return array_map(function($counter) {
            return CounterDTO::fromEntity($counter);
        }, $counters);
    }
}
