@props(['replies'])

@forelse($replies as $reply)
    <x-post.comment :post="$reply" />
@empty
    <div class="text-center text-muted small py-3">No replies available.</div>
@endforelse
