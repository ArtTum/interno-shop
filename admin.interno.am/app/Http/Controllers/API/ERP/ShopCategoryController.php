<?php

namespace App\Http\Controllers\API\ERP;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\ShopCategory;
use App\Models\ShopCategoryTranslation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ShopCategoryController extends Controller
{
    public function fetch(Request $request): JsonResponse
    {
        $languageId = $this->resolveLanguageId((int) $request->query('language_id', 0));
        $search = trim((string) $request->query('search', ''));
        $parentId = $request->query('parent_id', '');
        $status = (int) $request->query('status', -1);
        $orderingField = (string) $request->query('ordering_field', 'sort_order');
        $orderingDirection = $request->query('ordering_direction') === 'desc' ? 'desc' : 'asc';
        $orderingColumns = [
            'id' => 'shop_categories.id',
            'shop_categories.id' => 'shop_categories.id',
            'sort_order' => 'shop_categories.sort_order',
            'shop_categories.sort_order' => 'shop_categories.sort_order',
            'slug' => 'shop_categories.slug',
            'shop_categories.slug' => 'shop_categories.slug',
            'status' => 'shop_categories.status',
            'shop_categories.status' => 'shop_categories.status',
        ];
        $orderingColumn = $orderingColumns[$orderingField] ?? 'shop_categories.sort_order';

        $query = ShopCategory::query()
            ->with([
                'translations' => fn ($query) => $query->where('language_id', $languageId),
                'parent.translations' => fn ($query) => $query->where('language_id', $languageId),
            ])
            ->withCount(['children', 'products'])
            ->when($parentId !== '', fn ($query) => $parentId === 'root'
                ? $query->whereNull('parent_id')
                : $query->where('parent_id', (int) $parentId))
            ->when($status >= 0, fn ($query) => $query->where('status', (bool) $status))
            ->when($search !== '', function ($query) use ($search, $languageId) {
                $query->where(function ($query) use ($search, $languageId) {
                    $query->where('slug', 'like', "%{$search}%")
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
            ->orderBy('parent_id')
            ->orderBy('id');

        $perPage = max(1, (int) $request->query('per_page', 25));
        $page = max(1, (int) $request->query('page', 1));
        $total = (clone $query)->count();
        $items = $query->forPage($page, $perPage)->get()->map(fn (ShopCategory $category) => $this->formatCategory($category));

        return response()->json([
            'success' => true,
            'data' => $items,
            'languages' => $this->languageOptions(),
            'parents' => $this->parentOptions($languageId),
            'base_language_id' => $languageId,
            'pagination' => prepare_pagination($page, $perPage, $total),
        ]);
    }

    public function fetchParams(Request $request): JsonResponse
    {
        $languageId = $this->resolveLanguageId((int) $request->query('language_id', 0));
        $excludeId = (int) $request->query('exclude_id', 0);

        return response()->json([
            'success' => true,
            'languages' => $this->languageOptions(),
            'parents' => $this->parentOptions($languageId, $excludeId),
            'base_language_id' => $languageId,
        ]);
    }

    public function fetchByField(Request $request): JsonResponse
    {
        $category = ShopCategory::query()
            ->with(['translations', 'parent'])
            ->findOrFail((int) $request->query('id'));
        $languageId = $this->resolveLanguageId((int) $request->query('language_id', 0));
        $translation = $category->translations->firstWhere('language_id', $languageId);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'parent_id' => $category->parent_id,
                'global_slug' => $category->slug,
                'status' => $category->status,
                'sort_order' => $category->sort_order,
                'language_id' => $languageId,
                'name' => $translation?->title ?? '',
                'slug' => $translation?->slug ?? '',
                'meta_title' => $translation?->meta_title ?? '',
                'meta_description' => $translation?->meta_description ?? '',
                'meta_keywords' => $translation?->meta_keywords ?? '',
            ],
            'languages' => $this->languageOptions(),
            'parents' => $this->parentOptions($languageId, $category->id),
            'base_language_id' => $languageId,
        ]);
    }

    public function insert(Request $request): JsonResponse
    {
        $data = $this->validatedPayload($request);

        $category = DB::transaction(function () use ($data) {
            $category = ShopCategory::query()->create([
                'parent_id' => $data['parent_id'] ?? null,
                'slug' => $this->uniqueGlobalSlug($data['global_slug'] ?: $data['slug'] ?: $data['name']),
                'status' => $data['status'],
                'sort_order' => $data['sort_order'],
            ]);

            $this->saveTranslation($category, $data);

            return $category;
        });

        return response()->json([
            'success' => true,
            'message' => 'Successfully created!',
            'data' => ['id' => $category->id],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $category = ShopCategory::query()->findOrFail((int) $request->input('id'));
        $data = $this->validatedPayload($request, $category->id);

        DB::transaction(function () use ($category, $data) {
            $category->update([
                'parent_id' => $data['parent_id'] ?? null,
                'slug' => $this->uniqueGlobalSlug($data['global_slug'] ?: $data['slug'] ?: $data['name'], $category->id),
                'status' => $data['status'],
                'sort_order' => $data['sort_order'],
            ]);

            $this->saveTranslation($category, $data);
        });

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated!',
        ]);
    }

    public function delete(int $id): JsonResponse
    {
        ShopCategory::query()->findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully deleted!',
        ]);
    }

    private function validatedPayload(Request $request, ?int $categoryId = null): array
    {
        return $request->validate([
            'id' => ['nullable', 'integer'],
            'language_id' => ['required', 'integer', Rule::exists('languages', 'id')],
            'parent_id' => ['nullable', 'integer', Rule::exists('shop_categories', 'id')],
            'global_slug' => ['nullable', 'string', 'max:160'],
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:160'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
    }

    private function saveTranslation(ShopCategory $category, array $data): void
    {
        $category->translations()->updateOrCreate(
            ['language_id' => $data['language_id']],
            [
                'title' => $data['name'],
                'slug' => $this->normalizeSlug($data['slug'] ?: $data['name'], $category->slug),
                'meta_title' => $data['meta_title'] ?: $data['name'],
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
            ]
        );
    }

    private function formatCategory(ShopCategory $category): array
    {
        $translation = $category->translations->first();
        $parentTranslation = $category->parent?->translations->first();

        return [
            'id' => $category->id,
            'parent_id' => $category->parent_id,
            'parent_name' => $parentTranslation?->title,
            'global_slug' => $category->slug,
            'name' => $translation?->title ?? '',
            'slug' => $translation?->slug ?? '',
            'meta_title' => $translation?->meta_title ?? '',
            'status' => $category->status,
            'sort_order' => $category->sort_order,
            'children_count' => $category->children_count,
            'products_count' => $category->products_count,
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

    private function parentOptions(int $languageId, int $excludeId = 0): array
    {
        $categories = ShopCategory::query()
            ->where('status', true)
            ->when($excludeId > 0, fn ($query) => $query->where('id', '!=', $excludeId))
            ->with(['translations' => fn ($query) => $query->where('language_id', $languageId)])
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return collect([['value' => null, 'label' => 'No parent']])
            ->merge($categories->map(fn (ShopCategory $category) => [
                'value' => $category->id,
                'label' => ($category->parent_id ? '- ' : '') . ($category->translations->first()?->title ?: $category->slug),
            ]))
            ->values()
            ->all();
    }

    private function normalizeSlug(?string $value, string $fallback): string
    {
        $slug = Str::slug((string) $value);

        if ($slug === '') {
            $slug = Str::slug($fallback);
        }

        return $slug ?: 'category-' . uniqid();
    }

    private function uniqueGlobalSlug(?string $value, ?int $ignoreId = null): string
    {
        $base = $this->normalizeSlug($value, 'category');
        $slug = $base;
        $index = 2;

        while (
            ShopCategory::query()
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
