<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageSectionComponentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_section_component_id', 'category_translation_id', 'post_category_translation_id', 'product_translation_id',
        'page_translation_id', 'calculator_translation_id', 'media_id', 'priority', 'config'
    ];

    protected $casts = [
        'config' => 'array'
    ];

    public function page_section_component(): BelongsTo
    {
        return $this->belongsTo(PageSectionComponent::class);
    }

    public function category_translation(): BelongsTo
    {
        return $this->belongsTo(CategoryTranslation::class);
    }

    public function product_translation(): BelongsTo
    {
        return $this->belongsTo(ProductTranslation::class);
    }

    public function post_category_translation(): BelongsTo
    {
        return $this->belongsTo(PostCategoryTranslation::class);
    }

    public function page_translation(): BelongsTo
    {
        return $this->belongsTo(PageTranslation::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function calculator_translation(): BelongsTo
    {
        return $this->belongsTo(CalculatorTranslation::class);
    }
}
