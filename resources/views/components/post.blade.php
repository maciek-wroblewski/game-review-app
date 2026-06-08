@props(['post', 'showReplies' => true])

@php
    $isReview = $post->isReview();
    $isAdmin = auth()->check() && auth()->user()->is_admin;
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

    .admin-moderation-outline {
        border: 1px solid rgba(220, 53, 69, .25) !important;
        box-shadow: 0 0 0 1px rgba(220, 53, 69, .06), 0 0 18px rgba(220, 53, 69, .08) !important;
    }

    .admin-tool-btn {
        min-width: 115px;
    }

    .pinned-banner {
        background: linear-gradient(90deg, rgba(255, 193, 7, .18), rgba(255, 193, 7, .05));
    }
</style>

<div class="animate-fade-in js-post-wrapper js-post-card card shadow-sm mb-4 border-0 overflow-hidden {{ $isReview ? 'd-flex flex-row align-items-stretch' : '' }} {{ $isAdmin ? 'admin-moderation-outline' : '' }}"
    data-post-id="{{ $post->id }}" data-is-review="{{ $isReview ? 'true' : 'false' }}">

    @if($isReview && $post->review->type === 'recommendation')
    <x-post.rating-meter :rating="$post->review->rating ?? 0" />
    @endif

    <div class="flex-grow-1 d-flex flex-column {{ $isReview ? 'bg-white' : '' }}" style="min-width: 0;">

        @if($post->trashed())
        <p class="card-text text-muted fst-italic p-3">{{ __('common.this_post_has_been_deleted') }}</p>
        @else
        
        @if($post->is_pinned)
        <div class="pinned-banner px-3 py-2 border-bottom"
            style="background: linear-gradient(90deg, rgba(255,193,7,.18), rgba(255,193,7,.05));">
            <i class="bi bi-pin-angle-fill text-warning me-1"></i>
            <span class="fw-semibold">{{ __('common.pinned_post') }}</span>
        </div>
        @endif

        @if($post->author?->is_suspended)
        <div class="alert alert-danger rounded-0 border-0 mb-0">
            <i class="bi bi-slash-circle-fill me-1"></i> {{ __('common.author_suspended') }}
        </div>
        @endif

        @if($isAdmin)
        <div class="px-3 py-2 border-bottom bg-danger-subtle d-flex justify-content-between align-items-center">
            <div class="small fw-semibold text-danger">
                <i class="bi bi-shield-lock-fill me-1"></i> {{ __('common.admin_moderation_mode') }}
            </div>
            <div>
                <span class="badge bg-danger">POST #{{ $post->id }}</span>
            </div>
        </div>
        @endif

        <x-post.header :post="$post" />

        <div class="edit_form_collapsable">
            <x-post.content :post="$post" />
            <x-post.footer :post="$post" showReplies="{{ $showReplies }}" />

            @if($isAdmin)
            <div class="px-3 pb-3 d-flex gap-2 flex-wrap">
                <form method="POST" action="/posts/{{ $post->id }}" onsubmit="return confirm('{{ __('common.confirm_delete_post') }}')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger admin-tool-btn">
                        <i class="bi bi-trash-fill me-1"></i> {{ __('common.delete') }}
                    </button>
                </form>

                <form method="POST" action="/admin/posts/{{ $post->id }}/pin">
                    @csrf
                    <button
                        class="btn btn-sm {{ $post->is_pinned ? 'btn-warning' : 'btn-outline-warning' }} admin-tool-btn">
                        <i class="bi bi-pin-angle-fill me-1"></i> {{ $post->is_pinned ? __('common.unpin') : __('common.pin') }}
                    </button>
                </form>
                
                <form method="POST" action="/admin/posts/{{ $post->id }}/lock">
                    @csrf
                    <button
                        class="btn btn-sm {{ $post->admin_locked ? 'btn-secondary' : 'btn-outline-secondary' }} admin-tool-btn">
                        <i class="bi {{ $post->admin_locked ? 'bi-unlock-fill' : 'bi-lock-fill' }} me-1"></i> {{
                        $post->admin_locked ? __('posts.unlock') : __('posts.lock') }}
                    </button>
                </form>
            </div>
            @endif
        </div>

        <x-post.edit-form :post="$post" />
        @endif


        <x-post.reply-container :post="$post">
            @if(!$post->is_locked && !$post->admin_locked)
            <x-post.create-form :hubType="$post->hubable_type ?? $post->hub_type"
                :hubId="$post->hubable_id ?? $post->hub_id" :parentId="$post->id" />
            @endif
        </x-post.reply-container>
        
        @if($showReplies)
        <x-post.replies-container :postId="$post->id" id="accordion-{{ $post->id }}">
            <x-post.replies-list />
        </x-post.replies-container>
        @endif

    </div>
</div>