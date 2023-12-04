<?php

namespace App\Models;

use App\Models\Scopes\OrderByScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lacodix\LaravelModelFilter\Traits\IsSearchable;
use Lacodix\LaravelModelFilter\Traits\IsSortable;
use LaravelArchivable\Archivable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ClientCompany extends Model implements AuditableContract
{
    use Archivable, Auditable, HasFactory, IsSearchable, IsSortable;

    protected $fillable = [
        'name',
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
        'rate',
    ];

    protected $searchable = [
        'name',
        'email',
    ];

    protected $sortable = [
        'name',
        'email',
        'rate',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new OrderByScope('name'));
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_company', 'client_company_id', 'client_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public static function dropdownValues($options = []): array
    {
        return self::orderBy('name')
            ->when(in_array('hasProjects', $options), fn ($query) => $query->has('projects'))
            ->get(['id', 'name'])
            ->map(fn ($i) => ['value' => (string) $i->id, 'label' => $i->name])
            ->toArray();
    }
}
