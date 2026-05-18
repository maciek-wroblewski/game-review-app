@props(['replies' => null])

<div class="js-replies-wrapper">
    <div class="js-replies-content overflow-y-auto overflow-x-hidden" style="max-height: 60vh;">
        @if($replies)
            {{-- Direct Mode: Render items immediately on first load --}}
            <x-post.replies-items :replies="$replies" />
        @else
            {{-- Lazy-loaded Accordion Mode: Placeholder until expanded --}}
            <div class="text-center text-muted small py-4">
                <i class="bi bi-chat-dots me-1"></i> Click to load replies
            </div>
        @endif
    </div>

    {{-- Clean unified declaration using class scoping --}}
    <x-load-more 
        :paginator="$replies" 
        target=".js-replies-content" 
        button-class="btn btn-sm btn-outline-primary w-100" 
        text="Load More Replies" 
    />
</div>