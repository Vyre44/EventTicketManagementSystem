@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Sipariş #{{ $order->id }}</h1>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">Siparişlere Dön</a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body d-flex justify-content-between align-items-start">
        <div>
            <div class="text-muted">Kullanıcı E-posta</div>
            <div class="fw-semibold">{{ $order->user->email ?? '-' }}</div>
            <div class="text-muted mt-2">Oluşturulma</div>
            <div class="fw-semibold">{{ $order->created_at?->format('d.m.Y H:i') ?? '-' }}</div>
        </div>
        <div>
            @php
                $statusValue = $order->status->value ?? $order->status;
                $statusLabel = '';
                $badgeClass = 'bg-secondary';

                if ($statusValue === 'pending') {
                    $statusLabel = 'Beklemede';
                    $badgeClass = 'bg-warning text-dark';
                } elseif ($statusValue === 'paid') {
                    $statusLabel = 'Ödendi';
                    $badgeClass = 'bg-success';
                } elseif ($statusValue === 'cancelled') {
                    $statusLabel = 'İptal';
                    $badgeClass = 'bg-danger';
                } elseif ($statusValue === 'refunded') {
                    $statusLabel = 'İade';
                    $badgeClass = 'bg-secondary';
                } else {
                    $statusLabel = $order->status->name ?? $statusValue;
                }
            @endphp
            <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Bilet No</th>
                        <th>Durum</th>
                        <th>Bilet Tipi</th>
                        <th>Etkinlik</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->tickets as $ticket)
                        <tr>
                            <td class="ps-3">{{ $ticket->id }}</td>
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
                            <td>{{ $ticket->ticketType->name ?? '-' }}</td>
                            <td>{{ $ticket->ticketType->event->title ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Bilet bulunamadı</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
