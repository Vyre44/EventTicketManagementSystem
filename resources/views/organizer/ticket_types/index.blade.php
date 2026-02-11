{{-- 
    Organizatör Bilet Tipleri Yönetimi Sayfası
    Etkinliğe ait bilet tiplerini listeler (Özel, Standart, Ekonomik).
    İşlemler: oluştur, düzenle, sil (satılmış biletler kontrolü ile). Stoğ: kalan/toplam miktar.
--}}
@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Sayfa başlığı ve butonlar --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Bilet Tipleri</h1>
            {{-- Hangi etkinliğin biletlerini görüntülüyoruz --}}
            <div class="text-muted">Etkinlik: <strong>{{ $event->title }}</strong></div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('organizer.events.ticket-types.create', $event) }}" class="btn btn-primary">Yeni Bilet Tipi</a>
            <a href="{{ route('organizer.events.index') }}" class="btn btn-outline-secondary">Etkinliklere Dön</a>
        </div>
    </div>

    {{-- Session'dan gelen başarı mesajı --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Bilet tipleri yoksa bilgilendirme --}}
    @if($ticketTypes->isEmpty())
        <div class="alert alert-info">Bu etkinlik için bilet tipi bulunamadı.</div>
    @else
        {{-- Bilet tipleri tablosu --}}
        <div class="table-responsive bg-white border rounded">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ad</th>
                        <th>Fiyat</th>
                        <th>Toplam</th>
                        <th>Kalan</th>
                        <th>Satış Aralığı</th>
                        <th class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Her bilet tipi için satır --}}
                    @foreach($ticketTypes as $type)
                        <tr>
                            <td>{{ $type->name }}</td>
                            {{-- number_format ile fiyatı 2 ondalık göster --}}
                            <td>{{ number_format($type->price, 2) }} ₺</td>
                            <td>{{ $type->total_quantity }}</td>
                            <td>{{ $type->remaining_quantity }}</td>
                            <td>
                                {{-- Satış başlangıç ve bitiş tarihlerini göster --}}
                                @if($type->sale_start || $type->sale_end)
                                    {{ $type->sale_start?->format('d.m.Y H:i') ?? '—' }}
                                    -
                                    {{ $type->sale_end?->format('d.m.Y H:i') ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('organizer.events.ticket-types.edit', [$event, $type]) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
