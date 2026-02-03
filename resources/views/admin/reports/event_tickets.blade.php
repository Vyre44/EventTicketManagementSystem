@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.events.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Etkinliklere Dön
        </a>
        <h1 class="text-3xl font-bold">{{ $event->title }} - Bilet Raporu</h1>
        <p class="text-gray-600 mt-1">Etkinliğe ait tüm biletlerin durumu</p>
    </div>

    <!-- Filters -->
    <div class="bg-white border rounded-lg p-6 mb-6">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">Durum</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2">
                    <option value="">Tümü</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="checked_in" {{ request('status') === 'checked_in' ? 'selected' : '' }}>Kullanıldı</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>İptal</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>İade</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Ara (ID/Email)</label>
                <input type="text" name="search" class="w-full border rounded-lg px-3 py-2" placeholder="Bilet ID veya email" value="{{ request('search') }}">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">Sayfa Başına</label>
                <select name="per_page" class="w-full border rounded-lg px-3 py-2">
                    <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
            <div class="flex gap-2 items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex-1">
                    Filtrele
                </button>
                <a href="{{ route('admin.reports.events.tickets.export', [$event->id]) }}?{{ request()->query() }}" 
                   class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                    📥 CSV
                </a>
            </div>
        </form>
    </div>

    <!-- Results -->
    @if($tickets->isEmpty())
        <div class="bg-white border rounded-lg p-8 text-center">
            <p class="text-gray-600">Kriterlere uygun bilet bulunamadı.</p>
        </div>
    @else
        <div class="bg-white border rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Bilet ID</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Bilet Tipi</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Müşteri / Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Durum</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Check-in Zamanı</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Sipariş Durumu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-mono">{{ $ticket->id }}</td>
                            <td class="px-6 py-4 text-sm">{{ $ticket->ticketType->name }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($ticket->order?->user)
                                    <div>{{ $ticket->order->user->name }}</div>
                                    <div class="text-gray-600 text-xs">{{ $ticket->order->user->email }}</div>
                                @else
                                    <span class="text-gray-500">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($ticket->status->value === 'active')
                                    <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">Aktif</span>
                                @elseif($ticket->status->value === 'checked_in')
                                    <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">✅ Kullanıldı</span>
                                @elseif($ticket->status->value === 'cancelled')
                                    <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">❌ İptal</span>
                                @elseif($ticket->status->value === 'refunded')
                                    <span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold">🔄 İade</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($ticket->checked_in_at)
                                    {{ $ticket->checked_in_at->format('d.m.Y H:i') }}
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($ticket->order)
                                    @if($ticket->order->status->value === 'pending')
                                        <span class="text-yellow-600 text-xs font-semibold">⏳ Bekliyor</span>
                                    @elseif($ticket->order->status->value === 'paid')
                                        <span class="text-green-600 text-xs font-semibold">✅ Ödendi</span>
                                    @elseif($ticket->order->status->value === 'cancelled')
                                        <span class="text-red-600 text-xs font-semibold">❌ İptal</span>
                                    @elseif($ticket->order->status->value === 'refunded')
                                        <span class="text-gray-600 text-xs font-semibold">🔄 İade</span>
                                    @endif
                                @else
                                    <span class="text-gray-500 text-xs">N/A</span>
                                @endif
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
@endsection
