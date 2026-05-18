@props(['post'])
@php
$isReview = method_exists($post, 'isReview') && $post->isReview() && $post->review;
@endphp

<div class="js-post-wrapper js-post-card card shadow-sm mb-4 border-0 overflow-hidden {{ $isReview ? 'd-flex flex-row align-items-stretch' : '' }}"
    data-post-id="{{ $post->id }}" data-is-review="{{ $isReview ? 'true' : 'false' }}">

    @if($isReview && $post->review->type === 'recommendation')
    <x-post.rating-meter :rating="$post->review->rating ?? 0" />
    @endif

    <div class="flex-grow-1 d-flex flex-column {{ $isReview ? 'bg-white' : '' }}" style="min-width: 0;">
        @if($post->trashed())
        <p class="card-text text-muted fst-italic p-3">[This post has been deleted]</p>
        @else
        <x-post.header :post="$post" />
        <div class="edit_form_collapsable">
            <x-post.content :post="$post" />
            <x-post.footer :post="$post" />
        </div>
        <x-post.edit-form :post="$post" />
        @endif

        {{-- Reply Submission Box --}}
        <x-post.reply-container :post="$post">
            <x-post.comment-create :hubType="$post->hubable_type ?? $post->hub_type"
                :hubId="$post->hubable_id ?? $post->hub_id" :parentId="$post->id" />
        </x-post.reply-container>
        <x-post.replies-container :postId="$post->id" id="accordion-{{ $post->id }}">
            <x-post.replies-list />
        </x-post.replies-container>
    </div>
</div>