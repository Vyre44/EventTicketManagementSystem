@extends('layouts.app')

@section('content')
    <h1>Yeni Etkinlik Oluştur</h1>
    <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
        @csrf
        <label>Başlık: <input type="text" name="title" required></label><br>
        <label>Açıklama: <textarea name="description"></textarea></label><br>
        <label>Başlangıç: <input type="datetime-local" name="start_time" required></label><br>
        <label>Bitiş: <input type="datetime-local" name="end_time"></label><br>
        <label>Kapak Görseli: <input type="file" name="cover"></label><br>
        <button type="submit">Kaydet</button>
    </form>
@endsection
