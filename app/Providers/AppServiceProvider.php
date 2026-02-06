<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Listeners\CheckDeviceLogin;

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
        Event::listen(
            Login::class,
            CheckDeviceLogin::class
        );

        View::composer('layouts.contentNavbarLayout', function ($view) {
            $notifications = collect([]);
            $unreadCount = 0;

            if (Auth::check()) {
                /** @var User $user */
                $user = Auth::user();
                $notifications = $user->notifications()->take(5)->get();
                $unreadCount = $user->unreadNotifications()->count();
            }

            $view->with('notifications', $notifications)
                ->with('unreadCount', $unreadCount);
        });
    }
}
