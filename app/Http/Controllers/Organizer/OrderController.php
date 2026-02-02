<?php

namespace App\Http\Controllers\Organizer;

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

        // Admin tüm orders'ları görebilir, organizer sadece kendi event'lerininkini
        $orders = Order::with(['user:id,name,email', 'event:id,title,organizer_id'])
            ->when(!$user->isAdmin(), function (Builder $query) use ($user) {
                $query->whereHas('event', function (Builder $subquery) use ($user) {
                    $subquery->where('organizer_id', $user->id);
                });
            })
            ->withCount('tickets')
            ->latest()
            ->paginate(15);

        return view('organizer.orders.index', compact('orders'));
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

        $order->load(['user:id,name,email,phone', 'event:id,title,organizer_id', 'tickets.ticketType']);

        return view('organizer.orders.show', compact('order'));
    }
}
