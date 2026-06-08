<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageSectionComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_section_id', 'component_id', 'priority', 'config'
    ];

    protected $casts = [
        'config' => 'array'
    ];

    public function page_section_column(): BelongsTo
    {
        return $this->belongsTo(PageSection::class, 'page_section_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PageSectionComponentItem::class);
    }
}
