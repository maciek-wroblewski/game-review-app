<x-layout>
    <div class="container py-4">
        <div class="row justify-content-center posts-container">
            <div class="col-md-8">
                
                {{-- Clean component with inline replies disabled! --}}
                <x-post :post="$post" :showReplies="false" />
                
                <p>Comments:</p>
                <div class="post-replies bg-white border rounded p-3">
                    @if ($post->replies_count>0)
                    <h5 class="mb-3">Replies ({{ $post->replies_count }})</h5>
                    <x-post.replies-list :replies="$replies" />
                    @else
                    <p> No replies yet. Be the first to comment! </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layout>