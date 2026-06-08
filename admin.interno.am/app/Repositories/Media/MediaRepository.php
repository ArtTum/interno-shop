<?php

namespace App\Repositories\Media;

use App\Models\Media;
use App\Repositories\BaseRepository;
use App\Repositories\Media\Interfaces\MediaRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MediaRepository extends BaseRepository implements MediaRepositoryInterface
{
    public function __construct(Media $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when($params['type'] >= 0, function ($statusQuery) use ($params) {
                $statusQuery->where('type', $params['type']);
            })->when($params['year'] >= 0, function ($statusQuery) use ($params) {
                $statusQuery->whereYear('media.created_at', $params['year']);
            })->when($params['month'] >= 0, function ($statusQuery) use ($params) {
                $statusQuery->whereMonth('media.created_at', $params['month']);
            })->when(!empty($params['mediaTypes']), function ($mediaTypeQuery) use ($params) {
                $mediaTypeQuery->whereIn('media.type', $params['mediaTypes']);
            })
            ->when(isset($params['translation']) && $params['translation'] == 1, function ($query) use ($params) {
                $query->whereHas('media_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            })
            ->when(isset($params['translation']) && $params['translation'] == 0, function ($query) use ($params) {
                $query->whereDoesntHave('media_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            });
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->with([
                'media_translation' => function ($translationQuery) use ($params) {
                    $languageId = $params['language_id'];
                    $translationQuery->select('id', 'media_id', 'alt')
                        ->where('language_id', $languageId);
                }
            ])->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })->get();
    }

    public function all($selectedFields): Collection
    {
        return $this->model->select(DB::raw($selectedFields))
//            ->where('type', 'images')
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->count();
    }

    public function fetchForMediasTranslations(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): array
    {
        $fullData = [];
        self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->chunkById(500, function ($items) use (&$fullData) {
                $chunkData = $items->map(function ($item) {
                    return $item;
                });
                $fullData = array_merge($fullData, $chunkData->toArray());
            }, 'media.id', 'id');

        return $fullData;
    }

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields,array $params): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->with([
                'media_translation' => function ($translationQuery) use ($params) {
                    $languageId = $params['language_id'];
                    $translationQuery->select('id', 'media_id', 'alt')
                        ->orderByRaw("FIELD(language_id, $languageId) DESC, language_id ASC");
                }
            ])
            ->first();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function fetchExists(string $whereField, string|int $whereValue): bool
    {
        return $this->model->where($whereField, $whereValue)?->exists();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function fetchForExport(array $data)
    {
        return $this->model->select('media.id as id', 'original_path', 'alt')
            ->leftJoin('media_translations', function ($join) use ($data) {
                $join->on('media_translations.media_id', '=', 'media.id')
                    ->where('media_translations.language_id', $data['language_id']);
            })
            ->when(!filter_var($data['isAll'], FILTER_VALIDATE_BOOLEAN), function ($query) use ($data) {
                $query->whereIn('media.id', $data['ids'])
                    ->orderBy($data['ordering_field'], $data['ordering_direction']);
            })
            ->get();
    }
}
