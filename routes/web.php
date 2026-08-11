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
use App\Http\Controllers\TrashController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user->role === 'admin' && !session('is_impersonating_seat')) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('user.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
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

    // Custom Petition Routes (MUST be before resource route)
    Route::post('/petitions/check-petition-no', [PetitionController::class, 'checkPetitionNo'])->name('petitions.checkPetitionNo');
    Route::post('/petitions/check-duplicates', [PetitionController::class, 'checkDuplicates'])->name('petitions.checkDuplicates');
    Route::get('/petitions/search-duplicates', [PetitionController::class, 'searchDuplicates'])->name('petitions.searchDuplicates');
    Route::post('/petitions/{id}/link-duplicate', [PetitionController::class, 'linkDuplicate'])->name('petitions.linkDuplicate');
    Route::post('/petitions/{id}/unlink-duplicate', [PetitionController::class, 'unlinkDuplicate'])->name('petitions.unlinkDuplicate');
    Route::get('/petitions/download/{upload_id}', [PetitionController::class, 'downloadAttachment'])->name('petitions.download');

    Route::resource("petitions", PetitionController::class);

    // Petition Workflow Routes
    Route::post('/forwardings', [PetitionForwardingController::class, 'store'])->name('forwardings.store');
    Route::put('/forwardings/{id}/vr', [PetitionForwardingController::class, 'updateVr'])->name('forwardings.updateVr');
    Route::patch('/forwardings/{id}/receive-vr', [PetitionForwardingController::class, 'receiveVr'])->name('forwardings.receiveVr');
    
    Route::post('/decisions', [DecisionController::class, 'store'])->name('decisions.store');

    // Pull Back Routes
    Route::delete('/forwardings/{id}/pullback', [PetitionForwardingController::class, 'pullbackForwarding'])->name('forwardings.pullback');
    Route::patch('/forwardings/{id}/pullback-vr', [PetitionForwardingController::class, 'pullbackVr'])->name('forwardings.pullbackVr');
    Route::delete('/decisions/{id}/pullback', [DecisionController::class, 'pullbackDecision'])->name('decisions.pullback');

    Route::resource("units", UnitController::class)->names([
        'index' => 'admin.units.index',
        'create' => 'admin.units.create',
        'store' => 'admin.units.store',
        'edit' => 'admin.units.edit',
        'update' => 'admin.units.update',
        'destroy' => 'admin.units.destroy',
    ]);
    Route::post('/admin/units/check-code', [UnitController::class, 'checkUnitCode'])->name('admin.units.checkCode');
    Route::post('/users/check-unique', [UserController::class, 'checkUnique'])->name('users.checkUnique');

    Route::get('/seats/statistics', [SeatController::class, 'statistics'])->name('admin.seats.statistics');
    Route::get('/seats/statistics/export', [SeatController::class, 'exportStatistics'])->name('admin.seats.statistics.export');

    Route::resource("seats", SeatController::class)->names([
        'index' => 'admin.seats.index',
        'create' => 'admin.seats.create',
        'store' => 'admin.seats.store',
        'edit' => 'admin.seats.edit',
        'update' => 'admin.seats.update',
        'destroy' => 'admin.seats.destroy',
    ]);
    Route::post('/admin/seats/check-name', [SeatController::class, 'checkSeatName'])->name('admin.seats.checkName');
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

    // Trash bin routes
    Route::get('/admin/trash', [TrashController::class, 'index'])->name('admin.trash.index');
    Route::post('/admin/trash/{id}/restore', [TrashController::class, 'restore'])->name('admin.trash.restore');
    Route::delete('/admin/trash/{id}/force', [TrashController::class, 'forceDelete'])->name('admin.trash.forceDelete');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/password', [ProfileController::class, 'passwordEdit'])->name('profile.password.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Seat Switcher
    Route::post('/switch-seat/{seatId}', [SeatController::class, 'switchSeat'])->name('seat.switch');
    Route::post('/switch-back-admin', [SeatController::class, 'switchBack'])->name('seat.switchBack');
});

require __DIR__.'/auth.php';
