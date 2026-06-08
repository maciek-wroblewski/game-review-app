@props(['users'])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3 d-flex align-items-center">
            <i class="bi bi-people-fill text-primary me-2"></i>
            {{ __('home.top_reviewers') }}
        </h5>
        
        <div class="d-flex flex-column gap-3">
            @forelse($users as $user)
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <x-user.avatar :user="$user" size="40px" />
                        <div>
                            <a href="{{ url('/users/' . $user->username) }}" class="fw-semibold text-decoration-none text-dark text-truncate d-block" style="max-width: 120px;">
                                {{ $user->username }}
                            </a>
                            <div class="text-muted small">
                                {{ $user->posts_count }} {{ trans_choice('common.posts', $user->posts_count) }}
                            </div>
                        </div>
                    </div>

                    @if(auth()->check() && auth()->id() !== $user->id)
                        <x-follow-button :target-user="$user" />
                    @endif
                </div>
            @empty
                <div class="text-muted small text-center py-3">
                    {{ __('common.none') }}
                </div>
            @endforelse
        </div>
    </div>
</div>
