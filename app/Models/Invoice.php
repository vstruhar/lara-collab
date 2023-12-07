<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invoice extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'client_company_id',
        'created_by_user_id',
        'number',
        'status',
        'type',
        'amount',
        'amount_with_tax',
        'billing_date',
    ];

    protected $dates = ['billing_date'];

    public function clientCompany(): BelongsTo
    {
        return $this->belongsTo(ClientCompany::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
