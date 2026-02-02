<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Health check route
Route::get('/health', function () {
    return response()->json(['status' => 'ok'], 200);
});



// Admin route group
Route::prefix('admin')->name('admin.')->middleware(['auth','role:admin'])->group(function () {
    Route::resource('events', \App\Http\Controllers\Admin\EventController::class);
    Route::resource('users', UserController::class);
    Route::resource('tickets', \App\Http\Controllers\Admin\TicketController::class);
    Route::get('orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    // Diğer admin resource route'ları (ticketTypes, orders) burada eklenebilir
});

use App\Enums\UserRole;

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    $user = auth()->user();
    $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
    if ($role === UserRole::ADMIN->value) {
        return redirect()->route('admin.events.index');
    } elseif ($role === UserRole::ORGANIZER->value) {
        return redirect()->route('organizer.events.index');
    } else {
        return view('welcome');
    }
})->name('home');

// Organizer route group
Route::prefix('organizer')->name('organizer.')->middleware(['auth','role:admin,organizer'])->group(function () {
    Route::resource('events', \App\Http\Controllers\Organizer\EventController::class);
    // Check-in route'ları (event.owner middleware ile)
    Route::middleware('event.owner')->group(function () {
        Route::get('events/{event}/checkin', [\App\Http\Controllers\Organizer\CheckInController::class, 'showForm'])->name('events.checkin.form');
        Route::post('events/{event}/checkin', [\App\Http\Controllers\Organizer\CheckInController::class, 'check'])->name('events.checkin.check');
    });
});

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password reset
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
