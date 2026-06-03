export default function initLikeButton() {
    document.addEventListener('click', function(e) {
        const likeBtn = e.target.closest('.ajax-like-btn');
        if (!likeBtn) return;

        e.preventDefault();

        const targetId = likeBtn.dataset.targetId;
        const targetType = likeBtn.dataset.targetType;
        const matchingBtns = document.querySelectorAll(`.ajax-like-btn[data-target-id="${targetId}"][data-target-type="${targetType}"]`);

        matchingBtns.forEach(btn => {
            if (btn.classList.contains('is-processing')) return;
            btn.classList.add('is-processing');
            btn.querySelector('.like-count').classList.add('is-flipping');
        });

        fetch(likeBtn.dataset.url, {
            method: 'POST',
            body: new FormData(likeBtn.closest('form')),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            matchingBtns.forEach(btn => {
                const countEl = btn.querySelector('.like-count');
                const iconEl = btn.querySelector('.like-icon');

                countEl.textContent = data.likes.toLocaleString();
                countEl.classList.remove('is-flipping');

                if (data.liked) {
                    btn.classList.add('is-liked');
                    if (iconEl) iconEl.classList.replace('bi-heart', 'bi-heart-fill');
                } else {
                    btn.classList.remove('is-liked');
                    if (iconEl) iconEl.classList.replace('bi-heart-fill', 'bi-heart');
                }

                btn.classList.remove('is-processing');
            });
        })
        .catch(error => {
            console.error('Error:', error);
            matchingBtns.forEach(btn => {
                btn.querySelector('.like-count').classList.remove('is-flipping');
                btn.classList.remove('is-processing');
            });
        });
    });
}
