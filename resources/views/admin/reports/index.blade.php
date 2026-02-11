{{-- 
    Admin Raporlar Sayfası
    Etkinlik ve bilet istatistiklerini görüntülemek için ana sayfa.
    Raporlar: etkinlik satışları, etkinlik biletleri, gelir analizi.
    CSV export: Detaylı veri indirme özelliği.
--}}
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">📊 Etkinlik Satış Raporları</h1>
        <p class="text-muted mb-0">Etkinlik seçerek detaylı satış ve bilet raporlarını görüntüleyin</p>
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
    {{-- Arama / Filtreleme Alanı --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-9">
                    <label class="form-label fw-semibold small">Etkinlik Ara</label>
                    <input 
                        type="text" 
                        id="eventSearchInput" 
                        class="form-control" 
                        placeholder="Etkinlik adı veya organizatör yazın..."
                    >
                </div>
                <div class="col-md-3">
                    <button id="clearSearchBtn" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise me-2"></i>Temizle
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                    <tbody id="eventsTableBody">
                        @foreach($events as $event)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold">{{ $event->title }}</div>
                                    <div class="small mt-1">
                                        @if($event->status === \App\Enums\EventStatus::PUBLISHED)
                                            <span class="badge bg-success">● Yayında</span>
                                        @elseif($event->status === \App\Enums\EventStatus::DRAFT)
                                            <span class="badge bg-warning">● Taslak</span>
                                        @elseif($event->status === \App\Enums\EventStatus::ENDED)
                                            <span class="badge bg-secondary">● Bitti</span>
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

<script>
    /**
     * FRONT-END ARAMA / FİLTRELEME
     * 
     * Tablo satırlarını gerçek zamanlı olarak filtrele
     * Küçük/büyük harf duyarsız
     */
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('eventSearchInput');
        const clearBtn = document.getElementById('clearSearchBtn');
        const tableBody = document.getElementById('eventsTableBody');

        // Arama input'u - keyup event'inde filtreleme yap
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase().trim();
                filterTable(searchTerm);
            });
        }

        // Temizle butonu - input'u boşalt ve tüm satırları göster
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                filterTable('');
            });
        }

        /**
         * Tablo Filtreleme Fonksiyonu
         * 
         * @param {string} searchTerm - Arama metni (küçük harfli)
         */
        function filterTable(searchTerm) {
            if (!tableBody) return;

            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(row => {
                // Satırın tüm text içeriğini al
                const rowText = row.innerText.toLowerCase();

                // Arama terimi satır içinde var mı?
                if (searchTerm === '' || rowText.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Hiç eşleşme yoksa mesaj göster (opsiyonel)
            // İsteğe bağlı olarak eklenebilir
        }
    });
</script>

@endsection
