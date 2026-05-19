@props(['post', 'showReplies' => true])

@php
    $isReview = method_exists($post, 'isReview') && $post->isReview() && $post->review;
    // Consolidated, efficient auth checks
    $isAdmin = auth()->user()?->is_admin ?? false;
    $canModerate = auth()->check() && (auth()->id() === $post->user_id || $isAdmin);
@endphp

<style>
    .js-post-card {
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform, box-shadow;
    }
    .js-post-card:hover {
        transform: scale(1.015) translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .1) !important;
        z-index: 2;
    }
    /* Admin feature styles imported from main */
    .admin-moderation-outline {
        border: 1px solid rgba(220,53,69,.25)!important;
        box-shadow: 0 0 0 1px rgba(220,53,69,.06), 0 0 18px rgba(220,53,69,.08)!important;
    }
    .admin-tool-btn { min-width: 115px; }
    .pinned-banner { background: linear-gradient(90deg, rgba(255,193,7,.18), rgba(255,193,7,.05)); }
</style>

<div class="animate-fade-in js-post-wrapper js-post-card card shadow-sm mb-4 border-0 overflow-hidden {{ $isReview ? 'd-flex flex-row align-items-stretch' : '' }} {{ $isAdmin ? 'admin-moderation-outline' : '' }}"
    data-post-id="{{ $post->id }}" data-is-review="{{ $isReview ? 'true' : 'false' }}">

    @if($isReview && $post->review->type === 'recommendation')
        <x-post.rating-meter :rating="$post->review->rating ?? 0" />
    @endif

    <div class="flex-grow-1 d-flex flex-column {{ $isReview ? 'bg-white' : '' }}" style="min-width: 0;">

        @if($post->trashed())
            <p class="card-text text-muted fst-italic p-3">[This post has been deleted]</p>
        @else
                {{-- PINNED BANNER --}}
            @if($post->is_pinned)
                <div class="pinned-banner px-3 py-2 border-bottom" style="background: linear-gradient(90deg, rgba(255,193,7,.18), rgba(255,193,7,.05));">
                    <i class="bi bi-pin-angle-fill text-warning me-1"></i>
                    <span class="fw-semibold">Pinned Post</span>
                </div>
            @endif

            {{-- SUSPENDED AUTHOR BANNER --}}
            @if($post->user?->is_suspended)
                <div class="alert alert-danger rounded-0 border-0 mb-0">
                    <i class="bi bi-slash-circle-fill me-1"></i> Author suspended
                </div>
            @endif

            {{-- LOCKED STATUS BANNERS --}}
            @if($post->admin_locked)
                <div class="alert alert-danger rounded-0 border-0 mb-0">
                    <i class="bi bi-shield-lock-fill me-2"></i> This post was locked by an Admin. Replies disabled.
                </div>
            @elseif($post->is_locked)
                <div class="alert alert-secondary rounded-0 border-0 mb-0">
                    <i class="bi bi-lock-fill me-2"></i> The author locked this post. Replies disabled.
                </div>
            @endif
            {{-- Admin Moderation Bar --}}
            @if($isAdmin)
                <div class="px-3 py-2 border-bottom bg-danger-subtle d-flex justify-content-between align-items-center">
                    <div class="small fw-semibold text-danger">
                        <i class="bi bi-shield-lock-fill me-1"></i> ADMIN MODERATION MODE
                    </div>
                    <div>
                        <span class="badge bg-danger">POST #{{ $post->id }}</span>
                    </div>
                </div>
            @endif

            <x-post.header :post="$post" />
            
            <div class="edit_form_collapsable">
                <x-post.content :post="$post" />
                <x-post.footer :post="$post" />
                
                {{-- Moderation Controls --}}
                @if($canModerate)
                    <div class="px-3 pb-3 d-flex gap-2 flex-wrap">
                        {{-- Delete --}}
                        <form method="POST" action="/posts/{{ $post->id }}" onsubmit="return confirm('Delete post?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger admin-tool-btn">
                                <i class="bi bi-trash-fill me-1"></i> Delete
                            </button>
                        </form>

                        {{-- Admin Only Controls --}}
                        @if($isAdmin)
                            {{-- Pin --}}
                            <form method="POST" action="/admin/posts/{{ $post->id }}/pin">
                                @csrf
                                <button class="btn btn-sm {{ $post->is_pinned ? 'btn-warning' : 'btn-outline-warning' }} admin-tool-btn">
                                    <i class="bi bi-pin-angle-fill me-1"></i> {{ $post->is_pinned ? 'Unpin' : 'Pin' }}
                                </button>
                            </form>
                            {{-- Lock --}}
                            <form method="POST" action="/admin/posts/{{ $post->id }}/lock">
                                @csrf
                                <button class="btn btn-sm {{ $post->is_locked ? 'btn-secondary' : 'btn-outline-secondary' }} admin-tool-btn">
                                    <i class="bi {{ $post->is_locked ? 'bi-unlock-fill' : 'bi-lock-fill' }} me-1"></i> {{ $post->is_locked ? 'Unlock' : 'Lock' }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>

            <x-post.edit-form :post="$post" />
        @endif

        @if($showReplies)
            <x-post.reply-container :post="$post">
                {{-- Only allow comments if NOT locked by user and NOT locked by admin --}}
                @if(!$post->is_locked && !$post->admin_locked)
                    <x-post.comment-create :hubType="$post->hubable_type ?? $post->hub_type"
                        :hubId="$post->hubable_id ?? $post->hub_id" :parentId="$post->id" />
                @endif
            </x-post.reply-container>
            
            <x-post.replies-container :postId="$post->id" id="accordion-{{ $post->id }}">
                <x-post.replies-list />
            </x-post.replies-container>
        @endif
        
    </div>
</div>