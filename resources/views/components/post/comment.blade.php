@props(['post'])
{{-- Main container: Handles post data & state switching between view/edit modes --}}
<div class="js-post-card js-comment-card position-relative rounded shadow-sm mb-3 overflow-hidden"
    data-post-id="{{ $post->id }}" data-is-review="false">

    @if(!$post->trashed())
    {{-- Background Container: Holds image & gradient overlay --}}
    <div class="position-relative w-100"
        style="background-image: url('{{ $post->author->banner ?? '' }}'); background-size: cover; background-position: center;">

        {{-- Gradient Overlay --}}
        <div class="position-absolute top-0 start-0 w-100 h-100"
            style="background: linear-gradient(to right, rgb(255, 255, 255) 0%, rgba(255, 255, 255, 0.855) 40%, rgba(255, 255, 255, 0.213) 100%); pointer-events: none;">
        </div>

        {{-- View Mode wrapped in Clickable Card --}}
        <x-clickable-card :link="'/posts/' . $post->id">
            <div class="js-view-mode position-relative d-flex flex-row w-100 p-3 gap-3">
                <x-user.avatar :user="$post->author" :size="'36px'" />

                <div class="flex-grow-1 d-flex flex-column min-w-0">
                    <!-- Header / Info -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold small">{{ $post->author->username }}</span>
                            <span class="text-muted small" style="font-size: 0.75rem;">{{
                                $post->updated_at->diffForHumans() }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($post->author)
                            <x-follow-button :target-user="$post->author" />
                            @endif
                            <x-post.menu :post="$post" />
                        </div>
                    </div>

                    <!-- Content & Actions -->
                    <x-post.spoiler :is-spoiler="$post->is_spoiler">
                        <x-truncate-text :size="$post->media->count() > 0 ? 1 : 2">
                            <x-post.text-body :body="$post->body" />
                        </x-truncate-text>
                        <x-post.media-grid :media="$post->media" />
                    </x-post.spoiler>

                    <!-- Actions -->
                    <div class="d-flex flex-row  justify-content-between">
                        <div class="d-flex gap-2 mt-2">
                            {{-- 1. The Reply Button --}}
                            @if(!$post->is_locked)
                            <button class="js-btn-comment-reply btn btn-sm btn-light rounded-pill border-0 small"
                                data-hub-type="{{ $post->hubable_type }}" data-hub-id="{{ $post->hubable_id }}"
                                data-parent-id="{{ $post->id }}">
                                <i class="bi bi-reply me-1"></i>Reply
                            </button>
                            @else
                            {{-- Disabled State for Locked Posts --}}
                            <button class="btn btn-sm btn-light rounded-pill border-0 small text-muted disabled"
                                disabled title="This comment is locked">
                                <i class="bi bi-lock-fill me-1"></i>Locked
                            </button>
                            @endif

                            <button class="js-btn-show-replies btn btn-sm btn-light rounded-pill border-0 small">
                                <i class="bi bi-chevron-down me-1"></i>Show Replies
                            </button>
                        </div>
                        <x-like-button :post='$post'/>
                    </div>
                </div>
            </div>
        </x-clickable-card>
    </div>
    @if(!$post->is_locked)
    {{-- 2. Slide-down container for the comment form --}}
    {{-- Positioned outside the clickable card to avoid click conflicts, but inside the main post-card --}}
    <div class="js-reply-container overflow-hidden bg-white border-top border-light"
        style="max-height: 0; opacity: 0; transition: max-height 0.3s ease-out, opacity 0.3s ease-out;">
        <x-post.comment-create :hubType="$post->hubable_type" :hubId="$post->hubable_id" :parentId="$post->id" />
    </div>
    @endif

    {{-- Edit Form Component: Toggles visibility via edit-form.js --}}
    <x-post.edit-form :post="$post" />

    @else
    <p class="text-muted fst-italic small">[This comment has been deleted]</p>
    @endif
</div>

@if(!$post->is_locked)
@once
<script>
    document.addEventListener('click', (e) => {
    // 1. Check if the clicked element is a comment reply button
    const btn = e.target.closest('.js-btn-comment-reply');
    if (!btn) return;

    // 2. Find the specific comment card this button belongs to
    const card = btn.closest('.js-comment-card');
    if (!card) return;

    // 3. Find the sibling reply container within THIS card only
    const container = card.querySelector('.js-reply-container');
    if (!container) return;

    // Prevent default to avoid any parent clickable-card interference
    e.preventDefault();
    e.stopPropagation();

    const isOpen = container.dataset.open === 'true';

    if (!isOpen) {
        container.style.maxHeight = container.scrollHeight + 'px';
        container.style.opacity = '1';
        container.dataset.open = 'true';
    } else {
        container.style.maxHeight = '0';
        container.style.opacity = '0';
        container.dataset.open = 'false';
    }
});
</script>
@endonce
@endif