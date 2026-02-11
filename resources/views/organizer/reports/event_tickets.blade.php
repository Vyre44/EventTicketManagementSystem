{{-- Organizatör etkinlik biletleri raporu --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('organizer.events.index') }}" class="btn btn-outline-secondary btn-sm mb-3 d-inline-block">
                ← Etkinliklere Dön
            </a>
            <h1 class="h4 mb-1">{{ $event->title }} - Bilet Raporu</h1>
            <p class="text-muted mb-0">Etkinliğinize ait tüm biletlerin durumu</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="">Tümü</option>
                        <option value="{{ \App\Enums\TicketStatus::ACTIVE->value }}" {{ request('status') === \App\Enums\TicketStatus::ACTIVE->value ? 'selected' : '' }}>Aktif</option>
                        <option value="{{ \App\Enums\TicketStatus::CHECKED_IN->value }}" {{ request('status') === \App\Enums\TicketStatus::CHECKED_IN->value ? 'selected' : '' }}>Kullanıldı</option>
                        <option value="{{ \App\Enums\TicketStatus::CANCELLED->value }}" {{ request('status') === \App\Enums\TicketStatus::CANCELLED->value ? 'selected' : '' }}>İptal</option>
                        <option value="{{ \App\Enums\TicketStatus::REFUNDED->value }}" {{ request('status') === \App\Enums\TicketStatus::REFUNDED->value ? 'selected' : '' }}>İade</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ara (No/E-posta)</label>
                    <input type="text" name="search" class="form-control" placeholder="Bilet No veya e-posta" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sayfa Başına</label>
                    <select name="per_page" class="form-select">
                        <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        Filtrele
                    </button>
                    <a href="{{ route('organizer.reports.events.tickets.export', [$event->id]) }}?{{ http_build_query(request()->query()) }}" 
                       class="btn btn-success">
                        📥 CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    @if($tickets->isEmpty())
        <div class="card">
            <div class="card-body text-center text-muted">Kriterlere uygun bilet bulunamadı.</div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Bilet No</th>
                            <th>Bilet Tipi</th>
                            <th>Müşteri / E-posta</th>
                            <th>Durum</th>
                            <th>Giriş Zamanı</th>
                            <th>Sipariş Durumu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            <tr>
                                <td class="font-monospace">{{ $ticket->id }}</td>
                                <td>{{ $ticket->ticketType->name }}</td>
                                <td>
                                    @if($ticket->order?->user)
                                        <div>{{ $ticket->order->user->name }}</div>
                                        <div class="text-muted small">{{ $ticket->order->user->email }}</div>
                                    @else
                                        <span class="text-muted">Yok</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->status === \App\Enums\TicketStatus::ACTIVE)
                                        <span class="badge bg-primary">Aktif</span>
                                    @elseif($ticket->status === \App\Enums\TicketStatus::CHECKED_IN)
                                        <span class="badge bg-success">✅ Kullanıldı</span>
                                    @elseif($ticket->status === \App\Enums\TicketStatus::CANCELLED)
                                        <span class="badge bg-danger">❌ İptal</span>
                                    @elseif($ticket->status === \App\Enums\TicketStatus::REFUNDED)
                                        <span class="badge bg-secondary">🔄 İade</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($ticket->checked_in_at)
                                        {{ $ticket->checked_in_at->format('d.m.Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if($ticket->order)
                                        @if($ticket->order->status === \App\Enums\OrderStatus::PENDING)
                                            <span class="text-warning fw-semibold">⏳ Bekliyor</span>
                                        @elseif($ticket->order->status === \App\Enums\OrderStatus::PAID)
                                            <span class="text-success fw-semibold">✅ Ödendi</span>
                                        @elseif($ticket->order->status === \App\Enums\OrderStatus::CANCELLED)
                                            <span class="text-danger fw-semibold">❌ İptal</span>
                                        @elseif($ticket->order->status === \App\Enums\OrderStatus::REFUNDED)
                                            <span class="text-muted fw-semibold">🔄 İade</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Yok</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $tickets->render() }}
        </div>
    @endif
</div>
@endsection
