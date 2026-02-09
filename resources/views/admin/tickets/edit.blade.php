@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Bilet Düzenle #{{ $ticket->id }}</h1>
    <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-outline-secondary btn-sm">Detaya Dön</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
        @endforeach
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.tickets.update', $ticket) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Bilet Tipi</label>
                        <select name="ticket_type_id" class="form-select">
                            <option value="">Değiştirme</option>
                            @foreach($ticketTypes as $tt)
                                <option value="{{ $tt->id }}" @selected(old('ticket_type_id', $ticket->ticket_type_id) == $tt->id)>{{ $tt->event->title }} - {{ $tt->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="">Değiştirme</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $ticket->status->value) == $status->value)>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Giriş Zamanı (Opsiyonel)</label>
                        <input type="datetime-local" name="checked_in_at" value="{{ old('checked_in_at', $ticket->checked_in_at ? $ticket->checked_in_at->format('Y-m-d\TH:i') : '') }}" class="form-control">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                        <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-outline-secondary">İptal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="fw-semibold mb-2">Not</div>
                <div class="text-muted">Durum veya giriş zamanını güncelleyebilirsiniz.</div>
            </div>
        </div>
    </div>
</div>
@endsection
