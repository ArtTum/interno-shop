<?php

namespace App\Repositories\Service;

use App\Repositories\BaseRepository;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ServiceRepository extends BaseRepository
{
   public function __construct(Service $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchQuery(
        string $select,
        array $pagination,
        array $ordering,
        array $params,
        array $searchFields,
        array $joins
    ): Builder {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->with(['user', 'diseaseRecord', 'hospitalRecord'])
            ->when((!empty($params['year']) && $params['year'] > -1), function ($q) use ($params) {
                $q->where('year', $params['year']);
            })->when((!empty($params['month']) && $params['month'] > -1), function ($q) use ($params) {
                $q->where('month', $params['month']);
            })->when((!empty($params['color']) && $params['color'] !== '-1' && $params['color'] !== -1), function ($q) use ($params) {
                $type = $params['type'] ?? null;
                $isIncoming = $type === 'incoming' || (is_array($type) && $type === ['incoming']);
                if ($isIncoming) {
                    $q->where('incoming_color', $params['color']);
                } else {
                    $q->where('color', $params['color']);
                }
            })->when((!empty($params['find_about_us']) && $params['find_about_us'] !== '-1' && $params['find_about_us'] !== -1), function ($q) use ($params) {
                $q->where('find_aboutus', $params['find_about_us']);
            })->when(!empty($params['day_surgery_start']), function ($q) use ($params) {
                $q->where('day_surgery', '>=', $params['day_surgery_start']);
            })->when(!empty($params['day_surgery_end']), function ($q) use ($params) {
                $q->where('day_surgery', '<=', $params['day_surgery_end']);
            })->when(!empty($params['call_date']), function ($q) use ($params) {
                $q->whereDate('call_date', $params['call_date']);
            })->when(!empty($params['next_call_date']), function ($q) use ($params) {
                $q->whereDate('next_call_date', $params['next_call_date']);
            })->when(!empty($params['user']), function ($q) use ($params) {
                $q->where('user_id', $params['user']);
            })->when(!empty($params['hospital']), function ($q) use ($params) {
                $q->where('hospital_id', $params['hospital']);
            })->when(!empty($params['diseases']), function ($q) use ($params) {
                $q->where('disease_id', $params['diseases']);
            })->when(!empty($params['konsultacia']), function ($q) use ($params) {
                $q->where('konsultacia', $params['konsultacia']);
            })->when(!empty($params['type']), function ($q) use ($params) {
                $q->whereIn('type', (array) $params['type']);
            });
    }

    public function fetchQueryHospitalsBase(
        string $select,
        array $pagination,
        array $ordering,
        array $params,
        array $searchFields,
        array $joins
    ): Builder {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->where('copy', 0)
            ->with(['user', 'diseaseRecord', 'hospitalRecord'])
            ->when((!empty($params['year']) && $params['year'] > -1), function ($q) use ($params) {
                $q->whereYear('call_date', $params['year']);
            })->when((!empty($params['month']) && $params['month'] > -1), function ($q) use ($params) {
                $q->whereMonth('call_date', $params['month']);
            })->when((!empty($params['color']) && $params['color'] !== '-1' && $params['color'] !== -1), function ($q) use ($params) {
                $q->where('color', $params['color']);
            })->when((!empty($params['find_about_us']) && $params['find_about_us'] !== '-1' && $params['find_about_us'] !== -1), function ($q) use ($params) {
                $q->where('find_aboutus', $params['find_about_us']);
            })->when(!empty($params['day_surgery_start']), function ($q) use ($params) {
                $q->where('day_surgery', '>=', $params['day_surgery_start']);
            })->when(!empty($params['day_surgery_end']), function ($q) use ($params) {
                $q->where('day_surgery', '<=', $params['day_surgery_end']);
            })->when(!empty($params['call_date']), function ($q) use ($params) {
                $q->whereDate('call_date', $params['call_date']);
            })->when(!empty($params['next_call_date']), function ($q) use ($params) {
                $q->whereDate('next_call_date', $params['next_call_date']);
            })->when(!empty($params['user']), function ($q) use ($params) {
                $q->where('user_id', $params['user']);
            })->when(!empty($params['hospital']), function ($q) use ($params) {
                $q->where('hospital_id', $params['hospital']);
            })->when(!empty($params['diseases']), function ($q) use ($params) {
                $q->where('disease_id', $params['diseases']);
            })->when(!empty($params['konsultacia']), function ($q) use ($params) {
                $q->where('konsultacia', $params['konsultacia']);
            })->when(!empty($params['type']), function ($q) use ($params) {
                $q->whereIn('type', (array) $params['type']);
            });
    }

    public function fetchAndCountHospitalsBase(
        string $select,
        array $pagination,
        array $ordering,
        array $params,
        array $searchFields,
        array $joins
    ): array {
        $rows = self::fetchQueryHospitalsBase($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->selectRaw('COUNT(*) OVER() AS __total')
            ->when(!empty($pagination), fn($q) => $q->limit($pagination['limit'])->offset($pagination['offset']))
            ->get();

        $total = (int) ($rows->first()?->__total ?? 0);

        return [$rows, $total];
    }

    public function fetch(
        string $select,
        array $pagination,
        array $ordering,
        array $params,
        array $searchFields,
        array $joins
    ): Collection {
        $query = self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            });
        return $query->get();
    }

    public function fetchTotalCount(
        string $select,
        array $pagination,
        array $ordering,
        array $params,
        array $searchFields,
        array $joins
    ): int {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    /**
     * Fetch data + total count in a single query using COUNT(*) OVER() window function.
     * Returns [Collection $rows, int $total].
     */
    public function fetchAndCount(
        string $select,
        array $pagination,
        array $ordering,
        array $params,
        array $searchFields,
        array $joins
    ): array {
        $rows = self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->selectRaw('COUNT(*) OVER() AS __total')
            ->when(!empty($pagination), fn($q) => $q->limit($pagination['limit'])->offset($pagination['offset']))
            ->get();

        $total = (int) ($rows->first()?->__total ?? 0);

        return [$rows, $total];
    }

    public function fetchSums(array $ordering, array $params, array $searchFields): object
    {
        return self::fetchQuery('*', [], $ordering, $params, $searchFields, [])
            ->select(DB::raw('COALESCE(SUM(price), 0) as sum, COALESCE(SUM(sale_price), 0) as sale_price, COALESCE(SUM(a_d), 0) as a_d'))
            ->first();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function fetchByFieldWith(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->with(['user', 'diseaseRecord', 'hospitalRecord'])
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        $record = $this->model->find($id);
        if ($record) {
            $record->deleted_by = auth()->id();
            $record->save();
            $record->delete();
        }
        return true;
    }

    public function fetchTrashed(
        string $select,
        array $pagination,
        array $ordering,
        array $params,
        array $searchFields,
        array $joins
    ): Collection {
        $params['trash'] = 1;
        $query = self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->with('deletedByUser')
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])->offset($pagination['offset']);
            });
        return $query->get();
    }

    public function fetchTrashedCount(
        string $select,
        array $pagination,
        array $ordering,
        array $params,
        array $searchFields,
        array $joins
    ): int {
        $params['trash'] = 1;
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function restore(int $id): bool
    {
        return (bool) $this->model->withTrashed()->where('id', $id)->restore();
    }

    public function forceDelete(int $id): bool
    {
        return (bool) $this->model->withTrashed()->where('id', $id)->forceDelete();
    }

    public function getIdByGroup(string $key)
    {
        return $this->model->select('id')->value('id');
    }
}
