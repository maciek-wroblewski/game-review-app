<div class="col-md-6 col-lg-4 animate-fade-in">
    <a href="/users/{{ $followedUser->username }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <x-user.avatar :user='$followedUser'/>

                <div>
                    <h4 class="fw-bold text-dark mb-1">
                        {{ $followedUser->username }}
                    </h4>
                    <p class="text-muted mb-0">
                        {{ $followedUser->bio ?? 'No bio yet.' }}
                    </p>
                </div>
            </div>
        </div>
    </a>
</div>