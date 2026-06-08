<?php

namespace App\Repositories\CookieSetting;

use App\Repositories\BaseRepository;
use App\Models\CookieSetting;
use Illuminate\Database\Eloquent\Collection;

class CookieSettingRepository extends BaseRepository
{
    public function __construct(CookieSetting $model)
    {
        $this->model = $model;
    }

    public function fetchSettings(array $params)
    {
        return $this->model
            ->select('id', 'key', 'label')
            ->with([
                'cookie_setting_translation' => function ($query) use ($params) {
                    $query
                        ->select('id', 'cookie_setting_id', 'value')
                        ->where('language_id', $params['language_id']);
                }
            ])
            ->get();
    }
}
