<?php

namespace App\Http\Controllers\Account;

use App\Actions\User\UpdateAuthUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateAuthUserRequest;
use App\Http\Resources\User\AuthUserResource;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        return Inertia::render('Account/Profile/Edit', [
            'user' => new AuthUserResource(auth()->user()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAuthUserRequest $request)
    {
        (new UpdateAuthUser)->update($request->user(), $request->validated());

        return redirect()->back()->success('User updated', 'The user was successfully updated.');
    }
}
