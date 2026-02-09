@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Bilet Tipi Düzenle</h1>
        <a href="{{ route('organizer.events.ticket-types.index', $event) }}" class="btn btn-outline-secondary">Geri</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="ajax-alert-container"></div>
    <div id="validation-errors" class="alert alert-danger d-none"></div>

    <form id="ticket-type-update-form" method="POST" action="{{ route('organizer.events.ticket-types.update', [$event, $ticketType]) }}" class="card card-body mb-3">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label">Ad</label>
            <input type="text" name="name" value="{{ old('name', $ticketType->name) }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Fiyat</label>
            <input type="number" step="0.01" name="price" value="{{ old('price', $ticketType->price) }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Toplam Adet</label>
            <input type="number" name="total_quantity" value="{{ old('total_quantity', $ticketType->total_quantity) }}" class="form-control" required>
            <div class="form-text">Satılmış bilet sayısından düşük olamaz.</div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Satış Başlangıç</label>
                <input type="datetime-local" name="sale_start" value="{{ old('sale_start', $ticketType->sale_start?->format('Y-m-d\TH:i')) }}" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Satış Bitiş</label>
                <input type="datetime-local" name="sale_end" value="{{ old('sale_end', $ticketType->sale_end?->format('Y-m-d\TH:i')) }}" class="form-control">
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Güncelle</button>
            <a href="{{ route('organizer.events.ticket-types.index', $event) }}" class="btn btn-outline-secondary">İptal</a>
        </div>
    </form>

    <form id="ticket-type-delete-form" method="POST" action="{{ route('organizer.events.ticket-types.destroy', [$event, $ticketType]) }}" onsubmit="return confirm('Bilet tipini silmek istediğine emin misin?');">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-outline-danger">Sil</button>
    </form>
</div>

<script>
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';
}

function showTicketTypeAlert(message, type = 'success') {
    const container = document.getElementById('ajax-alert-container');
    const alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
    container.innerHTML = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
}

function showValidationErrors(errors) {
    const container = document.getElementById('validation-errors');
    if (!errors || Object.keys(errors).length === 0) {
        container.classList.add('d-none');
        container.innerHTML = '';
        return;
    }
    const items = Object.values(errors).flat().map(err => `<li>${err}</li>`).join('');
    container.innerHTML = `<ul class="mb-0">${items}</ul>`;
    container.classList.remove('d-none');
}

document.getElementById('ticket-type-update-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    showValidationErrors(null);

    const form = e.target;
    const formData = new FormData(form);
    const url = form.action;

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: formData,
        });

        const data = await res.json();

        if (!res.ok) {
            if (res.status === 422) {
                showValidationErrors(data.errors || {});
            } else {
                showTicketTypeAlert(data.message || 'Bir hata oluştu.', 'error');
            }
            return;
        }

        showTicketTypeAlert(data.message || 'Bilet tipi güncellendi.', 'success');
        if (data.data?.redirect_url) {
            window.location.href = data.data.redirect_url;
        }
    } catch (err) {
        showTicketTypeAlert('Sunucu ile bağlantı hatası.', 'error');
    }
});

document.getElementById('ticket-type-delete-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const url = form.action;

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: formData,
        });

        const data = await res.json();

        if (!res.ok) {
            showTicketTypeAlert(data.message || 'Silme işlemi başarısız.', 'error');
            return;
        }

        showTicketTypeAlert(data.message || 'Bilet tipi silindi.', 'success');
        if (data.data?.redirect_url) {
            window.location.href = data.data.redirect_url;
        }
    } catch (err) {
        showTicketTypeAlert('Sunucu ile bağlantı hatası.', 'error');
    }
});
</script>
@endsection
