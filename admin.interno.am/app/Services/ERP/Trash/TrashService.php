<?php

namespace App\Services\ERP\Trash;

use App\Repositories\Service\ServiceRepository;
use Illuminate\Http\JsonResponse;

class TrashService
{
    private ServiceRepository $repository;

    public function __construct(ServiceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function fetch(array $data): JsonResponse
    {
        $select = "*";
        $pagination = prepare_pagination_array($data['page'], $data['per_page']);
        $ordering = [
            'field' => $data['ordering_field'],
            'direction' => $data['ordering_direction']
        ];
        $searchFields = [];
        $params = $data;

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $this->repository->fetchTrashed($select, $pagination, $ordering, $params, $searchFields, []),
            'pagination' => prepare_pagination(
                $data['page'], $data['per_page'],
                $this->repository->fetchTrashedCount($select, $pagination, $ordering, $params, $searchFields, [])
            )
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $this->repository->restore($id);

        return response()->json([
            'success' => true,
            'message' => 'Successfully restored!'
        ]);
    }

    public function forceDelete(int $id): JsonResponse
    {
        $this->repository->forceDelete($id);

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!'
        ]);
    }
}