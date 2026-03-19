<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PetitionController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\SeatUserController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/user/dashboard', function () {
    return view('user.dashboard');
})->name('user.dashboard');

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');



Route::resource("users", UserController::class);

Route::resource("petitions", PetitionController::class);

Route::resource("units", UnitController::class)->names([
    'index' => 'admin.units.index',
    'create' => 'admin.units.create',
    'store' => 'admin.units.store',
    'edit' => 'admin.units.edit',
    'update' => 'admin.units.update',
    'destroy' => 'admin.units.destroy',
]);

Route::resource("seats", SeatController::class)->names([
    'index' => 'admin.seats.index',
    'create' => 'admin.seats.create',
    'store' => 'admin.seats.store',
    'edit' => 'admin.seats.edit',
    'update' => 'admin.seats.update',
    'destroy' => 'admin.seats.destroy',
]);

Route::resource("seatuser", SeatUserController::class)->names([
    'index' => 'admin.seatuser.index',
    'create' => 'admin.seatuser.create',
    'store' => 'admin.seatuser.store',
    'destroy' => 'admin.seatuser.destroy',
]);


Route::get('/admin/components-showcase', function () {
    return view('admin.components_showcase');
})->name('admin.components-showcase');
