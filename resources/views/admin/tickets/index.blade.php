@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Alert Container (AJAX) -->
    <div id="ajax-alert-container"></div>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold">Biletler</h1>
            <p class="text-gray-600 mt-1">Tüm biletleri yönetin</p>
        </div>
        <a href="{{ route('admin.tickets.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Yeni Bilet
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white border rounded-lg p-6 mb-6">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Kod veya ID ara" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <select name="status" class="w-full border rounded-lg px-3 py-2">
                    <option value="">Tüm Durumlar</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') == $status->value)>{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="text" name="user_email" value="{{ request('user_email') }}" placeholder="Kullanıcı Email" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <input type="text" name="event_id" value="{{ request('event_id') }}" placeholder="Event ID" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                    Filtrele
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    @if($tickets->isEmpty())
        <div class="bg-white border rounded-lg p-8 text-center">
            <p class="text-gray-600">Bilet bulunamadı.</p>
        </div>
    @else
        <div class="bg-white border rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Bilet ID</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Kod</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Durum</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Bilet Tipi</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Etkinlik</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Kullanıcı</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Check-in</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                        <tr class="border-b hover:bg-gray-50" data-ticket-id="{{ $ticket->id }}" data-ticket-status="{{ $ticket->status->value }}">
                            <td class="px-6 py-4 text-sm font-mono">{{ $ticket->id }}</td>
                            <td class="px-6 py-4 text-sm font-mono">{{ $ticket->code }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="ticket-status-badge">
                                    @if($ticket->status->value === 'active')
                                        <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">Aktif</span>
                                    @elseif($ticket->status->value === 'checked_in')
                                        <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">✅ Kullanıldı</span>
                                    @elseif($ticket->status->value === 'cancelled')
                                        <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">❌ İptal</span>
                                    @elseif($ticket->status->value === 'refunded')
                                        <span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold">🔄 İade</span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">{{ $ticket->ticketType->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $ticket->ticketType->event->title ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $ticket->order->user->email ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($ticket->checked_in_at)
                                    {{ $ticket->checked_in_at->format('d.m.Y H:i') }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex gap-2 justify-center items-center ticket-actions">
                                    @if($ticket->status->value === 'active')
                                        <button class="ticket-action-btn text-green-600 hover:text-green-800 text-sm font-medium" data-action="checkin" title="Check-in">
                                            ✅
                                        </button>
                                        <button class="ticket-action-btn text-red-600 hover:text-red-800 text-sm font-medium" data-action="cancel" title="İptal">
                                            ❌
                                        </button>
                                    @elseif($ticket->status->value === 'checked_in')
                                        <button class="ticket-action-btn text-orange-600 hover:text-orange-800 text-sm font-medium" data-action="undo" title="Geri Al">
                                            ↩️
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-sm">-</span>
                                    @endif
                                    <a href="{{ route('admin.tickets.show', $ticket) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Detay
                                    </a>
                                    <a href="{{ route('admin.tickets.edit', $ticket) }}" class="text-yellow-600 hover:text-yellow-800 text-sm font-medium">
                                        Düzenle
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $tickets->render() }}
        </div>
    @endif
</div>

<script>
    // Routes mapping for admin
    const routeNameMap = {
        'checkin': '{{ route("admin.tickets.checkin", ["ticket" => "__TICKET_ID__"]) }}',
        'undo': '{{ route("admin.tickets.checkinUndo", ["ticket" => "__TICKET_ID__"]) }}',
        'cancel': '{{ route("admin.tickets.cancelTicket", ["ticket" => "__TICKET_ID__"]) }}'
    };
</script>
@endsection
