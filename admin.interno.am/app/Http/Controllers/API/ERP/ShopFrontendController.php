<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\ShopCategory;
use App\Models\ShopPrivacyPolicy;
use App\Models\ShopPrivacyPolicyChecklistItem;
use App\Models\ShopProduct;
use App\Models\ShopSeoPage;
use App\Models\ShopPrivacyPolicySection;
use App\Models\Social;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ShopFrontendController extends Controller
{
    private string $configPath;
    private string $ordersPath;

    public function __construct()
    {
        $this->configPath = storage_path('app/shop_frontend.json');
        $this->ordersPath = storage_path('app/shop_orders.json');
    }

    public function publicConfig(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->readConfig(),
        ]);
    }

    public function fetch(): JsonResponse
    {
        return $this->publicConfig();
    }

    public function update(Request $request): JsonResponse
    {
        $payload = $request->all();

        $validator = Validator::make($payload, [
            'languages' => ['required', 'array', 'min:1'],
            'menuGroups' => ['required', 'array'],
            'translations' => ['required', 'array'],
            'products' => ['required', 'array'],
            'products.*.id' => ['required'],
            'settings' => ['required', 'array'],
            'seo' => ['nullable', 'array'],
            'privacy' => ['required', 'array'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->syncLanguagesToDatabase($payload['languages']);
        $this->syncCategoriesToDatabase($payload['menuGroups']);
        $this->syncProductsToDatabase($payload['products']);
        $this->syncSocialLinksToDatabase($payload['settings']['socialLinks'] ?? []);
        $this->syncSeoToDatabase($payload['seo'] ?? []);
        $this->syncPrivacyToDatabase($payload['privacy'] ?? []);
        $this->writeJson($this->configPath, $payload);

        return response()->json([
            'success' => true,
            'message' => 'Shop frontend settings saved.',
            'data' => $payload,
        ]);
    }

    public function storeOrder(Request $request): JsonResponse
    {
        $payload = $request->all();

        $validator = Validator::make($payload, [
            'customer.firstName' => ['required', 'string', 'max:120'],
            'customer.lastName' => ['required', 'string', 'max:120'],
            'customer.phone' => ['required', 'string', 'max:80'],
            'customer.email' => ['required', 'email', 'max:160'],
            'customer.address' => ['required', 'string', 'max:500'],
            'customer.masterCode' => ['nullable', 'string', 'max:120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.productId' => ['required'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'total' => ['required', 'numeric', 'min:0'],
            'language' => ['nullable', 'string', 'max:5'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $orders = $this->readOrders();
        $nextId = empty($orders) ? 1 : max(array_column($orders, 'id')) + 1;

        $order = [
            'id' => $nextId,
            'status' => 'new',
            'created_at' => now()->toDateTimeString(),
            'customer' => $payload['customer'],
            'items' => $payload['items'],
            'total' => $payload['total'],
            'language' => $payload['language'] ?? 'hy',
        ];

        array_unshift($orders, $order);
        $this->writeJson($this->ordersPath, $orders);

        return response()->json([
            'success' => true,
            'message' => 'Order created.',
            'data' => $order,
        ], 201);
    }

    public function orders(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->readOrders(),
        ]);
    }

    private function readConfig(): array
    {
        $default = config('shop_frontend', []);
        $stored = $this->readJson($this->configPath, []);

        $config = array_replace_recursive($default, $stored);
        $databaseLanguages = $this->readLanguagesFromDatabase($config['languages'] ?? []);

        if (!empty($databaseLanguages)) {
            $config['languages'] = $databaseLanguages;
        }

        $databaseCategories = $this->readCategoriesFromDatabase($config['languages'] ?? []);

        if (!empty($databaseCategories)) {
            $config['menuGroups'] = $databaseCategories;
        }

        $databaseProducts = $this->readProductsFromDatabase($config['languages'] ?? []);

        if (!empty($databaseProducts)) {
            $config['products'] = $databaseProducts;
        }

        $databaseSocialLinks = $this->readSocialLinksFromDatabase();

        if (!empty($databaseSocialLinks)) {
            $config['settings']['socialLinks'] = $databaseSocialLinks;
        }

        $databaseSeo = $this->readSeoFromDatabase($config['languages'] ?? []);

        if (!empty($databaseSeo)) {
            $config['seo'] = $databaseSeo;
        }

        $databasePrivacy = $this->readPrivacyFromDatabase();

        if (!empty($databasePrivacy)) {
            $config['privacy'] = $databasePrivacy;
        }

        return $config;
    }

    private function readOrders(): array
    {
        return $this->readJson($this->ordersPath, []);
    }

    private function readJson(string $path, array $fallback): array
    {
        if (!File::exists($path)) {
            return $fallback;
        }

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : $fallback;
    }

    private function writeJson(string $path, array $payload): void
    {
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function readLanguagesFromDatabase(array $configuredLanguages): array
    {
        if (!Schema::hasTable('languages')) {
            return [];
        }

        $configuredByCode = collect($configuredLanguages)->keyBy('code');

        return Language::query()
            ->where('status', true)
            ->orderByDesc('base')
            ->orderBy('code')
            ->get(['code', 'name'])
            ->map(function (Language $language) use ($configuredByCode) {
                $configured = $configuredByCode->get($language->code, []);

                return [
                    'code' => $language->code,
                    'label' => $language->name,
                    'icon' => $configured['icon'] ?? "/assets/icons/flag-{$language->code}.svg",
                ];
            })
            ->values()
            ->all();
    }

    private function syncLanguagesToDatabase(array $languages): void
    {
        if (!Schema::hasTable('languages')) {
            return;
        }

        $codes = collect($languages)
            ->pluck('code')
            ->filter()
            ->map(fn ($code) => strtolower(trim($code)))
            ->unique()
            ->values();

        if ($codes->isEmpty()) {
            return;
        }

        Language::query()
            ->whereNotIn('code', $codes->all())
            ->update(['status' => false]);

        foreach ($languages as $index => $language) {
            $code = strtolower(trim($language['code'] ?? ''));

            if ($code === '') {
                continue;
            }

            Language::query()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $language['label'] ?? strtoupper($code),
                    'status' => true,
                    'base' => $index === 0,
                    'hreflang' => $code,
                    'local_for_trustpilot' => $code,
                    'default_hreflang' => $index === 0,
                    'is_rtl' => false,
                ]
            );
        }
    }

    private function readCategoriesFromDatabase(array $languages): array
    {
        if (!Schema::hasTable('shop_categories') || !Schema::hasTable('shop_category_translations')) {
            return [];
        }

        $languageCodes = collect($languages)->pluck('code')->filter()->values();

        if ($languageCodes->isEmpty()) {
            return [];
        }

        $categories = ShopCategory::query()
            ->whereNull('parent_id')
            ->where('status', true)
            ->with([
                'translations.language:id,code',
                'children' => function ($query) {
                    $query->where('status', true)->orderBy('sort_order')->orderBy('id');
                },
                'children.translations.language:id,code',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($categories->isEmpty()) {
            return [];
        }

        return $categories->map(function (ShopCategory $category) use ($languageCodes) {
            return [
                'key' => $category->slug,
                'title' => $this->mapTranslationsByLanguage($category, $languageCodes),
                'meta' => $this->mapCategoryMetaByLanguage($category, $languageCodes),
                'children' => $languageCodes
                    ->mapWithKeys(function (string $code) use ($category) {
                        return [
                            $code => $category->children
                                ->map(fn (ShopCategory $child) => $this->categoryTitleForLanguage($child, $code))
                                ->values()
                                ->all(),
                        ];
                      })
                      ->all(),
                'childMeta' => $languageCodes
                    ->mapWithKeys(function (string $code) use ($category) {
                        return [
                            $code => $category->children
                                ->map(fn (ShopCategory $child) => $this->categoryMetaForLanguage($child, $code))
                                ->values()
                                ->all(),
                        ];
                    })
                    ->all(),
            ];
        })->values()->all();
    }

    private function syncCategoriesToDatabase(array $menuGroups): void
    {
        if (!Schema::hasTable('languages') || !Schema::hasTable('shop_categories') || !Schema::hasTable('shop_category_translations')) {
            return;
        }

        $languages = Language::query()->where('status', true)->get(['id', 'code'])->keyBy('code');

        if ($languages->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($menuGroups, $languages) {
            $activeParentSlugs = [];

            foreach (array_values($menuGroups) as $categoryIndex => $group) {
                $slug = $this->normalizeSlug($group['key'] ?? null, 'category-' . ($categoryIndex + 1));
                $activeParentSlugs[] = $slug;

                $category = ShopCategory::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'parent_id' => null,
                        'status' => true,
                        'sort_order' => $categoryIndex,
                    ]
                );

                $this->syncCategoryTranslations($category, $group['title'] ?? [], $languages);
                $this->syncSubcategories($category, $group['children'] ?? [], $languages);
            }

            ShopCategory::query()
                ->whereNull('parent_id')
                ->whereNotIn('slug', $activeParentSlugs)
                ->update(['status' => false]);
        });
    }

    private function syncSubcategories(ShopCategory $parent, array $childrenByLanguage, $languages): void
    {
        $maxChildren = collect($childrenByLanguage)
            ->filter(fn ($children) => is_array($children))
            ->map(fn ($children) => count($children))
            ->max() ?? 0;

        $activeChildSlugs = [];

        for ($index = 0; $index < $maxChildren; $index++) {
            $baseChildren = collect($childrenByLanguage)->first(fn ($children) => is_array($children) && !empty($children[$index]));
            $baseTitle = is_array($baseChildren) ? ($baseChildren[$index] ?? null) : null;
            $slug = $this->normalizeSlug($baseTitle, "{$parent->slug}-" . ($index + 1));
            $slug = Str::startsWith($slug, $parent->slug . '-') ? $slug : "{$parent->slug}-{$slug}";
            $activeChildSlugs[] = $slug;

            $child = ShopCategory::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'parent_id' => $parent->id,
                    'status' => true,
                    'sort_order' => $index,
                ]
            );

            $titles = [];

            foreach ($languages as $language) {
                $titles[$language->code] = $childrenByLanguage[$language->code][$index] ?? $baseTitle ?? '';
            }

            $this->syncCategoryTranslations($child, $titles, $languages);
        }

        ShopCategory::query()
            ->where('parent_id', $parent->id)
            ->whereNotIn('slug', $activeChildSlugs)
            ->update(['status' => false]);
    }

    private function syncCategoryTranslations(ShopCategory $category, array $titlesByLanguage, $languages): void
    {
        foreach ($languages as $language) {
            $title = trim((string)($titlesByLanguage[$language->code] ?? ''));

            if ($title === '') {
                continue;
            }

            $category->translations()->updateOrCreate(
                ['language_id' => $language->id],
                ['title' => $title]
            );
        }
    }

    private function mapTranslationsByLanguage(ShopCategory $category, $languageCodes): array
    {
        return $languageCodes
            ->mapWithKeys(fn (string $code) => [$code => $this->categoryTitleForLanguage($category, $code)])
            ->all();
    }

    private function mapCategoryMetaByLanguage(ShopCategory $category, $languageCodes): array
    {
        return $languageCodes
            ->mapWithKeys(fn (string $code) => [$code => $this->categoryMetaForLanguage($category, $code)])
            ->all();
    }

    private function categoryTitleForLanguage(ShopCategory $category, string $code): string
    {
        $translation = $category->translations->first(fn ($translation) => $translation->language?->code === $code);

        if ($translation) {
            return $translation->title;
        }

        return $category->translations->first()?->title ?? '';
    }

    private function categoryMetaForLanguage(ShopCategory $category, string $code): array
    {
        $translation = $category->translations->first(fn ($translation) => $translation->language?->code === $code)
            ?: $category->translations->first();
        $title = $translation?->title ?? '';

        return [
            'title' => $title,
            'metaTitle' => $translation?->meta_title ?: $title,
            'metaDescription' => $translation?->meta_description ?? '',
            'metaKeywords' => $translation?->meta_keywords ?? '',
        ];
    }

    private function normalizeSlug(?string $value, string $fallback): string
    {
        $slug = Str::slug((string)$value);

        if ($slug === '') {
            $slug = Str::slug($fallback);
        }

        return $slug ?: 'category-' . uniqid();
    }

    private function readProductsFromDatabase(array $languages): array
    {
        if (!Schema::hasTable('shop_products') || !Schema::hasTable('shop_product_translations')) {
            return [];
        }

        $languageCodes = collect($languages)->pluck('code')->filter()->values();

        if ($languageCodes->isEmpty()) {
            return [];
        }

        $products = ShopProduct::query()
            ->where('status', true)
            ->with([
                'translations.language:id,code',
                'category.parent',
                'media',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($products->isEmpty()) {
            return [];
        }

        return $products->map(function (ShopProduct $product) use ($languageCodes) {
            $category = $product->category;
            $parentCategory = $category?->parent ?: $category;

            return [
                'id' => $product->id,
                'slug' => $product->slug,
                'title' => $this->mapProductTranslationsByLanguage($product, $languageCodes),
                'meta' => $this->mapProductMetaByLanguage($product, $languageCodes),
                'price' => $this->formatPrice($product->price),
                'kind' => $product->kind ?? '',
                'image' => $product->media?->original_path ?: ($product->image ?? ''),
                'gallery' => $product->gallery ?: array_values(array_filter([$product->image])),
                'options' => $product->options ?: [],
                'isNew' => $product->is_new,
                'status' => $product->status,
                'categoryKey' => $parentCategory?->slug,
                'categoryChildIndex' => $category && $category->parent_id ? $category->sort_order : null,
            ];
        })->values()->all();
    }

    private function syncProductsToDatabase(array $products): void
    {
        if (!Schema::hasTable('languages') || !Schema::hasTable('shop_products') || !Schema::hasTable('shop_product_translations')) {
            return;
        }

        $languages = Language::query()->where('status', true)->get(['id', 'code'])->keyBy('code');

        if ($languages->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($products, $languages) {
            $activeProductIds = [];

            foreach (array_values($products) as $productIndex => $payload) {
                $id = (int)($payload['id'] ?? 0);

                if ($id <= 0) {
                    $id = ((int)ShopProduct::query()->max('id')) + 1;
                }

                $activeProductIds[] = $id;
                $titles = $payload['title'] ?? [];
                $baseTitle = collect($titles)->first(fn ($title) => trim((string)$title) !== '') ?: 'product-' . $id;
                $categoryId = $this->resolveProductCategoryId($payload);

                $product = ShopProduct::query()->updateOrCreate(
                    ['id' => $id],
                    [
                        'shop_category_id' => $categoryId,
                        'slug' => $this->normalizeSlug($payload['slug'] ?? $baseTitle, 'product-' . $id),
                        'price' => is_numeric($payload['price'] ?? null) ? $payload['price'] : 0,
                        'kind' => $payload['kind'] ?? null,
                        'image' => $payload['image'] ?? null,
                        'gallery' => $payload['gallery'] ?? array_values(array_filter([$payload['image'] ?? null])),
                        'options' => $payload['options'] ?? [],
                        'is_new' => (bool)($payload['isNew'] ?? false),
                        'status' => array_key_exists('status', $payload) ? (bool)$payload['status'] : true,
                        'sort_order' => $productIndex,
                    ]
                );

                $this->syncProductTranslations($product, $titles, $languages);
            }

            ShopProduct::query()
                ->whereNotIn('id', $activeProductIds)
                ->update(['status' => false]);
        });
    }

    private function resolveProductCategoryId(array $payload): ?int
    {
        if (!Schema::hasTable('shop_categories')) {
            return null;
        }

        $categoryKey = $payload['categoryKey'] ?? null;

        if (!$categoryKey) {
            return ShopCategory::query()
                ->where('status', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->value('id');
        }

        $parent = ShopCategory::query()
            ->whereNull('parent_id')
            ->where('slug', $categoryKey)
            ->where('status', true)
            ->first();

        if (!$parent) {
            return null;
        }

        if (isset($payload['categoryChildIndex']) && $payload['categoryChildIndex'] !== null && $payload['categoryChildIndex'] !== '') {
            $child = ShopCategory::query()
                ->where('parent_id', $parent->id)
                ->where('status', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->values()
                ->get((int)$payload['categoryChildIndex']);

            return $child?->id ?: $parent->id;
        }

        return $parent->id;
    }

    private function syncProductTranslations(ShopProduct $product, array $titlesByLanguage, $languages): void
    {
        foreach ($languages as $language) {
            $title = trim((string)($titlesByLanguage[$language->code] ?? ''));

            if ($title === '') {
                continue;
            }

            $product->translations()->updateOrCreate(
                ['language_id' => $language->id],
                ['title' => $title]
            );
        }
    }

    private function mapProductTranslationsByLanguage(ShopProduct $product, $languageCodes): array
    {
        return $languageCodes
            ->mapWithKeys(fn (string $code) => [$code => $this->productTitleForLanguage($product, $code)])
            ->all();
    }

    private function mapProductMetaByLanguage(ShopProduct $product, $languageCodes): array
    {
        return $languageCodes
            ->mapWithKeys(fn (string $code) => [$code => $this->productMetaForLanguage($product, $code)])
            ->all();
    }

    private function productTitleForLanguage(ShopProduct $product, string $code): string
    {
        $translation = $product->translations->first(fn ($translation) => $translation->language?->code === $code);

        if ($translation) {
            return $translation->title;
        }

        return $product->translations->first()?->title ?? '';
    }

    private function productMetaForLanguage(ShopProduct $product, string $code): array
    {
        $translation = $product->translations->first(fn ($translation) => $translation->language?->code === $code)
            ?: $product->translations->first();
        $title = $translation?->title ?? '';

        return [
            'title' => $translation?->meta_title ?: $title,
            'metaTitle' => $translation?->meta_title ?: $title,
            'metaDescription' => $translation?->meta_description ?? '',
            'metaKeywords' => $translation?->meta_keywords ?? '',
        ];
    }

    private function formatPrice($price): string
    {
        return rtrim(rtrim(number_format((float)$price, 2, '.', ''), '0'), '.');
    }

    private function readSocialLinksFromDatabase(): array
    {
        if (!Schema::hasTable('socials')) {
            return [];
        }

        return Social::query()
            ->where('status', true)
            ->where('show_in_frontend', true)
            ->whereNotNull('url')
            ->where('url', '!=', '')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['name', 'url'])
            ->map(fn (Social $social) => [
                'label' => $social->name,
                'href' => $social->url,
                'external' => true,
            ])
            ->values()
            ->all();
    }

    private function syncSocialLinksToDatabase(array $socialLinks): void
    {
        if (!Schema::hasTable('socials')) {
            return;
        }

        DB::transaction(function () use ($socialLinks) {
            $activeIcons = [];

            foreach (array_values($socialLinks) as $index => $link) {
                $label = trim((string)($link['label'] ?? ''));
                $href = trim((string)($link['href'] ?? ''));

                if ($label === '' || $href === '') {
                    continue;
                }

                $icon = Str::slug($label) ?: 'social-' . ($index + 1);
                $activeIcons[] = $icon;

                $social = Social::query()->firstOrNew(['icon' => $icon]);
                $social->fill([
                    'name' => $label,
                    'url' => $href,
                    'status' => true,
                    'show_in_frontend' => true,
                    'sort_order' => $index,
                ]);

                if (!$social->color) {
                    $social->color = $this->socialColorForIcon($icon);
                }

                $social->save();
            }

            Social::query()
                ->where('show_in_frontend', true)
                ->when(!empty($activeIcons), fn ($query) => $query->whereNotIn('icon', $activeIcons))
                ->update(['show_in_frontend' => false]);
        });
    }

    private function socialColorForIcon(string $icon): string
    {
        return match ($icon) {
            'facebook' => '#1877F2',
            'instagram' => '#E1306C',
            'youtube' => '#FF0000',
            'pinterest' => '#E60023',
            'tiktok' => '#69C9D0',
            default => '#111827',
        };
    }

    private function readSeoFromDatabase(array $languages): array
    {
        if (!Schema::hasTable('shop_seo_pages')) {
            return [];
        }

        $languageCodes = collect($languages)->pluck('code')->filter()->values();

        if ($languageCodes->isEmpty()) {
            return [];
        }

        $rows = ShopSeoPage::query()
            ->where('status', true)
            ->with('language:id,code')
            ->orderBy('page_key')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        return $rows
            ->groupBy('page_key')
            ->map(function ($items) {
                return $items
                    ->filter(fn (ShopSeoPage $row) => $row->language)
                    ->mapWithKeys(fn (ShopSeoPage $row) => [
                        $row->language->code => [
                            'title' => $row->title ?? '',
                            'metaTitle' => $row->meta_title ?? '',
                            'metaDescription' => $row->meta_description ?? '',
                            'metaKeywords' => $row->meta_keywords ?? '',
                        ],
                    ])
                    ->all();
            })
            ->all();
    }

    private function syncSeoToDatabase(array $seo): void
    {
        if (!Schema::hasTable('languages') || !Schema::hasTable('shop_seo_pages')) {
            return;
        }

        $languages = Language::query()->where('status', true)->get(['id', 'code'])->keyBy('code');

        if ($languages->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($seo, $languages) {
            foreach ($seo as $pageKey => $translations) {
                $pageKey = trim((string)$pageKey);

                if ($pageKey === '' || !is_array($translations)) {
                    continue;
                }

                foreach ($languages as $language) {
                    $payload = $translations[$language->code] ?? [];

                    if (!is_array($payload)) {
                        continue;
                    }

                    ShopSeoPage::query()->updateOrCreate(
                        [
                            'page_key' => $pageKey,
                            'language_id' => $language->id,
                        ],
                        [
                            'title' => $payload['title'] ?? null,
                            'meta_title' => $payload['metaTitle'] ?? null,
                            'meta_description' => $payload['metaDescription'] ?? null,
                            'meta_keywords' => $payload['metaKeywords'] ?? null,
                            'status' => true,
                        ]
                    );
                }
            }
        });
    }

    private function readPrivacyFromDatabase(): array
    {
        if (
            !Schema::hasTable('shop_privacy_policies')
            || !Schema::hasTable('shop_privacy_policy_translations')
            || !Schema::hasTable('shop_privacy_policy_checklist_items')
            || !Schema::hasTable('shop_privacy_policy_checklist_item_translations')
            || !Schema::hasTable('shop_privacy_policy_sections')
            || !Schema::hasTable('shop_privacy_policy_section_translations')
        ) {
            return [];
        }

        $policy = ShopPrivacyPolicy::query()
            ->where('status', true)
            ->with([
                'translations.language:id,code',
                'checklistItems.translations.language:id,code',
                'sections.translations.language:id,code',
            ])
            ->first();

        if (!$policy) {
            return [];
        }

        $content = [];

        foreach ($policy->translations as $translation) {
            if (!$translation->language) {
                continue;
            }

            $code = $translation->language->code;
            $content[$code] = [
                'kicker' => $translation->kicker ?? '',
                'title' => $translation->title ?? '',
                'intro' => $translation->intro ?? '',
                'badgeTitle' => $translation->badge_title ?? '',
                'badgeText' => $translation->badge_text ?? '',
                'summaryLabel' => $translation->summary_label ?? '',
                'summaryTitle' => $translation->summary_title ?? '',
                'summaryText' => $translation->summary_text ?? '',
                'checklist' => $policy->checklistItems
                    ->map(fn ($item) => $item->translations->first(fn ($itemTranslation) => $itemTranslation->language?->code === $code)?->text ?? '')
                    ->values()
                    ->all(),
                'updated' => $translation->updated_label ?? '',
                'summaryAria' => $translation->summary_aria ?? '',
                'checklistAria' => $translation->checklist_aria ?? '',
                'sections' => $policy->sections
                    ->map(function ($section) use ($code) {
                        $sectionTranslation = $section->translations->first(fn ($translation) => $translation->language?->code === $code);

                        return [
                            'index' => $section->section_index ?? '',
                            'icon' => $section->icon ?? '',
                            'title' => $sectionTranslation?->title ?? '',
                            'text' => $sectionTranslation?->text ?? '',
                        ];
                    })
                    ->values()
                    ->all(),
            ];
        }

        return [
            'updatedAt' => $policy->updated_at_label,
            'content' => $content,
        ];
    }

    private function syncPrivacyToDatabase(array $privacy): void
    {
        if (
            !Schema::hasTable('languages')
            || !Schema::hasTable('shop_privacy_policies')
            || !Schema::hasTable('shop_privacy_policy_translations')
            || !Schema::hasTable('shop_privacy_policy_checklist_items')
            || !Schema::hasTable('shop_privacy_policy_checklist_item_translations')
            || !Schema::hasTable('shop_privacy_policy_sections')
            || !Schema::hasTable('shop_privacy_policy_section_translations')
        ) {
            return;
        }

        $languages = Language::query()->where('status', true)->get(['id', 'code'])->keyBy('code');
        $content = $privacy['content'] ?? [];

        if ($languages->isEmpty() || !is_array($content)) {
            return;
        }

        DB::transaction(function () use ($privacy, $content, $languages) {
            $policy = ShopPrivacyPolicy::query()->updateOrCreate(
                ['slug' => 'privacy-policy'],
                [
                    'updated_at_label' => $privacy['updatedAt'] ?? null,
                    'status' => true,
                ]
            );

            foreach ($languages as $language) {
                if (!isset($content[$language->code]) || !is_array($content[$language->code])) {
                    continue;
                }

                $payload = $content[$language->code];

                $policy->translations()->updateOrCreate(
                    ['language_id' => $language->id],
                    [
                        'kicker' => $payload['kicker'] ?? null,
                        'title' => $payload['title'] ?? null,
                        'intro' => $payload['intro'] ?? null,
                        'badge_title' => $payload['badgeTitle'] ?? null,
                        'badge_text' => $payload['badgeText'] ?? null,
                        'summary_label' => $payload['summaryLabel'] ?? null,
                        'summary_title' => $payload['summaryTitle'] ?? null,
                        'summary_text' => $payload['summaryText'] ?? null,
                        'updated_label' => $payload['updated'] ?? null,
                        'summary_aria' => $payload['summaryAria'] ?? null,
                        'checklist_aria' => $payload['checklistAria'] ?? null,
                    ]
                );
            }

            $this->syncPrivacyChecklist($policy, $content, $languages);
            $this->syncPrivacySections($policy, $content, $languages);
        });
    }

    private function syncPrivacyChecklist(ShopPrivacyPolicy $policy, array $content, $languages): void
    {
        $maxItems = collect($content)
            ->filter(fn ($item) => is_array($item) && isset($item['checklist']) && is_array($item['checklist']))
            ->map(fn ($item) => count($item['checklist']))
            ->max() ?? 0;

        $activeIds = [];

        for ($index = 0; $index < $maxItems; $index++) {
            $item = ShopPrivacyPolicyChecklistItem::query()->firstOrCreate(
                [
                    'shop_privacy_policy_id' => $policy->id,
                    'sort_order' => $index,
                ],
                ['sort_order' => $index]
            );
            $activeIds[] = $item->id;

            foreach ($languages as $language) {
                $item->translations()->updateOrCreate(
                    ['language_id' => $language->id],
                    ['text' => $content[$language->code]['checklist'][$index] ?? null]
                );
            }
        }

        ShopPrivacyPolicyChecklistItem::query()
            ->where('shop_privacy_policy_id', $policy->id)
            ->when(!empty($activeIds), fn ($query) => $query->whereNotIn('id', $activeIds))
            ->delete();
    }

    private function syncPrivacySections(ShopPrivacyPolicy $policy, array $content, $languages): void
    {
        $maxSections = collect($content)
            ->filter(fn ($item) => is_array($item) && isset($item['sections']) && is_array($item['sections']))
            ->map(fn ($item) => count($item['sections']))
            ->max() ?? 0;

        $activeIds = [];

        for ($index = 0; $index < $maxSections; $index++) {
            $baseSection = collect($content)
                ->first(fn ($item) => is_array($item) && isset($item['sections'][$index]) && is_array($item['sections'][$index]));
            $baseSection = is_array($baseSection) ? ($baseSection['sections'][$index] ?? []) : [];
            $section = ShopPrivacyPolicySection::query()->firstOrCreate(
                [
                    'shop_privacy_policy_id' => $policy->id,
                    'sort_order' => $index,
                ],
                ['sort_order' => $index]
            );

            $section->fill([
                'section_index' => $baseSection['index'] ?? str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT),
                'icon' => $baseSection['icon'] ?? null,
                'is_wide' => ($baseSection['index'] ?? null) === '05',
                'sort_order' => $index,
            ])->save();

            $activeIds[] = $section->id;

            foreach ($languages as $language) {
                $sectionPayload = $content[$language->code]['sections'][$index] ?? [];
                $section->translations()->updateOrCreate(
                    ['language_id' => $language->id],
                    [
                        'title' => $sectionPayload['title'] ?? null,
                        'text' => $sectionPayload['text'] ?? null,
                    ]
                );
            }
        }

        ShopPrivacyPolicySection::query()
            ->where('shop_privacy_policy_id', $policy->id)
            ->when(!empty($activeIds), fn ($query) => $query->whereNotIn('id', $activeIds))
            ->delete();
    }
}
