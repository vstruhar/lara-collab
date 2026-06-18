<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'content',
        'is_locked',
        'passcode_salt',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    protected $hidden = [
        'passcode_salt',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
