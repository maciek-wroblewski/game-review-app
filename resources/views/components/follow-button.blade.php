<form action="/users/{{ $targetUser->id }}/follow" method="POST" class="m-0 ajax-follow-form d-inline-block"
    data-user-id="{{ $targetUser->id }}"
    data-follow-text="{{ __('common.follow') }}"
    data-unfollow-text="{{ __('common.unfollow') }}">
    @csrf
    <button type="submit"
        class="btn {{ $buttonClasses }} follow-btn {{ $isFollowing ? 'btn-outline-secondary' : 'btn-primary' }}">
        <span class="follow-text d-inline-block">{{ $isFollowing ? __('common.unfollow') : __('common.follow') }}</span>
    </button>
</form>