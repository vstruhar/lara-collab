<?php

namespace App\Models\Filters;

use Illuminate\Database\Eloquent\Builder;
use Lacodix\LaravelModelFilter\Filters\Filter;

class TaskOverdueFilter extends Filter
{
    public function __construct(protected string $field) {}

    public function apply(Builder $query): Builder
    {
        return $query->whereDate($this->field, '<', now())
            ->whereNull('completed_at');
    }
}
