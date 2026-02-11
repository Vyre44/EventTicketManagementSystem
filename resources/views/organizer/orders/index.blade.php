{{-- 
    Organizatör Siparişler Sayfası
    Yalnız kendi etkinlikleri için alınan siparişleri listeler.
    Filtreler: durum (PENDING, PAID, CANCELLED). Arama: sipariş ID, müşteri email.
--}}
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-0">Siparişler</h1>
        <div class="text-muted">
            {{-- Admin ise tüm siparişleri görür, organizatör ise sadece kendi etkinliklerinin siparişlerini --}}
            @if(auth()->user()->isAdmin())
                Tüm siparişleri yönetin
            @else
                Kendi etkinliklerinizin siparişlerini görüntüleyin
            @endif
        </div>
    </div>
</div>

{{-- Filtreleme formu --}}
<!-- Filter Card -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Sipariş No / E-posta Ara</label>
                {{-- Önceki arama terimini korumak için value="{{ request('search') }}" --}}
                <input type="text" name="search" class="form-control" placeholder="Sipariş No veya e-posta" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Durum</label>
                <select name="status" class="form-select">
                    <option value="">Tüm Durumlar</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') == $status->value)>
                            {{-- Durumları Türkçe'ye çevir --}}
                            @if($status->value === 'pending')
                                Ödeme Bekliyor
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
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrele</button>
            </div>
        </form>
    </div>
</div>

{{-- Sipariş yoksa boş durum, varsa tablo --}}
@if($orders->isEmpty())
    <div class="card shadow-sm">
        <div class="card-body text-center text-muted">Henüz sipariş bulunmamaktadır.</div>
    </div>
@else
    {{-- Siparişler tablosu --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Sipariş No</th>
                            <th>Etkinlik</th>
                            <th>Müşteri</th>
                            <th>Tutar</th>
                            <th class="text-center">Bilet Sayısı</th>
                            <th>Durum</th>
                            <th class="text-end pe-3">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td class="ps-3 fw-semibold">#{{ $order->id }}</td>
                                <td class="fw-semibold">{{ $order->event->title }}</td>
                                <td>
                                    {{-- Müşteri adı ve e-postası --}}
                                    <div>{{ $order->user->name }}</div>
                                    <div class="text-muted small">{{ $order->user->email }}</div>
                                </td>
                                {{-- number_format ile tutarı 2 ondalık göster --}}
                                <td class="fw-semibold">{{ number_format($order->total_amount, 2) }} ₺</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $order->tickets_count }}</span>
                                </td>
                                <td>
                                    {{-- Sipariş durumuna göre badge rengi --}}
                                    @if($order->status->value === 'pending')
                                        <span class="badge bg-warning text-dark">Beklemede</span>
                                    @elseif($order->status->value === 'paid')
                                        <span class="badge bg-success">Ödendi</span>
                                    @elseif($order->status->value === 'cancelled')
                                        <span class="badge bg-danger">İptal</span>
                                    @elseif($order->status->value === 'refunded')
                                        <span class="badge bg-secondary">İade</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('organizer.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">Detay</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sayfalama linkleri --}}
    <div class="mt-3">
        {{ $orders->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
