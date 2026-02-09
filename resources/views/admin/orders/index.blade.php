@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-0">Siparişler</h1>
        <div class="text-muted">Tüm siparişleri yönetin</div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Durum</label>
                <select name="status" class="form-select">
                    <option value="">Tüm Durumlar</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') == $status->value)>
                            @if($status->value === 'pending')
                                Beklemede
                            @elseif($status->value === 'paid')
                                Ödendi
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
            <div class="col-md-6">
                <label class="form-label fw-semibold">Sipariş No veya Kullanıcı E-posta</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Sipariş No veya kullanıcı e-posta" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrele</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Sipariş No</th>
                        <th>Kullanıcı E-posta</th>
                        <th>Durum</th>
                        <th>Bilet Sayısı</th>
                        <th>Oluşturulma</th>
                        <th class="text-end pe-3">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="ps-3 fw-semibold">#{{ $order->id }}</td>
                        <td>{{ $order->user->email ?? '-' }}</td>
                        <td>
                            @php
                                $statusValue = $order->status->value ?? $order->status;
                                $statusLabel = '';
                                $badgeClass = 'bg-secondary';
                                
                                if ($statusValue === 'pending') {
                                    $statusLabel = 'Beklemede';
                                    $badgeClass = 'bg-warning';
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
                        </td>
                        <td>{{ $order->tickets_count }}</td>
                        <td>{{ $order->created_at?->format('d.m.Y H:i') ?? '-' }}</td>
                        <td class="text-end pe-3">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">Detay</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Sipariş bulunamadı</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $orders->links('pagination::bootstrap-5') }}
</div>
@endsection
