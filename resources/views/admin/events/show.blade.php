@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">{{ $event->title }}</h1>
        <div>
            <a href="{{ route('admin.events.edit', $event) }}" class="bg-yellow-600 text-white px-4 py-2 rounded">Düzenle</a>
            <a href="{{ route('admin.events.index') }}" class="bg-gray-200 px-4 py-2 rounded ml-2">Listeye Dön</a>
        </div>
    </div>

    <div class="bg-white shadow rounded p-6 space-y-3">
        <div><strong>Başlangıç:</strong> {{ $event->start_time?->format('d.m.Y H:i') }}</div>
        <div><strong>Bitiş:</strong> {{ $event->end_time?->format('d.m.Y H:i') ?? '-' }}</div>
        <div><strong>Durum:</strong> {{ $event->status?->value ?? $event->status }}</div>
        <div><strong>Organizatör ID:</strong> {{ $event->organizer_id }}</div>
        @if($event->description)
            <div><strong>Açıklama:</strong> {{ $event->description }}</div>
        @endif
        @if($event->cover_image_url)
            <div>
                <strong>Kapak:</strong><br>
                <img src="{{ $event->cover_image_url }}" alt="Kapak" style="max-width:600px;max-height:400px;object-fit:cover;border-radius:0.5rem;">
            </div>
        @endif
    </div>
</div>
@endsection
