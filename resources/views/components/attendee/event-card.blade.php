{{-- Etkinlik kartı bileşeni (event-card): Etkinlik bilgisini görsel olarak gösterir --}}
@props(['event'])

{{-- Etkinlik kartını içeren div --}}
<div class="bg-white rounded-lg shadow-sm hover:shadow-md transition overflow-hidden">
    {{-- Etkinlik kapak resmi veya renkli fon --}}
    @if($event->cover_image_url)
        <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="w-full h-48 object-cover">
    @else
        {{-- Kapak resmi yoksa mavi gradyan arkaplan + emoji --}}
        <div class="w-full h-48 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-content-center text-white text-4xl">
            🎪
        </div>
    @endif

    {{-- Kart içeriği (başlık, tarih, yer, açıklama) --}}
    <div class="p-4">
        {{-- Etkinlik başlığı --}}
        <h3 class="text-lg font-bold text-gray-900 mb-2 truncate">
            {{ $event->title }}
        </h3>

        {{-- Tarih ve yer bilgileri --}}
        <div class="text-sm text-gray-600 space-y-1 mb-4">
            {{-- Başlangıç tarihi ve saati --}}
            <div class="flex items-center">
                <span class="text-lg mr-2">📅</span>
                <span>{{ $event->start_time->format('d.m.Y H:i') }}</span>
            </div>
            {{-- Mekan (varsa) --}}
            @if($event->location)
                <div class="flex items-center">
                    <span class="text-lg mr-2">📍</span>
                    <span>{{ $event->location }}</span>
                </div>
            @endif
        </div>

        {{-- Etkinlik açıklaması (en fazla 2 satır) --}}
        <p class="text-sm text-gray-600 mb-4 line-clamp-2">
            {{ $event->description }}
        </p>

        {{-- Detayları görme butonu --}}
        <a href="{{ route('attendee.events.show', $event) }}" class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
            Detayları Gör
        </a>
    </div>
</div>
