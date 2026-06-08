@if(!$isGuest)
<form action="/posts/{{ $post->id }}/like" method="POST" class="m-0 ajax-like-form d-inline-block" data-post-id="{{ $post->id }}" data-like-text="{{ __('posts.like') }}">
    @csrf
    <button type="submit" 
        class="btn btn-sm rounded-pill border-0 small d-flex align-items-center gap-2 like-btn {{ $hasLiked ? 'btn-primary is-liked' : 'btn-light' }}">
        <span class="like-icon-container">
            <i class="bi bi-heart icon-unliked"></i>
            <i class="bi bi-heart-fill icon-liked"></i>
        </span>
        <span class="like-count fw-medium">
            {{ $count > 0 ? $count : __('posts.like') }}
        </span>
    </button>
</form>
@else
<div class="m-0 d-inline-block">
    <button class="btn btn-sm btn-light rounded-pill border-0 small d-flex align-items-center gap-2" disabled>
        <i class="bi bi-heart"></i>
        <span class="fw-medium">{{ $count > 0 ? $count : __('posts.no_likes') }}</span>
    </button>
</div>
@endif