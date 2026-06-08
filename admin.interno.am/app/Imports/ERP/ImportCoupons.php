<?php

namespace App\Imports\ERP;

use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponAllowedEmail;
use App\Models\CouponCategory;
use App\Models\CouponProduct;
use App\Models\Product;
use App\Models\UploadLog;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\CouponAllowedEmail\CouponAllowedEmailRepository;
use App\Repositories\CouponCategory\CouponCategoryRepository;
use App\Repositories\CouponProduct\CouponProductRepository;
use App\Repositories\Product\ProductRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use App\Rules\ValidCouponAllowedEmailsFormat;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Validator;

class ImportCoupons implements ToCollection
{
    private $upload;
    private string $importType;
    private string $vendorKey;
    private CouponAllowedEmailRepository $couponAllowedEmailsRepository;
    private CouponRepository $couponRepository;
    private UploadLogRepository $uploadLogRepository;
    private CouponCategoryRepository $couponCategoryRepository;
    private CouponProductRepository $couponProductRepository;
    private ProductRepository $productRepository;
    private CategoryRepository $categoryRepository;
    private CouponAllowedEmailRepository $couponAllowedEmailRepository;
    public function __construct($upload, string $importType, string $vendorKey)
    {
        $this->upload = $upload;
        $this->importType = $importType;
        $this->vendorKey = $vendorKey;
        $this->uploadLogRepository = new UploadLogRepository(new UploadLog());
        $this->couponRepository = new CouponRepository(new Coupon());
        $this->couponAllowedEmailsRepository = new CouponAllowedEmailRepository(new CouponAllowedEmail());
        $this->couponCategoryRepository = new CouponCategoryRepository(new CouponCategory());
        $this->couponProductRepository = new CouponProductRepository(new CouponProduct());
        $this->productRepository = new ProductRepository(new Product());
        $this->categoryRepository = new CategoryRepository(new Category());
        $this->couponAllowedEmailRepository = new CouponAllowedEmailRepository(new CouponAllowedEmail());
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
                    if (count($data) !== count(UploadConstant::COUPONS_FILE_HEADERS)) {
                        throw new \Exception('Columns count is wrong');
                    }
                    continue;
                }
                $totalLines++;
                $logsLoopNumber++;


                $coupon = null;
                if (!empty($data[0])) {
                    $coupon = $this->couponRepository->fetchByField('id', $data[0], 'id', []);
                }

                $status = null;
                if ($data[12] === 'Active') {
                    $status = true;
                } else if ($data[12] === 'Inactive') {
                    $status = false;
                }

                $type = null;
                if ($data[2] === 'Fixed cart discount') {
                    $type = 0;
                } else if ($data[2] === 'Percentage discount') {
                    $type = 1;
                }

                $excludeSaleItems = null;
                if ($data[7] === 'Yes') {
                    $excludeSaleItems = true;
                } else if ($data[7] === 'No') {
                    $excludeSaleItems = false;
                }

                $freeShipping = null;
                if ($data[11] === 'Yes') {
                    $freeShipping = true;
                } else if ($data[11] === 'No') {
                    $freeShipping = false;
                }

                $validator = $this->validate($data, $type, $excludeSaleItems, $status, $freeShipping);

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
                    $couponPreparedArray = [
                        'code' => $data[1],
                        'type' => $type,
                        'amount' => $data[3],
                        'expires_at' => !empty($data[4]) ? Carbon::parse($data[4])->format('Y-m-d') : null,
                        'min_spend' => !empty($data[5]) ? $data[5] : null,
                        'max_spend' => !empty($data[6]) ? $data[6] : null,
                        'exclude_sale_items' => $excludeSaleItems,
                        'usage_limit' => !empty($data[8]) ? $data[8] : null,
                        'usage_limit_per_user' => !empty($data[9]) ? $data[9] : null,
                        'description' => !empty($data[10]) ? $data[10] : null,
                        'free_shipping' => $freeShipping,
                        'status' => $status
                    ];

                    $productIds = null;
                    if (!empty($data[13])) {
                        $productIds = explode(',', $data[13]);
                    }
                    $excludeProductIds = null;
                    if (!empty($data[14])) {
                        $excludeProductIds = explode(',', $data[14]);
                    }
                    $categoryIds = null;
                    if (!empty($data[15])) {
                        $categoryIds = explode(',', $data[15]);
                    }
                    $excludeCategoryIds = null;
                    if (!empty($data[16])) {
                        $excludeCategoryIds = explode(',', $data[16]);
                    }
                    $allowedEmails = null;
                    if (!empty($data[17])) {
                        $allowedEmails = explode('|', $data[17]);
                    }

                    if ($this->importType == 1) {
                        $coupon = $this->couponRepository->create($couponPreparedArray);
                    } else if ($this->importType == 2) {
                        $this->couponRepository->update('id', $coupon->id, $couponPreparedArray);
                    } else if ($this->importType == 3) {
                        if (!$coupon) {
                            $coupon = $this->couponRepository->create($couponPreparedArray);
                        } else {
                            $this->couponRepository->update('id', $coupon->id, $couponPreparedArray);
                        }
                    }
                    $prepareForNewProductsArray = [];
                    if ($productIds) {
                        $prepareForNewProductsArray = $this->checkCreateOrDeleteCouponProductsAndCategories(
                            $coupon->id,
                            $productIds,
                            'couponProductRepository',
                            $prepareForNewProductsArray,
                            false,
                            now(),
                            'product_id',
                            'deleteByCouponAndProductIds'
                        );
                    } else {
                        $this->couponProductRepository->deleteByCouponId($coupon->id, false);
                    }
                    if ($excludeProductIds) {
                        $prepareForNewProductsArray = $this->checkCreateOrDeleteCouponProductsAndCategories(
                            $coupon->id,
                            $excludeProductIds,
                            'couponProductRepository',
                            $prepareForNewProductsArray,
                            true,
                            now(),
                            'product_id',
                            'deleteByCouponAndProductIds'
                        );
                    } else {
                        $this->couponProductRepository->deleteByCouponId($coupon->id, true);
                    }
                    if (!empty($prepareForNewProductsArray)) {
                        $issueExists = false;
                        foreach ($prepareForNewProductsArray as $couponProduct) {
                            $productExists = $this->productRepository->checkExistsById($couponProduct['product_id']);

                            if (!$productExists) {
                                $issueExists = true;
                                $logs[] = [
                                    'upload_id' => $this->upload->id,
                                    'log' => "<span class='text-danger'>Error at line {$row}</span>: Invalid media ID",
                                ];
                            } else {
                                $this->couponProductRepository->insert($couponProduct);
                            }
                        }

                        if ($issueExists) $invalidLines++;
                    }

                    $prepareForNewCategoriesArray = [];
                    if ($categoryIds) {
                        $prepareForNewCategoriesArray = $this->checkCreateOrDeleteCouponProductsAndCategories(
                            $coupon->id,
                            $categoryIds,
                            'couponCategoryRepository',
                            $prepareForNewCategoriesArray,
                            false,
                            now(),
                            'category_id',
                            'deleteByCouponAndCategoryIds'
                        );
                    } else {
                        $this->couponCategoryRepository->deleteByCouponId($coupon->id, false);
                    }
                    if ($excludeCategoryIds) {
                        $prepareForNewCategoriesArray = $this->checkCreateOrDeleteCouponProductsAndCategories(
                            $coupon->id,
                            $excludeCategoryIds,
                            'couponCategoryRepository',
                            $prepareForNewCategoriesArray,
                            true,
                            now(),
                            'category_id',
                            'deleteByCouponAndCategoryIds'
                        );
                    } else {
                        $this->couponCategoryRepository->deleteByCouponId($coupon->id, true);
                    }
                    if (!empty($prepareForNewCategoriesArray)) {
                        $issueExists = false;
                        foreach ($prepareForNewCategoriesArray as $couponCategory) {
                            $categoryExists = $this->categoryRepository->checkExistsById($couponCategory['category_id']);

                            if (!$categoryExists) {
                                $issueExists = true;
                                $logs[] = [
                                    'upload_id' => $this->upload->id,
                                    'log' => "<span class='text-danger'>Error at line {$row}</span>: Invalid media ID",
                                ];
                            } else {
                                $this->couponCategoryRepository->insert($couponCategory);
                            }
                        }

                        if ($issueExists) $invalidLines++;
                    }

                    if ($allowedEmails) {
                        $prepareForEmailsArray = $this->checkCreateOrDeleteCouponProductsAndCategories(
                            $coupon->id,
                            $allowedEmails,
                            'couponAllowedEmailRepository',
                            [],
                            null,
                            now(),
                            'email',
                            'deleteByCouponAndEmails'
                        );

                        if (!empty($prepareForEmailsArray)) $this->couponAllowedEmailRepository->insert($prepareForEmailsArray);
                    } else {
                        $this->couponAllowedEmailRepository->deleteByCouponId($coupon->id);
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
            broadcast(new ReloadPagePublic('update-coupons-page'));

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

    private function checkCreateOrDeleteCouponProductsAndCategories(
        int    $couponId,
        array  $ids,
        string $repositoryVariable,
        array  $generalArray,
        ?bool  $isExcluded,
        string $now,
        string $fieldName,
        string $deletingFunctionName
    ): array
    {
        $preparedArray = $generalArray;
        $repository = $this->$repositoryVariable;
        $currentItems = call_user_func_array([$repository, 'fetchIdsByCouponId'], [$couponId, $isExcluded]);
        $itemsToAdd = array_diff($ids, $currentItems);

        foreach ($itemsToAdd as $itemId) {
            $insertingArray = [
                'coupon_id' => $couponId,
                $fieldName => $itemId
            ];

            if ($isExcluded !== null) {
                $insertingArray['is_excluded'] = $isExcluded;
            }

            $preparedArray[] = merge_dates_for_insert($insertingArray, $now);
        }
        $itemsToRemove = array_diff($currentItems, $ids);

        if (!empty($itemsToRemove)) call_user_func_array([$repository, $deletingFunctionName], [$couponId, $itemsToRemove, $isExcluded]);

        return $preparedArray;
    }

    private function validate(array $data, ?int $type, ?bool $excludeSaleItems, ?bool $status, ?bool $freeShipping): array
    {
        if ($this->importType == 2 && empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["ID is important for update"],
            ];
        } else if ($this->importType == 1 && !empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["Inserting coupon can't have ID"],
            ];
        }

        if (is_null($status)) {
            return [
                'success' => false,
                'errors' => ['Invalid "status" value'],
            ];
        }
        if (is_null($type)) {
            return [
                'success' => false,
                'errors' => ['Invalid "type" value'],
            ];
        }
        if (is_null($excludeSaleItems)) {
            return [
                'success' => false,
                'errors' => ['Invalid "Exclude sale items" value'],
            ];
        }
        if (is_null($freeShipping)) {
            return [
                'success' => false,
                'errors' => ['Invalid "Free shipping" value'],
            ];
        }

        $preparedValidationData = [
            'id' => !empty($data[0]) ? $data[0] : null,
            'code' => $data[1],
            'type' => $type,
            'amount' => $data[3],
            'expires_at' => $data[4],
            'min_spend' => $data[5],
            'max_spend' => $data[6],
            'exclude_sale_items' => $excludeSaleItems,
            'usage_limit' => $data[8],
            'usage_limit_per_user' => $data[9],
            'description' => $data[10],
            'free_shipping' => $freeShipping,
            'status' => $status,
            'allowed_emails' => $data[17],
        ];

        $rules = [
            'id' => 'nullable|integer|exists:coupons,id',
            'type' => 'required|integer',
            'amount' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'free_shipping' => 'required|boolean',
            'status' => 'required|boolean',
            'description' => 'nullable|string',
            'expires_at' => 'nullable|string',
            'min_spend' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'max_spend' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'exclude_sale_items' => 'required|boolean',
            'usage_limit' => 'nullable|integer',
            'usage_limit_per_user' => 'nullable|integer',
            'allowed_emails' => ['nullable', 'string', new ValidCouponAllowedEmailsFormat()],
        ];

        if (empty($data[0])) {
            $rules = array_merge($rules, [
                'code' => 'required|string|max:50|unique:coupons,code'
            ]);
        } else {
            $rules = array_merge($rules, [
                'code' => 'required|string|max:50|unique:coupons,code,' . $data[0],
            ]);
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
}
