<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

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
        View::composer('layouts.contentNavbarLayout', function ($view) {
            $notifications = collect([]);
            $unreadCount = 0;

            if (Auth::check()) {
                $user = Auth::user();
                // Ambil notif database
                $notifications = $user->notifications()->take(5)->get();
                $unreadCount = $user->unreadNotifications()->count();
            }

            $view->with('notifications', $notifications)
                ->with('unreadCount', $unreadCount);
        });
    }
}
