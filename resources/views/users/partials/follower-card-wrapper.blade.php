<div class="col-md-6 col-lg-4 animate-fade-in">
    <a href="/users/{{ $follower->username }}" class="text-decoration-none">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <x-user.avatar :user='$follower'/>

                <div>
                    <h4 class="fw-bold text-dark mb-1">
                        {{ $follower->username }}
                    </h4>
                    <p class="text-muted mb-0">
                        {{ $follower->bio ?? 'No bio yet.' }}
                    </p>
                </div>
            </div>
        </div>
    </a>
</div>