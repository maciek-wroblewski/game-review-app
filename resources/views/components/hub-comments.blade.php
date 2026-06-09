@props(['hubType', 'hubId', 'posts'])

<div class="hub-comments-wrapper my-4">
        <div class="mb-4">
            {{-- Passing down our explicit scoping values to the post-creation card --}}
            <x-post.form :hub-type="$hubType" :hub-id="$hubId" />
        </div>

    {{-- Pass the heavy lifting to our unified post list component! --}}
    <x-post.list 
        :posts="$posts" 
        feedId="hub-posts-feed-{{ $hubType }}-{{ $hubId }}"
        emptyMessage="{{ __('posts.hub_empty') }}"
        loadMoreText="{{ __('posts.load_more') }}"
    />
</div>