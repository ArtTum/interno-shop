<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadProjects extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_price',
        'service_prices'
    ];

    public function project_translations(): HasMany
    {
        return $this->hasMany(LeadProjectTranslation::class, 'project_id', 'id');
    }
}
