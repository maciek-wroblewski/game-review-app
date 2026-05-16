@props(['post', 'parentIsSpoiler' => false])

@if($post)
    <!-- 
        1. Added data-href for the redirect URL 
        2. Added 'clickable-card' class to match the header's styling/JS target
        3. Added cursor: pointer inline style (or via CSS class)
    -->
    <div class="border rounded p-3 hover-bg-light transition-all position-relative clickable-card" 
         style="background-color: #f8f9fa; cursor: pointer;"
         data-href="/posts/{{ $post->id }}">
        
        <!-- Header: Avatar, Username, and Date -->
        <div class="d-flex align-items-center gap-2 mb-2">
            <x-user.avatar :user="$post->author" layout="compact" :size="'24px'" />
            <span class="fw-semibold" style="font-size: 0.9rem;">
                {{ $post->author->username ?? $post->user->username ?? 'Anonymous' }}
            </span>
            <span class="text-muted" style="font-size: 0.8rem;">
                &middot; {{ $post->created_at->diffForHumans(null, true, true) }}
            </span>
        </div>

        <!-- Content Wrapper -->
        <div class="position-relative spoiler-container">
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

            <!-- Spoiler Overlay -->
            @if($post->is_spoiler && !$parentIsSpoiler)
                {{-- Added z-index: 1 so main post overlay (z-index: 10) sits on top --}}
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-75 text-white rounded spoiler-overlay" style="z-index: 1;">
                    <div class="text-center">
                        <i class="bi bi-eye-slash fs-5 mb-1 d-block"></i>
                        <span class="fw-bold small">Spoiler Content</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
