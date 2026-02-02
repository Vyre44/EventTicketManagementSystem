<h1>TicketType Detay</h1>
<p>ID: {{ $ticketType->id }}</p>
<p>Event: {{ $ticketType->event->title ?? '-' }}</p>
<p>Ad: {{ $ticketType->name }}</p>
<p>Fiyat: {{ $ticketType->price }}</p>
<p>Kota: {{ $ticketType->quota }}</p>
<p>Aktif: {{ $ticketType->is_active ? 'Evet' : 'Hayır' }}</p>
<a href="{{ route('admin.ticket-types.edit', $ticketType) }}">Düzenle</a>
<a href="{{ route('admin.ticket-types.index') }}">Listeye Dön</a>
