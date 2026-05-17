@props(['post'])
@if(!$post->trashed())

{{-- Footer stays fixed --}}
<div class="js-post-footer card-footer bg-white border-top border-light d-flex justify-content-between align-items-center py-3">
    <x-post.reply-toggle :post="$post" />
    <x-like-button :post='$post' />
</div>
<x-post.reply-container :post="$post" />

@endif
