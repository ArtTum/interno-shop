<?php

namespace App\Export\ERP;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class DefaultExport implements FromCollection, WithHeadings, WithStyles
{
    private Collection $data;
    private array $headers;

    public function __construct(Collection $data, array $headers)
    {
        $this->data = $data;
        $this->headers = $headers;
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headers;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}