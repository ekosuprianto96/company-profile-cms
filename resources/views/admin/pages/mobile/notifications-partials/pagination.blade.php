@if ($notifications->hasPages())
    <div class="d-flex justify-content-center">
        {{ $notifications->links('pagination::bootstrap-5') }}
    </div>
@endif
