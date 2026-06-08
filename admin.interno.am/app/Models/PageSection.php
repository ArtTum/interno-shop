<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_translation_id', 'parent_id', 'priority', 'responsive_settings', 'classes', 'type', 'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'responsive_settings' => 'array'
    ];

    public function page_translation(): BelongsTo
    {
        return $this->belongsTo(PageTranslation::class);
    }

    public function page_section_parent(): BelongsTo
    {
        return $this->belongsTo(PageSection::class, 'parent_id', 'id');
    }

    public function columns(): HasMany
    {
        return $this->hasMany(PageSection::class, 'parent_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(PageSectionComponent::class);
    }
}
