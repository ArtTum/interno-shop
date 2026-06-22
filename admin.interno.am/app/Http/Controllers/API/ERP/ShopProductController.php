<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Media;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use App\Models\ShopProductAttributePrice;
use App\Models\ShopProductAttributeValue;
use App\Models\ShopProductColor;
use App\Models\ShopProductOptionType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ShopProductController extends Controller
{
    public function fetch(Request $request): JsonResponse
    {
        $languageId = $this->resolveLanguageId((int) $request->query('language_id', 0));
        $search = trim((string) $request->query('search', ''));
        $categoryId = (int) $request->query('category_id', -1);
        $status = (int) $request->query('status', -1);
        $availability = (int) $request->query('availability', -1);
        $isNew = (int) $request->query('is_new', -1);
        $kind = trim((string) $request->query('kind', ''));
        $optionTypeId = (int) $request->query('option_type_id', -1);
        $optionColorId = (int) $request->query('option_color_id', -1);
        $orderingField = (string) $request->query('ordering_field', 'sort_order');
        $orderingDirection = $request->query('ordering_direction') === 'desc' ? 'desc' : 'asc';
        $orderingColumns = [
            'id' => 'shop_products.id',
            'shop_products.id' => 'shop_products.id',
            'sort_order' => 'shop_products.sort_order',
            'shop_products.sort_order' => 'shop_products.sort_order',
            'price' => 'shop_products.price',
            'shop_products.price' => 'shop_products.price',
            'status' => 'shop_products.status',
            'shop_products.status' => 'shop_products.status',
            'availability' => 'shop_products.is_temporarily_unavailable',
            'shop_products.is_temporarily_unavailable' => 'shop_products.is_temporarily_unavailable',
            'slug' => 'shop_products.slug',
            'shop_products.slug' => 'shop_products.slug',
        ];
        $orderingColumn = $orderingColumns[$orderingField] ?? 'shop_products.sort_order';

        $query = ShopProduct::query()
            ->with([
                'translations' => fn ($query) => $query->where('language_id', $languageId),
                'category.translations' => fn ($query) => $query->where('language_id', $languageId),
                'media',
                'optionType',
                'optionColor',
                'attributePrices.attributeValue',
            ])
            ->when($categoryId > 0, fn ($query) => $query->where('shop_category_id', $categoryId))
            ->when($status >= 0, fn ($query) => $query->where('status', (bool) $status))
            ->when($availability >= 0, fn ($query) => $query->where('is_temporarily_unavailable', (bool) $availability))
            ->when($isNew >= 0, fn ($query) => $query->where('is_new', (bool) $isNew))
            ->when($kind !== '', fn ($query) => $query->where('kind', $kind))
            ->when($optionTypeId > 0, fn ($query) => $query->where('option_type_id', $optionTypeId))
            ->when($optionColorId > 0, fn ($query) => $query->where(function ($query) use ($optionColorId) {
                $query->where('option_color_id', $optionColorId)
                    ->orWhereJsonContains('option_color_ids', $optionColorId);
            }))
            ->when($search !== '', function ($query) use ($search, $languageId) {
                $query->where(function ($query) use ($search, $languageId) {
                    $query->where('shop_products.id', (int) $search)
                        ->orWhere('shop_products.slug', 'like', "%{$search}%")
                        ->orWhere('shop_products.kind', 'like', "%{$search}%")
                        ->orWhereHas('translations', function ($query) use ($search, $languageId) {
                            $query->where('language_id', $languageId)
                                ->where(function ($query) use ($search) {
                                    $query->where('title', 'like', "%{$search}%")
                                        ->orWhere('slug', 'like', "%{$search}%")
                                        ->orWhere('meta_title', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->orderBy($orderingColumn, $orderingDirection)
            ->orderBy('shop_products.id');

        $perPage = max(1, (int) $request->query('per_page', 25));
        $page = max(1, (int) $request->query('page', 1));
        $total = (clone $query)->count();
        $items = $query->forPage($page, $perPage)->get()->map(fn (ShopProduct $product) => $this->formatProduct($product));

        return response()->json([
            'success' => true,
            'data' => $items,
            'languages' => $this->languageOptions(),
            'categories' => $this->categoryOptions($languageId),
            'kinds' => $this->kindOptions(),
            'optionTypes' => $this->optionTypeOptions(),
            'optionColors' => $this->optionColorOptions(),
            'attributeValues' => $this->attributeValueOptions(),
            'base_language_id' => $languageId,
            'pagination' => prepare_pagination($page, $perPage, $total),
        ]);
    }

    public function fetchParams(Request $request): JsonResponse
    {
        $languageId = $this->resolveLanguageId((int) $request->query('language_id', 0));

        return response()->json([
            'success' => true,
            'languages' => $this->languageOptions(),
            'categories' => $this->categoryOptions($languageId),
            'kinds' => $this->kindOptions(),
            'optionTypes' => $this->optionTypeOptions(),
            'optionColors' => $this->optionColorOptions(),
            'attributeValues' => $this->attributeValueOptions(),
            'base_language_id' => $languageId,
        ]);
    }

    public function fetchByField(Request $request): JsonResponse
    {
        $product = ShopProduct::query()
            ->with(['translations', 'category', 'media', 'optionType', 'optionColor', 'attributePrices.attributeValue'])
            ->findOrFail((int) $request->query('id'));
        $languageId = $this->resolveLanguageId((int) $request->query('language_id', 0));
        $translation = $product->translations->firstWhere('language_id', $languageId);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'shop_category_id' => $product->shop_category_id,
                'global_slug' => $product->slug,
                'price' => $product->price,
                'kind' => $product->kind ?? '',
                'media_id' => $product->media_id,
                'media' => $product->media ? [$this->formatMedia($product->media)] : [],
                'image' => $product->image ?? '',
                'gallery_media_ids' => $product->gallery_media_ids ?: [],
                'gallery' => $this->formatMediaList($product->gallery_media_ids ?: []),
                'gallery_text' => implode(PHP_EOL, $product->gallery ?: []),
                'options_text' => json_encode($product->options ?: new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'attribute_prices' => $this->formatAttributePrices($product),
                'option_code' => $product->option_code ?? '',
                'option_size' => $product->option_size ?? '',
                'option_quantity' => $product->option_quantity ?? '',
                'option_type_id' => $product->option_type_id,
                'option_unit' => $product->option_unit ?? '',
                'option_piece' => $product->option_piece ?? '',
                'option_height' => $product->option_height ?? '',
                'option_material' => $product->option_material ?? '',
                'option_color_id' => $product->option_color_id,
                'option_color_ids' => $this->normalizeColorIds($product->option_color_ids ?: [], $product->option_color_id),
                'is_new' => $product->is_new,
                'is_temporarily_unavailable' => $product->is_temporarily_unavailable,
                'status' => $product->status,
                'sort_order' => $product->sort_order,
                'language_id' => $languageId,
                'title' => $translation?->title ?? '',
                'slug' => $translation?->slug ?? '',
                'short_description' => $translation?->short_description ?? '',
                'description' => $translation?->description ?? '',
                'meta_title' => $translation?->meta_title ?? '',
                'meta_description' => $translation?->meta_description ?? '',
                'meta_keywords' => $translation?->meta_keywords ?? '',
            ],
            'languages' => $this->languageOptions(),
            'categories' => $this->categoryOptions($languageId),
            'kinds' => $this->kindOptions(),
            'optionTypes' => $this->optionTypeOptions(),
            'optionColors' => $this->optionColorOptions(),
            'attributeValues' => $this->attributeValueOptions(),
            'base_language_id' => $languageId,
        ]);
    }

    public function insert(Request $request): JsonResponse
    {
        $data = $this->validatedPayload($request);

        $product = DB::transaction(function () use ($data) {
            $product = ShopProduct::query()->create([
                'shop_category_id' => $data['shop_category_id'] ?? null,
                'slug' => $this->uniqueGlobalSlug(($data['global_slug'] ?? '') ?: ($data['slug'] ?? '') ?: $data['title']),
                'price' => $this->basePrice($data),
                'kind' => $data['kind'] ?: null,
                'media_id' => $data['media_id'] ?? null,
                'image' => $this->mediaPathById($data['media_id'] ?? null) ?: (($data['image'] ?? '') ?: null),
                'gallery' => $this->galleryPaths($data),
                'gallery_media_ids' => $data['gallery_media_ids'] ?? [],
                'options' => $this->buildOptions($data),
                'option_code' => $data['option_code'] ?? null,
                'option_size' => $data['option_size'] ?? null,
                'option_quantity' => $data['option_quantity'] ?? null,
                'option_type_id' => $data['option_type_id'] ?? null,
                'option_unit' => $data['option_unit'] ?? null,
                'option_piece' => $data['option_piece'] ?? null,
                'option_height' => $data['option_height'] ?? null,
                'option_material' => $data['option_material'] ?? null,
                'option_color_id' => $data['option_color_id'] ?? null,
                'option_color_ids' => $data['option_color_ids'],
                'is_new' => $data['is_new'],
                'is_temporarily_unavailable' => $data['is_temporarily_unavailable'],
                'status' => $data['status'],
                'sort_order' => $data['sort_order'],
            ]);

            $this->syncAttributePrices($product, $data);
            $this->saveTranslation($product, $data);

            return $product;
        });

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => ['id' => $product->id],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $product = ShopProduct::query()->findOrFail((int) $request->input('id'));
        $data = $this->validatedPayload($request, $product->id);

        DB::transaction(function () use ($product, $data) {
            $product->update([
                'shop_category_id' => $data['shop_category_id'] ?? null,
                'slug' => $this->uniqueGlobalSlug(($data['global_slug'] ?? '') ?: ($data['slug'] ?? '') ?: $data['title'], $product->id),
                'price' => $this->basePrice($data),
                'kind' => $data['kind'] ?: null,
                'media_id' => $data['media_id'] ?? null,
                'image' => $this->mediaPathById($data['media_id'] ?? null) ?: (($data['image'] ?? '') ?: null),
                'gallery' => $this->galleryPaths($data),
                'gallery_media_ids' => $data['gallery_media_ids'] ?? [],
                'options' => $this->buildOptions($data),
                'option_code' => $data['option_code'] ?? null,
                'option_size' => $data['option_size'] ?? null,
                'option_quantity' => $data['option_quantity'] ?? null,
                'option_type_id' => $data['option_type_id'] ?? null,
                'option_unit' => $data['option_unit'] ?? null,
                'option_piece' => $data['option_piece'] ?? null,
                'option_height' => $data['option_height'] ?? null,
                'option_material' => $data['option_material'] ?? null,
                'option_color_id' => $data['option_color_id'] ?? null,
                'option_color_ids' => $data['option_color_ids'],
                'is_new' => $data['is_new'],
                'is_temporarily_unavailable' => $data['is_temporarily_unavailable'],
                'status' => $data['status'],
                'sort_order' => $data['sort_order'],
            ]);

            $this->syncAttributePrices($product, $data);
            $this->saveTranslation($product, $data);
        });

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        ShopProduct::query()->findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }

    private function validatedPayload(Request $request, ?int $productId = null): array
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer'],
            'language_id' => ['required', 'integer', Rule::exists('languages', 'id')],
            'shop_category_id' => ['nullable', 'integer', Rule::exists('shop_categories', 'id')],
            'global_slug' => ['nullable', 'string', 'max:160'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'kind' => ['nullable', 'string', 'max:80'],
            'media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'image' => ['nullable', 'string', 'max:500'],
            'gallery_media_ids' => ['nullable', 'array'],
            'gallery_media_ids.*' => ['integer', Rule::exists('media', 'id')],
            'gallery_text' => ['nullable', 'string'],
            'attribute_prices' => ['nullable', 'array'],
            'attribute_prices.*' => ['nullable', 'array'],
            'attribute_prices.*.*.attribute_value_id' => ['required', 'integer', Rule::exists('shop_product_attribute_values', 'id')],
            'attribute_prices.*.*.price' => ['required', 'numeric', 'min:0'],
            'option_code' => ['nullable', 'string', 'max:120'],
            'option_size' => ['nullable', 'string', 'max:120'],
            'option_quantity' => ['nullable', 'string', 'max:120'],
            'option_type_id' => ['nullable', 'integer', Rule::exists('shop_product_option_types', 'id')],
            'option_unit' => ['nullable', 'string', 'max:120'],
            'option_piece' => ['nullable', 'string', 'max:120'],
            'option_height' => ['nullable', 'string', 'max:120'],
            'option_material' => ['nullable', 'string', 'max:120'],
            'option_color_id' => ['nullable', 'integer', Rule::exists('shop_product_colors', 'id')],
            'option_color_ids' => ['nullable', 'array'],
            'option_color_ids.*' => ['integer', Rule::exists('shop_product_colors', 'id')],
            'is_new' => ['required', 'boolean'],
            'is_temporarily_unavailable' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:160'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
        ]);

        $data['option_color_ids'] = $this->normalizeColorIds($data['option_color_ids'] ?? [], $data['option_color_id'] ?? null);
        $data['option_color_id'] = $data['option_color_id'] ?? ($data['option_color_ids'][0] ?? null);

        return $data;
    }

    private function saveTranslation(ShopProduct $product, array $data): void
    {
        $product->translations()->updateOrCreate(
            ['language_id' => $data['language_id']],
            [
                'title' => $data['title'],
                'slug' => $this->normalizeSlug($data['slug'] ?: $data['title'], $product->slug),
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
                'meta_title' => $data['meta_title'] ?: $data['title'],
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
            ]
        );
    }

    private function formatProduct(ShopProduct $product): array
    {
        $translation = $product->translations->first();
        $categoryTranslation = $product->category?->translations->first();
        $optionColors = $this->colorsByIds($this->normalizeColorIds($product->option_color_ids ?: [], $product->option_color_id));

        return [
            'id' => $product->id,
            'shop_category_id' => $product->shop_category_id,
            'category_name' => $categoryTranslation?->title ?? $product->category?->slug,
            'global_slug' => $product->slug,
            'title' => $translation?->title ?? '',
            'slug' => $translation?->slug ?? '',
            'meta_title' => $translation?->meta_title ?? '',
            'price' => $product->price,
            'attribute_prices' => $this->formatAttributePrices($product),
            'kind' => $product->kind ?? '',
            'option_type_name' => $product->optionType?->name,
            'option_color_id' => $product->option_color_id,
            'option_color_name' => $product->optionColor?->name,
            'option_color_value' => $product->optionColor?->value,
            'option_color_ids' => $optionColors->pluck('id')->values()->all(),
            'option_colors' => $optionColors->map(fn (ShopProductColor $color) => $this->formatColorOption($color))->values()->all(),
            'image' => $product->media?->original_path ?: ($product->image ?? ''),
            'media_id' => $product->media_id,
            'is_new' => $product->is_new,
            'is_temporarily_unavailable' => $product->is_temporarily_unavailable,
            'status' => $product->status,
            'sort_order' => $product->sort_order,
        ];
    }

    private function resolveLanguageId(int $languageId): int
    {
        if ($languageId > 0) {
            return $languageId;
        }

        return (int) Language::query()
            ->orderByDesc('base')
            ->orderBy('id')
            ->value('id');
    }

    private function languageOptions(): array
    {
        return Language::query()
            ->where('status', true)
            ->orderByDesc('base')
            ->orderBy('code')
            ->get(['id', 'code', 'name'])
            ->map(fn (Language $language) => [
                'value' => $language->id,
                'label' => "{$language->name} ({$language->code})",
                'code' => $language->code,
            ])
            ->values()
            ->all();
    }

    private function categoryOptions(int $languageId): array
    {
        $categories = ShopCategory::query()
            ->where('status', true)
            ->with(['translations' => fn ($query) => $query->where('language_id', $languageId)])
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return collect([['value' => null, 'label' => 'No category']])
            ->merge($categories->map(fn (ShopCategory $category) => [
                'value' => $category->id,
                'label' => ($category->parent_id ? '- ' : '') . ($category->translations->first()?->title ?: $category->slug),
            ]))
            ->values()
            ->all();
    }

    private function kindOptions(): array
    {
        $kinds = ShopProduct::query()
            ->whereNotNull('kind')
            ->where('kind', '!=', '')
            ->distinct()
            ->orderBy('kind')
            ->pluck('kind')
            ->map(fn (string $kind) => ['value' => $kind, 'label' => $kind])
            ->values()
            ->all();

        return collect([['value' => '', 'label' => 'All kinds']])
            ->merge($kinds)
            ->values()
            ->all();
    }

    private function optionTypeOptions(): array
    {
        return collect([['value' => null, 'label' => 'No type']])
            ->merge(ShopProductOptionType::query()
                ->where('status', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (ShopProductOptionType $type) => [
                    'value' => $type->id,
                    'label' => $type->name,
                ]))
            ->values()
            ->all();
    }

    private function optionColorOptions(): array
    {
        return collect([['value' => null, 'label' => 'No color']])
            ->merge(ShopProductColor::query()
                ->where('status', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'value'])
                ->map(fn (ShopProductColor $color) => [
                    'value' => $color->id,
                    'label' => $color->name,
                    'color' => $color->value,
                ]))
            ->values()
            ->all();
    }

    private function attributeValueOptions(): array
    {
        return ShopProductAttributeValue::query()
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'type', 'name', 'value'])
            ->groupBy('type')
            ->map(fn ($items) => $items
                ->map(fn (ShopProductAttributeValue $item) => [
                    'value' => $item->id,
                    'label' => $item->value ? "{$item->name} ({$item->value})" : $item->name,
                    'name' => $item->name,
                    'raw_value' => $item->value,
                ])
                ->values()
                ->all())
            ->all();
    }

    private function formatAttributePrices(ShopProduct $product): array
    {
        $groups = collect(ShopProductAttributeValue::TYPES)->mapWithKeys(fn (string $type) => [$type => []])->all();

        foreach ($product->attributePrices as $price) {
            $type = $price->attributeValue?->type;

            if (!$type) {
                continue;
            }

            $groups[$type][] = [
                'attribute_value_id' => $price->shop_product_attribute_value_id,
                'price' => $this->formatPrice($price->price),
            ];
        }

        return $groups;
    }

    private function syncAttributePrices(ShopProduct $product, array $data): void
    {
        $payload = $data['attribute_prices'] ?? [];
        $rows = [];
        $seenValueIds = [];

        foreach (ShopProductAttributeValue::TYPES as $type) {
            foreach (($payload[$type] ?? []) as $item) {
                $valueId = (int) ($item['attribute_value_id'] ?? 0);

                if ($valueId <= 0 || isset($seenValueIds[$valueId])) {
                    continue;
                }

                $attributeValue = ShopProductAttributeValue::query()
                    ->where('type', $type)
                    ->where('status', true)
                    ->find($valueId);

                if (!$attributeValue) {
                    continue;
                }

                $seenValueIds[$valueId] = true;
                $rows[] = [
                    'shop_product_id' => $product->id,
                    'shop_product_attribute_value_id' => $valueId,
                    'price' => $item['price'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        ShopProductAttributePrice::query()
            ->where('shop_product_id', $product->id)
            ->delete();

        if (!empty($rows)) {
            ShopProductAttributePrice::query()->insert($rows);
        }
    }

    private function basePrice(array $data): float
    {
        $prices = collect($data['attribute_prices'] ?? [])
            ->flatMap(fn ($items) => is_array($items) ? $items : [])
            ->pluck('price')
            ->filter(fn ($price) => is_numeric($price))
            ->map(fn ($price) => (float) $price)
            ->filter(fn (float $price) => $price >= 0);

        if ($prices->isNotEmpty()) {
            return (float) $prices->min();
        }

        return array_key_exists('attribute_prices', $data) ? 0 : (float) ($data['price'] ?? 0);
    }

    private function formatPrice($price): string
    {
        return rtrim(rtrim(number_format((float) $price, 2, '.', ''), '0'), '.');
    }

    private function parseGallery(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    private function galleryPaths(array $data): array
    {
        $mediaIds = $data['gallery_media_ids'] ?? [];

        if (!empty($mediaIds)) {
            return Media::query()
                ->whereIn('id', $mediaIds)
                ->get(['id', 'original_path'])
                ->sortBy(fn (Media $media) => array_search($media->id, $mediaIds))
                ->pluck('original_path')
                ->filter()
                ->values()
                ->all();
        }

        return $this->parseGallery($data['gallery_text'] ?? '');
    }

    private function mediaPathById(?int $mediaId): ?string
    {
        if (!$mediaId) {
            return null;
        }

        return Media::query()
            ->where('id', $mediaId)
            ->value('original_path');
    }

    private function formatMediaList(array $mediaIds): array
    {
        if (empty($mediaIds)) {
            return [];
        }

        return Media::query()
            ->whereIn('id', $mediaIds)
            ->get()
            ->sortBy(fn (Media $media) => array_search($media->id, $mediaIds))
            ->map(fn (Media $media) => $this->formatMedia($media))
            ->values()
            ->all();
    }

    private function formatMedia(Media $media): array
    {
        return [
            'id' => $media->id,
            'media_id' => $media->id,
            'path' => $media->original_path,
            'type' => $media->type,
            'file_type' => $media->file_type,
            'video_type' => '',
            'video_url' => '',
        ];
    }

    private function buildOptions(array $data): array
    {
        $type = !empty($data['option_type_id'])
            ? ShopProductOptionType::query()->find((int) $data['option_type_id'])
            : null;
        $color = !empty($data['option_color_id'])
            ? ShopProductColor::query()->find((int) $data['option_color_id'])
            : null;
        $colors = $this->colorsByIds($data['option_color_ids'] ?? []);

        return array_filter([
            'code' => $data['option_code'] ?? null,
            'size' => $data['option_size'] ?? null,
            'quantity' => $data['option_quantity'] ?? null,
            'type' => $type?->name,
            'type_id' => $type?->id,
            'unit' => $data['option_unit'] ?? null,
            'piece' => $data['option_piece'] ?? null,
            'height' => $data['option_height'] ?? null,
            'material' => $data['option_material'] ?? null,
            'color' => $color?->value ?: $color?->name,
            'color_id' => $color?->id,
            'colors' => $colors->map(fn (ShopProductColor $color) => $this->formatColorOption($color))->values()->all(),
            'color_ids' => $colors->pluck('id')->values()->all(),
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function normalizeColorIds($colorIds, ?int $mainColorId = null): array
    {
        $ids = collect(is_array($colorIds) ? $colorIds : [])
            ->map(fn ($colorId) => (int) $colorId)
            ->filter(fn (int $colorId) => $colorId > 0)
            ->unique()
            ->values()
            ->all();

        if ($mainColorId && !in_array($mainColorId, $ids, true)) {
            array_unshift($ids, $mainColorId);
        }

        return $ids;
    }

    private function colorsByIds(array $colorIds)
    {
        if (empty($colorIds)) {
            return collect();
        }

        return ShopProductColor::query()
            ->whereIn('id', $colorIds)
            ->get(['id', 'name', 'value'])
            ->sortBy(fn (ShopProductColor $color) => array_search($color->id, $colorIds, true))
            ->values();
    }

    private function formatColorOption(ShopProductColor $color): array
    {
        return [
            'id' => $color->id,
            'name' => $color->name,
            'value' => $color->value,
        ];
    }

    private function normalizeSlug(?string $value, string $fallback): string
    {
        $slug = Str::slug((string) $value);

        if ($slug === '') {
            $slug = Str::slug($fallback);
        }

        return $slug ?: 'product-' . uniqid();
    }

    private function uniqueGlobalSlug(?string $value, ?int $ignoreId = null): string
    {
        $base = $this->normalizeSlug($value, 'product');
        $slug = $base;
        $index = 2;

        while (
            ShopProduct::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$index}";
            $index++;
        }

        return $slug;
    }
}
