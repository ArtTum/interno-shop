<?php

namespace App\Repositories\Currency;

use App\Models\Currency;
use App\Repositories\BaseRepository;
use App\Repositories\Currency\Interfaces\CurrencyRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CurrencyRepository extends BaseRepository implements CurrencyRepositoryInterface
{
    public function __construct(Currency $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        $data['code'] = strtoupper($data['code']);
        return parent::insert($data);
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins);
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })
            ->get();
    }

    public function all(string $select): Collection
    {
        return self::fetchQuery($select, [], [], [], [], [])->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        $data['code'] = strtoupper($data['code']);
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function updateBaseForAllFalse(): bool
    {
        return $this->model->where('id', '>=', 0)->update(['base' => false]);
    }

    public function getBaseOrFirst()
    {
        return $this->model->select('symbol')->orderByRaw("FIELD(base, true) DESC, name ASC")->value('symbol');
    }

    public function getFieldByBase(string $field)
    {
        return $this->model->select($field)->where('base', true)->value($field);
    }

    public function fetchForFront()
    {
        return $this->model->select('id', 'symbol', 'base', 'code', 'rate')
            ->orderByRaw("FIELD(base, true) DESC, code ASC")
            ->get();
    }

    public function fetchRateByCode(string $code)
    {
        return $this->model->select('rate')->where('code', $code)->value('rate');
    }

    public function fetchRateWithGBByCode(string $code)
    {
        return $this->model->select('rate', 'gbp_rate', 'actual_rate')->where('code', $code)->first();
    }

    public function getBaseCode(): string
    {
        return $this->model->select('code')->orderByRaw("FIELD(base, true) DESC, name ASC")->value('code');
    }

    public function getIdByCode(string $code): string
    {
        return $this->model->select('id')->where('code', $code)->value('id');
    }

    public function getTranslationCountry(int $languageId, string $code)
    {
        return $this->model->from('countries')
            ->select('countries.id', 'country_translations.name', 'countries.code')
            ->where('countries.code', $code)
            ->leftJoin('country_translations', function ($join) use ($languageId) {
                $join->on('country_translations.country_id', '=', 'countries.id')
                    ->where('country_translations.language_id', $languageId);
            })
            ->first();
    }
}
