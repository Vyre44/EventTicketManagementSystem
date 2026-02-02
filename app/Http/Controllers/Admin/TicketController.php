<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTicketRequest;
use App\Http\Requests\Admin\UpdateTicketRequest;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\Order;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['order.user', 'ticketType.event']);

        // Filtre: code/ticket_id ara
        if ($q = $request->input('q')) {
            $query->where('code', 'like', "%$q%")
                  ->orWhere('id', $q);
        }

        // Filtre: status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filtre: event_id
        if ($request->filled('event_id')) {
            $query->whereHas('ticketType', function($q) {
                $q->where('event_id', request('event_id'));
            });
        }

        // Filtre: user email
        if ($request->filled('user_email')) {
            $query->whereHas('order.user', function($q) {
                $q->where('email', 'like', '%' . request('user_email') . '%');
            });
        }

        $tickets = $query->latest()->paginate(20)->withQueryString();
        $statuses = TicketStatus::cases();

        return view('admin.tickets.index', compact('tickets', 'statuses'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['order.user', 'ticketType.event']);
        return view('admin.tickets.show', compact('ticket'));
    }

    public function create()
    {
        $ticketTypes = TicketType::with('event')->get();
        $orders = Order::with('user', 'event')->get();
        $statuses = TicketStatus::cases();
        return view('admin.tickets.create', compact('ticketTypes', 'orders', 'statuses'));
    }

    public function store(StoreTicketRequest $request)
    {
        $data = $request->validated();
        $ticketType = TicketType::findOrFail($data['ticket_type_id']);

        // Quota kontrol
        if ($ticketType->quota <= 0) {
            return back()->withErrors(['ticket_type_id' => 'Kota tamamen doldurulmuş!'])->withInput();
        }

        // Order yok ise yarat
        if (empty($data['order_id'])) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'event_id' => $ticketType->event_id,
                'total_amount' => 0,
                'status' => \App\Enums\OrderStatus::PAID->value,
            ]);
            $data['order_id'] = $order->id;
        }

        Ticket::create($data);
        $ticketType->decrement('quota');

        return redirect()->route('admin.tickets.index')->with('success', 'Bilet oluşturuldu.');
    }

    public function edit(Ticket $ticket)
    {
        $ticket->load(['ticketType', 'order']);
        $ticketTypes = TicketType::with('event')->get();
        $statuses = TicketStatus::cases();
        return view('admin.tickets.edit', compact('ticket', 'ticketTypes', 'statuses'));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $data = $request->validated();

        // Status değişimi: CHECKED_IN ise checked_in_at set
        if (!empty($data['status']) && $data['status'] === TicketStatus::CHECKED_IN->value) {
            $data['checked_in_at'] = now();
        } elseif (!empty($data['status']) && $data['status'] !== TicketStatus::CHECKED_IN->value) {
            $data['checked_in_at'] = null;
        }

        $ticket->update($data);

        return redirect()->route('admin.tickets.show', $ticket)->with('success', 'Bilet güncellendi.');
    }

    public function destroy(Ticket $ticket)
    {
        $ticketType = $ticket->ticketType;
        
        // Status CANCELLED olarak işaretle
        $ticket->update(['status' => TicketStatus::CANCELLED->value]);

        // Quota iade et
        $ticketType->increment('quota');

        return redirect()->route('admin.tickets.index')->with('success', 'Bilet iptal edildi.');
    }
}
