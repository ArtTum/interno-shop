<?php

namespace App\Observers;

use App\Models\Service;
use App\Services\ERP\SmsBaza\SmsBazaPhoneSyncService;
use Illuminate\Support\Facades\Log;

class ServiceObserver
{
    public function created(Service $service): void
    {
        try {
            app(SmsBazaPhoneSyncService::class)->syncFromService($service);
        } catch (\Throwable $exception) {
            Log::channel('sms-bazas-errors')->error('Service->SmsBaza auto sync failed', [
                'service_id' => $service->id,
                'phone' => $service->phone,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}

