<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FinalizeDateColumns extends Command
{
    protected $signature = 'app:finalize-date-columns';

    protected $description = 'Drop old varchar date columns, rename _new DATE columns, restore indexes';

    public function handle(): void
    {
        // Safety check — verify no nulls remain where old column had data
        $failed = DB::select("
            SELECT COUNT(*) as cnt FROM services
            WHERE (call_date_new IS NULL AND call_date IS NOT NULL AND call_date != '')
               OR (next_call_date_new IS NULL AND next_call_date IS NOT NULL AND next_call_date != '')
               OR (finish_date_new IS NULL AND finish_date IS NOT NULL AND finish_date != '')
               OR (day_surgery_new IS NULL AND day_surgery IS NOT NULL AND day_surgery != '')
        ");

        if ($failed[0]->cnt > 0) {
            $this->error("Abort! {$failed[0]->cnt} records still not converted. Run app:convert-service-dates first.");
            return;
        }

        $this->info('All records converted. Proceeding...');

        DB::statement('ALTER TABLE services DROP INDEX services_call_date_index');
        DB::statement('ALTER TABLE services DROP INDEX services_next_call_date_index');
        DB::statement('ALTER TABLE services DROP INDEX services_day_surgery_index');
        $this->info('Indexes dropped.');

        DB::statement('ALTER TABLE services DROP COLUMN call_date, DROP COLUMN next_call_date, DROP COLUMN finish_date, DROP COLUMN day_surgery');
        $this->info('Old columns dropped.');

        $this->info('Proceeding...');

        DB::statement('ALTER TABLE services CHANGE call_date_new call_date DATE NULL');
        DB::statement('ALTER TABLE services CHANGE next_call_date_new next_call_date DATE NULL');
        DB::statement('ALTER TABLE services CHANGE finish_date_new finish_date DATE NULL');
        DB::statement('ALTER TABLE services CHANGE day_surgery_new day_surgery DATE NULL');
        $this->info('Columns renamed.');

        DB::statement('ALTER TABLE services ADD INDEX (call_date)');
        DB::statement('ALTER TABLE services ADD INDEX (next_call_date)');
        DB::statement('ALTER TABLE services ADD INDEX (day_surgery)');
        $this->info('Indexes restored.');

        $this->info('Done!');
    }
}
