{{-- Admin biletler sayfası --}}
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-0">Biletler</h1>
        <div class="text-muted">Tüm biletleri yönetin</div>
    </div>
    <a href="{{ route('admin.tickets.create') }}" class="btn btn-primary btn-sm">Yeni Bilet</a>
</div>

{{-- Filtreleme formu: kod, durum, kullanıcı, etkinlik --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Kod veya No</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Kod veya No ara" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Durum</label>
                <select name="status" class="form-select">
                    <option value="">Tüm Durumlar</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') == $status->value)>
                            {{-- Bilet durumı Türkçe etiketleri --}}
                            @if($status->value === 'active')
                                Aktif
                            @elseif($status->value === 'checked_in')
                                Kullanıldı
                            @elseif($status->value === 'cancelled')
                                İptal
                            @elseif($status->value === 'refunded')
                                İade
                            @else
                                {{ $status->name }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kullanıcı E-posta</label>
                <input type="text" name="user_email" value="{{ request('user_email') }}" placeholder="Kullanıcı e-posta" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Etkinlik No</label>
                <input type="text" name="event_id" value="{{ request('event_id') }}" placeholder="Etkinlik No" class="form-control">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-outline-primary w-100">Filtrele</button>
            </div>
        </form>
    </div>
</div>

{{-- Bilet yoksa boş durum, varsa tablo --}}
@if($tickets->isEmpty())
    <div class="card shadow-sm">
        <div class="card-body text-center text-muted">Bilet bulunamadı.</div>
    </div>
@else
    {{-- Biletler tablosu --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Bilet No</th>
                            <th>Kod</th>
                            <th>Durum</th>
                            <th>Bilet Tipi</th>
                            <th>Etkinlik</th>
                            <th>Kullanıcı</th>
                            <th>Giriş</th>
                            <th class="text-end pe-3">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            {{-- data-ticket-id ve data-ticket-status JavaScript için kullanılır --}}
                            <tr data-ticket-id="{{ $ticket->id }}" data-ticket-status="{{ $ticket->status->value }}">
                                {{-- font-monospace ile kodlar daha okunaklı --}}
                                <td class="ps-3 font-monospace">{{ $ticket->id }}</td>
                                <td class="font-monospace">{{ $ticket->code }}</td>
                                <td>
                                    {{-- Bilet durumuna göre badge --}}
                                    <span class="ticket-status-badge">
                                        @if($ticket->status === \App\Enums\TicketStatus::ACTIVE)
                                            <span class="badge bg-primary">Aktif</span>
                                        @elseif($ticket->status === \App\Enums\TicketStatus::CHECKED_IN)
                                            <span class="badge bg-success">Kullanıldı</span>
                                        @elseif($ticket->status === \App\Enums\TicketStatus::CANCELLED)
                                            <span class="badge bg-danger">İptal</span>
                                        @elseif($ticket->status === \App\Enums\TicketStatus::REFUNDED)
                                            <span class="badge bg-secondary">İade</span>
                                        @endif
                                    </span>
                                </td>
                                <td>{{ $ticket->ticketType->name ?? '-' }}</td>
                                <td>{{ $ticket->ticketType->event->title ?? '-' }}</td>
                                <td>{{ $ticket->order->user->email ?? '-' }}</td>
                                <td>
                                    {{-- Giriş tarihi varsa göster --}}
                                    @if($ticket->checked_in_at)
                                        {{ $ticket->checked_in_at->format('d.m.Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    {{-- Duruma göre farklı butonlar --}}
                                    <div class="d-inline-flex gap-2 align-items-center ticket-actions">
                                        @if($ticket->status === \App\Enums\TicketStatus::ACTIVE)
                                            {{-- Aktif bilette giriş onayla ve iptal butonları --}}
                                            <button class="ticket-action-btn btn btn-outline-success btn-sm" data-action="checkin" title="Giriş Kontrolü">
                                                ✅ Giriş Onayla
                                            </button>
                                            <button class="ticket-action-btn btn btn-outline-danger btn-sm" data-action="cancel" title="İptal">
                                                ❌ İptal
                                            </button>
                                        @elseif($ticket->status === \App\Enums\TicketStatus::CHECKED_IN)
                                            {{-- Kullanılmış bilette geri alma butonu --}}
                                            <button class="ticket-action-btn btn btn-outline-warning btn-sm" data-action="undo" title="Geri Al">
                                                ↩️ Geri Al
                                            </button>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                        <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-outline-primary btn-sm">Detay</a>
                                        <a href="{{ route('admin.tickets.edit', $ticket) }}" class="btn btn-outline-secondary btn-sm">Düzenle</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sayfalama --}}
    <div class="mt-3">
        {{ $tickets->links('pagination::bootstrap-5') }}
    </div>
@endif

{{-- JavaScript için route mapping --}}
<script>
    // AJAX butonlar için route tanımları
    const routeNameMap = {
        'checkin': '{{ route("admin.tickets.checkin", ["ticket" => "__TICKET_ID__"]) }}',
        'undo': '{{ route("admin.tickets.checkinUndo", ["ticket" => "__TICKET_ID__"]) }}',
        'cancel': '{{ route("admin.tickets.cancelTicket", ["ticket" => "__TICKET_ID__"]) }}'
    };
</script>
@endsection
