<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'name',
        'path',
        'thumb',
        'type',
        'size',
    ];

    protected $appends = [
        'url',
        'thumb_url',
    ];

    protected function url(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->exists) {
                return null;
            }

            return route('task-attachments.download', $this);
        });
    }

    protected function thumbUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->exists || $this->thumb === null || $this->thumb === '') {
                return null;
            }

            return route('task-attachments.thumb', $this);
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
