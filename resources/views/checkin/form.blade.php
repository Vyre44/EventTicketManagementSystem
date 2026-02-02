    <h1>{{ $event->title }} - Check-in</h1>

@if(session('error'))
    <div style="color:red">{{ session('error') }}</div>
@endif
@if(session('warning'))
    <div style="color:orange">{{ session('warning') }}</div>
@endif
@if(session('success'))
    <div style="color:green">{{ session('success') }}</div>
@endif


<form id="checkin-form" method="POST" action="{{ route('organizer.events.checkin.check', $event) }}">
    @csrf
    <input type="text" name="code" placeholder="Bilet kodu" autofocus>
    <button type="submit">Doğrula</button>
</form>

<script>
// CSRF token'ı formdaki _token input'undan al
function getCsrfTokenFromForm(form) {
    const input = form.querySelector('input[name="_token"]');
    return input ? input.value : '';
}

document.getElementById('checkin-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const code = form.code.value;
    const url = form.action;
    const csrf = getCsrfTokenFromForm(form);
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ code })
        });
        let text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            data = { message: text };
        }
        if (!res.ok) {
            // 422, 409, 500 gibi durumlarda
            alert(data.message || 'Bir hata oluştu.');
        } else {
            alert(data.message || 'Bilinmeyen cevap');
        }
    } catch (err) {
        alert('Bir hata oluştu.');
    }
});
</script>

@if(!empty($recent) && $recent->count())
    <h3>Son 10 Check-in</h3>
    <ul>
        @foreach($recent as $t)
            <li>{{ $t->code }} — {{ $t->checked_in_at?->format('d.m.Y H:i') }}</li>
        @endforeach
    </ul>
@endif
