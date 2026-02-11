{{-- Bilet tipi kartı (ticket-card): Bilet bilgileri ve miktar seçicisini gösterir --}}
@props(['ticketType', 'name' => null])

@php
     // Bilet stoğu kontrol et 
    $hasStock = $ticketType->remaining_quantity > 0;
    //Form alanının adını belirle
    $fieldName = $name ?? "ticket_types[{$ticketType->id}]";
@endphp

{{-- Bilet kartı --}}
<div class="border rounded p-3 d-flex justify-content-between align-items-center" style="transition: background-color .15s;">
    {{-- Bilet bilgileri (ad, fiyat, kalan bilet sayısı) --}}
    <div class="flex-grow-1">
        {{-- Bilet tipi adı --}}
        <h4 class="fw-semibold text-dark">{{ $ticketType->name }}</h4>
        <div class="small text-muted mt-1">
            {{-- Fiyat --}}
            <span class="fw-bold text-dark">₺{{ number_format($ticketType->price, 2, ',', '.') }}</span>
            <span class="mx-2">•</span>
            {{-- Kalan bilet sayısı (stok varsa yeşil, yoksa kırmızı) --}}
            @if($hasStock)
                <span class="fw-semibold text-success">{{ $ticketType->remaining_quantity }} kaldı</span>
            @else
                <span class="fw-semibold text-danger">Tükendi</span>
            @endif
        </div>
    </div>

    {{-- Miktar seçici (-, sayı input, +) --}}
    <div class="d-flex align-items-center gap-3 ms-3">
        {{-- Azalt butonu --}}
        <button type="button" class="qty-minus btn btn-outline-secondary btn-sm py-2 px-3" data-field="{{ $fieldName }}" {{ !$hasStock ? 'disabled' : '' }}>
            −
        </button>
        {{-- Miktar input alanı --}}
        <input 
            type="number" 
            name="{{ $fieldName }}" 
            value="0" 
            min="0" 
            max="{{ $hasStock ? 10 : 0 }}" 
            class="qty-input text-center border rounded py-2 ps-2 pe-2" 
            style="width: 3rem;"
            {{ !$hasStock ? 'disabled' : '' }}
            data-max="{{ $ticketType->remaining_quantity }}"
        />
        {{-- Arttır butonu --}}
        <button type="button" class="qty-plus btn btn-outline-secondary btn-sm py-2 px-3" data-field="{{ $fieldName }}" {{ !$hasStock ? 'disabled' : '' }}>
            +
        </button>
    </div>
</div>
