<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PetitionController;


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


Route::get('/admin/components-showcase', function () {
    return view('admin.components_showcase');
})->name('admin.components-showcase');
