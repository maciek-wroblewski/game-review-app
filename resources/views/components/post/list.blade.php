@props(['posts'])

<div class="post-list-wrapper">
    {{-- The Container where new posts will be appended --}}
    <div id="post-container" class="mb-4">
        <x-post.items :posts="$posts" />
    </div>

    {{-- Clean unified declaration --}}
    <x-load-more 
        :paginator="$posts" 
        target="#post-container" 
        text="Load More" 
    />
</div>