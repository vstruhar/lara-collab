<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class OwnerCompany extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'owner_company';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'logo',
        'address',
        'postal_code',
        'city',
        'country_id',
        'currency_id',
        'email',
        'phone',
        'web',
        'iban',
        'swift',
        'business_id',
        'tax_id',
        'vat',
        'tax',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_company', 'owner_company_id', 'client_id');
    }
}
