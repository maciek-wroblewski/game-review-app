@props(['postId'])

<div class="js-comment-list-container bg-white border-top border-light overflow-y-auto overflow-x-hidden"
     data-post-id="{{ $postId }}"
     data-open="false"
     data-loaded="false"
     style="max-height: 0vh; opacity: 0; transition: max-height 0.3s ease-out, opacity 0.3s ease-out;"
     {{ $attributes }}>
    <div class="p-3">
        {{ $slot }}
    </div>
</div>
