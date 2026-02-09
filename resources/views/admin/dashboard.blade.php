@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-0">Yönetici Paneli</h1>
        <div class="text-muted">Sistem istatistikleri ve özet</div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted">Toplam Etkinlik</div>
                    <div class="h4 text-primary mb-0">{{ $stats['total_events'] }}</div>
                </div>
                <div class="fs-3 text-primary">📅</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted">Toplam Organizatör</div>
                    <div class="h4 text-primary mb-0">{{ $stats['total_organizers'] ?? 0 }}</div>
                </div>
                <div class="fs-3 text-secondary">🎭</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted">Toplam Katılımcı</div>
                    <div class="h4 text-success mb-0">{{ $stats['total_attendees'] ?? 0 }}</div>
                </div>
                <div class="fs-3 text-success">👥</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted">Toplam Sipariş</div>
                    <div class="h4 text-success mb-0">{{ $stats['total_orders'] }}</div>
                </div>
                <div class="fs-3 text-success">📦</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted">Toplam Gelir (Ödendi)</div>
                    <div class="h4 text-success mb-0">{{ number_format($stats['total_revenue'] ?? 0, 2) }} ₺</div>
                </div>
                <div class="fs-3 text-success">💰</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted">Toplam Bilet</div>
                    <div class="h4 text-secondary mb-0">{{ $stats['total_tickets'] }}</div>
                </div>
                <div class="fs-3 text-secondary">🎫</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted">Kullanılan Bilet</div>
                    <div class="h4 text-warning mb-0">{{ $stats['checked_in_tickets'] }}</div>
                </div>
                <div class="fs-3 text-warning">✅</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted">Ödenen Sipariş</div>
                    <div class="h4 text-info mb-0">{{ $stats['paid_orders'] }}</div>
                </div>
                <div class="fs-3 text-info">💳</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="h5 mb-0">Bugünkü İstatistikler</div>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted">Bugün Yapılan Satış</div>
                    <div class="h4 text-primary mb-0">{{ $stats['today_orders'] ?? 0 }} sipariş</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted">Bugün Elde Edilen Gelir</div>
                    <div class="h4 text-success mb-0">{{ number_format($stats['today_revenue'] ?? 0, 2) }} ₺</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="h5 mb-3">Hızlı Erişim</div>
        <div class="row g-3">
            <div class="col-md-4">
                <a href="{{ route('admin.events.index') }}" class="text-decoration-none">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-semibold text-primary">Etkinlikler</div>
                        <div class="text-muted">Tüm etkinlikleri yönetin</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('admin.orders.index') }}" class="text-decoration-none">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-semibold text-success">Siparişler</div>
                        <div class="text-muted">Tüm siparişleri görüntüleyin</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('admin.tickets.index') }}" class="text-decoration-none">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-semibold text-secondary">Biletler</div>
                        <div class="text-muted">Biletleri yönetin</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-semibold text-warning">Kullanıcılar</div>
                        <div class="text-muted">Rol ve kullanıcı yönetimi</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('admin.reports.index') }}" class="text-decoration-none">
                    <div class="border rounded p-3 h-100">
                        <div class="fw-semibold text-dark">Raporlar</div>
                        <div class="text-muted">Etkinlik bazlı satış raporu</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
