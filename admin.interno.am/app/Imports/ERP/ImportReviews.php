<?php

namespace App\Imports\ERP;

use App\Constants\ReviewConstants;
use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Models\MediaSetting;
use App\Models\ProductReview;
use App\Models\ProductReviewAttachment;
use App\Models\ProductVariant;
use App\Models\UploadLog;
use App\Models\User;
use App\Repositories\MediaSetting\MediaSettingRepository;
use App\Repositories\ProductReview\ProductReviewRepository;
use App\Repositories\ProductReviewAttachment\ProductReviewAttachmentRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use App\Repositories\User\UserRepository;
use App\Services\ERP\File\FileService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportReviews implements ToCollection
{
    private $upload;
    private string $importType;
    private string $vendorKey;
    private UploadLogRepository $uploadLogRepository;
    private ProductReviewRepository $reviewRepository;
    private ProductReviewAttachmentRepository $reviewAttachmentRepository;
    private UserRepository $userRepository;
    private MediaSettingRepository $mediaSettingRepository;
    private FileService $fileService;
    private ProductVariantRepository $productVariantRepository;
    public function __construct($upload, string $importType, string $vendorKey)
    {
        $this->upload = $upload;
        $this->importType = $importType;
        $this->vendorKey = $vendorKey;
        $this->uploadLogRepository = new UploadLogRepository(new UploadLog());
        $this->reviewRepository = new ProductReviewRepository(new ProductReview());
        $this->reviewAttachmentRepository = new ProductReviewAttachmentRepository(new ProductReviewAttachment());
        $this->userRepository = new UserRepository(new User());
        $this->mediaSettingRepository = new MediaSettingRepository(new MediaSetting());
        $this->productVariantRepository = new ProductVariantRepository(new ProductVariant());
        $this->fileService = new FileService();
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
                    if (count($data) !== count(UploadConstant::REVIEWS_FILE_HEADERS)) {
                        throw new \Exception('Columns count is wrong');
                    }
                    continue;
                }
                $totalLines++;
                $logsLoopNumber++;

                $user = null;
                $userName = null;
                $userEmail = null;

                if (!empty($data[1])) {
                    $user = $this->userRepository->fetchByField('id', $data[1], 'id, name, email', []);
                } else if (!empty($data[4])) {
                    $user = $this->userRepository->fetchByField('email', $data[4], 'id, name, email', []);
                }

                if (!empty($data[2])) {
                    $data[2] = $this->productVariantRepository->getSKUForReviewUpload($data[2]);
                }

                if ($user) {
                    $userName = $user->name;
                    $userEmail = $user->email;
                } else if (!empty($data[4]) || !empty($data[3])) {
                    if (!empty($data[4])) {
                        $userEmail = $data[4];
                    }
                    if (!empty($data[3])) {
                        $userName = $data[3];
                    }
                }

                $status = null;
                if ($data[6] === 'Active') {
                    $status = true;
                } else if ($data[6] === 'Inactive') {
                    $status = false;
                }

                $validator = $this->validate($data, $userName, $userEmail, $status);

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

                $review = null;

                if (!empty($data[0])) {
                    $review = $this->reviewRepository->fetchByField('id', $data[0], 'id');
                }

                DB::beginTransaction();
                try {
                    $reviewPreparedArray = [
                        'user_id' => !empty($data[1]) ? $data[1] : null,
                        'product_id' => $data[2],
                        'name' => $userName,
                        'verified' => true,
                        'email' => $userEmail,
                        'rating' => $data[5],
                        'text' => $data[10],
                        'country_code' => $data[9],
                        'status' => $status,
                        'created_at' => Carbon::parse($data[8])->format('Y-m-d H:i:s'),
                    ];

                    if (!$review) {
                        $this->reviewRepository->insert($reviewPreparedArray);
                        $reviewId = $this->reviewRepository->getLastId();
                    } else {
                        $reviewId = $review->id;
                        $this->reviewRepository->update('id', $reviewId, $reviewPreparedArray);
                    }

                    $attachments = !empty($data[7]) ? explode(',', $data[7]) : [];

                    $attachmentsArray = [];
                    foreach ($attachments as $index => $attachment) {
                        $attachment = $this->prepareAttachment($attachment);

                        if (empty($attachment)) continue;
                        $attachment['priority'] = $index;
                        $attachment['product_review_id'] = $reviewId;

                        $attachmentsArray[] = merge_dates_for_insert($attachment, now());
                    }

                    if (!empty($attachments)) {
                        $this->reviewAttachmentRepository->insert($attachmentsArray);
                    } else {
                        $this->reviewAttachmentRepository->deleteByField('product_review_id', $reviewId);
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
            broadcast(new ReloadPagePublic('update-reviews-page'));
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

    private function validate(array $data, ?string $userName, ?string $userEmail, ?bool $status): array
    {
        if ($this->importType == 2 && empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["ID is important for update"],
            ];
        } else if ($this->importType == 1 && !empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["Inserting review can't have ID"],
            ];
        }

        if (is_null($status)) {
            return [
                'success' => false,
                'errors' => ["Invalid status value"],
            ];
        }

        $preparedValidationData = [
            'id' => $data[0],
            'user_id' => $data[1],
            'product_id' => $data[2],
            'name' => $userName,
            'email' => $userEmail,
            'rating' => $data[5],
            'text' => $data[10],
            'country_code' => $data[9],
            'status' => $status,
            'attachment' => $data[7],
        ];

        $rules = [
            'id' => 'nullable|integer|exists:product_reviews,id',
            'product_id' => 'required|integer|exists:products,id',
            'rating' => 'required|numeric|max:5,regex:/^\d+(\.\d+)?$/',
            'text' => 'required|string',
            'status' => 'required|boolean',
            'attachment' => 'nullable',
            'country_code' => 'required|string|max:10',
        ];

        if (!empty($data[1])) {
            $rules = array_merge($rules, [
                'user_id' => 'required|integer|exists:users,id',
            ]);
        } else {
            $rules = array_merge($rules, [
                'name' => 'required|string|max:60',
                'email' => 'required|string|max:80|email',
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

    private function prepareAttachment($baseMedia): ?array
    {
        $baseMedia = trim($baseMedia);
        if ($baseMedia && str_contains($baseMedia, 'http') && validate_url_for_download_image($baseMedia)) {
            $fileUrl = $baseMedia;
            $file = $this->downloadImageFromUrl($fileUrl);
            if (empty($file)) return null;

            try {
                $originalName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');
                $originalExtension = $file->getClientOriginalExtension();
                $fileType = $file->getClientMimeType();
                $year = Carbon::now()->year;
                $month = Carbon::now()->month;
                $newOriginalName = $originalName . '-' . time();

                if ($this->fileService->imagesSupportedTypes($fileType)) {
                    $type = ReviewConstants::TYPE_IMAGE;
                    $originalPath = "/uploads/$this->vendorKey/images/{$year}/{$month}/";

                    $mediaSetting = $this->mediaSettingRepository->fetch('id, name, width, height', [], [], [], [], []);
                    $path = "/{$year}/{$month}/" . $newOriginalName . '.webp';

                    foreach ($mediaSetting as $setting) {
                        $size = strtolower($setting->name);
                        if ($size === 'large' || $size === 'medium') {
                            continue;
                        }

                        $filePath = "/uploads/$this->vendorKey/images/{$size}/{$year}/{$month}/";
                        $dimensions = [$setting->width, $setting->height];
                        $this->fileService->save($file, $dimensions, $newOriginalName, $filePath, $fileType);

                        if ($fileType != 'image/webp' && $size === 'maximum') {
                            $this->fileService->save($file, $dimensions, $newOriginalName, $originalPath, $fileType, $originalExtension);
                        }

                        if ($fileType === 'image/webp' && $size === 'maximum') {
                            $originalPath = "/uploads/$this->vendorKey/images/$size/{$year}/{$month}/";
                        }
                    }
                } else {
                    $type = ReviewConstants::TYPE_VIDEO;
                    $originalPath = "/uploads/$this->vendorKey/videos/{$year}/{$month}/";
                    $this->fileService->save($file, [], $newOriginalName, $originalPath, $fileType, $originalExtension);
                    $path = $originalPath . $newOriginalName . '.' . $originalExtension;
                }

                return [
                    'path' => $path,
                    'type' => $type,
                ];
            } catch (\Exception $exception) {
                return null;
            }
        } else {
            return [];
        }
    }

    private function downloadImageFromUrl(string $url): ?UploadedFile
    {
        try {
            if ($this->urlExists($url)) {
                $imageContents = file_get_contents($url);
            } else {
                Log::error("Image not found: " . $url);
                return null; // or return a default image
            }
            $tempPath = tempnam(sys_get_temp_dir(), 'uploaded_file');
            file_put_contents($tempPath, $imageContents);

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tempPath);
            finfo_close($finfo);

            return new UploadedFile($tempPath, basename($url), $mimeType, null, true);
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function urlExists($url): bool
    {
        $headers = @get_headers($url);
        return strpos($headers[0], '404') === false;
    }
}
