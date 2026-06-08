<?php

namespace App\Imports\ERP;

use App\Models\CustomerSegmentUser;
use App\Models\User;
use App\Models\UserGroup;
use App\Repositories\CustomerSegmentUser\CustomerSegmentUserRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\UserGroup\UserGroupRepository;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportCustomersToSegment implements ToCollection
{
    private string $vendorKey;
    private CustomerSegmentUserRepository $customerSegmentUserRepository;
    private UserRepository $userRepository;
    private int $segmentId;
    private UserGroupRepository $userGroupRepository;

    public function __construct(string $vendorKey, int $segmentId)
    {
        $this->vendorKey = $vendorKey;
        $this->segmentId = $segmentId;
        $this->customerSegmentUserRepository = new CustomerSegmentUserRepository(new CustomerSegmentUser());
        $this->userRepository = new UserRepository(new User());
        $this->userGroupRepository = new UserGroupRepository(new UserGroup());
    }

    public function collection(Collection $collection): void
    {
        setDBConnection($this->vendorKey);
        try {
            $customerUserGroupId = $this->userGroupRepository->getIdByGroup('customer');

            foreach ($collection as $row => $data) {
                $data = $data->toArray();
                if ($row == 0) {
                    continue;
                }

                $user = $this->userRepository->fetchByFieldSimple('email', $data[0], 'id');

                if (!$user) {
                    $user = $this->userRepository->create([
                       'user_group_id' => $customerUserGroupId,
                       'email' => $data[0],
                       'name' => $data[1] ?? '-',
                       'last_name' => $data[2] ?? '-',
                       'newsletter_subscribed' => true,
                    ]);
                }

                $this->customerSegmentUserRepository->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'customer_segment_id' => $this->segmentId,
                    ],
                    [
                        'imported' => true
                    ]
                );
            }

        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }
    }

    public function chunkSize(): int
    {
        return 1000; // Number of rows per chunk
    }

    public function batchSize(): int
    {
        return 1000; // Number of rows per batch
    }
}
