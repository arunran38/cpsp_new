<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PetitionController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\SeatUserController;
use App\Http\Controllers\DecisionController;
use App\Http\Controllers\PetitionForwardingController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('user.dashboard');
    }
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/user/dashboard', function () {
        return view('user.dashboard');
    })->name('user.dashboard');

    Route::resource("users", UserController::class);
    Route::patch('/users/{id}/status', [UserController::class, 'updateStatus'])->name('users.updateStatus');
    // Unified Reports and Export
    Route::get('/petitions/reports', [PetitionController::class, 'reports'])->name('petitions.reports');
    Route::get('/petitions/export', [PetitionController::class, 'export'])->name('petitions.export');

    Route::resource("petitions", PetitionController::class);

    // Petition Workflow Routes
    Route::post('/forwardings', [PetitionForwardingController::class, 'store'])->name('forwardings.store');
    Route::put('/forwardings/{id}/vr', [PetitionForwardingController::class, 'updateVr'])->name('forwardings.updateVr');
    Route::patch('/forwardings/{id}/receive-vr', [PetitionForwardingController::class, 'receiveVr'])->name('forwardings.receiveVr');
    
    Route::post('/decisions', [DecisionController::class, 'store'])->name('decisions.store');

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
    Route::get('/seats/{id}/history', [SeatController::class, 'history'])->name('admin.seats.history');
    Route::post('/seats/{id}/revoke', [SeatController::class, 'revokeAssignment'])->name('admin.seats.revoke');

    Route::resource("seatuser", SeatUserController::class)->names([
        'index' => 'admin.seatuser.index',
        'create' => 'admin.seatuser.create',
        'store' => 'admin.seatuser.store',
        'destroy' => 'admin.seatuser.destroy',
    ]);

    Route::get('/admin/components-showcase', function () {
        return view('admin.components_showcase');
    })->name('admin.components-showcase');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
