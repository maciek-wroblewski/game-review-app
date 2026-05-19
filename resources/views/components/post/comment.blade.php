{{-- resources/views/comment.blade.php --}}

@props(['post'])

{{-- Main container --}}
<div class="js-post-card js-comment-card position-relative rounded shadow-sm overflow-hidden mb-3 js-post-wrapper"
    data-post-id="{{ $post->id }}" data-is-review="false">

    @if(!$post->trashed())
    {{-- Background Container --}}
    <div class="position-relative w-100"
        style="background-image: url('{{ $post->author->banner ?? '' }}'); background-size: cover; background-position: center;">

        <div class="edit_form_collapsable">
        <div class="position-absolute top-0 start-0 w-100 h-100"
            style="background: linear-gradient(to right, rgb(255, 255, 255) 0%, rgba(255, 255, 255, 0.945) 40%, rgba(255, 255, 255, 0.59) 100%); pointer-events: none;">
        </div>


            {{-- View Mode wrapped in Clickable Card --}}
            <x-clickable-card :link="'/posts/' . $post->id">

                <div class="js-view-mode position-relative d-flex flex-row w-100 p-3 gap-3">
                    <x-user.avatar :user="$post->author" :size="'36px'" />

                    <div class="flex-grow-1 d-flex flex-column min-w-0 row-gap-3">
                        <!-- Header / Info -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold small">{{ $post->author->username }}</span>
                                <span class="text-muted small" style="font-size: 0.75rem;">{{
                                    $post->updated_at->diffForHumans() }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($post->author)
                                <x-follow-button :target-user="$post->author" />
                                @endif
                                <x-post.menu :post="$post" />
                            </div>
                        </div>

                        <!-- Content & Actions -->
                        <div>
                            <x-post.spoiler :is-spoiler="$post->is_spoiler">
                                <x-truncate-text :size="$post->media->count() > 0 ? 1 : 2">
                                    <x-post.text-body :body="$post->body" />
                                </x-truncate-text>
                                <x-post.media-grid :media="$post->media" />
                            </x-post.spoiler>
                        </div>
                        <!-- Actions -->
                        <div class="d-flex flex-row justify-content-between">
                            <div class="d-flex column-gap-2">
                                <x-post.reply-toggle :post="$post" />
                                <x-post.replies-toggle :post="$post" />
                            </div>
                            <x-like-button :post='$post' />
                        </div>
                    </div>
                </div>
            </x-clickable-card>
            <x-post.reply-container :post="$post">
                <x-post.comment-create :hubType="$post->hubable_type ?? $post->hub_type"
                    :hubId="$post->hubable_id ?? $post->hub_id" :parentId="$post->id" />
            </x-post.reply-container>

            <x-post.replies-container :postId="$post->id" id="accordion-{{ $post->id }}">
                <x-post.replies-list />
            </x-post.replies-container>
        </div>
        <x-post.edit-form :post="$post" />
    </div>

    {{-- Edit Form Component --}}

    @else
    <p class="text-muted fst-italic small">[This comment has been deleted]</p>
    @endif
</div>