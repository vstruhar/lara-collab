<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => function () {
                    if (! auth()->check()) {
                        return null;
                    }
                    /** @var User */
                    $user = auth()->user();

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'job_title' => $user->job_title,
                        'roles' => $user->getRoleNames(),
                        'permissions' => $user->getAllPermissions()->pluck('name'),
                    ];
                },
                'notifications' => NotificationService::getLatest(6),
            ],
            'shared' => [
                'roles' => fn () => Role::orderBy('name')->get(['id', 'name'])->toArray(),
            ],
            'flash' => session()->get('flash'),
            'version' => config('app.version'),
        ]);
    }
}
