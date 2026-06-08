<?php

namespace App\Repositories\CampaignEmail;

use App\Constants\NewsletterConstants;
use App\Repositories\BaseRepository;
use App\Models\CampaignEmail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CampaignEmailRepository extends BaseRepository
{
    public function __construct(CampaignEmail $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->with('excluded_segments', 'segments')
            ->first();
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when($params['campaign_id'] > -1, function ($query) use ($params) {
                $query->where('campaign_id', $params['campaign_id']);
            });
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->join('campaigns', 'campaigns.id', '=', 'campaign_emails.campaign_id')
            ->join('languages', 'languages.id', '=', 'campaign_emails.language_id')
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function fetchForSending()
    {
        return $this->model->select('id', 'content', 'language_id')
            ->where('sending_time', '<=', Carbon::now()->toDateTimeString())
            ->whereIn('status', [NewsletterConstants::EMAIL_ADS_STATUS_WAITING_TO_LIMIT, NewsletterConstants::EMAIL_ADS_STATUS_NOT_PROCESSED])
            ->get();
    }
}
