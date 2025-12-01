<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Listeners\UserEventSubscriber;
use Illuminate\Support\Facades\Event;

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
        //
        // if (app()->environment('production')) {
        //     \URL::forceScheme('https');
        // }
        Event::subscribe(UserEventSubscriber::class);
    }
}
