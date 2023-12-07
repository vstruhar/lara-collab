<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Lacodix\LaravelModelFilter\Traits\IsSearchable;
use Lacodix\LaravelModelFilter\Traits\IsSortable;
use LaravelArchivable\Archivable;

class Label extends Model
{
    use Archivable, IsSearchable, IsSortable;

    protected $fillable = ['name', 'color'];

    protected $searchable = [
        'name',
    ];

    protected $sortable = [
        'name' => 'asc',
    ];
}
