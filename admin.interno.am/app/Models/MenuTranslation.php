<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id', 'type', 'priority', 'language_id', 'category_translation_id', 'product_translation_id',
        'page_translation_id', 'name', 'url', 'status', 'new_tab', 'text_for_all', 'approved'
    ];

    protected $casts = [
        'approved' => 'boolean',
        'status' => 'boolean',
        'new_tab' => 'boolean'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuTranslation::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuTranslation::class, 'parent_id')->orderBy('priority', 'ASC');
    }

    public function allDescendants(): Builder|HasMany
    {
        return $this->children()->with([
            'allDescendants' => function ($allDescendantsQuery) {
                $allDescendantsQuery->select('id', 'parent_id', 'name', 'text_for_all')->orderBy('priority', 'ASC');
            }
        ]);
    }

    public function childrenForFront(): HasMany
    {
        return $this->hasMany(MenuTranslation::class, 'parent_id')
            ->where('status', true)
            ->with([
                'category_translation' => function ($query) {
                    $query->select('id', 'path');
                },
                'product_translation' => function ($query) {
                    $query->select('id', 'path');
                },
                'page_translation' => function ($query) {
                    $query->select('id', 'path');
                },
            ])
            ->orderBy('priority', 'ASC');
    }

    public function allDescendantsForFront(): Builder|HasMany
    {
        return $this->childrenForFront()->with([
            'allDescendantsForFront' => function ($allDescendantsQuery) {
                $allDescendantsQuery->select('id', 'parent_id', 'type', 'category_translation_id', 'product_translation_id',
                    'page_translation_id', 'name', 'url', 'new_tab', 'text_for_all')
                    ->where('status', true)
                    ->with([
                        'category_translation' => function ($query) {
                            $query->select('id', 'path');
                        },
                        'product_translation' => function ($query) {
                            $query->select('id', 'path');
                        },
                        'page_translation' => function ($query) {
                            $query->select('id', 'path', 'page_id');
                        },
                    ])
                    ->orderBy('priority', 'ASC');
            }
        ]);
    }

    public function category_translation(): BelongsTo
    {
        return $this->belongsTo(CategoryTranslation::class);
    }

    public function product_translation(): BelongsTo
    {
        return $this->belongsTo(ProductTranslation::class);
    }

    public function page_translation(): BelongsTo
    {
        return $this->belongsTo(PageTranslation::class);
    }
}
