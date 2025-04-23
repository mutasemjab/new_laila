<?php

use App\Models\Room;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Admin\DayController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Reports\TaxReportController;
use App\Http\Controllers\Reports\OrderReportController;
use App\Http\Controllers\Reports\ProductReportController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use App\Http\Controllers\Reports\InventoryReportController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
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

define('PAGINATION_COUNT',11);
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {




    Route::group(['prefix'=>'admin','middleware'=>'auth:admin'],function(){
        Route::get('/',[DashboardController::class,'index'])->name('admin.dashboard');
        Route::get('logout',[LoginController::class,'logout'])->name('admin.logout');


        /* start  update login admin */
        Route::get('/admin/edit/{id}',[LoginController::class,'editlogin'])->name('admin.login.edit');
        Route::post('/admin/update/{id}',[LoginController::class,'updatelogin'])->name('admin.login.update');
        /* end update login admin */

        /// Role and permission
        Route::resource('employee', 'App\Http\Controllers\Admin\EmployeeController',[ 'as' => 'admin']);
        Route::get('role', 'App\Http\Controllers\Admin\RoleController@index')->name('admin.role.index');
        Route::get('role/create', 'App\Http\Controllers\Admin\RoleController@create')->name('admin.role.create');
        Route::get('role/{id}/edit', 'App\Http\Controllers\Admin\RoleController@edit')->name('admin.role.edit');
        Route::patch('role/{id}', 'App\Http\Controllers\Admin\RoleController@update')->name('admin.role.update');
        Route::post('role', 'App\Http\Controllers\Admin\RoleController@store')->name('admin.role.store');
        Route::post('admin/role/delete', 'App\Http\Controllers\Admin\RoleController@delete')->name('admin.role.delete');

        Route::get('/permissions/{guard_name}', function($guard_name){
            return response()->json(Permission::where('guard_name',$guard_name)->get());
        });

        /*         start  setting                */
        Route::get('/setting/index',[SettingController::class,'index'])->name('admin.setting.index');
        Route::get('/setting/create',[SettingController::class,'create'])->name('admin.setting.create');
        Route::post('/setting/store',[SettingController::class,'store'])->name('admin.setting.store');
        Route::get('/setting/edit/{id}',[SettingController::class,'edit'])->name('admin.setting.edit');
        Route::post('/setting/update/{id}',[SettingController::class,'update'])->name('admin.setting.update');

        /*         end  setting                */


        Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
        Route::post('/certificate/download', [UserController::class, 'generateCertificate'])->name('certificate.download');

        Route::view('/print_badge', 'print_badge')->name('admin.print.badge');


        //Reports
        Route::get('/inventory_report', [InventoryReportController::class, 'index'])->name('inventory_report');
        Route::get('/order_report', [OrderReportController::class, 'index'])->name('order_report');
        Route::get('/product_move', [ProductReportController::class, 'index'])->name('product_move');
        Route::get('/tax_report', [TaxReportController::class, 'index'])->name('tax_report');

        // Attendance routes
        Route::post('/scan-barcode', [AttendanceController::class, 'scanBarcode'])->name('scan.barcode');
        Route::post('/validate-barcode', [AttendanceController::class, 'validateBarcode'])->name('validate.barcode');
        Route::get('/room/statistics', [DashboardController::class, 'getStatistics'])
            ->name('room.statistics');
        Route::get('/initialize-room-occupancy', [AttendanceController::class, 'initializeRoomOccupancy'])->name('initialize.room.occupancy');
        Route::get('user-time/{id}', [UserController::class, 'showLogs'])->name('user-time.show');

        Route::get('/validate-barcode/{get_barcode?}', [AttendanceController::class, 'validateBarcode'])->name('validate.barcode');
        Route::get('/scan-barcode/{get_barcode?}/{get_room_id?}', [AttendanceController::class, 'scanBarcode'])->name('scan.barcode');

        // Resource Route
        Route::resource('users', UserController::class);

        // Route::get('/rooms', [RoomController::class,'index'])->name('rooms.index');
        Route::resource('rooms', RoomController::class);
        Route::get('/get_room/statistics/{room_id}', [AttendanceController::class, 'getStatistics'])
        ->name('room.get.statistics');
        Route::post('/room/get/users/{room_id?}', [AttendanceController::class, 'getRoomUsers'])
        ->name('room.get.users');
        Route::get('/room/{room_id}/{name?}', [AttendanceController::class,'room'])->name('room.attandance');

        Route::get('/day-statics/{day_id?}', [DayController::class,'index'])->name('day.index');
        Route::post('/day/get/users/{room_id?}/{day_id?}', [DayController::class, 'getDaysUsers'])
        ->name('day.get.users');
        Route::get('day-open', [DayController::class,'Open'])->name('day.open');
        Route::get('day-close', [DayController::class,'Close'])->name('day.close');

        Route::get('day-qualified/{minHours?}', [DayController::class,'qualified'])->name('day.qualified');
        Route::post('/day-qualified/get/users', [DayController::class, 'getUsersWithMinHours'])
        ->name('day.get.qualified-users');


    });
});



Route::group(['namespace'=>'Admin','prefix'=>'admin','middleware'=>'guest:admin'],function(){
    Route::get('login',[LoginController::class,'show_login_view'])->name('admin.showlogin');
    Route::post('login',[LoginController::class,'login'])->name('admin.login');

});







