{{-- Organizatör bilet tipi oluşturma formu (AJAX ile) --}}
@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Başlık ve geri butonu --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Yeni Bilet Tipi</h1>
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

    <form id="ticket-type-create-form" method="POST" action="{{ route('organizer.events.ticket-types.store', $event) }}" class="card card-body">
        @csrf
        <div class="mb-3">
            <label class="form-label">Ad</label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Fiyat</label>
            <input type="number" step="0.01" name="price" value="{{ old('price') }}" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Toplam Adet</label>
            <input type="number" name="total_quantity" value="{{ old('total_quantity') }}" class="form-control" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Satış Başlangıç</label>
                <input type="datetime-local" name="sale_start" value="{{ old('sale_start') }}" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Satış Bitiş</label>
                <input type="datetime-local" name="sale_end" value="{{ old('sale_end') }}" class="form-control">
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="{{ route('organizer.events.ticket-types.index', $event) }}" class="btn btn-outline-secondary">İptal</a>
        </div>
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

document.getElementById('ticket-type-create-form').addEventListener('submit', async function(e) {
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

        showTicketTypeAlert(data.message || 'Bilet tipi oluşturuldu.', 'success');
        if (data.data?.redirect_url) {
            window.location.href = data.data.redirect_url;
        }
    } catch (err) {
        showTicketTypeAlert('Sunucu ile bağlantı hatası.', 'error');
    }
});
</script>
@endsection
