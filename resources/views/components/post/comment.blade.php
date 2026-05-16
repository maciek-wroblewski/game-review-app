@props(['post', 'depth' => 0])

<div class="post-wrapper position-relative d-flex flex-row align-items-stretch rounded shadow-sm mb-3 overflow-hidden"
     style="background-image: url('{{ $post->author->banner ?? '' }}'); background-size: cover; background-position: center; margin-left: {{ $depth * 20 }}px;">
    
    <!-- Gradient Overlay (Stronger Left) -->
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to right, rgba(255,255,255,0.98) 0%, rgba(255,255,255,0.7) 40%, rgba(255,255,255,0) 100%); pointer-events: none;"></div>

    <div class="position-relative d-flex flex-row w-100 p-3 gap-3">
        <!-- Avatar -->
        <x-user.avatar :user="$post->author" :size="'36px'" />

        <!-- Main Content Div -->
        <div class="flex-grow-1 d-flex flex-column min-w-0">
            
            <!-- Info Div -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold small">{{ $post->author->username }}</span>
                    <span class="text-muted small" style="font-size: 0.75rem;">{{ $post->updated_at->diffForHumans() }}</span>
                </div>
                @if(auth()->id() === $post->user_id)
                    <div class="dropdown">
                        <button class="btn btn-link btn-sm p-0 text-muted" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><button class="js-btn-edit-post dropdown-item"><i class="bi bi-pencil me-2"></i>Edit</button></li>
                            <li><button class="js-btn-delete-post dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button></li>
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Lower Text / Content -->
            <div class="js-post-view-mode flex-grow-1">
                <x-post.spoiler :is-spoiler="$post->is_spoiler">
                    <x-post.truncate-text :text="$post->body" :max-lines="2" />
                    @if($post->media->count() > 0)
                        <x-post.media-grid :media="$post->media" />
                    @endif
                </x-post.spoiler>

                <!-- Actions -->
                <div class="d-flex gap-2 mt-2">
                    <button class="js-btn-post-reply btn btn-sm btn-light rounded-pill border-0 small"><i class="bi bi-reply me-1"></i>Reply</button>
                    <button class="js-btn-show-replies btn btn-sm btn-light rounded-pill border-0 small"><i class="bi bi-chevron-down me-1"></i>Show Replies</button>
                </div>
            </div>

            <!-- Edit Mode (Replaces content on toggle) -->
            <div class="js-post-edit-mode d-none">
                <x-post.edit-form :post="$post" />
            </div>
        </div>
    </div>
</div>

<!-- Lazy Reply Form Container -->
<div class="js-reply-container position-relative" style="margin-left: {{ ($depth + 1) * 20 }}px; display: none;">
    <!-- Form injected here on click -->
</div>

@once
<script>
document.addEventListener('click', (e) => {
    const post = e.target.closest('.post-wrapper');
    if (!post) return;

    // Edit Toggle
    if (e.target.closest('.js-btn-edit-post')) {
        e.preventDefault(); e.stopPropagation();
        const view = post.querySelector('.js-post-view-mode');
        const edit = post.querySelector('.js-post-edit-mode');
        if (view) view.classList.add('d-none');
        if (edit) edit.classList.remove('d-none');
    }

    // Delete
    if (e.target.closest('.js-btn-delete-post')) {
        e.preventDefault();
        if (!confirm('Permanently delete this post?')) return;
        post.style.transition = 'opacity 0.3s';
        post.style.opacity = '0';
        setTimeout(() => post.remove(), 300);
    }

    // Lazy Reply Form Injection
    if (e.target.closest('.js-btn-post-reply')) {
        e.preventDefault();
        const container = post.nextElementSibling;
        if (container && container.classList.contains('js-reply-container') && container.style.display === 'none') {
            container.style.display = 'block';
            // Inject form only when needed
            container.innerHTML = `@include('components.post.create-form', ['hubType' => $post->hub_type, 'hubId' => $post->hub_id, 'parentId' => $post->id])`;
        }
    }
});
</script>
@endonce
