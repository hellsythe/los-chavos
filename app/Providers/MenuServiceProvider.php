<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Sdkconsultoria\Base\Services\MenuService;
use App\Models\Sale;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Client;
use App\Models\Order;
use App\Models\Design;
use Base;

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
        $service_menu->addElement([
            'name' => 'Punto de venta',
            'icon' => Base::icon('computer-desktop', ['class' => 'h-6 w-6']),
            'url' =>  'sale.point',
            'crud' => '',
            'extra_urls' => [],
        ]);
        $service_menu->addElement(Sale::makeMenu('banknotes'));
        $service_menu->addElement(Employee::makeMenu('tag'));
        $service_menu->addElement(Design::makeMenu('puzzle-piece'));
        // $service_menu->addElement(Payment::makeMenu('book-open'));
        $service_menu->addElement(Client::makeMenu('user-group'));
        // $service_menu->addElement(Order::makeMenu('book-open'));
    }
}
