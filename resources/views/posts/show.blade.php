<x-layout>
    <div class="container py-4">
        <div class="row justify-content-center posts-container">
            <div class="col-md-8">
                
                {{-- Clean component with inline replies disabled! --}}
                <x-post :post="$post" :show-replies="false" />
                
                <p>Comments:</p>
                <div class="post-replies bg-white border rounded p-3">
                    <h5 class="mb-3">Replies ({{ $post->replies()->count() }})</h5>
                    <x-post.replies-list :replies="$replies" />
                </div>
            </div>
        </div>
    </div>
</x-layout>