@extends('layouts.app')

@section('content')
    <h1>Etkinliği Düzenle</h1>
    <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <label>Başlık: <input type="text" name="title" value="{{ $event->title }}" required></label><br>
        <label>Açıklama: <textarea name="description">{{ $event->description }}</textarea></label><br>
        <label>Başlangıç: <input type="datetime-local" name="start_time" value="{{ $event->start_time->format('Y-m-d\TH:i') }}" required></label><br>
        <label>Bitiş: <input type="datetime-local" name="end_time" value="{{ $event->end_time ? $event->end_time->format('Y-m-d\TH:i') : '' }}"></label><br>
        <label>Kapak Görseli: <input type="file" name="cover"></label><br>
        @if($event->cover_path)
            <img src="{{ asset('storage/' . $event->cover_path) }}" alt="Kapak Görseli" style="max-width:200px;">
        @endif<br>
        <button type="submit">Güncelle</button>
    </form>
@endsection
