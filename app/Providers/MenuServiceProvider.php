<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Sdkconsultoria\Base\Services\MenuService;
use App\Models\Design;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $service_menu = app(MenuService::class);
        $service_menu->addElement(Design::makeMenu('book-open'));
    }
}
