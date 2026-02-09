<?php

namespace App\Http\Controllers\Organizer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

/**
 * OrderController (Organizer)
 *
 * Organizer kendi event'lerine ait orders'ları görebilir.
 * Admin tüm orders'ları görebilir.
 */
class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,organizer']);
    }

    /**
     * Organizer/Admin için orders listesi
     *
     * GET /organizer/orders
     */
    public function index()
    {
        $user = auth()->user();

        $query = Order::with(['user:id,name,email', 'event:id,title,organizer_id'])
            ->when(!$user->isAdmin(), function (Builder $query) use ($user) {
                $query->whereHas('event', function (Builder $subquery) use ($user) {
                    $subquery->where('organizer_id', $user->id);
                });
            });

        // Filter by status
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        // Filter by search (order ID or user email)
        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function (Builder $q) use ($search) {
                $q->where('id', $search)
                  ->orWhereHas('user', function (Builder $subq) use ($search) {
                      $subq->where('email', 'like', "%$search%");
                  });
            });
        }

        $orders = $query
            ->withCount('tickets')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statuses = OrderStatus::cases();

        return view('organizer.orders.index', compact('orders', 'statuses'));
    }

    /**
     * Organizer/Admin için order detayı
     *
     * GET /organizer/orders/{order}
     */
    public function show(Order $order)
    {
        $user = auth()->user();

        // Ownership kontrolü (Admin bypass yapabilir)
        if (!$user->isAdmin() && $order->event->organizer_id !== $user->id) {
            abort(403, 'Bu sipariş bilgisini görüntüleme yetkiniz yok.');
        }

        $order->load(['user:id,name,email', 'event:id,title,organizer_id', 'tickets.ticketType']);

        return view('organizer.orders.show', compact('order'));
    }
}
