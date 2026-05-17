@foreach($replies as $reply)
    <x-post.comment :post="$reply" />
@endforeach

@if($replies->isEmpty())
    <div class="text-center text-muted small py-4">
        <i class="bi bi-chat-slash me-1"></i> No replies yet.
    </div>
@endif
