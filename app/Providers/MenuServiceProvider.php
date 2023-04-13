<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Sdkconsultoria\Base\Services\MenuService;
use App\Models\Typography;
use App\Models\Garment;
use App\Models\Subservice;
use App\Models\Service;
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
            'name' => 'Dashboard',
            'icon' => \Base::icon('home', ['class' => 'h-6 w-6']),
            'url' => 'dashboard',
        ], ['super-admin']);

        $service_menu->addElement([
            'name' => 'Punto de venta',
            'icon' => Base::icon('computer-desktop', ['class' => 'h-6 w-6']),
            'url' =>  'sale.point',
            'crud' => '',
            'extra_urls' => [],
        ], ['Punto de venta']);
        $service_menu->addElement(Design::makeMenu('puzzle-piece'), ['super-admin']);
        $service_menu->addElement(Client::makeMenu('user-group'), ['super-admin']);
        $service_menu->addElement(Typography::makeMenu('book-open'), ['super-admin']);
        $service_menu->addElement(Garment::makeMenu('book-open'), ['super-admin']);
        $service_menu->addElement(Service::makeMenu('book-open'), ['super-admin']);
        $service_menu->addElement(Subservice::makeMenu('book-open'), ['super-admin']);
        $service_menu->addElement(Sale::makeMenu('banknotes'), ['super-admin']);
        $service_menu->addElement(\App\Models\User::makeMenu('users'), ['super-admin']);
        // $service_menu->addElement(Employee::makeMenu('tag'), ['super-admin']);
        // $service_menu->addElement(Payment::makeMenu('book-open'));
        // $service_menu->addElement(Order::makeMenu('book-open'));
    }
}
