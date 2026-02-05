@props(['type' => 'info', 'dismissible' => true])

@php
    $classes = match($type) {
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        default => 'bg-blue-100 border-blue-400 text-blue-700',
    };
    
    $icon = match($type) {
        'success' => '✓',
        'error' => '✗',
        'warning' => '⚠',
        default => 'ℹ',
    };
@endphp

<div class="border rounded-lg px-4 py-3 mb-4 {{ $classes }} alert-{{ $type }}" role="alert" x-data="{ show: true }" x-show="show" x-cloak>
    <div class="flex items-start">
        <span class="text-lg mr-2">{{ $icon }}</span>
        <div class="flex-1">
            {{ $slot }}
        </div>
        @if($dismissible)
            <button @click="show = false" class="text-xl leading-none opacity-70 hover:opacity-100">
                ×
            </button>
        @endif
    </div>
</div>
