<?php

namespace App\Models\Filters;

use Illuminate\Database\Eloquent\Builder;
use Lacodix\LaravelModelFilter\Filters\Filter;

class WhereHasFilter extends Filter
{
    public function __construct(protected string $relation) {}

    public function apply(Builder $query): Builder
    {
        return $query->whereHas($this->relation, function (Builder $query) {
            $query->whereIn('id', $this->values);
        });
    }
}
