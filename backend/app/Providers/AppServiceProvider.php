<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
        \App\Models\BlotterRecord::observe(\App\Observers\BlotterObserver::class);
        Event::subscribe(\App\Listeners\DispatchN8nWebhook::class);
    }
}
