@props(['post'])

<div class="card shadow-sm mb-4 border-0">
    {{-- Post Header --}}
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom-0">
        <div class="d-flex align-items-center">
            {{-- Avatar --}}
            <a href="/users/{{ $post->author->username ?? '#' }}">
                <img src="{{ $post->author->avatar ?? asset('images/default-avatar.png') }}" 
                     alt="{{ $post->author->username ?? 'Anonymous' }} avatar" 
                     class="rounded-circle me-3 border" 
                     style="width: 48px; height: 48px; object-fit: cover;">
            </a>
            
            {{-- User Info & Dates --}}
            <div>
                <div class="d-flex align-items-center">
                    <a href="/users/{{ $post->author->username ?? '#' }}" class="text-decoration-none fw-bold text-dark fs-5 me-2">
                        {{ $post->author->username ?? 'Anonymous User' }}
                    </a>
                    @if(optional($post->author)->verified)
                        <i class="bi bi-patch-check-fill text-primary" title="Verified"></i>
                    @endif
                </div>
                <div class="text-muted small d-flex flex-wrap gap-2">
                    <span>{{ $post->created_at->format('M d, Y h:i A') }}</span>
                    @if($post->created_at->ne($post->updated_at))
                        <span class="fst-italic" title="Edited on {{ $post->updated_at->format('M d, Y h:i A') }}">
                            (Edited)
                        </span>
                    @endif
                    @if($post->hub)
                         <span class="text-secondary">&bull;</span>
                         <span>Posted in <a href="/games/{{ $post->hub->id }}" class="text-decoration-none">{{ $post->hub->title ?? 'Hub' }}</a></span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Misc Actions (Follow/Edit/Delete) --}}
        <div class="d-flex align-items-center gap-2">
            {{-- Extracted Follow Action Component --}}
            @if($post->author)
                <x-follow-button :target-user="$post->author" />
            @endif

            @auth
                @if(auth()->id() === $post->user_id)
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-circle border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a class="dropdown-item" href="/posts/{{ $post->id }}/edit"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                            <li>
                                <form action="/posts/{{ $post->id }}" method="POST" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Delete this post?')">
                                        <i class="bi bi-trash me-2"></i>Delete
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
            @endauth
        </div>
    </div>

    {{-- Post Body --}}
    <div class="card-body pt-2 position-relative spoiler-container" x-data="{ expanded: false }">
        @if($post->trashed())
            <p class="card-text text-muted fst-italic">[This post has been deleted]</p>
        @else
            @if($post->is_spoiler)
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-75 text-white rounded spoiler-overlay">
                    <div class="text-center">
                        <i class="bi bi-eye-slash fs-3 mb-2 d-block"></i>
                        <span class="fw-bold">Spoiler Content</span><br>
                        <small>Hover to reveal</small>
                    </div>
                </div>
                <style>
                    .spoiler-overlay {
                        z-index: 10;
                        backdrop-filter: blur(5px);
                        transition: opacity 0.2s ease, visibility 0.2s ease;
                        opacity: 1;
                        visibility: visible;
                    }

                    .spoiler-container:hover .spoiler-overlay {
                        opacity: 0;
                        visibility: hidden;
                        pointer-events: none; 
                    }
                </style>
            @endif

            {{-- Text Content --}}
            <div class="{{ $post->media->count() > 0 ? 'text-truncate-container' : '' }}" 
                 :class="{ 'text-truncate-container': !expanded && {{ $post->media->count() > 0 ? 'true' : 'false' }}, 'd-block': expanded || {{ $post->media->count() == 0 ? 'true' : 'false' }} }">
                <p class="card-text fs-5 mb-3" style="white-space: pre-line;">
                    {{ $post->body }}
                </p>
            </div>

            @if($post->media->count() > 0)
                <button x-show="!expanded" @click="expanded = true" class="btn btn-link text-decoration-none p-0 mb-3 fw-bold">
                    Read more...
                </button>
            @endif

            {{-- Media Rendering --}}
            @if($post->media->count() > 0)
                @php
                    $visualMedia = $post->media->filter(fn($m) => in_array($m->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'video/mp4']));
                    $otherMedia = $post->media->reject(fn($m) => in_array($m->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'video/mp4']));
                @endphp

                {{-- Visual Media Grid/Carousel --}}
                @if($visualMedia->count() > 0)
                    <div class="mb-3 rounded overflow-hidden border">
                        @if($visualMedia->count() == 1)
                            <img src="{{ $visualMedia->first()->file_path }}" class="img-fluid w-100" style="max-height: 500px; object-fit: contain; background: #f8f9fa;">
                        @elseif($visualMedia->count() == 2)
                            <div class="row g-0">
                                @foreach($visualMedia as $media)
                                    <div class="col-6">
                                        <img src="{{ $media->file_path }}" class="img-fluid w-100 h-100" style="object-fit: cover; aspect-ratio: 1;">
                                    </div>
                                @endforeach
                            </div>
                        @elseif($visualMedia->count() == 3)
                            <div class="row g-0">
                                <div class="col-6">
                                     <img src="{{ $visualMedia->values()[0]->file_path }}" class="img-fluid w-100 h-100" style="object-fit: cover; min-height: 100%;">
                                </div>
                                <div class="col-6 d-flex flex-column">
                                    <img src="{{ $visualMedia->values()[1]->file_path }}" class="img-fluid w-100 border-bottom border-white" style="object-fit: cover; aspect-ratio: 2/1;">
                                    <img src="{{ $visualMedia->values()[2]->file_path }}" class="img-fluid w-100" style="object-fit: cover; aspect-ratio: 2/1;">
                                </div>
                            </div>
                        @elseif($visualMedia->count() == 4)
                            <div class="row g-0">
                                @foreach($visualMedia as $media)
                                    <div class="col-6 {{ $loop->iteration < 3 ? 'border-bottom border-white' : '' }}">
                                        <img src="{{ $media->file_path }}" class="img-fluid w-100" style="object-fit: cover; aspect-ratio: 1;">
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div id="carouselPost{{ $post->id }}" class="carousel slide" data-bs-ride="false">
                                <div class="carousel-inner">
                                    @foreach($visualMedia as $index => $media)
                                        <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                            <img src="{{ $media->file_path }}" class="d-block w-100" style="max-height: 500px; object-fit: contain; background: #f8f9fa;">
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselPost{{ $post->id }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselPost{{ $post->id }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Other File Attachments --}}
                @if($otherMedia->count() > 0)
                    <div class="d-flex flex-column gap-2 mt-3">
                        @foreach($otherMedia as $media)
                            <a href="{{ $media->file_path }}" download class="btn btn-outline-secondary d-flex align-items-center justify-content-start text-start p-2">
                                <i class="bi bi-file-earmark-arrow-down fs-4 me-3"></i>
                                <div>
                                    <div class="fw-bold text-truncate" style="max-width: 200px;">Attachment {{ $loop->iteration }}</div>
                                    <small class="text-muted">{{ strtoupper(explode('/', $media->mime_type)[1] ?? 'FILE') }}</small>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            @endif
        @endif
    </div>

    {{-- Post Footer --}}
    @if(!$post->trashed())
        <div class="card-footer bg-white border-top border-light d-flex justify-content-between align-items-center py-3">
            {{-- Comment/Reply Action --}}
            <div>
                @if(!$post->is_locked)
                    <button class="btn btn-light rounded-pill border shadow-sm d-flex align-items-center gap-2" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#replyForm{{ $post->id }}">
                        <i class="bi bi-chat"></i> 
                        <span>Reply</span>
                    </button>
                @else
                    <span class="text-muted small"><i class="bi bi-lock-fill me-1"></i> Comments locked</span>
                @endif
            </div>

            {{-- Extracted Likes Action Component --}}
            <x-like-button :post="$post" />
        </div>

        {{-- Slide-in Comment Form (WIP Placeholder) --}}
        @if(!$post->is_locked)
            <div class="collapse border-top" id="replyForm{{ $post->id }}">
                <div class="p-3 bg-light rounded-bottom">
                    <div class="text-muted small mb-2">Replying to {{ $post->author->username ?? 'User' }}</div>
                    <textarea class="form-control mb-2" rows="2" placeholder="Write a reply..."></textarea>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary btn-sm rounded-pill px-4">Post Reply</button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

{{-- Required CSS for truncation --}}
<style>
    .text-truncate-container {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>