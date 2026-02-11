{{-- 
    Admin Check-in Formu
    Etkinliğe gelen kullanıcıların biletlerini doğrulama (QR/barcode).
    Admin tüm etkinliklere check-in yapabilir (ownership bypass).
    Son kontrol edilen biletler listesi ve real-time durum güncellemesi.
--}}
@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-0">{{ $event->title }} - Giriş Kontrolü</h1>
            <span class="badge bg-danger">YÖNETİCİ PANELİ</span>
        </div>
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Yönetici Etkinlikleri</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div id="ajax-alert-container"></div>

    <form id="checkin-form" method="POST" action="{{ route('admin.events.checkin.check', $event) }}" class="card card-body mb-4">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-bold">Bilet Kodu</label>
            <input type="text" name="code" id="code-input" class="form-control form-control-lg" placeholder="Bilet kodunu girin veya tarayıcıyla okutun" autofocus>
            <small class="text-muted">Yönetici yetkisi ile tüm etkinliklere giriş onayı yapabilirsiniz</small>
        </div>
        <button type="submit" class="btn btn-primary btn-lg">🔍 Doğrula ve Giriş Onayla</button>
    </form>

    @if(!empty($recent) && $recent->count())
        <div class="card card-body">
            <h3 class="h6 fw-bold mb-3">📋 Son 10 Giriş</h3>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Bilet Kodu</th>
                            <th>Giriş Zamanı</th>
                            <th>Bilet Tipi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent as $t)
                            <tr>
                                <td><code>{{ $t->code }}</code></td>
                                <td>{{ $t->checked_in_at?->format('d.m.Y H:i') }}</td>
                                <td>{{ $t->ticketType->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<script>
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';
}

function showAlert(message, type = 'success') {
    const container = document.getElementById('ajax-alert-container');
    const alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
    container.innerHTML = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
    setTimeout(() => {
        container.innerHTML = '';
    }, 5000);
}

document.getElementById('checkin-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const codeInput = document.getElementById('code-input');
    const code = codeInput.value.trim();
    
    if (!code) {
        showAlert('Lütfen bilet kodu girin.', 'error');
        return;
    }
    
    const url = form.action;
    const csrf = getCsrfToken();
    
    try {
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
        
        if (res.ok) {
            showAlert(data.message || 'Giriş onayı başarılı!', 'success');
            codeInput.value = '';
            codeInput.focus();
            
            // Sayfa yenile (son 10 check-in'i güncellemek için)
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            const alertType = res.status === 409 ? 'warning' : 'error';
            showAlert(data.message || 'Bir hata oluştu.', alertType);
            codeInput.value = '';
            codeInput.focus();
        }
    } catch (err) {
        showAlert('Sunucu ile bağlantı hatası.', 'error');
        console.error(err);
    }
});
</script>
@endsection
