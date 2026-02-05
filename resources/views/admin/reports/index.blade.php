@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold">📊 Etkinlik Satış Raporları</h1>
        <p class="text-gray-600 mt-2">Etkinlik seçerek detaylı satış ve bilet raporlarını görüntüleyin</p>
    </div>

    <div class="mb-6">
        <a href="{{ route('admin.reports.event-sales') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
            🔎 Etkinlik Bazlı Satış Raporu (AJAX)
        </a>
    </div>

    @if($events->isEmpty())
        <div class="bg-white border rounded-lg p-8 text-center">
            <p class="text-gray-600">Henüz rapor oluşturulacak etkinlik bulunmuyor.</p>
            <a href="{{ route('admin.events.create') }}" class="btn btn-primary mt-4">Yeni Etkinlik Oluştur</a>
        </div>
    @else
        <div class="bg-white border rounded-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Etkinlik Adı</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Organizatör</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Başlangıç</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold">Sipariş Sayısı</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold">Bilet Sayısı</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium">
                                {{ $event->title }}
                                <div class="text-xs text-gray-500 mt-1">
                                    @if($event->status === \App\Enums\EventStatus::PUBLISHED)
                                        <span class="text-green-600">● Yayında</span>
                                    @elseif($event->status === \App\Enums\EventStatus::DRAFT)
                                        <span class="text-yellow-600">● Taslak</span>
                                    @elseif($event->status === \App\Enums\EventStatus::CANCELLED)
                                        <span class="text-red-600">● İptal</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{ $event->organizer->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{ $event->start_time?->format('d.m.Y H:i') ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    {{ $event->orders_count }} sipariş
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span class="inline-block bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    {{ $event->tickets_count }} bilet
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('admin.reports.events.tickets', $event) }}" 
                                   class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition">
                                    📈 Raporu Görüntüle
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
