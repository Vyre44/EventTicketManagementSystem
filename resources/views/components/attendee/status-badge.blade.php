{{-- Durum rozetini göstermek için bileşen (status-badge) --}}
@props(['status'])
 {{-- Durum tablosu: Enum değerine göre uygun stil ve metin belirle --}}
@php
    $badgeClasses = match($status) {
        \App\Enums\OrderStatus::PENDING => ['bg-warning-subtle', 'text-warning-emphasis', '⏳ Ödeme Bekliyor'],
        \App\Enums\OrderStatus::PAID => ['bg-success-subtle', 'text-success-emphasis', '✅ Ödendi'],
        \App\Enums\OrderStatus::CANCELLED => ['bg-danger-subtle', 'text-danger-emphasis', '❌ İptal Edildi'],
        \App\Enums\OrderStatus::REFUNDED => ['bg-light', 'text-dark', '🔄 İade Edildi'],
        \App\Enums\TicketStatus::ACTIVE => ['bg-success-subtle', 'text-success-emphasis', '✅ Aktif'],
        \App\Enums\TicketStatus::CHECKED_IN => ['bg-info-subtle', 'text-info-emphasis', '✓ Giriş Onaylandı'],
        \App\Enums\TicketStatus::CANCELLED => ['bg-danger-subtle', 'text-danger-emphasis', '❌ İptal Edildi'],
        \App\Enums\TicketStatus::REFUNDED => ['bg-light', 'text-dark', '🔄 İade Edildi'],
        default => ['bg-light', 'text-dark', '⚪ Bilinmiyor']
    };
@endphp

{{-- Renklendirilmiş durum rozeti --}}
<span class="badge {{ $badgeClasses[0] }} {{ $badgeClasses[1] }} ps-3 pe-3 py-2 fw-semibold text-nowrap" id="{{ $attributes->get('id') }}">
    {{ $badgeClasses[2] }}
</span>
