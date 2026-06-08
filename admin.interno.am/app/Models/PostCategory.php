<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PostCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'language_id'
    ];

    public function post_category_translation(): HasOne
    {
        return $this->hasOne(PostCategoryTranslation::class);
    }
}
