<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id', 'language_id', 'parent_id', 'post_category_translation_id', 'name', 'subname',
        'slug', 'button_text', 'meta_title', 'meta_keywords', 'meta_description', 'path', 'breadcrumb', 'approved'
    ];

    protected $casts = [
        'approved' => 'boolean',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function postCategoryTranslation(): BelongsTo
    {
        return $this->belongsTo(PostCategoryTranslation::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->whereNull('parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PageTranslation::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PageTranslation::class, 'parent_id');
    }

    public function allDescendants(): Builder|HasMany
    {
        return $this->children()->with(['allDescendants' => function ($allDescendantsQuery) {
            $allDescendantsQuery->select('id', 'parent_id', 'name');
        }]);
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
}
