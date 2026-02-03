<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Route;

// Health check route
Route::get('/health', function () {
    return response()->json(['status' => 'ok'], 200);
});

// Admin route group
Route::prefix('admin')->name('admin.')->middleware(['auth','role:admin'])->group(function () {
    Route::resource('events', \App\Http\Controllers\Admin\EventController::class);
    Route::resource('users', UserController::class);
    Route::resource('ticket-types', \App\Http\Controllers\Admin\TicketTypeController::class);
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
    Route::resource('orders', \App\Http\Controllers\Organizer\OrderController::class, ['only' => ['index', 'show']]);
    Route::resource('tickets', \App\Http\Controllers\Organizer\TicketController::class, ['only' => ['index', 'show']]);
    Route::post('tickets/{ticket}/checkin', [\App\Http\Controllers\Organizer\TicketController::class, 'checkin'])->name('tickets.checkin');
    Route::post('tickets/{ticket}/checkin-undo', [\App\Http\Controllers\Organizer\TicketController::class, 'checkinUndo'])->name('tickets.checkinUndo');
    Route::post('tickets/{ticket}/cancel', [\App\Http\Controllers\Organizer\TicketController::class, 'cancel'])->name('tickets.cancel');
    
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

// Profile & verification routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/email/verification-notification', function (Request $request) {
        $user = $request->user();
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            return back()->with('status', 'verification-link-sent');
        }

        return back();
    })->name('verification.send');
});

// Attendee Routes (auth + role:attendee required for ALL)
Route::middleware(['auth', 'role:attendee'])->prefix('')->name('attendee.')->group(function () {
    // Events (public-like but auth required)
    Route::get('/events', [\App\Http\Controllers\Attendee\EventController::class, 'index'])->name('events.index');
    Route::get('/events/{event}', [\App\Http\Controllers\Attendee\EventController::class, 'show'])->name('events.show');
    
    // Purchase Flow (Model 1A)
    Route::post('/events/{event}/buy', [\App\Http\Controllers\Attendee\OrderController::class, 'buy'])->name('events.buy');
    Route::post('/orders/{order}/pay', [\App\Http\Controllers\Attendee\OrderController::class, 'pay'])->name('orders.pay');
    
    // Orders
    Route::get('/orders', [\App\Http\Controllers\Attendee\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Attendee\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [\App\Http\Controllers\Attendee\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/refund', [\App\Http\Controllers\Attendee\OrderController::class, 'refund'])->name('orders.refund');
});
