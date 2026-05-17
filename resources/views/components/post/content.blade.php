@props(['post'])
@php
$visualMedia = $post->media->filter(fn($m) => in_array($m->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'video/mp4']))->values();
$mediaCount = $visualMedia->count();
$lightboxPayload = $visualMedia->map(fn($m) => ['url' => $m->file_path, 'type' => str_starts_with($m->mime_type, 'video/') ? 'video' : 'image'])->toJson();
@endphp

<x-post.spoiler :isSpoiler="$post->is_spoiler">
    <div class="js-view-mode card-body pt-2 position-relative" data-media-count="{{ $mediaCount }}">
        @if($post->parentPost)
        <div class="border rounded p-3 mb-3 bg-light d-flex flex-column" style="cursor: pointer;" onclick="window.location.href='/posts/{{ $post->parentPost->id }}'">
            <div class="d-flex align-items-center mb-1">
                <img src="{{ $post->parentPost->author->avatar }}" class="rounded-circle me-2" style="width: 20px; height: 20px;">
                <span class="fw-bold small me-1">{{ $post->parentPost->author->username }}</span>
                <span class="text-muted small">· {{ $post->parentPost->created_at->shortAbsoluteDiffForHumans() }}</span>
            </div>
            <p class="mb-0 small text-truncate">{{ $post->parentPost->body }}</p>
        </div>
        @endif

        <x-post.text-body :body="$post->body" />
        <x-post.media-grid :media="$post->media" :lightboxPayload="$lightboxPayload" />

        @if($post->parent_id)
        <x-post.quote :post="$post->parent" :parentIsSpoiler="$post->is_spoiler" />
        @endif
    </div>
</x-post.spoiler>
