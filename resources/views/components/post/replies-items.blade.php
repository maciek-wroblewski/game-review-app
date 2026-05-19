@props(['replies'])

@foreach($replies as $reply)
    <x-post.comment :post="$reply" />
@endforeach

{{-- Render empty state only on initial page requests to prevent pagination glitches --}}
@if($replies->isEmpty() && request('page', 1) == 1)
    <div class="text-center text-muted small py-4">
        <i class="bi bi-chat-slash me-1"></i> No replies yet.
    </div>
@endif