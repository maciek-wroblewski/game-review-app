@props(['post'])

@auth
    <form action="/posts/{{ $post->id }}/like" method="POST" class="m-0 ajax-like-form d-inline-block">
        @csrf
        @php
            $hasLiked = $post->likes()->where('user_id', auth()->id())->exists();
            $hasCount = $post->likes_count > 0;
        @endphp
        
        <button type="submit" class="btn btn-sm shadow-sm rounded-pill px-3 like-btn {{ $hasLiked ? 'btn-primary text-white' : 'btn-light border' }}">
            <i class="bi {{ $hasLiked ? 'bi-heart-fill' : 'bi-heart' }} me-1 like-icon"></i>
            <span class="like-text">{{ $hasLiked ? 'Liked' : 'Like' }}</span>
            
            <!-- Replaced inline display:none with a conditional 'is-empty' CSS class for smooth transitions -->
            <span class="badge bg-white text-dark ms-1 rounded-pill like-count {{ $hasCount ? '' : 'is-empty' }}">
                {{ $hasCount ? $post->likes_count : '' }}
            </span>
        </button>
    </form>
@else
    <button class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 d-inline-block" disabled>
        <i class="bi bi-heart me-1"></i>
        @if($post->likes_count > 0)
            <span class="badge bg-white text-dark ms-1 rounded-pill">{{ $post->likes_count }}</span>
        @endif
    </button>
@endauth

@once
    <style>
        /* 1. Base Button Smooth Morphing */
        .like-btn {
            transition: background-color 0.3s cubic-bezier(0.25, 1, 0.5, 1),
                        border-color 0.3s cubic-bezier(0.25, 1, 0.5, 1),
                        color 0.3s cubic-bezier(0.25, 1, 0.5, 1),
                        box-shadow 0.3s cubic-bezier(0.25, 1, 0.5, 1) !important;
            position: relative;
            overflow: hidden;
        }

        /* 2. Heart Pop Keyframe Animation */
        @keyframes heartPulse {
            0% { transform: scale(1); }
            30% { transform: scale(1.45); }
            60% { transform: scale(0.85); }
            100% { transform: scale(1); }
        }

        .like-icon {
            display: inline-block;
            transition: transform 0.2s ease;
        }

        /* Class applied dynamically via JS on toggle */
        .heart-animate {
            animation: heartPulse 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        /* 3. Fluid Badge Transitions */
        .like-count {
            display: inline-block;
            vertical-align: middle;
            opacity: 1;
            transform: scale(1);
            max-width: 80px; /* High enough value to accommodate large numbers */
            transition: opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                        transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1),
                        max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                        margin 0.25s ease,
                        padding 0.25s ease;
        }

        /* Hidden State Class to morph values safely without display: none */
        .like-count.is-empty {
            opacity: 0;
            transform: scale(0.4);
            max-width: 0;
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-left: 0 !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('submit', function(e) {
                const likeForm = e.target.closest('.ajax-like-form');
                if (likeForm) {
                    e.preventDefault(); 
                    
                    let btn = likeForm.querySelector('.like-btn');
                    let icon = likeForm.querySelector('.like-icon');
                    let text = likeForm.querySelector('.like-text');
                    let countBadge = likeForm.querySelector('.like-count');

                    // Prevent rapid multiple submissons during animation runtime
                    if (btn.classList.contains('is-processing')) return;
                    btn.classList.add('is-processing');

                    // Trigger structural icon pop preparation
                    icon.classList.remove('heart-animate');
                    void icon.offsetWidth; // Reflow trigger to reset CSS animation state
                    icon.classList.add('heart-animate');

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
                        let currentCount = parseInt(countBadge.textContent.trim()) || 0;

                        if (data.status === 'liked') {
                            btn.classList.replace('btn-light', 'btn-primary');
                            btn.classList.replace('border', 'text-white');
                            icon.classList.replace('bi-heart', 'bi-heart-fill');
                            text.textContent = 'Liked';
                            
                            currentCount++;
                        } else {
                            btn.classList.replace('btn-primary', 'btn-light');
                            btn.classList.replace('text-white', 'border');
                            icon.classList.replace('bi-heart-fill', 'bi-heart');
                            text.textContent = 'Like';
                            
                            currentCount--;
                        }

                        // UI Counter Synchronization matching CSS transitions
                        if (currentCount > 0) {
                            countBadge.textContent = currentCount;
                            countBadge.classList.remove('is-empty');
                        } else {
                            countBadge.classList.add('is-empty');
                            // Delay text removal slightly to let the fade/shrink animation finish smoothly
                            setTimeout(() => {
                                if (countBadge.classList.contains('is-empty')) {
                                    countBadge.textContent = '';
                                }
                            }, 250);
                        }
                    })
                    .catch(err => console.error(err))
                    .finally(() => {
                        btn.classList.remove('is-processing');
                    });

                    // Cleanup animation class after completion to preserve memory lifecycle
                    icon.addEventListener('animationend', function() {
                        icon.classList.remove('heart-animate');
                    }, { once: true });
                }
            });
        });
    </script>
@endonce