<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Admin Sipariş Controller - Salt Okuma Görünümleri
 * 
 * Eager loading: with(['user', 'event']), withCount('tickets')
 * Filtreler: status (enum), q (ID veya kullanıcı e-postası araması)
 * whereHas(): İlişkili tablo filtreleme
 * Sayfalama: withQueryString() ile filtreleri korur
 */
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'event'])
            ->withCount('tickets');

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        // q: order id veya user email araması
        if ($q = $request->input('q')) {
            $query->where(function($q2) use ($q) {
                $q2->where('id', $q)
                   ->orWhereHas('user', function($q3) use ($q) {
                       $q3->where('email', 'like', "%$q%");
                   });
            });
        }
        $orders = $query->latest()->paginate(20)->withQueryString();
        $statuses = OrderStatus::cases();
        return view('admin.orders.index', compact('orders', 'statuses'));
    }
    // Route Model Binding: Order    
    public function show(Order $order)
    {
        $order->load(['user', 'tickets.ticketType.event']);
        return view('admin.orders.show', compact('order'));
    }
}
