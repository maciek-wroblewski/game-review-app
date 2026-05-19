@props(['hubType', 'hubId', 'posts'])

<div class="hub-comments-wrapper my-4">
    @auth
        <div class="mb-4">
            {{-- Passing down our explicit scoping values to the post-creation card --}}
            <x-post.create-form :hub-type="$hubType" :hub-id="$hubId" />
        </div>
    @else
        <div class="alert alert-secondary text-center small py-3 mb-4">
            Please <a href="{{ route('login') }}" class="fw-bold text-decoration-underline">Log In</a> to join the conversation.
        </div>
    @endauth

    {{-- Unique ID targeted wrapper box matching standard application grids --}}
    <div id="hub-posts-feed-{{ $hubType }}-{{ $hubId }}" class="post-list-wrapper d-flex flex-column gap-3">
        @forelse($posts as $post)
            <x-post.index :post="$post" />
        @empty
            <div class="text-center text-muted small py-4 bg-light rounded border border-dashed text-placeholder">
                No posts shared in this hub yet. Be the first to start the conversation!
            </div>
        @endforelse
    </div>

    {{-- Utilizing your robust native pagination element block --}}
    <div class="mt-2">
        <x-load-more 
            :paginator="$posts" 
            target="#hub-posts-feed-{{ $hubType }}-{{ $hubId }}" 
            buttonClass="btn btn-outline-primary btn-sm px-4 rounded-pill shadow-sm"
            text="Show More Discussions" 
        />
    </div>
</div>