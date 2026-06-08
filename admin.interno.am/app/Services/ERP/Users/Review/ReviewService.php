<?php

namespace App\Services\ERP\Users\Review;

use App\Constants\LanguageConstants;
use App\Constants\ReviewConstants;
use App\Constants\UploadConstant;
use App\Export\ERP\DefaultExport;
use App\Jobs\UploadReviews;
use App\Repositories\Country\CountryRepository;
use App\Repositories\GeneralSetting\GeneralSettingRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\MediaSetting\MediaSettingRepository;
use App\Repositories\ProductReview\ProductReviewRepository;
use App\Repositories\ProductReviewAttachment\ProductReviewAttachmentRepository;
use App\Repositories\Upload\UploadRepository;
use App\Repositories\User\UserRepository;
use App\Services\ERP\File\FileService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReviewService
{
    private string $exportFilePath = 'app/public/exports';
    private string $exportFileName = 'reviews.xlsx';

    public function __construct(
        ProductReviewRepository           $repository,
        UserRepository                    $userRepository,
        ProductReviewAttachmentRepository $productReviewAttachmentRepository,
        UploadRepository                  $uploadRepository,
        FileService                       $fileService,
        MediaSettingRepository            $mediaSettingRepository,
        LanguageRepository                $languageRepository,
        CountryRepository                 $countryRepository,
    )
    {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
        $this->productReviewAttachmentRepository = $productReviewAttachmentRepository;
        $this->uploadRepository = $uploadRepository;
        $this->fileService = $fileService;
        $this->mediaSettingRepository = $mediaSettingRepository;
        $this->languageRepository = $languageRepository;
        $this->countryRepository = $countryRepository;
        $this->apiBaseUrl = GeneralSettingRepository::getInstance()->getByKeyWithoutLanguage('admin_domain', DB::connection()->getName());
    }

    public function fetch(array $data): JsonResponse
    {
        $select = "id, product_id, name, email, rating, status, is_uploaded, IF(status = 1, 'Active', 'Inactive') AS status_text, IF(is_uploaded = 1, 'Yes', 'No') AS uploaded_text";
        $pagination = prepare_pagination_array($data['page'], $data['per_page']);
        $ordering = [
            'field' => $data['ordering_field'],
            'direction' => $data['ordering_direction']
        ];
        $searchFields = ['name'];

        $data['base_id_of_lang'] = $this->languageRepository->getBaseId();

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
        $select = "id, user_id, product_id, name, email, rating, status, is_uploaded, text, country_code, created_at";

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetchByField('id', $data['id'], $select),
        ]);
    }

    public function insert(array $data): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data['status'] = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);
            $data['verified'] = true;

            $files = $data['files'];
            unset($data['files']);

            if (empty($data['created_at'])) {
                $data['created_at'] = now();
            }
            $data['updated_at'] = now();

            $this->repository->insert($data);
            $reviewId = $this->repository->getLastId();

            if (!empty($files)) {
                $this->media($files, $reviewId);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Successfully created!'
            ]);
        } catch
        (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception);
        }
    }

    public function update(array $data): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data['status'] = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);

            if (!empty($data['removeImages'])) {
                foreach ($data['removeImages'] as $removeImage) {
                    $this->productReviewAttachmentRepository->delete($removeImage);
                }
            }

            if (!empty($data['files'])) {
                $this->media($data['files'], $data['id']);
                unset ($data['files']);
            }

            $updateingData = [
                "user_id" => $data['user_id'],
                "product_id" => $data['product_id'],
                "rating" => $data['rating'],
                "country_code" => $data['country_code'],
                "name" => $data['name'],
                "email" => $data['email'],
                "text" => $data['text'],
                "status" => $data['status'],
                "created_at" => !empty($data['created_at']) ? $data['created_at'] : now(),
            ];

            if ($updateingData['user_id'] === 'null') {
                $updateingData['user_id'] = null;
            }

            unset ($data['removeImages']);
            $this->repository->update('id', $data['id'], $updateingData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully updated!'
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception);
        }
    }

    private function media(array $files, $productReviewId): void
    {
        $now = now()->toDateTimeString();
        $vendorKey = DB::connection()->getName();
        foreach ($files as $k => $file) {

            $originalName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');
            $originalExtension = $file->getClientOriginalExtension();
            $fileType = $file->getClientMimeType();
            $year = Carbon::now()->year;
            $month = Carbon::now()->month;
            $newOriginalName = $originalName . '-' . time();
            if ($this->fileService->imagesSupportedTypes($fileType)) {
                $type = ReviewConstants::TYPE_IMAGE;
                $originalPath = "/uploads/{$vendorKey}/images/{$year}/{$month}/";
                $mediaSetting = $this->mediaSettingRepository->fetch('id, name, width, height', [], [], [], [], []);
                $path = "/{$year}/{$month}/" . $newOriginalName . '.webp';

                foreach ($mediaSetting as $setting) {
                    $size = strtolower($setting->name);
                    if ($size === 'large' || $size === 'medium') {
                        continue;
                    }

                    $filePath = "/uploads/{$vendorKey}/images/{$size}/{$year}/{$month}/";
                    $dimensions = [$setting->width, $setting->height];
                    $this->fileService->save($file, $dimensions, $newOriginalName, $filePath, $fileType);

                    if ($fileType != 'image/webp' && $size === 'maximum') {
                        $this->fileService->save($file, $dimensions, $newOriginalName, $originalPath, $fileType, $originalExtension);
                    }

                    if ($fileType === 'image/webp' && $size === 'maximum') {
                        $originalPath = "/uploads/{$vendorKey}/images/$size/{$year}/{$month}/";
                    }
                }

            } else {
                $type = ReviewConstants::TYPE_VIDEO;
                $originalPath = "/uploads/{$vendorKey}/videos/{$year}/{$month}/";
                $this->fileService->save($file, [], $newOriginalName, $originalPath, $fileType, $originalExtension);
                $path = $originalPath . $newOriginalName . '.' . $originalExtension;
            }

            $mediaImage = merge_dates_for_insert([
                'product_review_id' => $productReviewId,
                'priority' => $k,
                'type' => $type,
                'path' => $path,
            ], $now);


            $this->productReviewAttachmentRepository->insert($mediaImage);
        }
    }

    public function delete(int $id): JsonResponse
    {
        $this->repository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!'
        ]);
    }

    public function fetchParams(array $data): JsonResponse
    {
        if (array_key_exists('id', $data)) {
            $id = (int)$data['id'];
            $languageRelationArray = [
                'relation_name' => 'product_review_translation',
                'select' => "language_id",
                'where_field' => 'product_review_id',
                'id' => $id,
            ];
        } else {
            $languageRelationArray = [];
        }

        $languageParams['status'] = 1;
        $languages = $this->languageRepository->fetch("id, id as value, name as label, code, true as icon", [], [], $languageParams, [], [], $languageRelationArray);
        foreach ($languages as $language) {
            if ($language->product_review_translation) $language->fulled = true;
        }
        $mergedLanguages = array_merge([LanguageConstants::GENERAL_OPTION], $languages->toArray());

        $countries = $this->countryRepository->fetch("code as value, name as label, true as icon, code", [], [
            'field' => 'code',
            'direction' => 'asc',
        ], [], [], []);

        return response()->json(
            [
                'success' => true,
                'users' => [],
                'languages' => $mergedLanguages,
                'countries' => $countries,
                'products' => [],
                'message' => 'Successfully reached!'
            ]
        );
    }

    public function upload(array $data, Request $request): JsonResponse
    {
        $vendorKey = $request->header('VendorKey');

        if (!$vendorKey) {
            $vendorKey = $request->input('vendor_key');
        }

        $file = $data['file'];
        $fileName = $file->getClientOriginalName();

        $filePath = $file->storeAs('uploads/' . $vendorKey . '/temp', $fileName, 'public');
        $user = Auth::user();

        $upload = $this->uploadRepository->create([
            'name' => $fileName,
            'type' => UploadConstant::TYPES['Reviews'],
            'status' => UploadConstant::STATUSES['In process'],
            'total_lines' => 0,
            'invalid_lines' => 0,
            'succeed_lines' => 0,
            'uploaded_by' => $user->name . ' ' . $user->last_name,
            'user_id' => $user->id,
        ]);

        UploadReviews::dispatch($filePath, $upload, $data['import_type'], $vendorKey);

        return response()->json([
            'success' => true,
            'message' => 'Successfully uploaded!'
        ]);
    }

    public function export(array $data): BinaryFileResponse
    {
        if (!file_exists(storage_path($this->exportFilePath))) {
            mkdir(storage_path($this->exportFilePath), 777, true);
        }

        if (file_exists(storage_path("$this->exportFilePath/$this->exportFileName"))) {
            unlink(storage_path("$this->exportFilePath/$this->exportFileName"));
        }

        $collectData = [];

        $vendorKey = DB::connection()->getName();
        $headers = [
            UploadConstant::REVIEWS_FILE_HEADERS['id'],
            UploadConstant::REVIEWS_FILE_HEADERS['user_Id'],
            UploadConstant::REVIEWS_FILE_HEADERS['product_sku'],
            UploadConstant::REVIEWS_FILE_HEADERS['name'],
            UploadConstant::REVIEWS_FILE_HEADERS['email'],
            UploadConstant::REVIEWS_FILE_HEADERS['rating'],
            UploadConstant::REVIEWS_FILE_HEADERS['status'],
            UploadConstant::REVIEWS_FILE_HEADERS['attachment'],
            UploadConstant::REVIEWS_FILE_HEADERS['date'],
            UploadConstant::REVIEWS_FILE_HEADERS['country_code'],
            UploadConstant::REVIEWS_FILE_HEADERS['text'],
        ];

        if (!filter_var($data['justTemplate'], FILTER_VALIDATE_BOOLEAN)) {
            $reviews = $this->repository->fetchForExport($data, $this->apiBaseUrl, $vendorKey);
            foreach ($reviews as $review) {
                $collectData[] = [
                    $review->id,
                    $review->user_Id,
                    $review->product->product_variant_main->sku,
                    $review->name,
                    $review->email,
                    $review->rating,
                    $review->status ? 'Active' : 'Inactive',
                    !empty($review->attachments) ? implode(', ', $review->attachments->pluck('path')->toArray()) : '',
                    $review->created_at,
                    $review->country_code,
                    $review->text,
                ];
            }
        }

        return Excel::download(new DefaultExport(collect($collectData), $headers), $this->exportFileName);
    }
}
