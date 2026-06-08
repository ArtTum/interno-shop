<?php

namespace App\Imports\ERP;

use App\Constants\OrderConstants;
use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Models\Order;
use App\Models\OrderInfo;
use App\Models\OrderNote;
use App\Models\UploadLog;
use App\Repositories\Order\OrderRepository;
use App\Repositories\OrderInfo\OrderInfoRepository;
use App\Repositories\OrderNote\OrderNoteRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportShippingCostsUploader implements ToCollection
{
    private OrderNoteRepository $orderNoteRepository;
    private UploadLogRepository $uploadLogRepository;
    private mixed $user;
    private array $dataParams;
    private string $vendorKey;
    private mixed $upload;
    private OrderRepository $orderRepository;
    private OrderInfoRepository $orderInfoRepository;

    public function __construct($upload, string $vendorKey, array $dataParams, $user)
    {
        $this->upload = $upload;
        $this->vendorKey = $vendorKey;
        $this->dataParams = $dataParams;
        $this->user = $user;
        $this->orderRepository = new OrderRepository(new Order());
        $this->orderInfoRepository = new OrderInfoRepository(new OrderInfo());
        $this->uploadLogRepository = new UploadLogRepository(new UploadLog());
        $this->orderNoteRepository = new OrderNoteRepository(new OrderNote());
    }

    public function collection(Collection $collection): void
    {
        setDBConnection($this->vendorKey);
        $invalidLines = 0;
        $totalLines = 0;
        $logsLoopNumber = 0;
        $logs = [];
        try {
            foreach ($collection as $row => $data) {
                $data = $data->toArray();

                if (
                    $row == 0 || empty($data[($this->dataParams['order_number_column'] - 1)]) ||
                    empty($data[($this->dataParams['shipping_cost_column'] - 1)])
                ) {
                    continue;
                }
                $totalLines++;
                $logsLoopNumber++;

                $cost = $data[($this->dataParams['shipping_cost_column'] - 1)];
                $cleanedCost = preg_replace('/[^\d,.]/', '', $cost);
                $decimalCost = floatval(str_replace(',', '.', $cleanedCost));

                $orderId = $data[($this->dataParams['order_number_column'] - 1)];
                $orderId = preg_replace('/\D/', '', $orderId);

                if (empty($orderId)) {
                    $invalidLines ++;
                    $logs[] = [
                        'upload_id' => $this->upload->id,
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: Empty order number",
                    ];
                    continue;
                }
                if (empty($decimalCost)) {
                    $invalidLines ++;
                    $logs[] = [
                        'upload_id' => $this->upload->id,
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: Empty shipping price",
                    ];
                    continue;
                }

                $orderInfo = $this->orderInfoRepository->fetchByField('order_id', $orderId, 'id, order_id');

                if (!$orderInfo) {
                    $invalidLines ++;
                    $logs[] = [
                        'upload_id' => $this->upload->id,
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: Order not found",
                    ];
                    continue;
                }



                $orderInfo->update([
                    'shipping_cost' => $decimalCost,
                ]);

                $this->orderNoteRepository->insert(merge_dates_for_insert([
                    'order_id' => $orderId,
                    'user_id' => $this->user->id,
                    'log_by' => $this->user->name . ' ' . $this->user->last_name,
                    'note' => 'Added Shipping Cost: ' . $decimalCost,
                ], now()));


                if ($logsLoopNumber === 50) {
                    if (!empty($logs)) $this->uploadLogRepository->insert($logs);
                    $logsLoopNumber = 0;
                }
            }

            if (!empty($logs)) $this->uploadLogRepository->insert($logs);

            $this->upload->update(
                [
                    'status' => UploadConstant::STATUSES['Completed'],
                    'total_lines' => $totalLines,
                    'invalid_lines' => $invalidLines,
                    'succeed_lines' => $totalLines - $invalidLines,
                ]
            );

            broadcast(new ReloadPagePublic('update-uploads-page'));
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }
    }

    public function chunkSize(): int
    {
        return 1000; // Number of rows per chunk
    }

    public function batchSize(): int
    {
        return 1000; // Number of rows per batch
    }
}
