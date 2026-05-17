@props(['post'])

<div class="js-reply-container overflow-hidden bg-white border border-light shadow-sm rounded"
     data-open="false"
     style="max-height: 0; opacity: 0; transition: max-height 0.3s ease-out, opacity 0.3s ease-out;">
    <div class="p-3">
        <x-post.comment-create 
            :hubType="$post->hubable_type ?? $post->hub_type" 
            :hubId="$post->hubable_id ?? $post->hub_id" 
            :parentId="$post->id" 
        />
    </div>
</div>