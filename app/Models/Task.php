<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lacodix\LaravelModelFilter\Traits\IsSearchable;
use LaravelArchivable\Archivable;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Task extends Model implements Sortable
{
    use Archivable, HasFactory, IsSearchable, SortableTrait;

    protected $fillable = [
        'project_id',
        'task_group_id',
        'created_by_user_id',
        'assigned_to_user_id',
        'name',
        'number',
        'description',
        'due_on',
        'estimation',
        'hidden_from_clients',
        'billable',
        'order_column',
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
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function ($query) {
            $query->ordered();
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function taskGroup(): BelongsTo
    {
        return $this->belongsTo(TaskGroup::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
