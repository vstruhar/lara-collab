<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function form(): Response
    {
        return Inertia::render('Auth/Login', []);
    }

    public function attempt(LoginRequest $request): Response
    {
        return Inertia::render('Auth/Login', []);
    }
}
