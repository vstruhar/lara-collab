<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    public function clientCompanies(): HasMany
    {
        return $this->hasMany(ClientCompany::class);
    }

    public static function dropdownValues($options = []): array
    {
        return self::orderBy('name')
            ->when(isset($options['with']), fn ($query) => $query->with($options['with']))
            ->get()
            ->map(fn ($i) => array_merge([
                'value' => (string) $i->id,
                'label' => "{$i->name} ({$i->symbol})",
            ], isset($options['with']) ? $i->toArray() : []))
            ->toArray();
    }
}
