<?php

namespace App\Repositories\CustomerSegmentUser;

use App\Constants\NewsletterConstants;
use App\Models\CampaignEmailUser;
use App\Repositories\BaseRepository;
use App\Models\CustomerSegmentUser;
use Illuminate\Support\Str;

class CustomerSegmentUserRepository extends BaseRepository
{
    public function __construct(CustomerSegmentUser $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function deleteByCustomerSegmentId(int $customerSegmentId)
    {
        return $this->model->where('customer_segment_id', $customerSegmentId)->where('imported', false)->delete();
    }

    public function insertToEmailAdsUsers(int $segmentId, int $campaignEmailId, bool $excluded)
    {
        return $this->model->select('id', 'user_id')
            ->where('customer_segment_id', $segmentId)
            ->chunkById(400, function ($users) use ($excluded, $campaignEmailId) {
                foreach ($users as $user) {
                    $campaignEmail = CampaignEmailUser::select('id', 'excluded')
                        ->where('campaign_email_id', $campaignEmailId)
                        ->where('user_id', $user->user_id)
                        ->whereDoesntHave('user', function ($query) {
                            $query->whereIn('email', function ($subQuery) {
                                $subQuery->select('email')->from('newsletter_blacklists');
                            });
                        })
                        ->first();

                    if (!$campaignEmail) {
                        CampaignEmailUser::insert(merge_dates_for_insert([
                            'campaign_email_id' => $campaignEmailId,
                            'user_id' => $user->user_id,
                            'status' => NewsletterConstants::EMAIL_ADS_USER_STATUS_NOT_PROCESSED,
                            'excluded' => $excluded,
                            'token' => Str::random(70),
                        ], now()));
                    } else {
                        if ($excluded && !$campaignEmail->excluded) {
                            CampaignEmailUser::where('id', $campaignEmail->id)
                                ->update([
                                    'excluded' => true,
                                ]);
                        }
                    }
                }
            }, 'customer_segment_users.id', 'id');
    }

    public function updateOrInsert(array $conditions, array $params)
    {
        $row = $this->model->select('id')
            ->where($conditions)
            ->first();

        if ($row) {
            return $row->update($params);
        } else {
            return parent::insert(merge_dates_for_insert(array_merge($conditions, $params), now()));
        }
    }

    public function fetchImportedsForExport(int $segmentId): array
    {
        $fullData = [];

        $this->model->select('customer_segment_users.id', 'users.email', 'users.name', 'users.last_name')
            ->join('users', 'users.id', '=', 'customer_segment_users.user_id')
            ->where('customer_segment_users.imported', true)
            ->where('customer_segment_users.customer_segment_id', $segmentId)
            ->chunkById(500, function ($items) use (&$fullData) {
                $chunkData = $items->map(function ($item) {
                    return $item;
                });
                $fullData = array_merge($fullData, $chunkData->toArray());
            }, 'customer_segment_users.id', 'id');

        return $fullData;
    }
}
