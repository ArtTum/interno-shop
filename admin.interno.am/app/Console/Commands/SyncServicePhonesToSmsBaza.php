<?php

namespace App\Console\Commands;

use App\Services\ERP\SmsBaza\SmsBazaPhoneSyncService;
use Illuminate\Console\Command;

class SyncServicePhonesToSmsBaza extends Command
{
    protected $signature = 'app:sync-service-phones-to-sms-baza {--chunk=500 : Chunk size for processing services}';

    protected $description = 'Sync unique phone numbers from services table into sms_baza table';

    public function handle(SmsBazaPhoneSyncService $syncService): int
    {
        $chunk = max(1, (int) $this->option('chunk'));

        $this->info('Sync started...');
        $result = $syncService->backfillFromServices($chunk);

        $this->info('Sync finished.');
        $this->line('Processed: ' . $result['processed']);
        $this->line('Inserted: ' . $result['inserted']);
        $this->line('Skipped: ' . $result['skipped']);

        return self::SUCCESS;
    }
}

