@props(['post'])

<div class="card-header clickable-card bg-white d-flex justify-content-between align-items-center py-3 border-bottom-0"
    data-href="/posts/{{ $post->id }}" style="cursor: pointer;">
    <div class="d-flex align-items-center column-gap-4">
        <x-user.avatar :user="$post->author" layout="compact" :size="'50px'" />
        <div>
            <div class="d-flex align-items-center">
                <a href="/users/{{ $post->author->username ?? '#' }}" class="text-decoration-none fw-bold text-dark fs-5 me-2">
                    {{ $post->author->username ?? 'Anonymous' }}
                </a>
                @if(optional($post->author)->verified) <i class="bi bi-patch-check-fill text-primary"></i> @endif
                <span class="js-editing-badge badge bg-warning text-dark ms-2 d-none">Editing</span>
            </div>
            <div class="text-muted small d-flex flex-wrap gap-2">
                <span>{{ $post->created_at->diffForHumans(null, true, true) }}</span>
                @if($post->created_at->ne($post->updated_at))
                <span class="fst-italic" title="Edited on {{ $post->updated_at->diffForHumans(null, true, true) }}">(Edited)</span>
                @endif
                @if($post->hub)
                @php
                $routeName = $post->hub->getTable();
                $href = '/' . $routeName . "/" . $post->hub_id;
                @endphp
                <span class="text-secondary">&bull;</span>
                <span>Posted in <a href="{{$href}}">{{ $post->hub->title ?? $post->hub->name ?? $post->hub->username ?? 'Hub' }}</a></span>
                @endif
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        @if($post->author) <x-follow-button :target-user="$post->author" /> @endif
        @auth
        @if(auth()->id() === $post->user_id && !$post->trashed())
        <div class="dropdown">
            <button class="btn btn-light btn-sm rounded-circle border-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><button class="js-btn-edit dropdown-item"><i class="bi bi-pencil me-2"></i>Edit</button></li>
                <li><button class="js-btn-delete dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button></li>
            </ul>
        </div>
        @endif
        @endauth
    </div>
</div>

<style>
    {{-- Keep only Post-Card specific styles --}}
    .clickable-card { transition: background-color 0.2s ease; }
    .clickable-card:hover { background-color: rgba(0,0,0,0.02); }
</style>
@once
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        // Global Event Delegation for Header & Quote Actions
        document.addEventListener('click', (e) => {
            // --- 1. Handle Clickable Cards (Header AND Quote) ---
            const clickable = e.target.closest('.clickable-card');
            
            // If we clicked a clickable-card, AND we didn't click an internal link/button/dropdown
            if (clickable && !e.target.closest('a') && !e.target.closest('button') && !e.target.closest('.dropdown-menu')) {
                const url = clickable.getAttribute('data-href');
                if (url) {
                    window.location.href = url;
                }
                return; // Stop further processing for this click
            }

            // --- 2. Handle Post Card Specific Actions (Edit/Delete) ---
            // Only proceed if we are inside a post card for destructive actions
            const card = e.target.closest('.js-post-card');
            if (!card) return;

            // Destructive Actions API Hook
            if (e.target.closest('.js-btn-delete')) {
                if (!confirm('Permanently delete this post?')) return;
                fetch(`/posts/${card.dataset.postId}`, { 
                    method: 'DELETE', 
                    headers: { 'X-CSRF-TOKEN': csrfToken } 
                })
                .then(res => { 
                    if(res.ok) { 
                        card.style.transition = 'opacity 0.4s ease';
                        card.style.opacity = 0; 
                        setTimeout(() => card.remove(), 400); 
                    } 
                });
            }
        });
    });
</script>
@endonce
