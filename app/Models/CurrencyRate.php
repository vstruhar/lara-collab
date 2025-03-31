<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $fillable = [
        'base',
        'code',
        'value',
    ];

    public $timestamps = false;

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'code', 'code');
    }
}
