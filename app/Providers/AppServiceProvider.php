<?php

namespace App\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

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
        Event::listen(Login::class, function (Login $event): void {
            $event->user->forceFill([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => now(),
                'last_seen_at' => now(),
            ])->saveQuietly();

            activity('security')
                ->causedBy($event->user)
                ->withProperties(['ip' => request()->ip()])
                ->log('Connexion réussie');
        });

        Event::listen(Failed::class, function (Failed $event): void {
            if ($event->user !== null) {
                $event->user->increment('failed_login_attempts');

                $event->user->refresh();

                if ($event->user->failed_login_attempts >= (int) config('auth.lockout_attempts')) {
                    $event->user->forceFill([
                        'locked_until' => now()->addMinutes((int) config('auth.lockout_minutes')),
                    ])->saveQuietly();
                }
            }

            activity('security')
                ->withProperties([
                    'email' => $event->credentials['email'] ?? null,
                    'ip' => request()->ip(),
                ])
                ->log('Échec de connexion');
        });
    }
}
