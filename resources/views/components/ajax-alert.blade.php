{{-- AJAX Alert Container Component --}}
<div id="ajax-alert-container" class="fixed top-4 right-4 z-50 w-96 max-w-full">
    {{-- Alert messages will be injected here via JavaScript --}}
</div>

@push('scripts')
<script src="{{ asset('resources/js/ajax-helper.js') }}"></script>
@endpush
