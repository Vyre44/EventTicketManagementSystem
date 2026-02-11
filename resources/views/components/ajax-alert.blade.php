{{-- AJAX uyarı mesajları konteyneri (JavaScript üzerinden dinamik olarak doldurulur) --}}
<div id="ajax-alert-container" class="fixed top-4 right-4 z-50 w-96 max-w-full">
    {{-- Uyarı mesajları JavaScript tarafından buraya eklenecek --}}
</div>

@push('scripts')
{{-- AJAX yardımcı kütüphanesini yükle --}}
<script src="{{ asset('resources/js/ajax-helper.js') }}"></script>
@endpush
