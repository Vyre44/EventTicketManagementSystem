{{-- Ana sayfa şablonunu kullan --}}
@extends('layouts.app')

{{-- İçerik bölümünü tanımla --}}
@section('content')

{{-- Sayfa başlığı ve yeni etkinlik butonu --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-0">Etkinliklerim</h1>
        <div class="text-muted">Etkinlikleri yönetin ve raporlayın</div>
    </div>
    {{-- Yeni etkinlik oluşturma sayfasına link --}}
    <a href="{{ route('organizer.events.create') }}" class="btn btn-primary btn-sm">Yeni Etkinlik</a>
</div>

{{-- Filtreleme kartı --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        {{-- 
            Filtreleme formu:
            - method="GET": Sayfa yenilenerek filtreleme yapar
            - Filtreler URL parametresi olarak gönderilir
        --}}
        <form method="GET" action="" class="row g-3 align-items-end">
            {{-- Arama kutusu: Başlık veya konum ara --}}
            <div class="col-md-4">
                <label for="search" class="form-label">Başlık / Konum Ara</label>
                {{-- request('search'): URL'deki search parametresini göster --}}
                <input type="text" id="search" name="search" class="form-control" placeholder="Başlık veya konum" value="{{ request('search') }}">
            </div>
            
            {{-- Durum filtresi: Dropdown menü --}}
            <div class="col-md-3">
                <label for="status" class="form-label">Durum</label>
                <select id="status" name="status" class="form-select">
                    <option value="">Tüm Durumlar</option>
                    {{-- Controller'dan gelen durum listesini döngüyle göster --}}
                    @foreach($statuses as $status)
                        {{-- @selected: URL'deki status parametresiyle eşleşirse seçili yap --}}
                        <option value="{{ $status->value }}" @selected(request('status') == $status->value)>
                            {{-- Durum değerine göre Türkçe isim göster --}}
                            @if($status->value === 'draft')
                                Taslak
                            @elseif($status->value === 'published')
                                Yayında
                            @elseif($status->value === 'cancelled')
                                İptal
                            @elseif($status->value === 'concluded')
                                Tamamlandı
                            @elseif($status->value === 'ended')
                                Bitti
                            @else
                                {{ $status->name }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            
            {{-- Filtrele butonu --}}
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrele</button>
            </div>
        </form>
    </div>
</div>

{{-- Etkinlikler tablosu kartı --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        {{-- table-responsive: Mobilde kaydırılabilir tablo --}}
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                {{-- Tablo başlıkları --}}
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No</th>
                        <th>Başlık</th>
                        <th>Kapak</th>
                        <th>Durum</th>
                        <th class="text-end pe-3">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- 
                        Controller'dan gelen etkinlik listesini döngüyle göster
                        $events: Paginated (sayfalanmış) etkinlik listesi
                    --}}
                    @foreach($events as $event)
                    <tr>
                        {{-- Etkinlik ID --}}
                        <td class="ps-3">{{ $event->id }}</td>
                        
                        {{-- Etkinlik başlığı --}}
                        <td class="fw-semibold">{{ $event->title }}</td>
                        
                        {{-- Kapak görseli (varsa göster) --}}
                        <td>
                            @if($event->cover_image_url)
                                <img src="{{ $event->cover_image_url }}" alt="{{ $event->title }}" class="img-thumbnail" style="max-width:72px;">
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        
                        {{-- Durum badge'i (renkli etiket) --}}
                        <td>
                            {{-- 
                                @php..@endphp: Durum değerini kontrol edip rengini belirle
                                status->value: Enum değeri (published, draft, cancelled, ended)
                            --}}
                            @php
                                $statusValue = $event->status->value ?? $event->status;
                                $statusLabel = $statusValue;
                                $badgeClass = 'bg-secondary'; // Varsayılan renk

                                // Durum değerine göre label ve renk ata
                                if ($statusValue === 'published') {
                                    $statusLabel = 'Yayında';
                                    $badgeClass = 'bg-success'; // Yeşil
                                } elseif ($statusValue === 'draft') {
                                    $statusLabel = 'Taslak';
                                    $badgeClass = 'bg-warning text-dark'; // Sarı
                                } elseif ($statusValue === 'cancelled') {
                                    $statusLabel = 'İptal';
                                    $badgeClass = 'bg-danger'; // Kırmızı
                                } elseif ($statusValue === 'ended') {
                                    $statusLabel = 'Bitti';
                                    $badgeClass = 'bg-dark'; // Siyah
                                }
                            @endphp
                            {{-- Badge'i göster --}}
                            <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                        </td>
                        
                        {{-- İşlem butonları --}}
                        <td class="text-end pe-3">
                            <div class="d-inline-flex flex-wrap gap-2">
                                {{-- Görüntüle butonu --}}
                                <a href="{{ route('organizer.events.show', $event) }}" class="btn btn-outline-primary btn-sm">Görüntüle</a>
                                
                                {{-- Düzenle butonu --}}
                                <a href="{{ route('organizer.events.edit', $event) }}" class="btn btn-outline-secondary btn-sm">Düzenle</a>
                                
                                {{-- Bilet Tipleri yönetim butonu --}}
                                <a href="{{ route('organizer.events.ticket-types.index', $event) }}" class="btn btn-outline-info btn-sm">Bilet Tipleri</a>
                                
                                {{-- Check-in (giriş kontrolü) butonu --}}
                                <a href="{{ route('organizer.events.checkin.form', $event) }}" class="btn btn-outline-success btn-sm">Giriş Kontrolü</a>
                                
                                {{-- Rapor butonu --}}
                                <a href="{{ route('organizer.reports.events.tickets', $event) }}" class="btn btn-outline-dark btn-sm">Rapor</a>
                                
                                {{-- 
                                    Silme formu:
                                    - method="DELETE": Laravel'de silme işlemi için
                                    - Buton tıklandığında form gönderilir
                                --}}
                                <form method="POST" action="{{ route('organizer.events.destroy', $event) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Sil</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 
    Sayfalama (Pagination):
    - $events->links(): Laravel otomatik sayfa numaraları oluşturur
    - bootstrap-5: Bootstrap 5 stilinde göster
--}}
<div class="mt-3">
    {{ $events->links('pagination::bootstrap-5') }}
</div>

{{-- İçerik bölümünü kapat --}}
@endsection
