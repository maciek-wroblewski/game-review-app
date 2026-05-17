@props(['post', 'parentIsSpoiler' => false])

@php
$height = $post->media->count() > '0' ? '1':'3';
@endphp

@if($post)
<x-clickable-card :link="'/posts/' . $post->id">
    <div class="border rounded p-3 hover-bg-light" 
         style="background-color: #f8f9fa; cursor: pointer;"
         data-href="/posts/{{ $post->id }}">

        <!-- Header Row: Info left, Like right -->

        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
                <x-user.avatar :user="$post->author ?? $post->user" layout="compact" :size="'24px'" />
                <span class="fw-semibold" style="font-size: 0.9rem;">
                    {{ $post->author->username ?? $post->user->username ?? 'Anonymous' }}
                </span>
                <span class="text-muted" style="font-size: 0.8rem;">
                    &middot; {{ $post->created_at->diffForHumans(null, true, true) }}
                </span>
            </div>

            <!-- Like Button -->
            <div onclick="event.stopPropagation()">
                <x-like-button :post="$post" />
            </div>
        </div>

        <!-- Content Wrapper -->
        <x-post.spoiler :isSpoiler="$post->is_spoiler">
            <div class="d-flex flex-column">
                <!-- Text Body Component -->
                <x-truncate-text :size="$post->media->count() > 0 ? 2 : 5">
                <x-post.text-body :body="$post->body" />
                </x-truncate-text>
                <!-- Media Grid Component -->
                <x-post.media-grid :media="$post->media" />
            </div>
        </x-post.spoiler>
    </div>
</x-clickable-card>
@endif
