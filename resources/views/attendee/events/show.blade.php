@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Etkinlik Başlığı -->
    <div class="mb-6">
        <a href="{{ route('attendee.events.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Tüm Etkinlikler
        </a>
        <h1 class="text-4xl font-bold mb-2">{{ $event->title }}</h1>
        
        <div class="text-gray-600 space-y-1">
            <div class="flex items-center gap-2">
                <span>📅</span>
                <span>{{ $event->start_time->format('d.m.Y H:i') }}
                    @if($event->end_time)
                        - {{ $event->end_time->format('d.m.Y H:i') }}
                    @endif
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span>👤</span>
                <span>Organizatör: {{ $event->organizer->name }}</span>
            </div>
        </div>
    </div>

    <!-- Etkinlik Açıklaması -->
    @if($event->description)
        <div class="bg-gray-50 p-6 rounded-lg mb-8">
            <h2 class="text-xl font-bold mb-3">Açıklama</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $event->description }}</p>
        </div>
    @endif

    <!-- Bilet Tipleri -->
    <div class="bg-white border rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Bilet Tipleri</h2>

        @if($event->ticketTypes->isEmpty())
            <p class="text-gray-500">Bu etkinlik için henüz bilet tipi eklenmemiş.</p>
        @else
            @auth
                <form method="POST" action="{{ route('attendee.events.buy', $event) }}">
                    @csrf
                    <div class="space-y-4">
                        @foreach($event->ticketTypes as $ticketType)
                            <div class="border rounded-lg p-4 flex justify-between items-center">
                                <div class="flex-1">
                                    <h3 class="font-bold text-lg">{{ $ticketType->name }}</h3>
                                    @if($ticketType->description)
                                        <p class="text-gray-600 text-sm">{{ $ticketType->description }}</p>
                                    @endif
                                    <div class="text-gray-600 text-sm mt-1">
                                        <span class="font-semibold">{{ number_format($ticketType->price, 2) }} ₺</span>
                                        <span class="ml-3">Mevcut Kota: {{ $ticketType->remaining_quantity }}</span>
                                    </div>
                                </div>

                                <div class="ml-4">
                                    @if($ticketType->remaining_quantity > 0)
                                        <input 
                                            type="number" 
                                            name="ticket_types[{{ $ticketType->id }}]" 
                                            min="0" 
                                            max="{{ min($ticketType->remaining_quantity, 10) }}"
                                            value="0"
                                            class="border rounded px-3 py-2 w-20 text-center"
                                            placeholder="0"
                                        >
                                    @else
                                        <span class="text-red-600 font-semibold">TÜKENDİ</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @error('ticket_types')
                        <div class="text-red-600 mt-2">{{ $message }}</div>
                    @enderror

                    <div class="mt-6 flex justify-end">
                        <button 
                            type="submit" 
                            class="bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 text-lg"
                        >
                            🎟️ Satın Al
                        </button>
                    </div>
                </form>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
                    <p class="text-yellow-800">
                        Bilet satın almak için 
                        <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:text-blue-800">giriş yapmalısınız</a>.
                    </p>
                </div>

                <div class="mt-4 space-y-2">
                    @foreach($event->ticketTypes as $ticketType)
                        <div class="border rounded-lg p-4 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold">{{ $ticketType->name }}</h3>
                                <div class="text-gray-600 text-sm">
                                    <span class="font-semibold">{{ number_format($ticketType->price, 2) }} ₺</span>
                                    <span class="ml-3">Mevcut Kota: {{ $ticketType->remaining_quantity }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endauth
        @endif
    </div>
</div>
@endsection
