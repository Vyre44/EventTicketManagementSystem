@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-0">{{ $event->title }} - Bilet Raporu</h1>
        <div class="text-muted">Etkinliğe ait biletlerin durum özeti</div>
    </div>
    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary btn-sm">Etkinliklere Dön</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-lg-2">
                <div class="p-3 bg-success-subtle rounded">
                    <div class="text-muted small">Ödenen Gelir</div>
                    <div class="fw-bold">{{ number_format($summary['paid_revenue'], 2) }} ₺</div>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="p-3 bg-primary-subtle rounded">
                    <div class="text-muted small">Ödenen Sipariş</div>
                    <div class="fw-bold">{{ $summary['paid_count'] }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="p-3 bg-info-subtle rounded">
                    <div class="text-muted small">Ödenen Bilet</div>
                    <div class="fw-bold">{{ $summary['paid_tickets'] }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="p-3 bg-warning-subtle rounded">
                    <div class="text-muted small">Beklemede Olan Bilet</div>
                    <div class="fw-bold">{{ $summary['pending_count'] }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="p-3 bg-danger-subtle rounded">
                    <div class="text-muted small">İptal Edilen Bilet</div>
                    <div class="fw-bold">{{ $summary['cancelled_count'] }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="p-3 bg-secondary-subtle rounded">
                    <div class="text-muted small">İade Edilen Bilet</div>
                    <div class="fw-bold">{{ $summary['refunded_count'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-semibold">Bilet Durumu</label>
                <select name="status" class="form-select">
                    <option value="">Tümü</option>
                    <option value="{{ \App\Enums\TicketStatus::ACTIVE->value }}" {{ request('status') === \App\Enums\TicketStatus::ACTIVE->value ? 'selected' : '' }}>Aktif</option>
                    <option value="{{ \App\Enums\TicketStatus::CHECKED_IN->value }}" {{ request('status') === \App\Enums\TicketStatus::CHECKED_IN->value ? 'selected' : '' }}>Kullanıldı</option>
                    <option value="{{ \App\Enums\TicketStatus::CANCELLED->value }}" {{ request('status') === \App\Enums\TicketStatus::CANCELLED->value ? 'selected' : '' }}>İptal</option>
                    <option value="{{ \App\Enums\TicketStatus::REFUNDED->value }}" {{ request('status') === \App\Enums\TicketStatus::REFUNDED->value ? 'selected' : '' }}>İade</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Sipariş Durumu</label>
                <select name="order_status" class="form-select">
                    <option value="">Tümü</option>
                    <option value="{{ \App\Enums\OrderStatus::PENDING->value }}" {{ request('order_status') === \App\Enums\OrderStatus::PENDING->value ? 'selected' : '' }}>Beklemede</option>
                    <option value="{{ \App\Enums\OrderStatus::PAID->value }}" {{ request('order_status') === \App\Enums\OrderStatus::PAID->value ? 'selected' : '' }}>Ödendi</option>
                    <option value="{{ \App\Enums\OrderStatus::CANCELLED->value }}" {{ request('order_status') === \App\Enums\OrderStatus::CANCELLED->value ? 'selected' : '' }}>İptal</option>
                    <option value="{{ \App\Enums\OrderStatus::REFUNDED->value }}" {{ request('order_status') === \App\Enums\OrderStatus::REFUNDED->value ? 'selected' : '' }}>İade</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Ara (No/E-posta)</label>
                <input type="text" name="search" class="form-control" placeholder="Bilet No veya e-posta" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Sayfa Başına</label>
                <select name="per_page" class="form-select">
                    <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filtrele</button>
                <a href="{{ route('admin.reports.events.tickets.export', [$event->id]) }}?{{ http_build_query(request()->query()) }}" class="btn btn-success btn-sm">CSV</a>
            </div>
        </form>
    </div>
</div>

@if($tickets->isEmpty())
    <div class="card shadow-sm">
        <div class="card-body text-center text-muted">Kriterlere uygun bilet bulunamadı.</div>
    </div>
@else
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Bilet No</th>
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
                                <td class="ps-3 font-monospace">{{ $ticket->id }}</td>
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
                                        <span class="badge bg-success">Kullanıldı</span>
                                    @elseif($ticket->status === \App\Enums\TicketStatus::CANCELLED)
                                        <span class="badge bg-danger">İptal</span>
                                    @elseif($ticket->status === \App\Enums\TicketStatus::REFUNDED)
                                        <span class="badge bg-secondary">İade</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->checked_in_at)
                                        {{ $ticket->checked_in_at->format('d.m.Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->order)
                                        @if($ticket->order->status === \App\Enums\OrderStatus::PENDING)
                                            <span class="badge bg-warning text-dark">Beklemede</span>
                                        @elseif($ticket->order->status === \App\Enums\OrderStatus::PAID)
                                            <span class="badge bg-success">Ödendi</span>
                                        @elseif($ticket->order->status === \App\Enums\OrderStatus::CANCELLED)
                                            <span class="badge bg-danger">İptal</span>
                                        @elseif($ticket->order->status === \App\Enums\OrderStatus::REFUNDED)
                                            <span class="badge bg-secondary">İade</span>
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
    </div>

    <div class="mt-3">
        {{ $tickets->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
