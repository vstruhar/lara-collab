<?php

namespace App\Http\Controllers;

use App\Actions\User\CreateUser;
use App\Actions\User\UpdateUser;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Users/Index', [
            'items' => UserResource::collection(
                User::searchByQueryString()
                    ->sortByQueryString()
                    ->withoutRole('client')
                    ->with('roles:id,name')
                    ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                    ->paginate(12)
            ),
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create');
    }

    public function store(StoreUserRequest $request)
    {
        (new CreateUser)->create($request->validated());

        return redirect()->route('users.index')->success('User created', 'A new user was successfully created.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', ['item' => new UserResource($user)]);
    }

    public function update(User $user, UpdateUserRequest $request)
    {
        (new UpdateUser)->update($user, $request->validated());

        return redirect()->route('users.index')->success('User updated', 'The user was successfully updated.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->warning('Action stopped', 'You cannot archive the user with whom you are currently logged in.');
        }
        $user->archive();

        return redirect()->back()->success('User archived', 'The user was successfully archived.');
    }

    public function restore(int $userId)
    {
        $user = User::withArchived()->findOrFail($userId);

        $this->authorize('restore', $user);

        $user->unArchive();

        return redirect()->back()->success('User restored', 'The restoring of the user was completed successfully.');
    }
}
