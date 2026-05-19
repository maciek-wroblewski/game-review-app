<x-layout headtitle="{{ $user->username }}">

    <div class="container py-5">

        <!-- Profile Header -->
        <x-user.card :user="$user" />

        {{-- Admin Controls --}}
        @auth

            @if(auth()->user()->is_admin)

                <div class="mb-4 d-flex gap-2">

                    <form method="POST"
                          action="/admin/users/{{ $user->id }}/verify">

                        @csrf

                        <button type="submit"
                                class="btn {{ $user->verified
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