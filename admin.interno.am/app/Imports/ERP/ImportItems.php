<?php

namespace App\Imports\ERP;

use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Models\Item;
use App\Models\ProductVariant;
use App\Models\UploadLog;
use App\Repositories\Item\ItemRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportItems implements ToCollection
{
    private $upload;
    private string $importType;
    private string $vendorKey;
    private ItemRepository $itemRepository;
    private UploadLogRepository $uploadLogRepository;

    private $iniSKU;
    private $iniCategoryId;
    private $iniName;
    private $iniProductionPrice;
    private $iniRegularPrice;
    private $iniStockQuantity;
    private $iniNegativeStock;
    private $iniNetWeight;
    private $iniGrossWeight;
    private $iniCountry;
    private $iniHsCode;
    private $iniHsName;
    private $iniProperShippingName;
    private $iniUnNumbers;
    private $iniPackagesGroup;
    private $iniDangerousGoodsClass;
    private $iniMarkNos;
    private $iniPackageType;
    private $positionPackstation;
    private $iniStockStatus;
    private $iniStatus;
    private ProductVariantRepository $productVariantRepository;

    private array $fileHeaders;

    public function __construct($upload, string $importType, string $vendorKey)
    {
        $this->upload = $upload;
        $this->importType = $importType;
        $this->vendorKey = $vendorKey;
        $this->itemRepository = new ItemRepository(new Item());
        $this->uploadLogRepository = new UploadLogRepository(new UploadLog());
        $this->productVariantRepository = new ProductVariantRepository(new ProductVariant());
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
                if ($row == 0) {
                    $this->fileHeaders = $this->prepareHeadersFile($data);

                    if (count($data) !== count(UploadConstant::ITEMS_FILE_HEADERS)) {
                        throw new \Exception('Columns count is wrong');
                    }
                    continue;
                }

                $totalLines++;
                $logsLoopNumber++;

                $this->iniSKU = !empty($data[0]) ? trim($data[0]) : null;
                $this->iniCategoryId = !empty($data[1]) ? $data[1] : null;
                $this->iniName = !empty($data[2]) ? $data[2] : null;
                $this->iniProductionPrice = !empty($data[3]) ? str_replace(',', '.', $data[3]) : null;
                $this->iniRegularPrice = !empty($data[4]) ? str_replace(',', '.', $data[4]) : null;
                $this->iniStockQuantity = !empty($data[5]) ? $data[5] : null;
                $this->iniNegativeStock = !empty($data[6]) ? $data[6] : null;
                $this->iniNetWeight = !empty($data[7]) ? str_replace(',', '.', $data[7]) : null;
                $this->iniGrossWeight = !empty($data[8]) ? str_replace(',', '.', $data[8]) : null;
                $this->positionPackstation = !empty($data[9]) ? $data[9] : null;
                $this->iniCountry = !empty($data[10]) ? $data[10] : null;
                $this->iniHsCode = !empty($data[11]) ? $data[11] : null;
                $this->iniHsName = !empty($data[12]) ? $data[12] : null;
                $this->iniProperShippingName = !empty($data[13]) ? $data[13] : null;
                $this->iniUnNumbers = !empty($data[14]) ? $data[14] : null;
                $this->iniPackagesGroup = !empty($data[15]) ? $data[15] : null;
                $this->iniDangerousGoodsClass = !empty($data[16]) ? $data[16] : null;
                $this->iniMarkNos = !empty($data[17]) ? $data[17] : null;
                $this->iniPackageType = !empty($data[18]) ? $data[18] : null;
                $this->iniStockStatus = !empty($data[19]) ? $data[19] : 'out_of_stock';
                $this->iniStatus = !empty($data[20]) ? $data[20] : 'Inactive';

                $item = $this->itemRepository->fetchByField('sku', $this->iniSKU, 'id');

                $negativeStock = null;
                if ($this->iniNegativeStock === 'Yes') {
                    $negativeStock = true;
                } else if ($this->iniNegativeStock === 'No') {
                    $negativeStock = false;
                }

                if ($this->iniStockStatus === 'in_stock') {
                    $stockStatus = 1;
                } else {
                    $stockStatus = 0;
                }

                if ($this->iniStatus === 'Active') {
                    $status = true;
                } else {
                    $status = false;
                }

                $validator = $this->validate($item, $negativeStock);

                if (!$validator['success']) {
                    foreach ($validator['errors'] as $error) {
                        $logs[] = [
                            'upload_id' => $this->upload->id,
                            'log' => "<span class='text-danger'>Error at line {$row}</span>: {$error}",
                        ];
                    }
                    $invalidLines++;
                    continue;
                }

                DB::beginTransaction();
                try {
                    $preparedData = [
                        'category_id' => $this->iniCategoryId,
                        'sku' => $this->iniSKU,
                        'name' => $this->iniName,
                        'production_price' => $this->iniProductionPrice,
                        'regular_price' => $this->iniRegularPrice,
//                        'stock_status' => $stockStatus,
//                        'status' => $status,
                        'stock_quantity' => $this->iniStockQuantity ?: 0,
                        'negative_stock' => $negativeStock,
                        'net_weight' => $this->iniNetWeight,
                        'gross_weight' => $this->iniGrossWeight,
                        'country' => $this->iniCountry,
                        'position_packstation' => $this->positionPackstation,
                        'hs_code' => $this->iniHsCode,
                        'package_group' => $this->iniPackagesGroup,
                        'hs_name' => $this->iniHsName,
                        'proper_shipping_name' => $this->iniProperShippingName,
                        'un_numbers' => $this->iniUnNumbers,
                        'dangerous_goods_class' => $this->iniDangerousGoodsClass,
                        'mark_nos' => $this->iniMarkNos,
                        'packages_type' => $this->iniPackageType,
                    ];

                    if ($this->importType == 2 || ($this->importType == 3 && !empty($item))) {
                        $preparedData = $this->removeUnnecessaryKeysFromItemsArray($preparedData);
                    }

                    if (empty($item)) {
//                        $preparedData['status'] = true;
//                        $preparedData['stock_status'] = true;
                    }

                    if (!$item) {
                        $this->itemRepository->insert($preparedData);
                    } else {
                        $this->itemRepository->update('id', $item->id, $preparedData);

//                        if (isset($preparedData['stock_status'])) {
//                            $this->productVariantRepository->updateStatusFromItems($item->id, $stockStatus, 'stock_status');
//                        }
//                        if (isset($preparedData['status'])) {
//                            $this->productVariantRepository->updateStatusFromItems($item->id, $stockStatus, 'status');
//                        }
                    }

                    DB::commit();
                } catch (\Exception $exception) {
                    $logs[] = [
                        'upload_id' => $this->upload->id,
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: {$exception->getMessage()}",
                    ];
                    $invalidLines++;
                    DB::rollBack();
                    continue;
                }

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
            broadcast(new ReloadPagePublic('update-items-page'));
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

    private function validate($item, ?bool $negativeStock): array
    {
        if ($this->importType == 2 && empty($item)) {
            return [
                'success' => false,
                'errors' => ["There is not item by this SKU"],
            ];
        } else if ($this->importType == 1 && !empty($item)) {
            return [
                'success' => false,
                'errors' => ["Item by this SKU already exists"],
            ];
        }

        $preparedValidationData = [
            'category_id' => $this->iniCategoryId,
            'sku' => $this->iniSKU,
            'name' => $this->iniName,
            'production_price' => $this->iniProductionPrice,
            'regular_price' => $this->iniRegularPrice,
            'stock_quantity' => $this->iniStockQuantity,
            'negative_stock' => $negativeStock,
            'net_weight' => $this->iniNetWeight,
            'gross_weight' => $this->iniGrossWeight,
            'country' => $this->iniCountry,
            'position_packstation' => $this->positionPackstation,
            'hs_code' => $this->iniHsCode,
            'hs_name' => $this->iniHsName,
            'proper_shipping_name' => $this->iniProperShippingName,
            'package_group' => $this->iniPackagesGroup,
            'un_numbers' => $this->iniUnNumbers,
            'dangerous_goods_class' => $this->iniDangerousGoodsClass,
            'mark_nos' => $this->iniMarkNos,
            'packages_type' => $this->iniPackageType,
        ];

        $rules = [
            'category_id' => 'nullable|integer|exists:categories,id',
            'name' => 'required|string|max:250',
            'production_price' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
            'regular_price' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
            'stock_quantity' => 'integer|nullable',
            'negative_stock' => 'required|boolean',
            'net_weight' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
            'gross_weight' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
            'country' => 'nullable|string|max:120',
            'hs_code' => 'nullable',
            'hs_name' => 'nullable|string|max:250',
            'proper_shipping_name' => 'nullable|string|max:250',
            'un_numbers' => 'nullable|string|max:50',
            'package_group' => 'nullable|string|max:50',
            'dangerous_goods_class' => 'nullable|integer',
            'mark_nos' => 'nullable|string|max:250',
            'packages_type' => 'nullable|string|max:100',
        ];

        if ($this->importType == 2) {
            $rules['sku'] = 'required|string|max:80|unique:items,sku,' . $item->id;
        } else if ($this->importType == 1) {
            $rules['sku'] = 'required|string|max:80|unique:items,sku';
        } else if ($this->importType == 3) {
            if (empty($item)) {
                $rules['sku'] = 'required|string|max:80|unique:items,sku';
            } else {
                $rules['sku'] = 'required|string|max:80|unique:items,sku,' . $item->id;
            }
        }

        if ($this->importType == 2 || ($this->importType == 3 && !empty($item))) {
            $rules = $this->removeUnnecessaryKeysFromValidationArray($rules);
        }

        $validator = Validator::make($preparedValidationData, $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->all(),
            ];
        } else {
            return [
                'success' => true
            ];
        }
    }

    private function removeUnnecessaryKeysFromValidationArray(array $rules): array
    {
        if (str_contains(strtoupper($this->fileHeaders['iniCategoryId']), 'SKIP')) {
            unset($rules['category_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniName']), 'SKIP')) {
            unset($rules['name']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniProductionPrice']), 'SKIP')) {
            unset($rules['production_price']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniRegularPrice']), 'SKIP')) {
            unset($rules['regular_price']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniStockQuantity']), 'SKIP')) {
            unset($rules['stock_quantity']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniNegativeStock']), 'SKIP')) {
            unset($rules['negative_stock']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniNetWeight']), 'SKIP')) {
            unset($rules['net_weight']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniGrossWeight']), 'SKIP')) {
            unset($rules['gross_weight']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniCountry']), 'SKIP')) {
            unset($rules['country']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniHsCode']), 'SKIP')) {
            unset($rules['hs_code']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniHsName']), 'SKIP')) {
            unset($rules['hs_name']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniProperShippingName']), 'SKIP')) {
            unset($rules['proper_shipping_name']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniUnNumbers']), 'SKIP')) {
            unset($rules['un_numbers']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniPackagesGroup']), 'SKIP')) {
            unset($rules['package_group']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniDangerousGoodsClass']), 'SKIP')) {
            unset($rules['dangerous_goods_class']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniMarkNos']), 'SKIP')) {
            unset($rules['mark_nos']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniPackageType']), 'SKIP')) {
            unset($rules['packages_type']);
        }

        return $rules;
    }

    private function removeUnnecessaryKeysFromItemsArray(array $rules): array
    {
        if (str_contains(strtoupper($this->fileHeaders['iniCategoryId']), 'SKIP')) {
            unset($rules['category_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniName']), 'SKIP')) {
            unset($rules['name']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniProductionPrice']), 'SKIP')) {
            unset($rules['production_price']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniRegularPrice']), 'SKIP')) {
            unset($rules['regular_price']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniStockQuantity']), 'SKIP')) {
            unset($rules['stock_quantity']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniNegativeStock']), 'SKIP')) {
            unset($rules['negative_stock']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniNetWeight']), 'SKIP')) {
            unset($rules['net_weight']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniGrossWeight']), 'SKIP')) {
            unset($rules['gross_weight']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniCountry']), 'SKIP')) {
            unset($rules['country']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniHsCode']), 'SKIP')) {
            unset($rules['hs_code']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniPackagesGroup']), 'SKIP')) {
            unset($rules['package_group']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniHsName']), 'SKIP')) {
            unset($rules['hs_name']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniProperShippingName']), 'SKIP')) {
            unset($rules['proper_shipping_name']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniUnNumbers']), 'SKIP')) {
            unset($rules['un_numbers']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniDangerousGoodsClass']), 'SKIP')) {
            unset($rules['dangerous_goods_class']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniMarkNos']), 'SKIP')) {
            unset($rules['mark_nos']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniPackageType']), 'SKIP')) {
            unset($rules['packages_type']);
        }
//        if (str_contains(strtoupper($this->fileHeaders['iniStockStatus']), 'SKIP')) {
//            unset($rules['stock_status']);
//        }
//        if (str_contains(strtoupper($this->fileHeaders['iniStatus']), 'SKIP')) {
//            unset($rules['status']);
//        }

        return $rules;
    }

    private function prepareHeadersFile(array $headers): array
    {
        return [
            'iniSKU' => $headers[0],
            'iniCategoryId' => $headers[1],
            'iniName' => $headers[2],
            'iniProductionPrice' => $headers[3],
            'iniRegularPrice' => $headers[4],
            'iniStockQuantity' => $headers[5],
            'iniNegativeStock' => $headers[6],
            'iniNetWeight' => $headers[7],
            'iniGrossWeight' => $headers[8],
            'positionPackstation' => $headers[9],
            'iniCountry' => $headers[10],
            'iniHsCode' => $headers[11],
            'iniHsName' => $headers[12],
            'iniProperShippingName' => $headers[13],
            'iniUnNumbers' => $headers[14],
            'iniPackagesGroup' => $headers[15],
            'iniDangerousGoodsClass' => $headers[16],
            'iniMarkNos' => $headers[17],
            'iniPackageType' => $headers[18],
            'iniStockStatus' => $headers[19],
            'iniStatus' => $headers[20],
        ];
    }
}
