<?php

namespace App\Providers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        foreach (['info', 'success', 'warning', 'error'] as $type) {
            RedirectResponse::macro(
                $type,
                function ($title, $message = null) use ($type) {
                    return $this->with('flash', ['type' => $type, 'title' => $title, 'message' => $message]);
                }
            );
        }
    }
}
