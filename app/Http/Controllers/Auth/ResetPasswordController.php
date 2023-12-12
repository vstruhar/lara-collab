<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ResetPasswordController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', 'Email with reset link was sent');
        }

        $message = match ($status) {
            'passwords.throttled' => 'Please wait before retrying.',
            'passwords.user' => "We can't find a user with that email address.",
            default => 'Whoops, something went wrong.',
        };

        throw ValidationException::withMessages([
            'email' => [$message],
        ]);
    }
}
