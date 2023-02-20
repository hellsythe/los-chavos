<?php

use Illuminate\Support\Facades\Route;

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
    Route::SdkResource('sale-detail', SaleDetailController::class);
    Route::SdkResource('sale', SaleController::class);
    Route::SdkResource('employee', EmployeeController::class);
    Route::SdkResource('payment', PaymentController::class);
    Route::SdkResource('order-detail', OrderDetailController::class);
    Route::SdkResource('client', ClientController::class);
    Route::SdkResource('order', OrderController::class);
    Route::SdkResource('design', DesignController::class);
    Route::get('/', 'DashboardController@index')->name('dashboard');
    Route::get('/sale-point', 'SaleController@salePoint')->name('sale.point');
});
