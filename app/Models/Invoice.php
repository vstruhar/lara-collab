<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
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
}
