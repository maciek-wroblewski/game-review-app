export default function initFollowButton() {
    document.addEventListener('submit', function(e) {
        const followForm = e.target.closest('.ajax-follow-form');
        if (!followForm) return;
        
        e.preventDefault(); 
        
        const clickedBtn = followForm.querySelector('.follow-btn');
        if (clickedBtn.classList.contains('is-processing')) return;

        const targetUserId = followForm.dataset.userId;
        const matchingForms = document.querySelectorAll(`.ajax-follow-form[data-user-id="${targetUserId}"]`);

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
            matchingForms.forEach(form => {
                let btn = form.querySelector('.follow-btn');
                let text = form.querySelector('.follow-text');

                if (data.status === 'followed') {
                    btn.classList.replace('btn-primary', 'btn-outline-secondary');
                    text.textContent = window.LANG.unfollow || 'Unfollow';
                } else {
                    btn.classList.replace('btn-outline-secondary', 'btn-primary');
                    text.textContent = window.LANG.follow || 'Follow';
                }

                setTimeout(() => {
                    text.classList.remove('text-changing');
                }, 50);
            });

            const followerCounters = document.querySelectorAll(`.followers-count[data-user-id="${targetUserId}"]`);

            followerCounters.forEach(counter => {
                let currentCount = parseInt(counter.textContent.replace(/,/g, '') || 0, 10);
                
                if (data.status === 'followed') {
                    currentCount++;
                } else {
                    currentCount = Math.max(0, currentCount - 1);
                }
                
                counter.classList.add('is-flipping');
                
                setTimeout(() => {
                    counter.textContent = currentCount.toLocaleString();
                    counter.classList.remove('is-flipping');
                }, 200);
            });

            matchingForms.forEach(form => {
                let btn = form.querySelector('.follow-btn');
                btn.classList.remove('is-processing');
            });
        })
        .catch(error => {
            console.error('Error:', error);
            matchingForms.forEach(form => {
                let btn = form.querySelector('.follow-btn');
                let text = form.querySelector('.follow-text');
                btn.classList.remove('is-processing');
                text.classList.remove('text-changing');
            });
        });
    });
}
