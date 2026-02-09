@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">📊 Etkinlik Satış Raporları</h1>
        <p class="text-muted mb-0">Etkinlik seçerek detaylı satış ve bilet raporlarını görüntüleyin</p>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <a href="{{ route('admin.reports.event-sales') }}" class="btn btn-primary">
            🔎 Etkinlik Bazlı Satış Raporu (AJAX)
        </a>
    </div>
</div>

@if($events->isEmpty())
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <p class="text-muted mb-3">Henüz rapor oluşturulacak etkinlik bulunmuyor.</p>
            <a href="{{ route('admin.events.create') }}" class="btn btn-primary">Yeni Etkinlik Oluştur</a>
        </div>
    </div>
@else
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Etkinlik Adı</th>
                            <th>Organizatör</th>
                            <th>Başlangıç</th>
                            <th class="text-center">Sipariş Sayısı</th>
                            <th class="text-center">Bilet Sayısı</th>
                            <th class="text-center pe-3">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($events as $event)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold">{{ $event->title }}</div>
                                    <div class="small mt-1">
                                        @if($event->status === \App\Enums\EventStatus::PUBLISHED)
                                            <span class="badge bg-success">● Yayında</span>
                                        @elseif($event->status === \App\Enums\EventStatus::DRAFT)
                                            <span class="badge bg-warning">● Taslak</span>
                                        @elseif($event->status === \App\Enums\EventStatus::CANCELLED)
                                            <span class="badge bg-danger">● İptal</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $event->organizer->name ?? 'Yok' }}</td>
                                <td>{{ $event->start_time?->format('d.m.Y H:i') ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $event->orders_count }} sipariş</span>
                                </td>
                                @php
                                    $visibleTicketCount = $event->tickets
                                        ? $event->tickets->whereIn('status', [
                                            \App\Enums\TicketStatus::ACTIVE,
                                            \App\Enums\TicketStatus::CHECKED_IN,
                                        ])->count()
                                        : $event->tickets_count;
                                @endphp
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $visibleTicketCount }} bilet (aktif)</span>
                                </td>
                                <td class="text-center pe-3">
                                    <a href="{{ route('admin.reports.events.tickets', $event) }}" 
                                       class="btn btn-success btn-sm">
                                        📈 Raporu Görüntüle
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
