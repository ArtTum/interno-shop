<?php

namespace App\Repositories\CookieScript;

use App\Repositories\BaseRepository;
use App\Models\CookieScripts;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CookieScriptRepository extends BaseRepository
{
    public function __construct(CookieScripts $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetchScripts(array $params): Collection
    {
        return $this->model
            ->select('id', 'key')
            ->with([
                'cookie_script_translations' => function ($query) use ($params) {
                    $query
                        ->select('id', 'cookie_script_id', 'name', 'description', 'code', 'granted_anyway', 'consent_mode_v2', 'required_cookie')
                        ->where('language_id', intval($params['language_id']));
                }
            ])
            ->when(isset($params['translation']) && $params['translation'] == 1, function ($query) use ($params) {
                $query->whereHas('cookie_script_translations', function ($q) use ($params) {
                    $q->where('language_id', $params['language_id']);
                });
            })
            ->when(isset($params['translation']) && $params['translation'] == 0, function ($query) use ($params) {
                $query->whereDoesntHave('cookie_script_translations', function ($q) use ($params) {
                    $q->where('language_id', $params['language_id']);
                });
            })
            ->orderBy($params['ordering_field'] ?? 'id', $params['ordering_direction'] ?? 'asc')
            ->get();
    }

    public function fetchByFieldWithLanguage(string $whereField, string|int $whereValue, string $selectedFields, int $languageId): Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->with([
                'cookie_script_translations' => function ($translationQuery) use ($languageId) {
                    $translationQuery
                        ->select('id', 'cookie_script_id', 'name', 'description', 'code', 'granted_anyway', 'consent_mode_v2', 'required_cookie')
                        ->where('language_id', $languageId);
                }
            ])
            ->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }
}
