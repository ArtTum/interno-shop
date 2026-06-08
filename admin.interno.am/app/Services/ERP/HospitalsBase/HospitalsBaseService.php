<?php

namespace App\Services\ERP\HospitalsBase;

use App\Models\Disease;
use App\Models\Hospital;
use App\Repositories\Disease\DiseaseRepository;
use App\Repositories\Hospital\HospitalRepository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\User\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class HospitalsBaseService
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
        $data['type'] = ['incoming', 'recommendations', 'hospitals-base'];
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

        $search = $data['search'] ?? '';

        // Only wrap with REPLACE() when search actually contains special dot chars
        $hasSpecialDots = $search && preg_match('/[։·‥‧⋅]/u', $search);
        $colExpr = $hasSpecialDots
            ? fn(string $col) => "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE({$col}, '\u{0589}', '.'), '\u{00B7}', '.'), '\u{2024}', '.'), '\u{2027}', '.'), '\u{22C5}', '.')"
            : fn(string $col) => $col;

        // DATE_FORMAT is only useful when search looks like a date (contains digits)
        $searchLooksLikeDate = $search && preg_match('/\d/', $search);

        $hospitalIds = [];
        $diseaseIds = [];
        if ($search) {
            $escaped = addslashes($search);
            $hospitalIds = Hospital::where('name', 'LIKE', "%{$escaped}%")->pluck('id')->toArray();
            $diseaseIds  = Disease::where('name',  'LIKE', "%{$escaped}%")->pluck('id')->toArray();
        }

        $searchFields = [
            ['raw' => $colExpr('patient_full_name')],
            'phone',
            'other_phone',
            'call_date',
            ...($searchLooksLikeDate ? [
                ['raw' => "DATE_FORMAT(call_date, '%d.%m.%Y')"],
                ['raw' => "DATE_FORMAT(departure_datetime, '%d.%m.%Y %H:%i')"],
            ] : []),
            ['raw' => $colExpr('additional_data')],
            ['raw' => $colExpr('disease')],
            ['raw' => $colExpr('medical_and_doctor')],
            ['whereIn' => true, 'field' => 'hospital_id', 'values' => $hospitalIds],
            ['whereIn' => true, 'field' => 'disease_id',  'values' => $diseaseIds],
        ];

        [$rows, $totalCount] = $this->repository->fetchAndCountHospitalsBase($select, $pagination, $ordering, $data, $searchFields, []);

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $rows,
            'pagination' => prepare_pagination($data['page'], $data['per_page'], $totalCount)
        ]);
    }

    public function fetch2(array $data): JsonResponse
    {
//        $data['type'] = ['hospitals-base'];
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

        $search = $data['search'] ?? '';

        // Only wrap with REPLACE() when search actually contains special dot chars
        $hasSpecialDots = $search && preg_match('/[։·‥‧⋅]/u', $search);
        $colExpr = $hasSpecialDots
            ? fn(string $col) => "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE({$col}, '\u{0589}', '.'), '\u{00B7}', '.'), '\u{2024}', '.'), '\u{2027}', '.'), '\u{22C5}', '.')"
            : fn(string $col) => $col;

        // DATE_FORMAT is only useful when search looks like a date (contains digits)
        $searchLooksLikeDate = $search && preg_match('/\d/', $search);

        $hospitalIds = [];
        $diseaseIds = [];
        if ($search) {
            $escaped = addslashes($search);
            $hospitalIds = Hospital::where('name', 'LIKE', "%{$escaped}%")->pluck('id')->toArray();
            $diseaseIds  = Disease::where('name',  'LIKE', "%{$escaped}%")->pluck('id')->toArray();
        }

        $searchFields = [
            ['raw' => $colExpr('patient_full_name')],
            'phone',
            'other_phone',
            'call_date',
            ...($searchLooksLikeDate ? [
                ['raw' => "DATE_FORMAT(call_date, '%d.%m.%Y')"],
                ['raw' => "DATE_FORMAT(departure_datetime, '%d.%m.%Y %H:%i')"],
            ] : []),
            ['raw' => $colExpr('additional_data')],
            ['raw' => $colExpr('disease')],
            ['raw' => $colExpr('medical_and_doctor')],
            ['whereIn' => true, 'field' => 'hospital_id', 'values' => $hospitalIds],
            ['whereIn' => true, 'field' => 'disease_id',  'values' => $diseaseIds],
        ];

        [$rows, $totalCount] = $this->repository->fetchAndCount($select, $pagination, $ordering, $data, $searchFields, []);

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $rows,
            'pagination' => prepare_pagination($data['page'], $data['per_page'], $totalCount)
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
        $data['type'] = 'hospitals-base';
        $data['copy'] = 0;
        $this->repository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!'
        ]);
    }

    public function update(array $data): JsonResponse
    {
//        $data['type'] = 'hospitals-base';
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
