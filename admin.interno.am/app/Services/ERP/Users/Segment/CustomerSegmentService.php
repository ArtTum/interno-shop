<?php

namespace App\Services\ERP\Users\Segment;

use App\Export\ERP\DefaultExport;
use App\Jobs\Marketing\UpdateCustomersBySegment;
use App\Repositories\AbandonedEmail\AbandonedEmailRepository;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\Country\CountryRepository;
use App\Repositories\CustomerSegment\CustomerSegmentRepository;
use App\Repositories\CustomerSegmentUser\CustomerSegmentUserRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\Product\ProductRepository;
use App\Repositories\User\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerSegmentService
{
    private string $exportFilePath = 'app/public/exports';
    private string $exportFileName = 'imported-customers.xlsx';
    private array $structuredCategories = [];

    public function __construct(
        CustomerSegmentRepository     $repository,
        CustomerSegmentUserRepository $customerSegmentUserRepository,
        CountryRepository             $countryRepository,
        LanguageRepository            $languageRepository,
        ProductRepository             $productRepository,
        CategoryRepository            $categoryRepository,
        UserRepository                $userRepository,
        AbandonedEmailRepository      $abandonedEmailRepository,
    )
    {
        $this->repository = $repository;
        $this->customerSegmentUserRepository = $customerSegmentUserRepository;
        $this->countryRepository = $countryRepository;
        $this->languageRepository = $languageRepository;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->userRepository = $userRepository;
        $this->abandonedEmailRepository = $abandonedEmailRepository;
    }

    public static function getInstance()
    {
        return app(self::class);
    }

    public function fetch(array $data): JsonResponse
    {
        $select = "id, name, in_progress";
        $pagination = prepare_pagination_array($data['page'], $data['per_page']);
        $ordering = [
            'field' => $data['ordering_field'],
            'direction' => $data['ordering_direction']
        ];
        $searchFields = ['name'];
        $joins = [];

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetch($select, $pagination, $ordering, $data, $searchFields, $joins),
            'pagination' => prepare_pagination(
                $data['page'], $data['per_page'],
                $this->repository->fetchTotalCount($select, $pagination, $ordering, $data, $searchFields, $joins)
            )
        ]);
    }

    public function fetchByField(array $data): JsonResponse
    {
        $select = "id, name, criteria, cache_hours, expire_date, in_progress";
        $response = $this->repository->fetchByField('id', $data['id'], $select);

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $response,
        ]);
    }

    public function insert(array $data): JsonResponse
    {
        DB::beginTransaction();
        try {
            $vendorKey = DB::connection()->getName();

            if (!empty($data['criteria']['avg_revenue_from']) || (isset($data['criteria']['avg_revenue_from']) && $data['criteria']['avg_revenue_from'] == 0)) {
                $data['criteria']['avg_revenue_from'] = extractFormattedNumber($data['criteria']['avg_revenue_from']);
            }

            if (!empty($data['criteria']['avg_revenue_to']) || (isset($data['criteria']['avg_revenue_to']) && $data['criteria']['avg_revenue_to'] == 0)) {
                $data['criteria']['avg_revenue_to'] = extractFormattedNumber($data['criteria']['avg_revenue_to']);
            }

            $customerSegment = $this->repository->create([
                'criteria' => $data['criteria'],
                'name' => $data['name'],
                'cache_hours' => $data['cache_hours'],
                'in_progress' => true,
                'expire_date' => Carbon::now()->addHours((int) $data['cache_hours']),
            ]);

            if ($data['file']) {
                $file = $data['file'];
                $fileName = $file->getClientOriginalName();

                $filePath = $file->storeAs('uploads/' . $vendorKey . '/temp', $fileName, 'public');
            } else {
                $filePath = null;
            }

            UpdateCustomersBySegment::dispatch($vendorKey, $customerSegment->id, $filePath);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully created!',
                'id' => $customerSegment->id,
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();

            throw new \Exception($exception);
        }
    }

    public function update(array $data): JsonResponse
    {
        DB::beginTransaction();
        $vendorKey = DB::connection()->getName();

        try {
            if (!empty($data['criteria']['avg_revenue_from']) || (isset($data['criteria']['avg_revenue_from']) && $data['criteria']['avg_revenue_from'] == 0)) {
                $data['criteria']['avg_revenue_from'] = extractFormattedNumber($data['criteria']['avg_revenue_from']);
            }

            if (!empty($data['criteria']['avg_revenue_to']) || (isset($data['criteria']['avg_revenue_to']) && $data['criteria']['avg_revenue_to'] == 0)) {
                $data['criteria']['avg_revenue_to'] = extractFormattedNumber($data['criteria']['avg_revenue_to']);
            }

            $this->repository->update('id', $data['id'], [
                'criteria' => $data['criteria'],
                'name' => $data['name'],
                'cache_hours' => $data['cache_hours'],
                'in_progress' => true,
                'expire_date' => Carbon::now()->addHours((int) $data['cache_hours']),
            ]);

            if ($data['file']) {
                $file = $data['file'];
                $fileName = $file->getClientOriginalName();

                $filePath = $file->storeAs('uploads/' . $vendorKey . '/temp', $fileName, 'public');
            } else {
                $filePath = null;
            }

            UpdateCustomersBySegment::dispatch($vendorKey, $data['id'], $filePath);

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

    public function delete(int $id): JsonResponse
    {
        $this->repository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!'
        ]);
    }

    public function fetchParams(): JsonResponse
    {
        $languageId = $this->languageRepository->getBaseId();
        $products = $this->productRepository->fetchAsProductParam($languageId);

        $categories = $this->categoryRepository->getWithAllDescendants($languageId);
        if (!empty($categories)) $this->buildCategoryHierarchy($categories, 0);

        return response()->json(['success' => true,
            'countries' => $this->countryRepository->fetch("id as value, code as icon_code, name as label, true as icon", [], [
                'field' => 'code',
                'direction' => 'asc',
            ], [], [], []),
            'products' => $products,
            'languages' => $this->languageRepository->fetch("id, id as value, name as label, code, true as icon", [], [], ['status' => 1], [], []),
            'message' => 'Successfully reached!',
            'categories' => $this->structuredCategories
        ]);
    }

    private function buildCategoryHierarchy(object $categories, int $level): void
    {
        $lines = str_repeat('-   ', $level);

        foreach ($categories as $category) {
            if (!$category->category_translation_base) continue;
            $this->structuredCategories[] = [
                'label' => $lines . $category->category_translation_base->name,
                'translation_id' => $category->category_translation_base->id,
                'value' => $category->id,
            ];

            if ($category->children->isNotEmpty()) {
                $this->buildCategoryHierarchy($category->children, $level + 1);
            }
        }
    }

    public function updateCustomersBySegment(int $segmentId): void
    {
        $this->customerSegmentUserRepository->deleteByCustomerSegmentId($segmentId);

        $segment = $this->repository->fetchByField('id', $segmentId, 'criteria');

        if (!empty($segment->criteria['use_abandoned_emails'])) {
            $criteriaArgs = [
                'language_id' => $segment->criteria['language_id']
            ];

            if (!empty($segment->criteria['country_ids'])) {
                foreach ($segment->criteria['country_ids'] as $countryId) {
                    $criteriaArgs['countryCodes'][] = $this->countryRepository->getCodeById($countryId);
                }
            }

            $this->abandonedEmailRepository->insertUsersAndSegmentUsers($criteriaArgs, $segmentId);
        } else {
            $this->userRepository->getBySegmentCriterias($segment->criteria, $segmentId);
        }
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

        $headers = [
            'Email', 'First name', 'Last name'
        ];

        if (!empty($data['segment_id'])) {
            $users = $this->customerSegmentUserRepository->fetchImportedsForExport($data['segment_id']);

            foreach ($users as $user) {
                $collectData[] = [
                    $user['email'],
                    $user['name'],
                    $user['last_name'],
                ];
            }
        }

        return Excel::download(new DefaultExport(collect($collectData), $headers), $this->exportFileName);
    }
}
