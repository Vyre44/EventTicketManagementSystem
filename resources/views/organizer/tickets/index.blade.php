{{-- 
    Organizatör Biletleri Yönetimi Sayfası
    Yalnız kendi etkinlikleri için oluşturulan biletleri listeler.
    Filtreler: durum, etkinlik. Arama: bilet kodı, müşteri email. İşlem: check-in (doğrulama).
--}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- AJAX uyarı mesajları burada görünür --}}
    <div id="ajax-alert-container"></div>

    {{-- Sayfa başlığı --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Biletler</h1>
            <p class="text-muted mb-0">
                {{-- Admin ise tüm biletler, organizatör ise kendi etkinlik biletleri --}}
                @if(auth()->user()->isAdmin())
                    Tüm biletleri yönetin
                @else
                    Kendi etkinliklerinizin biletlerini görüntüleyin ve yönetin
                @endif
            </p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Bilet Kodu / E-posta</label>
                    <input type="text" name="search" class="form-control" placeholder="Bilet kodu veya e-posta" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Etkinlik Adı</label>
                    <input type="text" name="event_search" class="form-control" placeholder="Etkinlik adı ara" value="{{ request('event_search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="">Tüm Durumlar</option>
                        @foreach($statuses as $status)
                            {{-- Durum değerinin Türkçe etiketleri --}}
                            <option value="{{ $status->value }}" @selected(request('status') == $status->value)>
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
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrele</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bilet yoksa boş durum mesajı --}}
    @if($tickets->isEmpty())
        <div class="card">
            <div class="card-body text-center text-muted">Henüz bilet bulunmamaktadır.</div>
        </div>
    @else
        {{-- Biletler tablosu --}}
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Bilet Kodu</th>
                            <th>Etkinlik</th>
                            <th>Bilet Tipi</th>
                            <th>Müşteri</th>
                            <th>Durum</th>
                            <th>Giriş</th>
                            <th class="text-center">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            {{-- data-ticket-id ve data-ticket-status JavaScript işlemleri için --}}
                            <tr data-ticket-id="{{ $ticket->id }}" data-ticket-status="{{ $ticket->status->value }}">
                                {{-- font-monospace ile kod daha okunaklı --}}
                                <td class="font-monospace">{{ $ticket->code }}</td>
                                <td class="fw-medium">{{ $ticket->ticketType->event->title }}</td>
                                <td>{{ $ticket->ticketType->name }}</td>
                                <td>
                                    {{-- Müşteri bilgileri --}}
                                    @if($ticket->order)
                                        <div>{{ $ticket->order->user->name }}</div>
                                        <div class="text-muted small">{{ $ticket->order->user->email }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{-- Bilet durumu badge --}}
                                    <span class="ticket-status-badge">
                                        @if($ticket->order && $ticket->order->status === \App\Enums\OrderStatus::PENDING)
                                            <span class="badge bg-warning text-dark">⏳ Ödeme Bekliyor</span>
                                        @elseif($ticket->order && $ticket->order->status === \App\Enums\OrderStatus::CANCELLED)
                                            <span class="badge bg-secondary">İptal</span>
                                        @elseif($ticket->order && $ticket->order->status === \App\Enums\OrderStatus::REFUNDED)
                                            <span class="badge bg-info">🔄 İade</span>
                                        @elseif($ticket->status === \App\Enums\TicketStatus::ACTIVE)
                                            <span class="badge bg-primary">Aktif</span>
                                        @elseif($ticket->status === \App\Enums\TicketStatus::CHECKED_IN)
                                            <span class="badge bg-success">✅ Kullanıldı</span>
                                        @elseif($ticket->status === \App\Enums\TicketStatus::CANCELLED)
                                            <span class="badge bg-danger">❌ İptal</span>
                                        @elseif($ticket->status === \App\Enums\TicketStatus::REFUNDED)
                                            <span class="badge bg-secondary">🔄 İade</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="ticket-checkin-time">
                                    {{-- Giriş tarihi varsa göster --}}
                                    @if($ticket->checked_in_at)
                                        {{ $ticket->checked_in_at->format('d.m.Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{-- Duruma göre farklı butonlar --}}
                                    <div class="d-flex gap-2 justify-content-center align-items-center ticket-actions">
                                        @if($ticket->status === \App\Enums\TicketStatus::ACTIVE)
                                            {{-- Aktif bilette giriş onayla ve iptal --}}
                                            @php
                                                $isPaid = $ticket->order && $ticket->order->status === \App\Enums\OrderStatus::PAID;
                                            @endphp
                                            <button 
                                                class="ticket-action-btn btn btn-sm btn-outline-success" 
                                                data-action="checkin" 
                                                title="{{ $isPaid ? 'Giriş Kontrolü' : 'Ödeme tamamlanmadan giriş yapılamaz' }}"
                                                @if(!$isPaid) disabled @endif
                                            >
                                                ✅ Giriş Onayla
                                            </button>
                                            <button class="ticket-action-btn btn btn-sm btn-outline-danger" data-action="cancel" title="İptal Et">
                                                ❌ İptal
                                            </button>
                                        @elseif($ticket->status === \App\Enums\TicketStatus::CHECKED_IN)
                                            {{-- Kullanılmış bilette geri alma --}}
                                            <button class="ticket-action-btn btn btn-sm btn-outline-warning" data-action="undo" title="Giriş Onayını Geri Al">
                                                ↩️ Geri Al
                                            </button>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                        <a href="{{ route('organizer.tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                            Detay →
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $tickets->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

{{-- JavaScript route tanımları --}}
<script>
    // AJAX butonları için route'ları Blade'den al
    const routeNameMap = {
        'checkin': '{{ route("organizer.tickets.checkin", ["ticket" => "__TICKET_ID__"]) }}',
        'undo': '{{ route("organizer.tickets.checkinUndo", ["ticket" => "__TICKET_ID__"]) }}',
        'cancel': '{{ route("organizer.tickets.cancel", ["ticket" => "__TICKET_ID__"]) }}'
    };
</script>
@endsection
