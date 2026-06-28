<?php

namespace App\Services\ERP\Settings\Language;

use App\Jobs\CountryTranslations;
use App\Repositories\Language\LanguageRepository;
use App\Services\ERP\Settings\Language\Interfaces\LanguageServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LanguageService implements LanguageServiceInterface
{
    public function __construct(
        LanguageRepository        $repository,
    )
    {
        $this->repository = $repository;
    }

    public function fetch(array $data): JsonResponse
    {
        try {
            $select = "languages.id, languages.code, languages.draft, languages.name, status, languages.base, IF(status = 1, 'Active', 'Inactive') as status_text, IF(draft = 0, 'Active', 'Draft') as draft_text";
            $pagination = prepare_pagination_array($data['page'], $data['per_page']);
            $ordering = [
                'field' => $data['ordering_field'],
                'direction' => $data['ordering_direction']
            ];
            $searchFields = ['languages.code', 'languages.name'];

            return response()->json([
                'success' => true,
                'message' => 'Successfully reached!',
                'data' => $this->repository->fetchForAdmin($select, $pagination, $ordering, $data, $searchFields, []),
                'pagination' => prepare_pagination(
                    $data['page'], $data['per_page'],
                    $this->repository->fetchTotalCount($select, $pagination, $ordering, $data, $searchFields, [])
                )
            ]);
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }
    }

    public function fetchByField(array $data): JsonResponse
    {
        $select = "id, code, name, draft, status, base, default_hreflang, hreflang, local_for_trustpilot, email, is_rtl";
        $code = $data['code'];

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetchByField('code', $code, $select)
        ]);
    }

    public function insert(array $data): JsonResponse
    {
        unset($data['currency_id'], $data['currencies']);

        $now = now()->toDateTimeString();
        $data = merge_dates_for_insert($data, $now);
        $vendorName = Auth::user()->getConnectionName();

        if ($data['base']) $this->repository->updateBaseForAllFalse();

        if ($data['default_hreflang']) $this->repository->updateDefaultHreflangForAllFalse();

        $this->repository->create($data);

        CountryTranslations::dispatch($vendorName, []);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!'
        ]);
    }

    public function update(array $data): JsonResponse
    {
        $code = $data['old_code'];
        unset($data['old_code'], $data['currency_id'], $data['currencies']);

        if ($data['base']) $this->repository->updateBaseForAllFalse();

        if ($data['default_hreflang']) $this->repository->updateDefaultHreflangForAllFalse();

        $this->repository->update('code', $code, $data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!'
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        $this->repository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!'
        ]);
    }
}
