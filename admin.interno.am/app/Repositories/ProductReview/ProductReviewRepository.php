<?php

namespace App\Repositories\ProductReview;

use App\Constants\ProductConstants;
use App\Constants\ReviewConstants;
use App\Models\ProductReview;
use App\Repositories\BaseRepository;
use App\Repositories\ProductReview\Interfaces\ProductReviewRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductReviewRepository extends BaseRepository implements ProductReviewRepositoryInterface
{
    public function __construct(ProductReview $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->where('verified', 1)
            ->when($params['status'] >= 0, function ($statusQuery) use ($params) {
                $statusQuery->where('status', $params['status']);
            })->when($params['is_uploaded'] >= 0, function ($uploadedQuery) use ($params) {
                $uploadedQuery->where('is_uploaded', $params['is_uploaded']);
            })->when($params['user_id'] >= 0, function ($query) use ($params) {
                $query->where('user_id', $params['user_id']);
            })->when(!empty($params['product_id']) && $params['product_id'] > 0, function ($query) use ($params) {
                $query->where('product_id', $params['product_id']);
            })->when($params['attachments'] == 1, function ($query) use ($params) {
                $query->whereHas('attachments');
            })->when($params['attachments'] == 0, function ($query) use ($params) {
                $query->whereDoesntHave('attachments');
            });
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })
            ->with([
                'product' => function ($translationQuery) use ($params) {
                    $baseLanguageId = $params['base_id_of_lang'];
                    $translationQuery->select('products.id', 'product_translations.name')
                        ->join('product_translations', 'product_translations.product_id', '=', 'products.id')
                        ->where('product_translations.language_id', $baseLanguageId);
                }
            ])
            ->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields, array $with = []): ?Model
    {
        return $this->model->select(DB::raw($selectedFields))
            ->where($whereField, $whereValue)
            ->with([
                'attachments' => function ($q) {
                    $q->select(['id', 'path', 'product_review_id', 'type'])->orderBy('priority', 'ASC');
                }
            ])->first();
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function fetchForExport(array $data, string $baseUrl, string $vendorKey): Collection
    {
        return $this->model->with(
            [
                'attachments' => function ($translationQuery) use ($data, $baseUrl, $vendorKey) {
                    $translationQuery->selectRaw("product_review_id, CONCAT('{$baseUrl}/uploads/{$vendorKey}/images/maximum', path) as path");
                },
                'product' => function ($query) {
                    $query->select('id')
                        ->with([
                            'product_variant_main' => function ($query) {
                                $query->select('sku', 'product_id');
                            }
                        ]);
                }
            ]
        )
            ->when(!filter_var($data['isAll'], FILTER_VALIDATE_BOOLEAN), function ($query) use ($data) {
                $query->whereIn('id', $data['ids'])
                    ->orderBy($data['ordering_field'], $data['ordering_direction']);;
            })->get();
    }

    public function fetchForUpload(int $id)
    {
        return $this->model->select('id')
            ->where('id', $id)
            ->first();
    }

    public function loadReviews(int $offset, int $productId, int $languageId)
    {
        return $this->model->select(
            'product_reviews.id', 'product_id', 'name', 'rating', 'product_reviews.text', 'product_reviews.created_at', 'country_code',
            'product_review_translations.text as translated_text'
        )
            ->where('status', true)
            ->where('product_id', $productId)
            ->with([
                'attachments' => function ($q) {
                    $attachmentTypes = ReviewConstants::TYPES;
                    $q->select([
                        'product_review_attachments.id',
                        'product_review_attachments.product_review_id',
                        'path as general_path',
                        DB::raw("CASE type
                                    WHEN {$attachmentTypes['video']} THEN 'video'
                                    WHEN {$attachmentTypes['image']} THEN 'image'
                                    END as type")
                    ])
                        ->orderBy('priority', 'ASC');
                }
            ])
            ->leftJoin('product_review_translations', function ($join) use ($languageId) {
                $join->on('product_review_translations.product_review_id', '=', 'product_reviews.id')
                    ->where('product_review_translations.language_id', $languageId);
            })
            ->orderBy('product_reviews.created_at', 'desc')
            ->offset($offset)
            ->limit(ProductConstants::REVIEWS_LIMIT_FOR_RENDERING)
            ->get();
    }

    public function verifyReview(string $token)
    {
        return $this->model->where('verification_token', $token)
            ->update([
                'verified' => true,
                'verification_token' => null,
            ]);
    }

    public function getLastId()
    {
        return $this->model->select('id')->max('id');
    }
}
