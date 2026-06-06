export default class LikeButton {
    constructor() {
        document.addEventListener('submit', this.handleSubmit.bind(this));
    }

    handleSubmit(e) {
        const likeForm = e.target.closest('.ajax-like-form');
        if (!likeForm) return;

        e.preventDefault();

        const clickedBtn = likeForm.querySelector('.like-btn');
        if (clickedBtn.classList.contains('is-processing')) return;

        const postId = likeForm.dataset.postId;
        const matchingForms = document.querySelectorAll(`.ajax-like-form[data-post-id="${postId}"]`);

        matchingForms.forEach(form => {
            const btn = form.querySelector('.like-btn');
            btn.classList.add('is-processing');
            btn.classList.remove('animate-pop');
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

            const clickedCountBadge = likeForm.querySelector('.like-count');
            let currentCount = parseInt(clickedCountBadge.textContent.trim()) || 0;

            if (isLiked) {
                currentCount++;
            } else {
                currentCount = Math.max(0, currentCount - 1);
            }

            const likeText = likeForm.dataset.likeText;

            matchingForms.forEach(form => {
                const btn = form.querySelector('.like-btn');
                const countBadge = form.querySelector('.like-count');

                if (isLiked) {
                    btn.classList.add('btn-primary', 'is-liked');
                    btn.classList.remove('btn-light');
                } else {
                    btn.classList.remove('btn-primary', 'is-liked');
                    btn.classList.add('btn-light');
                }

                countBadge.textContent = currentCount > 0 ? currentCount : likeText;
                btn.classList.add('animate-pop');
            });
        })
        .catch(err => {
            console.error(err);
        })
        .finally(() => {
            matchingForms.forEach(form => {
                form.querySelector('.like-btn').classList.remove('is-processing');
            });
        });
    }
}

