@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Etkinlikler (Admin)</h1>
        <a href="{{ route('admin.events.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">Yeni Etkinlik</a>
    </div>

    <div class="bg-white shadow rounded">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="p-3">Başlık</th>
                    <th class="p-3">Kapak</th>
                    <th class="p-3">Başlangıç</th>
                    <th class="p-3">Durum</th>
                    <th class="p-3">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                    <tr class="border-b">
                        <td class="p-3">{{ $event->title }}</td>
                        <td class="p-3">
                            @if($event->cover_image_url)
                                <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="w-16 h-16 object-cover rounded">
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-3">{{ $event->start_time?->format('d.m.Y H:i') }}</td>
                        <td class="p-3">{{ $event->status?->value ?? $event->status }}</td>
                        <td class="p-3">
                            <a href="{{ route('admin.events.show', $event) }}" class="text-blue-600">Görüntüle</a>
                            <a href="{{ route('admin.events.edit', $event) }}" class="text-yellow-600 ml-3">Düzenle</a>
                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 ml-3" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $events->links() }}
    </div>
</div>
@endsection
