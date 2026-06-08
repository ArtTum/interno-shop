<?php

namespace App\Imports\ERP;

use App\Models\DpdDepot;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportDpdDepots implements ToCollection
{
    /**
     * Process the collection of rows from the CSV.
     *
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        // Extract header row
        $header = $collection->shift()->toArray();

        $chunkSize = 500;

        // Map rows to associative arrays using header columns
        $data = $collection->map(function ($row) use ($header) {
            return array_combine($header, $row->toArray());
        })->filter();

        // Chunk and process updateOrCreate
        $data->chunk($chunkSize)->each(function (Collection $chunk) {
            foreach ($chunk as $row) {
                if (empty($row['country_code'])) {
                    continue;
                }

                DpdDepot::updateOrCreate(
                    ['depot' => $row['depot']],
                    [
                        'country_code' => $row['country_code'],
                        'city' => $row['city'] ?? null,
                        'post_code' => $row['post_code'] ?? null,
                        'street' => $row['street'] ?? null,
                        'bu_code' => $row['bu_code'] ?? null,
                        'lat' => $row['lat'] ?? null,
                        'lng' => $row['lng'] ?? null,
                    ]
                );
            }
        });
    }
}
