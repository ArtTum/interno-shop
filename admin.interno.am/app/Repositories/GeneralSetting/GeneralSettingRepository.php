<?php

namespace App\Repositories\GeneralSetting;

use App\Models\GeneralSetting;
use App\Repositories\BaseRepository;
use App\Repositories\GeneralSetting\Interfaces\GeneralSettingRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GeneralSettingRepository extends BaseRepository implements GeneralSettingRepositoryInterface
{
    public function __construct(GeneralSetting $model)
    {
        $this->model = $model;
    }

    public static function getInstance()
    {
        return app(self::class);
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->where('is_visible', true)
            ->when($params['translation'] == 1, function ($query) use ($params) {
                $query->whereHas('general_setting_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            })
            ->when($params['translation'] == 0, function ($query) use ($params) {
                $query->whereDoesntHave('general_setting_translation', function ($q) use ($params) {
                    $q->where('language_id', $params['base_language_id']);
                });
            })
            ->when($params['group'] > -1, function ($query) use ($params) {
                $query->where('group', $params['group']);
            });
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })->with([
                'general_setting_translation' => function ($translationQuery) use ($params) {
                    $baseLanguageId = $params['base_language_id'];
                    $translationQuery->select('id', 'general_setting_id', 'value')->where('language_id', $baseLanguageId);
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
                'general_setting_translation' => function ($translationQuery) use ($languageId, $baseLanguageId) {
                    if ($languageId == -1) {
                        $translationQuery
                            ->orderByRaw("FIELD(language_id, $baseLanguageId) DESC, language_id ASC");
                    } else {
                        $translationQuery->where('language_id', $languageId);
                    }
                }
            ])
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function fetchForFront(int $languageId)
    {
        return $this->model->select('key', 'general_settings.value as base_value', 'general_setting_translations.value', 'array_value')
            ->leftJoin('general_setting_translations', function ($join) use ($languageId) {
                $join->on('general_setting_translations.general_setting_id', '=', 'general_settings.id')
                    ->where('general_setting_translations.language_id', $languageId);
            })
            ->get();
    }

    public function getByKeyWithoutLanguage(string $key, string $vendorKey)
    {
        $val = Cache::tags([$vendorKey, "{$vendorKey}_general"])->get("{$vendorKey}:{$key}_by_ey_without_lang");

        if ($val) return $val;

        $val = $this->model->select('value')
            ->where('key', $key)
            ->value('value');

        Cache::tags([$vendorKey, "{$vendorKey}_general"])->put("{$vendorKey}:{$key}_by_ey_without_lang", $val, 1000000);

        return $val;
    }

    public function getByKey(string $key, int $languageId)
    {
        return $this->model->select('general_settings.value as base_value', 'general_setting_translations.value')
            ->where('key', $key)
            ->leftJoin('general_setting_translations', function ($join) use ($languageId) {
                $join->on('general_setting_translations.general_setting_id', '=', 'general_settings.id')
                    ->where('general_setting_translations.language_id', $languageId);
            })
            ->first();
    }

    public function getByKeyArrayValue(string $key)
    {
        return $this->model->select('array_value')
            ->where('key', $key)
            ->first();
    }

    public function getByKeys(array $keys, int $languageId)
    {
        return $this->model->select('key', 'general_settings.value as base_value', 'general_setting_translations.value')
            ->whereIn('key', $keys)
            ->leftJoin('general_setting_translations', function ($join) use ($languageId) {
                $join->on('general_setting_translations.general_setting_id', '=', 'general_settings.id')
                    ->where('general_setting_translations.language_id', $languageId);
            })
            ->get();
    }

    public function getByKeysWithoutLanguage(array $keys)
    {
        return $this->model->select('key', 'general_settings.value')
            ->whereIn('key', $keys)
            ->get();
    }

    public function getAllValuesByKey(string $key)
    {
        return $this->model->select('general_setting_translations.value')
            ->where('key', $key)
            ->leftJoin('general_setting_translations', function ($join) {
                $join->on('general_setting_translations.general_setting_id', '=', 'general_settings.id');
            })
            ->get()
            ->pluck('value')
            ->toArray();
    }

    public function getByGroupObj(string $vendorKey, string $group)
    {
        $val = Cache::tags([$vendorKey, "{$vendorKey}_general"])->get("{$vendorKey}:{$group}_by_group_obj");

        if ($val) return $val;

        $val = $this->model->select('value', 'key')
            ->where('group', $group)
            ->pluck('value', 'key')
            ->toArray();

        Cache::tags([$vendorKey, "{$vendorKey}_general"])->put("{$vendorKey}:{$group}_by_group_obj", $val, 1000000);

        return $val;
    }
}
