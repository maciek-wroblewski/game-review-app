<x-layout>
    <div class="container py-4">
        <div class="row justify-content-center posts-container">
            <div class="col-md-8">
                
                {{-- <x-post :post='$post'/> lazy way to make the post dont have the button --}}
                @php
                $isReview = method_exists($post, 'isReview') && $post->isReview() && $post->review;
                @endphp
                <style>
                    .js-post-card {
                        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1);
                        will-change: transform, box-shadow;
                    }

                    .js-post-card:hover {
                        transform: scale(1.015) translateY(-2px);
                        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .1) !important;
                        z-index: 2;
                    }
                </style>

                <div class="js-post-wrapper js-post-card card shadow-sm mb-4 border-0 overflow-hidden {{ $isReview ? 'd-flex flex-row align-items-stretch' : '' }}"
                    data-post-id="{{ $post->id }}" data-is-review="{{ $isReview ? 'true' : 'false' }}">

                    @if($isReview && $post->review->type === 'recommendation')
                    <x-post.rating-meter :rating="$post->review->rating ?? 0" />
                    @endif

                    <div class="flex-grow-1 d-flex flex-column {{ $isReview ? 'bg-white' : '' }}" style="min-width: 0;">

                        {{-- Collapsible View Container --}}
                        @if($post->trashed())
                        <p class="card-text text-muted fst-italic p-3">[This post has been deleted]</p>
                        @else
                        <x-post.header :post="$post" />
                        <div class="edit_form_collapsable">
                            <x-post.content :post="$post" />
                            @if(!$post->trashed())

                            <div
                                class="js-post-footer card-footer bg-white border-top border-light d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex column-gap-2"">
                                    <x-post.reply-toggle :post="$post" />
                                </div>
                                <x-like-button :post='$post' />
                            </div>

                            @endif
                        </div>
                        <x-post.edit-form :post="$post" />
                        @endif

                        <x-post.reply-container :post="$post">
                            <x-post.comment-create :hubType="$post->hubable_type ?? $post->hub_type"
                                :hubId="$post->hubable_id ?? $post->hub_id" :parentId="$post->id" />
                        </x-post.reply-container>
                    </div>
                </div>
                <p>Comments:</p>
                <div class="post-replies bg-white border rounded p-3">
                    <h5 class="mb-3">Replies ({{ $post->replies()->count() }})</h5>
                    
                    <!-- Render direct-mode comments with server-populated $replies -->
                    <x-post.replies-list :replies="$replies" />
                </div>
            </div>
        </div>
    </div>
</x-layout>