<?php

namespace App\Services\ERP\SmsShablon;

use App\Repositories\SmsShablon\SmsShablonRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class SmsShablonService
{
    private SmsShablonRepository $repository;

    public function __construct(SmsShablonRepository $repository)
    {
        $this->repository = $repository;
    }

    public function fetch(array $data): JsonResponse
    {
        $select = "id, name, sms_text";
        $pagination = prepare_pagination_array($data['page'], $data['per_page']);
        $ordering = [
            'field' => $data['ordering_field'],
            'direction' => $data['ordering_direction']
        ];
        $searchFields = ['name', 'sms_text'];

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
        $select = "id, name, sms_text";
        $response = $this->repository->fetchByFieldWith('id', $data['id'], $select);

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
