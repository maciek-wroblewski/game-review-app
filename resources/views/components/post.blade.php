@props(['post'])

@php
$isReview = method_exists($post, 'isReview') && $post->isReview() && $post->review;

$canModerate =
    auth()->check() &&
    (
        auth()->id() === $post->user_id ||
        auth()->user()->is_admin
    );
@endphp

<style>

    .js-post-card {

        transition:
            transform 0.25s cubic-bezier(0.4, 0, 0.2, 1),
            box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1);

        will-change: transform, box-shadow;
    }

    .js-post-card:hover {

        transform: scale(1.015) translateY(-2px);

        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .1) !important;

        z-index: 2;
    }

    .admin-moderation-outline {

        border: 1px solid rgba(220, 53, 69, 0.35) !important;

        box-shadow:
            0 0 0 1px rgba(220, 53, 69, 0.1),
            0 0 18px rgba(220, 53, 69, 0.08) !important;
    }

</style>

<div class="js-post-wrapper js-post-card card shadow-sm mb-4 border-0 overflow-hidden
            {{ $isReview ? 'd-flex flex-row align-items-stretch' : '' }}
            {{ auth()->user()?->is_admin ? 'admin-moderation-outline' : '' }}"
    data-post-id="{{ $post->id }}"
    data-is-review="{{ $isReview ? 'true' : 'false' }}">

    @if($isReview && $post->review->type === 'recommendation')

        <x-post.rating-meter :rating="$post->review->rating ?? 0" />

    @endif

    <div class="flex-grow-1 d-flex flex-column {{ $isReview ? 'bg-white' : '' }}"
         style="min-width: 0;">

        {{-- Deleted --}}
        @if($post->trashed())

            <p class="card-text text-muted fst-italic p-3">

                [This post has been deleted]

            </p>

        @else

            {{-- Admin Moderation Bar --}}
            @if(auth()->user()?->is_admin)

                <div class="px-3 py-2 border-bottom bg-danger-subtle d-flex justify-content-between align-items-center">

                    <div class="small fw-semibold text-danger">

                        <i class="bi bi-shield-lock-fill me-1"></i>

                        ADMIN MODERATION MODE

                    </div>

                    <div>

                        <span class="badge bg-danger">

                            POST #{{ $post->id }}

                        </span>

                    </div>

                </div>

            @endif

            <x-post.header :post="$post" />

            <div class="edit_form_collapsable">

                <x-post.content :post="$post" />

                <x-post.footer :post="$post" />

                {{-- Admin Controls --}}
                @if($canModerate)

                    <div class="px-3 pb-3">

                        <form method="POST"
                              action="/posts/{{ $post->id }}"
                              onsubmit="return confirm('Delete this post?')">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="btn btn-sm btn-outline-danger">

                                <i class="bi bi-trash-fill me-1"></i>

                                Delete Post

                            </button>

                        </form>

                    </div>

                @endif

            </div>

            <x-post.edit-form :post="$post" />

        @endif

        <x-post.reply-container :post="$post">

            <x-post.comment-create
                :hubType="$post->hubable_type ?? $post->hub_type"
                :hubId="$post->hubable_id ?? $post->hub_id"
                :parentId="$post->id" />

        </x-post.reply-container>

        <x-post.replies-container
            :postId="$post->id"
            id="accordion-{{ $post->id }}">

            <x-post.replies-list : />

        </x-post.replies-container>

    </div>

</div>