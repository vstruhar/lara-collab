<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelArchivable\Archivable;

class TaskPriority extends Model
{
    use Archivable;

    protected $fillable = ['label', 'color', 'order'];
}
