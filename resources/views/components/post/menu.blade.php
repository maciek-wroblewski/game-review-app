@props(['post'])

@auth
@if(auth()->id() === $post->user_id && !$post->trashed())
<div class="dropdown">
    <button class="btn btn-light btn-sm rounded-circle border-0" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-three-dots"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li><button class="js-btn-edit dropdown-item"><i class="bi bi-pencil me-2"></i>Edit</button></li>
        <li><button class="js-btn-delete dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button></li>
    </ul>
</div>
@endif
@endauth

{{-- JS remains the same as above --}}
@once
<script>
document.addEventListener('click', async (e) => {
    if (!e.target.closest('.js-btn-delete')) return;
    const card = e.target.closest('.js-post-card');
    if (!card || !confirm('Permanently delete this post?')) return;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
        const res = await fetch(`/posts/${card.dataset.postId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } });
        if (res.ok) {
            card.style.transition = 'opacity 0.4s ease';
            card.style.opacity = '0';
            setTimeout(() => card.remove(), 400);
        }
    } catch (err) { console.error(err); }
});
</script>
@endonce
