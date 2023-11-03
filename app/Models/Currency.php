<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    public static function dropdownValues(): array
    {
        return self::orderBy('name')
            ->get(['id', 'name', 'symbol'])
            ->map(fn ($i) => ['value' => (string) $i->id, 'label' => "{$i->name} ({$i->symbol})"])
            ->toArray();
    }
}
