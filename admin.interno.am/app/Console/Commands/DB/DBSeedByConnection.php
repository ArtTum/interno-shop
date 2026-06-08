<?php

namespace App\Console\Commands\DB;

use App\Repositories\Vendor\VendorRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class DBSeedByConnection extends Command
{
    protected $signature = 'db:seed-c {class_name} {connection?}';

    private VendorRepository $vendorRepository;

    public function __construct(
        VendorRepository $vendorRepository,
    )
    {
        $this->vendorRepository = $vendorRepository;
        parent::__construct();
    }

    public function handle(): void
    {
        $className = $this->argument('class_name');

        $connection = $this->argument('connection');
        $vendors = $this->vendorRepository->fetchForPreparingDB($connection);

        foreach ($vendors as $vendor) {
            $dbName = Config::get("database.connections.{$vendor->db_name}.database", '');

            $paramsArray = [
                '--database' => $dbName,
                '--force' => true
            ];

            if ($className) $paramsArray['--class'] = $className;
            Artisan::call('db:seed', $paramsArray);
        }
    }
}
