<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Etkinlik Biletleri')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts (Vite: Bootstrap 5 CSS+JS imported) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm role-accent-attendee">
        <div class="container">
            <!-- Logo -->
            <a href="{{ route('attendee.events.index') }}" class="navbar-brand">
                <span class="fs-4 fw-bold text-primary">🎫 EventTickets</span>
            </a>

            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#attendeeNav" aria-controls="attendeeNav" aria-expanded="false" aria-label="Menüyü Aç">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Collapsible Menu -->
            <div class="collapse navbar-collapse" id="attendeeNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a href="{{ route('attendee.events.index') }}" class="nav-link">
                            Etkinlikler
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('attendee.orders.index') }}" class="nav-link">
                            Siparişlerim
                        </a>
                    </li>
                </ul>

                <!-- Right Side: User Dropdown + Logout -->
                <div class="d-flex align-items-center gap-2">
                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-success btn-sm dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            👤 {{ Auth::user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Çıkış</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- AJAX Alert Container (for dynamic alerts) -->
    <div id="ajax-alert-container" class="position-fixed top-0 end-0 p-4" style="max-width: 420px; z-index: 1050;"></div>

    <!-- FLASH MESSAGES -->
    <div class="container py-4">
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Hata!</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✓ Başarılı!</strong>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>✗ Hata!</strong>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>⚠ Uyarı!</strong>
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- MAIN CONTENT -->
    <main class="container py-4">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <h5 class="text-white fw-bold">EventTickets</h5>
                    <p class="small">Etkinlik biletlerini hızlı ve güvenli şekilde satın alın.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5 class="text-white fw-bold">Bağlantılar</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('attendee.events.index') }}" class="text-decoration-none text-light">Etkinlikler</a></li>
                        <li><a href="{{ route('attendee.orders.index') }}" class="text-decoration-none text-light">Siparişlerim</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h5 class="text-white fw-bold">İletişim</h5>
                    <p class="small">📧 info@eventtickets.com</p>
                    <p class="small">📱 +90 123 456 7890</p>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center small">
                <p>&copy; 2026 EventTickets. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    @include('attendee.js.ajax')

</body>
</html>

