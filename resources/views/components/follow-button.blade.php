@props(['targetUser', 'buttonClasses' => 'btn-sm rounded-pill px-3 shadow-sm'])

@if(auth()->check() && auth()->id() !== $targetUser->id)
<!-- Added data-user-id attribute for global synchronization -->
<form action="/users/{{ $targetUser->id }}/follow" method="POST" class="m-0 ajax-follow-form d-inline-block"
    data-user-id="{{ $targetUser->id }}">
    @csrf
    @php
    $isFollowing = auth()->user()->following->contains($targetUser);
    @endphp
    <button type="submit"
        class="btn {{ $buttonClasses }} follow-btn {{ $isFollowing ? 'btn-outline-secondary' : 'btn-primary' }}">
        <span class="follow-text d-inline-block">{{ $isFollowing ? 'Unfollow' : 'Follow' }}</span>
    </button>
</form>
@endif

@once
<style>
    /* 1. Smooth Core Button Morphing */
    .follow-btn {
        transition: background-color 0.25s cubic-bezier(0.4, 0, 0.2, 1),
            border-color 0.25s cubic-bezier(0.4, 0, 0.2, 1),
            color 0.25s cubic-bezier(0.4, 0, 0.2, 1),
            transform 0.15s ease !important;
        position: relative;
    }

    /* Tactile shrink effect when clicked */
    .follow-btn:active {
        transform: scale(0.95) !important;
    }

    /* 2. Text Pop Interaction Styles */
    .follow-text {
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1),
            opacity 0.2s ease;
    }

    /* Temporary state class applied to animate text swap smoothly */
    .text-changing {
        transform: scale(0.7);
        opacity: 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('submit', function(e) {
                const followForm = e.target.closest('.ajax-follow-form');
                if (followForm) {
                    e.preventDefault(); 
                    
                    const clickedBtn = followForm.querySelector('.follow-btn');
                    if (clickedBtn.classList.contains('is-processing')) return;

                    // 1. Grab the target user ID to find ALL instances on the page
                    const targetUserId = followForm.dataset.userId;
                    const matchingForms = document.querySelectorAll(`.ajax-follow-form[data-user-id="${targetUserId}"]`);

                    // 2. Lock and trigger visual transition preparations on ALL matching buttons
                    matchingForms.forEach(form => {
                        let btn = form.querySelector('.follow-btn');
                        let text = form.querySelector('.follow-text');
                        
                        btn.classList.add('is-processing');
                        text.classList.add('text-changing');
                    });

                    fetch(followForm.action, {
                        method: 'POST',
                        body: new FormData(followForm),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        // 3a. Loop through all matching instances and sync their appearance perfectly
                        matchingForms.forEach(form => {
                            let btn = form.querySelector('.follow-btn');
                            let text = form.querySelector('.follow-text');

                            if (data.status === 'followed') {
                                btn.classList.replace('btn-primary', 'btn-outline-secondary');
                                text.textContent = 'Unfollow';
                            } else {
                                btn.classList.replace('btn-outline-secondary', 'btn-primary');
                                text.textContent = 'Follow';
                            }

                            // Remove text scaling state to drop it cleanly back into view
                            setTimeout(() => {
                                text.classList.remove('text-changing');
                            }, 50);
                        });

                        // 3b. Sync the follower counts across the page
                        const followerCounters = document.querySelectorAll(`.followers-count[data-user-id="${targetUserId}"]`);

                        followerCounters.forEach(counter => {
                            let currentCount = parseInt(counter.textContent.replace(/,/g, '') || 0, 10);
                            
                            if (data.status === 'followed') {
                                currentCount++;
                            } else {
                                currentCount = Math.max(0, currentCount - 1);
                            }
                            
                            // 1. Trigger the flip out (rotates 90 degrees)
                            counter.classList.add('is-flipping');
                            
                            // 2. Wait exactly 150ms (matching the CSS transition)
                            setTimeout(() => {
                                // Change the number while it is temporarily "invisible" at 90 degrees
                                counter.textContent = currentCount; 
                                
                                // 3. Remove the class to trigger the flip back in
                                counter.classList.remove('is-flipping');
                            }, 150); 
                        });
                    })
                    .catch(err => console.error(err))
                    .finally(() => {
                        // 4. Release execution locks across all instances
                        matchingForms.forEach(form => {
                            form.querySelector('.follow-btn').classList.remove('is-processing');
                        });
                    });
                }
            });
        });
</script>
@endonce