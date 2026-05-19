<x-layout headtitle="{{ $user->username }}">

    <div class="container py-5">

        <!-- Profile Header -->
        <x-user.card :user="$user" />

        {{-- Admin Controls --}}
        @auth

            @if(auth()->user()->is_admin)

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-body p-4">

                        <div class="d-flex align-items-center justify-content-between mb-3">

                            <div>

                                <h4 class="fw-bold mb-1">

                                    <i class="bi bi-shield-lock-fill text-danger me-2"></i>

                                    Admin Controls

                                </h4>

                                <p class="text-muted mb-0">

                                    Moderate and manage this account

                                </p>

                            </div>

                            <span class="badge bg-danger px-3 py-2">

                                ADMIN

                            </span>

                        </div>

                        <div class="d-flex gap-2 flex-wrap">

                            {{-- Verify --}}
                            <form method="POST"
                                  action="/admin/users/{{ $user->id }}/verify">

                                @csrf

                                <button type="submit"
                                        class="btn
                                        {{ $user->verified
                                            ? 'btn-outline-danger'
                                            : 'btn-outline-primary' }}">

                                    <i class="bi bi-patch-check-fill me-1"></i>

                                    @if($user->verified)

                                        Remove Verification

                                    @else

                                        Verify User

                                    @endif

                                </button>

                            </form>

                            {{-- Admin Toggle --}}
                            @if(auth()->id() !== $user->id)

                                <form method="POST"
                                      action="/admin/users/{{ $user->id }}/admin">

                                    @csrf

                                    <button type="submit"
                                            class="btn
                                            {{ $user->is_admin
                                                ? 'btn-outline-warning'
                                                : 'btn-outline-dark' }}">

                                        <i class="bi bi-shield-lock-fill me-1"></i>

                                        @if($user->is_admin)

                                            Remove Admin

                                        @else

                                            Make Admin

                                        @endif

                                    </button>

                                </form>

                            @endif

                            {{-- Suspend --}}
                            @if(auth()->id() !== $user->id)

                                <form method="POST"
                                      action="/admin/users/{{ $user->id }}/suspend">

                                    @csrf

                                    <button type="submit"
                                            class="btn
                                            {{ $user->is_suspended
                                                ? 'btn-outline-success'
                                                : 'btn-outline-danger' }}">

                                        <i class="bi bi-slash-circle-fill me-1"></i>

                                        @if($user->is_suspended)

                                            Unsuspend User

                                        @else

                                            Suspend User

                                        @endif

                                    </button>

                                </form>

                            @endif

                            {{-- Delete User --}}
                            @if(auth()->id() !== $user->id)

                                <form method="POST"
                                      action="/admin/users/{{ $user->id }}"
                                      onsubmit="return confirm('Delete this user account permanently?')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-danger">

                                        <i class="bi bi-trash-fill me-1"></i>

                                        Delete User

                                    </button>

                                </form>

                            @endif

                        </div>

                        {{-- Suspended Warning --}}
                        @if($user->is_suspended)

                            <div class="alert alert-danger border-0 mt-4 mb-0">

                                <div class="d-flex align-items-center gap-2">

                                    <i class="bi bi-exclamation-triangle-fill"></i>

                                    <strong>

                                        This account is currently suspended.

                                    </strong>

                                </div>

                            </div>

                        @endif

                    </div>

                </div>

            @endif

        @endauth

        <!-- Grid Row -->
        <div class="row g-4">

            <!-- Recent Posts -->
            <div class="col-md-6">

                <div class="card h-100 shadow-sm border-0">

                    <div class="card-body p-4 p-lg-5">

                        <div class="d-flex justify-content-between align-items-center mb-5">

                            <div>

                                <h2 class="fw-bold mb-1">

                                    Recent Posts

                                </h2>

                                <p class="text-muted mb-0">

                                    Latest thoughts and opinions from {{ $user->username }}

                                </p>

                            </div>

                        </div>

                        @forelse($user->posts as $post)

                            <x-post :post="$post" />

                        @empty

                            <div class="alert alert-info border-0 shadow-sm p-4 text-center">

                                <h4 class="fw-bold mb-2">

                                    No posts yet

                                </h4>

                                <p class="mb-0">

                                    This user has not posted anything yet.

                                </p>

                            </div>

                        @endforelse

                    </div>

                </div>

            </div>

            <!-- Profile Comments -->
            <div class="col-md-6">

                <div class="card h-100 shadow-sm border-0">

                    <div class="card-body p-4 p-lg-5">

                        <div class="d-flex justify-content-between align-items-center mb-5">

                            <div>

                                <h2 class="fw-bold mb-1">

                                    Comments

                                </h2>

                                <p class="text-muted mb-0">

                                    What users think of {{ $user->username }}

                                </p>

                            </div>

                        </div>

                        <div class="container">

                            <div class="mt-5 max-w-3xl mx-auto">

                                <x-hub-comments
                                    hub-type="user"
                                    :hub-id="$user->id"
                                    :posts="$posts" />

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-layout>