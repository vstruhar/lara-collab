<?php

namespace App\Models\Filters;

use Illuminate\Database\Eloquent\Builder;
use Lacodix\LaravelModelFilter\Filters\Filter;

class IsNullFilter extends Filter
{
    public function __construct(protected string $field) {}

    public function apply(Builder $query): Builder
    {
        return $query->whereNull($this->field);
    }
}
