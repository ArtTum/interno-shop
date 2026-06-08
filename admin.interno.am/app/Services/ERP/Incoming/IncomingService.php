<?php

namespace App\Services\ERP\Incoming;

use App\Export\ERP\DefaultExport;
use App\Repositories\Disease\DiseaseRepository;
use App\Repositories\Hospital\HospitalRepository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\User\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IncomingService
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
        $data['type'] = 'incoming';
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
            ['raw' => "DATE_FORMAT(departure_datetime, '%d.%m.%Y %H:%i')"],
            ['raw' => $normalizeDots('additional_data')],
            ['raw' => $normalizeDots('disease')],
            ['raw' => $normalizeDots('medical_and_doctor')],
            'price',
            'sale_price',
            'a_d',
            'sale',
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

    public function fetchStats(array $data): JsonResponse
    {
        $data['type'] = 'incoming';
        $ordering = [
            'field' => $data['ordering_field'] ?? 'id',
            'direction' => $data['ordering_direction'] ?? 'desc'
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
            ['raw' => "DATE_FORMAT(departure_datetime, '%d.%m.%Y %H:%i')"],
            ['raw' => $normalizeDots('additional_data')],
            ['raw' => $normalizeDots('disease')],
            ['raw' => $normalizeDots('medical_and_doctor')],
            'price',
            'sale_price',
            'a_d',
            'sale',
            ['relation_name' => 'hospitalRecord', 'fields' => ['name']],
            ['relation_name' => 'diseaseRecord', 'fields' => ['name']],
        ];

        $sums = $this->repository->fetchSums($ordering, $data, $searchFields);

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'sum' => $sums->sum,
            'sale_price' => $sums->sale_price,
            'a_d' => $sums->a_d,
        ]);
    }

    public function fetchByField(array $data): JsonResponse
    {
        $select = "*";
        $response = $this->repository->fetchByFieldWith('id', $data['id'], $select);

        if (!empty($response->call_date)) {
            try {
                $response->call_date = Carbon::parse($response->call_date)->format('Y.m.d');
            } catch (\Exception $e) {}
        }

        if (!empty($response->next_call_date)) {
            try {
                $response->next_call_date = Carbon::parse($response->next_call_date)->format('Y.m.d');
            } catch (\Exception $e) {}
        }

        if (!empty($response->day_surgery)) {
            try {
                $response->day_surgery = Carbon::parse($response->day_surgery)->format('Y.m.d');
            } catch (\Exception $e) {}
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $response
        ]);
    }

    public function insert(array $data): JsonResponse
    {
        $data['type'] = 'incoming';
        $data['copy'] = 0;
        $this->repository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!'
        ]);
    }

    public function update(array $data): JsonResponse
    {
        $current = $this->repository->fetchByField('id', $data['id'], 'type, incoming_color');
        if ($current && $current->type !== 'incoming') {
            $data['color'] = '#00B050';
        }
        $data['type'] = 'incoming';
        $data['updated_at'] = now();
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

    public function export(array $data): BinaryFileResponse
    {
        $data['type'] = 'incoming';
        $ordering = [
            'field' => $data['ordering_field'] ?? 'id',
            'direction' => $data['ordering_direction'] ?? 'desc'
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
            ['raw' => "DATE_FORMAT(departure_datetime, '%d.%m.%Y %H:%i')"],
            ['raw' => $normalizeDots('additional_data')],
            ['raw' => $normalizeDots('disease')],
            ['raw' => $normalizeDots('medical_and_doctor')],
            'price',
            'sale_price',
            'a_d',
            'sale',
            ['relation_name' => 'hospitalRecord', 'fields' => ['name']],
            ['relation_name' => 'diseaseRecord', 'fields' => ['name']],
        ];

        $records = $this->repository->fetch('*', [], $ordering, $data, $searchFields, []);

        $headers = [
            '#',
            'Վիրահատության օր',
            'Անուն Ազգանուն',
            'Հեռախոս',
            'Հիվանդություն',
            'Բուժհաստատություն և բժիշկ',
            'Չզեղչված գին',
            'Զեղչված գին',
            'AD',
            'Զեղչ',
        ];

        $collectData = collect($records->map(function ($item, $index) {
            $hospital = optional($item->hospitalRecord)->name;
            $doctor = $item->medical_and_doctor;
            $hospitalAndDoctor = trim(($hospital ? $hospital . ' ' : '') . ($doctor ?? ''));
            $disease = $item->disease ?? optional($item->diseaseRecord)->name ?? '';

            return [
                $index + 1,
                $item->day_surgery ? \Carbon\Carbon::parse($item->day_surgery)->format('d.m.Y') : '',
                $item->patient_full_name,
                $item->phone,
                $disease,
                $hospitalAndDoctor,
                $item->price,
                $item->sale_price,
                $item->a_d,
                $item->sale,
            ];
        }));

        return Excel::download(new DefaultExport($collectData, $headers), 'incomings.xlsx');
    }

}
