<?php

use Illuminate\Http\Request;
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
    Route::get('/sale-point', 'OrderController@salePoint')->name('sale.point');
    Route::post('/sale-save', 'OrderController@processOrder')->name('sale.point.save');
});



Route::get('/', function(){
    return redirect('admin');
});


Route::namespace('\Sdkconsultoria\Core\Http\Controllers')
->prefix('admin/v1')->group(function () {
    Route::SdkApi('role', 'RoleController');
});
