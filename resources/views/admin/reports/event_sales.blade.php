@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Etkinlik Bazlı Satış Raporu</h1>
            <div class="text-muted">Etkinlik seçimi ile rapor</div>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">Raporlara Dön</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Etkinlik Seç</label>
                    <select id="event-select" class="form-select">
                        <option value="">-- Etkinlik seçin --</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}">{{ $event->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button id="fetch-report" class="btn btn-primary btn-sm w-100">Raporu Getir</button>
                </div>
            </div>
        </div>
    </div>

    <div id="ajax-alert-container"></div>

    <div id="report-container" class="d-none">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title" id="report-title"></h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="p-3 bg-success-subtle rounded">
                            <div class="text-muted small">Ödenen Sipariş</div>
                            <div class="fw-bold" id="paid-orders">0</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-primary-subtle rounded">
                            <div class="text-muted small">Ödenen Gelir</div>
                            <div class="fw-bold" id="paid-revenue">0 ₺</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 bg-warning-subtle rounded">
                            <div class="text-muted small">Beklemede</div>
                            <div class="fw-bold" id="pending-count">0</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 bg-danger-subtle rounded">
                            <div class="text-muted small">İptal</div>
                            <div class="fw-bold" id="cancelled-count">0</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="p-3 bg-secondary-subtle rounded">
                            <div class="text-muted small">İade</div>
                            <div class="fw-bold" id="refunded-count">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';
}

function showAlert(message, type = 'success') {
    const container = document.getElementById('ajax-alert-container');
    const alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
    container.innerHTML = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
}

function formatCurrency(value) {
    const num = Number(value || 0);
    return new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num) + ' ₺';
}

document.getElementById('fetch-report').addEventListener('click', async function() {
    const eventId = document.getElementById('event-select').value;
    if (!eventId) {
        showAlert('Lütfen bir etkinlik seçin.', 'warning');
        return;
    }

    const url = `{{ route('admin.reports.event-sales.data') }}?event_id=${eventId}`;
    try {
        const res = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            }
        });
        const payload = await res.json();

        if (!res.ok || !payload.success) {
            showAlert(payload.message || 'Rapor alınamadı.', 'error');
            return;
        }

        const data = payload.data;
        document.getElementById('report-title').innerText = data.event.title + ' - Satış Özeti';
        document.getElementById('paid-orders').innerText = data.paid_orders;
        document.getElementById('paid-revenue').innerText = formatCurrency(data.paid_revenue);
        document.getElementById('pending-count').innerText = data.pending_count;
        document.getElementById('cancelled-count').innerText = data.cancelled_count;
        document.getElementById('refunded-count').innerText = data.refunded_count;
        document.getElementById('report-container').classList.remove('d-none');
        showAlert(payload.message || 'Rapor başarıyla getirildi.', 'success');
    } catch (err) {
        showAlert('Sunucu ile bağlantı hatası.', 'error');
    }
});
</script>
@endsection
