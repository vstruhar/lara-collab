<?php

namespace App\Models;

use App\Services\RichTextSanitizer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'task_id',
        'content',
    ];

    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => RichTextSanitizer::cleanStored($value),
            set: fn (?string $value): ?string => RichTextSanitizer::cleanStored($value),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'activity_capable');
    }
}
