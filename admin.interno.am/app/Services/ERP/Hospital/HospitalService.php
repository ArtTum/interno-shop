<?php

namespace App\Services\ERP\Hospital;

use App\Repositories\Hospital\HospitalRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class HospitalService
{
    private HospitalRepository $repository;

    public function __construct(HospitalRepository $repository)
    {
        $this->repository = $repository;
    }

    public function fetch(array $data): JsonResponse
    {
        $select = "id, name";
        $pagination = prepare_pagination_array($data['page'], $data['per_page']);
        $ordering = [
            'field' => $data['ordering_field'],
            'direction' => $data['ordering_direction']
        ];
        $searchFields = ['name'];

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetch($select, $pagination, $ordering, $data, $searchFields, []),
            'pagination' => prepare_pagination(
                $data['page'], $data['per_page'],
                $this->repository->fetchTotalCount($select, $pagination, $ordering, $data, $searchFields, [])
            )
        ]);
    }

    public function fetchByField(array $data): JsonResponse
    {
        $select = "id, name";
        $response = $this->repository->fetchByFieldWith('id', $data['id'], $select);

        if (!empty($response->ips)) {
            foreach ($response->ips as $ip) {
                $fullDate = $ip->expires_at;
                if (!$fullDate) continue;

                $ip->expires_at = Carbon::parse($fullDate)->toDateString();
                $ip->time = Carbon::parse($fullDate)->format('H:i');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $response
        ]);
    }

    public function insert(array $data): JsonResponse
    {
       $this->repository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!'
        ]);
    }

    public function update(array $data): JsonResponse
    {

        $this->repository->update('id', $data['id'], $data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!'
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        $this->repository->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!'
        ]);
    }
}
