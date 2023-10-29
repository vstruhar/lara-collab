<?php

namespace App\Http\Controllers;

use App\Actions\User\CreateUser;
use App\Actions\User\UpdateUser;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Users/Index', [
            'users' => new UserCollection(
                User::searchByQueryString()
                    ->sortByQueryString()
                    ->with('roles:id,name')
                    ->paginate(12)
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Users/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        (new CreateUser)->create($request->validated());

        return redirect()->route('users.index')->success('User created', 'New user was created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', ['user' => new UserResource($user)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(User $user, UpdateRequest $request)
    {
        (new UpdateUser)->update($user, $request->validated());

        return redirect()->route('users.index')->success('User updated', 'User was updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
