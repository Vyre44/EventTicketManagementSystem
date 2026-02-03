<h1>Etkinliklerim</h1>
<a href="{{ route('organizer.events.create') }}">Yeni Etkinlik</a>
<table>
    <tr>
        <th>ID</th><th>Başlık</th><th>Kapak</th><th>Durum</th><th>İşlem</th>
    </tr>
    @foreach($events as $event)
    <tr>
        <td>{{ $event->id }}</td>
        <td>{{ $event->title }}</td>
        <td>
            @if($event->cover_image_url)
                <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="w-16 h-16 object-cover">
            @else
                <span>-</span>
            @endif
        </td>
        <td>{{ $event->status->value ?? $event->status }}</td>
        <td>
            <a href="{{ route('organizer.events.show', $event) }}">Görüntüle</a>
            <a href="{{ route('organizer.events.edit', $event) }}">Düzenle</a>
            <a href="{{ route('organizer.reports.events.tickets', $event) }}">📊 Rapor</a>
            <form method="POST" action="{{ route('organizer.events.destroy', $event) }}" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit">Sil</button>
            </form>
        </td>
    </tr>
    @endforeach
</table>
{{ $events->links() }}
