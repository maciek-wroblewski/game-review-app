@props(['post'])

@if($post)
<a href="/posts/{{$post->id }}" class="text-decoration-none text-body d-block mt-3">
    <div class="border rounded p-3 hover-bg-light transition-all" style="background-color: #f8f9fa;">
        
        <!-- Header: Avatar, Username, and Date -->
        <div class="d-flex align-items-center gap-2 mb-2">
            <img 
                src="{{ $post->author->avatar}}" 
                alt="Avatar" 
                class="rounded-circle object-fit-cover" 
                style="width: 24px; height: 24px;"
            >
            <span class="fw-semibold" style="font-size: 0.9rem;">
                {{ $post->user->username ?? 'Anonymous' }}
            </span>
            <span class="text-muted" style="font-size: 0.8rem;">
                &middot; {{ $post->created_at->diffForHumans(null, true, true) }}
            </span>
        </div>

        <!-- Body: Clamped Text -->
        <div 
            class="text-muted" 
            style="font-size: 0.85rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
            {{ $post->body }}
        </div>
    </div>
</a>
@endif