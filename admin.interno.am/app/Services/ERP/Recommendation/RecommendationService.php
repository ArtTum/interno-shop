<?php

namespace App\Services\ERP\Recommendation;

use App\Export\ERP\DefaultExport;
use App\Repositories\Disease\DiseaseRepository;
use App\Repositories\Hospital\HospitalRepository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\User\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RecommendationService
{
    private ServiceRepository $repository;

    public function __construct(
        ServiceRepository $repository,
        HospitalRepository $hospitalRepository,
        DiseaseRepository $diseaseRepository,
        UserRepository $userRepository
    )
    {
        $this->repository = $repository;
        $this->hospitalRepository = $hospitalRepository;
        $this->diseaseRepository = $diseaseRepository;
        $this->userRepository = $userRepository;
    }

    public function fetch(array $data): JsonResponse
    {
        $data['type'] = ['incoming', 'recommendations'];
        $select = "*";
        $pagination = prepare_pagination_array($data['page'], $data['per_page']);
        $ordering = [
            'field' => $data['ordering_field'],
            'direction' => $data['ordering_direction']
        ];

        if (!empty($data['search'])) {
            $data['search'] = str_replace(
                ["\u{0589}", "\u{00B7}", "\u{2024}", "\u{2027}", "\u{22C5}"],
                '.',
                $data['search']
            );
        }

        $normalizeDots = fn(string $col) => "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE({$col}, '\u{0589}', '.'), '\u{00B7}', '.'), '\u{2024}', '.'), '\u{2027}', '.'), '\u{22C5}', '.')";

        $searchFields = [
            ['raw' => $normalizeDots('patient_full_name')],
            'phone',
            'other_phone',
            'call_date',
            ['raw' => "DATE_FORMAT(call_date, '%d.%m.%Y')"],
//            ['raw' => "DATE_FORMAT(departure_datetime, '%d.%m.%Y %H:%i')"],
            ['raw' => $normalizeDots('departure_datetime')],
            ['raw' => $normalizeDots('additional_data')],
            ['raw' => $normalizeDots('disease')],
            ['raw' => $normalizeDots('medical_and_doctor')],
            ['relation_name' => 'hospitalRecord', 'fields' => ['name']],
            ['relation_name' => 'diseaseRecord', 'fields' => ['name']],
        ];

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
        $select = "*";
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
        $data['copy'] = 0;
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = json_encode($value);
            }
        }
        $this->repository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!'
        ]);
    }

    public function update(array $data): JsonResponse
    {
        $select = "*";
        $viewData = $this->repository->fetchByFieldWith('id', $data['id'], $select);

        if ($data['id'] && !empty($data['day_surgery']) && !$viewData->day_surgery) {
            $data['color'] = '#0070C0';
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = json_encode($value);
            }
        }

        if (!empty($data['month_copy'])) {
            $data2 = $data;
            $data2['copy'] = 1;
            if ($data2['month'] > $data2['month_copy']) {
                $data2['year'] = $data2['year'] + 1;
            }

            $data2['month'] = $data2['month_copy'];
            if ($data['color'] === '#FF0000') {
                $data2['call_date'] = now()->toDateString();
            }
            $this->repository->create($data2);

            if (in_array($viewData->color, ['#FF0000', '#00B050'])) {
                $originalUpdate = ['copy' => 0];
                $this->repository->update('id', $data['id'], $originalUpdate);
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully updated!'
                ]);
            }

            if ($data['color'] !== '#FF0000') {
                $data['color'] = '#E26B0A';
            }
        }
        $data['copy'] = 0;
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

    public function fetchIndexParams(): JsonResponse
    {
        $startYear = 2016;
        $endYear = now()->year + 1;

        $years = [
            [
                'value' => -1,
                'label' => 'Բոլորը',
            ]
        ];

        for ($year = $startYear; $year <= $endYear; $year++) {
            $years[] = [
                'value' => $year,
                'label' => $year,
            ];
        }

        $months = [
            [
                'value' => -1,
                'label' => 'Բոլորը',
            ],
            [
                'value' => 1,
                'label' => 'Հունվար',
            ],
            [
                'value' => 2,
                'label' => 'Փետրվար',
            ],
            [
                'value' => 3,
                'label' => 'Մարտ',
            ],
            [
                'value' => 4,
                'label' => 'Ապրիլ',
            ],
            [
                'value' => 5,
                'label' => 'Մայիս',
            ],
            [
                'value' => 6,
                'label' => 'Հունիս',
            ],
            [
                'value' => 7,
                'label' => 'Հուլիս',
            ],
            [
                'value' => 8,
                'label' => 'Օգոստոս',
            ],
            [
                'value' => 9,
                'label' => 'Սեպտեմբեր',
            ],
            [
                'value' => 10,
                'label' => 'Հոկտեմբեր',
            ],
            [
                'value' => 11,
                'label' => 'Նոյեմբեր',
            ],
            [
                'value' => 12,
                'label' => 'Դեկտեմբեր',
            ],
        ];
        $colors = [
            [
                'value' => -1,
                'label' => 'Բոլորը',
            ],
            [
                'value' => '#000000',
                'label' => 'Սև',
            ],
            [
                'value' => '#0070C0',
                'label' => 'Կապույտ',
            ],
            [
                'value' => '#E26B0A',
                'label' => 'Դեղին',
            ],
            [
                'value' => '#00B050',
                'label' => 'Կանաչ',
            ],
            [
                'value' => '#FF0000',
                'label' => 'Կարմիր',
            ],
            [
                'value' => '#7030A0',
                'label' => 'Մանուշկագույն',
            ],
            [
                'value' => '#FFC000',
                'label' => 'Նարջագույն',
            ],
            [
                'value' => '#6f2222',
                'label' => 'Շագանակագույն',
            ],
        ];
        $findAboutUS = [
            [
                'value' => -1,
                'label' => 'Բոլորը',
            ],
            [
                'value' => 'Facebook',
                'label' => 'Facebook',
            ],
            [
                'value' => 'Instagram',
                'label' => 'Instagram',
            ],
            [
                'value' => 'Friend',
                'label' => 'Friend',
            ],
            [
                'value' => 'Google',
                'label' => 'Google',
            ],
            [
                'value' => 'doctor911.am',
                'label' => 'doctor911.am',
            ],
            [
                'value' => 'whatsapp',
                'label' => 'WhatsApp',
            ],
            [
                'value' => 'viber',
                'label' => 'Viber',
            ],
            [
                'value' => 'yell.am',
                'label' => 'yell.am',
            ],
            [
                'value' => 'zang',
                'label' => 'Zang',
            ],
            [
                'value' => 'այլ',
                'label' => 'այլ',
            ],
        ];
        $hospitals = $this->hospitalRepository->fetch("id as value, name as label", [], [
            'field' => 'id',
            'direction' => 'asc',
        ], [], [], []);

        $diseases = $this->diseaseRepository->fetch("id as value, name as label", [], [
            'field' => 'id',
            'direction' => 'asc',
        ], [], [], []);

        $users = $this->userRepository->fetchUser(
            "id as value, CONCAT(name, ' ', last_name) as label", [],
            [
                'field' => 'id',
                'direction' => 'desc',
            ], ['source' => 0], [], []
        );

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'years' => $years,
            'months' => $months,
            'colors' => $colors,
            'users' => $users,
            'findAboutUS' => $findAboutUS,
            'hospitals' => $hospitals,
            'diseases' => $diseases,
        ]);
    }
    public function export(array $data): BinaryFileResponse
    {
        $data['type'] = 'recommendations';
        $ordering = [
            'field' => $data['ordering_field'] ?? 'id',
            'direction' => $data['ordering_direction'] ?? 'desc'
        ];
        $searchFields = [
            'patient_full_name',
            'phone',
            'other_phone',
            'call_date',
            ['raw' => "DATE_FORMAT(call_date, '%d.%m.%Y')"],
            'departure_datetime',
            'additional_data',
            'disease',
            'medical_and_doctor',
            ['relation_name' => 'hospitalRecord', 'fields' => ['name']],
            ['relation_name' => 'diseaseRecord', 'fields' => ['name']],
        ];

        $records = $this->repository->fetch('*', [], $ordering, $data, $searchFields, []);

        $headers = [
            'Զանգի ամսաթիվ',
            'Գնալու ամսաթիվ և ժամ',
            'Լրացուցիչ տվյալներ',
            'Հեռախոս',
            'Հեռախոս 2',
            'Հիվանդի անուն ազգանուն',
            'Հիվանդի տարիքը',
            'Հիվանդություն',
            'Բուժհաստատություն և բժիշկ',
            'Նախնական գին',
        ];

        $collectData = collect($records->map(function ($item) {
            return [
                $item->call_date ? \Carbon\Carbon::parse($item->call_date)->format('d.m.Y') : '',
                $item->departure_datetime,
                $item->additional_data,
                $item->phone,
                $item->other_phone,
                $item->patient_full_name,
                $item->age,
                $item->disease,
                $item->medical_and_doctor,
                $item->preliminary_price,
            ];
        }));

        return Excel::download(new DefaultExport($collectData, $headers), 'recommendations.xlsx');
    }

    public function fetchParams(): JsonResponse
    {
        $startYear = 2016;
        $endYear = now()->year + 1;

        $years = [];

        for ($year = $startYear; $year <= $endYear; $year++) {
            $years[] = [
                'value' => $year,
                'label' => $year,
            ];
        }

        $months = [
            [
                'value' => 1,
                'label' => 'Հունվար',
            ],
            [
                'value' => 2,
                'label' => 'Փետրվար',
            ],
            [
                'value' => 3,
                'label' => 'Մարտ',
            ],
            [
                'value' => 4,
                'label' => 'Ապրիլ',
            ],
            [
                'value' => 5,
                'label' => 'Մայիս',
            ],
            [
                'value' => 6,
                'label' => 'Հունիս',
            ],
            [
                'value' => 7,
                'label' => 'Հուլիս',
            ],
            [
                'value' => 8,
                'label' => 'Օգոստոս',
            ],
            [
                'value' => 9,
                'label' => 'Սեպտեմբեր',
            ],
            [
                'value' => 10,
                'label' => 'Հոկտեմբեր',
            ],
            [
                'value' => 11,
                'label' => 'Նոյեմբեր',
            ],
            [
                'value' => 12,
                'label' => 'Դեկտեմբեր',
            ],
        ];
        $colors = [
            [
                'value' => '#000000',
                'label' => 'Սև',
            ],
            [
                'value' => '#0070C0',
                'label' => 'Կապույտ',
            ],
            [
                'value' => '#E26B0A',
                'label' => 'Դեղին',
            ],
            [
                'value' => '#00B050',
                'label' => 'Կանաչ',
            ],
            [
                'value' => '#FF0000',
                'label' => 'Կարմիր',
            ],
            [
                'value' => '#7030A0',
                'label' => 'Մանուշկագույն',
            ],
            [
                'value' => '#FFC000',
                'label' => 'Նարջագույն',
            ],
            [
                'value' => '#6f2222',
                'label' => 'Շագանակագույն',
            ],
        ];
        $findAboutUS = [
            [
                'value' => 'Facebook',
                'label' => 'Facebook',
            ],
            [
                'value' => 'Instagram',
                'label' => 'Instagram',
            ],
            [
                'value' => 'Friend',
                'label' => 'Friend',
            ],
            [
                'value' => 'Google',
                'label' => 'Google',
            ],
            [
                'value' => 'doctor911.am',
                'label' => 'doctor911.am',
            ],
            [
                'value' => 'whatsapp',
                'label' => 'WhatsApp',
            ],
            [
                'value' => 'viber',
                'label' => 'Viber',
            ],
            [
                'value' => 'yell.am',
                'label' => 'yell.am',
            ],
            [
                'value' => 'zang',
                'label' => 'Zang',
            ],
            [
                'value' => 'այլ',
                'label' => 'այլ',
            ],
        ];
        $hospitals = $this->hospitalRepository->fetch("id as value, name as label", [], [
            'field' => 'id',
            'direction' => 'asc',
        ], [], [], []);

        $diseases = $this->diseaseRepository->fetch("id as value, name as label", [], [
            'field' => 'id',
            'direction' => 'asc',
        ], [], [], []);

        $users = $this->userRepository->fetchUser(
            "id as value, CONCAT(name, ' ', last_name) as label", [],
            [
                'field' => 'id',
                'direction' => 'desc',
            ], ['source' => 0], [], []
        );

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'years' => $years,
            'months' => $months,
            'colors' => $colors,
            'users' => $users,
            'findAboutUS' => $findAboutUS,
            'hospitals' => $hospitals,
            'diseases' => $diseases,
        ]);
    }

}
