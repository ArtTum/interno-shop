<?php

namespace App\Imports\ERP;

use App\Constants\MediaConstants;
use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Models\Language;
use App\Models\Media;
use App\Models\MediaSetting;
use App\Models\UploadLog;
use App\Repositories\Attribute\AttributeRepository;
use App\Repositories\AttributeTranslation\AttributeTranslationRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\Media\MediaRepository;
use App\Repositories\MediaSetting\MediaSettingRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use App\Services\ERP\File\FileService;
use App\Services\General\CustomSlugService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportAttributes implements ToCollection
{
    private $upload;
    private string $importType;
    private string $vendorKey;
    private UploadLogRepository $uploadLogRepository;
    private AttributeRepository $attributeRepository;
    private LanguageRepository $languageRepository;
    private AttributeTranslationRepository $attributeTranslationRepository;
    private MediaRepository $mediaRepository;
    private FileService $fileService;
    private MediaSettingRepository $mediaSettingRepository;
    private int $userId;
    public function __construct($upload, string $importType, string $vendorKey, $userId)
    {
        $this->upload = $upload;
        $this->importType = $importType;
        $this->vendorKey = $vendorKey;
        $this->userId = $userId;
        $this->uploadLogRepository = new UploadLogRepository(new UploadLog());
        $this->attributeRepository = new AttributeRepository(new Attribute());
        $this->attributeTranslationRepository = new AttributeTranslationRepository(new AttributeTranslation());
        $this->languageRepository = new LanguageRepository(new Language());
        $this->mediaRepository = new MediaRepository(new Media());
        $this->fileService = new FileService();
        $this->mediaSettingRepository = new MediaSettingRepository(new MediaSetting());
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
                    if (count($data) !== count(UploadConstant::ATTRIBUTE_FILE_HEADERS)) {
                        throw new \Exception('Columns count is wrong');
                    }
                    continue;
                }
                $totalLines++;
                $logsLoopNumber++;


                $data[1] = $this->prepareBaseMedia($data[1]);
                if (empty($data[1])) $data[1] = null;

                $validator = $this->validate($data);

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

                $attribute = null;
                $attributeTranslation = null;

                DB::beginTransaction();
                try {
                    try {
                        $languageId = $this->languageRepository->getIdByCode($data[4]);
                    } catch (\Exception $exception) {
                        $logs[] = [
                            'upload_id' => $this->upload->id,
                            'log' => "<span class='text-danger'>Error at line {$row}</span>: {$exception->getMessage()}",
                        ];
                        $invalidLines++;
                        continue;
                    }

                    if (!$languageId) {
                        $logs[] = [
                            'upload_id' => $this->upload->id,
                            'log' => "<span class='text-danger'>Error at line {$row}</span>: Not correct locale: {$data[4]}",
                        ];
                        $invalidLines++;
                        continue;
                    }

                    $attributePreparedArray = [
                        'media_id' => !empty($data[1]) ? $data[1] : null,
                        'attribute_type_id' => $data[2],
                        'priority' => $data[3],
                    ];

                    if (!empty($data[0])) {
                        $attribute = $this->attributeRepository->fetchByFieldWithLanguage('id', $data[0], 'id', $languageId);
                    }

                    if (!$attribute) {
                        $attribute = $this->attributeRepository->create($attributePreparedArray);
                    } else {
                        unset($attributePreparedArray['attribute_type_id']);
                        $this->attributeRepository->update('id', $attribute->id, $attributePreparedArray);
                    }

                    $attributeTranslation = $this->attributeTranslationRepository->fetchByAttributeAndLanguageId($attribute->id, $languageId, 'id, slug');

                    if ($this->attributeRepository->checkUniqingByAttributeType($data[2], $data[5], $languageId, $attributeTranslation?->id)) {
                        $logs[] = [
                            'upload_id' => $this->upload->id,
                            'log' => "<span class='text-danger'>Error at line {$row}</span>: There is attribute value with this name for this language and attribute",
                        ];
                        $invalidLines++;
                        DB::rollBack();
                        continue;
                    }

                    $attributeTranslationPreparedArray = [
                        'attribute_id' => $attribute->id,
                        'language_id' => $languageId,
                        'value' => $data[5],
                        'description' => $data[7],
                    ];

                    if ($attributeTranslation) {
                        if ($attributeTranslation->slug !== $data[6] || empty($data[5])) {
                            $slugValue = !empty($data[6]) ? $data[6] : $data[5];
                            $slug = CustomSlugService::createCustomSlug(
                                new AttributeTranslation(),
                                $slugValue,
                                $languageId
                            );
                        } else {
                            $slug = $data[6];
                        }
                    } else {
                        $slugValue = !empty($data[6]) ? $data[6] : $data[5];
                        $slug = CustomSlugService::createCustomSlug(
                            new AttributeTranslation(),
                            $slugValue,
                            $languageId
                        );
                    }

                    $attributeTranslationPreparedArray['slug'] = $slug;

                    if ($this->importType == 1) {
                        $this->attributeTranslationRepository->insert($attributeTranslationPreparedArray);
                    } else if ($this->importType == 2 || $this->importType == 3) {
                        if (!$attributeTranslation) {
                            $this->attributeTranslationRepository->insert($attributeTranslationPreparedArray);
                        } else {
                            unset($attributeTranslationPreparedArray['slug']);
                            $this->attributeTranslationRepository->update('id', $attributeTranslation->id, $attributeTranslationPreparedArray);
                        }
                    }

                    DB::commit();
                } catch (\Exception $exception) {
                    Log::error("Job failed during execution: {$exception}");
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
            broadcast(new ReloadPagePublic('update-attributes-page'));
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

    private function urlExists($url): bool
    {
        $headers = @get_headers($url);
        return strpos($headers[0], '404') === false;
    }

    private function prepareBaseMedia($baseMedia)
    {
        $baseMedia = trim($baseMedia);
        if ($baseMedia && str_contains($baseMedia, 'http') && validate_url_for_download_image($baseMedia)) {
            $fileUrl = $baseMedia;
            $fileInfoArray = pathinfo(basename($fileUrl));

            $originalName = Str::slug($fileInfoArray['filename'], '-');
            $originalExtension = $fileInfoArray['extension'];

            try {
                $newOriginalName = $originalName;

                $mediaExists = $this->mediaRepository->fetchByField('file_name', "$originalName.$originalExtension", 'id, file_name');
                if ($mediaExists) return $mediaExists->id;

                $file = $this->downloadImageFromUrl($fileUrl);
                if (empty($file)) return null;

                $fileSize = $file->getSize();
                $fileType = $file->getClientMimeType();

                $year = Carbon::now()->year;
                $month = Carbon::now()->month;

                if ($this->fileService->imagesSupportedTypes($fileType)) {
                    $originalPath = "/uploads/$this->vendorKey/images/{$year}/{$month}/";
                    $image = Image::make($file->getRealPath());

                    $width = $image->width();
                    $height = $image->height();
                    $mediaSetting = $this->mediaSettingRepository->fetch('id, name, width, height', [], [], [], [], []);
                    $path = "/{$year}/{$month}/" . $newOriginalName . '.webp';
                    $type = MediaConstants::TYPE_IMAGE;

                    foreach ($mediaSetting as $setting) {
                        $size = strtolower($setting->name);
                        $filePath = "/uploads/$this->vendorKey/{$type}/{$size}/{$year}/{$month}/";
                        $dimensions = [$setting->width, $setting->height];
                        $this->fileService->save($file, $dimensions, $newOriginalName, $filePath, $fileType);

                        if ($fileType != 'image/webp' && $size === 'maximum') {
                            $this->fileService->save($file, $dimensions, $newOriginalName, $originalPath, $fileType, $originalExtension);
                        }

                        if ($fileType === 'image/webp' && $size === 'maximum') {
                            $originalPath = "/uploads/$this->vendorKey/$type/$size/{$year}/{$month}/";
                        }
                    }
                } else {
                    $type = MediaConstants::TYPE_FILE;
                    if (str_starts_with($fileType, 'video/')) {
                        $type = MediaConstants::TYPE_VIDEO;
                    } elseif (str_starts_with($fileType, 'image/')) {
                        $type = MediaConstants::TYPE_IMAGE;
                    }

                    $originalPath = "/uploads/$this->vendorKey/$type/{$year}/{$month}/";
                    $this->fileService->save($file, [], $newOriginalName, $originalPath, $fileType, $originalExtension);
                }

                $now = now()->toDateTimeString();
                $mediaData = merge_dates_for_insert([
                    'user_id' => $this->userId,
                    'file_name' => $newOriginalName . '.' . $originalExtension,
                    'original_path' => $originalPath . $newOriginalName . '.' . $originalExtension,
                    'path' => $path ?? null,
                    'file_size' => $fileSize,
                    'width' => $width ?? null,
                    'height' => $height ?? null,
                    'file_type' => $fileType,
                    'type' => $type,
                ], $now);

                $media = $this->mediaRepository->create($mediaData);

                return $media->id;
            } catch (\Exception $exception) {
                return null;
            }
        } else {
            return $baseMedia;
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

    private function validate(array $data): array
    {
        if ($this->importType == 2 && empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["ID is important for update"],
            ];
        } else if ($this->importType == 1 && !empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["Inserting attribute value can't have ID"],
            ];
        }

        $preparedValidationData = [
            'id' => $data[0],
            'media_id' => !empty($data[1]) ? $data[1] : null,
            'attribute_type_id' => !empty($data[2]) ? $data[2] : null,
            'language_code' => $data[4],
            'value' => $data[5],
            'description' => $data[7],
            'priority' => $data[3],
            'slug' => $data[6],
        ];

        $validator = Validator::make($preparedValidationData, [
            'id' => 'nullable|integer|exists:attributes,id',
            'media_id' => 'nullable|integer|exists:media,id',
            'attribute_type_id' => 'required|integer|exists:attribute_types,id',
            'language_code' => 'required|exists:languages,code',
            'value' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'priority' => 'required|integer',
            'slug' => 'nullable|string|max:250',
        ]);

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
