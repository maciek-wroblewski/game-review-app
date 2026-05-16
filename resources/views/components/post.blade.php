@props(['post', 'layout' => 'full'])

@php
    $isReview = method_exists($post, 'isReview') && $post->isReview() && $post->review;
@endphp

<style>
    /* 1. Smooth Scaling/Resizing of the whole card (e.g., on Hover) */
.js-post-card {
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform, box-shadow; /* Forces GPU acceleration */
}

.js-post-card:hover {
    transform: scale(1.015) translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important;
    z-index: 2; /* Keeps scaled card above others */
}

/* 2. Smooth Height Expansion (for Reply form or Read More collapsing) */
/* Replaces standard Bootstrap collapse for smoother animation */
.collapse:not(.show) {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows 0.3s ease-out;
}

.collapse.show {
    display: grid;
    grid-template-rows: 1fr;
    transition: grid-template-rows 0.3s ease-in;
}

/* The inner div must have overflow hidden for the grid trick to work */
.collapse > div {
    overflow: hidden;
}
</style>
<div class="js-post-card card shadow-sm mb-4 border-0 overflow-hidden {{ $isReview ? 'd-flex flex-row align-items-stretch' : '' }}" 
     data-post-id="{{ $post->id }}" 
     data-is-review="{{ $isReview ? 'true' : 'false' }}">

    @if($isReview && $post->review->type === 'recommendation')
        <x-post.rating :rating="$post->review->rating ?? 0" />
    @endif

    <div class="w-100 flex-grow-1 d-flex flex-column {{ $isReview ? 'bg-white' : '' }}">
        <x-post.header :post="$post" />

            @if($post->trashed())
                <p class="card-text text-muted fst-italic">[This post has been deleted]</p>
            @else
                <div class="card-body pt-2 position-relative spoiler-container">
                    @if($post->is_spoiler)
                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-75 text-white rounded spoiler-overlay">
                            <div class="text-center"><i class="bi bi-eye-slash fs-3 mb-2 d-block"></i><span class="fw-bold">Spoiler Content</span></div>
                        </div>
                    @endif

                    <!-- Main Post Content -->
                    <x-post.content :post="$post" />
                    
                    <!-- Quoted Parent Post (Twitter-style Quote) -->
                    @if($post->parent_id && $post->parent)
                        <x-post.quote :post="$post->parent" />
                    @endif
                </div>

                <!-- Edit Form -->
                <x-post.edit-form :post="$post" />
            @endif

        @if(!$post->trashed())
            <div class="js-post-footer card-footer bg-white border-top border-light d-flex justify-content-between align-items-center py-3">
                {{-- There will be 2 buttons. The one below that then slides down a text box (with attachment picker) that allows user to reply to this post
                adds a form FOR LOGGED USERS that lets user create a post --}}
                <button class="btn btn-light rounded-pill border shadow-sm d-flex align-items-center gap-2"><i class="bi bi-chat"></i> Reply</button>
                {{-- the second one, minor one will just slide down the comments section. THIS COMMENT SECTION IS OUR MICRO COMPONENT THAT WE WILL DO RIGHT NOW.  --}}
                <x-like-button :post="$post" />
            </div>
        @endif
    </div>
</div>

