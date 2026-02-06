@extends('attendee.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('attendee.events.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold inline-flex items-center">
            ← Etkinliklere Dön
        </a>
    </div>

    @if($event->cover_image_url)
        <div class="mb-6">
            <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="w-full h-64 object-cover rounded-lg">
        </div>
    @else
        <div class="mb-6 w-full h-64 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-5xl rounded-lg">
            🎪
        </div>
    @endif

    <!-- Event Header -->
    <div class="bg-gradient-to-br from-blue-400 to-blue-600 text-white rounded-lg p-8 mb-8">
        <h1 class="text-4xl font-bold mb-4">{{ $event->title }}</h1>
        
        <div class="space-y-2 text-blue-100">
            <div class="flex items-center gap-2">
                <span class="text-2xl">📅</span>
                <span class="text-lg">{{ $event->start_time->format('d MMMM Y') }} • {{ $event->start_time->format('H:i') }}</span>
            </div>
            @if($event->location)
                <div class="flex items-center gap-2">
                    <span class="text-2xl">📍</span>
                    <span class="text-lg">{{ $event->location }}</span>
                </div>
            @endif
            <div class="flex items-center gap-2">
                <span class="text-2xl">👤</span>
                <span class="text-lg">Organize Eden: {{ $event->organizer->name }}</span>
            </div>
        </div>
    </div>

    <!-- Description -->
    @if($event->description)
        <div class="bg-white border rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4">📖 Açıklama</h2>
            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $event->description }}</p>
        </div>
    @endif

    <!-- Ticket Selection -->
    @php
        $totalRemaining = $event->ticketTypes->sum('remaining_quantity');
    @endphp
    @if($event->ticketTypes->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p class="text-yellow-800 text-lg">Bu etkinlik için henüz bilet satılmamaktadır.</p>
        </div>
    @elseif($totalRemaining <= 0)
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
            <p class="text-red-800 text-lg">Biletler tükendi. Lütfen daha sonra tekrar deneyiniz.</p>
        </div>
    @else
        <div class="bg-white border rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6">🎟️ Biletleri Seç</h2>

            @auth
                <form id="buy-form" action="{{ route('attendee.events.buy', $event) }}" method="POST">
                    @csrf
                    
                    <!-- Ticket Types -->
                    <div class="space-y-4 mb-8">
                        @foreach($event->ticketTypes as $ticketType)
                            <x-attendee.ticket-card :ticket-type="$ticketType" />
                        @endforeach
                    </div>

                    <!-- Total Amount (Dynamic) -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-8">
                        <div class="flex justify-between items-center text-lg">
                            <span class="font-semibold text-gray-900">Toplam:</span>
                            <span class="text-2xl font-bold text-blue-600" id="total-amount">₺0,00</span>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4 flex-col md:flex-row">
                        <a href="{{ route('attendee.events.index') }}" class="flex-1 text-center bg-gray-300 text-gray-900 px-6 py-3 rounded-lg font-semibold hover:bg-gray-400 transition">
                            ❌ İptal
                        </a>
                        <button type="submit" class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition text-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            ✓ Satın Al
                        </button>
                    </div>
                </form>

                <script>
                    // Total Price Calculator
                    document.addEventListener('DOMContentLoaded', function() {
                        const ticketTypeMap = {
                            @foreach($event->ticketTypes as $type)
                                {{ $type->id }}: {{ $type->price }},
                            @endforeach
                        };

                        function updateTotal() {
                            let total = 0;
                            document.querySelectorAll('.qty-input').forEach(input => {
                                const qty = parseInt(input.value) || 0;
                                const ticketTypeId = input.name.match(/\[(\d+)\]/)[1];
                                total += qty * ticketTypeMap[ticketTypeId];
                            });

                            document.getElementById('total-amount').textContent = 
                                '₺' + total.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                            // Disable submit if no tickets selected
                            const submitBtn = document.querySelector('button[type="submit"]');
                            submitBtn.disabled = total === 0;
                        }

                        // Listen to qty changes
                        document.querySelectorAll('.qty-input').forEach(input => {
                            input.addEventListener('change', updateTotal);
                            input.addEventListener('input', updateTotal);
                        });

                        // Initial calc
                        updateTotal();
                    });
                </script>
            @else
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                    <p class="text-blue-900 mb-4">Bilet satın almak için giriş yapmanız gerekir.</p>
                    <a href="{{ route('login') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        🔐 Giriş Yap
                    </a>
                </div>
            @endauth
        </div>
    @endif
</div>
@endsection
