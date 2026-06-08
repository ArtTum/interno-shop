<?php

namespace App\Imports\Front;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportCart implements ToCollection
{
    protected array $processedData = [];
    protected array $processedSKUs = [];

    public function collection(Collection $collection): void
    {
        foreach ($collection as $index => $row) {
            if (!$index) continue;
            $this->processedData[] = $row;
            $this->processedSKUs[] = $row[0];
        }
    }

    public function getProcessedData(): array
    {
        return [
            'data' => $this->processedData,
            'skus' => $this->processedSKUs,
        ];
    }

    public function chunkSize(): int
    {
        return 1000; // Number of rows per chunk
    }

    public function batchSize(): int
    {
        return 1000; // Number of rows per batch
    }
}
