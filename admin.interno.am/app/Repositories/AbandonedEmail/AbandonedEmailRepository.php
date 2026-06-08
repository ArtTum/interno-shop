<?php

namespace App\Repositories\AbandonedEmail;

use App\Repositories\BaseRepository;
use App\Models\AbandonedEmail;
use App\Repositories\CustomerSegmentUser\CustomerSegmentUserRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\UserGroup\UserGroupRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AbandonedEmailRepository extends BaseRepository
{
    public function __construct(AbandonedEmail $model)
    {
        $this->model = $model;
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function emailExists(string $email)
    {
        return $this->model->select('cart_id')
            ->where('email', $email)
            ->first();
    }

    public function getByEmail(string $email)
    {
        $result = $this->model->select('id', 'email', 'synced_to_newsletter')->where('email', $email)->first();

        if ($result) {
            return $result->toArray();
        }

        return $result;
    }

    public function deleteById(int $id): bool
    {
        return parent::delete($id);
    }

    public function getUnsyncedEmails()
    {
        $result = $this->model->select('id', 'email', 'first_name', 'last_name', 'country', 'cart_id', 'locale', 'language_id')
            ->where('synced_to_newsletter', false)
            ->get();

        if ($result) {
            return $result->toArray();
        }

        return $result;
    }

    private function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins);
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when($params['language_id'] > -1, function ($query) use ($params) {
                $query->where('language_id', $params['language_id']);
            })
            ->limit($pagination['limit'])
            ->offset($pagination['offset'])
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when($params['language_id'] > -1, function ($query) use ($params) {
                $query->where('language_id', $params['language_id']);
            })
            ->count();
    }

    public function insertUsersAndSegmentUsers(array $criteria, int $segmentId): void
    {
        $userRepository = app(UserRepository::class);
        $userGroupRepository = app(UserGroupRepository::class);
        $customerSegmentUserRepository = app(CustomerSegmentUserRepository::class);
        $customerUserGroupId = $userGroupRepository->getIdByGroup('customer');

        $this->model
            ->select('id', 'email', 'first_name', 'last_name')
            ->when(!empty($criteria['language_id']) && $criteria['language_id'] > -1, function ($q) use ($criteria) {
                $q->where('language_id', $criteria['language_id']);
            })
            ->when(!empty($criteria['countryCodes']), function ($q) use ($criteria) {
                $q->whereIn('country', $criteria['countryCodes']);
            })
            ->chunkById(500, function ($abandonedEmails) use ($segmentId, $userGroupRepository, $userRepository, $customerSegmentUserRepository, $customerUserGroupId) {
                foreach ($abandonedEmails as $abandonedEmail) {
                    $user = $userRepository->fetchByFieldSimple('email', $abandonedEmail['email'], 'id');

                    if (!$user) {
                        $user = $userRepository->create([
                            'user_group_id' => $customerUserGroupId,
                            'email' => $abandonedEmail['email'],
                            'name' => $abandonedEmail['first_name'] ?? '-',
                            'last_name' => $abandonedEmail['last_name'] ?? '-',
                        ]);
                    }

                    $customerSegmentUserRepository->updateOrInsert(
                        [
                            'user_id' => $user->id,
                            'customer_segment_id' => $segmentId,
                        ],
                        [
                            'imported' => false,
                            'abandoned_email_id' => $abandonedEmail['id']
                        ]
                    );
                }
            });
    }
}
