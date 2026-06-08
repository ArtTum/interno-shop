<?php

namespace App\Repositories\GlobalDocumentSetting;

use App\Models\GlobalDocumentSetting;
use App\Repositories\BaseRepository;
use App\Repositories\GlobalDocumentSetting\Interfaces\GlobalDocumentSettingRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GlobalDocumentSettingRepository extends BaseRepository implements GlobalDocumentSettingRepositoryInterface
{
    public function __construct(GlobalDocumentSetting $model)
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
            'global_document_setting_translation' => function ($translationQuery) use ($params) {
                $baseLanguageId = $params['base_language_id'];
                $translationQuery->select('id', 'global_document_setting_id', 'name', 'slug')
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
        $baseLanguageId = $data['base_language_id'];

        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->with([
                'global_document_setting_translation' => function ($translationQuery) use ($languageId, $baseLanguageId) {
                    if ($languageId == -1) {
                        $translationQuery->select('id', 'global_document_setting_id', 'name', 'phone', 'email', 'address', 'footer_text', 'seller_info', 'rules', 'language_id')
                            ->orderByRaw("FIELD(language_id, $baseLanguageId) DESC, language_id ASC");
                    } else {
                        $translationQuery->select('id', 'global_document_setting_id', 'name', 'phone', 'email', 'address', 'footer_text', 'seller_info', 'rules', 'language_id')
                            ->where('language_id', $languageId);
                    }
                },
                'media' => function ($imageQuery) {
                    $imageQuery->select([
                        'id', 'type', 'original_path AS path'
                    ]);
                },
            ])
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }
}
