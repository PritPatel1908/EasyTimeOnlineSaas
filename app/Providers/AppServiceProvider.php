<?php

namespace App\Providers;

use App\Models\Central\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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
        View::composer(['layouts.partials.header', 'partials.topbar'], function (\Illuminate\View\View $view): void {
            $user = Auth::guard('tenant')->user() ?? Auth::user();
            $notifications = $user && method_exists($user, 'unreadNotifications')
                ? $user->unreadNotifications()->limit(5)->get()
                : collect();

            $view->with('adminNotifications', $notifications);
        });
    }
}
