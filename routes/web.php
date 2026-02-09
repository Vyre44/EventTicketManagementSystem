<?php

/**
 * Web Routes - Role-Based Architecture
 * 
 * Route grupları rol bazlı RoleMiddleware ve CheckEventOwner ile korunur.
 * Admin prefix'i tüm sistemi yönetir, organizer kendi events'lerini.
 * Resource routes otomatik CRUD işlemleri sağlar.
 */

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Route;

/**
 * Health Check - Docker ve Kubernetes için endpoint
 */
Route::get('/health', function () {
    return response()->json(['status' => 'ok'], 200);
});

/**
 * Admin Panel - RoleMiddleware: 'admin'
 * 
 * Resource routes: events, users, ticket-types, tickets
 * AJAX endpoints: checkin, checkinUndo, cancelTicket (JSON response)
 * Reports: CSV export ile bilet raporları
 */

Route::prefix('admin')->name('admin.')->middleware(['auth','role:admin'])->group(function () {
    // Dashboard - Aggregate Statistics
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Resource Controllers (RESTful CRUD)
    Route::resource('events', \App\Http\Controllers\Admin\EventController::class);
    Route::resource('users', UserController::class);
    Route::resource('ticket-types', \App\Http\Controllers\Admin\TicketTypeController::class);
    Route::resource('tickets', \App\Http\Controllers\Admin\TicketController::class);
    
    // Order Views (index, show only)
    Route::get('orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');

    // AJAX Ticket Operations - JSON Response
    Route::post('tickets/{ticket}/checkin', [\App\Http\Controllers\Admin\TicketController::class, 'checkin'])->name('tickets.checkin');
    Route::post('tickets/{ticket}/checkin-undo', [\App\Http\Controllers\Admin\TicketController::class, 'checkinUndo'])->name('tickets.checkinUndo');
    Route::post('tickets/{ticket}/cancel-ticket', [\App\Http\Controllers\Admin\TicketController::class, 'cancelTicket'])->name('tickets.cancelTicket');

    // Admin Check-in (SÜPER YETKİ - tüm events için)
    Route::get('events/{event}/checkin', [\App\Http\Controllers\Admin\CheckInController::class, 'showForm'])->name('events.checkin.form');
    Route::post('events/{event}/checkin', [\App\Http\Controllers\Admin\CheckInController::class, 'check'])->name('events.checkin.check');

    // Reports - CSV Export
    Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/event-sales', [\App\Http\Controllers\Admin\ReportController::class, 'eventSales'])->name('reports.event-sales');
    Route::get('reports/event-sales/data', [\App\Http\Controllers\Admin\ReportController::class, 'eventSalesData'])->name('reports.event-sales.data');
    Route::get('reports/events/{event}/tickets', [\App\Http\Controllers\Admin\ReportController::class, 'eventTickets'])->name('reports.events.tickets');
    Route::get('reports/events/{event}/tickets/export', [\App\Http\Controllers\Admin\ReportController::class, 'exportEventTickets'])->name('reports.events.tickets.export');
});


use App\Enums\UserRole;

/**
 * Home - Role-Based Redirect
 * Admin: /admin/dashboard
 * Organizer: /organizer/events
 * Attendee: /events
 * Guest: /login
 */
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    $user = auth()->user();
    $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
    if ($role === UserRole::ADMIN->value) {
        return redirect()->route('admin.dashboard');
    } elseif ($role === UserRole::ORGANIZER->value) {
        return redirect()->route('organizer.events.index');
    } else {
        return redirect()->route('attendee.events.index');
    }
})->name('home');


/**
 * Organizer Panel - RoleMiddleware: 'admin,organizer'
 * 
 * Admin: Tüm events'leri yönetir
 * Organizer: Sadece owner olduğu events'leri (CheckEventOwner middleware)
 * Resource routes: events, orders (index,show), tickets (index,show)
 * AJAX: checkin, checkinUndo, cancel
 */

Route::prefix('organizer')->name('organizer.')->middleware(['auth','role:admin,organizer'])->group(function () {
    // Resource Routes - index, create, store için sahiplik kontrolü gereksiz
    Route::get('events', [\App\Http\Controllers\Organizer\EventController::class, 'index'])->name('events.index');
    Route::get('events/create', [\App\Http\Controllers\Organizer\EventController::class, 'create'])->name('events.create');
    Route::post('events', [\App\Http\Controllers\Organizer\EventController::class, 'store'])->name('events.store');
    
    // Resource Routes - show, edit, update, destroy için sahiplik kontrolü gerekli
    Route::middleware('event.owner')->group(function () {
        Route::get('events/{event}', [\App\Http\Controllers\Organizer\EventController::class, 'show'])->name('events.show');
        Route::get('events/{event}/edit', [\App\Http\Controllers\Organizer\EventController::class, 'edit'])->name('events.edit');
        Route::put('events/{event}', [\App\Http\Controllers\Organizer\EventController::class, 'update'])->name('events.update');
        Route::delete('events/{event}', [\App\Http\Controllers\Organizer\EventController::class, 'destroy'])->name('events.destroy');
    });
    
    Route::resource('orders', \App\Http\Controllers\Organizer\OrderController::class, ['only' => ['index', 'show']]);
    Route::resource('tickets', \App\Http\Controllers\Organizer\TicketController::class, ['only' => ['index', 'show']]);

    // Organizer Ticket Type Management (Event scoped - ownership verification)
    Route::middleware('event.owner')->group(function () {
        Route::get('events/{event}/ticket-types', [\App\Http\Controllers\Organizer\TicketTypeController::class, 'index'])->name('events.ticket-types.index');
        Route::get('events/{event}/ticket-types/create', [\App\Http\Controllers\Organizer\TicketTypeController::class, 'create'])->name('events.ticket-types.create');
        Route::post('events/{event}/ticket-types', [\App\Http\Controllers\Organizer\TicketTypeController::class, 'store'])->name('events.ticket-types.store');
        Route::get('events/{event}/ticket-types/{ticketType}/edit', [\App\Http\Controllers\Organizer\TicketTypeController::class, 'edit'])->name('events.ticket-types.edit');
        Route::put('events/{event}/ticket-types/{ticketType}', [\App\Http\Controllers\Organizer\TicketTypeController::class, 'update'])->name('events.ticket-types.update');
        Route::delete('events/{event}/ticket-types/{ticketType}', [\App\Http\Controllers\Organizer\TicketTypeController::class, 'destroy'])->name('events.ticket-types.destroy');
    });
    
    // AJAX Ticket Operations
    Route::post('tickets/{ticket}/checkin', [\App\Http\Controllers\Organizer\TicketController::class, 'checkin'])->name('tickets.checkin');
    Route::post('tickets/{ticket}/checkin-undo', [\App\Http\Controllers\Organizer\TicketController::class, 'checkinUndo'])->name('tickets.checkinUndo');
    Route::post('tickets/{ticket}/cancel', [\App\Http\Controllers\Organizer\TicketController::class, 'cancel'])->name('tickets.cancel');
    
    // Check-in Form (event.owner middleware - ownership verification)
    Route::middleware('event.owner')->group(function () {
        Route::get('events/{event}/checkin', [\App\Http\Controllers\Organizer\CheckInController::class, 'showForm'])->name('events.checkin.form');
        Route::post('events/{event}/checkin', [\App\Http\Controllers\Organizer\CheckInController::class, 'check'])->name('events.checkin.check');
    });

    // Reports & CSV Export (event.owner middleware - ownership verification)
    Route::middleware('event.owner')->group(function () {
        Route::get('reports/events/{event}/tickets', [\App\Http\Controllers\Organizer\ReportController::class, 'eventTickets'])->name('reports.events.tickets');
        Route::get('reports/events/{event}/tickets/export', [\App\Http\Controllers\Organizer\ReportController::class, 'exportEventTickets'])->name('reports.events.tickets.export');
    });
});


/**
 * Authentication Routes - Laravel Breeze style
 * Login, Register, Logout
 * Password Reset Flow (forgot -> reset)
 */
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');


/**
 * Profile & Email Verification - Laravel'in built-in contract'ı (MustVerifyEmail)
 */
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


/**
 * Attendee Panel - RoleMiddleware: 'attendee'
 * 
 * Event Discovery: Sadece published events'leri görür
 * Purchase Flow: buy -> pay (Order status: PENDING -> PAID)
 * Order Management: index, show, cancel (PENDING için), refund (PAID için)
 */
Route::middleware(['auth', 'role:attendee'])->prefix('')->name('attendee.')->group(function () {
    // Event Discovery
    Route::get('/events', [\App\Http\Controllers\Attendee\EventController::class, 'index'])->name('events.index');
    Route::get('/events/{event}', [\App\Http\Controllers\Attendee\EventController::class, 'show'])->name('events.show');
    
    // Purchase Flow
    Route::post('/events/{event}/buy', [\App\Http\Controllers\Attendee\OrderController::class, 'buy'])->name('events.buy');
    Route::post('/orders/{order}/pay', [\App\Http\Controllers\Attendee\OrderController::class, 'pay'])->name('orders.pay');
    
    // Order Management
    Route::get('/orders', [\App\Http\Controllers\Attendee\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Attendee\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [\App\Http\Controllers\Attendee\OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('/orders/{order}/refund', [\App\Http\Controllers\Attendee\OrderController::class, 'refund'])->name('orders.refund');
});
