<div class="card premium-dashboard-card border-0 mb-4 overflow-hidden">
    <div class="p-4 bg-dark text-white position-relative" style="background: linear-gradient(135deg, #0d6efd 0%, #083c8c 100%);">
        <div class="d-flex align-items-center gap-3">
            <x-user.static-avatar :user="auth()->user()" size="65px" class="border border-2 border-white shadow" />
            <div>
                <h5 class="fw-bold mb-0 text-white">{{ auth()->user()->username }}</h5>
                @if(auth()->user()->is_admin)
                    <span class="badge bg-danger small mt-1">ADMIN</span>
                @else
                    <span class="text-white-50 small">Member</span>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body p-4 bg-white">
        <!-- Stats counters -->
        <div class="row text-center g-2 mb-4">
            <div class="col-4">
                <div class="fw-bold fs-5 text-dark">
                    {{ auth()->user()->posts()->count() + auth()->user()->reviews()->count() }}
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">Contributions</div>
            </div>
            <div class="col-4">
                <div class="fw-bold fs-5 text-dark">
                    {{ auth()->user()->followers()->count() }}
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">Followers</div>
            </div>
            <div class="col-4">
                <div class="fw-bold fs-5 text-dark">
                    {{ auth()->user()->following()->count() }}
                </div>
                <div class="text-muted small" style="font-size: 0.75rem;">Following</div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <a href="/users/{{ auth()->user()->username }}" class="btn btn-sm btn-outline-primary rounded-pill">
                <i class="bi bi-person-circle me-1"></i> {{ __('common.view_profile') }}
            </a>
        </div>
    </div>
</div>
