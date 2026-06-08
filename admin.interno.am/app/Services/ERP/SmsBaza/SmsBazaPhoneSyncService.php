<?php

namespace App\Services\ERP\SmsBaza;

use App\Models\Service;
use App\Models\SmsBaza;

class SmsBazaPhoneSyncService
{
    public function syncFromService(Service $service): bool
    {
        $phone = $this->normalizePhone($service->phone);

        if ($phone === null) {
            return false;
        }

        $exists = SmsBaza::query()
            ->whereRaw('TRIM(phone) = ?', [$phone])
            ->exists();

        if ($exists) {
            return false;
        }

        SmsBaza::query()->create([
            'year' => $service->year,
            'month' => $service->month,
            'call_date' => $service->call_date,
            'phone' => $phone,
            'other_phone' => $service->other_phone,
            'patient_full_name' => $service->patient_full_name,
            'disease' => $service->disease,
            'medical_and_doctor' => $service->medical_and_doctor,
        ]);

        return true;
    }

    public function backfillFromServices(int $chunkSize = 500): array
    {
        $processed = 0;
        $inserted = 0;
        $skipped = 0;

        Service::query()
            ->select([
                'id',
                'year',
                'month',
                'call_date',
                'phone',
                'other_phone',
                'patient_full_name',
                'disease',
                'medical_and_doctor',
            ])
            ->whereNotNull('phone')
            ->orderBy('id')
            ->chunkById($chunkSize, function ($services) use (&$processed, &$inserted, &$skipped) {
                foreach ($services as $service) {
                    dump($service->id);
                    $processed++;
                    if ($this->syncFromService($service)) {
                        $inserted++;
                    } else {
                        $skipped++;
                    }
                }
            });

        return [
            'processed' => $processed,
            'inserted' => $inserted,
            'skipped' => $skipped,
        ];
    }

    private function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $phone = trim($phone);

        return $phone === '' ? null : $phone;
    }
}

