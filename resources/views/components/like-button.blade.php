@props(['post'])

@auth
<form action="/posts/{{ $post->id }}/like" method="POST" class="m-0 ajax-like-form d-inline-block" data-post-id="{{ $post->id }}">
@csrf
@php
    $authId = auth()->id();
    $hasLiked = false;
    if ($authId) {
        if ($post->getAttribute('liked_by_auth') !== null) {
            $hasLiked = (bool) $post->getAttribute('liked_by_auth');
        } elseif ($post->relationLoaded('likes')) {
            $hasLiked = $post->likes->contains('id', $authId);
        } else {
            $hasLiked = $post->likes()->where('user_id', $authId)->exists();
        }
    }
    $count = $post->likes_count ?? ($post->relationLoaded('likes') ? $post->likes->count() : 0);
@endphp
    <button type="submit" 
        class="btn btn-sm rounded-pill border-0 small d-flex align-items-center gap-2 like-btn {{ $hasLiked ? 'btn-primary is-liked' : 'btn-light' }}">
        <span class="like-icon-container">
            <i class="bi bi-heart icon-unliked"></i>
            <i class="bi bi-heart-fill icon-liked"></i>
        </span>
        <span class="like-count fw-medium">
            {{ $count > 0 ? $count : 'Like' }}
        </span>
    </button>
</form>
@else
<div class="m-0 d-inline-block">
    @php $guestCount = $post->likes_count ?? 0; @endphp
    <button class="btn btn-sm btn-light rounded-pill border-0 small d-flex align-items-center gap-2" disabled>
        <i class="bi bi-heart"></i>
        <span class="fw-medium">{{ $guestCount > 0 ? $guestCount : 'No likes' }}</span>
    </button>
</div>
@endauth

@once
<style>
    .like-btn {
        /* Smooth transition for background and color changes */
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.1s ease;
        white-space: nowrap;
    }

    .like-icon-container {
        display: inline-grid;
        place-items: center;
        /* Slight adjustment to align heart with text */
        margin-bottom: -1px; 
    }

    .icon-unliked, .icon-liked {
        grid-area: 1/1;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }

    /* Default State (Unliked) */
    .like-btn .icon-liked { opacity: 0; transform: scale(0.5); }
    .like-btn .icon-unliked { opacity: 1; transform: scale(1); }

    /* Liked State */
    .like-btn.is-liked .icon-liked { opacity: 1; transform: scale(1); }
    .like-btn.is-liked .icon-unliked { opacity: 0; transform: scale(0.5); }

    /* Heart Pop Animation */
    @keyframes heartPulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.4); }
        100% { transform: scale(1); }
    }
    .like-btn.animate-pop .like-icon-container {
        animation: heartPulse 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('submit', function(e) {
        const likeForm = e.target.closest('.ajax-like-form');
        if (!likeForm) return;

        e.preventDefault(); 
        
        const clickedBtn = likeForm.querySelector('.like-btn');
        // Prevent double submissions
        if (clickedBtn.classList.contains('is-processing')) return;

        // 1. Grab the target post ID to find ALL instances on the page
        const postId = likeForm.dataset.postId;
        const matchingForms = document.querySelectorAll(`.ajax-like-form[data-post-id="${postId}"]`);

        // 2. Lock and trigger visual transition preparations on ALL matching buttons
        matchingForms.forEach(form => {
            const btn = form.querySelector('.like-btn');
            btn.classList.add('is-processing');
            
            // Reset animation class to allow re-trigger
            btn.classList.remove('animate-pop');
            // Force reflow to ensure animation restarts if it was already running
            void btn.offsetWidth; 
        });

        fetch(likeForm.action, {
            method: 'POST',
            body: new FormData(likeForm),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            const isLiked = data.status === 'liked';
            
            // Parse current count safely from the clicked form to ensure math is consistent
            const clickedCountBadge = likeForm.querySelector('.like-count');
            let currentCount = parseInt(clickedCountBadge.textContent.trim()) || 0;

            // Calculate the new count once
            if (isLiked) {
                currentCount++;
            } else {
                currentCount = Math.max(0, currentCount - 1);
            }
            
            const newText = currentCount > 0 ? currentCount : 'Like';

            // 3. Loop through all matching instances and sync their appearance perfectly
            matchingForms.forEach(form => {
                const btn = form.querySelector('.like-btn');
                const countBadge = form.querySelector('.like-count');

                // Update UI State
                if (isLiked) {
                    btn.classList.add('btn-primary', 'is-liked');
                    btn.classList.remove('btn-light');
                } else {
                    btn.classList.remove('btn-primary', 'is-liked');
                    btn.classList.add('btn-light');
                }

                // Update Text
                countBadge.textContent = newText;

                // Trigger Animation on all instances
                btn.classList.add('animate-pop');
            });

        })
        .catch(err => {
            console.error(err);
        })
        .finally(() => {
            // 4. Release execution locks across all instances
            matchingForms.forEach(form => {
                form.querySelector('.like-btn').classList.remove('is-processing');
            });
        });
    });
});
</script>
@endonce