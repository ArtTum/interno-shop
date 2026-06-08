<?php

namespace App\Repositories\ProductTranslation;

use App\Models\ProductTranslation;
use App\Repositories\BaseRepository;
use App\Repositories\ProductTranslation\Interfaces\ProductTranslationRepositoryInterface;
use App\Services\General\CustomSlugService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductTranslationRepository extends BaseRepository implements ProductTranslationRepositoryInterface
{
    public function __construct(ProductTranslation $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        $data = CustomSlugService::setPathBySlugProduct($data, 'product');
        return parent::create($data);
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        $data = CustomSlugService::setPathBySlugProduct($data, 'product');
        return parent::update($whereField, $whereValue, $data);
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function fetchForMenuName(int $id)
    {
        return $this->model->select('name')
            ->where('id', $id)
            ->value('name');
    }

    public function fetchByProductAndLanguageId(int $productId, string $languageId, string $selectFields): Model|null
    {
        return $this->model->select(DB::raw($selectFields))
            ->where('product_id', $productId)
            ->where('language_id', $languageId)
            ->first();
    }

    public function autocomplete(string $field, ?string $searchTerm, int $languageId, array $alreadySelectIds): array
    {
        $limit = count($alreadySelectIds) + 10;

        return $this->model->select('id as value', 'name as label')
            ->where('language_id', $languageId)
            ->where(function ($query) use ($field, $searchTerm, $alreadySelectIds) {
                $query->when(!empty($searchTerm), function ($query) use ($field, $searchTerm) {
                    $searchTerm = addslashes($searchTerm);
                    $query->whereRaw("$field LIKE '%$searchTerm%'");
                })->when(!empty($alreadySelectIds), function ($query) use ($alreadySelectIds) {
                    $query->orWhereIn('id', $alreadySelectIds);
                });
            })
            ->limit($limit)
            ->offset(0)
            ->get()
            ->toArray();
    }

    public function getPathByProductIdAndLanguageId(int $productId, int $languageId)
    {
        return $this->model->select('id', 'path', 'slug')->where('product_id', $productId)->where('language_id', $languageId)->first();
    }

    public function getIdByProductIdAndLanguageId(int $productId, int $languageId): ?int
    {
        return $this->model->select('id')
            ->where('product_id', $productId)
            ->where('language_id', $languageId)
            ->value('id');
    }

    public function getTranslationByProductIdAndLanguageId(int $productId, int $languageId)
    {
        return $this->model->select('*')
            ->where('product_id', $productId)
            ->where('language_id', $languageId)
            ->first();
    }

    public function fetchForSEOAlternatives(int $productId)
    {
        return $this->model->select('path', 'hreflang', 'languages.code', 'default_hreflang')
            ->join('languages', 'languages.id', '=', 'product_translations.language_id')
            ->where('product_id', $productId)
            ->where('product_translations.translation_status', true)
            ->get();
    }

    public function getProductIdById(int $id)
    {
        return $this->model->select('product_id')
            ->where('id', $id)
            ->value('product_id');
    }

    public function getPathsForCacheClearing(int $productId, int $languageId)
    {
        return $this->model->select('path')
            ->join('languages', 'languages.id', '=', 'product_translations.language_id')
            ->where('product_id', $productId)
            ->when($languageId > -1, function ($query) use ($languageId) {
                $query->where('languages.id', $languageId);
            })
            ->get();
    }

    public function getProductTranslations()
    {
        return $this->model->select('id', 'slug', 'name', 'language_id', 'path')
            ->get()
            ->toArray();
    }

    public function getProductTranslationsForFixAPlus()
    {
        return $this->model->select('id', 'a_plus_content_id', 'language_id')
            ->whereNotNull('a_plus_content_id')
            ->with([
                'a_plus_content' => function ($query) {
                    $query->select('id', 'page_id', 'language_id');
                }
            ])
            ->get()
            ->toArray();
    }

    public function checkExistsStr(string $str, string $field)
    {
        return $this->model->select('id', 'name', 'sub_name', 'short_description', 'description', 'meta_title', 'meta_description', 'meta_keywords')
            ->where($field, 'LIKE', "%{$str}%")
            ->get()
            ->toArray();
    }

    public function checkAndReplace(int $id, $newStr, string $field)
    {
        return $this->model->where('id', $id)
            ->update([
                $field => $newStr,
            ]);
    }

    public function updateOrCreate(array $firstArray, array $secondArray)
    {
        return $this->model->updateOrCreate($firstArray, $secondArray);
    }

    public function getNeededTranslationPathByParams(string $path, int $languageId)
    {
        return $this->model->select('path')
            ->whereHas('product', function ($q) use ($path) {
                $q->whereHas('product_translation', function ($q) use ($path) {
                    $q->where('path', $path);
                });
            })
            ->where('language_id', $languageId)
            ->value('path');
    }

    public function getNeededTranslationIdByParams(int $translationId, int $languageId)
    {
        return $this->model->select('id')
            ->whereHas('product', function ($q) use ($translationId) {
                $q->whereHas('product_translation', function ($q) use ($translationId) {
                    $q->where('id', $translationId);
                });
            })
            ->where('language_id', $languageId)
            ->value('id');
    }
}
