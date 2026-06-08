<?php

namespace App\Repositories\PostCategoryTranslation;

use App\Models\PostCategoryTranslation;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PostCategoryTranslationRepository extends BaseRepository
{
    public function __construct(PostCategoryTranslation $model)
    {
        $this->model = $model;
    }

    public function create(array $data): Model
    {
        return parent::create($data);
    }

    public function insert(array $data): bool
    {
        return parent::insert($data);
    }

    public function update(string $whereField, string|int $whereValue, array $data): bool
    {
        return parent::update($whereField, $whereValue, $data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function fetchById(int $id)
    {
        return $this->model->where('id', $id)->first();
    }


    public function fetchAsParam(int $languageId): Collection
    {
        return $this->model->select('id as value', 'name as label')
            ->where('language_id', $languageId)
            ->get();
    }

    public function getSlugAndNameById(int $id)
    {
        return $this->model->select('slug', 'name')->where('id', $id)->first();
    }

    public function getPostCategoryIdById(int $id)
    {
        return $this->model->select('post_category_id')
            ->where('id', $id)
            ->value('post_category_id');
    }

    public function getIdByPostCategoryIdAndLanguageId(int $postCategoryId, int $languageId): ?int
    {
        return $this->model->select('id')
            ->where('post_category_id', $postCategoryId)
            ->where('language_id', $languageId)
            ->value('id');
    }

    public function getNeededTranslationIdByParams(int $translationId, int $languageId)
    {
        return $this->model->select('id')
            ->whereHas('post_category', function ($q) use ($translationId) {
                $q->whereHas('post_category_translation', function ($q) use ($translationId) {
                    $q->where('id', $translationId);
                });
            })
            ->where('language_id', $languageId)
            ->value('id');
    }
}
