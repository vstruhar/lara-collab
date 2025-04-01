<?php

namespace App\Models;

use App\Enums\Invoice as InvoiceEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Lacodix\LaravelModelFilter\Traits\IsSearchable;
use Lacodix\LaravelModelFilter\Traits\IsSortable;
use LaravelArchivable\Archivable;

class Invoice extends Model
{
    use Archivable, IsSearchable, IsSortable;

    const UPDATED_AT = null;

    protected $fillable = [
        'client_company_id',
        'created_by_user_id',
        'number',
        'status',
        'type',
        'amount',
        'amount_with_tax',
        'hourly_rate',
        'due_date',
        'note',
        'filename',
    ];

    protected $searchable = [
        'number',
        'status',
    ];

    protected $sortable = [
        'client_company_id',
        'number',
        'status',
        'amount',
        'amount_with_tax',
        'due_date',
        'note',
        'created_at' => 'desc',
    ];

    protected $dates = ['due_date'];

    public function clientCompany(): BelongsTo
    {
        return $this->belongsTo(ClientCompany::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function isFixedAmount(): bool
    {
        return $this->type === InvoiceEnum::TYPE_FIXED_AMOUNT->value;
    }

    public static function getNextNumber(): string
    {
        $number = 0;
        $last = self::latest()->first();

        if ($last?->created_at->isCurrentYear()) {
            $number = (int) Str::substr($last->number, 4);
        }

        return (string) Str::of(++$number)
            ->padLeft(4, '0')
            ->prepend(today()->year);
    }
}
