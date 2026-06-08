<?php

namespace App\Services\ERP\Media;

use App\Constants\MediaConstants;
use App\Constants\UploadConstant;
use App\Export\ERP\DefaultExport;
use App\Jobs\Media\MediaTranslateAI;
use App\Jobs\Media\TranslationTranslateAI;
use App\Jobs\UploadMedia;
use App\Models\UserActionHistory;
use App\Repositories\Attribute\AttributeRepository;
use App\Repositories\GeneralSetting\GeneralSettingRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\Media\MediaRepository;
use App\Repositories\MediaSetting\MediaSettingRepository;
use App\Repositories\MediaTranslation\MediaTranslationRepository;
use App\Repositories\Product\ProductRepository;
use App\Repositories\Upload\UploadRepository;
use App\Services\ERP\File\FileService;
use App\Services\ERP\Media\Interfaces\MediaServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaService implements MediaServiceInterface
{
    private string $exportFilePath = 'app/public/exports';

    private string $exportFileName = 'media.xlsx';

    public function __construct(
        MediaRepository            $repository,
        MediaSettingRepository     $mediaSettingRepository,
        MediaTranslationRepository $mediaTranslationRepository,
        FileService                $fileService,
        LanguageRepository         $languageRepository,
        UploadRepository           $uploadRepository,
        ProductRepository          $productRepository,
        AttributeRepository        $attributeRepository,
        GeneralSettingRepository      $generalSettingRepository,
    )
    {
        $this->repository = $repository;
        $this->mediaSettingRepository = $mediaSettingRepository;
        $this->mediaTranslationRepository = $mediaTranslationRepository;
        $this->fileService = $fileService;
        $this->languageRepository = $languageRepository;
        $this->uploadRepository = $uploadRepository;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->genralSettingRepository = $generalSettingRepository;
    }

    public function fetch(array $data): JsonResponse
    {
        $select = "media.id, users.name, users.last_name, file_name, path, original_path, file_type, type, file_size, width, height, media.created_at";
        $pagination = prepare_pagination_array($data['page'], $data['per_page']);
        $searchFields = ['file_name'];
        $joins = [['users', 'media.user_id', '=', 'users.id']];
        $ordering = ['field' => 'id', 'direction' => 'DESC'];
        $baseLanguageId = $this->languageRepository->getBaseId();

        $data['base_language_id'] = isset($data['language_id']) && $data['language_id'] > 0 ? $data['language_id'] : $baseLanguageId;

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetch($select, $pagination, $ordering, $data, $searchFields, $joins),
            'base_language_id' => $baseLanguageId,
            'languages' => $this->languageRepository->fetch("id, id as value, name as label, code, true as icon", [], [], ['status' => 1], [], []),
            'pagination' => prepare_pagination(
                $data['page'], $data['per_page'],
                $this->repository->fetchTotalCount($select, $pagination, [], $data, $searchFields, [])
            )
        ]);
    }

    public function fetchByField(array $data): JsonResponse
    {
        $select = "id, media_id, alt";

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->mediaTranslationRepository->fetchByFieldLanguage('media_id', $data['media_id'], $select, $data)
        ]);
    }

    public function insert(array $data): JsonResponse
    {
        $file = $data['file'];
        $vendorKey = DB::connection()->getName();
        try {
            $originalName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');
            $originalExtension = $file->getClientOriginalExtension();
            $fileName = $this->repository->fetchByField('file_name', $originalName . '.' . $originalExtension, 'id, file_name');

            if (empty($fileName)) {
                $newOriginalName = $this->generateUniqueFilename($originalName, $originalExtension);
                $fileSize = $file->getSize();
                $fileType = $file->getClientMimeType();

                $year = Carbon::now()->year;
                $month = Carbon::now()->month;

                if ($this->fileService->imagesSupportedTypes($fileType)) {
                    $originalPath = "/uploads/{$vendorKey}/images/{$year}/{$month}/";
                    $image = Image::make($file->getRealPath());

                    $width = $image->width();
                    $height = $image->height();
                    $mediaSetting = $this->mediaSettingRepository->fetch('id, name, width, height', [], [], [], [], []);
                    $path = "/{$year}/{$month}/" . $newOriginalName . '.webp';
                    $type = MediaConstants::TYPE_IMAGE;

                    foreach ($mediaSetting as $setting) {
                        $size = strtolower($setting->name);
                        $filePath = "/uploads/{$vendorKey}/{$type}/{$size}/{$year}/{$month}/";
                        $dimensions = [$setting->width, $setting->height];
                        $this->fileService->save($file, $dimensions, $newOriginalName, $filePath, $fileType);

                        if ($fileType != 'image/webp' && $size === 'maximum') {
                            $this->fileService->save($file, $dimensions, $newOriginalName, $originalPath, $fileType, $originalExtension);
                        }

                        if ($fileType === 'image/webp' && $size === 'maximum') {
                            $originalPath = "/uploads/{$vendorKey}/$type/$size/{$year}/{$month}/";
                        }
                    }

                } else {
                    $type = MediaConstants::TYPE_FILE;
                    if (str_starts_with($fileType, 'video/')) {
                        $type = MediaConstants::TYPE_VIDEO;
                    } elseif (str_starts_with($fileType, 'image/')) {
                        $type = MediaConstants::TYPE_IMAGE;
                    }

                    $originalPath = "/uploads/{$vendorKey}/$type/{$year}/{$month}/";
                    $this->fileService->save($file, [], $newOriginalName, $originalPath, $fileType, $originalExtension);
                }

                $now = now()->toDateTimeString();
                $mediaData = merge_dates_for_insert([
                    'user_id' => auth()->id(),
                    'file_name' => $newOriginalName . '.' . $originalExtension,
                    'original_path' => $originalPath . $newOriginalName . '.' . $originalExtension,
                    'path' => $path ?? null,
                    'file_size' => $fileSize,
                    'width' => $width ?? null,
                    'height' => $height ?? null,
                    'file_type' => $fileType,
                    'type' => $type,
                ], $now);

                $this->repository->insert($mediaData);

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully created!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'This file already exists'
            ], 500);
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }
    }

    public function replaceImage(array $data): JsonResponse
    {
        $file = $data['file'];
        $id = $data['media_id'];
        $vendorKey = DB::connection()->getName();

        try {
            $media = $this->repository->fetchByField('id', $id, 'id, file_name, original_path, path');
            $newOriginalName = pathinfo($media->file_name, PATHINFO_FILENAME);
            $originalExtension = pathinfo($media->file_name, PATHINFO_EXTENSION);
            $originalPath = str_replace(basename($media->original_path), '', $media->original_path);
            $yearAndMonth = str_replace(basename($media->path), '', $media->path);
            $type = MediaConstants::TYPE_IMAGE;
            $fileSize = $file->getSize();
            $fileType = $file->getClientMimeType();

            if ($this->fileService->imagesSupportedTypes($fileType)) {
                $image = Image::make($file->getRealPath());
                $width = $image->width();
                $height = $image->height();
                $mediaSetting = $this->mediaSettingRepository->fetch('id, name, width, height', [], [], [], [], []);

                foreach ($mediaSetting as $setting) {
                    $size = strtolower($setting->name);
                    $filePath = "/uploads/{$vendorKey}/{$type}/{$size}{$yearAndMonth}";
                    $dimensions = [$setting->width, $setting->height];
                    $this->fileService->save($file, $dimensions, $newOriginalName, $filePath, $fileType);

                    if ($fileType != 'image/webp' && $size === 'maximum') {
                        $this->fileService->save($file, $dimensions, $newOriginalName, $originalPath, $fileType, $originalExtension);
                    }

                    if ($fileType === 'image/webp' && $size === 'maximum') {
                        $originalPath = "/uploads/{$vendorKey}/{$type}/{$size}{$yearAndMonth}";
                    }
                }

            } else {
                $this->fileService->save($file, [], $newOriginalName, $originalPath, $fileType, $originalExtension);
            }

            $now = now()->toDateTimeString();
            $mediaData = merge_dates_for_insert([
                'user_id' => auth()->id(),
                'file_size' => $fileSize,
                'width' => $width ?? null,
                'height' => $height ?? null,
            ], $now);

            $this->repository->update('id', $id, $mediaData);

            return response()->json([
                'success' => true,
                'message' => 'Successfully replace!'
            ]);

        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }
    }

    private function generateUniqueFilename(string $originalName, string $extension): string
    {
        $newOriginalName = $originalName;
        $filename = $originalName . '.' . $extension;
        $counter = 1;

        while ($this->repository->fetchExists('file_name', $filename)) {
            $newOriginalName = $originalName . '-' . $counter;
            $filename = $newOriginalName . '.' . $extension;
            $counter++;
        }

        return $newOriginalName;
    }

    public function update(array $data): JsonResponse
    {
        UserActionHistory::create([
            'user_id' => Auth::user()?->id,
            'author' => Auth::user()?->name,
            'type' => 'update media',
            'description' => json_encode($data),
        ]);

        $prepareData['media_id'] = $data['media_id'];
        $prepareData['language_id'] = $data['language_id'];
        $prepareData['alt'] = $data['alt'] ?? '';

        if (!empty($data['id'])) {
            $this->mediaTranslationRepository->update('id', $data['id'], $prepareData);
        } else {
            $now = now()->toDateTimeString();
            $prepareData = merge_dates_for_insert($prepareData, $now);
            $this->mediaTranslationRepository->insert($prepareData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!'
        ]);
    }

    public function translateAI(array $data): JsonResponse
    {
        $vendorKey = DB::connection()->getName();
        $gptToken = $this->genralSettingRepository->getByKeyWithoutLanguage('chat_gpt_token', $vendorKey);

        MediaTranslateAI::dispatch($vendorKey, $gptToken, $data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully submitted!',
        ]);
    }

    public function delete(array $ids): JsonResponse
    {
        $mediaSetting = $this->mediaSettingRepository->fetch('id, name', [], [], [], [], []);
        $vendorKey = DB::connection()->getName();

        foreach ($ids as $id) {
            $media = $this->repository->fetchByField('id', $id, 'id, type, file_name, path, original_path');
            $originalPath = ($media->original_path);

            if (Storage::disk('public')->exists($originalPath)) {
                Storage::disk('public')->delete($originalPath);
            }

            foreach ($mediaSetting as $mediaSize) {
                if (!empty($media->path)) {
                    $size = strtolower($mediaSize->name);
                    $path = ("/uploads/{$vendorKey}/$media->type/$size/$media->path");

                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
            }

            $this->repository->delete($id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully delete!'
        ]);
    }

    public function fetchParams(array $data): JsonResponse
    {
        if (array_key_exists('id', $data)) {
            $id = (int)$data['id'];
            $languageRelationArray = [
                'relation_name' => 'media_translation',
                'select' => "language_id",
                'where_field' => 'media_id',
                'id' => $id,
            ];
        } else {
            $languageRelationArray = [];
        }

        $languageParams['status'] = 1;
        $languages = $this->languageRepository->fetch("id, id as value, name as label, code, true as icon", [], [], $languageParams, [], [], $languageRelationArray);
        foreach ($languages as $language) {
            if ($language->media_translation) $language->fulled = true;
        }

        return response()->json([
            'success' => true,
            'languages' => $languages,
            'language_id' => $this->languageRepository->getBaseId(),
            'message' => 'Successfully reached!'
        ]);
    }

    public function showSquareImageFromUrl($imageUrl)
    {
        $imageUrl = config('app.url') . '/' . $imageUrl;
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'Invalid URL'], 400);
        }

        try {
            $image = Image::make($imageUrl);
            $smallestSide = min($image->width(), $image->height());
            $image->resizeCanvas($smallestSide, $smallestSide, 'center', false, 'ffffff');
            return $image->response();
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }
    }

    public function overlay(): JsonResponse
    {
        try {
            $backgroundUrl = '/uploads/images/maximum/2024/11/svetlofon-traffic-white.webp';
            $overlayUrl = public_path('uploads/microcement-watermark.png');
            $background = Image::make($backgroundUrl);
            $background->resize(800, 800);


            $overlay = Image::make($overlayUrl);
            $overlay->resize(null, 800, function ($constraint) {
                $constraint->aspectRatio();
            });
            $background->insert($overlay, 'bottom-left', -100, -40);

            $fileName = 'final_' . '.png';
            $filePath = 'public/uploads/' . $fileName;
            $background->save(storage_path('app/' . $filePath));

            return response()->json([
                'message' => 'Image overlay created successfully',
                'url' => asset("/uploads/{$fileName}")
            ]);
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }
    }

    public function overlayWatermark($params)
    {
        $params = json_decode($params, true);

        try {
            $backgroundUrl = '/uploads/images/maximum/2024/11/svetlofon-traffic-white.webp';
            $overlayUrl = public_path($params['watermark_media']);
            $background = Image::make($backgroundUrl);
            $background->resize(800, 800);

            $overlay = Image::make($overlayUrl);
            $overlay->resize(null, $params['watermark_height'], function ($constraint) {
                $constraint->aspectRatio();
            });
            $background->insert($overlay, $params['watermark_position'], $params['watermark_x'], $params['watermark_y']);

            $fileName = 'final_' . '.png';
            $filePath = 'public/uploads/' . $fileName;
            $background->save(storage_path('app/' . $filePath));

            return $background->response();
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }
    }

    public function watermarkImage(string $vendor_key, int $product_id, int $attribute_id, $language_id)
    {
        try {
            DB::setDefaultConnection($vendor_key);
            $attribute = $this->attributeRepository->fetchByWatermarkImage('id', $attribute_id, 'id, media_id');
            $product = $this->productRepository->fetchByWatermarkImage('id', $product_id, $language_id, 'id, watermark_settings');
            $watermarkMedia = $product->product_translation->watermark_settings['watermark_media'] ?? '';

            $fileName = $product_id . '-' . $attribute_id . ($watermarkMedia ? '-' . $language_id : '')
                . '-' . $product->watermark_settings['watermark_height']
                . '-' . $product->watermark_settings['watermark_position']
                . '-' . $product->watermark_settings['watermark_x']
                . '-' . $product->watermark_settings['watermark_y']
                . '.png';

            $directory = 'uploads/' . $vendor_key . '/watermark/';
            $filePath = $directory . $fileName;

            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            if (Storage::disk('public')->exists('uploads/' . $vendor_key . '/watermark/' . $fileName)) {
                $background = Image::make(storage_path('app/public/' . $filePath));
                return $background->response();
            }

            $backgroundUrl = str_replace('\/', '/', public_path($attribute->media->path));
            $overlayUrl = str_replace('\/', '/', public_path($watermarkMedia ?: $product->watermark_settings['watermark_media']));

            $background = Image::make($backgroundUrl);
            $background->resize(800, 800);

            $overlay = Image::make($overlayUrl);
            $overlay->resize(null, $product->watermark_settings['watermark_height'], function ($constraint) {
                $constraint->aspectRatio();
            });
            $background->insert($overlay, $product->watermark_settings['watermark_position'], $product->watermark_settings['watermark_x'], $product->watermark_settings['watermark_y']);
            $background->save(storage_path('app/public/' . $filePath));

            return $background->response();
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }
    }

    public function export(array $data): BinaryFileResponse
    {
        $this->apiBaseUrl = GeneralSettingRepository::getInstance()->getByKeyWithoutLanguage('admin_domain', DB::connection()->getName());
        if (!file_exists(storage_path($this->exportFilePath))) {
            mkdir(storage_path($this->exportFilePath), 777, true);
        }

        if (file_exists(storage_path("$this->exportFilePath/$this->exportFileName"))) {
            unlink(storage_path("$this->exportFilePath/$this->exportFileName"));
        }

        $collectData = [];

        $headers = [
            UploadConstant::MEDIA_FILE_HEADERS['id'],
            UploadConstant::MEDIA_FILE_HEADERS['url'],
            UploadConstant::MEDIA_FILE_HEADERS['language_code'],
            UploadConstant::MEDIA_FILE_HEADERS['alt']
        ];

        if (!filter_var($data['justTemplate'], FILTER_VALIDATE_BOOLEAN)) {
            $data['ordering_field'] = 'media.id';
            $data['ordering_direction'] = 'desc';

            $menus = $this->repository->fetchForExport($data);
            $languageCode = $this->languageRepository->getCodeById($data['language_id']);
            $menus = $menus ? $menus->toArray() : [];

            $menusChunked = array_chunk($menus, 100);

            foreach ($menusChunked as $menuChunked) {
                foreach ($menuChunked as $menu) {
                    $collectData[] = [
                        $menu['id'],
                        "{$this->apiBaseUrl}{$menu['original_path']}",
                        $languageCode,
                        $menu['alt']
                    ];
                }
            }
        }

        return Excel::download(new DefaultExport(collect($collectData), $headers), $this->exportFileName);
    }

    public function uploadFile(array $data, Request $request): JsonResponse
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
            'type' => UploadConstant::TYPES['Media'],
            'status' => UploadConstant::STATUSES['In process'],
            'total_lines' => 0,
            'invalid_lines' => 0,
            'succeed_lines' => 0,
            'uploaded_by' => $user->name . ' ' . $user->last_name,
            'user_id' => $user->id,
        ]);

        UploadMedia::dispatch($filePath, $upload, $vendorKey);

        return response()->json([
            'success' => true,
            'message' => 'Successfully uploaded!'
        ]);
    }
}
