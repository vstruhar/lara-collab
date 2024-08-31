<?php

namespace App\Models;

use App\Models\Filters\IsNullFilter;
use App\Models\Filters\TaskCompletedFilter;
use App\Models\Filters\TaskOverdueFilter;
use App\Models\Filters\WhereHasFilter;
use App\Models\Filters\WhereInFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Lacodix\LaravelModelFilter\Traits\HasFilters;
use Lacodix\LaravelModelFilter\Traits\IsSearchable;
use LaravelArchivable\Archivable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Task extends Model implements AuditableContract, Sortable
{
    use Archivable, Auditable, HasFactory, HasFilters, IsSearchable, SortableTrait;

    protected $fillable = [
        'project_id',
        'group_id',
        'created_by_user_id',
        'assigned_to_user_id',
        'invoice_id',
        'name',
        'number',
        'description',
        'due_on',
        'estimation',
        'hidden_from_clients',
        'billable',
        'order_column',
        'assigned_at',
        'completed_at',
    ];

    protected $searchable = [
        'name',
        'number',
    ];

    protected $casts = [
        'due_on' => 'date',
        'completed_at' => 'datetime',
        'hidden_from_clients' => 'boolean',
        'billable' => 'boolean',
        'estimation' => 'float',
    ];

    protected $observables = [
        'archived',
        'unArchived',
    ];

    public array $defaultWith = [
        'project:id,name',
        'createdByUser:id,name,avatar',
        'assignedToUser:id,name,avatar',
        'subscribedUsers:id',
        'labels:id,name,color',
        'attachments',
        'timeLogs.user:id,name',
    ];

    public function filters(): array
    {
        return [
            (new WhereInFilter('group_id'))->setQueryName('groups'),
            (new WhereInFilter('assigned_to_user_id'))->setQueryName('assignees'),
            (new TaskOverdueFilter('due_on'))->setQueryName('overdue'),
            (new IsNullFilter('due_on'))->setQueryName('not_set'),
            (new TaskCompletedFilter('completed_at'))->setQueryName('status'),
            (new WhereHasFilter('labels'))->setQueryName('labels'),
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function ($query) {
            $query->ordered();
        });
    }

    public function scopeWithDefault(Builder $query)
    {
        $query->with($this->defaultWith);
    }

    public function loadDefault()
    {
        return $this->load($this->defaultWith);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function taskGroup(): BelongsTo
    {
        return $this->belongsTo(TaskGroup::class, 'group_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function subscribedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'subscribe_task');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'activity_capable');
    }
}
