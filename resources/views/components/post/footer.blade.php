@props(['post'])
@if(!$post->trashed())
<div class="js-post-footer card-footer bg-white border-top border-light d-flex justify-content-between align-items-center py-3">
    {{-- Updated: Added data attributes to pass context to the reply component --}}
    <button 
        class="js-btn-reply btn btn-light rounded-pill border shadow-sm d-flex align-items-center gap-2"
        data-hub-type="{{ $post->hubable_type }}"
        data-hub-id="{{ $post->hubable_id }}"
        data-parent-id="{{ $post->parent_id }}">
        <i class="bi bi-chat"></i> Reply
    </button>

    <x-like-button :post="$post" />
</div>
@endif
