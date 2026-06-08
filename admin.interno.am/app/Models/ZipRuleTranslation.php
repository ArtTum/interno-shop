<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZipRuleTranslation extends Model
{
    protected $fillable = [
        'fee_label', 'language_id', 'zip_rule_id'
    ];
}
