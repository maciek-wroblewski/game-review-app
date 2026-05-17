@props(['post'])
<x-clickable-card :href="'/posts/' . $post->id" class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom-0">
    <div class="d-flex align-items-center column-gap-4">
        <x-user.avatar :user="$post->author" layout="compact" :size="'50px'" />
        <div>
            <div class="d-flex align-items-center">
                <a href="/users/{{ $post->author->username ?? '#' }}" class="text-decoration-none fw-bold text-dark fs-5 me-2">
                    {{ $post->author->username ?? 'Anonymous' }}
                </a>
                @if(optional($post->author)->verified) <i class="bi bi-patch-check-fill text-primary"></i> @endif
                <span class="js-editing-badge badge bg-warning text-dark ms-2 d-none">Editing</span>
            </div>
            <div class="text-muted small d-flex flex-wrap gap-2">
                <span>{{ $post->created_at->diffForHumans(null, true, true) }}</span>
                @if($post->created_at->ne($post->updated_at))
                <span class="fst-italic" title="Edited on {{ $post->updated_at->diffForHumans(null, true, true) }}">(Edited)</span>
                @endif
                @if($post->hub)
                <span class="text-secondary">&bull;</span>
                <span>Posted in <a href="/{{ $post->hub->getTable() }}/{{ $post->hub_id }}">{{ $post->hub->title ?? $post->hub->name ?? $post->hub->username ?? 'Hub' }}</a></span>
                @endif
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        @if($post->author) <x-follow-button :target-user="$post->author" /> @endif
        <x-post.menu :post="$post" />
    </div>
</x-clickable-card>
