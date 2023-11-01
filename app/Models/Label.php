<?php

namespace App\Models;

use App\Models\Scopes\OrderByScope;
use Illuminate\Database\Eloquent\Model;
use Lacodix\LaravelModelFilter\Traits\IsSearchable;
use Lacodix\LaravelModelFilter\Traits\IsSortable;
use LaravelArchivable\Archivable;

class Label extends Model
{
    use Archivable, IsSearchable, IsSortable;

    protected static function booted(): void
    {
        static::addGlobalScope(new OrderByScope('name'));
    }

    protected $fillable = ['name', 'color'];

    protected $searchable = [
        'name',
    ];

    protected $sortable = [
        'name',
    ];
}
