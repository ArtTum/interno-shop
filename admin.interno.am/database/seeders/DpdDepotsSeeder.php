<?php

namespace Database\Seeders;

use App\Imports\ERP\ImportDpdDepots;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class DpdDepotsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = database_path('seeders/data/dpd_depots.csv');

        Excel::import(new ImportDpdDepots, $filePath);
    }
}
