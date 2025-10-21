<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyzedString extends Model
{
    protected $table = 'analyzed_strings';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'value', 'properties'];
    protected $casts = [
        'properties' => 'array',
    ];
}

