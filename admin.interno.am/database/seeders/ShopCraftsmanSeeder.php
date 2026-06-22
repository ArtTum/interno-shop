<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\ShopCraftsman;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ShopCraftsmanSeeder extends Seeder
{
    public function run(): void
    {
        $craftsmen = [
            [
                'code' => 'ARH-1001',
                'first_name' => 'Արման',
                'last_name' => 'Մկրտչյան',
                'phone' => '+374 91 100 101',
                'work_region' => 'Երևան',
                'work_city' => 'Արաբկիր',
                'work_field' => 'Ձգվող առաստաղներ',
                'has_whatsapp' => true,
                'has_viber' => true,
                'sort_order' => 1,
                'photo' => 'worker-seed-001.png',
            ],
            [
                'code' => 'ARH-1002',
                'first_name' => 'Գոռ',
                'last_name' => 'Սարգսյան',
                'phone' => '+374 93 200 202',
                'work_region' => 'Կոտայք',
                'work_city' => 'Աբովյան',
                'work_field' => 'ՄԴՖ շրիշակ',
                'has_whatsapp' => true,
                'has_viber' => false,
                'sort_order' => 2,
                'photo' => 'worker-seed-002.png',
            ],
            [
                'code' => 'ARH-1003',
                'first_name' => 'Հայկ',
                'last_name' => 'Հակոբյան',
                'phone' => '+374 77 300 303',
                'work_region' => 'Արմավիր',
                'work_city' => 'Էջմիածին',
                'work_field' => 'Ալյումինե պրոֆիլ',
                'has_whatsapp' => false,
                'has_viber' => true,
                'sort_order' => 3,
                'photo' => 'worker-seed-003.png',
            ],
            [
                'code' => 'ARH-1004',
                'first_name' => 'Դավիթ',
                'last_name' => 'Ավետիսյան',
                'phone' => '+374 55 400 404',
                'work_region' => 'Շիրակ',
                'work_city' => 'Գյումրի',
                'work_field' => 'Լուսավորություն',
                'has_whatsapp' => true,
                'has_viber' => true,
                'sort_order' => 4,
                'photo' => 'worker-seed-004.png',
            ],
        ];

        foreach ($craftsmen as $craftsman) {
            $photo = $craftsman['photo'];
            unset($craftsman['photo']);

            $craftsman['media_id'] = $this->mediaIdForPhoto($photo);
            $craftsman['status'] = true;

            ShopCraftsman::query()->updateOrCreate(
                ['code' => $craftsman['code']],
                $craftsman
            );
        }
    }

    private function mediaIdForPhoto(string $fileName): ?int
    {
        $relativePath = "/assets/craftsmen/{$fileName}";
        $path = public_path($relativePath);

        if (!File::exists($path)) {
            return null;
        }

        $media = Media::query()->updateOrCreate(
            ['file_name' => $fileName],
            [
                'user_id' => 1,
                'original_path' => $relativePath,
                'path' => null,
                'file_size' => (int) ceil(File::size($path) / 1024),
                'width' => 320,
                'height' => 400,
                'file_type' => 'image/png',
                'type' => 'images',
            ]
        );

        return $media->id;
    }
}
