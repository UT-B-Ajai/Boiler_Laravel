<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        ResetPassword::createUrlUsing(function ($notifiable, $token) {
            return url('/reset-password?token='.$token.'&email='.$notifiable->getEmailForPasswordReset());
        });
        // ResetPassword::createUrlUsing(function ($notifiable, $token) {
        //     return env('FRONTEND_URL', 'http://localhost:3000') . "/reset-password?token={$token}&email={$notifiable->getEmailForPasswordReset()}";
        // });
    }
}
