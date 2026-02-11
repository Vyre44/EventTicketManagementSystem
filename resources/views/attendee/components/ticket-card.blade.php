{{-- Bilet kartı komponenti - Ekran seçim kartı --}}
@props(['ticketType', 'name' => null])

@php
    $hasStock = $ticketType->remaining_quantity > 0;
    $fieldName = $name ?? "ticket_types[{$ticketType->id}]";
@endphp

<div class="border rounded-lg p-4 flex items-center justify-between hover:bg-gray-50 transition">
    <!-- Ticket Info -->
    <div class="flex-1">
        <h4 class="font-semibold text-gray-900">{{ $ticketType->name }}</h4>
        <div class="text-sm text-gray-600 mt-1">
            <span class="font-bold text-gray-900">₺{{ number_format($ticketType->price, 2, ',', '.') }}</span>
            <span class="mx-2">•</span>
            @if($hasStock)
                <span class="text-green-600 font-semibold">{{ $ticketType->remaining_quantity }} kaldı</span>
            @else
                <span class="text-red-600 font-semibold">Tükendi</span>
            @endif
        </div>
    </div>

    <!-- Quantity Selector -->
    <div class="flex items-center space-x-3">
        <button type="button" class="qty-minus px-3 py-2 border rounded hover:bg-gray-100" data-field="{{ $fieldName }}" {{ !$hasStock ? 'disabled' : '' }}>
            −
        </button>
        <input 
            type="number" 
            name="{{ $fieldName }}" 
            value="0" 
            min="0" 
            max="{{ $hasStock ? 10 : 0 }}" 
            class="qty-input w-12 text-center border rounded py-2" 
            {{ !$hasStock ? 'disabled' : '' }}
            data-max="{{ $ticketType->remaining_quantity }}"
        />
        <button type="button" class="qty-plus px-3 py-2 border rounded hover:bg-gray-100" data-field="{{ $fieldName }}" {{ !$hasStock ? 'disabled' : '' }}>
            +
        </button>
    </div>
</div>
