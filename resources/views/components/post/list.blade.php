@props([
    'posts', 
    'feedId' => 'post-container', 
    'emptyMessage' => 'No posts to show yet.',
    'loadMoreText' => 'Load More'
])

<div class="post-list-wrapper">
    {{-- The Container where new posts will be appended --}}
    <div id="{{ $feedId }}" class="d-flex flex-column gap-3 mb-4">
        
        @if($posts->isNotEmpty())
            {{-- Render the exact same partial the AJAX request returns --}}
            <x-post.items :posts="$posts" />
        @else
            <div class="text-center text-muted small py-4 bg-light rounded border border-dashed text-placeholder">
                {{ $emptyMessage }}
            </div>
        @endif

    </div>

    {{-- Clean unified declaration --}}
    <div class="mt-2">
        <x-load-more 
            :paginator="$posts" 
            target="#{{ $feedId }}" 
            buttonClass="btn btn-outline-primary btn-sm px-4 rounded-pill shadow-sm"
            :text="$loadMoreText" 
        />
    </div>
</div>