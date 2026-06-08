<?php

namespace App\Imports\ERP;

use App\Constants\PageConstants;
use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Models\Language;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\PostCategoryTranslation;
use App\Models\UploadLog;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\Page\PageRepository;
use App\Repositories\PageTranslation\PageTranslationRepository;
use App\Repositories\PostCategoryTranslation\PostCategoryTranslationRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportPages implements ToCollection
{
    private $upload;
    private string $vendorKey;
    private UploadLogRepository $uploadLogRepository;
    private LanguageRepository $languageRepository;
    private PageRepository $pageRepository;
    private PageTranslationRepository $pageTranslationRepository;
    private string $pageType;
    private PostCategoryTranslationRepository $postCategoryTranslationRepository;
    public function __construct($upload, string $vendorKey, string $pageType)
    {
        $this->upload = $upload;
        $this->vendorKey = $vendorKey;
        $this->pageType = $pageType;
        $this->uploadLogRepository = new UploadLogRepository(new UploadLog());
        $this->pageRepository = new PageRepository(new Page());
        $this->pageTranslationRepository = new PageTranslationRepository(new PageTranslation());
        $this->languageRepository = new LanguageRepository(new Language());
        $this->postCategoryTranslationRepository = new PostCategoryTranslationRepository(new PostCategoryTranslation());
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
                    if (count($data) !== count(UploadConstant::PAGES_FILE_HEADERS)) {
                        throw new \Exception('Columns count is wrong');
                    }
                    continue;
                }
                $totalLines++;
                $logsLoopNumber++;


                if (empty($data[0])) {
                    $logs[] = [
                        'upload_id' => $this->upload->id,
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: Page ID is required",
                    ];
                }

                $page = $this->pageRepository->fetchByFieldWithLanguageForUpload('id', $data[0], 'id, is_home, type', PageConstants::PAGE_TYPES[$this->pageType]);

                $validator = $this->validate($data, $page);

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

                $pageTranslation = null;

                DB::beginTransaction();
                try {
                    try {
                        $languageId = $this->languageRepository->getIdByCode($data[1]);
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
                            'log' => "<span class='text-danger'>Error at line {$row}</span>: Not correct locale: {$data[1]}",
                        ];
                        $invalidLines++;
                        continue;
                    }

                    $pageTranslation = $this->pageTranslationRepository->fetchByPageAndLanguageId($page->id, $languageId, 'id, parent_id, post_category_translation_id');

//                    if ($pageTranslation && $pageTranslation['parent_id']) {
//                        $pathWithBreadcrumb = $this->pageTranslationRepository->generatePathForPage($pageTranslation['parent_id']);
//
//                        $pagePath = "{$pathWithBreadcrumb['path']}";
//                        $pageBreadcrumb = "{$pathWithBreadcrumb['breadcrumb']}";
//                    } else if ($pageTranslation && $page['type'] === PageConstants::PAGE_TYPES['post']) {
//                        $postCategory = $this->postCategoryTranslationRepository->getSlugAndNameById($pageTranslation['post_category_translation_id']);
//                        $pagePath = "/{$postCategory['slug']}";
//                        $pageBreadcrumb = "{$postCategory['name']}";
//                    } else {
//                        $pagePath = '';
//                        $pageBreadcrumb = '';
//                    }

//                    $fullPath = '';
//                    $fullBreadcrumb = '';
//
//                    if ($data[3] || $data[3] === '') {
//                        if ($data[3] === '') {
//                            $fullPath = ltrim("{$pagePath}", '/');
//                            $fullBreadcrumb = "{$pageBreadcrumb}";
//                        } else {
//                            $fullPath = ltrim("{$pagePath}/{$data[3]}", '/');
//                            $fullBreadcrumb = "{$pageBreadcrumb}#&^{$data[2]}";
//                        }
//                    }

                    $pageTranslationPreparedArray = [
                        'page_id' => $page->id,
                        'language_id' => $languageId,
                        'name' => $data[2],
//                        'slug' => $data[3],
                        'meta_title' => $data[4],
                        'meta_keywords' => $data[5],
                        'meta_description' => $data[6],
//                        'path' => $fullPath,
//                        'breadcrumb' => $fullBreadcrumb,
                    ];

                    if (!$pageTranslation) {
                        $this->pageTranslationRepository->create($pageTranslationPreparedArray);
                    } else {
                        $this->pageTranslationRepository->update('id', $pageTranslation->id, $pageTranslationPreparedArray);
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
            broadcast(new ReloadPagePublic('update-pages-page'));
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }
    }

    private function validate(array $data, $page): array
    {
        if (empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["ID is important for update"],
            ];
        }

        $preparedValidationData = [
            'id' => $data[0],
            'language_code' => $data[1],
            'name' => $data[2],
//            'slug' => $data[3],
            'meta_title' => $data[4],
            'meta_keywords' => $data[5],
            'meta_description' => $data[6],
        ];

        $validator = Validator::make($preparedValidationData, [
            'id' => 'nullable|integer|exists:pages,id',
            'language_code' => 'required|exists:languages,code',
            'name' => 'required|string|max:80',
//            'slug' => $page && !$page->is_home ? 'nullable|string|max:250' : 'required|string|max:250',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
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

    public function chunkSize(): int
    {
        return 1000; // Number of rows per chunk
    }

    public function batchSize(): int
    {
        return 1000; // Number of rows per batch
    }
}
