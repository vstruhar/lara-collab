<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CurrencyRate\StoreCurrencyRateRequest;
use App\Models\ClientCompany;
use App\Models\Currency;
use App\Models\CurrencyRate;
use App\Models\OwnerCompany;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Lacodix\LaravelModelFilter\Traits\IsSearchable;
use Lacodix\LaravelModelFilter\Traits\IsSortable;

class CurrencyRateController extends Controller
{
    use IsSearchable, IsSortable;

    public function index(): Response
    {
        $this->authorize('viewAny', CurrencyRate::class);

        $baseCurrency = OwnerCompany::with('currency')->first()->currency;

        $usedCurrencyIds = ClientCompany::with('currency')->get()->map(fn ($clientCompany) => $clientCompany->currency->id)
            ->merge(User::with('currency')->get()->map(fn ($user) => $user->currency->id))
            ->reject(fn ($currencyId) => $currencyId === $baseCurrency->id);

        return Inertia::render('Settings/CurrencyRates/Index', [
            'baseCurrency' => $baseCurrency->code,
            'items' => Currency::searchByQueryString()
                ->sortByQueryString()
                ->whereIn('id', $usedCurrencyIds)
                ->get(['code', 'name', 'symbol', 'decimals']),
        ]);
    }

    public function store(StoreCurrencyRateRequest $request)
    {
        $this->authorize('create', CurrencyRate::class);

        return redirect()->route('settings.currency-rates.index')->success('Currency rates updated', 'The currency rates were successfully updated.');
    }
}
