@php
    $userRole = auth()->user()->role ?? null;
    $navbarClass = 'navbar navbar-expand-lg navbar-light bg-white shadow-sm';
    $accentClass = 'btn-outline-secondary';
    $roleClass = '';
    
    if ($userRole === \App\Enums\UserRole::ADMIN) {
        $roleClass = ' role-accent-admin';
        $accentClass = 'btn-outline-danger';
    } elseif ($userRole === \App\Enums\UserRole::ORGANIZER) {
        $roleClass = ' role-accent-organizer';
        $accentClass = 'btn-outline-primary';
    } elseif ($userRole === \App\Enums\UserRole::ATTENDEE) {
        $roleClass = ' role-accent-attendee';
        $accentClass = 'btn-outline-success';
    } else {
        $roleClass = '';
    }
@endphp

<nav class="{{ $navbarClass }}{{ $roleClass }}" data-bs-theme="light">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ route('home') }}">
            {{ config('app.name', 'Bilet Sistemi') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#primaryNav" aria-controls="primaryNav" aria-expanded="false" aria-label="Menüyü Aç">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="primaryNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @auth
                    @if($userRole === \App\Enums\UserRole::ADMIN)
                        <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link">Panel</a></li>
                        <li class="nav-item"><a href="{{ route('admin.events.index') }}" class="nav-link">Etkinlikler</a></li>
                        <li class="nav-item"><a href="{{ route('admin.orders.index') }}" class="nav-link">Siparişler</a></li>
                        <li class="nav-item"><a href="{{ route('admin.users.index') }}" class="nav-link">Kullanıcılar</a></li>
                        <li class="nav-item"><a href="{{ route('admin.reports.index') }}" class="nav-link">Raporlar</a></li>
                    @elseif($userRole === \App\Enums\UserRole::ORGANIZER)
                        <li class="nav-item"><a href="{{ route('organizer.events.index') }}" class="nav-link">Etkinliklerim</a></li>
                        <li class="nav-item"><a href="{{ route('organizer.orders.index') }}" class="nav-link">Siparişler</a></li>
                        <li class="nav-item"><a href="{{ route('organizer.tickets.index') }}" class="nav-link">Biletler</a></li>
                        <li class="nav-item"><a href="{{ route('organizer.events.index') }}" class="nav-link">Raporlar</a></li>
                    @else
                        <li class="nav-item"><a href="{{ route('attendee.events.index') }}" class="nav-link">Etkinlikler</a></li>
                        <li class="nav-item"><a href="{{ route('attendee.orders.index') }}" class="nav-link">Siparişlerim</a></li>
                    @endif
                @else
                    <li class="nav-item"><a href="{{ route('home') }}" class="nav-link">Ana Sayfa</a></li>
                @endauth
            </ul>

            <div class="d-flex align-items-center gap-2">
                @auth
                    <div class="dropdown">
                        <button class="btn {{ $accentClass }} btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            👤 {{ Auth::user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a href="{{ route('profile.edit') }}" class="dropdown-item">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Çıkış</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Giriş Yap</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
