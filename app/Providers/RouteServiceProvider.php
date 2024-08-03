<?php

namespace App\Providers;

use App\Models\ClientCompany;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskGroup;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        Route::model('company', ClientCompany::class);

        Route::model('project', Project::class, function ($value) {
            return Project::where('id', $value)
                ->when(auth()->user()->isAdmin(), fn ($query) => $query->withArchived())
                ->firstOrFail();
        });

        Route::model('taskGroup', TaskGroup::class, function ($value) {
            return TaskGroup::where('id', $value)
                ->when(auth()->user()->isAdmin(), fn ($query) => $query->withArchived())
                ->firstOrFail();
        });

        Route::model('task', Task::class, function ($value) {
            return Task::where('id', $value)
                ->when(auth()->user()->isAdmin(), fn ($query) => $query->withArchived())
                ->firstOrFail();
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('auth')
                ->group(base_path('routes/auth.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
