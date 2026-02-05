<h1>{{ $event->title }}</h1>
<p>{{ $event->description }}</p>
<p>Başlangıç: {{ $event->start_time }}</p>
<p>Bitiş: {{ $event->end_time }}</p>
<p>Durum: {{ $event->status->value ?? $event->status }}</p>
@if($event->cover_image_url)
    <img src="{{ $event->cover_image_url }}" alt="Kapak" style="max-width:300px;">
@endif

<a href="{{ route('organizer.events.edit', $event) }}">Düzenle</a>
<a href="{{ route('organizer.events.ticket-types.index', $event) }}">Bilet Tipleri</a>
<a href="{{ route('organizer.events.checkin.form', $event) }}">Check-in</a>
<a href="{{ route('organizer.events.index') }}">Listeye Dön</a>
