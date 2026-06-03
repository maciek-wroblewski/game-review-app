export default class FollowButton {
    constructor() {
        document.addEventListener('submit', (e) => {
            const followForm = e.target.closest('.ajax-follow-form');
            if (!followForm) return;

            e.preventDefault();

            const clickedBtn = followForm.querySelector('.follow-btn');
            if (clickedBtn.classList.contains('is-processing')) return;

            const targetUserId = followForm.dataset.userId;
            const matchingForms = document.querySelectorAll(`.ajax-follow-form[data-user-id="${targetUserId}"]`);

            matchingForms.forEach(form => {
                const btn = form.querySelector('.follow-btn');
                const text = form.querySelector('.follow-text');

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
                const followText = followForm.dataset.followText;
                const unfollowText = followForm.dataset.unfollowText;

                matchingForms.forEach(form => {
                    const btn = form.querySelector('.follow-btn');
                    const text = form.querySelector('.follow-text');

                    if (data.status === 'followed') {
                        btn.classList.replace('btn-primary', 'btn-outline-secondary');
                        text.textContent = unfollowText;
                    } else {
                        btn.classList.replace('btn-outline-secondary', 'btn-primary');
                        text.textContent = followText;
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
                        counter.textContent = currentCount;
                        counter.classList.remove('is-flipping');
                    }, 150);
                });
            })
            .catch(err => console.error(err))
            .finally(() => {
                matchingForms.forEach(form => {
                    form.querySelector('.follow-btn').classList.remove('is-processing');
                });
            });
        });
    }
}
