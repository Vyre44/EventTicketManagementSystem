@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Etkinlikler</h1>
        
        <!-- Arama Formu -->
        <form method="GET" action="{{ route('attendee.events.index') }}" class="flex gap-2">
            <input 
                type="text" 
                name="q" 
                value="{{ request('q') }}" 
                placeholder="Etkinlik ara..." 
                class="border rounded px-4 py-2 w-64"
            >
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Ara
            </button>
            @if(request('q'))
                <a href="{{ route('attendee.events.index') }}" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
                    Temizle
                </a>
            @endif
        </form>
    </div>

    @if($events->isEmpty())
        <div class="text-center py-12 text-gray-500">
            <p class="text-xl">{{ request('q') ? 'Aramanıza uygun etkinlik bulunamadı.' : 'Henüz yayınlanmış etkinlik bulunmuyor.' }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $event)
                <div class="border rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <div class="p-6">
                        <h2 class="text-xl font-bold mb-2">
                            <a href="{{ route('attendee.events.show', $event) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $event->title }}
                            </a>
                        </h2>
                        
                        <div class="text-sm text-gray-600 mb-4 space-y-1">
                            <div>📅 {{ $event->start_time->format('d.m.Y H:i') }}</div>
                        </div>

                        @if($event->description)
                            <p class="text-gray-700 mb-4 line-clamp-3">
                                {{ Str::limit($event->description, 120) }}
                            </p>
                        @endif

                        @if($event->ticketTypes->isNotEmpty())
                            <div class="text-sm text-gray-600 mb-4">
                                <strong>Bilet Fiyatları:</strong> 
                                {{ number_format($event->ticketTypes->min('price'), 2) }} ₺ 
                                - 
                                {{ number_format($event->ticketTypes->max('price'), 2) }} ₺
                            </div>
                        @endif

                        <a 
                            href="{{ route('attendee.events.show', $event) }}" 
                            class="block text-center bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        >
                            Detayları Gör
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $events->links() }}
        </div>
    @endif
</div>
@endsection
