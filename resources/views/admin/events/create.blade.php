@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <h1 class="text-2xl font-bold mb-6">Yeni Etkinlik (Admin)</h1>

    <form method="POST" action="{{ route('admin.events.store') }}">
        @csrf

        <div class="mb-4">
            <label class="block font-semibold mb-1">Başlık</label>
            <input type="text" name="title" value="{{ old('title') }}" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Açıklama</label>
            <textarea name="description" class="w-full border rounded px-3 py-2" rows="4">{{ old('description') }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Başlangıç</label>
            <input type="datetime-local" name="start_time" value="{{ old('start_time') }}" class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold mb-1">Bitiş</label>
            <input type="datetime-local" name="end_time" value="{{ old('end_time') }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-6">
            <label class="block font-semibold mb-1">Organizatör ID (Opsiyonel)</label>
            <input type="number" name="organizer_id" value="{{ old('organizer_id') }}" class="w-full border rounded px-3 py-2">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Kaydet</button>
            <a href="{{ route('admin.events.index') }}" class="bg-gray-200 px-4 py-2 rounded">İptal</a>
        </div>
    </form>
</div>
@endsection
