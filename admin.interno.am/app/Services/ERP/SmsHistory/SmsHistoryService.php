<?php

namespace App\Services\ERP\SmsHistory;

use App\Repositories\SmsHistory\SmsHistoryRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class SmsHistoryService
{
    private SmsHistoryRepository $repository;

    public function __construct(SmsHistoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function fetchIndexParams(): JsonResponse
    {
        $startYear = 2020;
        $endYear = now()->year + 1;

        $years = [['value' => -1, 'label' => 'Բոլորը']];
        for ($year = $startYear; $year <= $endYear; $year++) {
            $years[] = ['value' => $year, 'label' => $year];
        }

        $months = [
            ['value' => -1, 'label' => 'Բոլորը'],
            ['value' => 1, 'label' => 'Հունվար'],
            ['value' => 2, 'label' => 'Փետրվար'],
            ['value' => 3, 'label' => 'Մարտ'],
            ['value' => 4, 'label' => 'Ապրիլ'],
            ['value' => 5, 'label' => 'Մայիս'],
            ['value' => 6, 'label' => 'Հունիս'],
            ['value' => 7, 'label' => 'Հուլիս'],
            ['value' => 8, 'label' => 'Օգոստոս'],
            ['value' => 9, 'label' => 'Սեպտեմբեր'],
            ['value' => 10, 'label' => 'Հոկտեմբեր'],
            ['value' => 11, 'label' => 'Նոյեմբեր'],
            ['value' => 12, 'label' => 'Դեկտեմբեր'],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'years' => $years,
            'months' => $months,
        ]);
    }

    public function fetch(array $data): JsonResponse
    {
        $select = "id, year, month, sms_date, phone, sms_text";
        $pagination = prepare_pagination_array($data['page'], $data['per_page']);
        $ordering = [
            'field' => $data['ordering_field'],
            'direction' => $data['ordering_direction']
        ];
        $searchFields = ['sms_text', 'phone'];

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetch($select, $pagination, $ordering, $data, $searchFields, []),
            'pagination' => prepare_pagination(
                $data['page'], $data['per_page'],
                $this->repository->fetchTotalCount($select, $pagination, $ordering, $data, $searchFields, [])
            )
        ]);
    }

    public function fetchByField(array $data): JsonResponse
    {
        $select = "id, year, month, sms_date, phone, sms_text";
        $response = $this->repository->fetchByFieldWith('id', $data['id'], $select);

        if (!empty($response->ips)) {
            foreach ($response->ips as $ip) {
                $fullDate = $ip->expires_at;
                if (!$fullDate) continue;

                $ip->expires_at = Carbon::parse($fullDate)->toDateString();
                $ip->time = Carbon::parse($fullDate)->format('H:i');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $response
        ]);
    }

    public function insert(array $data): JsonResponse
    {
       $this->repository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!'
        ]);
    }

    public function update(array $data): JsonResponse
    {

        $this->repository->update('id', $data['id'], $data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!'
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        $this->repository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!'
        ]);
    }

    public function send(array $data): JsonResponse
    {
        $phone = str_replace(["-", "(", ")", ".", ",", " "], "", $data['phone']);
        $smsText = $data['sms_text'];
        $refId = now()->format('Y-m-d H:i:s');

        $envelope = '<?xml version="1.0" encoding="UTF-8"?>
<bulk-request login="doctor911" password="doctor911" ref-id="' . $refId . '" delivery-notification-requested="true" version="1.0">
  <message id="1" msisdn="' . $phone . '" service-number="Doctor911am" defer-date="' . $refId . '" validity-period="3" priority="1">
    <content type="text/plain">' . $smsText . '</content>
  </message>
</bulk-request>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://31.47.195.66:80/broker/');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $envelope);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type:text/xml; charset="utf-8"']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_exec($ch);
        curl_close($ch);

        $this->repository->create([
            'year'     => now()->year,
            'month'    => now()->month,
            'sms_date' => now()->format('Y-m-d H:i:s'),
            'phone'    => $data['phone'],
            'sms_text' => $smsText,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SMS sent successfully!',
        ]);
    }
}
