<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Auth\GoogleSocialiteController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['guest']], function () {
    Route::get('login', [AuthenticationController::class, 'create'])->name('auth.login.form');
    Route::post('login', [AuthenticationController::class, 'store'])->name('auth.login.attempt');
    Route::get('password/forgot', [ResetPasswordController::class, 'create'])->name('auth.forgotPassword.form');
    Route::post('password/forgot', [ResetPasswordController::class, 'store'])->name('auth.forgotPassword.sendLink');
    Route::get('password/new/{token}', [NewPasswordController::class, 'create'])->name('auth.newPassword.form');
    Route::post('password/new', [NewPasswordController::class, 'store'])->name('auth.newPassword.save');

    Route::get('auth/google', [GoogleSocialiteController::class, 'redirectToGoogle'])->name('auth.login.social.google');
    Route::get('callback/google', [GoogleSocialiteController::class, 'handleCallback']);
});

Route::group(['middleware' => ['auth']], function () {
    Route::delete('logout', [AuthenticationController::class, 'destroy'])->name('logout');
});
