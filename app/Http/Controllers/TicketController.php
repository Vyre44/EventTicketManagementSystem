<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// TicketController: Bilet satın alma ve check-in işlemlerini yöneten controller.
// - Satın alma işlemi transaction ve stok kilitleme ile güvenli.
// - Check-in işlemi admin ve organizer yetki kontrolü ile güvenli.
class TicketController extends Controller
{
    // Bilet satın alma işlemi.
    // - Kullanıcı, etkinlik ve bilet tipi seçerek bilet satın alır.
    // - Stok yarışını engellemek için lockForUpdate ile satır kilitlenir.
    // - TicketType'ın event_id'si ile order'ın event_id'si eşleşir.
    // - Stok yetersizse 409 döner, işlem rollback olur.
    // - Order önce pending, sonra paid yapılır. Ticket'lar sadece ödeme başarılıysa oluşturulur.
    // - Tüm işlemler transaction içinde, hata olursa hiçbir kayıt değişmez.
    public function purchase(Request $request)
    {
        $request->validate([
            'ticket_type_id' => 'required|exists:ticket_types,id',
            'quantity' => 'required|integer|min:1',
            'event_id' => 'required|exists:events,id',
        ]);

        $user = Auth::user();
        $ticketTypeId = $request->ticket_type_id;
        $quantity = $request->quantity;
        $eventId = $request->event_id;

        return DB::transaction(function () use ($user, $ticketTypeId, $quantity, $eventId) {
            // TicketType'ı lockForUpdate ile kilitle
            $ticketType = TicketType::where('id', $ticketTypeId)->lockForUpdate()->firstOrFail();

            // TicketType'ın event_id'si ile order'ın event_id'si eşleşmeli
            if ($ticketType->event_id != $eventId) {
                abort(403, 'TicketType event uyuşmazlığı!');
            }

            // Stok kontrolü
            if ($ticketType->remaining_quantity < $quantity) {
                abort(409, 'Yeterli bilet yok!');
            }

            // Order önce pending oluştur
            $order = Order::create([
                'user_id' => $user->id,
                'event_id' => $eventId,
                'total_amount' => $ticketType->price * $quantity,
                'status' => 'pending',
                'paid_at' => null,
            ]);

            // Ödeme başarılı simülasyonu (gerçek ödeme entegrasyonunda burası değişir)
            $order->update([
                'status' => 'paid',
                'paid_at' => Carbon::now(),
            ]);

            // Ticket'ları oluştur (her biri unique code ile)
            for ($i = 0; $i < $quantity; $i++) {
                Ticket::create([
                    'order_id' => $order->id,
                    'ticket_type_id' => $ticketType->id,
                    'code' => uniqid('TCKT'),
                    'status' => 'active',
                ]);
            }

            // Stok güncelle
            $ticketType->decrement('remaining_quantity', $quantity);

            return response()->json(['success' => true, 'order_id' => $order->id]);
        });
    }

    // Bilet check-in işlemi.
    // - Ticket code ile bilet bulunur.
    // - Organizer sadece kendi etkinliğinin biletini doğrulayabilir, admin tüm biletlerde yetkilidir.
    // - Bilet zaten check-in olduysa hata döner.
    // - Başarılıysa status 'checked_in' ve checked_in_at güncellenir.
    public function checkIn(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = Auth::user();
        $ticket = Ticket::where('code', $request->code)->firstOrFail();
        $event = $ticket->ticketType->event;

        // Admin ise bypass, organizer ise sadece kendi etkinliğinde yetkili
        if ($user->role !== 'admin') {
            if ($event->organizer_id !== $user->id) {
                abort(403, 'Bu etkinliğe yetkiniz yok!');
            }
        }

        if ($ticket->status === 'checked_in') {
            return response()->json(['error' => 'Bilet zaten check-in yapıldı!'], 400);
        }

        $ticket->status = 'checked_in';
        $ticket->checked_in_at = Carbon::now();
        $ticket->save();

        return response()->json(['success' => true, 'ticket_id' => $ticket->id]);
    }
}
