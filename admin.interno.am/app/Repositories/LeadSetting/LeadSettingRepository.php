<?php

namespace App\Repositories\LeadSetting;

use App\Repositories\BaseRepository;
use App\Models\LeadSetting;
use Illuminate\Database\Eloquent\Collection;

class LeadSettingRepository extends BaseRepository
{
    public function __construct(LeadSetting $model)
    {
        $this->model = $model;
    }

    public function fetchSettings(array $params): Collection
    {
        return $this->model
            ->select('id', 'key', 'label')
            ->with([
                'lead_setting_translation' => function ($query) use ($params) {
                    $query
                        ->select('id', 'lead_setting_id', 'value')
                        ->where('language_id', $params['language_id']);
                }
            ])
            ->get();
    }

    public function getSettingValueByKey(string $key, $language_id): LeadSetting
    {
        return $this->model
            ->select('id', 'key', 'label')
            ->where('key', $key)
            ->with([
                'lead_setting_translations' => function ($query) use ($language_id) {
                    $query
                        ->select('id', 'lead_setting_id', 'value')
                        ->where('language_id', $language_id);
                }
            ])
            ->first();
    }
}
