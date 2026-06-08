<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id', 'category_id', 'a_plus_content_id', 'snippet_id', 'related_category_translation_id',
        'language_id', 'name', 'slug', 'path', 'breadcrumb', 'description', 'meta_title', 'meta_keywords', 'meta_description', 'approved'
    ];

    protected $casts = [
        'approved' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CategoryTranslation::class, 'parent_id')->select('id', 'category_id', 'parent_id', 'name', 'slug');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class, 'parent_id')->select('id', 'category_id', 'parent_id', 'name', 'slug');
    }

    public function allDescendants(): Builder|HasMany
    {
        return $this->children()->with(['allDescendants' => function ($allDescendantsQuery) {
            $allDescendantsQuery->select('id', 'parent_id', 'name', 'slug');
        }]);
    }

    public function related(): BelongsTo
    {
        return $this->belongsTo(CategoryTranslation::class, 'related_category_translation_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getSlugPathWithBreadcrumb(): array
    {
        $slugs = [];
        $names = [];
        $currentPage = $this;


        while ($currentPage) {
            array_unshift($slugs, $currentPage->slug);
            array_unshift($names, $currentPage->name); // Assuming you have a 'name' property
            $currentPage = $currentPage->parent;
        }

        $path = '/' . implode('/', $slugs);
        $breadcrumb = implode('#&^', $names);

        return [
            'path' => $path,
            'breadcrumb' => $breadcrumb,
        ];
    }

    public function a_plus_content(): BelongsTo
    {
        return $this->belongsTo(PageTranslation::class);
    }

    public function snippet(): BelongsTo
    {
        return $this->belongsTo(PageTranslation::class);
    }
}
