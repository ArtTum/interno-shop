<?php

namespace App\Imports\ERP;

use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Models\CategoryTranslation;
use App\Models\Language;
use App\Models\MenuTranslation;
use App\Models\PageTranslation;
use App\Models\ProductTranslation;
use App\Models\UploadLog;
use App\Repositories\CategoryTranslation\CategoryTranslationRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\MenuTranslation\MenuTranslationRepository;
use App\Repositories\PageTranslation\PageTranslationRepository;
use App\Repositories\ProductTranslation\ProductTranslationRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportMenu implements ToCollection
{
    private $upload;
    private string $importType;
    private string $vendorKey;
    private UploadLogRepository $uploadLogRepository;
    private LanguageRepository $languageRepository;
    private MenuTranslationRepository $menuTranslationRepository;
    private CategoryTranslationRepository $categoryTranslationRepository;
    private ProductTranslationRepository $productTranslationRepository;
    private PageTranslationRepository $pageTranslationRepository;
    public function __construct($upload, string $importType, string $vendorKey)
    {
        $this->upload = $upload;
        $this->importType = $importType;
        $this->vendorKey = $vendorKey;
        $this->uploadLogRepository = new UploadLogRepository(new UploadLog());
        $this->menuTranslationRepository = new MenuTranslationRepository(new MenuTranslation());
        $this->languageRepository = new LanguageRepository(new Language());
        $this->productTranslationRepository = new ProductTranslationRepository(new ProductTranslation());
        $this->categoryTranslationRepository = new CategoryTranslationRepository(new CategoryTranslation());
        $this->pageTranslationRepository = new PageTranslationRepository(new PageTranslation());
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
                    if (count($data) !== count(UploadConstant::MENUS_FILE_HEADERS)) {
                        throw new \Exception('Columns count is wrong');
                    }
                    continue;
                }
                $totalLines++;
                $logsLoopNumber++;

                $newTab = null;
                if ($data[7] === 'Yes') {
                    $newTab = true;
                } else if ($data[7] === 'No') {
                    $newTab = false;
                }

                $type = null;
                if ($data[1] === 'HeaderTop') {
                    $type = 0;
                } else if ($data[1] === 'HeaderBottom') {
                    $type = 1;
                } else if ($data[1] === 'FooterInformation') {
                    $type = 2;
                } else if ($data[1] === 'FooterAccess') {
                    $type = 3;
                }

                $status = null;
                if ($data[8] === 'Active') {
                    $status = true;
                } else if ($data[8] === 'Inactive') {
                    $status = false;
                }

                if (!empty($data[3])) {
                    $data[4] = null;
                    $data[5] = null;
                    $data[6] = null;
                    $data[11] = null;
                } else if (!empty($data[4])) {
                    $data[5] = null;
                    $data[6] = null;
                    $data[11] = null;
                } else if (!empty($data[5])) {
                    $data[6] = null;
                    $data[11] = null;
                } else if (!empty($data[6])) {
                    $data[11] = null;
                }

                try {
                    $languageId = $this->languageRepository->getIdByCode($data[9]);
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
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: Not correct locale: {$data[9]}",
                    ];
                    $invalidLines++;
                    continue;
                }

                if (!empty($data[3])) {
                    $categoryTranslationId = $this->categoryTranslationRepository->getIdByCategoryIdAndLanguageId($data[3], $languageId);
                    if (!$categoryTranslationId) $categoryTranslationId = 999999999999999;
                } else {
                    $categoryTranslationId = null;
                }

                if (!empty($data[4])) {
                    $productTranslationId = $this->productTranslationRepository->getIdByProductIdAndLanguageId($data[4], $languageId);
                    if (!$productTranslationId) $productTranslationId = 999999999999999;
                } else {
                    $productTranslationId = null;
                }

                if (!empty($data[5])) {
                    $pageTranslationId = $this->pageTranslationRepository->getIdByPageIdAndLanguageId($data[5], $languageId);
                    if (!$pageTranslationId) $pageTranslationId = 999999999999999;
                } else {
                    $pageTranslationId = null;
                }

                if (!$pageTranslationId) {
                    if (!empty($data[6])) {
                        $pageTranslationId = $this->pageTranslationRepository->getIdByPageIdAndLanguageId($data[6], $languageId);
                        if (!$pageTranslationId) $pageTranslationId = 999999999999999;
                    }
                }

                $validator = $this->validate(
                    $data, $newTab, $type, $status, $categoryTranslationId, $productTranslationId, $pageTranslationId
                );

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

                $menu = null;

                try {
                    $menuPreparedArray = [
                        'type' => $type,
                        'parent_id' => !empty($data[2]) ? $data[2] : null,
                        'category_translation_id' => $categoryTranslationId,
                        'product_translation_id' => $productTranslationId,
                        'page_translation_id' => $pageTranslationId,
                        'new_tab' => $newTab,
                        'status' => $status,
                        'language_id' => $languageId,
                        'url' => !empty($data[11]) ? $data[11] : null,
                    ];

                    if (!empty($data[10])) {
                        $menuPreparedArray['name'] = $data[10];
                    }

                    if (!empty($data[0])) {
                        $menu = $this->menuTranslationRepository->fetchByField('id', $data[0], 'id');
                    }

                    if ($this->importType == 1) {
                        $this->menuTranslationRepository->insert($menuPreparedArray);
                    } else if ($this->importType == 2) {
                        $this->menuTranslationRepository->update('id', $menu->id, $menuPreparedArray);
                    } else if ($this->importType == 3) {
                        if (!$menu) {
                            $this->menuTranslationRepository->insert($menuPreparedArray);
                        } else {
                            $this->menuTranslationRepository->update('id', $menu->id, $menuPreparedArray);
                        }
                    }

                } catch (\Exception $exception) {
                    $logs[] = [
                        'upload_id' => $this->upload->id,
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: {$exception->getMessage()}",
                    ];
                    $invalidLines++;
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
            broadcast(new ReloadPagePublic('update-menus-page'));
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }
    }

    private function validate(
        array $data, ?bool $newTab, ?int $type, ?bool $status,
        ?int  $categoryTranslationId, ?int $productTranslationId, ?int $pageTranslationId): array
    {
        if ($this->importType == 2 && empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["ID is important for update"],
            ];
        } else if ($this->importType == 1 && !empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["Inserting menu can't have ID"],
            ];
        }

        $preparedValidationData = [
            'id' => $data[0],
            'type' => $type,
            'parent_id' => !empty($data[2]) ? $data[2] : null,
            'category_translation_id' => $categoryTranslationId,
            'product_translation_id' => $productTranslationId,
            'page_translation_id' => $pageTranslationId,
            'new_tab' => $newTab,
            'status' => $status,
            'language_code' => $data[9],
            'name' => !empty($data[10]) ? $data[10] : null,
            'url' => !empty($data[11]) ? $data[11] : null,
        ];

        $rules = [
            'id' => 'nullable|integer|exists:menu_translations,id',
            'type' => 'required|integer',
            'parent_id' => 'nullable|integer|exists:menu_translations,id',
            'category_translation_id' => 'nullable|integer|exists:category_translations,id',
            'product_translation_id' => 'nullable|integer|exists:product_translations,id',
            'page_translation_id' => 'nullable|integer|exists:page_translations,id',
            'new_tab' => 'required|boolean',
            'status' => 'required|boolean',
            'language_code' => 'required|exists:languages,code',
            'url' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255'
        ];

        if (empty($data[3]) && empty($data[4]) && empty($data[5]) && empty($data[6])) {
            $rules['name'] = 'required|string|max:255';
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

    public function chunkSize(): int
    {
        return 1000; // Number of rows per chunk
    }

    public function batchSize(): int
    {
        return 1000; // Number of rows per batch
    }
}
