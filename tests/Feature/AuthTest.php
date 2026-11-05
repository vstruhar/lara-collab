<?php

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Socialite\Facades\Socialite;

uses(RefreshDatabase::class);

beforeEach(function () {
    resetPermissionServiceCache();
    seedAccessControl();
    createOwnerCompany();
    $this->admin = createAdmin();
});

it('shows the login form', function () {
    $this->get(route('auth.login.form'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
});

it('logs in and logs out', function () {
    $this->post(route('auth.login.attempt'), [
        'email' => $this->admin->email,
        'password' => 'password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($this->admin);

    $this->delete(route('logout'))
        ->assertRedirect(route('auth.login.form'));

    $this->assertGuest();
});

it('sends a password reset link', function () {
    Notification::fake();

    $this->get(route('auth.forgotPassword.form'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Auth/ForgotPassword'));

    $this->post(route('auth.forgotPassword.sendLink'), [
        'email' => $this->admin->email,
    ])->assertRedirect()
        ->assertSessionHas('status', 'Email with reset link was sent');

    Notification::assertSentTo($this->admin, ResetPassword::class);
});

it('resets a password with a valid token', function () {
    $token = Password::createToken($this->admin);

    $this->get(route('auth.newPassword.form', ['token' => $token, 'email' => $this->admin->email]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Auth/ResetPassword')
            ->where('email', $this->admin->email)
            ->where('token', $token));

    $this->post(route('auth.newPassword.save'), [
        'token' => $token,
        'email' => $this->admin->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertRedirect(route('auth.login.form'))
        ->assertSessionHas('notify', 'password-reset');

    expect(Hash::check('new-password', $this->admin->fresh()->password))->toBeTrue();
});

it('redirects to google and logs in a matching user', function () {
    $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
    $provider->shouldReceive('redirect')
        ->once()
        ->andReturn(redirect('https://accounts.google.com'));

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

    $this->get(route('auth.login.social.google'))
        ->assertRedirect('https://accounts.google.com');

    $socialUser = new Laravel\Socialite\Two\User;
    $socialUser->id = 'google-1';
    $socialUser->email = $this->admin->email;

    $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
    $provider->shouldReceive('user')->once()->andReturn($socialUser);

    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

    $this->get('/callback/google')
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($this->admin);
    expect($this->admin->fresh()->google_id)->toBe('google-1');
});
