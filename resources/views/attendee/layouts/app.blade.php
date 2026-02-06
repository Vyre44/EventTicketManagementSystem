<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Etkinlik Biletleri')</title>
    
    <!-- Tailwind CSS -->
    @vite('resources/css/app.css')
    
    <!-- Alpine.js (AJAX için) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- NAVBAR -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('attendee.events.index') }}" class="text-2xl font-bold text-blue-600">
                        🎫 EventTickets
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="flex items-center space-x-6">
                    <a href="{{ route('attendee.events.index') }}" class="text-gray-700 hover:text-blue-600 transition">
                        Etkinlikler
                    </a>
                    <a href="{{ route('attendee.orders.index') }}" class="text-gray-700 hover:text-blue-600 transition">
                        Siparişlerim
                    </a>

                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 text-sm font-medium text-red-600 hover:text-red-800 transition">
                            Çıkış
                        </button>
                    </form>
                    
                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600">
                            <span>👤 {{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4" :class="{ 'transform rotate-180': open }">
                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <div x-show="open" class="absolute right-0 mt-2 w-48 bg-white border rounded-lg shadow-lg py-2">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                Profil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- FLASH MESSAGES -->
    <div class="container mx-auto px-4 pt-4">
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4" role="alert">
                <strong class="font-bold">Hata!</strong>
                <ul class="mt-2 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 alert-success" role="alert">
                <strong class="font-bold">✓ Başarılı!</strong>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4" role="alert">
                <strong class="font-bold">✗ Hata!</strong>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-4" role="alert">
                <strong class="font-bold">⚠ Uyarı!</strong>
                <span>{{ session('warning') }}</span>
            </div>
        @endif
    </div>

    <!-- MAIN CONTENT -->
    <main class="container mx-auto px-4 pb-12">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-gray-900 text-gray-300 py-8 mt-16">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div>
                    <h3 class="text-white font-bold mb-4">EventTickets</h3>
                    <p class="text-sm">Etkinlik biletlerini hızlı ve güvenli şekilde satın alın.</p>
                </div>
                <div>
                    <h3 class="text-white font-bold mb-4">Bağlantılar</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('attendee.events.index') }}" class="hover:text-white">Etkinlikler</a></li>
                        <li><a href="{{ route('attendee.orders.index') }}" class="hover:text-white">Siparişlerim</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white font-bold mb-4">İletişim</h3>
                    <p class="text-sm">📧 info@eventtickets.com</p>
                    <p class="text-sm">📱 +90 123 456 7890</p>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-4 text-center text-sm">
                <p>&copy; 2026 EventTickets. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <!-- AJAX SCRIPT -->
    @include('attendee.js.ajax')
</body>
</html>
