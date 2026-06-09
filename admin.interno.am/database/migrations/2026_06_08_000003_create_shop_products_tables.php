<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_category_id')->nullable()->constrained('shop_categories')->nullOnDelete();
            $table->string('slug')->unique();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('kind')->nullable();
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_new')->default(false)->index();
            $table->boolean('status')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('shop_product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->unsignedInteger('language_id');
            $table->string('title');
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->timestamps();

            $table->unique(['shop_product_id', 'language_id'], 'shop_product_language_unique');
            $table->foreign('language_id', 'shop_product_language_fk')->references('id')->on('languages')->cascadeOnDelete();
        });

        $languageIds = DB::table('languages')->pluck('id', 'code');
        $categoryId = DB::table('shop_categories')->where('slug', 'stretch-1')->value('id')
            ?: DB::table('shop_categories')->where('slug', 'stretch')->value('id');

        if ($languageIds->isNotEmpty()) {
            $products = [
                ['id' => 1, 'slug' => 'profile-black-fine', 'title' => ['hy' => 'Ալյումինե, երկաթե հիմք նուրբ սև գույնով', 'en' => 'Aluminum profile with a refined black finish', 'ru' => 'Алюминиевый профиль с черным покрытием'], 'price' => 15000, 'kind' => 'black', 'image' => '/assets/products/profile-black.png'],
                ['id' => 2, 'slug' => 'profile-silver-stretch', 'title' => ['hy' => 'Արծաթագույն պրոֆիլ ձգվող առաստաղների համար', 'en' => 'Silver profile for stretch ceiling systems', 'ru' => 'Серебристый профиль для натяжных потолков'], 'price' => 15000, 'kind' => 'silver', 'image' => '/assets/products/profile-silver.png'],
                ['id' => 3, 'slug' => 'profile-black-corner', 'title' => ['hy' => 'Սև անկյունային պրոֆիլ ճշգրիտ ամրացման համար', 'en' => 'Black corner profile for precise installation', 'ru' => 'Черный угловой профиль для точного монтажа'], 'price' => 15000, 'kind' => 'black', 'image' => '/assets/products/profile-black.png'],
                ['id' => 4, 'slug' => 'profile-silver-light-lines', 'title' => ['hy' => 'Արծաթագույն ամուր պրոֆիլ լուսային գծերի համար', 'en' => 'Durable silver profile for light lines', 'ru' => 'Прочный серебристый профиль для световых линий'], 'price' => 15000, 'kind' => 'silver', 'image' => '/assets/products/profile-silver.png'],
                ['id' => 5, 'slug' => 'panel-wood-wall-ceiling', 'title' => ['hy' => 'Փայտային պանել պատերի և առաստաղի հարդարման համար', 'en' => 'Wood-look panel for wall and ceiling finishes', 'ru' => 'Панель под дерево для стен и потолков'], 'price' => 15000, 'kind' => 'wood', 'image' => '/assets/products/panel-wood.png'],
                ['id' => 6, 'slug' => 'profile-black-minimal', 'title' => ['hy' => 'Սև պրոֆիլ մինիմալ ինտերիերի լուծումների համար', 'en' => 'Black profile for minimal interior solutions', 'ru' => 'Черный профиль для минималистичных интерьеров'], 'price' => 15000, 'kind' => 'black', 'image' => '/assets/products/profile-black.png'],
                ['id' => 7, 'slug' => 'profile-silver-mounting', 'title' => ['hy' => 'Արծաթագույն մոնտաժային պրոֆիլ արագ տեղադրման համար', 'en' => 'Silver mounting profile for fast installation', 'ru' => 'Серебристый монтажный профиль для быстрой установки'], 'price' => 15000, 'kind' => 'silver', 'image' => '/assets/products/profile-silver.png'],
                ['id' => 8, 'slug' => 'profile-black-concealed', 'title' => ['hy' => 'Սև պրոֆիլ թաքնված ամրացման համակարգերի համար', 'en' => 'Black profile for concealed mounting systems', 'ru' => 'Черный профиль для скрытых систем крепления'], 'price' => 15000, 'kind' => 'black', 'image' => '/assets/products/profile-black.png'],
                ['id' => 9, 'slug' => 'profile-silver-led-channel', 'title' => ['hy' => 'Արծաթագույն պրոֆիլ LED լուսավորության ալիքով', 'en' => 'Silver profile with LED lighting channel', 'ru' => 'Серебристый профиль с каналом для LED'], 'price' => 15000, 'kind' => 'silver', 'image' => '/assets/products/profile-silver.png'],
                ['id' => 10, 'slug' => 'panel-wood-warm-tone', 'title' => ['hy' => 'Փայտային դեկորատիվ պանել տաք երանգով', 'en' => 'Warm-tone decorative wood-look panel', 'ru' => 'Декоративная панель под дерево теплого оттенка'], 'price' => 15000, 'kind' => 'wood', 'image' => '/assets/products/panel-wood.png'],
            ];

            foreach ($products as $index => $product) {
                DB::table('shop_products')->insert([
                    'id' => $product['id'],
                    'shop_category_id' => $categoryId,
                    'slug' => $product['slug'],
                    'price' => $product['price'],
                    'kind' => $product['kind'],
                    'image' => $product['image'],
                    'gallery' => json_encode([$product['image']]),
                    'options' => json_encode(['code' => '111', 'size' => 'Պրոֆիլ', 'quantity' => '1', 'type' => 'Տեսակ', 'unit' => '111', 'piece' => '44', 'height' => '111', 'material' => 'Նյութի անուն', 'color' => '#1f1f1f']),
                    'is_new' => true,
                    'status' => true,
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($product['title'] as $code => $title) {
                    if (isset($languageIds[$code])) {
                        DB::table('shop_product_translations')->insert([
                            'shop_product_id' => $product['id'],
                            'language_id' => $languageIds[$code],
                            'title' => $title,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_product_translations');
        Schema::dropIfExists('shop_products');
    }
};
