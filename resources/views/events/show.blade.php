@extends('layouts.app')

@section('content')
    <h1>{{ $event->title }}</h1>
    <p>{{ $event->description }}</p>
    <p>Başlangıç: {{ $event->start_time }}</p>
    <p>Bitiş: {{ $event->end_time }}</p>
    <p>Durum: {{ $event->status->value }}</p>
    @if($event->cover_path)
        <img src="{{ asset('storage/' . $event->cover_path) }}" alt="Kapak Görseli" style="max-width:300px;">
    @endif
    <a href="{{ route('admin.events.edit', $event) }}">Düzenle</a>
@endsection
