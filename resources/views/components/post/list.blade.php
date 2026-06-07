@props([
    'posts', 
    'feedId' => 'post-container', 
    'emptyMessage' => __('posts.no_posts'),
    'loadMoreText' => __('posts.load_more')
])

<div class="post-list-wrapper">
    {{-- The Container where new posts will be appended --}}
    <div id="{{ $feedId }}" class="d-flex flex-column gap-3 mb-4 js-post-list-container">
        
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
            target=".js-post-list-container" 
            buttonClass="btn btn-outline-primary btn-sm px-4 rounded-pill shadow-sm"
            :text="$loadMoreText" 
        />
    </div>
</div>