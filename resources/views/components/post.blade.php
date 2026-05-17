@props(['post'])
@php
$isReview = method_exists($post, 'isReview') && $post->isReview() && $post->review;
@endphp
<style>
.js-post-card { transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1); will-change: transform, box-shadow; }
.js-post-card:hover { transform: scale(1.015) translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .1) !important; z-index: 2; }
</style>
<div class="js-post-card card shadow-sm mb-4 border-0 overflow-hidden {{ $isReview ? 'd-flex flex-row align-items-stretch' : '' }}"
    data-post-id="{{ $post->id }}" data-is-review="{{ $isReview ? 'true' : 'false' }}">

    @if($isReview && $post->review->type === 'recommendation')
    <x-post.rating-meter :rating="$post->review->rating ?? 0" />
    @endif

    <div class="flex-grow-1 d-flex flex-column {{ $isReview ? 'bg-white' : '' }}" style="min-width: 0;">
        <x-post.header :post="$post" />

        @if($post->trashed())
        <p class="card-text text-muted fst-italic p-3">[This post has been deleted]</p>
        @else
            <x-post.content :post="$post" />
            <x-post.edit-form :post="$post" />
        @endif
        <x-post.footer :post="$post" />
    </div>
</div>
