@props(['status'])

@php
    $badgeClasses = match($status) {
        \App\Enums\OrderStatus::PENDING => ['bg-yellow-100', 'text-yellow-800', '⏳ Ödeme Bekliyor'],
        \App\Enums\OrderStatus::PAID => ['bg-green-100', 'text-green-800', '✅ Ödendi'],
        \App\Enums\OrderStatus::CANCELLED => ['bg-red-100', 'text-red-800', '❌ İptal Edildi'],
        \App\Enums\OrderStatus::REFUNDED => ['bg-gray-100', 'text-gray-800', '🔄 İade Edildi'],
        \App\Enums\TicketStatus::ACTIVE => ['bg-green-100', 'text-green-800', '✅ Aktif'],
        \App\Enums\TicketStatus::CHECKED_IN => ['bg-blue-100', 'text-blue-800', '✓ Check-in Yapıldı'],
        \App\Enums\TicketStatus::CANCELLED => ['bg-red-100', 'text-red-800', '❌ İptal Edildi'],
        \App\Enums\TicketStatus::REFUNDED => ['bg-gray-100', 'text-gray-800', '🔄 İade Edildi'],
        default => ['bg-gray-100', 'text-gray-800', '⚪ Bilinmiyor']
    };
@endphp

<span class="inline-block {{ $badgeClasses[0] }} {{ $badgeClasses[1] }} px-3 py-1 rounded-full text-sm font-semibold whitespace-nowrap" id="{{ $attributes->get('id') }}">
    {{ $badgeClasses[2] }}
</span>
