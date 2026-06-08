<?php

namespace App\Services\ERP\Settings\Language;

use App\Jobs\CountryTranslations;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Language\LanguageRepository;
use App\Services\ERP\Settings\Language\Interfaces\LanguageServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LanguageService implements LanguageServiceInterface
{
    public function __construct(
        LanguageRepository        $repository,
        CurrencyRepository        $currencyRepository,
    )
    {
        $this->repository = $repository;
        $this->currencyRepository = $currencyRepository;
    }

    public function fetch(array $data): JsonResponse
    {
        try {
            $select = "languages.id, languages.code, currencies.code as currency_code, languages.name, status, languages.base, IF(status = 1, 'Active', 'Inactive') as status_text";
            $pagination = prepare_pagination_array($data['page'], $data['per_page']);
            $ordering = [
                'field' => $data['ordering_field'],
                'direction' => $data['ordering_direction']
            ];
            $searchFields = ['code', 'name'];

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
        $select = "id, code, name, status, base, default_hreflang, hreflang, local_for_trustpilot, email, currency_id, is_rtl";
        $code = $data['code'];

        $orderingCurrency = [
            'field' => 'code',
            'direction' => 'asc'
        ];

        $currencies = $this->currencyRepository->fetch("id, id as value, code as label", [], $orderingCurrency, [], [], []);
        $currencies = array_merge([['value' => null, 'label' => 'None']], $currencies->toArray());

        return response()->json([
            'success' => true,
            'currencies' => $currencies,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetchByField('code', $code, $select)
        ]);
    }

    public function insert(array $data): JsonResponse
    {
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
        unset($data['old_code']);

        if ($data['base']) $this->repository->updateBaseForAllFalse();

        if ($data['default_hreflang']) $this->repository->updateDefaultHreflangForAllFalse();

        $this->repository->update('code', $code, $data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!'
        ]);
    }

    public function updateOutlook(int $id, string $vendor, array $data)
    {


    }

    public function outlook(array $data): JsonResponse
    {
        return response()->json([
            'url' => '',
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
