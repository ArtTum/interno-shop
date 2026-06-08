<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(UserGroupPermission::class);
    }

    public function ips(): HasMany
    {
        return $this->hasMany(UserGroupIP::class);
    }
}
