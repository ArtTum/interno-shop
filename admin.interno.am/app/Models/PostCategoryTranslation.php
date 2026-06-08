<?php

namespace App\Models;

use Beta\Microsoft\Graph\Model\Post;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_category_id', 'language_id', 'name', 'slug', 'description'
    ];

    public function post_category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(PageTranslation::class);
    }
}
