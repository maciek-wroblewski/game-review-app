@props(['post'])

@auth
@if(auth()->id() === $post->user_id && !$post->trashed() && !auth()->user()->is_suspended)
<div class="d-flex align-items-center gap-2 public-post-actions">
    <button class="js-btn-edit btn btn-sm btn-light rounded-pill border-0 small btn-outline-secondary" data-post-id="{{ $post->id }}">
        <i class="bi bi-pencil me-1"></i><span>{{ __('common.edit') }}</span>
    </button>


    <form action="{{ route('posts.destroy', $post->id) }}" method="POST" class="d-inline m-0">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-light rounded-pill border-0 small btn-outline-danger" onclick="return confirm('Permanently delete this post?');">
            <i class="bi bi-trash me-1"></i><span>{{ __('common.delete') }}</span>
        </button>
    </form>
</div>
@endif
@endauth
