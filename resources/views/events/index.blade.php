@extends('layouts.app')

@section('content')
    <h1>Etkinlikler</h1>
    <a href="{{ route('events.create') }}">Yeni Etkinlik</a>
    <ul>
        @foreach($events as $event)
            <li>
                <a href="{{ route('events.show', $event) }}">{{ $event->title }}</a>
                ({{ $event->status->value }})
            </li>
        @endforeach
    </ul>
@endsection
