<?php

namespace App\Imports\ERP;

use App\Constants\OrderConstants;
use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Jobs\Order\GenerateDocumentAndSendEmail;
use App\Models\Order;
use App\Models\OrderNote;
use App\Models\UploadLog;
use App\Repositories\Order\OrderRepository;
use App\Repositories\OrderNote\OrderNoteRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportBankTransferProcessing implements ToCollection
{
    private OrderNoteRepository $orderNoteRepository;
    private UploadLogRepository $uploadLogRepository;
    private mixed $user;
    private array $dataParams;
    private string $vendorKey;
    private mixed $upload;
    private OrderRepository $orderRepository;

    public function __construct($upload, string $vendorKey, array $dataParams, $user)
    {
        $this->upload = $upload;
        $this->vendorKey = $vendorKey;
        $this->dataParams = $dataParams;
        $this->user = $user;
        $this->orderRepository = new OrderRepository(new Order());
        $this->uploadLogRepository = new UploadLogRepository(new UploadLog());
        $this->orderNoteRepository = new OrderNoteRepository(new OrderNote());
    }

    public function collection(Collection $collection): void
    {
        setDBConnection($this->vendorKey);
        $invalidLines = 0;
        $totalLines = 0;
        $logsLoopNumber = 0;
        try {
            foreach ($collection as $row => $data) {
                $data = $data->toArray();
                if (
                    $row == 0 || empty($data[($this->dataParams['order_id_column'] - 1)]) ||
                    empty($data[($this->dataParams['price_column'] - 1)])
                ) {
                    continue;
                }
                $totalLines++;
                $logsLoopNumber++;

                $price = $data[($this->dataParams['price_column'] - 1)];
                $cleanedPrice = preg_replace('/[^\d,]/', '', $price);
                $decimalPrice = floatval(str_replace(',', '.', $cleanedPrice));

                $orderId = $data[($this->dataParams['order_id_column'] - 1)];
                $orderId = preg_replace('/\D/', '', $orderId);

                if (empty($orderId)) {
                    $invalidLines ++;
                    $logs[] = [
                        'upload_id' => $this->upload->id,
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: Empty order ID",
                    ];
                    continue;
                }
                if (empty($decimalPrice)) {
                    $invalidLines ++;
                    $logs[] = [
                        'upload_id' => $this->upload->id,
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: Empty price",
                    ];
                    continue;
                }

                $order = $this->orderRepository->checkBankTransferOrder($orderId);

                if (!$order) {
                    $invalidLines ++;
                    $logs[] = [
                        'upload_id' => $this->upload->id,
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: Order not found",
                    ];
                    continue;
                }

                if ($order->total_price > $decimalPrice) {
                    $invalidLines ++;
                    $logs[] = [
                        'upload_id' => $this->upload->id,
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: Incorrect price",
                    ];
                    continue;
                }

                $order->update([
                    'status' => OrderConstants::STATUSES['Processing'],
                    'status_change_date' => Carbon::now(),
                    'payment_date' => now()
                ]);

                $this->orderNoteRepository->insert(merge_dates_for_insert([
                    'order_id' => $order->id,
                    'user_id' => $this->user->id,
                    'log_by' => $this->user->name . ' ' . $this->user->last_name,
                    'note' => 'Status changed to Processing via Bank Transfer Processing'
                ], now()));

                Log::channel('generate-document-and-send-email-errors')->error('Failed', [
                    'info' => [
                        'order_id' => $order->id,
                        'shipping_carrier' => $order->shipping_carrier ?? '',
                        'path' => 'import bank transfer',
                    ],
                ]);

                GenerateDocumentAndSendEmail::dispatch($order->id, OrderConstants::STATUSES['Processing'], $this->vendorKey, true);


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
