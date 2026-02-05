@extends('attendee.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">🎪 Yaklaşan Etkinlikler</h1>
        <p class="text-gray-600">Katılmak istediğiniz etkinliği bulun ve biletinizi satın alın.</p>
    </div>

    <!-- Arama Formu -->
    <div class="mb-8">
        <form method="GET" action="{{ route('attendee.events.index') }}" class="flex gap-2 flex-col md:flex-row">
            <input 
                type="text" 
                name="q" 
                value="{{ request('q') }}" 
                placeholder="Etkinlik adı ara..." 
                class="flex-1 border rounded-lg px-4 py-3 text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                🔍 Ara
            </button>
            @if(request('q'))
                <a href="{{ route('attendee.events.index') }}" class="bg-gray-300 text-gray-900 px-6 py-3 rounded-lg font-semibold hover:bg-gray-400 transition text-center">
                    ✕ Temizle
                </a>
            @endif
        </form>
    </div>

    <!-- Events Grid veya Empty State -->
    @if($events->isEmpty())
        <div class="text-center py-16">
            <div class="text-6xl mb-4">🎪</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                {{ request('q') ? 'Etkinlik bulunamadı' : 'Henüz aktif etkinlik yok' }}
            </h2>
            <p class="text-gray-600 mb-6">
                {{ request('q') ? 'Farklı bir arama deneyin.' : 'Yakında daha fazla etkinlik eklenecektir.' }}
            </p>
            @if(request('q'))
                <a href="{{ route('attendee.events.index') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    Tüm Etkinlikleri Gör
                </a>
            @endif
        </div>
    @else
        <!-- Event Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($events as $event)
                <x-attendee.event-card :event="$event" />
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-12 flex justify-center">
            {{ $events->links() }}
        </div>
    @endif
</div>
@endsection
