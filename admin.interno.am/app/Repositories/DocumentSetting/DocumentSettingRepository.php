<?php

namespace App\Repositories\DocumentSetting;

use App\Models\DocumentSetting;
use App\Repositories\BaseRepository;
use App\Repositories\DocumentSetting\Interfaces\DocumentSettingRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocumentSettingRepository extends BaseRepository implements DocumentSettingRepositoryInterface
{
    public function __construct(DocumentSetting $model)
    {
        $this->model = $model;
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
            })->with([
            'document_setting_translation' => function ($translationQuery) use ($params) {
                $baseLanguageId = $params['base_language_id'];
                $translationQuery->select('id', 'document_setting_id', 'document_title', 'number_format_prefix', 'text_below_title')
                    ->orderByRaw("FIELD(language_id, $baseLanguageId) DESC, language_id ASC");
            }
        ])->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields, array $data): Model
    {
        $languageId = $data['languageId'];
        $baseLanguageId = $data['base_language_id'] ?? '';

        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->with([
                'document_setting_translation' => function ($translationQuery) use ($languageId, $baseLanguageId) {
                    if ($languageId == -1) {
                        $translationQuery->select('id', 'document_setting_id', 'document_title', 'number_format_prefix', 'text_below_title')->orderByRaw("FIELD(language_id, $baseLanguageId) DESC, language_id ASC");
                    } else {
                        $translationQuery->select('id', 'document_setting_id', 'document_title', 'number_format_prefix', 'text_below_title')->where('language_id', $languageId);
                    }
                }
            ])
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }
}
