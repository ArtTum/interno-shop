<?php

namespace App\Repositories\Offer;

use App\Models\Offer;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OfferRepository extends BaseRepository
{
    public function __construct(Offer $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($params['created_at_from']), function ($query) use ($params) {
                $query->where('created_at', '>=', $params['created_at_from']);
            })
            ->when(!empty($params['created_at_to']), function ($query) use ($params) {
                $query->where('created_at', '<=', $params['created_at_to'] . ' 23:59:59');
            });
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        $query = self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->with([
                'user' => function ($query) {
                    $query->select(['id', 'name', 'last_name']);
                },
                'offered_user' => function ($query) {
                    $query->select(['id', 'name', 'last_name', 'email']);
                },
            ])
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            });
        return $query->get();
    }

    public function autocomplete(string $field, ?string $searchTerm, array $alreadySelectIds): array
    {
        $limit = count($alreadySelectIds) + 10;
        $query = $this->model->query();

        if (!empty($searchTerm)) {
            $query->where($field, 'like', '%' . $searchTerm . '%');
        }

        if (!empty($alreadySelectIds)) {
            $query->orWhereIn('id', $alreadySelectIds);
        }

        $results = $query->limit($limit)->pluck($field, 'id');

        return $results->map(function ($label, $id) {
            return [
                'value' => $id,
                'label' => $label,
            ];
        })->values()->toArray();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchForFront(int $userId, $offerId)
    {
        return $this->model->select('id', 'cart_data')
            ->where('offers.id', $offerId)
            ->where('offered_user_id', $userId)
            ->where('expire_date', '>=', now()->toDateString())
            ->whereNull('order_id')
            ->first();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields, array $with = []): ?Model
    {
        $query = $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function checkExistsByField(string $whereField, string $whereValue)
    {
        return $this->model->where($whereField, $whereValue)->exists();
    }

    public function fetchForFrontAccount(int $userId, array $pagination)
    {
        return $this->model->select('id', 'order_id', 'title', 'expire_date', 'cart_data')
            ->where('offered_user_id', $userId)
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getOffer(int $offerId)
    {
        return $this->model->select('currencies.rate', 'cart_data')
            ->join('currencies', 'currencies.id', 'offers.currency_id')
            ->where('offers.id', $offerId)
            ->where('offered_user_id', Auth::user()->id)
            ->first();
    }
}
