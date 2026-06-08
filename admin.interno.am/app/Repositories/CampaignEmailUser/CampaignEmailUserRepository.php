<?php

namespace App\Repositories\CampaignEmailUser;

use App\Constants\NewsletterConstants;
use App\Repositories\BaseRepository;
use App\Models\CampaignEmailUser;

class CampaignEmailUserRepository extends BaseRepository
{
    public function __construct(CampaignEmailUser $model)
    {
        $this->model = $model;
    }

    public function deleteByEmailAdsId(int $emailAdsId)
    {
        return $this->model->where('campaign_email_id', $emailAdsId)->delete();
    }

    public function markEmailAdsAsOpened(string $token)
    {
        return $this->model->where('token', $token)
            ->where('status', '<', NewsletterConstants::EMAIL_ADS_USER_STATUS_OPENED)
            ->update([
                'status' => NewsletterConstants::EMAIL_ADS_USER_STATUS_OPENED,
            ]);
    }

    public function markEmailAdsAsVisited(string $token)
    {
        return $this->model->where('token', $token)
            ->where('status', '<', NewsletterConstants::EMAIL_ADS_USER_STATUS_CLICKED)
            ->update([
                'status' => NewsletterConstants::EMAIL_ADS_USER_STATUS_CLICKED,
            ]);
    }

    public function markEmailAdsAsOrdered(string $token)
    {
        return $this->model->where('token', $token)
            ->where('status', '<', NewsletterConstants::EMAIL_ADS_USER_STATUS_ORDERED)
            ->update([
                'status' => NewsletterConstants::EMAIL_ADS_USER_STATUS_ORDERED,
            ]);
    }

    public function markEmailAdsAsUnsubscribed(string $token)
    {
        return $this->model->where('token', $token)
            ->where('status', '<', NewsletterConstants::EMAIL_ADS_USER_STATUS_ORDERED)
            ->update([
                'status' => NewsletterConstants::EMAIL_ADS_USER_STATUS_UNSUBSCRIBED,
            ]);
    }

    public function getCampaignEmailIdByToken(string $token)
    {
        return $this->model->select('campaign_email_id')
            ->where('token', $token)
            ->value('campaign_email_id');
    }

    public function getUserIdByToken(string $token)
    {
        return $this->model->select('user_id')
            ->where('token', $token)
            ->value('user_id');
    }

    public function deleteByUserId(int $userId)
    {
        return $this->model->where('user_id', $userId)
            ->where('status', NewsletterConstants::EMAIL_ADS_USER_STATUS_NOT_PROCESSED)
            ->delete();
    }

    public function getResultsByEmailId(?int $emailId = null)
    {
        return $this->model->selectRaw('
        COUNT(CASE WHEN status >= 0 THEN 1 END) as all_recipients,
        COUNT(CASE WHEN status = ? THEN 1 END) as bounced_count,
        COUNT(CASE WHEN status >= ? THEN 1 END) as delivered_count,
        COUNT(CASE WHEN status >= ? THEN 1 END) as opened_count,
        COUNT(CASE WHEN status >= ? THEN 1 END) as clicked_count,
        COUNT(CASE WHEN status = ? THEN 1 END) as ordered_count,
        COUNT(CASE WHEN status = ? THEN 1 END) as unsubscribed_count
    ', [
            NewsletterConstants::EMAIL_ADS_USER_STATUS_BOUNCED,
            NewsletterConstants::EMAIL_ADS_USER_STATUS_DELIVERED,
            NewsletterConstants::EMAIL_ADS_USER_STATUS_OPENED,
            NewsletterConstants::EMAIL_ADS_USER_STATUS_CLICKED,
            NewsletterConstants::EMAIL_ADS_USER_STATUS_ORDERED,
            NewsletterConstants::EMAIL_ADS_USER_STATUS_UNSUBSCRIBED,
        ])
            ->when($emailId, function ($q) use ($emailId) {
                $q->where('campaign_email_id', $emailId);
            })
            ->where('excluded', false)
            ->first();
    }

    public function deleteExcludeds(int $campaignEmailId)
    {
        return $this->model->where('excluded', true)
            ->where('campaign_email_id', $campaignEmailId)
            ->delete();
    }

    public function checkExistsByStatus(int $status)
    {
        return $this->model->select('id')
            ->where('status', $status)
            ->exists();
    }
}
