@props(['post'])

@auth
<form action="/posts/{{ $post->id }}/like" method="POST" class="m-0 ajax-like-form d-inline-block">
    @csrf
    @php
        $hasLiked = $post->likes()->where('user_id', auth()->id())->exists();
        $count = $post->likes_count ?? 0;
    @endphp
    
    <button type="submit" class="btn rounded-pill border shadow-sm d-flex align-items-center gap-2 like-btn {{ $hasLiked ? 'btn-primary is-liked' : 'btn-light' }}">
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
    <button class="btn btn-light rounded-pill border shadow-sm d-flex align-items-center gap-2" disabled>
        <i class="bi bi-heart"></i>
        <span class="fw-medium">{{ $guestCount > 0 ? $guestCount : 'Like' }}</span>
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
            
            const btn = likeForm.querySelector('.like-btn');
            const countBadge = likeForm.querySelector('.like-count');

            // Prevent double submissions
            if (btn.classList.contains('is-processing')) return;
            btn.classList.add('is-processing');

            // Reset animation class to allow re-trigger
            btn.classList.remove('animate-pop');
            // Force reflow to ensure animation restarts if it was already running
            void btn.offsetWidth; 

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
                
                // Parse current count safely
                let currentCount = parseInt(countBadge.textContent.trim()) || 0;

                // Update Count
                if (isLiked) {
                    currentCount++;
                    btn.classList.add('btn-primary', 'is-liked');
                    btn.classList.remove('btn-light');
                } else {
                    currentCount--;
                    btn.classList.remove('btn-primary', 'is-liked');
                    btn.classList.add('btn-light');
                }

                // Update Text
                countBadge.textContent = currentCount > 0 ? currentCount : 'Like';

                // Trigger Animation only if the state actually changed visually
                // (We can assume it changed if we got a valid response)
                btn.classList.add('animate-pop');

            })
            .catch(err => {
                console.error(err);
                // Optional: Revert UI if error occurred
            })
            .finally(() => {
                btn.classList.remove('is-processing');
            });
        });
    });
</script>
@endonce
