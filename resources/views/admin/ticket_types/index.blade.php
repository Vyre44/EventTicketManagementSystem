@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Bilet Tipleri</h1>
    <a href="{{ route('admin.ticket-types.create') }}" class="btn btn-primary btn-sm">Yeni TicketType</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
        @endforeach
    </div>
@endif

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="{{ route('admin.ticket-types.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Etkinlik</label>
                <select name="event_id" class="form-select">
                    <option value="">Tüm Etkinlikler</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @selected((string)$eventId === (string)$event->id)>{{ $event->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Ad</label>
                <input name="q" value="{{ $q ?? '' }}" placeholder="Ad ara" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filtrele</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Event</th>
                        <th>Ad</th>
                        <th>Fiyat</th>
                        <th>Kota</th>
                        <th>Aktif</th>
                        <th class="text-end pe-3">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ticketTypes as $tt)
                        <tr>
                            <td class="ps-3">{{ $tt->id }}</td>
                            <td>{{ $tt->event->title ?? '-' }}</td>
                            <td>{{ $tt->name }}</td>
                            <td>{{ $tt->price }}</td>
                            <td>{{ $tt->quota }}</td>
                            <td>
                                <span class="badge {{ $tt->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $tt->is_active ? 'Evet' : 'Hayır' }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.ticket-types.show', $tt) }}" class="btn btn-outline-primary btn-sm">Görüntüle</a>
                                    <a href="{{ route('admin.ticket-types.edit', $tt) }}" class="btn btn-outline-secondary btn-sm">Düzenle</a>
                                    <form method="POST" action="{{ route('admin.ticket-types.destroy', $tt) }}" class="d-inline">
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

<div class="mt-3">
    {{ $ticketTypes->links('pagination::bootstrap-5') }}
</div>
@endsection
