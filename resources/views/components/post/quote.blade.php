@props(['post', 'parentIsSpoiler' => false])

@if($post)
<x-clickable-card :link="'/posts/' . $post->id">
<div class="border rounded p-3 hover-bg-light transition-all position-relative clickable-card" 
     style="background-color: #f8f9fa; cursor: pointer;"
     data-href="/posts/{{ $post->id }}">

    <!-- Header Row: Info left, Like right -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="d-flex align-items-center gap-2">
            <x-user.avatar :user="$post->author" layout="compact" :size="'24px'" />
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
    <x-post.spoiler :isSpoiler='$post->is_spoiler'>
        <!-- Clamped Text -->
        <div class="text-muted" style="font-size: 0.85rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
            {{ $post->body }}
        </div>

        <!-- Media (Limited to 1 for quotes to keep it compact) -->
        @php
        $visualMedia = ($post->media ?? collect())->filter(fn($m) => in_array($m->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'video/mp4']))->take(1);
        @endphp
        @if($visualMedia->isNotEmpty())
        <div class="mt-2 rounded overflow-hidden">
            @foreach($visualMedia as $media)
            @if(str_starts_with($media->mime_type, 'video/'))
            <video src="{{ $media->file_path }}" class="w-100 rounded" muted></video>
            @else
            <img src="{{ $media->file_path }}" class="w-100 rounded" alt="quoted media">
            @endif
            @endforeach
        </div>
        @endif
    </div>
</x-post.spoiler>
</x-clickable-card>
@endif
