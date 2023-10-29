<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleSocialiteController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            $findUser = User::where('google_id', $user->id)->first();

            if ($findUser) {
                Auth::login($findUser);

                return redirect()->route('dashboard');
            }

            $findUser = User::where('email', $user->email)->first();

            if ($findUser) {
                $findUser->update(['google_id' => $user->id]);
                Auth::login($findUser);

                return redirect()->route('dashboard');
            }

            return redirect()->route('auth.login.form')->with(['notify' => 'social-login-user-not-found']);
        } catch (Exception $e) {
            Log::error('Social login with google has failed', ['message' => $e->getMessage()]);

            return redirect()->route('auth.login.form')->with(['notify' => 'social-login-failed']);
        }
    }
}
