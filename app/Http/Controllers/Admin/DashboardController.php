<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Enums\TicketStatus;
use App\Enums\OrderStatus;

/**
 * AdminDashboardController
 * 
 * Admin dashboard - istatistikler ve sistem özeti
 */
class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Admin Dashboard
     * 
     * GET /admin/dashboard
     */
    public function index()
    {
        $stats = [
            'total_events' => Event::count(),
            'total_orders' => Order::count(),
            'total_tickets' => Ticket::count(),
            'checked_in_tickets' => Ticket::where('status', TicketStatus::CHECKED_IN)->count(),
            'paid_orders' => Order::where('status', OrderStatus::PAID)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
