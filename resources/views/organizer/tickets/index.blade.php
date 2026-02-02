@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Biletler</h1>
        <p class="text-gray-600 mt-1">
            @if(auth()->user()->isAdmin())
                Tüm biletleri yönetin
            @else
                Kendi event'lerinizin biletlerini görüntüleyin ve yönetin
            @endif
        </p>
    </div>

    @if($tickets->isEmpty())
        <div class="bg-white border rounded-lg p-8 text-center">
            <p class="text-gray-600">Henüz bilet bulunmamaktadır.</p>
        </div>
    @else
        <div class="bg-white border rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Bilet Kodu</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Etkinlik</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Bilet Tipi</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Müşteri</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono">{{ $ticket->code }}</td>
                            <td class="px-6 py-4 text-sm font-medium">{{ $ticket->ticketType->event->title }}</td>
                            <td class="px-6 py-4 text-sm">{{ $ticket->ticketType->name }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($ticket->order)
                                    <div>{{ $ticket->order->user->name }}</div>
                                    <div class="text-gray-600 text-xs">{{ $ticket->order->user->email }}</div>
                                @else
                                    <span class="text-gray-500 text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($ticket->status->value === 'active')
                                    <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        Aktif
                                    </span>
                                @elseif($ticket->status->value === 'checked_in')
                                    <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        ✅ Kullanıldı
                                    </span>
                                @elseif($ticket->status->value === 'cancelled')
                                    <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        ❌ İptal
                                    </span>
                                @elseif($ticket->status->value === 'refunded')
                                    <span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold">
                                        🔄 İade
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('organizer.tickets.show', $ticket) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Detay →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection
