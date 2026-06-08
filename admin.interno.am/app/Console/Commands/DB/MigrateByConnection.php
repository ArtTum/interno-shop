<?php

namespace App\Console\Commands\DB;

use App\Repositories\Vendor\VendorRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class MigrateByConnection extends Command
{
    protected $signature = 'migrate-c {connection?}';

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
        $connection = $this->argument('connection');
        $vendors = $this->vendorRepository->fetchForPreparingDB($connection);

        foreach ($vendors as $vendor) {
            $dbName = Config::get("database.connections.{$vendor->db_name}.database", '');

            Artisan::call('migrate', [
                '--database' => $dbName,
                '--force' => true
            ]);
        }
    }
}
