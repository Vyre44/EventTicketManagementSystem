{{-- Etkinlik kartı komponenti - Event listesi bulunan kart --}}
@props(['event'])

<div class="bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden">
    <!-- Event Image -->
    @if($event->cover_image_url)
        <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="w-full h-48 object-cover">
    @else
        <div class="w-full h-48 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-4xl">
            🎪
        </div>
    @endif

    <!-- Card Content -->
    <div class="p-4">
        <!-- Title -->
        <h3 class="text-lg font-bold text-gray-900 mb-2 truncate">
            {{ $event->title }}
        </h3>

        <!-- Date & Time -->
        <div class="text-sm text-gray-600 space-y-1 mb-4">
            <div class="flex items-center">
                <span class="text-lg mr-2">📅</span>
                <span>{{ $event->start_time->format('d.m.Y H:i') }}</span>
            </div>
            @if($event->location)
                <div class="flex items-center">
                    <span class="text-lg mr-2">📍</span>
                    <span>{{ $event->location }}</span>
                </div>
            @endif
        </div>

        <!-- Description (truncate 2 lines) -->
        <p class="text-sm text-gray-600 mb-4 line-clamp-2">
            {{ $event->description }}
        </p>

        <!-- Button -->
        <a href="{{ route('attendee.events.show', $event) }}" class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
            Detayları Gör
        </a>
    </div>
</div>
