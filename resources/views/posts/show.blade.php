<x-layout>
    <div class="container py-4">
        <div class="row justify-content-center posts-container">
            <div class="col-md-8">
                
                {{-- Clean component with inline replies disabled! --}}
                <x-post :post="$post" :showReplies="false" />
                
                <p>{{ __('posts.comments') }}</p>
                <div class="post-replies bg-white border rounded p-3">
                    <h5 class="js-replies-count-title mb-3 {{ $post->replies_count == 0 ? 'd-none' : '' }}">
                        {{ __('posts.replies', ['count' => $post->replies_count]) }}
                    </h5>
                    
                    <div class="js-replies-empty-msg text-placeholder {{ $post->replies_count > 0 ? 'd-none' : '' }}">
                        <p class="text-muted py-3 m-0 text-center">{{ __('posts.no_replies_yet') }}</p>
                    </div>

                    <x-post.replies-list :replies="$replies" />
                </div>
            </div>
        </div>
    </div>
</x-layout>