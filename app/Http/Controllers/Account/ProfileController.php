<?php

namespace App\Http\Controllers\Account;

use App\Actions\User\UpdateAuthUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateAuthUserRequest;
use App\Http\Resources\User\AuthUserResource;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit()
    {
        return Inertia::render('Account/Profile/Edit', [
            'user' => new AuthUserResource(auth()->user()),
        ]);
    }

    public function update(UpdateAuthUserRequest $request)
    {
        (new UpdateAuthUser)->update($request->user(), $request->validated());

        return redirect()->back()->success('User updated', 'The user was successfully updated.');
    }
}
