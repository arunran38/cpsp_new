<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');



Route::resource("users", UserController::class);

Route::get('/admin/components-showcase', function () {
    return view('admin.components_showcase');
})->name('admin.components-showcase');
