<?php

namespace App\Models;

use App\Models\Filters\WhereInFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Lacodix\LaravelModelFilter\Traits\HasFilters;

class Activity extends Model
{
    use HasFilters;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'project_id',
        'title',
        'subtitle',
        'created_at',
    ];

    public function filters(): array
    {
        return [
            (new WhereInFilter('project_id'))->setQueryName('project'),
        ];
    }

    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if (! $user->hasRole('client')) {
            return;
        }

        $hiddenTaskIds = fn () => Task::withArchived()
            ->withoutGlobalScope('ordered')
            ->where('hidden_from_clients', true)
            ->select('tasks.id');

        $query->where(function (Builder $query) use ($hiddenTaskIds) {
            $query->where(function (Builder $query) use ($hiddenTaskIds) {
                $query->where('activity_capable_type', Task::class)
                    ->whereNotIn('activity_capable_id', $hiddenTaskIds());
            })->orWhere(function (Builder $query) use ($hiddenTaskIds) {
                $query->where('activity_capable_type', Comment::class)
                    ->whereNotIn(
                        'activity_capable_id',
                        Comment::query()->whereIn('task_id', $hiddenTaskIds())->select('id'),
                    );
            })->orWhere(function (Builder $query) {
                $query->whereNotIn('activity_capable_type', [Task::class, Comment::class]);
            });
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activityCapable(): MorphTo
    {
        return $this->morphTo();
    }
}
