<?php

namespace App\Domain\Services;

class QueueNumberGenerator
{
    private string $prefix;

    public function __construct(string $prefix = 'A')
    {
        $this->prefix = $prefix;
    }

    /**
     * Generate nomor antrian berikutnya berdasarkan nomor terakhir hari ini.
     * Contoh: A001 -> A002, atau null -> A001
     */
    public function generateNext(?string $lastQueueNumber): string
    {
        if (!$lastQueueNumber) {
            return $this->prefix . '001';
        }

        // Hapus prefix untuk mengambil angka sequence
        $numberPart = str_replace($this->prefix, '', $lastQueueNumber);
        $nextNumber = (int) $numberPart + 1;

        // Pad dengan 0 di depan agar formatnya tetap 3 digit (misal: 001, 012, 100)
        return $this->prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
