<?php

namespace App\Repositories\CampaignEmailSegment;

use App\Repositories\BaseRepository;
use App\Models\CampaignEmailSegment;

class CampaignEmailSegmentRepository extends BaseRepository
{
    public function __construct(CampaignEmailSegment $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function deleteByEmailAdsId(int $emailAdsId)
    {
        return $this->model->where('campaign_email_id', $emailAdsId)->delete();
    }
}
