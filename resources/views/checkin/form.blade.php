{{-- Giriş kontrolü (check-in) formu --}}
@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Sayfa başlığı ve geri dön butonu --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">{{ $event->title }} - Giriş Kontrolü</h1>
        <a href="{{ route('organizer.events.index') }}" class="btn btn-outline-secondary">Etkinliklere Dön</a>
    </div>

    {{-- Session mesajları (hata, uyarı, başarı) --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Bilet kodu giriş formu --}}
    <form id="checkin-form" method="POST" action="{{ route('organizer.events.checkin.check', $event) }}" class="card card-body mb-4">
        @csrf
        <div class="mb-3">
            <label class="form-label">Bilet Kodu</label>
            {{-- autofocus ile sayfa açılınca input otomatik focus alır --}}
            <input type="text" name="code" class="form-control" placeholder="Bilet kodu" autofocus>
        </div>
        <button type="submit" class="btn btn-primary">Doğrula</button>
    </form>

    {{-- Son 10 giriş yapılan biletler listesi --}}
    @if(!empty($recent) && $recent->count())
        <div class="card card-body">
            <h3 class="h6">Son 10 Giriş</h3>
            <ul class="mb-0">
                @foreach($recent as $t)
                    <li>{{ $t->code }} — {{ $t->checked_in_at?->format('d.m.Y H:i') }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

{{-- JavaScript ile AJAX check-in işlemi --}}
<script>
// CSRF token'i almak için fonksiyon
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';
}

// Form submit edildiğinde AJAX isteği gönder
document.getElementById('checkin-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const code = form.code.value;
    const url = form.action;
    const csrf = getCsrfToken();
    try {
        // fetch ile POST isteği
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ code })
        });
        const data = await res.json();
        // Cevaba göre alert göster
        if (!res.ok) {
            alert(data.message || 'Bir hata oluştu.');
        } else {
            alert(data.message || 'Bilinmeyen cevap');
        }
    } catch (err) {
        alert('Bir hata oluştu.');
    }
});
</script>
@endsection
