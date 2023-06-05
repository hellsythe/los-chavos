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
        Route::get('/order/update/{id}', 'OrderController@salePointEdit')->name('sale.point.edit');

        Route::get('/cashbox', 'CashController@report')->name('cashbox.report');
        Route::get('/ticket/{id}', 'OrderController@ticket')->name('sale.ticket');

        Route::get('/sale-point', 'SalePointController@index')->name('sale.point');
        Route::post('/sale-save', 'SalePointController@saveOrder')->name('sale.point.save');
    });



Route::get('/', function () {
    return redirect('admin');
});


Route::namespace('\Sdkconsultoria\Core\Http\Controllers')
    ->prefix('admin/v1')->group(function () {
        Route::SdkApi('role', 'RoleController');
    });



// Route::get('/test', function () {

//     // $request = Http::withToken(config('app.whatsapp_token'))->post('https://graph.facebook.com/v16.0/' . config('app.whatsapp_phone_id') . '/messages', [
//     //     "messaging_product" => "whatsapp",
//     //     "to" => "522213428198",
//     //     "type" => "template",
//     //     "template" => [
//     //         "name" => "notificar_solicitud",
//     //         "language" => ["code" => "es_MX"],

//     //     ],
//     // ]);
//     // dd($request->body());
//     $order = Order::first();
//     (new WhatsappNotification())->sendRequestNotification($order);
//     dd('holis');
// });
