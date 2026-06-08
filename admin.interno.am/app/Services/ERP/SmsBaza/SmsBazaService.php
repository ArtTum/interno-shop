<?php

namespace App\Services\ERP\SmsBaza;

use App\Repositories\SmsBaza\SmsBazaRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class SmsBazaService
{
    private SmsBazaRepository $repository;

    public function __construct(SmsBazaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function fetchIndexParams(): JsonResponse
    {
        $startYear = 2020;
        $endYear = now()->year + 1;

        $years = [['value' => -1, 'label' => 'Բոլորը']];
        for ($year = $startYear; $year <= $endYear; $year++) {
            $years[] = ['value' => $year, 'label' => $year];
        }

        $months = [
            ['value' => -1, 'label' => 'Բոլորը'],
            ['value' => 1, 'label' => 'Հունվար'],
            ['value' => 2, 'label' => 'Փետրվար'],
            ['value' => 3, 'label' => 'Մարտ'],
            ['value' => 4, 'label' => 'Ապրիլ'],
            ['value' => 5, 'label' => 'Մայիս'],
            ['value' => 6, 'label' => 'Հունիս'],
            ['value' => 7, 'label' => 'Հուլիս'],
            ['value' => 8, 'label' => 'Օգոստոս'],
            ['value' => 9, 'label' => 'Սեպտեմբեր'],
            ['value' => 10, 'label' => 'Հոկտեմբեր'],
            ['value' => 11, 'label' => 'Նոյեմբեր'],
            ['value' => 12, 'label' => 'Դեկտեմբեր'],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'years' => $years,
            'months' => $months,
        ]);
    }

    public function fetch(array $data): JsonResponse
    {
        $select = "id, year, month, call_date, phone, other_phone, patient_full_name, disease, medical_and_doctor";
        $pagination = prepare_pagination_array($data['page'], $data['per_page']);
        $ordering = [
            'field' => $data['ordering_field'],
            'direction' => $data['ordering_direction']
        ];
        $searchFields = ['patient_full_name', 'phone', 'other_phone', 'disease'];

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
        $select = "id, year, month, call_date, phone, other_phone, sms_bazacol, patient_full_name, disease, medical_and_doctor";
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
