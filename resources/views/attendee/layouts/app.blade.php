<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Etkinlik Biletleri')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Alpine.js (AJAX için) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none; }
    </style>
</head>
<body class="bg-light">
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <!-- Logo -->
            <a href="{{ route('attendee.events.index') }}" class="navbar-brand">
                <span class="fs-4 fw-bold text-primary">🎫 EventTickets</span>
            </a>

            <!-- Desktop Menu -->
                <div class="d-flex gap-3">
                    <a href="{{ route('attendee.events.index') }}" class="text-dark text-decoration-none">
                        Etkinlikler
                    </a>
                    <a href="{{ route('attendee.orders.index') }}" class="text-dark text-decoration-none">
                        Siparişlerim
                    </a>

                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-link text-danger">
                            Çıkış
                        </button>
                    </form>
                    
                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-link text-dark dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                            👤 {{ Auth::user()->name }}
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profil</a></li>
                        </ul>
                    </div>
                </div>
        </div>
    </nav>

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
    <main class="container pb-5">
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
