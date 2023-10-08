<?php

use App\Models\Order;
use App\Services\WhatsappNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::namespace('\App\Http\Controllers\Admin')
    ->middleware('auth')
    ->prefix('admin')->group(function () { 
    Route::SdkResource('setting', SettingController::class);
        Route::SdkResource('cash-box-report', CashBoxReportController::class);
        Route::SdkResource('order-design-print', OrderDesignPrintController::class);
        Route::SdkResource('design-print', DesignPrintController::class);
        Route::SdkResource('typography', TypographyController::class);
        Route::SdkResource('garment', GarmentController::class);
        Route::SdkResource('subservice', SubserviceController::class);
        Route::SdkResource('service', ServiceController::class);
        Route::SdkResource('employee', EmployeeController::class);
        Route::SdkResource('payment', PaymentController::class);
        Route::SdkResource('order-detail', OrderDetailController::class);
        Route::SdkResource('client', ClientController::class);
        Route::SdkResource('order', OrderController::class);
        Route::SdkResource('design', DesignController::class);
        Route::get('/', 'DashboardController@index')->name('dashboard');
        Route::get('/orders-by-design', 'DashboardController@indexGrupBy')->name('dashboard.groupby');
        Route::get('/orders-by-design/{id}', 'DashboardController@ordersGroupBy')->name('dashboard.grouped');
        Route::get('/api-order/update-status/{id}/{status}', 'OrderController@updateOrderStatus')->name('order.update.status');
        Route::get('/api-order/whatsapp/{id}', 'OrderController@notifyClientByWhatsapp')->name('order.notify.whatsapp');

        Route::get('/cash-box-report/create', 'CashController@report')->name('cash-box-report.create');
        Route::post('/cashbox/save', 'CashController@save')->name('cashbox.save');

        Route::get('sale-point', 'SalePointController@index')->name('sale.point');
        Route::get('order/update/{id}', 'SalePointController@update')->name('sale.point.edit');
        Route::post('sale-save', 'SalePointController@saveOrder')->name('sale.point.save');
        Route::get('ticket/{id}', 'OrderController@ticket')->name('sale.ticket');
        Route::get('etiquetas/{id}', 'OrderController@etiqueta')->name('sale.etiqueta');
        Route::get('payment/report/pdf', 'PaymentController@report')->name('payment.report');
    });
Route::get('aviso-privacidad', '\App\Http\Controllers\StaticSiteController@privacy');
Route::get('/', function () {
    return redirect('admin');
});


Route::namespace('\Sdkconsultoria\Core\Http\Controllers')
    ->prefix('admin/v1')->group(function () {
        Route::SdkApi('role', 'RoleController');
    });
