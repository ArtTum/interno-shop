<?php

namespace App\Repositories\ReminderEmail;

use App\Models\ReminderEmail;
use App\Repositories\BaseRepository;
use App\Repositories\ReminderEmail\Interfaces\ReminderEmailRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReminderEmailRepository extends BaseRepository implements ReminderEmailRepositoryInterface
{
    public function __construct(ReminderEmail $model)
    {
        $this->model = $model;
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(isset($params['translation']) && $params['translation'] == 1, function ($query) use ($params) {
                $query->whereHas('reminder_email_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            })
            ->when(isset($params['translation']) && $params['translation'] == 0, function ($query) use ($params) {
                $query->whereDoesntHave('reminder_email_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            })->when(isset($params['language_id']) && $params['language_id'] > 0, function ($query) use ($params) {
                $query->with('reminder_email_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['language_id']);
                });
            });
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {

        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })->get();
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
        return parent::update($whereField, $whereValue, $data);
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields, int $languageId): Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->when($languageId > 0, function ($query) use ($languageId) {
                $query->with([
                    'reminder_email_translation' => function ($translationQuery) use ($languageId) {
                        $translationQuery->select('id', 'reminder_email_id', 'language_id', 'subject', 'title', 'top_text', 'bottom_text', 'footer_text')->where('language_id', $languageId);
                    }
                ]);
            })
            ->first();
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

}
