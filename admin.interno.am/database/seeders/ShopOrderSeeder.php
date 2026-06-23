<?php

namespace Database\Seeders;

use App\Models\ShopCraftsman;
use App\Models\ShopOrder;
use Illuminate\Database\Seeder;

class ShopOrderSeeder extends Seeder
{
    public function run(): void
    {
        ShopOrder::query()->truncate();

        $craftsmen = ShopCraftsman::query()->where('status', true)
            ->get(['id', 'code', 'first_name', 'last_name'])
            ->keyBy('code');

        foreach ($this->orders() as $data) {
            $code = $data['craftsman_code'];
            unset($data['craftsman_code']);

            $cr = $code ? $craftsmen->get($code) : null;
            $data['craftsman_id']   = $cr?->id;
            $data['craftsman_code'] = $code;
            $data['craftsman_name'] = $cr ? trim($cr->first_name . ' ' . $cr->last_name) : null;

            ShopOrder::query()->create($data);
        }
    }

    private function product(int $id, string $hy, string $ru, string $en, string $price, array $options, int $qty = 1): array
    {
        return [
            'productId' => $id,
            'quantity'  => $qty,
            'product'   => [
                'title' => ['hy' => $hy, 'ru' => $ru, 'en' => $en],
                'price' => $price,
                'kind'  => $options['kind'] ?? null,
                'image' => $options['image'] ?? null,
                'options' => array_filter([
                    'code'     => $options['code']     ?? null,
                    'size'     => $options['size']     ?? null,
                    'height'   => $options['height']   ?? null,
                    'unit'     => $options['unit']     ?? null,
                    'piece'    => $options['piece']    ?? null,
                    'type'     => $options['type']     ?? null,
                    'material' => $options['material'] ?? null,
                    'color'    => $options['color']    ?? null,
                    'quantity' => $options['qty_opt']  ?? null,
                ], fn($v) => $v !== null && $v !== ''),
            ],
        ];
    }

    private function orders(): array
    {
        return [
            // ── Order 1 ── 3 ապրանք, craftsman ARH-1001
            [
                'status'               => 'new',
                'language'             => 'hy',
                'customer_first_name'  => 'Անի',
                'customer_last_name'   => 'Հովհաննիսյան',
                'customer_phone'       => '+374 99 112 233',
                'customer_email'       => 'ani.hovh@mail.ru',
                'customer_address'     => 'Երևան, Կոմիտաս 42, բն. 18',
                'customer_master_code' => null,
                'craftsman_code'       => 'ARH-1001',
                'items' => [
                    $this->product(1, 'Ալյումինե, երկաթե հիմք նուրբ սև գույնով', 'Алюминиевый профиль черный', 'Aluminum profile black', '15000', [
                        'code' => 'ALU-BLK-001', 'size' => '3 մ', 'height' => '60 մմ', 'unit' => 'մ',
                        'piece' => '4', 'type' => 'Անկյունային', 'material' => 'Ալյումին', 'color' => '#1f1f1f',
                    ], 2),
                    $this->product(2, 'Արծաթագույն պրոֆիլ ձգվող առաստաղների համար', 'Серебристый профиль', 'Silver stretch ceiling profile', '15000', [
                        'code' => 'ALU-SLV-002', 'size' => '2.5 մ', 'height' => '55 մմ', 'unit' => 'մ',
                        'piece' => '6', 'type' => 'Ուղիղ', 'material' => 'Ալյումին', 'color' => '#c0c0c0',
                    ], 1),
                    $this->product(9, 'Արծաթագույն պրոֆիլ LED լուսավորության ալիքով', 'Профиль LED канал', 'Silver LED channel profile', '18000', [
                        'code' => 'LED-SLV-009', 'size' => '2 մ', 'height' => '40 մմ', 'unit' => 'մ',
                        'piece' => '2', 'type' => 'LED ալիք', 'material' => 'Ալյումին', 'color' => '#d4d4d4',
                    ], 3),
                ],
                'total'      => 15000 * 2 + 15000 + 18000 * 3,
                'created_at' => now()->subHours(3),
            ],

            // ── Order 2 ── 2 ապրանք, processing, craftsman ARH-1002
            [
                'status'               => 'processing',
                'language'             => 'ru',
                'customer_first_name'  => 'Наталья',
                'customer_last_name'   => 'Степанян',
                'customer_phone'       => '+374 91 334 455',
                'customer_email'       => 'natalya.st@yandex.ru',
                'customer_address'     => 'Абовян, Котайк 15/3',
                'customer_master_code' => 'MST-0042',
                'craftsman_code'       => 'ARH-1002',
                'items' => [
                    $this->product(5, 'Փայտային պանել պատերի և առաստաղի հարդարման համար', 'Деревянная панель для отделки', 'Wood panel for wall & ceiling', '22000', [
                        'code' => 'WD-OAK-005', 'size' => '120×20 սմ', 'height' => '20 մմ', 'unit' => 'հատ',
                        'piece' => '10', 'type' => 'Դեկորատիվ', 'material' => 'MDF կաղնու', 'color' => '#8B6914',
                    ], 4),
                    $this->product(10, 'Փայտային դեկորատիվ պանել տաք երանգով', 'Деревянная декор панель тёплая', 'Warm-tone decorative wood panel', '19000', [
                        'code' => 'WD-WAL-010', 'size' => '100×15 սմ', 'height' => '15 մմ', 'unit' => 'հատ',
                        'piece' => '8', 'type' => 'Դեկորատիվ', 'material' => 'MDF ընկուզենի', 'color' => '#6B3A2A',
                    ], 2),
                ],
                'total'      => 22000 * 4 + 19000 * 2,
                'created_at' => now()->subDays(1),
            ],

            // ── Order 3 ── 4 ապրանք, completed, craftsman ARH-1003
            [
                'status'               => 'completed',
                'language'             => 'hy',
                'customer_first_name'  => 'Կարեն',
                'customer_last_name'   => 'Մկրտչյան',
                'customer_phone'       => '+374 55 223 344',
                'customer_email'       => 'karen.mkr@gmail.com',
                'customer_address'     => 'Գյումրի, Շիրակ 8, բն. 3',
                'customer_master_code' => null,
                'craftsman_code'       => 'ARH-1003',
                'items' => [
                    $this->product(3, 'Սև անկյունային պրոֆիլ ճշգրիտ ամրացման համար', 'Черный угловой профиль', 'Black corner profile precision mount', '15000', [
                        'code' => 'ALU-BLK-003', 'size' => '2 մ', 'height' => '50 մմ', 'unit' => 'մ',
                        'piece' => '8', 'type' => 'Անկյունային', 'material' => 'Ալյումին', 'color' => '#111111',
                    ], 5),
                    $this->product(4, 'Արծաթագույն ամուր պրոֆիլ լուսային գծերի համար', 'Серебристый жёсткий профиль', 'Silver rigid light-line profile', '15000', [
                        'code' => 'ALU-SLV-004', 'size' => '3 մ', 'height' => '65 մմ', 'unit' => 'մ',
                        'piece' => '6', 'type' => 'Ուղիղ', 'material' => 'Ալյումին', 'color' => '#b8b8b8',
                    ], 3),
                    $this->product(6, 'Սև պրոֆիլ մինիմալ ինտերիերի լուծումների համար', 'Черный профиль минимал', 'Black minimal interior profile', '15000', [
                        'code' => 'ALU-BLK-006', 'size' => '2.5 մ', 'height' => '45 մմ', 'unit' => 'մ',
                        'piece' => '4', 'type' => 'Բարակ', 'material' => 'Ալյումին', 'color' => '#0a0a0a',
                    ], 2),
                    $this->product(8, 'Սև պրոֆիլ թաքնված ամրացման համակարգերի համար', 'Черный профиль скрытое крепление', 'Black hidden-mount profile', '17000', [
                        'code' => 'ALU-BLK-008', 'size' => '3 մ', 'height' => '70 մմ', 'unit' => 'մ',
                        'piece' => '3', 'type' => 'Թաքնված', 'material' => 'Ալյումին', 'color' => '#1a1a1a',
                    ], 1),
                ],
                'total'      => 15000 * 5 + 15000 * 3 + 15000 * 2 + 17000,
                'created_at' => now()->subDays(5),
            ],

            // ── Order 4 ── 2 ապրանք, new, craftsman ARH-1004
            [
                'status'               => 'new',
                'language'             => 'en',
                'customer_first_name'  => 'Artur',
                'customer_last_name'   => 'Davtyan',
                'customer_phone'       => '+374 93 556 677',
                'customer_email'       => 'artur.d@gmail.com',
                'customer_address'     => 'Yerevan, Mashtots 22, apt. 5',
                'customer_master_code' => 'MST-0088',
                'craftsman_code'       => 'ARH-1004',
                'items' => [
                    $this->product(7, 'Արծաթագույն մոնտաժային պրոֆիլ արագ տեղադրման համար', 'Серебристый монтажный профиль', 'Silver fast-install mount profile', '16000', [
                        'code' => 'ALU-SLV-007', 'size' => '3 մ', 'height' => '60 մմ', 'unit' => 'մ',
                        'piece' => '5', 'type' => 'Մոնտաժային', 'material' => 'Ալյումին', 'color' => '#cecece',
                    ], 4),
                    $this->product(9, 'Արծաթագույն պրոֆիլ LED լուսավորության ալիքով', 'Профиль LED канал серебро', 'Silver LED channel profile', '18000', [
                        'code' => 'LED-SLV-009', 'size' => '2 մ', 'height' => '40 մմ', 'unit' => 'մ',
                        'piece' => '6', 'type' => 'LED ալիք', 'material' => 'Ալյումին', 'color' => '#d4d4d4',
                    ], 2),
                ],
                'total'      => 16000 * 4 + 18000 * 2,
                'created_at' => now()->subHours(10),
            ],

            // ── Order 5 ── 1 ապրանք, cancelled, no craftsman
            [
                'status'               => 'cancelled',
                'language'             => 'hy',
                'customer_first_name'  => 'Մարինե',
                'customer_last_name'   => 'Գրիգորյան',
                'customer_phone'       => '+374 77 667 788',
                'customer_email'       => 'marine.gr@mail.ru',
                'customer_address'     => 'Վանաձոր, Տիգրանյան 5',
                'customer_master_code' => null,
                'craftsman_code'       => null,
                'items' => [
                    $this->product(5, 'Փայտային պանել պատերի և առաստաղի հարդարման համար', 'Деревянная панель', 'Wood panel', '22000', [
                        'code' => 'WD-OAK-005', 'size' => '120×20 սմ', 'height' => '20 մմ', 'unit' => 'հատ',
                        'piece' => '5', 'type' => 'Դեկորատիվ', 'material' => 'MDF կաղնու', 'color' => '#8B6914',
                    ], 1),
                ],
                'total'      => 22000,
                'created_at' => now()->subDays(12),
            ],
        ];
    }
}
