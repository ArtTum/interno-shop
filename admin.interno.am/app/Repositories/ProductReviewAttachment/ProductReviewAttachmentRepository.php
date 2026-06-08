<?php

namespace App\Repositories\ProductReviewAttachment;

use App\Constants\ReviewConstants;
use App\Models\ProductReviewAttachment;
use App\Repositories\BaseRepository;
use App\Repositories\ProductReviewAttachment\Interfaces\ProductReviewAttachmentRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductReviewAttachmentRepository extends BaseRepository implements ProductReviewAttachmentRepositoryInterface
{
    public function __construct(ProductReviewAttachment $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function fetchQuery(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Builder
    {
        return parent::fetch($select, $pagination, $ordering, $params, $searchFields, $joins);
    }

    public function fetch(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): Collection
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)
            ->when(!empty($pagination), function ($query) use ($pagination) {
                $query->limit($pagination['limit'])
                    ->offset($pagination['offset']);
            })->get();
    }

    public function fetchTotalCount(string $select, array $pagination, array $ordering, array $params, array $searchFields, array $joins): int
    {
        return self::fetchQuery($select, $pagination, $ordering, $params, $searchFields, $joins)->count();
    }

    public function fetchByField(string $whereField, string|int $whereValue, string $selectedFields): ?Model
    {
        return parent::fetchByField($whereField, $whereValue, $selectedFields);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    public function deleteByField(string $fieldName, int $fieldValue): bool
    {
        return $this->model->where($fieldName, $fieldValue)->delete();
    }

    public function getMediaIdsByReviewId(int $reviewId)
    {
        return $this->model->select('media_id')
            ->where('product_review_id', $reviewId)
            ->pluck('media_id')
            ->toArray();
    }

    public function bulkDeleteByMediaIds(array $mediaIds): bool
    {
        return $this->model->whereIn('media_id', $mediaIds)->delete();
    }

    public function fetchForProduct(int $productId)
    {
        $attachmentTypes = ReviewConstants::TYPES;
        return $this->model->select([
            'product_review_attachments.id',
            'product_review_attachments.product_review_id',
            'path as general_path',
            'product_reviews.name',
            DB::raw("CONCAT('Review attachment by: ', product_reviews.name) as alt"),
            DB::raw("CASE type
                WHEN {$attachmentTypes['video']} THEN 'video'
                WHEN {$attachmentTypes['image']} THEN 'image'
                END as type")
        ])
            ->join('product_reviews', 'product_reviews.id', '=', 'product_review_attachments.product_review_id')
            ->whereHas('product_review', function($query) use ($productId) {
                $query->where('status', true)
                    ->whereHas('product', function($query) use ($productId) {
                    $query->where('id', $productId);
                });
            })
            ->orderBy('product_reviews.created_at', 'DESC')
            ->orderBy('priority', 'ASC')
            ->get();
    }
}
