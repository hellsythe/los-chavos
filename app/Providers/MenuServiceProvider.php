<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Sdkconsultoria\Base\Services\MenuService;
use App\Models\Branch;
use App\Models\Planning;
use App\Models\Employee;
use App\Models\EmbroideryStatistics;
use App\Models\Setting;
use App\Models\CashBoxReport;
use App\Models\DesignPrint;
use App\Models\Typography;
use App\Models\Garment;
use App\Models\Subservice;
use App\Models\Service;
use App\Models\Client;
use App\Models\Order;
use App\Models\Design;
use App\Models\Payment;
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
        ]);

        $service_menu->addElement([
            'name' => 'Punto de venta',
            'icon' => Base::icon('computer-desktop', ['class' => 'h-6 w-6']),
            'url' =>  'sale.point',
            'crud' => '',
            'extra_urls' => [],
        ], ['super-admin', 'Punto de venta']);

        $service_menu->addElement([
            'name' => 'Plan de trabajo',
            'icon' => Base::icon('calendar', ['class' => 'h-6 w-6']),
            'url' =>  'embrodery.calendar',
            'crud' => '',
            'extra_urls' => [],
        ], ['super-admin']);

        $service_menu->addElement([
            'id' => 'chat-menu',
            'name' => 'Chat',
            'icon' => Base::icon('chat-bubble-left-right', ['class' => 'h-6 w-6']),
            'url' =>  'chat.index',
            'crud' => '',
            'extra_urls' => [],
        ], ['super-admin', 'Punto de venta']);

        // $service_menu->addElement(Planning::makeMenu('calendar'), ['super-admin', 'Bordador']);
        $service_menu->addElement(Planning::makeMenu('calendar-days'), ['super-admin']);

        $service_menu->addElement(Order::makeMenu('truck', [], 'deadline'), ['super-admin', 'Punto de venta', 'Bordador', 'Estampador']);
        $service_menu->addElement(CashBoxReport::makeMenu('currency-dollar'), ['super-admin', 'Punto de venta']);

        // $service_menu->addElement([
        //     'name' => 'Caja',
        //     'icon' => Base::icon('currency-dollar', ['class' => 'h-6 w-6']),
        //     'url' =>  'cashbox.report',
        //     'crud' => '',
        //     'extra_urls' => [],
        // ], ['super-admin']);

        $service_menu->addElement(Payment::makeMenu('currency-dollar'), ['super-admin']);
        $service_menu->addElement(Employee::makeMenu('identification'), ['super-admin']);

        $service_menu->addElement(EmbroideryStatistics::makeMenu('chart-bar', [], '-date'), ['super-admin']);

        $service_menu->addElement(Design::makeMenu('puzzle-piece'), ['super-admin', 'Punto de venta', 'Bordador']);
        $service_menu->addElement(DesignPrint::makeMenu('puzzle-piece'),['super-admin', 'Punto de venta', 'Estampador']);
        $service_menu->addElement(Client::makeMenu('user-group'), ['super-admin']);
        $service_menu->addElement(Typography::makeMenu('language'), ['super-admin']);
        $service_menu->addElement(Garment::makeMenu('academic-cap'), ['super-admin']);
        $service_menu->addElement(Service::makeMenu('book-open'), ['super-admin']);
        $service_menu->addElement(Subservice::makeMenu('hashtag'), ['super-admin']);
        $service_menu->addElement(\App\Models\User::makeMenu('users'), ['super-admin']);
        $service_menu->addElement(Branch::makeMenu('book-open'));
        $service_menu->addElement(Setting::makeMenu('book-open'), ['super-admin']);
    }
}
