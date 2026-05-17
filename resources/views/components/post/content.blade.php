@props(['post'])
@php
$height = $post->media->count() > '0' ? '2':'5';
@endphp
<div class="js-view-mode card-body pt-2 position-relative">

    <x-post.spoiler :isSpoiler='$post->is_spoiler'>
        <x-truncate-text :size="$post->media->count() > 0 ? 2 : 5">
            <x-post.text-body :body="$post->body" />
        </x-truncate-text>
        <x-post.media-grid :media="$post->media" />

    </x-post.spoiler>

    @if($post->parent_id)
    <div class="mt-3">
    <x-post.quote :post="$post->parent" :parentIsSpoiler="$post->is_spoiler" />
    </div>
    @endif
</div>