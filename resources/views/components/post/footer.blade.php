@props(['post'])
@if(!$post->trashed())

{{-- Footer stays fixed --}}
<div
    class="js-post-footer card-footer bg-white border-top border-light d-flex justify-content-between align-items-center py-3">
    <div class="d-flex column-gap-2"">
        <x-post.reply-toggle :post="$post" />
        <x-post.replies-toggle :post="$post" />
    </div>
    <x-like-button :post='$post' />
</div>

@endif