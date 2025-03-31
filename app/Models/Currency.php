<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lacodix\LaravelModelFilter\Traits\IsSearchable;
use Lacodix\LaravelModelFilter\Traits\IsSortable;

class Currency extends Model
{
    use IsSearchable, IsSortable;

    protected $searchable = [
        'name',
        'code',
    ];

    protected $sortable = [
        'name' => 'asc',
        'code' => 'asc',
    ];

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
