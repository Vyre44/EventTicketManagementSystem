{{-- Tüm sayfaların temel HTML yapısı: başlık, meta etiketleri, CSS ve JS --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        {{-- Karakterleri doğru göstermek için UTF-8 kodlaması bildir --}}
        <meta charset="utf-8">
        {{-- Mobil cihazlarda doğru görünüm için viewport ayarı --}}
        <meta name="viewport" content="width=device-width, initial-scale=1">
        {{-- CSRF güvenlik için token bilgisi (JavaScript'te kullanılacak) --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Tarayıcı başlığında gösterilecek başlık --}}
        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- Google Fonts: Yazı tipi kaynağı --}}
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        {{-- CSS ve JavaScript dosyalarını yükle (Vite alet tarafından yönetilir: Bootstrap 5 dahil) --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-light">
        {{-- Sayfanın en dış kabı: arka plan ve minimum yükseklik ayarları --}}
        <div class="min-vh-100 bg-light">
            {{-- Tüm sayfaların en üstündeki navigasyon çubuğunu dahil et --}}
            @include('layouts.navigation')

            {{-- AJAX istekleri için hata/başarı mesajlarının gösterileceği alan --}}
            <div id="ajax-alert-container" class="position-fixed top-0 end-0 p-4 z-3" style="max-width: 420px;"></div>

            {{-- Ana sayfa içeriği: her sayfada farklı olacak --}}
            <main class="container py-4">
                {{-- yield: alt sayfaların içeriğini buraya yerleştir --}}
                @yield('content')
            </main>
        </div>
    </body>
</html>
