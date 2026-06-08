<?php

namespace App\Models;

use App\Repositories\Language\LanguageRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'calculator_id', 'parent_id', 'media_id', 'responsive_settings', 'price_adjustment', 'hide_for_front'
    ];

    protected $casts = [
        'responsive_settings' => 'array',
        'hide_for_front' => 'boolean',
    ];

    public function calculator(): BelongsTo
    {
        return $this->belongsTo(Calculator::class);
    }

    public function category_translation(): HasOne
    {
        return $this->hasOne(CategoryTranslation::class);
    }

    public function category_translation_base(): HasOne
    {
        $languageRepository = new LanguageRepository(new Language());
        $baseLanguageId = $languageRepository->getBaseId();

        return $this->hasOne(CategoryTranslation::class)->where("language_id", $baseLanguageId);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id')->select('id', 'parent_id');
    }

    public function category_translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function products_pivot(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->select('id', 'parent_id');
    }

    public function allDescendants(): Builder|HasMany
    {
        return $this->children()->with(['allDescendants' => function ($allDescendantsQuery) {
            $allDescendantsQuery->select('id', 'parent_id')
                ->with([
                    'category_translation_base' => function ($translationQuery) {
                        $translationQuery->select('id', 'category_id', 'name');
                    }
                ]);
        }]);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
